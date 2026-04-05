<?php
function getAcceleratedUrl($rawUrl) {
    if (!GITHUB_ACCELERATE) return $rawUrl;
    // 只对 github raw 链接加速
    if (strpos($rawUrl, 'raw.githubusercontent.com') !== false) {
        $path = parse_url($rawUrl, PHP_URL_PATH);
        return rtrim(GITHUB_ACCELERATE_URL, '/') . $path;
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