<?php
// GitHubStorage.php
class GitHubStorage {
    private $token, $owner, $repo, $branch;

    public function __construct() {
        $this->token = GITHUB_TOKEN;
        $this->owner = GITHUB_OWNER;
        $this->repo = GITHUB_REPO;
        $this->branch = GITHUB_BRANCH;
    }

    public function upload($localPath, $remotePath) {
        $content = base64_encode(file_get_contents($localPath));
        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/contents/{$remotePath}";
        $data = ['message' => 'Upload music', 'content' => $content, 'branch' => $this->branch];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MusicSystem');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: token ' . $this->token, 'Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 201 || $httpCode == 200) {
            $rawUrl = "https://raw.githubusercontent.com/{$this->owner}/{$this->repo}/{$this->branch}/{$remotePath}";
            return ['driver'=>'github', 'path'=>$remotePath, 'url'=>$rawUrl];
        } else {
            throw new Exception("GitHub upload failed: " . $response);
        }
    }

    public function delete($remotePath) {
        // 获取SHA
        $url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/contents/{$remotePath}?ref={$this->branch}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MusicSystem');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: token ' . $this->token]);
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        curl_close($ch);
        if (!isset($data['sha'])) throw new Exception("File not found");
        $sha = $data['sha'];

        $delUrl = "https://api.github.com/repos/{$this->owner}/{$this->repo}/contents/{$remotePath}";
        $delData = ['message' => 'Delete', 'sha' => $sha, 'branch' => $this->branch];
        $ch = curl_init($delUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MusicSystem');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: token ' . $this->token, 'Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($delData));
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($httpCode == 200 || $httpCode == 204);
    }
}