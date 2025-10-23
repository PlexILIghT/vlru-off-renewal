<template>
  <footer>
    <div class="container">
      <div class="footer-grid">
        <div class="footer-column">
          <h3>Разделы</h3>
          <ul class="footer-links">
            <li><a href="#">Главная</a></li>
            <li><a href="#">Показания счетчикоыв</a></li>
            <li><a href="#">Уведомления об отключениях на мобилььный</a></li>
            <li><a href="#">Управляющие организации Владивостока</a></li>
            <li><a href="#">Посты</a></li>
          </ul>
        </div>

        <div class="footer-column">
          <h3>Сайты Владивостока</h3>
          <ul class="footer-links">
            <li><a href="#">Авто</a></li>
            <li><a href="#">Кино</a></li>
            <li><a href="#">Недвижимость</a></li>
            <li><a href="#">Новости</a></li>
            <li><a href="#">объявления</a></li>
            <li><a href="#">Работа</a></li>
            <li><a href="#">Справочник компаний</a></li>
          </ul>
        </div>

        <div class="footer-column">
          <h3>Информация</h3>
          <ul class="footer-links">
            <li><a href="#">Вакансии</a></li>
            <li><a href="#">Контакты</a></li>
            <li>
              По вопросам, предложениям или ошибкам пишите на почту:
              <a id="mail" href="https://www.vl.ru/off/#">off@vl.ru</a>
            </li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>
          &copy; ООО «Фарпост», 2003 — 2025<br />
          При любом использовании материалов ссылка на
          <a href="https://www.vl.ru/">VL.ru</a> обязательна.<br />
          Цитирование в Интернете возможно только при наличии гиперссылки.<br />
          Все права защищены.
        </p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
footer {
  padding: 30px 0 30px;
}

.footer-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 40px;
  margin-bottom: 40px;
}

.footer-column h3 {
  font-size: 1.2rem;
  margin-bottom: 20px;
  font-weight: 600;
}

.footer-links {
  list-style: none;
}

.footer-links li {
  margin-bottom: 10px;
}

.footer-links a {
  text-decoration: none;
  color: black;
}

.footer-bottom {
  text-align: center;
  padding-top: 30px;
  color: #94a3b8;
  font-size: 0.9rem;
}

.footer-bottom a {
  color: #94a3b8;
}

#mail {
  text-decoration: underline;
}
</style>
