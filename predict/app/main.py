"""
District Multi-Head Forecast API (FastAPI)
- Загружает артефакт модели (LightGBM MultiOutput) и SQLite (Dump.db)
- Строит панель по районам, генерирует фичи и делает прогноз на H дней вперёд
- Отдаёт total и компоненты (по типам), согласуя sum(components)=total
- ДОБАВЛЕНО: эндпоинт alerts для UX (warn/critical по λ)
"""
import os
import re
import sqlite3
from datetime import datetime, timezone
from typing import Dict, List, Optional

import joblib
import numpy as np
import pandas as pd
from fastapi import FastAPI
from pydantic import BaseModel
from starlette.middleware.cors import CORSMiddleware

ARTIFACT_PATH = os.getenv("ARTIFACT_PATH", "current.joblib")
DB_PATH = os.getenv("DB_PATH", "Dump.db")

# Пороги алертов (можно переопределить в окружении)
ALERT_WARN_LAMBDA = float(os.getenv("ALERT_WARN_LAMBDA", "2.0"))
ALERT_CRIT_LAMBDA = float(os.getenv("ALERT_CRIT_LAMBDA", "5.0"))

ART = joblib.load(ARTIFACT_PATH)

MODEL = ART["model"]
FEATURE_COLS = ART["feature_cols"]  # базовые фичи (без OHE районов)
DISTRICT_OHE = ART["district_ohe"]  # список OHE колонок для районов
HEADS = ART["heads"]  # ['total_blackouts', '<type1>', ...]
H = int(ART["H"])
VERSION = ART["model_version"]
DISTRICT_NAME_MAP = ART.get("district_name_map", {})
Q_MODELS = ART.get("quantile_models", {})
RECONCILE = bool(ART.get("reconcile", True))
RECONCILE_WIN = int(ART.get("reconcile_share_window", 28))
TYPE_SAN_MAP = ART.get("type_san_map", {})

def _sanitize(s: str) -> str:
    return re.sub(r"\W+", "_", str(s)).lower()


def load_tables_from_sqlite(db_path: str):
    conn = sqlite3.connect(db_path)
    buildings = pd.read_sql("SELECT id, district_id, is_fake FROM buildings", conn)
    blackouts = pd.read_sql("SELECT id, start_date, end_date, type FROM blackouts", conn)
    bbl = pd.read_sql("SELECT blackout_id, building_id FROM blackouts_buildings", conn)
    conn.close()
    return buildings, blackouts, bbl


def build_district_daily(buildings: pd.DataFrame,
                         blackouts: pd.DataFrame,
                         bbl: pd.DataFrame) -> pd.DataFrame:
    bo = blackouts.copy()
    bo["start_date"] = pd.to_datetime(bo["start_date"]).dt.floor("D")
    bo["end_date"] = pd.to_datetime(bo["end_date"]).fillna(bo["start_date"]).dt.floor("D")
    bad = bo["end_date"] < bo["start_date"]
    bo.loc[bad, "end_date"] = bo.loc[bad, "start_date"]

    data = (
        bbl.merge(
            buildings[["id", "district_id", "is_fake"]],
            left_on="building_id",
            right_on="id",
            how="left",
        )
        .query("is_fake == 0")
        .merge(
            bo[["id", "start_date", "end_date", "type"]],
            left_on="blackout_id",
            right_on="id",
            how="inner",
        )
    )
    data["type"] = data["type"].fillna("unknown")

    data["date_range"] = data.apply(
        lambda r: pd.date_range(r["start_date"], r["end_date"], freq="D"), axis=1
    )
    long = data.explode("date_range", ignore_index=True).rename(columns={"date_range": "date"})

    long = long.drop_duplicates(subset=["district_id", "blackout_id", "date", "type"])

    dd = (
        long.groupby(["district_id", "date", "type"])
        .size()
        .unstack(fill_value=0)
        .sort_index()
    )
    dd.columns = [_sanitize(c) for c in dd.columns]
    dd["total_blackouts"] = dd.sum(axis=1).astype("int64")

    dmin, dmax = dd.index.get_level_values("date").min(), dd.index.get_level_values("date").max()
    full_idx = pd.MultiIndex.from_product(
        [dd.index.get_level_values("district_id").unique(), pd.date_range(dmin, dmax, freq="D")],
        names=["district_id", "date"],
    )
    dd = dd.reindex(full_idx, fill_value=0).sort_index()
    return dd


