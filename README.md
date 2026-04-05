# 🎵 音乐上传管理系统

一个基于 PHP + MySQL 的音乐分享平台，支持用户上传、在线播放、会员订阅（易支付），音乐文件可托管至 GitHub 或 Gitee 仓库。代码简洁，适合快速部署。

## ✨ 功能特点

- 用户注册/登录，会话管理
- 音乐上传至 GitHub/Gitee 仓库（支持切换驱动）
- 在线播放所有音乐（HTML5 Audio）
- 免费用户上传数量限制（默认 5 首）
- 会员订阅（月付/年付），集成易支付接口
- 个人音乐管理（删除自己的音乐）
- GitHub Raw 文件加速访问（国内友好）
- 配置化存储驱动，一键切换
- 响应式前端，简单美观

## 📦 技术栈

- **后端**：PHP 7.4+，原生 SQL（PDO）
- **数据库**：MySQL 5.7+
- **存储 API**：GitHub REST API / Gitee REST API
- **支付接口**：易支付（任意易支付系统）
- **前端**：HTML5，CSS3，原生 JavaScript

## 🚀 快速部署

### 1. 环境要求

- PHP 7.4+（开启 `curl`、`session`、`file_uploads`、`pdo_mysql`）
- MySQL 5.7+
- Web 服务器（Apache / Nginx）

### 2. 下载源码

将本项目所有文件上传至网站根目录（如 `/var/www/html/music`）。

### 3. 创建数据库

- 新建数据库（例如 `music_system`），导入根目录下的 `install.sql` 文件。
- 修改 `config.php` 中的数据库连接信息：

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'music_system');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
4. 配置存储驱动
支持 GitHub 或 Gitee 作为文件存储，二选一。

使用 GitHub
创建一个 GitHub 仓库（例如 music-repo）。

生成 Personal Access Token（Settings → Developer settings → Personal access tokens → Tokens (classic)），勾选 repo 权限。

修改 config.php：

php
define('STORAGE_DRIVER', 'github');
define('GITHUB_TOKEN', 'ghp_xxxxxxxxxxxx');
define('GITHUB_OWNER', 'your_username');
define('GITHUB_REPO', 'music-repo');
define('GITHUB_BRANCH', 'main');   // 或 master
使用 Gitee
创建一个 Gitee 仓库（例如 music-repo）。

生成私人令牌（设置 → 安全设置 → 私人令牌），勾选 projects 权限。

修改 config.php：

php
define('STORAGE_DRIVER', 'gitee');
define('GITEE_TOKEN', 'your_gitee_token');
define('GITEE_OWNER', 'your_username');
define('GITEE_REPO', 'music-repo');
define('GITEE_BRANCH', 'master');
5. 配置易支付
注册易支付商户，获取 pid 和 key。

修改 config.php：

php
define('EPAY_PID', 1001);
define('EPAY_KEY', 'your_merchant_key');
define('EPAY_API_URL', 'https://your-epay.com/submit.php');
define('EPAY_NOTIFY_URL', SITE_URL . '/index.php?action=pay_callback');
define('EPAY_RETURN_URL', SITE_URL . '/index.php?action=subscribe_result');
注意：EPAY_NOTIFY_URL 必须为公网可访问地址，确保易支付能回调。

6. 设置站点 URL
修改 config.php 中的 SITE_URL：

php
define('SITE_URL', 'https://your-domain.com');   // 结尾不要加斜杠
7. 设置目录权限
无需特殊写入权限，所有文件直接上传至 Git 仓库，PHP 只需临时目录可写（通常已满足）。

8. 访问测试
访问 https://your-domain.com/index.php，注册用户，上传音乐，测试播放和订阅功能。

⚙️ 配置详解
所有配置位于 config.php，常用项说明：

配置项	说明	示例值
SITE_URL	网站根 URL	https://music.example.com
SITE_NAME	网站标题	音乐分享站
STORAGE_DRIVER	存储驱动：github 或 gitee	github
GITHUB_ACCELERATE	是否启用 GitHub 加速	true
GITHUB_ACCELERATE_URL	加速代理地址	https://ghproxy.net/
FREE_UPLOAD_LIMIT	免费用户最大上传数	5
$plans	订阅套餐定义	['month'=>['price'=>10,'days'=>30]]
📖 使用指南
普通用户
注册/登录：访问首页，点击“注册”创建账号。

浏览音乐：首页展示所有用户上传的音乐，可直接在线播放。

上传音乐：登录后点击“上传音乐”，填写标题并选择音频文件（支持 mp3/wav/ogg/m4a）。

我的音乐：查看自己上传的音乐，可删除。

订阅会员：点击“开通会员”，选择套餐，跳转易支付完成支付，成功后自动延长订阅期。

管理员（可选）
本系统未内置后台管理，但可通过直接操作数据库进行管理。如需管理员功能，可自行扩展 is_admin 字段。

🔧 常见问题
1. 上传失败，提示 GitHub API 错误？
检查 Token 是否具有 repo 权限。

确认仓库存在且分支名称正确（区分大小写）。

服务器能否访问 api.github.com？（可用 curl 测试）

2. 播放时 404 或无法播放？
检查数据库中 file_url 是否存储正确。

若启用了加速，尝试更换加速代理地址（如 https://raw.fastgit.org/）。

直接访问原始 raw 地址，确认文件是否真实存在。

3. 支付回调不生效？
确保 EPAY_NOTIFY_URL 外网可达。

检查 EPAY_KEY 是否与商户后台一致。

查看服务器日志，确认是否收到 POST 请求。

4. 订阅后仍显示免费用户？
确认 users.subscribe_expire 已更新为未来时间。

检查 PHP 时区设置，可在 config.php 添加 date_default_timezone_set('Asia/Shanghai')。

📁 目录结构
text
music_system/
├── index.php                # 前端路由与业务逻辑
├── config.php               # 全局配置
├── DB.php                   # 数据库封装
├── StorageFactory.php       # 存储工厂
├── GitHubStorage.php        # GitHub 存储实现
├── GiteeStorage.php         # Gitee 存储实现
├── Payment.php              # 易支付处理
├── functions.php            # 辅助函数
├── assets/
│   └── style.css            # 全局样式
├── templates/               # 视图模板
│   ├── home.php
│   ├── login.php
│   ├── register.php
│   ├── my_music.php
│   ├── subscribe.php
│   └── upload_form.php
├── install.sql              # 数据库初始化脚本
└── README.md                # 本文件
🤝 贡献与许可
开源协议：MIT License，可自由修改和商用。

贡献：欢迎提交 Issue 或 Pull Request。

📧 联系
如有问题，请在项目中提交 Issue，或通过邮箱联系维护者（示例：qlm@qlm.org.cn）。

祝您使用愉快！ 🎶
