<?php
// index.php - 主入口
require_once 'config.php';
require_once 'DB.php';
require_once 'StorageFactory.php';
require_once 'Payment.php';
require_once 'functions.php';

// ---------- 易支付签名函数 ----------
/**
 * 生成签名
 * @param array $params 参数数组（不含sign）
 * @param string $key 商户密钥
 * @return string
 */
function epay_sign($params, $key) {
    ksort($params);
    $str = '';
    foreach ($params as $k => $v) {
        if ($v === '' || $v === null) continue;
        $str .= $k . '=' . $v . '&';
    }
    $str = rtrim($str, '&');
    $str .= $key;
    return md5($str);
}

/**
 * 验证回调签名
 * @param array $data 回调数据（含sign）
 * @param string $key 商户密钥
 * @return bool
 */
function epay_verify_sign($data, $key) {
    if (!isset($data['sign'])) return false;
    $sign = $data['sign'];
    unset($data['sign']);
    $calcSign = epay_sign($data, $key);
    return $calcSign === $sign;
}
// ---------------------------------

$pdo = DB::getInstance()->getPDO();
$action = $_GET['action'] ?? 'home';

$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 流式播放处理
if ($action == 'stream') {
    $id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare("SELECT * FROM musics WHERE id = ?");
    $stmt->execute([$id]);
    $music = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($music && $music['storage_driver'] == 'onedrive') {
        $onedrive = new OneDriveStorage();
        $url = $onedrive->getDownloadUrl($music['storage_path']);
        if ($url) {
            header('Location: ' . $url);
            exit;
        }
    }
    die('无法获取播放链接');
}