import holidays

RU_HOLIDAYS = holidays.Russia()


def make_panel_features(panel_df: pd.DataFrame):
    """
    Устойчивые фичи:
    - 'district_id' и 'date' → именно СТОЛБЦЫ на выходе
    - календарь присваивается массивами (to_numpy) → без выравнивания по индексам и NaN
    - фиксированные лаги/окна (1,2,3,7,14,28 и 7,14,28)
    """
    df = panel_df.copy()
    df.index.names = ["district_id", "date"]
    df = df.sort_index()

    value_cols = list(df.columns)
    lags = [1, 2, 3, 7, 14, 28]
    wins = [7, 14, 28]
    parts = []

    for district, g in df.groupby(level="district_id", sort=False):
        g = g.copy()
        g.index = g.index.get_level_values("date")

        for col in value_cols:
            for L in lags:
                g[f"{col}_lag_{L}"] = g[col].shift(L)
            for W in wins:
                roll = g[col].rolling(W)
                g[f"{col}_roll_mean_{W}"] = roll.mean()
                g[f"{col}_roll_std_{W}"] = roll.std()

        idx = g.index
        g["day_of_week"] = idx.dayofweek.astype("int8")
        g["day_of_month"] = idx.day.astype("int8")
        g["week_of_year"] = idx.isocalendar().week.to_numpy(dtype="int16", copy=False)
        g["month"] = idx.month.astype("int8")
        g["quarter"] = idx.quarter.astype("int8")
        g["is_weekend"] = (g["day_of_week"] >= 5).astype("int8")
        g["sin_dow"] = np.sin(2 * np.pi * g["day_of_week"] / 7)
        g["cos_dow"] = np.cos(2 * np.pi * g["day_of_week"] / 7)
        g["sin_month"] = np.sin(2 * np.pi * g["month"] / 12)
        g["cos_month"] = np.cos(2 * np.pi * g["month"] / 12)
        g["is_holiday"] = pd.Series(
            (1 if d in RU_HOLIDAYS else 0 for d in idx), index=idx, dtype="int8"
        )

        g = g.reset_index().rename(columns={"index": "date"})
        g["district_id"] = str(district)
        parts.append(g)

    feat = pd.concat(parts, ignore_index=True)
    feat["date"] = pd.to_datetime(feat["date"])
    feat["district_id"] = feat["district_id"].astype(str)
    feat = feat.sort_values(["date", "district_id"]).reset_index(drop=True)
    return feat


def build_features_as_of(features_panel: pd.DataFrame,
                         district_ohe_cols: List[str],
                         feature_cols: List[str],
                         as_of_date: Optional[str] = None):
    df = features_panel.copy()
    if as_of_date is None:
        as_of = df["date"].max()
    else:
        as_of = pd.to_datetime(as_of_date).floor("D")
    df = df[df["date"] <= as_of]

    if df.empty:
        return pd.DataFrame(columns=["district_id", "date"]), pd.DataFrame()

    last_rows = (
        df.sort_values(["district_id", "date"])
        .groupby("district_id", as_index=False)
        .tail(1)
        .copy()
        .reset_index(drop=True)
    )
    # Базовые фичи
    X_base = last_rows[feature_cols].copy()

    # OHE районов
    X_cat = pd.get_dummies(last_rows[["district_id"]], prefix="district", drop_first=False)
    for c in district_ohe_cols:
        if c not in X_cat.columns:
            X_cat[c] = 0
    X_cat = X_cat[district_ohe_cols]

    X_inf = pd.concat([X_base, X_cat], axis=1)
    meta = last_rows[["district_id", "date"]].copy().reset_index(drop=True)
    return meta, X_inf


