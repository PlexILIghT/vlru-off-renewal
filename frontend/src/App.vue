<script setup>
import Header from "./components/Header.vue";
import Footer from "./components/Footer.vue";
import Search from "./components/Search.vue";
import Map from "./components/Map.vue";
</script>

<template>
  <body>
  <Header></Header>
  <div id="grad">
    <h1>Отключения воды и электричества во Владивостоке</h1>
    <p>
      Актуальная информация о плановых и аварийных отключениях коммунальных
      услуг в вашем районе
    </p>
    <div class="container status-container">
      <div class="status-card">
        <h2>Сейчас в городе</h2>
        <div class="summary-now">
          <ul>
            <li>
              <a
                href="http:vl.ru/off/summary/addr?date=now&amp;resourceType=cold_water"
                >нет холодной воды:</a
              >
              столько-то домов
            </li>
            <li>
              <a
                href="http:vl.ru/off/summary/addr?date=now&amp;resourceType=cold_water"
                >нет электричества:</a
              >
              столько-то домов
            </li>
            <li>
              <a
                href="http:vl.ru/off/summary/addr?date=now&amp;resourceType=cold_water"
                >нет горячей воды:</a
              >
              столько-то домов
            </li>
          </ul>
        </div>
      </div>
      <div class="status-card">
        <h2>Ссылка и инфа на фичу?</h2>
      </div>
    </div>
  </div>
  <Search></Search>
  <Map></Map>
  <hr></hr>
  <Footer></Footer>
  </body>
</template>

<style scoped>
#grad {
  background: var(--primary);
  color: white;
  padding: 80px 0;
  text-align: center;
}

div#grad p {
  max-width: 700px;
  margin: 0 auto 40px;
  font-size: 1.2rem;
}

div#grad h1 {
  font-size: 2.5rem;
  margin-bottom: 20px;
  font-weight: 700;
}

.status-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 40px;
  color: black;
  font-size: 1.2rem;
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

hr{
  border: 2px solid var(--primary);
  width: 95%;
  margin: 0px auto;
  border-radius: var(--border-radius);
}
</style>

<script>
import api from "./services/api";

export default {
  data() {
    return {
      products: [],
    };
  },
  async mounted() {
    try {
      const response = await api.getProducts();
      this.products = response.data;
    } catch (error) {
      console.error("Error fetching products:", error);
    }
  },
};
</script>
