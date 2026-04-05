<?php
// 请填写您的真实配置
$client_id = 'bc772668-a4a5-4f6e-aff2-89b7131f5bb1';
$client_secret = 'kov8Q~aIi.ONt2dmB4bnUokqZkNHSJRAkUAhGa3E';
$redirect_uri = 'https://music.qyffjqw.cn/onedrive_callback.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $code = $_POST['code'];
    $url = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    $data = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => $redirect_uri,
        'scope' => 'https://graph.microsoft.com/Files.ReadWrite offline_access User.Read'
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        echo "<h3>成功获取 token</h3>";
        echo "<p><strong>refresh_token:</strong> <code>" . htmlspecialchars($result['refresh_token']) . "</code></p>";
        echo "<p>请将上面的 refresh_token 复制到 config.php 的 <code>OD_REFRESH_TOKEN</code> 中。</p>";
        echo "<p><strong>access_token 预览:</strong> " . substr($result['access_token'], 0, 50) . "...</p>";
    } else {
        echo "<h3>错误 (HTTP $httpCode)</h3>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>获取 OneDrive Refresh Token</title></head>
<body>
<h2>步骤1：获取授权码</h2>
<p><a href="https://login.microsoftonline.com/common/oauth2/v2.0/authorize?client_id=<?= urlencode($client_id) ?>&response_type=code&redirect_uri=<?= urlencode($redirect_uri) ?>&scope=https://graph.microsoft.com/Files.ReadWrite%20offline_access%20User.Read&response_mode=query" target="_blank">点击这里登录 Microsoft 账户并授权</a></p>
<p>授权后，浏览器会跳转回您的网站，地址栏中会出现 <code>?code=...</code>，请复制整个 code 参数的值。</p>

<h2>步骤2：粘贴授权码换取 refresh_token</h2>
<form method="post">
    <textarea name="code" rows="4" cols="80" placeholder="粘贴完整的授权码"></textarea><br>
    <button type="submit">换取 refresh_token</button>
</form>
</body>
</html>