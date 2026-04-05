<?php
// functions.php - 公共函数
require_once 'config.php';

function getProxyUrl($rawUrl) {
    if (!GITHUB_ACCELERATE) return $rawUrl;
    if (strpos($rawUrl, 'raw.githubusercontent.com') !== false) {
        global $GITHUB_PROXIES;
        $proxy = $GITHUB_PROXIES[array_rand($GITHUB_PROXIES)];
        $path = parse_url($rawUrl, PHP_URL_PATH);
        return rtrim($proxy, '/') . $path;
    }
    return $rawUrl;
}

function isSubscribed($user) {
    if (!$user) return false;
    $expire = $user['subscribe_expire'] ?? null;
    return $expire && strtotime($expire) > time();
}

function getUserById($id) {
    $pdo = DB::getInstance()->getPDO();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateUserSubscribe($userId, $days) {
    $pdo = DB::getInstance()->getPDO();
    $user = getUserById($userId);
    $currentExpire = $user['subscribe_expire'];
    if ($currentExpire && strtotime($currentExpire) > time()) {
        $newExpire = date('Y-m-d H:i:s', strtotime($currentExpire . " + $days days"));
    } else {
        $newExpire = date('Y-m-d H:i:s', time() + $days * 86400);
    }
    $stmt = $pdo->prepare("UPDATE users SET subscribe_expire = ? WHERE id = ?");
    $stmt->execute([$newExpire, $userId]);
}

function getPlayUrl($music) {
    $driver = $music['storage_driver'];
    $ref = $music['storage_path'];
    if ($driver == 'github' || $driver == 'gitee') {
        return getProxyUrl($music['file_url']);
    } elseif ($driver == 'onedrive') {
        // 通过 stream action 动态获取
        return SITE_URL . '/index.php?action=stream&id=' . $music['id'];
    }
    return $music['file_url'];
}

// ------------------ 后台辅助函数 ------------------
function isAdmin($userId) {
    $pdo = DB::getInstance()->getPDO();
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    return $res && $res['is_admin'] == 1;
}

function getSetting($key, $default = null) {
    $pdo = DB::getInstance()->getPDO();
    $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return $row['value'];
    }
    // 从 config.php 的默认值中回退
    global $plans;
    switch ($key) {
        case 'site_name': return defined('SITE_NAME') ? SITE_NAME : '樱花音乐站';
        case 'free_upload_limit': return defined('FREE_UPLOAD_LIMIT') ? FREE_UPLOAD_LIMIT : 5;
        case 'storage_driver': return defined('STORAGE_DRIVER') ? STORAGE_DRIVER : 'onedrive';
        case 'month_plan_name': return $plans['month']['name'] ?? '月度会员';
        case 'month_plan_price': return $plans['month']['price'] ?? 1;
        case 'month_plan_days': return $plans['month']['days'] ?? 30;
        case 'year_plan_name': return $plans['year']['name'] ?? '年度会员';
        case 'year_plan_price': return $plans['year']['price'] ?? 5;
        case 'year_plan_days': return $plans['year']['days'] ?? 365;
        default: return $default;
    }
}

function updateSetting($key, $value) {
    $pdo = DB::getInstance()->getPDO();
    $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
    $stmt->execute([$key, $value, $value]);
}

// ------------------ 邮件发送功能 ------------------
##require_once __DIR__ . '/Mailer.php';##

/**
 * 发送邮件
 * @param string $to 收件人
 * @param string $subject 主题
 * @param string $body 内容(HTML)
 * @return bool|string 成功返回true，失败返回错误信息
 */
function send_mail($to, $subject, $body) {
    $driver = getSetting('mail_driver', MAIL_DRIVER);
    
    if ($driver == 'smtp') {
        return send_smtp_mail($to, $subject, $body);
    } else {
        // 使用原生 mail 函数
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=utf-8" . "\r\n";
        $fromName = getSetting('mail_from_name', MAIL_FROM_NAME);
        $fromAddr = getSetting('mail_from_address', MAIL_FROM_ADDRESS);
        if ($fromAddr) {
            $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$fromAddr>" . "\r\n";
        }
        if (mail($to, $subject, $body, $headers)) {
            return true;
        } else {
            return "PHP mail() 发送失败";
        }
    }
}

/**
 * 通过 SMTP 发送邮件（使用 PHPMailer）
 */
function send_smtp_mail($to, $subject, $body) {
    // 检查 PHPMailer 是否可用
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        // 尝试自动加载
        $autoload = __DIR__ . '/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        } else {
            return "未找到 PHPMailer 库，请运行 composer require phpmailer/phpmailer";
        }
    }
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = getSetting('mail_host', MAIL_HOST);
        $mail->SMTPAuth   = true;
        $mail->Username   = getSetting('mail_username', MAIL_USERNAME);
        $mail->Password   = getSetting('mail_password', MAIL_PASSWORD);
        $mail->SMTPSecure = getSetting('mail_encryption', MAIL_ENCRYPTION);
        $mail->Port       = getSetting('mail_port', MAIL_PORT);
        
        $mail->setFrom(getSetting('mail_from_address', MAIL_FROM_ADDRESS), getSetting('mail_from_name', MAIL_FROM_NAME));
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        return "邮件发送失败: {$mail->ErrorInfo}";
    }
}

/**
 * 发送订阅成功通知邮件
 */
function send_subscription_notification($userId, $planName, $expireDate) {
    $user = getUserById($userId);
    if (!$user || empty($user['email'])) return false;
    
    $siteName = getSetting('site_name', SITE_NAME);
    $subject = "{$siteName} - 订阅成功通知";
    $body = "<h2>尊敬的{$user['username']}，您好！</h2>
             <p>您已成功订阅 <strong>{$planName}</strong> 套餐。</p>
             <p>会员有效期至：<strong>{$expireDate}</strong></p>
             <p>感谢您的支持！</p>
             <p><a href='" . SITE_URL . "'>点击访问音乐站</a></p>";
    
    return send_mail($user['email'], $subject, $body);
}

// ------------------ 易支付配置动态获取扩展 ------------------
function getEpayConfig() {
    return [
        'pid' => getSetting('epay_pid', defined('EPAY_PID') ? EPAY_PID : ''),
        'key' => getSetting('epay_key', defined('EPAY_KEY') ? EPAY_KEY : ''),
        'api_url' => getSetting('epay_api_url', defined('EPAY_API_URL') ? EPAY_API_URL : ''),
        'notify_url' => getSetting('epay_notify_url', defined('EPAY_NOTIFY_URL') ? EPAY_NOTIFY_URL : SITE_URL . '/index.php?action=pay_callback'),
        'return_url' => getSetting('epay_return_url', defined('EPAY_RETURN_URL') ? EPAY_RETURN_URL : SITE_URL . '/index.php?action=subscribe_result')
    ];
}