-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 06 2026 г., 07:21
-- Версия сервера: 5.7.39-log
-- Версия PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `manga_shop`
--

-- --------------------------------------------------------

--
-- Структура таблицы `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `title`, `description`) VALUES
(1, 'Сёнэн', 'Манга для любителей приключений, битв и развития героя'),
(2, 'Сэйнэн', 'Более взрослая манга с серьёзными сюжетами'),
(3, 'Романтика', 'Истории любви, школы и повседневности'),
(4, 'Фэнтези', 'Магия, другие миры и приключения'),
(5, 'Хоррор', 'Мрачная и пугающая манга'),
(6, 'Тестик', 'Чтобы всё работало'),
(7, 'Драма', 'Эмоциональные истории с сильным сюжетом'),
(8, 'Комедия', 'Лёгкая и смешная манга'),
(9, 'Повседневность', 'Истории из обычной жизни персонажей'),
(10, 'Спорт', 'Манга о соревнованиях, командах и личном росте'),
(11, 'Детектив', 'Расследования, тайны и психологические загадки'),
(12, 'Киберпанк', 'Будущее, технологии и мрачные города'),
(13, 'Исекай', 'Попадание героя в другой мир'),
(14, 'Приключения', 'Путешествия, опасности и новые открытия');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Новый',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `status`, `created_at`) VALUES
(1, 2, '2497.00', 'Новый', '2026-06-06 03:41:18'),
(2, 2, '849.00', 'Отправлен', '2026-06-06 03:41:52'),
(3, 3, '1299.00', 'Новый', '2026-06-06 04:09:14');

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `title`, `price`, `quantity`) VALUES
(1, 1, 1, 'Наруто. Том 1', '799.00', 1),
(2, 1, 5, 'Магическая битва. Том 1', '849.00', 2),
(3, 2, 5, 'Магическая битва. Том 1', '849.00', 1),
(4, 3, 3, 'Берсерк. Том 1', '1299.00', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `author` varchar(150) DEFAULT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `category_id`, `title`, `author`, `description`, `price`, `image`, `stock`, `created_at`) VALUES
(1, 1, 'Наруто. Том 1', 'Масаси Кисимото', 'Начало истории юного ниндзя Наруто Узумаки.', '799.00', 'naruto-1.jpg', 9, '2026-06-06 03:12:45'),
(2, 1, 'One Piece. Том 1', 'Эйитиро Ода', 'Луффи отправляется на поиски легендарного сокровища.', '899.00', 'one-piece-1.jpg', 8, '2026-06-06 03:12:45'),
(3, 2, 'Берсерк. Том 1', 'Кэнтаро Миура', 'Тёмное фэнтези о воине по имени Гатс.', '1299.00', 'berserk-1.jpg', 4, '2026-06-06 03:12:45'),
(4, 3, 'Твоё имя. Том 1', 'Макото Синкай', 'Романтическая история о загадочной связи двух подростков.', '699.00', 'your-name-1.jpg', 12, '2026-06-06 03:12:45'),
(5, 4, 'Магическая битва. Том 1', 'Гэгэ Акутами', 'Мир проклятий, магии и опасных сражений.', '849.00', 'jujutsu-1.jpg', 6, '2026-06-06 03:12:45'),
(6, 6, 'Тестовый', 'КЦФ', 'Тест', '10000.00', '', 0, '2026-06-06 03:47:06'),
(7, 1, 'Chainsaw Man. Том 1', 'Тацуки Фудзимото', 'Дэндзи охотится на демонов и мечтает о нормальной жизни.', '899.00', '', 14, '2026-06-06 04:05:03'),
(8, 1, 'Моя геройская академия. Том 1', 'Кохэй Хорикоси', 'Мир, где почти у всех есть сверхспособности.', '799.00', '', 11, '2026-06-06 04:05:03'),
(9, 1, 'Клинок, рассекающий демонов. Том 1', 'Коёхару Готогэ', 'Тандзиро становится охотником на демонов.', '849.00', '', 10, '2026-06-06 04:05:03'),
(10, 2, 'Токийский гуль. Том 1', 'Суи Исида', 'Кэн Канэки оказывается между миром людей и гулей.', '999.00', '', 9, '2026-06-06 04:05:03'),
(11, 2, 'Монстр. Том 1', 'Наоки Урасава', 'Психологический триллер о враче и опасном пациенте.', '1199.00', '', 5, '2026-06-06 04:05:03'),
(12, 3, 'Хоримия. Том 1', 'HERO', 'Романтическая школьная история о двух старшеклассниках.', '699.00', '', 13, '2026-06-06 04:05:03'),
(13, 3, 'Форма голоса. Том 1', 'Ёситоки Оима', 'История вины, взросления и попытки всё исправить.', '749.00', '', 8, '2026-06-06 04:05:03'),
(14, 4, 'Re:Zero. Том 1', 'Таппэй Нагацуки', 'Субару попадает в другой мир и получает странную способность.', '899.00', '', 7, '2026-06-06 04:05:03'),
(15, 4, 'Поднятие уровня в одиночку. Том 1', 'Chugong', 'Слабейший охотник получает шанс стать сильнейшим.', '1099.00', '', 12, '2026-06-06 04:05:03'),
(16, 5, 'Узумаки. Том 1', 'Дзюндзи Ито', 'Город охвачен пугающей одержимостью спиралями.', '1299.00', '', 4, '2026-06-06 04:05:03'),
(17, 6, 'Тетрадь смерти. Том 1', 'Цугуми Оба', 'Лайт Ягами получает тетрадь, способную убивать людей.', '899.00', '', 10, '2026-06-06 04:05:03'),
(18, 7, 'Гинтама. Том 1', 'Хидэаки Сорати', 'Самурайская комедия с абсурдным юмором.', '799.00', '', 6, '2026-06-06 04:05:03'),
(19, 8, 'Мартовский лев. Том 1', 'Тика Умино', 'История молодого игрока в сёги и его взросления.', '849.00', '', 7, '2026-06-06 04:05:03'),
(20, 9, 'Баскетбол Куроко. Том 1', 'Тадатоси Фудзимаки', 'Спортивная манга о школьной баскетбольной команде.', '749.00', '', 9, '2026-06-06 04:05:03'),
(21, 10, 'Плутон. Том 1', 'Наоки Урасава', 'Детективная история в мире роботов и людей.', '1199.00', '', 6, '2026-06-06 04:05:03'),
(22, 11, 'Призрак в доспехах. Том 1', 'Масамунэ Сиро', 'Киберпанк о будущем, киборгах и цифровом сознании.', '1399.00', '', 5, '2026-06-06 04:05:03'),
(23, 12, 'О моём перерождении в слизь. Том 1', 'Фьюз', 'Обычный человек перерождается в другом мире в виде слизи.', '899.00', '', 10, '2026-06-06 04:05:03'),
(24, 13, 'Made in Abyss. Том 1', 'Акихито Цукуси', 'Девочка отправляется исследовать загадочную бездну.', '999.00', '', 6, '2026-06-06 04:05:03');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Администратор', 'admin@mail.ru', '$2y$10$MUzYJbMheAWoZn7mYCyAbOk90ryrYDr82.9xKPWoh4zwLSbN1LxaO', 'admin', '2026-06-06 03:18:51'),
(2, 'Иван', 'user@mail.ru', '$2y$10$GR.qrlV6y3s.Ae5sz0oUWunWipBCKnVcgnXTE.1Qdr6w3Q0uEMpF6', 'user', '2026-06-06 03:25:37'),
(3, 'test', 'test@mail.ru', '$2y$10$/knu3ikSQrggCbA/NuYVWelmm3hWfXVU4COCiOOcYDdDKIc.N2Zzq', 'user', '2026-06-06 04:08:35');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cart_user` (`user_id`),
  ADD KEY `fk_cart_product` (`product_id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_user` (`user_id`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_order` (`order_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_products_category` (`category_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
