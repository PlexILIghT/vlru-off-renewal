<script setup>
import Header from "./components/Header.vue";
import Footer from "./components/Footer.vue";
import Search from "./components/Search.vue";
import Map from "./components/Map.vue";
import Analitic from "./components/Analitic.vue";
import Prediction from "./components/Prediction.vue";
import { ref, onMounted } from 'vue';
import { mockApi } from './services/mockApi';
import {api} from "@/services/api.js";

const hasPlannedOutages = ref(false);
const currentStats = ref({
  cold_water: 0,
  hot_water: 0,
  electricity: 0,
  heating: 0
});
const orgStats = ref({
  'СП "Приморские тепловые сети" АО "ДГК"': 0,
  'МУПВ ВПЭС': 0,
  'АО "Оборонэнерго"': 0,
  'Управляющие организации': 0,
  'КГУП "Приморский водоканал"': 0
});

const loadCurrentStats = async () => {
  try {
    const activeOutages = await api.getActiveOutages();
    currentStats.value = {
      cold_water: activeOutages.filter(o => o.outageType === 'cold_water').length,
      hot_water: activeOutages.filter(o => o.outageType === 'hot_water').length,
      electricity: activeOutages.filter(o => o.outageType === 'electricity').length,
      heating: activeOutages.filter(o => o.outageType === 'heating').length
    };

    activeOutages.forEach(outage => {
      orgStats.value[outage.organization.name] += outage.houses.length;
    });
  } catch (error) {
    console.error('Error loading stats:', error);
  }
};

onMounted(() => {
  loadCurrentStats();
});
</script>

<template>
  <div class="app-wrapper">
    <Header />
    <div class="hero-section">
      <div id="grad">
        <div class="container">
          <h1>Отключения воды и электричества во Владивостоке</h1>
          <p>
            Актуальная информация о плановых и аварийных отключениях коммунальных
            услуг в вашем районе
          </p>
        </div>
      </div>
      <div class="search-overlap">
        <div class="container">
          <Search />
        </div>
      </div>
    </div>

    <div class="container">
      <div class="status-container">
        <div class="status-cards-grid">
          <div class="status-card">
            <h2>Сейчас в городе</h2>
            <div class="summary-now">
              <ul>
                <li>
                  <a href="https://www.vl.ru/off/summary/addr?date=now&resourceType=cold_water" target="_blank">нет
                    холодной воды:</a>
                  {{ currentStats.cold_water }} домов
                </li>
                <li>
                  <a href="https://www.vl.ru/off/summary/addr?date=now&resourceType=electricity" target="_blank">нет
                    электричества:</a>
                  {{ currentStats.electricity }} домов
                </li>
                <li>
                  <a href="https://www.vl.ru/off/summary/addr?date=now&resourceType=hot_water" target="_blank">нет
                    горячей воды:</a>
                  {{ currentStats.hot_water }} домов
                </li>
                <li>
                  <a href="https://www.vl.ru/off/summary/addr?date=now&resourceType=heat" target="_blank">нет
                    отопления:</a>
                  {{ currentStats.heating }} домов
                </li>
              </ul>
            </div>
          </div>
          <div class="status-card">
            <h2>Организации</h2>
            <div class="summary-now">
              <ul>
                <li>
                  <a href="https://www.vl.ru/off/summary/addr?date=now&organization=%D0%A1%D0%9F%20%22%D0%9F%D1%80%D0%B8%D0%BC%D0%BE%D1%80%D1%81%D0%BA%D0%B8%D0%B5%20%D1%82%D0%B5%D0%BF%D0%BB%D0%BE%D0%B2%D1%8B%D0%B5%20%D1%81%D0%B5%D1%82%D0%B8%22%20%D0%90%D0%9E%20%22%D0%94%D0%93%D0%9A%22"
                    target="_blank">СП "Приморские тепловые сети" АО "ДГК": </a>
                  {{ orgStats['СП "Приморские тепловые сети" АО "ДГК"'] }} домов
                </li>
                <li>
                  <a href="https://www.vl.ru/off/summary/addr?date=now&organization=%D0%9C%D0%A3%D0%9F%D0%92%20%D0%92%D0%9F%D0%AD%D0%A1"
                    target="_blank">МУПВ
                    ВПЭС:</a>
                  {{ orgStats['МУПВ ВПЭС'] }} домов
                </li>
                <li>
                  <a href="https://www.vl.ru/off/summary/addr?date=now&organization=%D0%90%D0%9E%20%22%D0%9E%D0%B1%D0%BE%D1%80%D0%BE%D0%BD%D1%8D%D0%BD%D0%B5%D1%80%D0%B3%D0%BE%22"
                    target="_blank">АО
                    "Оборонэнерго":</a>
                  {{ orgStats['АО "Оборонэнерго"'] }} домов
                </li>
                <li>
                  <a href="https://www.vl.ru/off/summary/addr?date=now&organization=%D0%A3%D0%BF%D1%80%D0%B0%D0%B2%D0%BB%D1%8F%D1%8E%D1%89%D0%B8%D0%B5%20%D0%BE%D1%80%D0%B3%D0%B0%D0%BD%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D0%B8"
                    target="_blank">Управляющие организации:</a>
                  {{ orgStats['Управляющие организации'] }} домов
                </li>
                <li>
                  <a href="https://www.vl.ru/off/summary/addr?date=now&organization=%D0%9A%D0%93%D0%A3%D0%9F%20%C2%AB%D0%9F%D1%80%D0%B8%D0%BC%D0%BE%D1%80%D1%81%D0%BA%D0%B8%D0%B9%20%D0%B2%D0%BE%D0%B4%D0%BE%D0%BA%D0%B0%D0%BD%D0%B0%D0%BB%C2%BB"
                    target="_blank">КГУП "Приморский водоканал":</a>
                  {{ orgStats['КГУП "Приморский водоканал"'] }} домов
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="status-today">
          <h2 class="status-title">Сегодня:</h2>
          <div class="status-links">
            <a href="https://www.vl.ru/off/summary?date=today" target="_blank" class="status-link">Общая сводка</a>
            <a href="https://www.vl.ru/off/summary/blackouts?date=today" target="_blank"
              class="status-link">Отключения</a>
            <a v-if="hasPlannedOutages" href="https://www.vl.ru/off/summary/blackouts?date=future" class="status-link"
              target="_blank">
              Плановые отключения
            </a>
          </div>
        </div>
        <Prediction />
      </div>
    </div>

    <Analitic />
    <Map />
    <hr>
    <Footer />
  </div>
