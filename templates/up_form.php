<!DOCTYPE html>
<html>
<head><title>上传音乐</title><link rel="stylesheet" href="/assets/style.css"></head>
<body>
<div class="container">
    <h2>上传音乐</h2>
    <?php
    $isSub = isSubscribed($currentUser);
    $uploadCount = $currentUser['upload_count'];
    if (!$isSub && $uploadCount >= FREE_UPLOAD_LIMIT):
    ?>
        <p style="color:red">您已达到免费上传限制（<?= FREE_UPLOAD_LIMIT ?>首），请<a href="?action=subscribe">订阅会员</a>继续上传。</p>
    <?php else: ?>
        <form method="post" action="?action=upload" enctype="multipart/form-data">
            <label>标题：<input type="text" name="title" placeholder="音乐标题"></label><br>
            <label>文件：<input type="file" name="music_file" accept="audio/*" required></label><br>
            <button type="submit">上传</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>