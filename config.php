<?php
// 站点配置
define('SITE_URL', 'http://your-domain.com');  // 修改为你的实际域名
define('SITE_NAME', '音乐分享站');

// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'music_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// 存储驱动: 'github' 或 'gitee'
define('STORAGE_DRIVER', 'github');

// GitHub 配置（当 STORAGE_DRIVER = github 时生效）
define('GITHUB_TOKEN', 'your_github_personal_token');
define('GITHUB_OWNER', 'your_username');
define('GITHUB_REPO', 'music-repo');
define('GITHUB_BRANCH', 'main');
define('GITHUB_RAW_PREFIX', 'https://raw.githubusercontent.com/' . GITHUB_OWNER . '/' . GITHUB_REPO . '/' . GITHUB_BRANCH . '/');

// Gitee 配置（当 STORAGE_DRIVER = gitee 时生效）
define('GITEE_TOKEN', 'your_gitee_token');
define('GITEE_OWNER', 'your_username');
define('GITEE_REPO', 'music-repo');
define('GITEE_BRANCH', 'master');
define('GITEE_RAW_PREFIX', 'https://gitee.com/' . GITEE_OWNER . '/' . GITEE_REPO . '/raw/' . GITEE_BRANCH . '/');

// 加速访问配置（针对 GitHub raw 链接）
define('GITHUB_ACCELERATE', true);
define('GITHUB_ACCELERATE_URL', 'https://ghproxy.net/'); // 例如 https://ghproxy.net/ 或 https://raw.fastgit.org/

// 易支付配置
define('EPAY_PID', 1001);               // 商户ID
define('EPAY_KEY', 'your_merchant_key'); // 商户密钥
define('EPAY_API_URL', 'https://your-epay.com/submit.php'); // 易支付网关
define('EPAY_NOTIFY_URL', SITE_URL . '/index.php?action=pay_callback');
define('EPAY_RETURN_URL', SITE_URL . '/index.php?action=subscribe_result');

// 套餐定义
$plans = [
    'month' => ['name' => '月度会员', 'price' => 10, 'days' => 30],
    'year'  => ['name' => '年度会员', 'price' => 100, 'days' => 365]
];

// 免费用户最大上传数
define('FREE_UPLOAD_LIMIT', 5);

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);