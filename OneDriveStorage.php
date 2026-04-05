<?php

/**
 * OneDriveStorage - 使用 Microsoft Graph API 操作 OneDrive
 * 
 * 需要的常量（需在配置文件中定义）：
 * define('OD_CLIENT_ID', 'your_client_id');
 * define('OD_CLIENT_SECRET', 'your_client_secret');
 * define('OD_REFRESH_TOKEN', 'initial_refresh_token');
 * define('OD_DRIVE_ID', ''); // 可选，如果不提供则自动获取默认驱动器
 * 
 * 若希望自动保存新的 refresh_token，请实现 saveRefreshToken($newToken) 回调。
 */
class OneDriveStorage
{
    private $accessToken;
    private $driveId;
    private $tokenRefreshCallback; // 可选：回调函数，用于保存新的 refresh_token

    /**
     * @param callable|null $tokenRefreshCallback 当获得新 refresh_token 时调用，参数为 string $newToken
     */
    public function __construct(callable $tokenRefreshCallback = null)
    {
        $this->driveId = defined('OD_DRIVE_ID') ? OD_DRIVE_ID : '';
        $this->tokenRefreshCallback = $tokenRefreshCallback;
        $this->refreshAccessToken();
    }

    /**
     * 刷新 access_token
     * @throws Exception
     */
    private function refreshAccessToken(): void
    {
        $url = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
        $data = [
            'client_id'     => OD_CLIENT_ID,
            'client_secret' => OD_CLIENT_SECRET,
            'refresh_token' => OD_REFRESH_TOKEN,
            'grant_type'    => 'refresh_token',
            'scope'         => 'https://graph.microsoft.com/Files.ReadWrite offline_access User.Read'
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
            $errorMsg = "OneDrive token refresh failed (HTTP $httpCode): " . ($response ?: $curlError);
            error_log($errorMsg);
            throw new Exception($errorMsg);
        }

        $json = json_decode($response, true);
        if (!isset($json['access_token'])) {
            throw new Exception("OneDrive token refresh response missing access_token: " . $response);
        }

        $this->accessToken = $json['access_token'];

        // 如果响应中包含新的 refresh_token，且回调存在，则保存它
        if (!empty($json['refresh_token']) && is_callable($this->tokenRefreshCallback)) {
            call_user_func($this->tokenRefreshCallback, $json['refresh_token']);
        }
    }

    /**
     * 获取当前驱动器的 ID（优先使用构造函数传入的，否则请求 /me/drive）
     * @return string
     * @throws Exception
     */
    private function getDriveId(): string
    {
        if (!empty($this->driveId)) {
            return $this->driveId;
        }

        $url = 'https://graph.microsoft.com/v1.0/me/drive';
        $response = $this->httpRequest('GET', $url);
        $data = json_decode($response, true);

        if (!isset($data['id'])) {
            throw new Exception("Unable to retrieve drive ID: " . $response);
        }

        $this->driveId = $data['id'];
        return $this->driveId;
    }

