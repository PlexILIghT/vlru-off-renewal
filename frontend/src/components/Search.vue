<template>
  <div class="container">
    <div class="search-container">
      <h3 style="margin-bottom: 15px">Проверьте отключения по адресу</h3>
      <form class="search-form">
        <input
          type="text"
          class="search-input"
          placeholder="Введите ваш адрес..."
        />
        <button class="search-btn">Найти</button>
      </form>
    </div>
  </div>
</template>
<style scoped>
.search-container {
  background: white;
  border-radius: var(--border-radius);
  padding: 25px;
  box-shadow: var(--shadow);
  max-width: 800px;
  margin: -40px auto 0;
  position: relative;
  z-index: 10;
}

.search-form {
  display: flex;
  gap: 10px;
}

.search-input {
  flex: 1;
  padding: 15px;
  border: 1px solid var(--light-gray);
  border-radius: var(--border-radius);
  font-size: 1rem;
  transition: var(--transition);
}

.search-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.search-btn {
  background: var(--primary);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  padding: 0 25px;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
}

.search-btn:hover {
  background: var(--primary-dark);
}
</style>
