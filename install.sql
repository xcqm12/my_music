-- 数据库表结构，导入到你的 MySQL 数据库
CREATE DATABASE IF NOT EXISTS `music_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `music_system`;

-- 用户表
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `upload_count` int(11) DEFAULT 0,
  `subscribe_expire` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 音乐表
CREATE TABLE `musics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `file_name` varchar(200) NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `size` int(11) DEFAULT 0,
  `duration` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 订单表（支付记录）
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan` varchar(20) NOT NULL COMMENT 'month,year',
  `amount` decimal(10,2) NOT NULL,
  `status` tinyint(4) DEFAULT 0 COMMENT '0未支付,1已支付',
  `pay_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;