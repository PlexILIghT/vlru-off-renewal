<script setup lang="ts">
import IconVLRu from "./icons/IconVLRu.vue";
import { ref } from 'vue';

const isMenuOpen = ref(false);

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
  isMenuOpen.value = false;
};
</script>

<template>
  <header>
    <div class="container header-container">
      <IconVLRu />

      <button class="burger-menu" :class="{ 'active': isMenuOpen }" @click="toggleMenu" aria-label="Открыть меню">
        <span></span>
        <span></span>
        <span></span>
      </button>

      <div class="auth-buttons" :class="{ 'active': isMenuOpen }">
        <a href="https://www.vl.ru/off/o/" target="_blank" class="btn btn-outline" @click="closeMenu">Счетчики</a>
        <a href="https://www.vl.ru/off/o/" target="_blank" class="btn btn-outline" @click="closeMenu">Уведомления</a>
        <a class="btn btn-primary" href="https://www.vl.ru/off/telegram/login" target="_blank"
          @click="closeMenu">Войти</a>
      </div>

    </div>
  </header>
</template>

<style scoped>
header {
  background-color: white;
  box-shadow: var(--shadow);
  position: sticky;
  top: 0;
  width: 100%;
  z-index: 1000;
}

.header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 10px;
  position: relative;
}

.auth-buttons {
  display: flex;
  gap: 15px;
  transition: all 0.3s ease;
}

.burger-menu {
  display: none;
  flex-direction: column;
  justify-content: space-between;
  width: 30px;
  height: 21px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  z-index: 1001;
}

.burger-menu span {
  display: block;
  height: 3px;
  width: 100%;
  background-color: var(--primary);
  border-radius: 3px;
  transition: all 0.3s ease;
}

.burger-menu.active span:nth-child(1) {
  transform: translateY(9px) rotate(45deg);
}

.burger-menu.active span:nth-child(2) {
  opacity: 0;
}

.burger-menu.active span:nth-child(3) {
  transform: translateY(-9px) rotate(-45deg);
}

@media (max-width: 800px) {
  .burger-menu {
    display: flex;
  }

  .auth-buttons {
    position: fixed;
    top: 0;
    right: -100%;
    width: 240px;
    height: 100vh;
    background: white;
    flex-direction: column;
    justify-content: flex-start;
    align-items: stretch;
    padding: 70px 15px 20px;
    box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
    z-index: 999;
    gap: 0;
    transition: right 0.3s ease;
  }

  .auth-buttons.active {
    right: 0;
  }

  .auth-buttons .btn {
    width: 100%;
    justify-content: flex-start;
    padding: 12px 15px;
    margin-bottom: 8px;
    border-radius: var(--border-radius);
    border: none;
    background: none;
    text-align: left;
    font-size: 1rem;
  }

  .auth-buttons .btn:hover {
    background-color: rgba(249, 125, 65, 0.1);
  }

  .auth-buttons .btn-primary {
    background-color: var(--primary);
    color: white;
    margin-top: 15px;
  }

  .auth-buttons .btn-primary:hover {
    background-color: var(--primary-dark);
  }
}

@media (min-width: 801px) {
  .auth-buttons {
    display: flex !important;
  }

  .burger-menu {
    display: none !important;
  }
}
</style>