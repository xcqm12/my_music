<?php
require_once 'config.php';
require_once 'DB.php';
require_once 'StorageFactory.php';
require_once 'Payment.php';
require_once 'functions.php';

$pdo = DB::getInstance()->getPDO();
$action = $_GET['action'] ?? 'home';

// 用户认证检查（部分页面无需登录）
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 路由分发
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
            } else {
                $error = '用户名或密码错误';
            }
        }
        include 'templates/login.php';
        break;

    case 'register':
        if ($_POST) {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $email = $_POST['email'] ?? '';
            if (strlen($password) < 6) {
                $error = '密码至少6位';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
                    $stmt->execute([$username, $hash, $email]);
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    header('Location: index.php');
                    exit;
                } catch (PDOException $e) {
                    $error = '用户名已存在';
                }
            }
        }
        include 'templates/register.php';
        break;

    case 'upload_form':
        if (!$currentUser) {
            header('Location: index.php?action=login');
            exit;
        }
        include 'templates/upload_form.php';
        break;

    case 'upload':
        if (!$currentUser) {
            header('Location: index.php?action=login');
            exit;
        }
        // 检查上传限制
        $uploadCount = $currentUser['upload_count'];
        $isSub = isSubscribed($currentUser);
        if (!$isSub && $uploadCount >= FREE_UPLOAD_LIMIT) {
            die('免费用户最多上传 ' . FREE_UPLOAD_LIMIT . ' 首音乐，请订阅会员');
        }
        if ($_FILES['music_file']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['music_file']['tmp_name'];
            $originalName = $_FILES['music_file']['name'];
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), ['mp3', 'wav', 'ogg', 'm4a'])) {
                die('只支持 mp3, wav, ogg, m4a 格式');
            }
            // 生成唯一文件名
            $newFileName = uniqid() . '.' . $ext;
            $remotePath = 'music/' . $newFileName;

            try {
                $storage = StorageFactory::create();
                $rawUrl = $storage->upload($tmpName, $remotePath);
                // 保存到数据库
                $stmt = $pdo->prepare("INSERT INTO musics (user_id, title, file_name, file_url, size) VALUES (?, ?, ?, ?, ?)");
                $title = $_POST['title'] ?? pathinfo($originalName, PATHINFO_FILENAME);
                $size = $_FILES['music_file']['size'];
                $stmt->execute([$currentUser['id'], $title, $newFileName, $rawUrl, $size]);
                // 更新用户上传计数
                $pdo->prepare("UPDATE users SET upload_count = upload_count + 1 WHERE id = ?")->execute([$currentUser['id']]);
                header('Location: index.php?action=my_music&msg=uploaded');
                exit;
            } catch (Exception $e) {
                die('上传失败：' . $e->getMessage());
            }
        } else {
            die('文件上传错误');
        }
        break;

    case 'delete_music':
        if (!$currentUser) die('请先登录');
        $musicId = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM musics WHERE id = ? AND user_id = ?");
        $stmt->execute([$musicId, $currentUser['id']]);
        $music = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$music) die('音乐不存在或无权限');
        // 删除远程文件（可选，可能失败但不影响本地记录删除）
        try {
            $storage = StorageFactory::create();
            $remotePath = 'music/' . $music['file_name'];
            $storage->delete($remotePath);
        } catch (Exception $e) {
            // 记录日志，继续删除数据库记录
        }
        $pdo->prepare("DELETE FROM musics WHERE id = ?")->execute([$musicId]);
        $pdo->prepare("UPDATE users SET upload_count = upload_count - 1 WHERE id = ?")->execute([$currentUser['id']]);
        header('Location: index.php?action=my_music&msg=deleted');
        exit;

    case 'my_music':
        if (!$currentUser) {
            header('Location: index.php?action=login');
            exit;
        }
        $stmt = $pdo->prepare("SELECT * FROM musics WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$currentUser['id']]);
        $myMusics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        include 'templates/my_music.php';
        break;

    case 'subscribe':
        if (!$currentUser) {
            header('Location: index.php?action=login');
            exit;
        }
        global $plans;
        if ($_POST && isset($_POST['plan'])) {
            $plan = $_POST['plan'];
            if (!isset($plans[$plan])) die('无效套餐');
            $amount = $plans[$plan]['price'];
            $orderNo = date('YmdHis') . rand(1000, 9999);
            // 保存订单
            $stmt = $pdo->prepare("INSERT INTO orders (order_no, user_id, plan, amount, status) VALUES (?, ?, ?, ?, 0)");
            $stmt->execute([$orderNo, $currentUser['id'], $plan, $amount]);
            // 跳转到易支付
            $payUrl = Payment::createOrder($orderNo, $amount, $plans[$plan]['name']);
            header('Location: ' . $payUrl);
            exit;
        }
        include 'templates/subscribe.php';
        break;

    case 'pay_callback':
        // 易支付异步通知
        $data = $_POST;
        if (Payment::verifyCallback($data)) {
            $orderNo = $data['out_trade_no'];
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_no = ? FOR UPDATE");
                $stmt->execute([$orderNo]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($order && $order['status'] == 0) {
                    // 更新订单状态
                    $pdo->prepare("UPDATE orders SET status = 1, pay_time = NOW() WHERE id = ?")->execute([$order['id']]);
                    // 增加用户订阅天数
                    global $plans;
                    $days = $plans[$order['plan']]['days'];
                    $user = getUserById($order['user_id']);
                    $currentExpire = $user['subscribe_expire'];
                    if ($currentExpire && strtotime($currentExpire) > time()) {
                        $newExpire = date('Y-m-d H:i:s', strtotime($currentExpire . " + $days days"));
                    } else {
                        $newExpire = date('Y-m-d H:i:s', time() + $days * 86400);
                    }
                    $pdo->prepare("UPDATE users SET subscribe_expire = ? WHERE id = ?")->execute([$newExpire, $order['user_id']]);
                }
                $pdo->commit();
                echo 'success';
            } catch (Exception $e) {
                $pdo->rollBack();
                echo 'fail';
            }
        } else {
            echo 'fail';
        }
        exit;

    case 'subscribe_result':
        // 同步返回页面
        if (isset($_GET['out_trade_no'])) {
            $orderNo = $_GET['out_trade_no'];
            $stmt = $pdo->prepare("SELECT status FROM orders WHERE order_no = ?");
            $stmt->execute([$orderNo]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($order && $order['status'] == 1) {
                echo '<script>alert("订阅成功！");location.href="index.php?action=my_music";</script>';
            } else {
                echo '<script>alert("支付未完成，请稍后重试");location.href="index.php?action=subscribe";</script>';
            }
        } else {
            header('Location: index.php');
        }
        break;

    case 'home':
    default:
        // 获取所有音乐（公开播放列表）
        $stmt = $pdo->query("SELECT m.*, u.username FROM musics m LEFT JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC");
        $musics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // 应用加速地址
        foreach ($musics as &$music) {
            $music['file_url_acc'] = getAcceleratedUrl($music['file_url']);
        }
        include 'templates/home.php';
        break;
}