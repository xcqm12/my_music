<?php
// config.php - 主配置文件
define('SITE_URL', 'https://music.qyffjqw.cn');  // 修改为你的实际域名
define('SITE_NAME', '樱花音乐站');

// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'music_qyffjqw_cn');
define('DB_USER', 'music_qyffjqw_cn');
define('DB_PASS', 'ikBR5rcWxTSeDD6m');

// 存储驱动: 'github', 'gitee', 'onedrive'
define('STORAGE_DRIVER', 'onedrive');

// GitHub 配置
define('GITHUB_TOKEN', 'your_github_token');
define('GITHUB_OWNER', 'your_username');
define('GITHUB_REPO', 'music-repo');
define('GITHUB_BRANCH', 'main');

// 邮件配置默认值（实际从数据库读取）
define('MAIL_DRIVER', 'smtp'); // smtp 或 mail
define('MAIL_HOST', 'smtp.qq.com');
define('MAIL_PORT', 465);
define('MAIL_USERNAME', '');
define('MAIL_PASSWORD', '');
define('MAIL_ENCRYPTION', 'ssl');
define('MAIL_FROM_ADDRESS', '');
define('MAIL_FROM_NAME', SITE_NAME);

// Gitee 配置
define('GITEE_TOKEN', 'your_gitee_token');
define('GITEE_OWNER', 'your_username');
define('GITEE_REPO', 'music-repo');
define('GITEE_BRANCH', 'master');

// OneDrive 配置 (Microsoft Graph)
define('OD_CLIENT_ID', 'your_client_id');
define('OD_CLIENT_SECRET', 'your_client_secret');
define('OD_REFRESH_TOKEN', 'your_refresh_token');
define('OD_DRIVE_ID', ''); // 可选，留空则使用默认drive

// GitHub 加速代理列表 (多个站点防止失效)
define('GITHUB_ACCELERATE', true);
$GITHUB_PROXIES = [
    'https://gh-proxy.org/',
    'https://hk.gh-proxy.org/',
    'https://cdn.gh-proxy.org/',
    'https://edgeone.gh-proxy.org/'
];

// 易支付配置
define('EPAY_PID', '0968519204');
define('EPAY_KEY', 'MqU2YeS2VBbLy3kX8plD0tYCW9ecrJ');
define('EPAY_API_URL', 'https://pay.al0.top/submit.php');
define('EPAY_NOTIFY_URL', SITE_URL . 'https://music.qyffjqw.cn/index.php?action=pay_callback');
define('EPAY_RETURN_URL', SITE_URL . 'https://music.qyffjqw.cn/index.php?action=subscribe_result');

// 套餐定义
$plans = [
    'month' => ['name' => '月度会员', 'price' => 1, 'days' => 30],
    'year'  => ['name' => '年度会员', 'price' => 5, 'days' => 365]
];

define('FREE_UPLOAD_LIMIT', 5);

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);