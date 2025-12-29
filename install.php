<?php
/**
 * 香蕉CMS 安装向导
 * Powered by https://xpornkit.com
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', __DIR__ . '/');
define('CONFIG_PATH', ROOT_PATH . 'config/');

// 检查是否已安装
if (file_exists(CONFIG_PATH . 'install.lock')) {
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:sans-serif;text-align:center;padding:50px;"><h1>🍌 香蕉CMS</h1><p>系统已安装，如需重新安装请删除 config/install.lock</p><p><a href="/">首页</a> | <a href="/admin.php">后台</a></p></body></html>');
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
                $config = "<?php\ndefine('APP_DEBUG', true);\ndefine('APP_SECRET', '{$secret}');\n";
                $config .= "define('SITE_NAME', '{$siteName}');\ndefine('SITE_URL', '{$siteUrl}');\n";
                $config .= "define('SITE_KEYWORDS', '');\ndefine('SITE_DESCRIPTION', '');\n";
                $config .= "define('DB_HOST', '{$dbHost}');\ndefine('DB_PORT', '{$dbPort}');\n";
                $config .= "define('DB_NAME', '{$dbName}');\ndefine('DB_USER', '{$dbUser}');\n";
                $config .= "define('DB_PASS', '{$dbPass}');\ndefine('DB_CHARSET', 'utf8mb4');\n";
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
                
                file_put_contents(CONFIG_PATH . 'config.php', $config);
                file_put_contents(CONFIG_PATH . 'install.lock', date('Y-m-d H:i:s'));
                
                $_SESSION['install_admin'] = $adminUser;
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
                        <div><label class="block text-sm mb-1">密码 * (至少6字符)</label><input type="password" name="admin_pass" required minlength="6" class="w-full border rounded px-3 py-2"></div>
                        <div><label class="block text-sm mb-1">确认密码 *</label><input type="password" name="admin_pass_confirm" required class="w-full border rounded px-3 py-2"></div>
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

        <?php elseif ($step === 4): ?>
        <div class="text-center">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="text-2xl font-bold mb-4 text-green-600">安装成功！</h2>
            <div class="bg-gray-50 rounded p-6 mb-6">
                <p class="mb-2"><strong>管理员：</strong><?= htmlspecialchars($_SESSION['install_admin'] ?? '') ?></p>
                <p class="text-sm text-gray-500 mb-4">请牢记您设置的密码</p>
            </div>
            <div class="flex justify-center space-x-4">
                <a href="/" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded font-bold">访问首页</a>
                <a href="/admin.php" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded font-bold">进入后台</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="text-center mt-6 text-white/60 text-sm">Powered by <a href="https://xpornkit.com" class="text-white">香蕉CMS</a></div>
</div>
</body>
</html>
