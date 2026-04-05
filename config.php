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
define('GITHUB_TOKEN', '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');
define('GITHUB_OWNER', 'xcqm12');
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
define('OD_CLIENT_ID', 'bc772668-a4a5-4f6e-aff2-89b7131f5bb1');
define('OD_CLIENT_SECRET', 'kov8Q~aIi.ONt2dmB4bnUokqZkNHSJRAkUAhGa3E');
define('OD_REFRESH_TOKEN', '1.Aa8A_yR0BhzxDkGbc2d-qCCBJWgmd7ylpG5Pr_KJtxMfW7FKAcuvAA.BQABAwEAAAADAOz_BQD0_0V2b1N0c0FydGlmYWN0cwIAAAAAAJpKFSioD8u9xVCErWoPFw1XQtYmiKPTQ59njye1zB2zFdoTraK4lY9uELAgn8VvTuC6yGGvpyrf8x8hkT6pfA21TqEW1QVbdc_VBoe6qzwUePxxfrB2ZblZtmrjGzhZllom2Zl63K2N3bMaxEc3aQ_V-cgKYGbRjh-fT2i941IeNZUV_4MIxz6BWqdoYzg3NfFfj5T001WBwVASAAOTHzyGNVpMN01PauUdT0KpSuOaaHiR98YGJK7zDdM2LzTiXQX0oEzzzqCRFmfZMcMR5-dPP2tjXrkhdGcCIuhOEeIxmLsyvj0d-RAArUENzNQwnN6jSDaF41nU6O-cBjoR8zXQ2eTShwh8oIlEGJDDuIImxkRxDltBTv-Xv_huhrZ8QGWki_RijOGuR1KK8C4wh6T6xAT9IoxObd1rUy1IyasYbVYj0rYLfqCz8t7Uq-PsuWD7pp35jvTNPdS-73l900_mQzyI3EFiO_9d7SvZnWeYs1kNtWJCb5-Y3uNy50UQibrUqZsgO9zGfUs8HrlFcbvpllwhpvJjioF4DT282k3HVF5YfCDQsvbDchAe7gJinp7hHsNI9Q8S0AiIOwBwRcqvyIT8QxPVWZPMesMAih378aMzC0K7ML5Gr7dsw2ofaoGSyOIBveY4Xz6_Zz-go5QqPkgo1uG2cdnOy4Q4ES38O7u28F_bdLYukZ9wX93PgfgnElrJxGRBryHdfirN2x61NpkVNRf3dXZbp3fcnwMeXFSMIwSWO_PKpHky4ERZHL3PNo1zRBmTvg_7JDf2KoOahB22MrA1hDeq4BONOVKdBdlUA7SKn9sT3OqasdHjkeLCyh2CDUcvAh_SC5U6Bk4EPkmh1OCANbtqQ6az_HQKO2kxOHkISX__gsbUwDCbRyhwy4gsXpQM3-U3oOP782k4cBZjoZFBx7dHIjiND6GrfD0U1tXwhQps2Qipy4QwOiLx3GC1pr_ongiQAv6quOhTtN_0CBC2JHUFPtT9myiT1IA4KdC6z2eDgPx7x2dkyzEYx7Q_ILM0184KoB1j7HNurRokKsF4_lBgNaRnlPrLJbxemOwIQ3FELihvckrfodcmKmxqHzk7mrjGIwEHdi231j4ec70RD7SLQG28MBp-IBYZ9V8B_-fr0ozgnfmgYc9ju7v1kQtS6FOzbx-o6MKHGv9XXJfOhxpdAc8lrUicm3_AWFeevFZBptUqwv1Gzz2QatXMikya9JdCoA76M9Bzo04WhngCWihsGmz7tNiGso2csKuZsyXsk2TzHP2x9UYyKhhx64QjYPz8qdSQC6vW0DC2McWhVaRBqFEGhyQuB7xFjS6sRnBWayZuyNv7wQJhV9VKGbq3vy0vLhHFKDnS61cIO-XLdam4bUDkhYQnUl2qLWtxDl0qrqF89KG8WNdsD68YMWaveB17aVE23ajKwk4UemB46kGzZ0DQtXWlbpuxT4dxy0WpP5eO602Y9iGERsG63jBjPMxGckzN0f0lUiDugWjyQhpCLlaHTKnvSEis8KJpmoedrcibVIai3b_D62QhQcYYX3b2k53UwfE');
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