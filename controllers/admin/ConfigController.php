<?php
/**
 * 后台系统配置控制器
 * Powered by https://xpornkit.com
 */

class AdminConfigController extends AdminBaseController
{
    /**
     * 配置页面
     */
    public function index(): void
    {
        $db = XpkDatabase::getInstance();
        $configs = $db->query("SELECT * FROM " . DB_PREFIX . "config");
        
        $configMap = [];
        foreach ($configs as $config) {
            $configMap[$config['config_name']] = $config['config_value'];
        }

        // 获取可用模板列表
        $templates = $this->getTemplateList();

        $this->assign('config', $configMap);
        $this->assign('templates', $templates);
        $this->assign('csrfToken', $this->csrfToken());
        $this->assign('flash', $this->getFlash());
        $this->render('config/index', '系统配置');
    }

    /**
     * 获取模板列表
     */
    private function getTemplateList(): array
    {
        $templates = [];
        $tplPath = TPL_PATH;
        
        if (!is_dir($tplPath)) {
            return $templates;
        }
        
        $dirs = scandir($tplPath);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            
            $dirPath = $tplPath . $dir;
            if (!is_dir($dirPath)) continue;
            
            // 检查是否是有效模板（包含index目录或index.html）
            $isValid = is_dir($dirPath . '/index') || file_exists($dirPath . '/index/index.html');
            
            $templates[] = [
                'name' => $dir,
                'path' => $dirPath,
                'valid' => $isValid,
                'size' => $this->getDirSize($dirPath),
                'mtime' => filemtime($dirPath)
            ];
        }
        
