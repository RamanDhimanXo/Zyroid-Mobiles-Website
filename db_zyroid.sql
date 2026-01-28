
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `tb_admin` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `con_pass` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tb_admin` (`id`, `fullname`, `email`, `phone`, `pass`, `con_pass`) VALUES
(34, 'admin', 'admin@gmail.com', '8437693044', '827ccb0eea8a706c4c34a16891f84e7b', '81dc9bdb52d04dc20036dbd8313ed055');

CREATE TABLE `tb_cart` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `product_id` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` varchar(255) NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `quantity` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tb_cart` (`id`, `email`, `product_id`, `product_name`, `product_price`, `product_image`, `quantity`) VALUES
(37, 'demo@gmail.com', '29', 'demoproduct', '99', 'Artboard 6.png', '0');

CREATE TABLE `tb_categories` (
  `cat_id` int(11) NOT NULL,
  `cat_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tb_categories` (`cat_id`, `cat_name`) VALUES
(1, 'IPHONES'),
(2, 'ANDROID'),
(3, 'GAMING'),
(4, 'ACCESSORIES');

CREATE TABLE `tb_comments` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tb_notifications` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `source_id` varchar(255) NOT NULL,
  `is_read` int(11) NOT NULL COMMENT '1 read = 2 dont_read',
  `created_at` date NOT NULL,
  `message` varchar(255) NOT NULL,
  `Comments` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tb_notifications` (`id`, `user_id`, `type`, `source_id`, `is_read`, `created_at`, `message`, `Comments`, `user_email`) VALUES
(18, '9', 'comment', '29', 1, '2026-01-05', 'New comment from demo', '', 'demo@gmail.com'),
(19, '', 'product', '31', 1, '2026-01-06', 'New product \'ewrgerg\' has been added.', '', ''),
(20, '', 'product', '31', 1, '2026-01-08', 'Product \'ewrgerg\' has been updated.', '', ''),
(21, '', 'product', '30', 1, '2026-01-08', 'Product \'ggggg\' has been updated.', '', ''),
(22, '', 'product', '30', 1, '2026-01-08', 'Product \'ggggg\' has been updated.', '', ''),
(23, '', 'product', '30', 1, '2026-01-08', 'Product \'ggggg\' has been updated.', '', ''),
(24, '', 'product', '32', 1, '2026-01-08', 'New product \'dfvwefewd\' has been added.', '', '');

CREATE TABLE `tb_orders` (
  `id` int(11) NOT NULL,
  `Product` varchar(255) NOT NULL,
  `Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` int(11) NOT NULL COMMENT '1 Active = deactivate ',
  `amount` varchar(255) NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `shipping_name` varchar(255) DEFAULT NULL,
  `shipping_phone` varchar(20) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tb_products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `product_price` varchar(255) NOT NULL,
  `add_product_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `stocks` varchar(255) NOT NULL,
  `device_storage` enum('256GB','512GB','1TB') NOT NULL,
  `product_color` enum('black','blue','white') NOT NULL,
  `product_des` varchar(255) NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `en_des_pro` int(11) NOT NULL COMMENT '1 Active = 2 deactivate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tb_products` (`id`, `product_name`, `category`, `product_price`, `add_product_date`, `stocks`, `device_storage`, `product_color`, `product_des`, `product_image`, `en_des_pro`) VALUES
(29, 'demoproduct', 'ANDROID', '99', '2026-01-05 15:00:12', '12', '1TB', 'black', 'ferhtrhtrhrthtg', 'Artboard 6.png', 1),
(30, 'ggggg', 'IPHONES', '12', '2026-01-08 04:41:50', '12', '256GB', 'black', '123456', 'Artboard 2.png', 1),
(31, 'ewrgerg', 'GAMING', '12345', '2026-01-08 04:41:06', '123', '512GB', 'black', 'qwfergerge', 'CRUD.png', 1),
(32, 'dfvwefewd', 'ACCESSORIES', '9', '2026-01-08 04:47:41', '123', '512GB', 'white', 'dddd', 'what.png', 1);

CREATE TABLE `tb_users` (
  `id` int(11) NOT NULL,
  `user` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `con_pass` varchar(255) NOT NULL,
  `registor_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `orders` varchar(255) NOT NULL,
  `total spent` varchar(255) NOT NULL,
  `user_images` varchar(255) NOT NULL,
  `user_id` varchar(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `user_status` int(11) NOT NULL COMMENT '1 Active = 2 deactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tb_users` (`id`, `user`, `email`, `phone`, `pass`, `con_pass`, `registor_time`, `orders`, `total spent`, `user_images`, `user_id`, `token`, `user_status`) VALUES
(5, 'vandna', 'v@gmail.com', '9876543210', 'cd2896c9fa231203e9308231cf62aad1', '', '2026-01-03 08:41:50', '', '', '', '', '', 1),
(8, 'Raman', 'Raman@gmail.com', '1234567890', 'e10adc3949ba59abbe56e057f20f883e', 'e10adc3949ba59abbe56e057f20f883e', '2026-01-03 11:09:16', '', '', '', '', '3cc12fe62ecdba81b66769efcb878347', 1),
(9, 'demo', 'demo@gmail.com', '0987654321', '$2y$10$vNKnAxDM89knGZtUy.dfWePy.AIXbV7CnoVLB1xYnfj9vVhXJ8eyy', '', '2026-01-06 10:13:45', '', '', '', '', '5309ea7d9a31e5200f80516a53b16ece', 0);

CREATE TABLE `tb_wishlist` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `wishlist` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `tb_admin`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tb_cart`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tb_categories`
  ADD PRIMARY KEY (`cat_id`);

ALTER TABLE `tb_comments`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tb_notifications`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tb_orders`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tb_products`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tb_wishlist`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tb_users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tb_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

ALTER TABLE `tb_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

ALTER TABLE `tb_categories`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `tb_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `tb_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

ALTER TABLE `tb_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

ALTER TABLE `tb_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

ALTER TABLE `tb_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `tb_wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;
