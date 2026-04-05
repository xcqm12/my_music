-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2026-04-05 16:22:09
-- 服务器版本： 5.7.44-log
-- PHP 版本： 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `music_qyffjqw_cn`
--
CREATE DATABASE IF NOT EXISTS `music_qyffjqw_cn` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `music_qyffjqw_cn`;

-- --------------------------------------------------------

--
-- 表的结构 `musics`
--

CREATE TABLE `musics` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `file_name` varchar(200) NOT NULL,
  `file_url` varchar(500) DEFAULT NULL,
  `storage_driver` varchar(20) NOT NULL,
  `storage_path` varchar(500) NOT NULL,
  `size` int(11) DEFAULT '0',
  `duration` int(11) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `musics`
--

INSERT INTO `musics` (`id`, `user_id`, `title`, `file_name`, `file_url`, `storage_driver`, `storage_path`, `size`, `duration`, `created_at`) VALUES
(1, 1, '乱世书（清朝DJ版）', '69d2196db2f00.wav', '', 'onedrive', 'music/69d2196db2f00.wav', 43676200, 0, '2026-04-05 16:13:09');

-- --------------------------------------------------------

--
-- 表的结构 `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_no` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` tinyint(4) DEFAULT '0',
  `pay_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `settings`
--

CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL,
  `value` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `settings`
--

INSERT INTO `settings` (`key`, `value`) VALUES
('epay_api_url', 'https://pay.al0.top/submit.php'),
('epay_key', 'MqU2YeS2VBbLy3kX8plD0tYCW9ecrJ'),
('epay_notify_url', 'https://music.qyffjqw.cnhttps://music.qyffjqw.cn/index.php?action=pay_callback'),
('epay_pid', '0968519204'),
('epay_return_url', 'https://music.qyffjqw.cnhttps://music.qyffjqw.cn/index.php?action=subscribe_result'),
('free_upload_limit', '5'),
('month_plan_days', '30'),
('month_plan_name', '月度会员'),
('month_plan_price', '1'),
('site_name', '樱花音乐站'),
('storage_driver', 'onedrive'),
('year_plan_days', '365'),
('year_plan_name', '年度会员'),
('year_plan_price', '5');

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `upload_count` int(11) DEFAULT '0',
  `subscribe_expire` datetime DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `upload_count`, `subscribe_expire`, `is_admin`, `created_at`) VALUES
(1, 'admin', '$2y$10$TRXfsuRnplx373Xze5Nyq.Y85pI6ob5Gjj0yyv4zizBqAqYiDnWLS', 'qlm@qlm.org.cn', 1, NULL, 1, '2026-04-05 15:48:48');

--
-- 转储表的索引
--

--
-- 表的索引 `musics`
--
ALTER TABLE `musics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- 表的索引 `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_no` (`order_no`),
  ADD KEY `user_id` (`user_id`);

--
-- 表的索引 `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `musics`
--
ALTER TABLE `musics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