        return $templates;
    }

    /**
     * 获取目录大小
     */
    private function getDirSize(string $dir): int
    {
        $size = 0;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($files as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    /**
     * 保存配置
     */
    public function save(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $db = XpkDatabase::getInstance();
        $configs = [
            'site_name' => trim($this->post('site_name', '')),
            'site_url' => trim($this->post('site_url', '')),
            'site_keywords' => trim($this->post('site_keywords', '')),
            'site_description' => trim($this->post('site_description', '')),
            'site_icp' => trim($this->post('site_icp', '')),
            'site_logo' => trim($this->post('site_logo', '')),
            'site_template' => trim($this->post('site_template', 'default')),
            'site_status' => trim($this->post('site_status', '1')),
            'site_close_tip' => trim($this->post('site_close_tip', '')),
            // 调试模式
            'app_debug' => trim($this->post('app_debug', '0')),
            // 用户设置
            'user_register' => trim($this->post('user_register', '1')),
            'user_register_limit' => trim($this->post('user_register_limit', '5')),
            // 导航设置
            'nav_type_limit' => trim($this->post('nav_type_limit', '10')),
            // URL规则
            'url_mode' => trim($this->post('url_mode', '4')),
            'url_vod_detail' => trim($this->post('url_vod_detail', '')),
            'url_vod_play' => trim($this->post('url_vod_play', '')),
            'url_type' => trim($this->post('url_type', '')),
            'url_actor_detail' => trim($this->post('url_actor_detail', '')),
            'url_art_detail' => trim($this->post('url_art_detail', '')),
            // 统计代码
            'site_analytics' => $this->post('site_analytics', ''),
            // SEO模板
            'seo_title_vod_detail' => trim($this->post('seo_title_vod_detail', '')),
            'seo_keywords_vod_detail' => trim($this->post('seo_keywords_vod_detail', '')),
            'seo_description_vod_detail' => trim($this->post('seo_description_vod_detail', '')),
            'seo_title_type' => trim($this->post('seo_title_type', '')),
            'seo_keywords_type' => trim($this->post('seo_keywords_type', '')),
            'seo_title_actor_detail' => trim($this->post('seo_title_actor_detail', '')),
            'seo_title_art_detail' => trim($this->post('seo_title_art_detail', '')),
        ];

        foreach ($configs as $name => $value) {
            $exists = $db->queryOne(
                "SELECT config_id FROM " . DB_PREFIX . "config WHERE config_name = ?",
                [$name]
            );

            if ($exists) {
                $db->execute(
                    "UPDATE " . DB_PREFIX . "config SET config_value = ? WHERE config_name = ?",
                    [$value, $name]
                );
            } else {
                $db->execute(
                    "INSERT INTO " . DB_PREFIX . "config (config_name, config_value) VALUES (?, ?)",
                    [$name, $value]
                );
            }
        }

        // 保存 Redis 配置到 config.php
        $this->saveRedisConfig();

        // 清除所有缓存（包括模板编译缓存）
        xpk_cache()->clear();
        $this->clearTemplateCache();

        $this->log('修改', '配置', '更新站点配置');
        $this->flash('success', '保存成功');
        $this->redirect('/' . $this->adminEntry . '?s=config');
    }

    /**
     * 清除模板编译缓存
     */
    private function clearTemplateCache(): void
    {
        $cachePath = RUNTIME_PATH . 'cache/';
        if (!is_dir($cachePath)) {
            return;
        }
        
        // 清除所有 .php 编译缓存文件
        $files = glob($cachePath . '*.php');
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    /**
     * 保存 Redis 配置到 config.php
     */
    private function saveRedisConfig(): void
    {
        $cacheDriver = trim($this->post('cache_driver', ''));
        $sessionDriver = trim($this->post('session_driver', ''));
        
        // 如果没有提交 Redis 配置，跳过
        if (empty($cacheDriver) && empty($sessionDriver)) {
            return;
        }
        
        $redisHost = trim($this->post('redis_host', '127.0.0.1'));
        $redisPort = (int)$this->post('redis_port', 6379);
        $redisPass = $this->post('redis_pass', '');
        $redisDb = (int)$this->post('redis_db', 0);
        $redisSessionDb = (int)$this->post('redis_session_db', 1);
        $redisPrefix = trim($this->post('redis_prefix', 'xpk:'));
        
        $configFile = CONFIG_PATH . 'config.php';
        if (!file_exists($configFile) || !is_writable($configFile)) {
            return;
        }
        
        $content = file_get_contents($configFile);
        
        // 更新或添加配置项
        $replacements = [
            'CACHE_DRIVER' => $cacheDriver,
            'SESSION_DRIVER' => $sessionDriver,
            'REDIS_HOST' => $redisHost,
            'REDIS_PORT' => $redisPort,
            'REDIS_PASS' => $redisPass,
            'REDIS_DB' => $redisDb,
            'REDIS_SESSION_DB' => $redisSessionDb,
            'REDIS_PREFIX' => $redisPrefix,
        ];
        
        foreach ($replacements as $key => $value) {
            // 根据值类型决定格式
            if (is_int($value)) {
                $valueStr = $value;
            } else {
                $valueStr = "'" . addslashes($value) . "'";
            }
            
            // 尝试替换现有配置
            $pattern = "/define\('{$key}',\s*[^)]+\);/";
            $replacement = "define('{$key}', {$valueStr});";
            
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                // 如果不存在，在文件末尾添加（在最后一个 require 之前）
                $insertPos = strrpos($content, 'require_once');
                if ($insertPos !== false) {
                    $content = substr($content, 0, $insertPos) . $replacement . "\n" . substr($content, $insertPos);
                }
            }
        }
        
        file_put_contents($configFile, $content);
    }

    /**
     * 测试 Redis 连接
     */
    public function testRedis(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }
        
        if (!extension_loaded('redis')) {
            $this->error('Redis 扩展未安装');
        }
        
        $host = trim($this->post('redis_host', '127.0.0.1'));
        $port = (int)$this->post('redis_port', 6379);
        $pass = $this->post('redis_pass', '');
        $db = (int)$this->post('redis_db', 0);
        
        try {
            $redis = new \Redis();
            $connected = @$redis->connect($host, $port, 3);
            
            if (!$connected) {
                $this->error('无法连接到 Redis 服务器');
            }
            
            if (!empty($pass)) {
                if (!$redis->auth($pass)) {
                    $this->error('Redis 密码错误');
                }
            }
            
            $redis->select($db);
            
            // 测试读写
            $testKey = 'xpk_test_' . time();
            $redis->set($testKey, 'test', 5);
            $value = $redis->get($testKey);
            $redis->del($testKey);
            
            if ($value !== 'test') {
                $this->error('Redis 读写测试失败');
            }
            
            $info = $redis->info('server');
            $version = $info['redis_version'] ?? '未知';
            
            $redis->close();
            
            $this->success("连接成功，Redis 版本: {$version}");
            
        } catch (\Exception $e) {
            $this->error('连接失败: ' . $e->getMessage());
        }
    }

    /**
     * 上传文件（Logo等）
     */
    public function upload(): void
    {
        // 检查是否有文件上传
        if (empty($_FILES['file'])) {
            // 检查是否是POST大小超限导致的
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)) {
                $maxSize = ini_get('post_max_size');
                $this->error("上传失败：文件大小超过服务器限制（post_max_size={$maxSize}）");
            }
            $this->error('请选择文件');
        }

        $file = $_FILES['file'];
        $type = $_POST['type'] ?? 'image';

        // 检查上传错误
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => '文件大小超过服务器限制（upload_max_filesize=' . ini_get('upload_max_filesize') . '）',
                UPLOAD_ERR_FORM_SIZE => '文件大小超过表单限制',
                UPLOAD_ERR_PARTIAL => '文件只上传了一部分',
                UPLOAD_ERR_NO_FILE => '没有文件被上传',
                UPLOAD_ERR_NO_TMP_DIR => '服务器临时目录不存在',
                UPLOAD_ERR_CANT_WRITE => '服务器写入失败',
                UPLOAD_ERR_EXTENSION => '上传被PHP扩展阻止',
            ];
            $msg = $errorMessages[$file['error']] ?? "未知错误（错误码：{$file['error']}）";
            $this->error("上传失败：{$msg}");
        }

        // 检查临时文件是否存在
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->error('上传失败：临时文件不存在');
        }

        // 检查大小（2MB）
        if ($file['size'] > 2 * 1024 * 1024) {
            $this->error('文件大小不能超过2MB');
        }

        // 检查文件类型 - 多种方式验证
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // 获取扩展名
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            $this->error("不支持的文件格式：{$ext}，只支持 JPG/PNG/GIF/WEBP");
        }

        // 检测MIME类型（多种方式）
        $mimeType = null;
        
        // 方式1：使用finfo（推荐）
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
        // 方式2：使用mime_content_type
        elseif (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($file['tmp_name']);
        }
        // 方式3：使用getimagesize（仅图片）
        elseif (function_exists('getimagesize')) {
            $imageInfo = @getimagesize($file['tmp_name']);
            if ($imageInfo) {
                $mimeType = $imageInfo['mime'];
            }
        }

        // 验证MIME类型
        if ($mimeType && !in_array($mimeType, $allowedTypes)) {
            $this->error("文件类型不正确：{$mimeType}，只支持 JPG/PNG/GIF/WEBP");
        }

        // 额外验证：检查是否是真实图片
        if (function_exists('getimagesize')) {
            $imageInfo = @getimagesize($file['tmp_name']);
            if (!$imageInfo) {
                $this->error('文件不是有效的图片');
            }
        }

        // 生成文件名
        if ($type === 'logo') {
            $remotePath = 'config/logo.' . $ext;
        } else {
            $remotePath = date('Y/m/d/') . md5(uniqid(mt_rand(), true)) . '.' . $ext;
        }

        // 使用存储类上传
        require_once CORE_PATH . 'Storage.php';
        
        try {
            $storage = xpk_storage();
            $url = $storage->upload($file['tmp_name'], $remotePath);
            
            // Logo加时间戳防缓存
            if ($type === 'logo') {
                $url .= '?t=' . time();
            }
            
            $this->log('上传', '文件', $remotePath);
            $this->success('上传成功', ['url' => $url]);
        } catch (Exception $e) {
            $this->error('上传失败：' . $e->getMessage());
        }
    }

    /**
     * 上传模板（ZIP格式）
     */
    public function uploadTemplate(): void
    {
        if (empty($_FILES['file'])) {
            $this->error('请选择模板文件');
        }

        $file = $_FILES['file'];

        // 检查错误
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->error('上传失败，错误码：' . $file['error']);
        }

        // 检查大小（50MB）
        if ($file['size'] > 50 * 1024 * 1024) {
            $this->error('模板文件不能超过50MB');
        }

        // 检查类型
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            $this->error('只支持ZIP格式的模板文件');
        }

        // 检查ZipArchive扩展
        if (!class_exists('ZipArchive')) {
            $this->error('服务器未安装ZIP扩展，无法解压模板');
        }

        $zip = new ZipArchive();
        if ($zip->open($file['tmp_name']) !== true) {
            $this->error('无法打开ZIP文件，文件可能已损坏');
        }

        // 检查ZIP内容，获取模板名称
        $templateName = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            // 查找第一级目录名
            if (strpos($name, '/') !== false) {
                $parts = explode('/', $name);
                if (!empty($parts[0]) && $parts[0] !== '.' && $parts[0] !== '..') {
                    $templateName = $parts[0];
                    break;
                }
            }
        }

        if (!$templateName) {
            // 如果没有目录，使用文件名作为模板名
            $templateName = pathinfo($file['name'], PATHINFO_FILENAME);
        }

        // 安全检查模板名
        $templateName = preg_replace('/[^a-zA-Z0-9_-]/', '', $templateName);
        if (empty($templateName)) {
            $templateName = 'template_' . time();
        }

        // 目标目录
        $targetDir = TPL_PATH . $templateName;

        // 如果目录已存在，添加时间戳
        if (is_dir($targetDir)) {
            $templateName = $templateName . '_' . date('His');
            $targetDir = TPL_PATH . $templateName;
        }

        // 创建临时目录解压
        $tempDir = RUNTIME_PATH . 'temp_tpl_' . uniqid();
        if (!mkdir($tempDir, 0755, true)) {
            $zip->close();
            $this->error('无法创建临时目录');
        }

        // 解压到临时目录
        if (!$zip->extractTo($tempDir)) {
            $zip->close();
            $this->removeDir($tempDir);
            $this->error('解压失败');
        }
        $zip->close();

        // 检查解压后的结构
        $extractedDirs = array_filter(scandir($tempDir), function($d) use ($tempDir) {
            return $d !== '.' && $d !== '..' && is_dir($tempDir . '/' . $d);
        });

        if (count($extractedDirs) === 1) {
            // 如果只有一个目录，使用该目录作为模板
            $sourceDir = $tempDir . '/' . reset($extractedDirs);
        } else {
            // 否则整个临时目录就是模板
            $sourceDir = $tempDir;
        }

        // 验证模板结构（必须有index目录）
        if (!is_dir($sourceDir . '/index')) {
            $this->removeDir($tempDir);
            $this->error('无效的模板结构，模板必须包含index目录');
        }

        // 移动到模板目录
        if (!rename($sourceDir, $targetDir)) {
            // 如果rename失败，尝试复制
            $this->copyDir($sourceDir, $targetDir);
        }

        // 清理临时目录
        if (is_dir($tempDir)) {
            $this->removeDir($tempDir);
        }

        $this->log('上传', '模板', $templateName);
        $this->success('模板上传成功', ['name' => $templateName]);
    }

    /**
     * 删除模板
     */
    public function deleteTemplate(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $name = trim($this->post('name', ''));
        
        // 安全检查
        if (empty($name) || $name === 'default') {
            $this->error('无法删除默认模板');
        }

        // 只允许字母数字下划线横线
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            $this->error('无效的模板名称');
        }

        $targetDir = TPL_PATH . $name;

        if (!is_dir($targetDir)) {
            $this->error('模板不存在');
        }

        // 检查是否正在使用
        $db = XpkDatabase::getInstance();
        $currentTemplate = $db->queryOne(
            "SELECT config_value FROM " . DB_PREFIX . "config WHERE config_name = 'site_template'"
        );
        
        if ($currentTemplate && $currentTemplate['config_value'] === $name) {
            $this->error('该模板正在使用中，请先切换到其他模板');
        }

        // 删除目录
        if (!$this->removeDir($targetDir)) {
            $this->error('删除失败，请检查目录权限');
        }

        $this->log('删除', '模板', $name);
        $this->success('模板删除成功');
    }

    /**
     * 递归删除目录
     */
    private function removeDir(string $dir): bool
    {
        if (!is_dir($dir)) {
            return true;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }
        return rmdir($dir);
    }

    /**
     * 递归复制目录
     */
    private function copyDir(string $src, string $dst): bool
    {
        if (!is_dir($src)) {
            return false;
        }

        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }

        $files = scandir($src);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            
            if (is_dir($srcPath)) {
                $this->copyDir($srcPath, $dstPath);
            } else {
                copy($srcPath, $dstPath);
            }
        }
        return true;
    }

    /**
     * 安全配置页面
     */
    public function security(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateSecurityConfig();
            return;
        }
        
        $config = $this->getSecurityConfig();
        $this->assign('config', $config);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('config/security', '安全配置');
    }

    /**
     * 更新安全配置
     */
    private function updateSecurityConfig(): void
    {
        $configs = [
            'security_csp_enabled' => $this->post('security_csp_enabled', '0'),
            'security_csp_script_src' => trim($this->post('security_csp_script_src', "'self' 'unsafe-inline'")),
            'security_csp_style_src' => trim($this->post('security_csp_style_src', "'self' 'unsafe-inline'")),
            'security_csp_img_src' => trim($this->post('security_csp_img_src', "'self' data: https:")),
            'security_frame_options' => $this->post('security_frame_options', 'SAMEORIGIN'),
            'security_xss_protection' => $this->post('security_xss_protection', '1'),
            'security_referrer_policy' => $this->post('security_referrer_policy', 'strict-origin-when-cross-origin'),
            'security_hsts_max_age' => (int)$this->post('security_hsts_max_age', 31536000),
            'security_hsts_include_subdomains' => $this->post('security_hsts_include_subdomains', '1'),
            'security_hsts_preload' => $this->post('security_hsts_preload', '0'),
            'security_permissions_policy' => trim($this->post('security_permissions_policy', 'camera=(), microphone=(), geolocation=(), payment=()')),
            'security_coep_enabled' => $this->post('security_coep_enabled', '0'),
            'security_coop_enabled' => $this->post('security_coop_enabled', '0'),
            'security_coop_policy' => $this->post('security_coop_policy', 'same-origin'),
            'security_corp_enabled' => $this->post('security_corp_enabled', '0'),
            'security_corp_policy' => $this->post('security_corp_policy', 'same-origin'),
            'security_hide_server_info' => $this->post('security_hide_server_info', '1')
        ];
        
        foreach ($configs as $name => $value) {
            $this->db->execute(
                "INSERT INTO " . DB_PREFIX . "config (config_name, config_value) VALUES (?, ?) 
                 ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)",
                [$name, $value]
            );
        }
        
        // 清理安全配置缓存
        require_once CORE_PATH . 'Security.php';
        XpkSecurity::clearConfigCache();
        
        $this->jsonResponse(['code' => 0, 'msg' => '安全配置保存成功']);
    }

    /**
     * 获取安全配置
     */
    private function getSecurityConfig(): array
    {
        $rows = $this->db->query("SELECT config_name, config_value FROM " . DB_PREFIX . "config WHERE config_name LIKE 'security_%'");
        $config = [];
        foreach ($rows as $row) {
            $config[$row['config_name']] = $row['config_value'];
        }
        
        // 设置默认值
        $defaults = [
            'security_csp_enabled' => '1',
            'security_csp_script_src' => "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com",
            'security_csp_style_src' => "'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com",
            'security_csp_img_src' => "'self' data: https: http:",
            'security_frame_options' => 'SAMEORIGIN',
            'security_xss_protection' => '1',
            'security_referrer_policy' => 'strict-origin-when-cross-origin',
            'security_hsts_max_age' => '31536000',
            'security_hsts_include_subdomains' => '1',
            'security_hsts_preload' => '0',
            'security_permissions_policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
            'security_coep_enabled' => '0',
            'security_coop_enabled' => '0',
            'security_coop_policy' => 'same-origin',
            'security_corp_enabled' => '0',
            'security_corp_policy' => 'same-origin',
            'security_hide_server_info' => '1'
        ];
        
        return array_merge($defaults, $config);
    }
}
