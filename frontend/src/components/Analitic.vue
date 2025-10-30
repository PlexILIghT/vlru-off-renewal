<template>
  <section class="analytics-section">
    <div class="container">
      <h2>Аналитика отключений</h2>

      <div class="period-selector">
        <button v-for="period in periods" :key="period.value" @click="setPeriod(period.value)"
          :class="['period-btn', { active: selectedPeriod === period.value }]">
          {{ period.label }}
        </button>
      </div>

      <div class="period-stats">
        <div class="stat-item">
          <div class="stat-value">{{ stats.totalOutages }}</div>
          <div class="stat-label">Всего отключений</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">{{ stats.activeOutages }}</div>
          <div class="stat-label">Активные сейчас</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">{{ stats.affectedHouses }}</div>
          <div class="stat-label">Затронуто домов</div>
        </div>
      </div>

      <div class="chart-container">
        <div class="chart" ref="chartEl"></div>

        <div class="legend">
          <div v-for="type in outageTypes" :key="type.value" class="legend-item">
            <div class="legend-color" :style="{ backgroundColor: type.color }"></div>
            <span class="legend-label">{{ type.label }}</span>
            <span class="legend-count">{{ getTypeCount(type.value) }}</span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, onMounted, watch, reactive, nextTick } from 'vue';
import { mockApi } from '@/services/mockApi';
import {api} from '@/services/api.js';

export default {
  name: 'Analitic',
  setup() {
    const selectedPeriod = ref('24h');
    const selectedBar = ref(null);
    const chartEl = ref(null);
    const outagesData = ref([]);
    const analyticsData = ref({});

    const stats = reactive({
      totalOutages: 0,
      activeOutages: 0,
      affectedHouses: 0
    });

    const periods = [
      { label: '60 минут', value: '60m' },
      { label: '24 часа', value: '24h' },
      { label: '30 дней', value: '30d' }
    ];

    const outageTypes = [
      { value: 'cold_water', label: 'Холодная вода', color: '#3498db' },
      { value: 'hot_water', label: 'Горячая вода', color: '#e74c3c' },
      { value: 'electricity', label: 'Электричество', color: '#f39c12' },
      { value: 'heating', label: 'Отопление', color: '#9b59b6' }
    ];

    // Загрузка данных
    const loadData = async () => {
      try {
        outagesData.value = await api.getOutages();
        // outagesData.value = await mockApi.getOutages();
        await generateAnalyticsData();
        updateStats();
        await nextTick();
        renderChart();
      } catch (error) {
        console.error('Error loading data:', error);
      }
    };

    // Обновление статистики
    const updateStats = () => {
      const periodData = analyticsData.value[selectedPeriod.value];
      stats.totalOutages = periodData ? periodData.reduce((total, bar) => total + bar.total, 0) : 0;
      stats.activeOutages = outagesData.value.filter(outage => outage.status === 'active').length;
      stats.affectedHouses = periodData ? periodData.reduce((total, bar) => total + bar.affectedHouses, 0) : 0;
    };

    // Генерация данных для графика
    const generateAnalyticsData = async () => {
      try {

        const data60m = await api.getAnalytics('60m');
        const data24h = await api.getAnalytics('24h');
        const data30d = await api.getAnalytics('30d');

        // const data60m = await mockApi.getAnalytics('60m');
        // const data24h = await mockApi.getAnalytics('24h');
        // const data30d = await mockApi.getAnalytics('30d');

        analyticsData.value = {
          '60m': data60m,
          '24h': data24h,
          '30d': data30d
        };

      } catch (error) {
        console.error('Error generating analytics data:', error);
      }
    };

    // Отрисовка графика
    const renderChart = () => {
      if (!chartEl.value) {
        console.log('Chart element not found');
        return;
      }

      const data = analyticsData.value[selectedPeriod.value];
      if (!data || data.length === 0) {
        console.log('No data for chart');
        chartEl.value.innerHTML = '<div class="no-data">Нет данных для отображения</div>';
        return;
      }

      console.log('Rendering chart with data:', data);

      chartEl.value.innerHTML = '';

      const width = chartEl.value.clientWidth;
      const height = 220;
      const margin = { top: 20, right: 20, bottom: 40, left: 40 };
      const chartWidth = width - margin.left - margin.right;
      const chartHeight = height - margin.top - margin.bottom;

      const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      const svgWidth = Math.max(chartEl.value.clientWidth, data.length * 33);
      svg.setAttribute('width', svgWidth);
      svg.setAttribute('height', height);
      svg.setAttribute('class', 'chart-svg');

      // Находим максимальное значение для масштабирования
      const maxValue = Math.max(...data.map(bar =>
        Object.values(bar.types).reduce((sum, houses) => sum + houses, 0)
      ), 0);

      const barWidth = Math.max(20, chartWidth / data.length - 10);

      data.forEach((bar, index) => {
        const x = margin.left + index * (barWidth + 10);
        let yAccumulator = margin.top + chartHeight;

        // Рисуем сегменты столбца
        outageTypes.forEach(type => {
          const count = bar.types[type.value] || 0;
          if (count > 0) {
            const segmentHeight = (count / maxValue) * chartHeight;
            const y = yAccumulator - segmentHeight;

            const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            rect.setAttribute('x', x);
            rect.setAttribute('y', y);
            rect.setAttribute('width', barWidth);
            rect.setAttribute('height', segmentHeight);
            rect.setAttribute('fill', type.color);
            rect.setAttribute('class', 'bar-segment');
            rect.setAttribute('data-index', index);

            rect.addEventListener('mouseenter', () => handleBarHover(index));
            rect.addEventListener('mouseleave', handleBarLeave);
            rect.addEventListener('click', () => handleBarClick(bar));

            svg.appendChild(rect);
            yAccumulator = y;
          }
        });

        // Подпись снизу
        const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', x + barWidth / 2);
        text.setAttribute('y', height - 10);
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('class', 'bar-label');
        text.setAttribute('font-size', '12px');
        text.setAttribute('fill', '#666');
        text.textContent = bar.label;
        svg.appendChild(text);

        // Подпись сверху с общим количеством
        const total = Object.values(bar.types).reduce((sum, count) => sum + count, 0);
        if (total > 0) {
          const totalText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
          totalText.setAttribute('x', x + barWidth / 2);
          totalText.setAttribute('y', margin.top + chartHeight - (total / maxValue) * chartHeight - 5);
          totalText.setAttribute('text-anchor', 'middle');
          totalText.setAttribute('class', 'bar-value');
          totalText.setAttribute('font-size', '11px');
          totalText.setAttribute('font-weight', 'bold');
          totalText.setAttribute('fill', 'white');
          totalText.textContent = total;
          svg.appendChild(totalText);
        }
      });

      chartEl.value.appendChild(svg);
    };

    const handleBarHover = (index) => {
      const segments = document.querySelectorAll(`.bar-segment[data-index="${index}"]`);
      segments.forEach(segment => {
        segment.style.opacity = '0.8';
      });
    };

    const handleBarLeave = () => {
      const segments = document.querySelectorAll('.bar-segment');
      segments.forEach(segment => {
        segment.style.opacity = '1';
      });
    };

    const handleBarClick = (bar) => {
      selectedBar.value = bar;
    };

    const setPeriod = async (period) => {
      selectedPeriod.value = period;
      await generateAnalyticsData();
      await nextTick();
      updateStats();
      renderChart();
      selectedBar.value = null;
    };

    const getTypeCount = (type) => {
      const data = analyticsData.value[selectedPeriod.value];
      if (!data || data.length === 0) return 0;
      const totalHouses = data.reduce((total, bar) => total + (bar.types[type] || 0), 0);
      return totalHouses;
    };

    const getBarTypeCount = (bar, type) => {
      return bar.types[type] || 0;
    };

    onMounted(() => {
      loadData();
    });

    watch(selectedPeriod, async () => {
      await generateAnalyticsData();
      await nextTick();
      updateStats();
      renderChart();
      selectedBar.value = null;
    });

    return {
      selectedPeriod,
      selectedBar,
      stats,
      periods,
      outageTypes,
      chartEl,
      setPeriod,
      getTypeCount,
      getBarTypeCount
    };
  }
};
</script>

