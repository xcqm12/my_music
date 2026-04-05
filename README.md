# 樱花音乐站 - 在线音乐分享系统

一个基于 PHP + MySQL 的音乐分享平台，支持用户上传/管理音乐、会员订阅、多种云存储驱动（GitHub、Gitee、OneDrive）、易支付自动开通会员，并提供完整的后台管理系统。

## 功能特性

- **用户系统**：注册、登录、个人音乐管理、上传数量限制（免费用户限制5首，订阅用户不限）
- **会员订阅**：支付宝/微信支付（通过易支付），购买月度/年度会员，自动延长订阅有效期
- **音乐管理**：支持 mp3/wav/ogg/m4a 格式上传，自动存储到云端（GitHub/Gitee/OneDrive），在线播放列表
- **流式播放**：OneDrive 文件通过临时下载链接播放，节省流量
- **后台管理**：仪表盘统计、用户管理（编辑上传次数/会员到期/管理员权限）、音乐管理、订单管理（补单）、系统设置（站点名称/上传限制/存储驱动/套餐价格/支付配置）、邮件配置（SMTP）
- **邮件通知**：订阅成功后自动发送邮件通知（支持 SMTP 或 PHP mail 函数）
- **多存储驱动**：可无缝切换 GitHub、Gitee 或 OneDrive 作为文件存储后端
- **易支付集成**：动态配置商户ID、密钥、接口地址，支持异步回调签名验证

## 环境要求

- PHP 7.4+（推荐 8.0）
- MySQL 5.7+
- PHP 扩展：PDO、curl、json、openssl、fileinfo
- 可选：Composer（用于安装 PHPMailer）

## 安装步骤

### 1. 下载源码

将全部文件上传至您的网站目录（如 `/var/www/html`）。

### 2. 创建数据库

在 MySQL 中创建一个数据库，然后导入 `install.sql`（需自行根据代码结构编写建表语句，或使用以下示例 SQL）。

**示例表结构：**

```sql
-- 用户表
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `upload_count` int(11) DEFAULT 0,
  `subscribe_expire` datetime DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
);

-- 音乐表
CREATE TABLE `musics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `file_name` varchar(200) NOT NULL,
  `file_url` text,
  `storage_driver` varchar(20) DEFAULT NULL,
  `storage_path` varchar(500) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
);

-- 订单表
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` tinyint(1) DEFAULT 0,
  `pay_time` datetime DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`)
);

-- 系统设置表
CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL,
  `value` text,
  PRIMARY KEY (`key`)
);
3. 修改数据库配置
编辑 config.php，修改数据库连接信息：

php
define('DB_HOST', 'localhost');
define('DB_NAME', '你的数据库名');
define('DB_USER', '数据库用户名');
define('DB_PASS', '数据库密码');
4. 设置管理员账号
方式一：在数据库中手动插入一条 is_admin=1 的用户记录，密码使用 password_hash() 加密。

方式二：注册一个普通用户后，在后台 用户管理 中编辑该用户，勾选“管理员”并保存。

推荐方式一：执行 SQL（密码为 admin123 的哈希值示例，请自行替换）：

sql
INSERT INTO `users` (`username`, `password`, `email`, `is_admin`) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere', 'admin@example.com', 1);
5. 配置存储驱动
在 config.php 中选择并配置一种存储驱动：

使用 GitHub
php
define('STORAGE_DRIVER', 'github');
define('GITHUB_TOKEN', '你的Personal Access Token');
define('GITHUB_OWNER', '你的GitHub用户名');
define('GITHUB_REPO', '存储音乐文件的仓库名');
define('GITHUB_BRANCH', 'main');
使用 Gitee（码云）
php
define('STORAGE_DRIVER', 'gitee');
define('GITEE_TOKEN', '你的Gitee私人令牌');
define('GITEE_OWNER', '你的用户名');
define('GITEE_REPO', '仓库名');
define('GITEE_BRANCH', 'master');
使用 OneDrive（Microsoft Graph）
php
define('STORAGE_DRIVER', 'onedrive');
define('OD_CLIENT_ID', '你的应用ID');
define('OD_CLIENT_SECRET', '你的应用密钥');
define('OD_REFRESH_TOKEN', '刷新令牌（首次需获取）');
define('OD_DRIVE_ID', ''); // 可选，留空自动获取
获取 OneDrive Refresh Token：
访问项目中的 refresh_onedrive.php 文件（需配置好域名），按页面提示操作，将获得的 refresh_token 填入 OD_REFRESH_TOKEN。

6. 配置邮件服务（可选）
在后台 邮件设置 中填写 SMTP 信息，或直接在 config.php 中定义默认值：

php
define('MAIL_HOST', 'smtp.qq.com');
define('MAIL_PORT', 465);
define('MAIL_USERNAME', 'your@email.com');
define('MAIL_PASSWORD', 'your_password_or_auth_code');
define('MAIL_ENCRYPTION', 'ssl');
define('MAIL_FROM_ADDRESS', 'your@email.com');
define('MAIL_FROM_NAME', SITE_NAME);
若需使用 PHPMailer 库，请在项目根目录执行：

