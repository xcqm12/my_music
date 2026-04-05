<!DOCTYPE html>
<html>
<head><title>注册</title><link rel="stylesheet" href="/assets/style.css"></head>
<body>
<div class="container">
    <h2>注册</h2>
    <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    <form method="post">
        用户名：<input type="text" name="username" required><br>
        密码（至少6位）：<input type="password" name="password" required><br>
        邮箱：<input type="email" name="email"><br>
        <button type="submit">注册</button>
    </form>
    <a href="?action=login">已有账号？登录</a>
</div>
</body>
</html>