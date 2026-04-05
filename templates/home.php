<!DOCTYPE html>
<html>
<head>
    <title><?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<div class="container">
    <div class="nav">
        <a href="index.php">首页</a>
        <?php if ($currentUser): ?>
            <span>欢迎，<?= htmlspecialchars($currentUser['username']) ?></span>
            <?php if (isSubscribed($currentUser)): ?>
                <span style="color:green;">订阅中</span>
            <?php else: ?>
                <span style="color:red;">免费用户 (<?= $currentUser['upload_count'] ?>/<?= FREE_UPLOAD_LIMIT ?>)</span>
                <a href="?action=subscribe">开通会员</a>
            <?php endif; ?>
            <a href="?action=upload_form">上传音乐</a>
            <a href="?action=my_music">我的音乐</a>
            <a href="?action=logout">退出</a>
        <?php else: ?>
            <a href="?action=login">登录</a>
            <a href="?action=register">注册</a>
        <?php endif; ?>
    </div>
    <h2>音乐广场</h2>
    <?php foreach ($musics as $music): ?>
        <div class="music-item">
            <strong><?= htmlspecialchars($music['title']) ?></strong> - 上传者：<?= htmlspecialchars($music['username']) ?>
            <audio controls class="audio-player" src="<?= $music['file_url_acc'] ?>"></audio>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>