def reshape_multihead(yf: np.ndarray, heads: List[str], H: int) -> Dict[str, np.ndarray]:
    out = {}
    for hi, head in enumerate(heads):
        out[head] = yf[:, hi * H: (hi + 1) * H]
    return out


def recent_type_shares(panel_df: pd.DataFrame, district_id: str, types: List[str], window: int):
    try:
        g = panel_df.loc[district_id].tail(window)
        s = g[types].sum()
        if s.sum() > 0:
            return (s / s.sum()).to_dict()
    except Exception:
        pass
    return {t: 1.0 / len(types) for t in types}


class MultiHeadItem(BaseModel):
    district_id: str
    district_name: Optional[str] = None
    as_of_date: str
    start_date: str
    horizon_days: int
    yhat_total: List[float]
    components: Dict[str, List[float]]
    reconciled: bool
    pi80_low_total: Optional[List[float]] = None
    pi80_high_total: Optional[List[float]] = None
    components_pi80_low: Optional[Dict[str, List[float]]] = None
    components_pi80_high: Optional[Dict[str, List[float]]] = None


class MultiHeadResponse(BaseModel):
    model_version: str
    generated_at: str
    granularity: str
    horizon: int
    unit: str
    component_types: List[str]
    component_types_display: Dict[str, str]
    forecast: List[MultiHeadItem]


class AlertDay(BaseModel):
    date: str
    lambda_total: float
    p_any: float
    severity: str
    top_type: Optional[str] = None
    components: Dict[str, float]
    shares: Dict[str, float]

class DistrictAlerts(BaseModel):
    district_id: str
    district_name: Optional[str] = None
    max_lambda: float
    alerts: List[AlertDay]


class AlertsResponse(BaseModel):
    model_version: str
    generated_at: str
    as_of_date: str
    granularity: str
    horizon: int
    unit: str
    thresholds: Dict[str, float]
    summary: Dict[str, int]
    alerts: List[DistrictAlerts]
    message: str



app = FastAPI(title="District Multi-Head Forecast API", version="1.1.0")

origins = ["*"]

