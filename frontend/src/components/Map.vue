<template>
  <section class="section map-section">
    <div class="container">
      <h2 class="section-title">Карта отключений</h2>
      <div class="map-container">
        <div class="map-placeholder">

        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { mockApi } from '../services/mockApi';

const loadCurrentStats = async () => {
  try {
    const activeOutages = await mockApi.getActiveOutages();
  } catch (error) {
    console.error('Error loading stats:', error);
  }
};

onMounted(() => {
  loadCurrentStats();
});
</script>

<style scoped>
.section {
  padding: 20px 0 80px 0;
}

.section-title {
  text-align: center;
  margin-bottom: 50px;
  font-size: 2rem;
  font-weight: 700;
}

.map-section {
  background: var(--light);
}

.map-container {
  height: 400px;
  border-radius: var(--border-radius);
  overflow: hidden;
  background: var(--light-gray);
}

.map-placeholder {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  color: var(--gray);
}

iframe {
  height: 100%;
  width: 100%;
  border-radius: var(--border-radius);
  border: none;
  display: block;
}

@media (max-width: 800px) {
  .section {
    padding: 60px 0;
  }

  .section-title {
    margin-bottom: 30px;
    font-size: 1.7rem;
  }

  .map-container {
    height: 350px;
    border-radius: 12px;
  }
}

@media (max-width: 600px) {
  .section {
    padding: 40px 0;
  }

  .section-title {
    margin-bottom: 25px;
    font-size: 1.5rem;
  }

  .map-container {
    height: 300px;
    border-radius: 8px;
    margin: 0 -10px;
    width: calc(100% + 20px);
  }

  iframe {
    border-radius: 0;
  }
}

@media (max-width: 480px) {
  .section {
    padding: 0 0 30px 0;
  }

  .section-title {
    font-size: 1.3rem;
    margin-bottom: 20px;
  }

  .map-container {
    height: 250px;
  }
}

@media (max-width: 360px) {
  .map-container {
    height: 200px;
  }
}
</style>