bash
composer require phpmailer/phpmailer
7. 配置支付接口（易支付）
在 config.php 中填写默认值（后台也可动态修改）：

php
define('EPAY_PID', '你的商户ID');
define('EPAY_KEY', '你的商户密钥');
define('EPAY_API_URL', 'https://支付网关/submit.php');
define('EPAY_NOTIFY_URL', SITE_URL . '/index.php?action=pay_callback');
define('EPAY_RETURN_URL', SITE_URL . '/index.php?action=subscribe_result');
8. 设置网站域名和名称
编辑 config.php：

php
define('SITE_URL', 'https://你的域名');
define('SITE_NAME', '樱花音乐站');
9. 目录权限
确保以下目录可写（用于临时文件或日志，视具体存储驱动而定）：

uploads/（如果存在）

logs/（如有错误日志）

使用说明
前台
首页：展示所有用户上传的音乐列表，支持在线试听。

注册/登录：新用户注册后自动登录。

上传音乐：登录后点击“上传音乐”，填写标题并选择本地音乐文件（支持 mp3/wav/ogg/m4a）。免费用户最多上传5首，会员无限制。

我的音乐：查看自己上传的音乐，可删除。

订阅会员：选择月度或年度套餐，跳转易支付完成付款，成功后自动延长会员有效期并发送邮件通知。

后台管理
访问 admin.php，使用管理员账号登录。

仪表盘：查看网站总用户数、音乐数、订单数、总收入、最近订单。

用户管理：查看所有用户，编辑上传配额、会员到期时间、管理员权限，删除用户（连带删除其所有音乐和订单）。

音乐管理：浏览所有音乐，可删除任意音乐（并减少对应用户的上传计数）。

订单管理：查看订单列表，可手动修改订单状态为“已支付”，系统会自动增加用户订阅时长。

系统设置：

常规：站点名称、免费用户上传限制、默认存储驱动。

套餐：月度/年度套餐的名称、价格、有效天数。

易支付：商户ID、密钥、API地址、通知/返回URL。

邮件设置：配置 SMTP 或 PHP mail，并可发送测试邮件。

注意事项
安全性：

部署后请删除 refresh_onedrive.php 或限制其访问，防止敏感信息泄露。

确保 config.php 中的数据库密码、API密钥等不外泄。

建议开启 HTTPS 以保证支付回调和数据传输安全。

存储限制：GitHub/Gitee 仓库单个文件通常限制 100MB，不适合上传超大音乐文件；OneDrive 分片上传支持大文件。

回调地址：易支付回调必须能被外网访问（不可本地测试），确保 index.php?action=pay_callback 正确响应。

邮件发送：如果使用 SMTP，建议使用授权码而非邮箱密码；如果使用 PHP mail 函数，请确保服务器已配置 sendmail。

分片上传：OneDrive 驱动默认使用 10MB 分片，可根据网络调整 chunkSize。

常见问题
Q：上传失败，提示“GitHub upload failed”？
A：检查 GitHub Token 是否有 repo 权限，仓库是否存在且分支正确。

Q：支付回调后会员没有生效？
A：检查 index.php 中 pay_callback 签名验证逻辑，确保 EPAY_KEY 与商户平台一致；查看数据库 orders 表 status 是否更新为1。

Q：邮件发送失败
A：若使用 SMTP，请测试 SMTP 服务器地址、端口、加密方式和密码是否正确；若使用 PHP mail，请检查服务器 mail 函数是否可用。

Q：OneDrive 上传大文件超时
A：可减小分片大小（例如 5MB），并增加 PHP max_execution_time 和 memory_limit。

文件结构
text
├── admin.php                 # 后台入口
├── config.php                # 核心配置（数据库、存储、支付等）
├── DB.php                    # 数据库单例类
├── functions.php             # 公共函数（邮件、签名、订阅等）
├── index.php                 # 前台主入口
├── Payment.php               # 易支付集成类
├── StorageFactory.php        # 存储驱动工厂
├── GitHubStorage.php         # GitHub 存储实现
├── GiteeStorage.php          # Gitee 存储实现
├── OneDriveStorage.php       # OneDrive 存储实现
├── refresh_onedrive.php      # 辅助获取 OneDrive refresh_token
├── templates/                # 前端模板文件（HTML+PHP）
│   ├── admin/                # 后台模板
│   │   ├── dashboard.php
│   │   ├── users.php
│   │   ├── user_edit.php
│   │   ├── musics.php
│   │   ├── orders.php
│   │   ├── order_edit.php
│   │   ├── settings.php
│   │   ├── mail_settings.php
│   │   └── login.php
│   ├── home.php
│   ├── login.php
│   ├── register.php
│   ├── upload_form.php
│   ├── my_music.php
│   └── subscribe.php
└── README.md                 # 本文件
贡献与许可
本项目为开源代码，仅供学习交流使用。请勿用于非法或商业盈利。使用前请遵守相关云存储及支付接口的服务条款。

