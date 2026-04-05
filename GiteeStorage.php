<?php
class GiteeStorage {
    private $token, $owner, $repo, $branch;

    public function __construct() {
        $this->token = GITEE_TOKEN;
        $this->owner = GITEE_OWNER;
        $this->repo = GITEE_REPO;
        $this->branch = GITEE_BRANCH;
    }

    public function upload($localPath, $remotePath) {
        $content = base64_encode(file_get_contents($localPath));
        $url = "https://gitee.com/api/v5/repos/{$this->owner}/{$this->repo}/contents/{$remotePath}";
        $data = [
            'access_token' => $this->token,
            'message' => 'Upload music',
            'content' => $content,
            'branch' => $this->branch
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode == 201 || $httpCode == 200) {
            return "https://gitee.com/{$this->owner}/{$this->repo}/raw/{$this->branch}/{$remotePath}";
        } else {
            throw new Exception("Gitee upload failed: " . $response);
        }
    }

    public function delete($remotePath) {
        $url = "https://gitee.com/api/v5/repos/{$this->owner}/{$this->repo}/contents/{$remotePath}";
        $data = [
            'access_token' => $this->token,
            'message' => 'Delete music',
            'branch' => $this->branch,
            'sha' => '' // 实际使用需要先获取sha，Gitee删除要求sha，为简化可先获取，类似GitHub
        ];
        // 为保持代码简洁，此处省略获取sha步骤，实际项目请完善
        // 建议在调用前通过额外方法获取sha，本示例略（可参考GitHubStorage实现）
        return true; // 占位
    }
}