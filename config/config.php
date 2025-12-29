<?php
/**
 * 香蕉CMS 配置文件
 * Powered by https://xpornkit.com
 */

// 调试模式
define('APP_DEBUG', true);

// 安全密钥
define('APP_SECRET', 'xpk_banana_' . md5(__FILE__));

// 站点信息
define('SITE_NAME', '香蕉影视');
define('SITE_URL', 'http://localhost');
define('SITE_KEYWORDS', '香蕉CMS,BananaCMS,免费影视CMS');
define('SITE_DESCRIPTION', '香蕉CMS - 轻量级影视内容管理系统');

// 数据库配置
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'bananacms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PREFIX', 'xpk_');

// 路径定义
define('ROOT_PATH', dirname(__DIR__) . '/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('CORE_PATH', ROOT_PATH . 'core/');
define('MODEL_PATH', ROOT_PATH . 'models/');
define('CTRL_PATH', ROOT_PATH . 'controllers/');
define('VIEW_PATH', ROOT_PATH . 'views/');
define('TPL_PATH', ROOT_PATH . 'template/');
define('STATIC_PATH', ROOT_PATH . 'static/');
define('UPLOAD_PATH', ROOT_PATH . 'upload/');
define('RUNTIME_PATH', ROOT_PATH . 'runtime/');

// 时区
date_default_timezone_set('Asia/Shanghai');

// 分页配置
define('PAGE_SIZE', 24);

// 后台入口（用于重定向，可自定义）
define('ADMIN_ENTRY', 'admin.php');

// 上传配置
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_ALLOW_EXT', 'jpg,jpeg,png,gif,webp');

// 缓存配置（file 或 redis）
define('CACHE_DRIVER', 'file');
define('CACHE_TTL', 3600);

// Session配置（file 或 redis）
define('SESSION_DRIVER', 'file');
define('SESSION_TTL', 7200);

// Redis配置（当CACHE_DRIVER或SESSION_DRIVER为redis时生效）
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_PASS', '');           // Redis密码，无密码留空
define('REDIS_DB', 0);              // 缓存使用的数据库
define('REDIS_SESSION_DB', 1);      // Session使用的数据库（建议与缓存分开）
define('REDIS_PREFIX', 'xpk:');     // 缓存键前缀

// 存储配置（local 或 r2）
define('STORAGE_DRIVER', 'local');

// Cloudflare R2 配置（当STORAGE_DRIVER为r2时生效）
define('R2_ACCOUNT_ID', '');        // Cloudflare Account ID
define('R2_ACCESS_KEY_ID', '');     // R2 Access Key ID
define('R2_SECRET_ACCESS_KEY', ''); // R2 Secret Access Key
define('R2_BUCKET', '');            // Bucket名称
define('R2_PUBLIC_URL', '');        // 公开访问域名（如 https://cdn.example.com）