switch ($action) {
    case 'logout': 
        session_destroy(); 
        header('Location: index.php'); 
        exit;
        
    case 'login': 
        if ($_POST) {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: index.php');
                exit;
            } else $error = '用户名或密码错误';
        }
        include 'templates/login.php';
        break;
        
    case 'register':
        if ($_POST) {
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $email = $_POST['email'];
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?,?,?)");
                $stmt->execute([$username, $password, $email]);
                $_SESSION['user_id'] = $pdo->lastInsertId();
                header('Location: index.php');
                exit;
            } catch (PDOException $e) { $error = '用户名已存在'; }
        }
        include 'templates/register.php';
        break;
        
    case 'upload_form':
        if (!$currentUser) { header('Location: index.php?action=login'); exit; }
        include 'templates/upload_form.php';
        break;
        
    case 'upload':
        if (!$currentUser) die('请登录');
        $uploadCount = $currentUser['upload_count'];
        if (!isSubscribed($currentUser) && $uploadCount >= FREE_UPLOAD_LIMIT) die('免费用户最多上传5首');
        if ($_FILES['music_file']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['music_file']['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), ['mp3','wav','ogg','m4a'])) die('格式不支持');
            $newFileName = uniqid() . '.' . $ext;
            $remotePath = 'music/' . $newFileName;
            $storage = StorageFactory::create();
            $result = $storage->upload($_FILES['music_file']['tmp_name'], $remotePath);
            $stmt = $pdo->prepare("INSERT INTO musics (user_id, title, file_name, file_url, storage_driver, storage_path, size) VALUES (?,?,?,?,?,?,?)");
            $title = $_POST['title'] ?? pathinfo($_FILES['music_file']['name'], PATHINFO_FILENAME);
            $stmt->execute([$currentUser['id'], $title, $newFileName, $result['url'] ?? '', $result['driver'], $result['path'], $_FILES['music_file']['size']]);
            $pdo->prepare("UPDATE users SET upload_count = upload_count+1 WHERE id=?")->execute([$currentUser['id']]);
            header('Location: index.php?action=my_music&msg=uploaded');
        } else die('上传失败');
        break;
        
    case 'delete_music':
        if (!$currentUser) die('请登录');
        $musicId = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM musics WHERE id=? AND user_id=?");
        $stmt->execute([$musicId, $currentUser['id']]);
        $music = $stmt->fetch();
        if ($music) {
            try {
                $storage = StorageFactory::create();
                $storage->delete($music['storage_path']);
            } catch(Exception $e) {}
            $pdo->prepare("DELETE FROM musics WHERE id=?")->execute([$musicId]);
            $pdo->prepare("UPDATE users SET upload_count = upload_count-1 WHERE id=?")->execute([$currentUser['id']]);
        }
        header('Location: index.php?action=my_music');
        break;
        
    case 'my_music':
        if (!$currentUser) { header('Location: index.php?action=login'); exit; }
        $stmt = $pdo->prepare("SELECT * FROM musics WHERE user_id=? ORDER BY created_at DESC");
        $stmt->execute([$currentUser['id']]);
        $myMusics = $stmt->fetchAll();
        include 'templates/my_music.php';
        break;
        
    case 'subscribe':
        if (!$currentUser) { header('Location: index.php?action=login'); exit; }
        if ($_POST && isset($_POST['plan'])) {
            $plan = $_POST['plan'];
            global $plans;
            $amount = $plans[$plan]['price'];
            $orderNo = date('YmdHis') . rand(1000,9999);
            $stmt = $pdo->prepare("INSERT INTO orders (order_no, user_id, plan, amount, status) VALUES (?,?,?,?,0)");
            $stmt->execute([$orderNo, $currentUser['id'], $plan, $amount]);
            $payUrl = Payment::createOrder($orderNo, $amount, $plans[$plan]['name']);
            header('Location: ' . $payUrl);
            exit;
        }
        include 'templates/subscribe.php';
        break;
        
    case 'pay_callback':
        // 使用易支付签名验证（完整回调处理）
        if (empty($_POST)) {
            die('fail');
        }
        
        // 从配置文件获取商户密钥（需要在config.php中定义EPAY_KEY）
        $epayKey = defined('EPAY_KEY') ? EPAY_KEY : '';
        if (!$epayKey) {
            error_log('EPAY_KEY未配置');
            die('fail');
        }
        
        // 验证签名
        if (!epay_verify_sign($_POST, $epayKey)) {
            error_log('签名验证失败: ' . json_encode($_POST));
            die('fail');
        }
        
        $orderNo = $_POST['out_trade_no'];
        $tradeNo = $_POST['trade_no'];
        $totalFee = $_POST['total_fee'];
        $status = $_POST['trade_status'];
        
        // 只处理已支付的订单
        if ($status !== 'TRADE_SUCCESS') {
            echo 'success';  // 其他状态也返回success，避免重复通知
            exit;
        }
        
        $pdo->beginTransaction();
        try {
            // 锁定订单行
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_no=? FOR UPDATE");
            $stmt->execute([$orderNo]);
            $order = $stmt->fetch();
            
            if ($order && $order['status'] == 0) {
                // 更新订单状态
                $updateOrder = $pdo->prepare("UPDATE orders SET status=1, pay_time=NOW(), transaction_id=? WHERE id=?");
                $updateOrder->execute([$tradeNo, $order['id']]);
                
                // 更新用户订阅时长
                global $plans;
                $days = isset($plans[$order['plan']]['days']) ? $plans[$order['plan']]['days'] : 30;
                updateUserSubscribe($order['user_id'], $days);
                
                // 在 $pdo->commit(); 之前添加
// 发送邮件通知
$user = getUserById($order['user_id']);
if ($user && !empty($user['email'])) {
    $planName = $plans[$order['plan']]['name'] ?? $order['plan'];
    $newExpire = $user['subscribe_expire']; // 已经更新过
    send_subscription_notification($order['user_id'], $planName, $newExpire);
}

                $pdo->commit();
                echo 'success';
            } else {
                $pdo->rollBack();
                echo 'success'; // 已处理过的订单也返回success
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('回调处理异常: ' . $e->getMessage());
            echo 'fail';
        }
        exit;
        
    case 'subscribe_result':
        $orderNo = $_GET['out_trade_no'] ?? '';
        $stmt = $pdo->prepare("SELECT status FROM orders WHERE order_no=?");
        $stmt->execute([$orderNo]);
        $order = $stmt->fetch();
        echo '<script>alert("'.($order && $order['status']==1 ? '订阅成功' : '支付未完成').'");location.href="index.php?action=my_music";</script>';
        break;
        
    case 'home':
    default:
        $stmt = $pdo->query("SELECT m.*, u.username FROM musics m LEFT JOIN users u ON m.user_id=u.id ORDER BY m.created_at DESC");
        $musics = $stmt->fetchAll();
        include 'templates/home.php';
        break;
}