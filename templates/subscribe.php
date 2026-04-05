<!DOCTYPE html>
<html>
<head><title>订阅会员</title><link rel="stylesheet" href="/assets/style.css"></head>
<body>
<div class="container">
    <h2>订阅会员</h2>
    <p>当前状态：<?= isSubscribed($currentUser) ? '已订阅，有效期至 ' . $currentUser['subscribe_expire'] : '未订阅' ?></p>
    <form method="post">
        <?php global $plans; foreach ($plans as $key => $plan): ?>
            <label>
                <input type="radio" name="plan" value="<?= $key ?>" required>
                <?= $plan['name'] ?> - <?= $plan['price'] ?> 元 (<?= $plan['days'] ?>天)
            </label><br>
        <?php endforeach; ?>
        <button type="submit">立即支付</button>
    </form>
</div>
</body>
</html>