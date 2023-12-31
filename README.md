Для запуска программы необходимо:

1. Установить и запустить MySQL Server,
2. В файле comments-api/public/index.php заменить параметры $username, $password, указав пользователя и пароль для доступа к MySQL Sever, введенные при установке,
3. Зайти в папку comments-api и запустить серверную часть приложения, введя в терминале команду: php -S localhost:8888 -t public,
4. Зайти в папку comments-front и загрузить все необходимые зависимости для фронтовой части приложения, введя в терминале команду: npm install,
5. Запустить фронтовую часть приложения на localhost:3000, введя в терминале команду: npm start и выбрав порт 3000,
6. Открыть в браузере страницу localhost по тому порту, который был указан после запуска фронтовой части приложения.

Описание функционала, которое выполняет приложение:

В качестве тестового задания предлагается создать продукт
«Комментарии к изображению».

Технические ограничения
1. Язык реализации — PHP (версия 7 или 8).
Желательно использовать для построения API микрофреймворк Slim.
2. Используемая СУБД — MySQL (или MariaDB).
3. Желательно реализовать функционал без перезагрузки страницы,
используя асинхронные запросы к серверу (fetch или любая
библиотека на ваш выбор).
4. Желательно использовать при разработке фронтенда фреймворк
Vue. Дизайн страницы на ваше усмотрение.

Описание

Есть страница с каким-либо контентом. Для примера пусть это будет
изображение. Под изображением располагается блок с
комментариями.
Он представляет собой:
— форму для добавления нового комментария с полями «Имя»,
«Текст комментария», «Капча» и с кнопкой «Добавить
комментарий».
— список добавленных ранее комментариев, упорядоченный по дате
добавления (сверху — последний). Каждый комментарий содержит
имя добавившего комментарий, дату и время добавления
комментария, текст комментария и кнопку удаления комментария.

Базовый функционал страницы
1. При нажатии на кнопку добавления комментария, если капча
пройдена успешно, новый комментарий добавляется в базу и
появляется на странице. При неверном вводе капчи появляется
сообщение о неверном вводе капчи.
2. При нажатии на кнопку удаления соответствующий комментарий
удаляется из базы и со страницы.
