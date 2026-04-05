<!DOCTYPE html>
<html>
<head><title>我的音乐</title><link rel="stylesheet" href="/assets/style.css"></head>
<body>
<div class="container">
    <div class="nav">
        <a href="index.php">首页</a> | <a href="?action=upload_form">上传音乐</a> | <a href="?action=logout">退出</a>
    </div>
    <h2>我的音乐</h2>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'uploaded') echo "<p style='color:green'>上传成功！</p>"; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') echo "<p style='color:green'>删除成功！</p>"; ?>
    <?php if (empty($myMusics)): ?>
        <p>还没有上传过音乐，<a href="?action=upload_form">立即上传</a></p>
    <?php else: ?>
        <?php foreach ($myMusics as $music): ?>
            <div class="music-item">
                <strong><?= htmlspecialchars($music['title']) ?></strong>
                <audio controls src="<?= getAcceleratedUrl($music['file_url']) ?>"></audio>
                <a href="?action=delete_music&id=<?= $music['id'] ?>" onclick="return confirm('确定删除？')" class="btn btn-danger">删除</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>