<template>
  <div class="container">
    <div class="search-container">
      <h3 style="margin-bottom: 15px">Проверьте отключения по адресу</h3>
      <form class="search-form" @submit.prevent="handleSearch">
        <div class="search-input-wrapper">
          <input type="text" v-model="searchQuery" @input="handleInput" @focus="showSuggestions = true"
            class="search-input" placeholder="Введите ваш адрес..." />

          <!-- Выпадающий список с подсказками -->
          <div v-if="showSuggestions && suggestions.length > 0" class="suggestions-dropdown">
            <div v-for="suggestion in suggestions" :key="suggestion" @click="selectSuggestion(suggestion)"
              class="suggestion-item">
              {{ suggestion }}
            </div>
          </div>
        </div>

        <button type="submit" class="search-btn">
          Найти
        </button>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import { mockApi } from '@/services/mockApi';
import {api} from '@/services/api.js';

export default {
  name: 'Search',
  setup() {
    const searchQuery = ref('');
    const suggestions = ref([]);
    const showSuggestions = ref(false);

    // Дебаунс для уменьшения количества запросов
    let debounceTimer = null;

    const handleInput = async () => {
      clearTimeout(debounceTimer);

      if (searchQuery.value.length < 2) {
        suggestions.value = [];
        return;
      }

      debounceTimer = setTimeout(async () => {
        try {
          // suggestions.value = await mockApi.getAddressSuggestions(searchQuery.value);
          suggestions.value = await api.getAddressSuggestions(searchQuery.value);
        } catch (error) {
          console.error('Error fetching suggestions:', error);
          suggestions.value = [];
        }
      }, 300);
    };

    const selectSuggestion = (suggestion) => {
      searchQuery.value = suggestion;
      showSuggestions.value = false;
    };

    const handleSearch = () => {
      if (!searchQuery.value.trim()) return;
      showSuggestions.value = false;
      // Здесь можно добавить логику поиска если нужно
    };

    // Закрытие выпадающего списка при клике вне его области
    const handleClickOutside = (event) => {
      if (!event.target.closest('.search-input-wrapper')) {
        showSuggestions.value = false;
      }
    };

    onMounted(() => {
      document.addEventListener('click', handleClickOutside);
    });

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside);
    });

    return {
      searchQuery,
      suggestions,
      showSuggestions,
      handleInput,
      selectSuggestion,
      handleSearch
    };
  }
};
</script>

<style scoped>
.search-container {
  background: white;
  border-radius: var(--border-radius);
  padding: 25px;
  box-shadow: var(--shadow);
  max-width: 800px;
  margin: -40px auto 0;
}

.search-form {
  display: flex;
  gap: 10px;
  position: relative;
}

.search-input-wrapper {
  flex: 1;
  position: relative;
}

.search-input {
  width: 100%;
  padding: 15px;
  border: 1px solid var(--light-gray);
  border-radius: var(--border-radius);
  font-size: 1rem;
  transition: var(--transition);
}

.search-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(249, 125, 65, 0.1);
}

/* Выпадающий список с подсказками */
.suggestions-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid var(--light-gray);
  border-top: none;
  border-radius: 0 0 var(--border-radius) var(--border-radius);
  box-shadow: var(--shadow);
  z-index: 100;
  max-height: 200px;
  overflow-y: auto;
}

.suggestion-item {
  padding: 12px 15px;
  border-bottom: 1px solid var(--light-gray);
  transition: var(--transition);
}

.suggestion-item:hover {
  background: var(--primary);
  color: white;
}

.suggestion-item:last-child {
  border-bottom: none;
}

.search-btn {
  background: var(--primary);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  padding: 0 25px;
  font-weight: 500;
  transition: var(--transition);
  min-width: 100px;
}

.search-btn:hover {
  background: var(--primary-dark);
}

@media (max-width: 800px) {
  .search-form {
    flex-direction: column;
  }

  .search-btn {
    width: 100%;
    padding: 15px;
  }
}
</style>