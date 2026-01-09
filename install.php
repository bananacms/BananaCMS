<?php
/**
 * 香蕉CMS 安装向导
 * Powered by https://xpornkit.com
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', __DIR__ . '/');
define('CONFIG_PATH', ROOT_PATH . 'config/');

// 获取需要删除的敏感文件列表
function getSensitiveFiles(): array {
    $files = [];
    $sensitiveExts = ['md', 'sql', 'txt', 'rar', 'zip'];
    $excludeFiles = ['.htaccess', 'index.html', 'robots.txt']; // 排除这些文件
    
    foreach (glob(ROOT_PATH . '*') as $file) {
        if (!is_file($file)) continue;
        $basename = basename($file);
        $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
        
        // 排除特定文件
        if (in_array($basename, $excludeFiles)) continue;
        
        // 检查扩展名
        if (in_array($ext, $sensitiveExts) || $basename === 'install.php') {
            $files[] = $basename;
        }
    }
    
    return $files;
}

// 检查是否已安装
if (file_exists(CONFIG_PATH . 'install.lock')) {
    // 尝试加载配置以获取后台入口
    $adminEntry = 'admin';
    if (file_exists(CONFIG_PATH . 'config.php')) {
        $configContent = file_get_contents(CONFIG_PATH . 'config.php');
        if (preg_match("/define\('ADMIN_ENTRY',\s*'([^']+)'\)/", $configContent, $matches)) {
            $adminEntry = $matches[1];
        }
    }
    
    // 处理删除文件请求
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['file'])) {
        header('Content-Type: application/json');
        $allowedFiles = getSensitiveFiles();
        $file = $_GET['file'];
        
        if (!in_array($file, $allowedFiles)) {
            echo json_encode(['code' => 1, 'msg' => '不允许删除该文件']);
            exit;
        }
        
        $filePath = ROOT_PATH . $file;
        if (!file_exists($filePath)) {
            echo json_encode(['code' => 0, 'msg' => '文件已删除']);
            exit;
        }
        
        if (@unlink($filePath)) {
            echo json_encode(['code' => 0, 'msg' => '删除成功']);
        } else {
            echo json_encode(['code' => 1, 'msg' => '删除失败，请检查文件权限']);
        }
        exit;
    }
    
    // 获取文件列表（用于 AJAX）
    if (isset($_GET['action']) && $_GET['action'] === 'list') {
        header('Content-Type: application/json');
        echo json_encode(['code' => 0, 'files' => getSensitiveFiles()]);
        exit;
    }
    
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:sans-serif;text-align:center;padding:50px;"><h1>🍌 香蕉CMS</h1><p>系统已安装，如需重新安装请删除 config/install.lock</p><p><a href="/">首页</a> | <a href="/' . $adminEntry . '">后台</a></p></body></html>');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$step = max(1, min(4, (int)($_GET['step'] ?? 1)));
$error = '';

// 处理POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        header('Location: install.php?step=3');
        exit;
    }
    
    if ($step === 3) {
        $dbHost = trim($_POST['db_host'] ?? 'localhost');
        $dbPort = trim($_POST['db_port'] ?? '3306');
        $dbName = trim($_POST['db_name'] ?? '');
        $dbUser = trim($_POST['db_user'] ?? '');
        $dbPass = $_POST['db_pass'] ?? '';
        $dbPrefix = trim($_POST['db_prefix'] ?? 'xpk_');
        $adminUser = trim($_POST['admin_user'] ?? '');
        $adminPass = $_POST['admin_pass'] ?? '';
        $adminPassConfirm = $_POST['admin_pass_confirm'] ?? '';
        $adminEntry = trim($_POST['admin_entry'] ?? 'admin');
        $siteName = trim($_POST['site_name'] ?? '香蕉影视');
        $siteUrl = trim($_POST['site_url'] ?? '');
        
        // 验证
        if (empty($dbName) || empty($dbUser)) {
            $error = '请填写数据库名称和用户名';
        } elseif (empty($adminUser) || strlen($adminUser) < 3) {
            $error = '管理员用户名至少3个字符';
        } elseif (empty($adminPass) || strlen($adminPass) < 6) {
            $error = '管理员密码至少6个字符';
        } elseif ($adminPass !== $adminPassConfirm) {
            $error = '两次输入的密码不一致';
        } elseif (empty($adminEntry) || !preg_match('/^[a-zA-Z0-9_-]+$/', $adminEntry)) {
            $error = '后台入口文件名只能包含字母、数字、下划线和连字符';
        } elseif (in_array($adminEntry, ['index', 'api', 'install', 'cron', 'sitemap'])) {
            $error = '后台入口文件名不能使用系统保留名称';
        } else {
            try {
                $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
                $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4");
                $pdo->exec("USE `{$dbName}`");
                
                $sqlFile = ROOT_PATH . 'data.sql';
                if (!file_exists($sqlFile)) {
                    throw new Exception('data.sql 文件不存在');
                }
                
                $sql = file_get_contents($sqlFile);
                $sql = str_replace('xpk_', $dbPrefix, $sql);
                // 移除默认管理员插入语句
                $sql = preg_replace("/INSERT INTO `{$dbPrefix}admin`[^;]+;/", '', $sql);
                
                // 分割并逐条执行SQL
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    // 跳过空语句、注释、SET语句
                    if (empty($statement) || preg_match('/^(--|#|SET\s|\/\*)/i', $statement)) {
                        continue;
                    }
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // 忽略表已存在等非致命错误，继续执行
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            // 记录错误但继续
                        }
                    }
                }
                
                // 插入管理员
                $stmt = $pdo->prepare("INSERT INTO `{$dbPrefix}admin` (admin_name, admin_pwd, admin_status) VALUES (?, ?, 1)");
                $stmt->execute([$adminUser, password_hash($adminPass, PASSWORD_DEFAULT)]);
                
                // 更新配置（使用 REPLACE 确保写入）
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (1, 'site_name', " . $pdo->quote($siteName) . ")");
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (2, 'site_url', " . $pdo->quote($siteUrl) . ")");
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (3, 'site_keywords', '香蕉CMS,BananaCMS,免费影视CMS,在线观看')");
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (4, 'site_description', '香蕉CMS - 轻量级影视内容管理系统，提供最新电影、电视剧、综艺、动漫在线观看')");
                // URL模式默认为4（slug模式）
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (5, 'url_mode', '4')");
                // SEO模板默认值
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (6, 'seo_title_vod_detail', '{name}在线观看 - {sitename}')");
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (7, 'seo_keywords_vod_detail', '{name},{actor},{type},{year},{area}')");
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (8, 'seo_description_vod_detail', '{name}由{actor}主演，{year}年{area}{type}，{description}')");
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (9, 'seo_title_type', '{name}大全_最新{name}排行榜 - {sitename}')");
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (10, 'seo_keywords_type', '{name},{name}大全,最新{name},{name}排行榜')");
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (11, 'seo_title_actor_detail', '{name}个人资料_主演作品 - {sitename}')");
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (12, 'seo_title_art_detail', '{name} - {sitename}')");
                // 评论和用户设置
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (13, 'comment_audit', '0')");
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (14, 'comment_guest', '1')");
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (15, 'user_register', '1')");
                $pdo->exec("REPLACE INTO `{$dbPrefix}config` (config_id, config_name, config_value) VALUES (16, 'user_register_limit', '5')");
                // 生成配置文件
                $secret = 'xpk_' . bin2hex(random_bytes(16));
                
                // 转义特殊字符，防止配置文件语法错误
                $escapedDbPass = addslashes($dbPass);
                $escapedSiteName = addslashes($siteName);
                $escapedSiteUrl = addslashes($siteUrl);
                
                $config = "<?php\ndefine('APP_DEBUG', true);\ndefine('APP_SECRET', '{$secret}');\n";
                $config .= "define('SITE_NAME', '{$escapedSiteName}');\ndefine('SITE_URL', '{$escapedSiteUrl}');\n";
                $config .= "define('SITE_KEYWORDS', '');\ndefine('SITE_DESCRIPTION', '');\n";
                $config .= "define('DB_HOST', '{$dbHost}');\ndefine('DB_PORT', '{$dbPort}');\n";
                $config .= "define('DB_NAME', '{$dbName}');\ndefine('DB_USER', '{$dbUser}');\n";
                $config .= "define('DB_PASS', '{$escapedDbPass}');\ndefine('DB_CHARSET', 'utf8mb4');\n";
                $config .= "define('DB_PREFIX', '{$dbPrefix}');\n";
                $config .= "define('ROOT_PATH', dirname(__DIR__) . '/');\n";
                $config .= "define('CONFIG_PATH', ROOT_PATH . 'config/');\n";
                $config .= "define('CORE_PATH', ROOT_PATH . 'core/');\n";
                $config .= "define('MODEL_PATH', ROOT_PATH . 'models/');\n";
                $config .= "define('CTRL_PATH', ROOT_PATH . 'controllers/');\n";
                $config .= "define('VIEW_PATH', ROOT_PATH . 'views/');\n";
                $config .= "define('TPL_PATH', ROOT_PATH . 'template/');\n";
                $config .= "define('STATIC_PATH', ROOT_PATH . 'static/');\n";
                $config .= "define('UPLOAD_PATH', ROOT_PATH . 'upload/');\n";
                $config .= "define('RUNTIME_PATH', ROOT_PATH . 'runtime/');\n";
                $config .= "date_default_timezone_set('Asia/Shanghai');\n";
                $config .= "define('PAGE_SIZE', 24);\n";
                $config .= "define('UPLOAD_MAX_SIZE', 10485760);\n";
                $config .= "define('UPLOAD_ALLOW_EXT', 'jpg,jpeg,png,gif,webp');\n";
                $config .= "define('ADMIN_ENTRY', '{$adminEntry}');\n";
                $config .= "define('XPK_ROOT', true);\n";
                $config .= "require_once CONFIG_PATH . 'constants.php';\n";
                
                file_put_contents(CONFIG_PATH . 'config.php', $config);
                file_put_contents(CONFIG_PATH . 'install.lock', date('Y-m-d H:i:s'));
                
                // 创建自定义后台入口文件
                $adminContent = file_get_contents(ROOT_PATH . 'admin.php');
                file_put_contents(ROOT_PATH . $adminEntry . '.php', $adminContent);
                
                // 如果不是默认的admin.php，删除原admin.php文件
                if ($adminEntry !== 'admin' && file_exists(ROOT_PATH . 'admin.php')) {
                    @unlink(ROOT_PATH . 'admin.php');
                }
                
                // Generate robots.txt with full sitemap URL
                $robotsContent = "User-agent: *\n";
                $robotsContent .= "Allow: /\n";
                $robotsContent .= "Disallow: /{$adminEntry}.php\n";
                $robotsContent .= "Disallow: /api.php\n";
                $robotsContent .= "Disallow: /install.php\n";
                $robotsContent .= "Disallow: /search\n";
                $robotsContent .= "Disallow: /search/\n";
                $robotsContent .= "Disallow: /user/\n";
                $robotsContent .= "Disallow: /config/\n";
                $robotsContent .= "Disallow: /runtime/\n";
                $robotsContent .= "Disallow: /controllers/\n";
                $robotsContent .= "Disallow: /models/\n";
                $robotsContent .= "Disallow: /core/\n";
                $robotsContent .= "Disallow: /views/\n";
                $robotsContent .= "\n";
                $robotsContent .= "Sitemap: {$siteUrl}/sitemap.xml\n";
                file_put_contents(ROOT_PATH . 'robots.txt', $robotsContent);
                
                // Generate .htaccess for Apache
                $htaccessContent = "# 香蕉CMS Apache 伪静态配置\n";
                $htaccessContent .= "# 后台入口: /{$adminEntry}\n\n";
                $htaccessContent .= "<IfModule mod_rewrite.c>\n";
                $htaccessContent .= "    RewriteEngine On\n";
                $htaccessContent .= "    RewriteBase /\n\n";
                $htaccessContent .= "    # Sitemap\n";
                $htaccessContent .= "    RewriteRule ^sitemap\\.xml\$ sitemap.php [QSA,L]\n\n";
                $htaccessContent .= "    # Static files\n";
                $htaccessContent .= "    RewriteCond %{REQUEST_URI} ^/static/ [OR]\n";
                $htaccessContent .= "    RewriteCond %{REQUEST_URI} ^/upload/\n";
                $htaccessContent .= "    RewriteRule ^ - [L]\n\n";
                $htaccessContent .= "    # All requests to index.php\n";
                $htaccessContent .= "    RewriteCond %{REQUEST_FILENAME} !-f\n";
                $htaccessContent .= "    RewriteCond %{REQUEST_FILENAME} !-d\n";
                $htaccessContent .= "    RewriteRule ^(.*)\$ index.php?s=\$1 [QSA,L]\n";
                $htaccessContent .= "</IfModule>\n\n";
                $htaccessContent .= "<IfModule mod_negotiation.c>\n";
                $htaccessContent .= "    Options -MultiViews\n";
                $htaccessContent .= "</IfModule>\n\n";
                $htaccessContent .= "AcceptPathInfo On\n\n";
                $htaccessContent .= "# Block sensitive directories\n";
                $htaccessContent .= "<FilesMatch \"^(config|core|models|controllers|views|runtime)\">\n";
                $htaccessContent .= "    Order deny,allow\n";
                $htaccessContent .= "    Deny from all\n";
                $htaccessContent .= "</FilesMatch>\n";
                file_put_contents(ROOT_PATH . '.htaccess', $htaccessContent);
                
                // Generate nginx.conf for reference
                $nginxContent = "# 香蕉CMS Nginx 伪静态配置\n";
                $nginxContent .= "# 后台入口: /{$adminEntry}\n";
                $nginxContent .= "# 使用方法: 宝塔面板 → 网站设置 → 伪静态 → 粘贴规则 → 保存\n\n";
                $nginxContent .= "location = /sitemap.xml {\n";
                $nginxContent .= "    rewrite ^ /sitemap.php last;\n";
                $nginxContent .= "}\n\n";
                $nginxContent .= "location ~ ^/(config|core|models|controllers|views|runtime)/ {\n";
                $nginxContent .= "    deny all;\n";
                $nginxContent .= "}\n\n";
                $nginxContent .= "location /static/ {\n";
                $nginxContent .= "    try_files \$uri =404;\n";
                $nginxContent .= "}\n\n";
                $nginxContent .= "location /upload/ {\n";
                $nginxContent .= "    try_files \$uri =404;\n";
                $nginxContent .= "}\n\n";
                $nginxContent .= "location / {\n";
                $nginxContent .= "    try_files \$uri \$uri/ /index.php?s=\$uri&\$args;\n";
                $nginxContent .= "}\n";
                file_put_contents(ROOT_PATH . 'nginx.conf', $nginxContent);
                
                // 自动删除敏感文件
                $sensitiveExts = ['md', 'sql', 'txt', 'rar', 'zip'];
                $excludeFiles = ['.htaccess', 'index.html', 'robots.txt'];
                foreach (glob(ROOT_PATH . '*') as $file) {
                    if (!is_file($file)) continue;
                    $basename = basename($file);
                    $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
                    if (in_array($basename, $excludeFiles)) continue;
                    if (in_array($ext, $sensitiveExts)) {
                        @unlink($file);
                    }
                }
                
                $_SESSION['install_admin'] = $adminUser;
                $_SESSION['install_admin_pass'] = $adminPass;
                $_SESSION['install_admin_entry'] = $adminEntry;
                $_SESSION['install_site_url'] = $siteUrl;
                header('Location: install.php?step=4');
                exit;
                
            } catch (Exception $e) {
                $error = '安装失败: ' . $e->getMessage();
            }
        }
    }
}

// 环境检测
function checkEnv(): array {
    $checks = [];
    $checks[] = ['PHP版本', '8.0+', PHP_VERSION, version_compare(PHP_VERSION, '8.0.0', '>=')];
    $checks[] = ['PDO扩展', '必须', extension_loaded('pdo') ? '已安装' : '未安装', extension_loaded('pdo')];
    $checks[] = ['PDO MySQL', '必须', extension_loaded('pdo_mysql') ? '已安装' : '未安装', extension_loaded('pdo_mysql')];
    $checks[] = ['config目录', '可写', is_writable(ROOT_PATH . 'config') ? '可写' : '不可写', is_writable(ROOT_PATH . 'config')];
    $checks[] = ['runtime目录', '可写', is_writable(ROOT_PATH . 'runtime') ? '可写' : '不可写', is_writable(ROOT_PATH . 'runtime')];
    $checks[] = ['upload目录', '可写', is_writable(ROOT_PATH . 'upload') ? '可写' : '不可写', is_writable(ROOT_PATH . 'upload')];
    return $checks;
}
$envChecks = checkEnv();
$envPass = !in_array(false, array_column($envChecks, 3));
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装向导 - 香蕉CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    .install-toast{position:fixed;top:20px;left:50%;transform:translateX(-50%);padding:12px 24px;border-radius:8px;color:#fff;font-size:14px;z-index:9999;animation:toastIn .3s}
    .install-toast.success{background:#22c55e}
    .install-toast.error{background:#ef4444}
    @keyframes toastIn{from{opacity:0;top:0}to{opacity:1;top:20px}}
    </style>
    <script>
    function showToast(msg,type){var t=document.createElement('div');t.className='install-toast '+(type||'success');t.textContent=msg;document.body.appendChild(t);setTimeout(function(){t.remove()},3000)}
    </script>
</head>
<body class="bg-gradient-to-br from-yellow-400 to-orange-500 min-h-screen py-10">
<div class="max-w-2xl mx-auto px-4">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">🍌 香蕉CMS</h1>
        <p class="text-white/80">轻量级影视内容管理系统</p>
    </div>

    <div class="flex justify-center mb-8">
        <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold <?= $i < $step ? 'bg-green-500 text-white' : ($i == $step ? 'bg-white text-yellow-600' : 'bg-white/30 text-white') ?>">
                <?= $i < $step ? '✓' : $i ?>
            </div>
            <?php if ($i < 4): ?><div class="w-12 h-1 <?= $i < $step ? 'bg-green-500' : 'bg-white/30' ?>"></div><?php endif; ?>
        </div>
        <?php endfor; ?>
    </div>

    <div class="bg-white rounded-lg shadow-xl p-8">
        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
        <h2 class="text-2xl font-bold mb-6">许可协议</h2>
        <div class="bg-gray-50 rounded p-4 h-48 overflow-y-auto text-sm text-gray-600 mb-6">
            <p class="font-bold mb-2">MIT License - Copyright (c) <?= date('Y') ?> XPornKit.com</p>
            <p class="mb-2">本软件免费开源，您可以自由使用、修改和分发。</p>
            <p class="text-red-600 font-bold">特别说明：请保留页脚版权信息，删除可能导致部分功能受限。</p>
        </div>
        <div class="flex justify-end">
            <a href="install.php?step=2" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded font-bold">同意并继续</a>
        </div>

        <?php elseif ($step === 2): ?>
        <h2 class="text-2xl font-bold mb-6">环境检测</h2>
        <table class="w-full mb-6">
            <tr class="border-b"><th class="text-left py-2">检测项</th><th class="text-left py-2">要求</th><th class="text-left py-2">当前</th><th class="text-center py-2">状态</th></tr>
            <?php foreach ($envChecks as $c): ?>
            <tr class="border-b"><td class="py-2"><?= $c[0] ?></td><td class="py-2 text-gray-500"><?= $c[1] ?></td><td class="py-2"><?= $c[2] ?></td><td class="py-2 text-center"><?= $c[3] ? '<span class="text-green-500">✓</span>' : '<span class="text-red-500">✗</span>' ?></td></tr>
            <?php endforeach; ?>
        </table>
        <?php if ($envPass): ?>
        <form method="POST"><div class="flex justify-between"><a href="install.php?step=1" class="text-gray-500 py-2">上一步</a><button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded font-bold">下一步</button></div></form>
        <?php else: ?>
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded">环境检测未通过，请先解决问题</div>
        <?php endif; ?>

        <?php elseif ($step === 3): ?>
        <h2 class="text-2xl font-bold mb-6">配置信息</h2>
        <form method="POST" class="space-y-6">
            <div>
                <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">数据库配置</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm mb-1">主机</label><input type="text" name="db_host" value="localhost" class="w-full border rounded px-3 py-2"></div>
                    <div><label class="block text-sm mb-1">端口</label><input type="text" name="db_port" value="3306" class="w-full border rounded px-3 py-2"></div>
                    <div><label class="block text-sm mb-1">数据库名 *</label><input type="text" name="db_name" value="" required class="w-full border rounded px-3 py-2" placeholder="请输入数据库名"></div>
                    <div><label class="block text-sm mb-1">表前缀</label><input type="text" name="db_prefix" value="xpk_" class="w-full border rounded px-3 py-2"></div>
                    <div><label class="block text-sm mb-1">用户名 *</label><input type="text" name="db_user" required class="w-full border rounded px-3 py-2"></div>
                    <div><label class="block text-sm mb-1">密码</label><input type="password" name="db_pass" class="w-full border rounded px-3 py-2"></div>
                </div>
            </div>
            <div>
                <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">管理员账号</h3>
                <div class="space-y-4">
                    <div><label class="block text-sm mb-1">用户名 * (至少3字符)</label><input type="text" name="admin_user" required minlength="3" class="w-full border rounded px-3 py-2"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm mb-1">密码 * (至少6字符)</label>
                            <div class="flex gap-2">
                                <input type="text" name="admin_pass" id="admin_pass" required minlength="6" class="flex-1 border rounded px-3 py-2">
                                <button type="button" onclick="generatePassword()" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm" title="随机生成密码">🎲</button>
                            </div>
                        </div>
                        <div><label class="block text-sm mb-1">确认密码 *</label><input type="text" name="admin_pass_confirm" id="admin_pass_confirm" required class="w-full border rounded px-3 py-2"></div>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">安全设置</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm mb-1">后台入口文件名 * (不含.php后缀)</label>
                        <input type="text" name="admin_entry" value="admin" required pattern="[a-zA-Z0-9_-]+" class="w-full border rounded px-3 py-2" placeholder="例如: admin, manage, backend">
                        <p class="text-xs text-gray-500 mt-1">自定义后台访问路径，配合伪静态可直接访问 /admin 等路径。只能包含字母、数字、下划线和连字符。</p>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">站点信息</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm mb-1">站点名称</label><input type="text" name="site_name" value="香蕉影视" class="w-full border rounded px-3 py-2"></div>
                    <div><label class="block text-sm mb-1">站点URL</label><input type="text" name="site_url" value="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] ?>" class="w-full border rounded px-3 py-2"></div>
                </div>
            </div>
            <div class="flex justify-between pt-4">
                <a href="install.php?step=2" class="text-gray-500 py-2">上一步</a>
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded font-bold">开始安装</button>
            </div>
        </form>
        <script>
        function generatePassword() {
            const chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefghjkmnpqrstwxyz23456789!@#$%';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('admin_pass').value = password;
            document.getElementById('admin_pass_confirm').value = password;
            showToast('已生成随机密码，请牢记或下载保存', 'success');
        }
        </script>

        <?php elseif ($step === 4): 
            // 检查是否有安装信息（防止直接访问 step 4）
            if (empty($_SESSION['install_admin']) || empty($_SESSION['install_admin_entry'])) {
                header('Location: install.php?step=1');
                exit;
            }
            
            // 检测服务器类型
            $serverSoftware = strtolower($_SERVER['SERVER_SOFTWARE'] ?? '');
            $serverType = 'unknown';
            $serverTypeName = '未知';
            if (strpos($serverSoftware, 'nginx') !== false) {
                $serverType = 'nginx';
                $serverTypeName = 'Nginx';
            } elseif (strpos($serverSoftware, 'apache') !== false) {
                $serverType = 'apache';
                $serverTypeName = 'Apache';
            } elseif (strpos($serverSoftware, 'litespeed') !== false) {
                $serverType = 'litespeed';
                $serverTypeName = 'LiteSpeed (兼容Apache规则)';
                $serverType = 'apache'; // LiteSpeed 兼容 Apache 规则
            }
            $adminEntry = $_SESSION['install_admin_entry'];
            $adminUser = $_SESSION['install_admin'];
            $adminPass = $_SESSION['install_admin_pass'] ?? '';
            $siteUrl = rtrim($_SESSION['install_site_url'] ?? '', '/');
            
            // 如果 siteUrl 为空，使用当前请求的域名
            if (empty($siteUrl)) {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $siteUrl = $protocol . '://' . $_SERVER['HTTP_HOST'];
            }
            
            $fullAdminUrl = $siteUrl . '/' . $adminEntry;
        ?>
        <div class="text-center">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="text-2xl font-bold mb-4 text-green-600">安装成功！</h2>
            <div class="bg-gray-50 rounded p-6 mb-6">
                <p class="mb-2"><strong>管理员：</strong><?= htmlspecialchars($adminUser) ?></p>
                <p class="mb-2"><strong>后台地址：</strong><a href="/<?= htmlspecialchars($adminEntry) ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($fullAdminUrl) ?></a></p>
                <p class="text-sm text-gray-500 mb-4">请牢记您设置的密码和后台访问地址</p>
                <button onclick="downloadCredentials()" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded text-sm font-medium">
                    📥 下载账号信息
                </button>
            </div>

            <!-- 伪静态配置（重要！） -->
            <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-6 text-left">
                <h3 class="font-bold text-blue-800 mb-2 flex items-center">
                    <span class="text-xl mr-2">⚙️</span>
                    伪静态配置
                    <span class="ml-2 px-2 py-0.5 bg-red-500 text-white text-xs rounded">必须配置</span>
                </h3>
                <p class="text-sm text-blue-700 mb-3">
                    检测到您的服务器为 <strong><?= htmlspecialchars($serverTypeName) ?></strong>，
                    请复制以下规则到您的服务器配置中，否则无法正常访问后台和前台页面。
                </p>
                
                <!-- 服务器类型切换 -->
                <div class="flex space-x-2 mb-3">
                    <button type="button" onclick="switchRewriteRules('nginx')" id="btn-nginx" 
                        class="px-3 py-1 rounded text-sm font-medium <?= $serverType === 'nginx' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                        Nginx
                    </button>
                    <button type="button" onclick="switchRewriteRules('apache')" id="btn-apache"
                        class="px-3 py-1 rounded text-sm font-medium <?= $serverType === 'apache' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                        Apache
                    </button>
                </div>

                <!-- 规则内容 -->
                <div class="relative">
                    <pre id="rewrite-rules" class="bg-gray-900 text-green-400 p-3 rounded text-xs font-mono overflow-x-auto max-h-48 whitespace-pre"><?= $serverType === 'nginx' ? htmlspecialchars("# 香蕉CMS Nginx 伪静态配置
# 后台入口: /{$adminEntry}

location = /sitemap.xml {
    rewrite ^ /sitemap.php last;
}

location ~ ^/(config|core|models|controllers|views|runtime)/ {
    deny all;
}

location /static/ {
    try_files \$uri =404;
}

location /upload/ {
    try_files \$uri =404;
}

location / {
    try_files \$uri \$uri/ /index.php?s=\$uri&\$args;
}") : htmlspecialchars("# 香蕉CMS Apache 伪静态配置
# 后台入口: /{$adminEntry}

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    RewriteRule ^sitemap\\.xml\$ sitemap.php [QSA,L]
    
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)\$ index.php?s=\$1 [QSA,L]
</IfModule>

<IfModule mod_negotiation.c>
    Options -MultiViews
</IfModule>

AcceptPathInfo On

# 禁止访问敏感目录
<FilesMatch \"^(config|core|models|controllers|views|runtime)\">
    Order deny,allow
    Deny from all
</FilesMatch>") ?></pre>
                    <button type="button" onclick="copyRewriteRules()" 
                        class="absolute top-2 right-2 px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white text-xs rounded">
                        📋 复制
                    </button>
                </div>

                <!-- 使用说明 -->
                <div class="mt-3 text-xs text-blue-600">
                    <div id="usage-nginx" class="<?= $serverType !== 'nginx' ? 'hidden' : '' ?>">
                        <p><strong>宝塔面板：</strong>网站设置 → 伪静态 → 粘贴规则 → 保存</p>
                        <p><strong>其他环境：</strong>将规则添加到 nginx.conf 的 server 块中</p>
                    </div>
                    <div id="usage-apache" class="<?= $serverType !== 'apache' ? 'hidden' : '' ?>">
                        <p><strong>方法1：</strong>将规则保存为 .htaccess 文件，上传到网站根目录</p>
                        <p><strong>方法2：</strong>宝塔面板 → 网站设置 → 伪静态 → 粘贴规则 → 保存</p>
                    </div>
                </div>
            </div>
            
            <!-- 安全提示 -->
            <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-6 text-left">
                <h3 class="font-bold text-yellow-800 mb-2">⚠️ 安全提示</h3>
                <p class="text-sm text-yellow-700 mb-3">为了网站安全，建议删除以下敏感文件（防止被扫描下载）：</p>
                <div id="deleteFiles" class="space-y-2 max-h-64 overflow-y-auto">
                    <?php 
                    $sensitiveFiles = getSensitiveFiles();
                    $fileDescriptions = [
                        'install.php' => '安装向导',
                        'data.sql' => '数据库结构',
                        'README.md' => '项目说明',
                        'README.en.md' => '英文说明',
                        '部署指南.md' => '部署文档',
                        '模板制作.md' => '模板文档',
                        '备注.txt' => '开发备注',
                    ];
                    foreach ($sensitiveFiles as $file): 
                        $exists = file_exists(ROOT_PATH . $file);
                        $desc = $fileDescriptions[$file] ?? pathinfo($file, PATHINFO_EXTENSION) . '文件';
                    ?>
                    <div class="flex items-center justify-between bg-white rounded px-3 py-2 border" id="file-<?= md5($file) ?>">
                        <div class="flex items-center">
                            <span class="text-sm <?= $exists ? 'text-gray-700' : 'text-gray-400 line-through' ?>"><?= htmlspecialchars($file) ?></span>
                            <span class="text-xs text-gray-400 ml-2">(<?= htmlspecialchars($desc) ?>)</span>
                        </div>
                        <?php if ($exists): ?>
                        <button onclick="deleteFile('<?= htmlspecialchars($file, ENT_QUOTES) ?>', '<?= md5($file) ?>')" class="text-xs bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">删除</button>
                        <?php else: ?>
                        <span class="text-xs text-green-500">已删除</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($sensitiveFiles)): ?>
                    <div class="text-center text-green-600 py-4">✓ 所有敏感文件已清理完毕</div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($sensitiveFiles)): ?>
                <button onclick="deleteAllFiles()" class="mt-3 w-full text-sm bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded font-bold">🗑️ 一键删除所有敏感文件</button>
                <?php endif; ?>
            </div>
            
            <div class="flex justify-center space-x-4">
                <a href="/" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded font-bold">访问首页</a>
                <a href="/<?= htmlspecialchars($adminEntry) ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded font-bold">进入后台</a>
            </div>
        </div>
        
        <?php
        // 生成文件名用的域名（去掉www）
        $host = parse_url($siteUrl, PHP_URL_HOST) ?: $_SERVER['HTTP_HOST'];
        $filenameDomain = preg_replace('/^www\./i', '', $host);
        
        // 转义 JS 字符串中的特殊字符（用于模板字符串）
        $jsAdminPass = addslashes($adminPass);
        $jsAdminPass = str_replace(['`', '${'], ['\\`', '\\${'], $jsAdminPass);
        
        // 如果密码为空，显示提示
        $displayPass = !empty($adminPass) ? $jsAdminPass : '（密码未保存，请使用您设置的密码）';
        ?>
        <script>
        // 账号信息下载
        function downloadCredentials() {
            const content = `========================================
香蕉CMS 后台账号信息
========================================

后台地址：<?= htmlspecialchars($fullAdminUrl) ?>

管理员账号：<?= htmlspecialchars($adminUser) ?>

管理员密码：<?= $displayPass ?>

========================================
⚠️ 重要提示：
1. 请妥善保管此文件，切勿泄露给他人
2. 建议登录后台后立即修改密码
3. 此文件建议阅读后删除
========================================

安装时间：<?= date('Y-m-d H:i:s') ?>
`;
            
            const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = '<?= htmlspecialchars($filenameDomain) ?>.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showToast('账号信息已下载，请妥善保管', 'success');
        }

        // 页面加载后自动下载
        window.addEventListener('load', function() {
            setTimeout(downloadCredentials, 500);
        });

        // 伪静态规则
        const rewriteRules = {
            nginx: `# 香蕉CMS Nginx 伪静态配置
# 后台入口: /<?= htmlspecialchars($adminEntry) ?>

location = /sitemap.xml {
    rewrite ^ /sitemap.php last;
}

location ~ ^/(config|core|models|controllers|views|runtime)/ {
    deny all;
}

location /static/ {
    try_files \\$uri =404;
}

location /upload/ {
    try_files \\$uri =404;
}

location / {
    try_files \\$uri \\$uri/ /index.php?s=\\$uri&\\$args;
}`,
            apache: `# 香蕉CMS Apache 伪静态配置
# 后台入口: /<?= htmlspecialchars($adminEntry) ?>

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    RewriteRule ^sitemap\\.xml$ sitemap.php [QSA,L]
    
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)\\$ index.php?s=\\$1 [QSA,L]
</IfModule>

<IfModule mod_negotiation.c>
    Options -MultiViews
</IfModule>

AcceptPathInfo On

# 禁止访问敏感目录
<FilesMatch "^(config|core|models|controllers|views|runtime)">
    Order deny,allow
    Deny from all
</FilesMatch>`
        };

        function switchRewriteRules(type) {
            document.getElementById('rewrite-rules').textContent = rewriteRules[type];
            
            // 切换按钮样式
            document.getElementById('btn-nginx').className = type === 'nginx' 
                ? 'px-3 py-1 rounded text-sm font-medium bg-blue-600 text-white'
                : 'px-3 py-1 rounded text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300';
            document.getElementById('btn-apache').className = type === 'apache'
                ? 'px-3 py-1 rounded text-sm font-medium bg-blue-600 text-white'
                : 'px-3 py-1 rounded text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300';
            
            // 切换使用说明
            document.getElementById('usage-nginx').classList.toggle('hidden', type !== 'nginx');
            document.getElementById('usage-apache').classList.toggle('hidden', type !== 'apache');
        }

        function copyRewriteRules() {
            const rules = document.getElementById('rewrite-rules').textContent;
            navigator.clipboard.writeText(rules).then(() => {
                showToast('已复制到剪贴板', 'success');
            }).catch(() => {
                // 降级方案
                const textarea = document.createElement('textarea');
                textarea.value = rules;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                showToast('已复制到剪贴板', 'success');
            });
        }

        function deleteFile(file, id) {
            if (!confirm('确定要删除 ' + file + ' 吗？')) return;
            
            fetch('install.php?action=delete&file=' + encodeURIComponent(file))
                .then(r => r.json())
                .then(data => {
                    if (data.code === 0) {
                        const el = document.getElementById('file-' + id);
                        el.querySelector('span').classList.add('line-through', 'text-gray-400');
                        el.querySelector('span').classList.remove('text-gray-700');
                        el.querySelector('button').outerHTML = '<span class="text-xs text-green-500">已删除</span>';
                        showToast(file + ' 已删除', 'success');
                    } else {
                        showToast(data.msg || '删除失败', 'error');
                    }
                })
                .catch(() => showToast('删除失败', 'error'));
        }
        
        function deleteAllFiles() {
            if (!confirm('确定要删除所有敏感文件吗？\n\n包括：install.php、*.sql、*.md、*.txt、*.rar、*.zip\n\n删除后将无法重新安装！')) return;
            
            // 获取当前页面上所有待删除的文件
            const buttons = document.querySelectorAll('#deleteFiles button');
            const files = [];
            buttons.forEach(btn => {
                const onclick = btn.getAttribute('onclick');
                if (onclick && onclick.includes('deleteFile')) {
                    const match = onclick.match(/deleteFile\('([^']+)'/);
                    if (match) files.push(match[1]);
                }
            });
            
            if (files.length === 0) {
                showToast('没有需要删除的文件', 'success');
                return;
            }
            
            let deleted = 0;
            let errors = 0;
            
            files.forEach(file => {
                fetch('install.php?action=delete&file=' + encodeURIComponent(file))
                    .then(r => r.json())
                    .then(data => {
                        deleted++;
                        if (data.code !== 0) errors++;
                        
                        // 更新UI
                        const id = Array.from(document.querySelectorAll('#deleteFiles > div')).find(el => 
                            el.querySelector('span')?.textContent === file
                        )?.id;
                        if (id) {
                            const el = document.getElementById(id);
                            if (el) {
                                el.querySelector('span').classList.add('line-through', 'text-gray-400');
                                el.querySelector('span').classList.remove('text-gray-700');
                                const btn = el.querySelector('button');
                                if (btn) btn.outerHTML = '<span class="text-xs text-green-500">已删除</span>';
                            }
                        }
                        
                        if (deleted === files.length) {
                            if (errors > 0) {
                                showToast('部分文件删除失败，请检查权限', 'error');
                            } else {
                                showToast('所有敏感文件已删除', 'success');
                                setTimeout(() => location.href = '/' + '<?= htmlspecialchars($adminEntry) ?>', 1500);
                            }
                        }
                    })
                    .catch(() => {
                        deleted++;
                        errors++;
                    });
            });
        }
        </script>
        <?php endif; ?>
    </div>
    <div class="text-center mt-6 text-white/60 text-sm">Powered by <a href="https://xpornkit.com" class="text-white">香蕉CMS</a></div>
</div>
</body>
</html>