app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/predict/forecast/districts/multihead", response_model=MultiHeadResponse)
def forecast_multihead(as_of_date: Optional[str] = None, horizon: Optional[int] = None):
    buildings, blackouts, bbl = load_tables_from_sqlite(DB_PATH)
    panel = build_district_daily(buildings, blackouts, bbl)
    feat = make_panel_features(panel)

    meta_last, Xinf = build_features_as_of(feat, DISTRICT_OHE, FEATURE_COLS, as_of_date=as_of_date)
    if Xinf.shape[0] == 0:
        return MultiHeadResponse(
            model_version=VERSION,
            generated_at=datetime.now(timezone.utc).isoformat(),
            granularity="district",
            horizon=min(H, horizon or H),
            unit="events_per_day",
            component_types=[h for h in HEADS if h != "total_blackouts"],
            component_types_display={h: TYPE_SAN_MAP.get(h, h) for h in HEADS if h != "total_blackouts"},
            forecast=[],
        )

    yhat_flat = np.maximum(MODEL.predict(Xinf), 0.0)
    y_by_head = reshape_multihead(yhat_flat, HEADS, H)

    p10_by_head = p90_by_head = None
    if Q_MODELS:
        p10_by_head = reshape_multihead(np.maximum(Q_MODELS[0.1].predict(Xinf), 0.0), HEADS, H)
        p90_by_head = reshape_multihead(np.maximum(Q_MODELS[0.9].predict(Xinf), 0.0), HEADS, H)

    total_key = "total_blackouts"
    comp_types = [h for h in HEADS if h != total_key]
    for t in comp_types:
        if t not in panel.columns:
            panel[t] = 0

    meta_last = meta_last.reset_index(drop=True)
    assert yhat_flat.shape[0] == len(meta_last), "meta_last и предсказания разной длины"

    Hh = H if horizon is None else max(1, min(int(horizon), H))
    as_of = pd.to_datetime(meta_last["date"].iloc[0]).strftime("%Y-%m-%d")

    items: List[MultiHeadItem] = []
    for i, row in meta_last.iterrows():  # i = 0..N-1
        d_id = row["district_id"]
        tot = y_by_head[total_key][i][:Hh].copy()
        comps = {t: y_by_head[t][i][:Hh].copy() for t in comp_types}
        coherent = False

        if RECONCILE and len(comp_types) > 0:
            sums = np.zeros(Hh)
            for t in comp_types:
                sums += comps[t]
            if np.any(sums > 0):
                for h in range(Hh):
                    sh = sum(comps[t][h] for t in comp_types)
                    if sh > 0:
                        scale = tot[h] / sh
                        for t in comp_types:
                            comps[t][h] *= scale
                coherent = True
            else:
                shares = recent_type_shares(panel, d_id, comp_types, RECONCILE_WIN)
                for h in range(Hh):
                    for t in comp_types:
                        comps[t][h] = float(tot[h] * shares[t])
                coherent = True

        item = dict(
            district_id=d_id,
            district_name=DISTRICT_NAME_MAP.get(d_id, str(d_id)),
            as_of_date=as_of,
            start_date=(pd.to_datetime(as_of) + pd.Timedelta(days=1)).strftime("%Y-%m-%d"),
            horizon_days=Hh,
            yhat_total=[float(v) for v in tot],
            components={t: [float(v) for v in comps[t]] for t in comp_types},
            reconciled=coherent,
        )
        if p10_by_head is not None:
            item["pi80_low_total"] = [float(v) for v in p10_by_head[total_key][i][:Hh]]
            item["pi80_high_total"] = [float(v) for v in p90_by_head[total_key][i][:Hh]]
            item["components_pi80_low"] = {t: [float(v) for v in p10_by_head[t][i][:Hh]] for t in comp_types}
            item["components_pi80_high"] = {t: [float(v) for v in p90_by_head[t][i][:Hh]] for t in comp_types}

        items.append(MultiHeadItem(**item))

    return MultiHeadResponse(
        model_version=VERSION,
        generated_at=datetime.now(timezone.utc).isoformat(),
        granularity="district",
        horizon=Hh,
        unit="events_per_day",
        component_types=comp_types,
        component_types_display={t: TYPE_SAN_MAP.get(t, t) for t in comp_types},
        forecast=items,
    )


@app.get("/predict/models/district-forecast/metadata")
def model_metadata():
    return {
        "model_version": VERSION,
        "train_time": ART.get("train_time"),
        "train_range": ART.get("train_range"),
        "metrics_cv": ART.get("metrics_cv", {}),
        "features": FEATURE_COLS + DISTRICT_OHE,
        "heads": HEADS,
        "horizon": H,
        "reconcile": RECONCILE,
        "reconcile_share_window": RECONCILE_WIN,
        "quantiles_enabled": bool(Q_MODELS),
    }


