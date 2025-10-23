<script setup lang="ts">
import IconVLRu from "./icons/IconVLRu.vue";
</script>

<template>
  <header>
    <div class="container header-container">
      <IconVLRu />
      <nav class="quick-nav">
        <a href="#" class="nav-btn">Аварийность по жалобам</a>
        <a href="#" class="nav-btn">Передать показания счетчиков</a>
        <a href="#" class="nav-btn">Сегодня</a>
      </nav>

      <div class="auth-buttons">
        <button class="btn btn-outline">Уведомления</button>
        <button class="btn btn-primary">Войти</button>
      </div>
    </div>
  </header>
</template>

<style scoped>
header {
  background-color: white;
  box-shadow: var(--shadow);
  position: top;
  width: 100%;
}

.header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 10px;
}

.quick-nav {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.nav-btn {
  padding: 10px 20px;
  text-decoration: none;
  color: var(--primary);
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  border-radius: var(--border-radius);
  transition: var(--transition);
}

.nav-btn:hover {
  color: white;
  background-color: var(--primary-dark);
}

.auth-buttons {
  display: flex;
  gap: 15px;
}
</style>
