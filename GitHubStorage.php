<?php
class GitHubStorage {
    private $token, $owner, $repo, $branch;

    public function __construct() {
        $this->token = GITHUB_TOKEN;
        $this->owner = GITHUB_OWNER;
        $this->repo = GITHUB_REPO;
        $this->branch = GITHUB_BRANCH;
    }

    // 上传文件，返回 raw 地址
    public function upload($localPath, $remotePath) {
        $content = base64_encode(file_get_contents($localPath));
        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/contents/{$remotePath}";
        $data = [
            'message' => 'Upload music via API',
            'content' => $content,
            'branch' => $this->branch
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MusicSystem');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: token ' . $this->token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 201 || $httpCode == 200) {
            return "https://raw.githubusercontent.com/{$this->owner}/{$this->repo}/{$this->branch}/{$remotePath}";
        } else {
            throw new Exception("GitHub upload failed: " . $response);
        }
    }

    // 删除远程文件
    public function delete($remotePath) {
        // 先获取文件SHA
        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/contents/{$remotePath}?ref={$this->branch}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MusicSystem');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: token ' . $this->token]);
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        curl_close($ch);
        if (!isset($data['sha'])) {
            throw new Exception("File not found on GitHub");
        }
        $sha = $data['sha'];

        // 删除
        $delUrl = "https://api.github.com/repos/{$this->owner}/{$this->repo}/contents/{$remotePath}";
        $delData = [
            'message' => 'Delete music file',
            'sha' => $sha,
            'branch' => $this->branch
        ];
        $ch = curl_init($delUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MusicSystem');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: token ' . $this->token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($delData));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($httpCode == 200 || $httpCode == 204);
    }
}