</template>

<style scoped>
.app-wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.hero-section {
  position: relative;
}

#grad {
  background: var(--primary);
  color: white;
  padding: 40px 0 60px 0;
  text-align: center;
}

.search-overlap {
  position: relative;
  margin-top: -40px;
}

div#grad p {
  max-width: 700px;
  margin: 0 auto;
  font-size: 1.2rem;
}

div#grad h1 {
  font-size: 2.5rem;
  margin-bottom: 20px;
  font-weight: 700;
}

.status-container {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
  margin-top: 20px;
  color: black;
  font-size: 1.2rem;
}

.status-today {
  background: white;
  border-radius: var(--border-radius);
  padding: 0.8rem;
  color: black;
  box-shadow: var(--shadow);
  grid-column: 1 / -1;
  text-align: center;
}

.status-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  grid-column: 1 / -1;
}

.status-card {
  background: white;
  border-radius: var(--border-radius);
  padding: 25px;
  box-shadow: var(--shadow);
  text-align: center;
}

.summary-now {
  text-align: left;
}

.summary-now a {
  text-decoration: none;
  color: black;
}

.summary-now a:hover {
  background-color: rgba(249, 125, 65, 0.15);
  border-radius: 5px;
  color: var(--primary-dark);
}

.summary-now ul {
  list-style-type: none;
}

.organizations-list {
  margin-top: 15px;
}

.org-item {
  padding: 10px;
  margin: 5px 0;
  background: var(--light-gray);
  border-radius: var(--border-radius);
  transition: var(--transition);
}

.org-item:hover {
  background: var(--primary);
  color: white;
}

hr {
  border: 2px solid var(--primary);
  width: 95%;
  margin: 20px auto;
  border-radius: var(--border-radius);
}

.status-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 20px;
  display: inline-flex;
}

.status-links {
  display: inline-flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.status-link {
  text-decoration: none;
  color: black;
  padding: 0.75rem 1.5rem;
  border-radius: var(--border-radius);
  transition: var(--transition);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: 2px solid transparent;
}

.status-link:hover {
  color: var(--primary-dark);
  border-color: var(--primary);
}

@media (max-width: 800px) {
  #grad {
    padding: 40px 0 50px 0;
  }

  .search-overlap {
    margin-top: -30px;
  }

  .status-container {
    gap: 15px;
    margin-top: 15px;
  }

  .status-today {
    padding: 1.5rem 1rem;
  }

  .status-cards-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .status-card {
    padding: 1.5rem 1rem;
  }

  .status-title {
    margin-bottom: 1rem;
    text-align: center;
  }

  .status-links {
    flex-direction: column;
    width: 100%;
    gap: 0.75rem;
  }

  .status-link {
    width: 100%;
    justify-content: center;
    padding: 1rem;
  }

  .summary-now {
    text-align: center;
  }

  .summary-now ul {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .summary-now li {
    padding: 0.5rem 0;
  }
}
</style>