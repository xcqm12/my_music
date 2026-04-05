<!DOCTYPE html>
<html>
<head><title>登录</title><link rel="stylesheet" href="/assets/style.css"></head>
<body>
<div class="container">
    <h2>登录</h2>
    <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    <form method="post">
        用户名：<input type="text" name="username" required><br>
        密码：<input type="password" name="password" required><br>
        <button type="submit">登录</button>
    </form>
    <a href="?action=register">没有账号？注册</a>
</div>
</body>
</html>