    /**
     * 执行 HTTP 请求（自动处理 access_token）
     * @param string $method GET|POST|PUT|DELETE
     * @param string $url
     * @param array|null $postFields 用于 POST/PUT 的字段或原始数据
     * @param array $headers 额外的头部
     * @return string 响应体
     * @throws Exception
     */
    private function httpRequest(string $method, string $url, $postFields = null, array $headers = []): string
    {
        $ch = curl_init($url);
        $defaultHeaders = ['Authorization: Bearer ' . $this->accessToken];
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => array_merge($defaultHeaders, $headers),
            CURLOPT_TIMEOUT        => 60,
        ]);

        if ($method === 'PUT' && is_resource($postFields)) {
            // 上传文件流
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_INFILE, $postFields);
            curl_setopt($ch, CURLOPT_INFILESIZE, fstat($postFields)['size'] ?? 0);
        } elseif ($method === 'POST' && $postFields !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        } elseif (($method === 'PUT' || $method === 'DELETE') && $postFields !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // 如果 token 过期（401），尝试刷新一次并重试
        if ($httpCode === 401 && strpos($response, 'expired') !== false) {
            $this->refreshAccessToken();
            // 重新发起请求（递归调用，但只重试一次）
            return $this->httpRequest($method, $url, $postFields, $headers);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMsg = "HTTP $httpCode on $method $url: " . ($response ?: $curlError);
            error_log($errorMsg);
            throw new Exception($errorMsg);
        }

        return $response;
    }

    /**
     * 上传文件（小文件直接 PUT，大文件自动分片上传）
     * @param string $localPath 本地文件路径
     * @param string $remotePath 远程路径，如 "folder/file.txt"
     * @return array ['driver'=>'onedrive', 'path'=>$remotePath, 'url'=>'']
     * @throws Exception
     */
    public function upload(string $localPath, string $remotePath): array
    {
        $fileSize = filesize($localPath);
        if ($fileSize === false) {
            throw new Exception("Cannot read file: $localPath");
        }

        // 微软 Graph API 单次 PUT 上传限制为 60MB（实际建议 4MB 分片，但直接 PUT 限制 60MB）
        // 这里以 60MB 为阈值，超过则使用分片上传
        if ($fileSize < 60 * 1024 * 1024) {
            return $this->simpleUpload($localPath, $remotePath);
        } else {
            return $this->chunkedUpload($localPath, $remotePath, $fileSize);
        }
    }

    /**
     * 简单 PUT 上传（适用于小文件）
     */
    private function simpleUpload(string $localPath, string $remotePath): array
    {
        $driveId = $this->getDriveId();
        $encodedPath = $this->encodeRemotePath($remotePath);
        $url = "https://graph.microsoft.com/v1.0/drives/{$driveId}/root:/{$encodedPath}:/content";

        $handle = fopen($localPath, 'r');
        if (!$handle) {
            throw new Exception("Cannot open file: $localPath");
        }

        $response = $this->httpRequest('PUT', $url, $handle, ['Content-Type: application/octet-stream']);
        fclose($handle);

        $data = json_decode($response, true);
        if (isset($data['id'])) {
            return ['driver' => 'onedrive', 'path' => $remotePath, 'url' => ''];
        }

        throw new Exception("Upload failed: " . $response);
    }

    /**
     * 分片上传（适用于大文件，>60MB）
     * 参考：https://docs.microsoft.com/en-us/graph/api/driveitem-createuploadsession?view=graph-rest-1.0
     */
    private function chunkedUpload(string $localPath, string $remotePath, int $fileSize): array
    {
        $driveId = $this->getDriveId();
        $encodedPath = $this->encodeRemotePath($remotePath);
        $createSessionUrl = "https://graph.microsoft.com/v1.0/drives/{$driveId}/root:/{$encodedPath}:/createUploadSession";

        // 1. 创建上传会话
        $sessionResponse = $this->httpRequest('POST', $createSessionUrl, json_encode(['item' => ['@microsoft.graph.conflictBehavior' => 'replace']]), ['Content-Type: application/json']);
        $sessionData = json_decode($sessionResponse, true);
        if (empty($sessionData['uploadUrl'])) {
            throw new Exception("Failed to create upload session: " . $sessionResponse);
        }
        $uploadUrl = $sessionData['uploadUrl'];

        // 2. 分片上传（每片 10MB）
        $chunkSize = 10 * 1024 * 1024;
        $handle = fopen($localPath, 'r');
        if (!$handle) {
            throw new Exception("Cannot open file: $localPath");
        }

        $offset = 0;
        while (!feof($handle)) {
            $chunkData = fread($handle, $chunkSize);
            $currentSize = strlen($chunkData);
            if ($currentSize === 0) break;

            $headers = [
                'Content-Length: ' . $currentSize,
                'Content-Range: bytes ' . $offset . '-' . ($offset + $currentSize - 1) . '/' . $fileSize
            ];

            $ch = curl_init($uploadUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST  => 'PUT',
                CURLOPT_HTTPHEADER     => array_merge(['Authorization: Bearer ' . $this->accessToken], $headers),
                CURLOPT_POSTFIELDS     => $chunkData,
                CURLOPT_TIMEOUT        => 120,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 && $httpCode !== 201 && $httpCode !== 202) {
                fclose($handle);
                throw new Exception("Chunk upload failed at offset $offset: HTTP $httpCode - $response");
            }

            $offset += $currentSize;
        }
        fclose($handle);

        // 分片完成，最后响应应包含文件信息
        $finalData = json_decode($response, true);
        if (isset($finalData['id'])) {
            return ['driver' => 'onedrive', 'path' => $remotePath, 'url' => ''];
        }

        return ['driver' => 'onedrive', 'path' => $remotePath, 'url' => ''];
    }

    /**
     * 删除远程文件
     * @param string $remotePath
     * @return bool
     * @throws Exception
     */
    public function delete(string $remotePath): bool
    {
        $driveId = $this->getDriveId();
        $encodedPath = $this->encodeRemotePath($remotePath);
        $url = "https://graph.microsoft.com/v1.0/drives/{$driveId}/root:/{$encodedPath}";
        $this->httpRequest('DELETE', $url);
        return true;
    }

    /**
     * 获取文件的临时下载链接（有效期约 1 小时）
     * @param string $remotePath
     * @return string|null
     * @throws Exception
     */
    public function getDownloadUrl(string $remotePath): ?string
    {
        $driveId = $this->getDriveId();
        $encodedPath = $this->encodeRemotePath($remotePath);
        $url = "https://graph.microsoft.com/v1.0/drives/{$driveId}/root:/{$encodedPath}";
        $response = $this->httpRequest('GET', $url);
        $data = json_decode($response, true);
        return $data['@microsoft.graph.downloadUrl'] ?? null;
    }

    /**
     * 对远程路径进行 URL 编码（保留斜杠）
     */
    private function encodeRemotePath(string $path): string
    {
        $parts = explode('/', $path);
        $encodedParts = array_map('rawurlencode', $parts);
        return implode('/', $encodedParts);
    }
}