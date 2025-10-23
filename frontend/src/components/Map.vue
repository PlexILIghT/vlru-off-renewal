<template>
  <section class="section map-section">
    <div class="container">
      <h2 class="section-title">Карта отключений</h2>
      <div class="map-container">
        <div class="map-placeholder">
          <i class="fas fa-map-marked-alt"></i>
          <p>Интерактивная карта отключений</p>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.section {
  padding: 80px 0;
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
  background: var(--light-gray);
  border-radius: var(--border-radius);
  overflow: hidden;
  position: relative;
}

.map-placeholder {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  color: var(--gray);
}

.map-placeholder i {
  font-size: 4rem;
  margin-bottom: 20px;
  opacity: 0.5;
}
</style>