@app.get("/predict/forecast/districts/alerts", response_model=AlertsResponse)
def forecast_alerts(as_of_date: Optional[str] = None,
                    horizon: Optional[int] = None,
                    top_k: Optional[int] = None):
    buildings, blackouts, bbl = load_tables_from_sqlite(DB_PATH)
    panel = build_district_daily(buildings, blackouts, bbl)
    feat = make_panel_features(panel)

    meta_last, Xinf = build_features_as_of(feat, DISTRICT_OHE, FEATURE_COLS, as_of_date=as_of_date)

    if Xinf.shape[0] == 0:
        as_of = as_of_date or "unknown"
        return AlertsResponse(
            model_version=VERSION,
            generated_at=datetime.now(timezone.utc).isoformat(),
            as_of_date=str(as_of),
            granularity="district",
            horizon=min(H, horizon or H),
            unit="events_per_day",
            thresholds={"warn": ALERT_WARN_LAMBDA, "critical": ALERT_CRIT_LAMBDA},
            summary={"warn_days": 0, "critical_days": 0, "districts": 0},
            alerts=[],
            message="Нет данных для инференса на указанную дату или ранее — проблем не обнаружено.",
        )

    yhat_flat = np.maximum(MODEL.predict(Xinf), 0.0)
    y_by_head = reshape_multihead(yhat_flat, HEADS, H)

    total_key = "total_blackouts"
    comp_types = [h for h in HEADS if h != total_key]
    for t in comp_types:
        if t not in panel.columns:
            panel[t] = 0

    meta_last = meta_last.reset_index(drop=True)
    assert yhat_flat.shape[0] == len(meta_last), "meta_last и предсказания разной длины"

    Hh = H if horizon is None else max(1, min(int(horizon), H))
    as_of = pd.to_datetime(meta_last["date"].iloc[0]).strftime("%Y-%m-%d")

    district_alerts: List[DistrictAlerts] = []
    warn_count = 0
    crit_count = 0

    for i, row in meta_last.iterrows():
        d_id = row["district_id"]
        d_name = DISTRICT_NAME_MAP.get(d_id, str(d_id))

        tot = y_by_head[total_key][i][:Hh].copy()
        comps = {t: y_by_head[t][i][:Hh].copy() for t in comp_types}

        sums = np.zeros(Hh)
        for t in comp_types:
            sums += comps[t]
        if RECONCILE and np.any(sums > 0):
            for h in range(Hh):
                sh = sum(comps[t][h] for t in comp_types)
                if sh > 0:
                    scale = tot[h] / sh
                    for t in comp_types:
                        comps[t][h] *= scale
        elif RECONCILE and not np.any(sums > 0) and len(comp_types) > 0:
            shares = recent_type_shares(panel, d_id, comp_types, RECONCILE_WIN)
            for h in range(Hh):
                for t in comp_types:
                    comps[t][h] = float(tot[h] * shares[t])

        day_alerts: List[AlertDay] = []
        for h in range(Hh):
            lam = float(tot[h])
            if lam >= ALERT_CRIT_LAMBDA:
                severity = "critical"
            elif lam >= ALERT_WARN_LAMBDA:
                severity = "warn"
            else:
                continue

            p_any = float(1.0 - np.exp(-lam))
            comp_vals = {t: float(comps[t][h]) for t in comp_types}
            total_for_share = sum(comp_vals.values())
            shares = {t: (comp_vals[t] / total_for_share) if total_for_share > 0 else 0.0 for t in comp_types}
            top_type = max(comp_vals, key=comp_vals.get) if len(comp_vals) else None

            alert_day = AlertDay(
                date=(pd.to_datetime(as_of) + pd.Timedelta(days=h + 1)).strftime("%Y-%m-%d"),
                lambda_total=lam,
                p_any=p_any,
                severity=severity,
                top_type=top_type,
                components=comp_vals,
                shares=shares,
            )
            day_alerts.append(alert_day)

            if severity == "critical":
                crit_count += 1
            else:
                warn_count += 1

        if day_alerts:
            district_alerts.append(
                DistrictAlerts(
                    district_id=d_id,
                    district_name=d_name,
                    max_lambda=max(a.lambda_total for a in day_alerts),
                    alerts=sorted(day_alerts, key=lambda a: a.date),
                )
            )

    if top_k is not None:
        try:
            k = max(1, int(top_k))
            district_alerts = sorted(district_alerts, key=lambda x: x.max_lambda, reverse=True)[:k]
        except Exception:
            pass
    else:
        district_alerts = sorted(district_alerts, key=lambda x: x.max_lambda, reverse=True)

    message = "На горизонте проблемные дни обнаружены." if (warn_count + crit_count) > 0 \
        else f"Все хорошо: на горизонте {Hh} дней λ_total во всех районах ниже порога warn ({ALERT_WARN_LAMBDA})."

    return AlertsResponse(
        model_version=VERSION,
        generated_at=datetime.now(timezone.utc).isoformat(),
        as_of_date=as_of,
        granularity="district",
        horizon=Hh,
        unit="events_per_day",
        thresholds={"warn": ALERT_WARN_LAMBDA, "critical": ALERT_CRIT_LAMBDA},
        summary={"warn_days": warn_count, "critical_days": crit_count, "districts": len(district_alerts)},
        alerts=district_alerts,
        message=message,
    )