<style scoped>
.analytics-section {
  padding: 3rem 0 1rem 0;
}

.analytics-section h2 {
  text-align: center;
  margin-bottom: 2rem;
  font-size: 2rem;
}

.period-selector {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.period-btn {
  padding: 0.75rem 1.5rem;
  border: 2px solid var(--primary);
  background: white;
  color: var(--primary);
  border-radius: var(--border-radius);
  transition: var(--transition);
  font-weight: 600;
}

.period-btn:hover {
  background: var(--primary-dark);
  color: white;
}

.period-btn.active {
  background: var(--primary);
  color: white;
}

.period-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-item {
  text-align: center;
  padding: 1.5rem;
  border-radius: var(--border-radius);
  background: white;
  box-shadow: var(--shadow);
}

.stat-value {
  font-size: 2rem;
  font-weight: bold;
  color: var(--primary);
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 0.9rem;
  color: var(--gray);
}

.chart-container {
  background: white;
  border-radius: var(--border-radius);
  padding: 2rem;
  box-shadow: var(--shadow);
  margin-bottom: 2rem;
}

.chart {
  width: 100%;
  height: 90%;
  margin-bottom: 2rem;
  overflow-x: auto;
}

.no-data {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: var(--gray);
  font-size: 1.1rem;
}

.bar-segment {
  transition: var(--transition);
}

.bar-segment:hover {
  opacity: 0.8;
}

.legend {
  display: flex;
  justify-content: center;
  gap: 2rem;
  flex-wrap: wrap;
  padding-top: 1rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  background: var(--light);
}

.legend-color {
  width: 16px;
  height: 16px;
  border-radius: 50%;
}

.legend-label {
  font-size: 0.9rem;
  font-weight: 500;
}

.legend-count {
  font-size: 0.8rem;
  background: white;
  padding: 0.2rem 0.5rem;
  border-radius: 10px;
  font-weight: bold;
}

.bar-details {
  border-radius: var(--border-radius);
  padding: 2rem;
  margin-top: 1rem;
  background: white;
  box-shadow: var(--shadow);
}

.bar-details h3 {
  margin-bottom: 1.5rem;
  text-align: center;
  color: var(--dark);
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: var(--light);
  border-radius: var(--border-radius);
}

.detail-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.detail-color {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.detail-type {
  font-weight: 500;
  color: var(--dark);
}

.detail-value {
  font-weight: bold;
  color: var(--primary);
  font-size: 1.1rem;
}

@media (max-width: 800px) {
  .analytics-section {
    padding: 1.5rem 0;
  }

  .period-selector {
    gap: 0.5rem;
  }

  .period-btn {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
  }

  .chart-container {
    padding: 1rem;
  }

  .chart {
    height: 250px;
    overflow-x: auto;
    overflow-y: hidden;
  }

  .chart-svg {
    min-width: 600px;
  }

  .legend {
    gap: 1rem;
  }

  .legend-item {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
  }
}
</style>