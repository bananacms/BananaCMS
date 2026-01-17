<?php
/**
 * 香蕉CMS 配置文件
 * Powered by https://xpornkit.com
 * 
 * 支持环境变量配置（用于 Docker 部署）
 * 环境变量优先级高于硬编码值
 */

// 调试模式
define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN));

// 安全密钥
define('APP_SECRET', getenv('APP_SECRET') ?: 'xpk_banana_' . md5(__FILE__));

// 管理员IP混淆盐值（用于生成伪造IP，请修改为随机字符串）
define('ADMIN_IP_SALT', getenv('ADMIN_IP_SALT') ?: 'BananaCMS_Admin_Security_' . md5('banana_ip_salt_' . __FILE__));

// 站点信息
define('SITE_NAME', getenv('SITE_NAME') ?: '香蕉影视');
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost');
define('SITE_KEYWORDS', getenv('SITE_KEYWORDS') ?: '香蕉CMS,BananaCMS,免费影视CMS');
define('SITE_DESCRIPTION', getenv('SITE_DESCRIPTION') ?: '香蕉CMS - 轻量级影视内容管理系统');

// 数据库配置
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));
define('DB_NAME', getenv('DB_NAME') ?: 'bananacms');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
define('DB_PREFIX', getenv('DB_PREFIX') ?: 'xpk_');

// Redis 配置（可选）
define('REDIS_HOST', getenv('REDIS_HOST') ?: '127.0.0.1');
define('REDIS_PORT', (int)(getenv('REDIS_PORT') ?: 6379));
define('REDIS_PASS', getenv('REDIS_PASS') ?: '');
define('REDIS_DB', (int)(getenv('REDIS_DB') ?: 0));

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
date_default_timezone_set(getenv('TIMEZONE') ?: 'Asia/Shanghai');

// 分页配置
define('PAGE_SIZE', (int)(getenv('PAGE_SIZE') ?: 24));

// 后台入口（用于重定向，可自定义）
define('ADMIN_ENTRY', getenv('ADMIN_ENTRY') ?: 'admin');

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

// 定义根标识常量（用于防止直接访问配置文件）
define('XPK_ROOT', true);

// 加载常量定义
require_once CONFIG_PATH . 'constants.php';
