<?php
// GiteeStorage.php (完善版)
class GiteeStorage {
    private $token, $owner, $repo, $branch;

    public function __construct() {
        $this->token = GITEE_TOKEN;
        $this->owner = GITEE_OWNER;
        $this->repo = GITEE_REPO;
        $this->branch = GITEE_BRANCH;
    }

    private function getFileSha($remotePath) {
        $url = "https://gitee.com/api/v5/repos/{$this->owner}/{$this->repo}/contents/{$remotePath}?ref={$this->branch}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: token ' . $this->token]);
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        curl_close($ch);
        return $data['sha'] ?? null;
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
            $rawUrl = "https://gitee.com/{$this->owner}/{$this->repo}/raw/{$this->branch}/{$remotePath}";
            return ['driver'=>'gitee', 'path'=>$remotePath, 'url'=>$rawUrl];
        } else {
            throw new Exception("Gitee upload failed: " . $response);
        }
    }

    public function delete($remotePath) {
        $sha = $this->getFileSha($remotePath);
        if (!$sha) throw new Exception("File not found");
        $url = "https://gitee.com/api/v5/repos/{$this->owner}/{$this->repo}/contents/{$remotePath}";
        $data = [
            'access_token' => $this->token,
            'message' => 'Delete music',
            'branch' => $this->branch,
            'sha' => $sha
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpCode == 204;
    }
}