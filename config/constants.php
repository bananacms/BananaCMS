<?php
/**
 * 系统常量配置文件
 * 统一管理所有硬编码的魔法字符串
 */

// 防止直接访问
if (!defined('XPK_ROOT')) {
    exit('Access denied');
}

// ========== 安全的文件检查函数 ==========
// 避免 open_basedir 限制错误

if (!function_exists('xpk_file_exists')) {
    function xpk_file_exists(string $path): bool {
        try {
            return @file_exists($path);
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('xpk_is_executable')) {
    function xpk_is_executable(string $path): bool {
        try {
            return @is_executable($path);
        } catch (Exception $e) {
            return false;
        }
    }
}

// ========== 系统基础常量 ==========

// 默认值常量
define('XPK_DEFAULT_TEMPLATE', 'default');
define('XPK_DEFAULT_PAGE_SIZE', 20);
define('XPK_DEFAULT_CACHE_TIME', 3600);
define('XPK_DEFAULT_SESSION_TIME', 86400);

// 用户相关常量
define('XPK_USER_STATUS_NORMAL', 1);
define('XPK_USER_STATUS_BANNED', 0);
define('XPK_USER_DEFAULT_GROUP', 1);
define('XPK_USER_ADMIN_GROUP', 9);

// 内容状态常量
define('XPK_VOD_STATUS_NORMAL', 1);
define('XPK_VOD_STATUS_HIDDEN', 0);
define('XPK_VOD_STATUS_PENDING', 2);

define('XPK_COMMENT_STATUS_NORMAL', 1);
define('XPK_COMMENT_STATUS_HIDDEN', 0);
define('XPK_COMMENT_STATUS_PENDING', 2);

// ========== 路径和URL常量 ==========

// 管理后台路径
define('XPK_ADMIN_PATH', 'admin');
define('XPK_API_PATH', 'api');

// 用户相关路径
define('XPK_USER_LOGIN_PATH', '/user/login');
define('XPK_USER_REGISTER_PATH', '/user/register');
define('XPK_USER_CENTER_PATH', '/user/center');
define('XPK_USER_LOGOUT_PATH', '/user/logout');

// 内容相关路径
define('XPK_VOD_DETAIL_PATH', '/vod/detail');
define('XPK_VOD_PLAY_PATH', '/vod/play');
define('XPK_VOD_TYPE_PATH', '/vod/type');

// ========== 数据库表名常量 ==========

define('XPK_TABLE_USER', 'xpk_user');
define('XPK_TABLE_VOD', 'xpk_vod');
define('XPK_TABLE_TYPE', 'xpk_type');
define('XPK_TABLE_COMMENT', 'xpk_comment');
define('XPK_TABLE_SCORE', 'xpk_score');
define('XPK_TABLE_AD', 'xpk_ad');
define('XPK_TABLE_LINK', 'xpk_link');
define('XPK_TABLE_PAGE', 'xpk_page');
define('XPK_TABLE_CONFIG', 'xpk_config');

// ========== 缓存键名常量 ==========

define('XPK_CACHE_CONFIG', 'config');
define('XPK_CACHE_TYPES', 'types');
define('XPK_CACHE_NAV_TYPES', 'nav_types');
define('XPK_CACHE_HOT_VODS', 'hot_vods');
define('XPK_CACHE_NEW_VODS', 'new_vods');
define('XPK_CACHE_USER_PREFIX', 'user_');
define('XPK_CACHE_VOD_PREFIX', 'vod_');

// ========== 文件和目录常量 ==========

define('XPK_UPLOAD_DIR', 'upload');
define('XPK_STATIC_DIR', 'static');
define('XPK_TEMPLATE_DIR', 'template');
define('XPK_RUNTIME_DIR', 'runtime');

// 允许的文件类型
define('XPK_ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('XPK_ALLOWED_VIDEO_TYPES', ['mp4', 'avi', 'mkv', 'mov', 'wmv']);

// ========== API响应码常量 ==========

define('XPK_API_SUCCESS', 0);
define('XPK_API_ERROR', 1);
define('XPK_API_AUTH_REQUIRED', 2);
define('XPK_API_PERMISSION_DENIED', 3);
define('XPK_API_INVALID_PARAMS', 4);
define('XPK_API_NOT_FOUND', 404);
define('XPK_API_SERVER_ERROR', 500);

// ========== 分页相关常量 ==========

define('XPK_PAGE_SIZE_SMALL', 10);
define('XPK_PAGE_SIZE_MEDIUM', 20);
define('XPK_PAGE_SIZE_LARGE', 50);
define('XPK_MAX_PAGE_SIZE', 100);

// ========== 内容限制常量 ==========

define('XPK_MAX_TITLE_LENGTH', 255);
define('XPK_MAX_CONTENT_LENGTH', 10000);
define('XPK_MAX_COMMENT_LENGTH', 500);
define('XPK_MAX_USERNAME_LENGTH', 50);
define('XPK_MIN_PASSWORD_LENGTH', 6);
define('XPK_MAX_DESCRIPTION_LENGTH', 160);

// ========== 评分相关常量 ==========

define('XPK_SCORE_MIN', 1);
define('XPK_SCORE_MAX', 10);
define('XPK_SCORE_DEFAULT', 5);

// ========== 广告位置常量 ==========

define('XPK_AD_POSITION_HOME_TOP', 'home_top');
define('XPK_AD_POSITION_HOME_BOTTOM', 'home_bottom');
define('XPK_AD_POSITION_DETAIL_TOP', 'detail_top');
define('XPK_AD_POSITION_DETAIL_BOTTOM', 'detail_bottom');
define('XPK_AD_POSITION_FLOAT', 'home_float');
define('XPK_AD_POSITION_POPUP', 'popup');

// ========== 时间格式常量 ==========

define('XPK_DATE_FORMAT', 'Y-m-d');
define('XPK_DATETIME_FORMAT', 'Y-m-d H:i:s');
define('XPK_TIME_FORMAT', 'H:i:s');

// ========== 正则表达式常量 ==========

define('XPK_REGEX_EMAIL', '/^[^\s@]+@[^\s@]+\.[^\s@]+$/');
define('XPK_REGEX_USERNAME', '/^[a-zA-Z0-9_]{3,20}$/');
define('XPK_REGEX_SLUG', '/^[a-zA-Z0-9-_]+$/');
define('XPK_REGEX_URL', '/^https?:\/\/.+/');

// ========== 消息类型常量 ==========

define('XPK_MSG_SUCCESS', 'success');
define('XPK_MSG_ERROR', 'error');
define('XPK_MSG_WARNING', 'warning');
define('XPK_MSG_INFO', 'info');

// ========== 排序相关常量 ==========

define('XPK_ORDER_TIME_DESC', 'time_desc');
define('XPK_ORDER_TIME_ASC', 'time_asc');
define('XPK_ORDER_HITS_DESC', 'hits_desc');
define('XPK_ORDER_SCORE_DESC', 'score_desc');
define('XPK_ORDER_NAME_ASC', 'name_asc');

// ========== 搜索相关常量 ==========

define('XPK_SEARCH_TYPE_ALL', 'all');
define('XPK_SEARCH_TYPE_VOD', 'vod');
define('XPK_SEARCH_TYPE_USER', 'user');
define('XPK_SEARCH_MIN_LENGTH', 2);
define('XPK_SEARCH_MAX_LENGTH', 100);

// ========== 安全相关常量 ==========

define('XPK_CSRF_TOKEN_NAME', '_token');
define('XPK_SESSION_NAME', 'XPK_SESSION');
define('XPK_COOKIE_PREFIX', 'xpk_');
define('XPK_MAX_LOGIN_ATTEMPTS', 5);
define('XPK_LOGIN_LOCKOUT_TIME', 900); // 15分钟

// ========== 模板相关常量 ==========

class XpkTemplateConstants {
    // 模板名称
    const DEFAULT_TEMPLATE = 'default';
    const NETFLIX_TEMPLATE = 'netflix';
    const IQIYI_TEMPLATE = 'iqiyi';
    const BILIBILI_TEMPLATE = 'bilibili';
    const DOUBAN_TEMPLATE = 'douban';
    
    // 布局文件
    const LAYOUT_HEADER = 'layouts/header.php';
    const LAYOUT_FOOTER = 'layouts/footer.php';
    const LAYOUT_SIDEBAR = 'layouts/sidebar.php';
    
    // 页面模板
    const PAGE_INDEX = 'index/index.html';
    const PAGE_VOD_DETAIL = 'vod/detail.html';
    const PAGE_VOD_PLAY = 'vod/play.html';
    const PAGE_VOD_TYPE = 'vod/type.html';
    const PAGE_USER_LOGIN = 'user/login.html';
    const PAGE_USER_REGISTER = 'user/register.html';
    const PAGE_USER_CENTER = 'user/center.html';
}

// ========== 控制器相关常量 ==========

class XpkControllerConstants {
    // 控制器名称
    const HOME_CONTROLLER = 'HomeController';
    const VOD_CONTROLLER = 'VodController';
    const USER_CONTROLLER = 'UserController';
    const COMMENT_CONTROLLER = 'CommentController';
    const SCORE_CONTROLLER = 'ScoreController';
    const ADMIN_CONTROLLER = 'AdminController';
    
    // 动作名称
    const ACTION_INDEX = 'index';
    const ACTION_DETAIL = 'detail';
    const ACTION_PLAY = 'play';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_REGISTER = 'register';
    const ACTION_ADD = 'add';
    const ACTION_EDIT = 'edit';
    const ACTION_DELETE = 'delete';
    const ACTION_LIST = 'list';
}

// ========== 配置键名常量 ==========

class XpkConfigKeys {
    // 站点配置
    const SITE_NAME = 'site_name';
    const SITE_KEYWORDS = 'site_keywords';
    const SITE_DESCRIPTION = 'site_description';
    const SITE_LOGO = 'site_logo';
    const SITE_ICP = 'site_icp';
    const SITE_URL = 'site_url';
    const SITE_STATUS = 'site_status';
    const SITE_CLOSE_TIP = 'site_close_tip';
    
    // 系统配置
    const SYSTEM_TEMPLATE = 'system_template';
    const SYSTEM_CACHE_TIME = 'system_cache_time';
    const SYSTEM_PAGE_SIZE = 'system_page_size';
    const SYSTEM_TIMEZONE = 'system_timezone';
    
    // 导航配置
    const NAV_TYPE_LIMIT = 'nav_type_limit';
    const URL_MODE = 'url_mode';
    
    // 用户配置
    const USER_REGISTER_ENABLED = 'user_register_enabled';
    const USER_EMAIL_REQUIRED = 'user_email_required';
    const USER_DEFAULT_GROUP = 'user_default_group';
    
    // 内容配置
    const CONTENT_AUDIT_ENABLED = 'content_audit_enabled';
    const COMMENT_ENABLED = 'comment_enabled';
    const SCORE_ENABLED = 'score_enabled';
    
    // 安全配置
    const SECURITY_CSRF_ENABLED = 'security_csrf_enabled';
    const SECURITY_XSS_FILTER = 'security_xss_filter';
    const SECURITY_SQL_FILTER = 'security_sql_filter';
}

// ========== 错误消息常量 ==========

class XpkErrorMessages {
    // 通用错误
    const INVALID_REQUEST = '无效的请求';
    const ACCESS_DENIED = '访问被拒绝';
    const NOT_FOUND = '页面不存在';
    const SERVER_ERROR = '服务器内部错误';
    const CSRF_FAILED = '安全验证失败，请刷新页面重试';
    
    // 用户相关错误
    const USER_NOT_FOUND = '用户不存在';
    const USER_LOGIN_REQUIRED = '请先登录';
    const USER_PERMISSION_DENIED = '权限不足';
    const USER_BANNED = '账户已被禁用';
    
    // 表单验证错误
    const FORM_INVALID_EMAIL = '邮箱格式不正确';
    const FORM_INVALID_USERNAME = '用户名格式不正确';
    const FORM_PASSWORD_TOO_SHORT = '密码长度不能少于6位';
    const FORM_REQUIRED_FIELD = '此字段为必填项';
    
    // 内容相关错误
    const CONTENT_NOT_FOUND = '内容不存在';
    const CONTENT_ACCESS_DENIED = '无权访问此内容';
    const COMMENT_TOO_LONG = '评论内容过长';
    const SCORE_INVALID_RANGE = '评分必须在1-10之间';
}

// ========== 成功消息常量 ==========

class XpkSuccessMessages {
    const LOGIN_SUCCESS = '登录成功';
    const LOGOUT_SUCCESS = '退出成功';
    const REGISTER_SUCCESS = '注册成功';
    const SAVE_SUCCESS = '保存成功';
    const DELETE_SUCCESS = '删除成功';
    const UPDATE_SUCCESS = '更新成功';
    const COMMENT_SUCCESS = '评论发表成功';
    const SCORE_SUCCESS = '评分成功';
}

// ========== 日志级别常量 ==========

class XpkLogLevel {
    const DEBUG = 'debug';
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';
    const CRITICAL = 'critical';
}

// ========== 事件类型常量 ==========

class XpkEventTypes {
    const USER_LOGIN = 'user.login';
    const USER_LOGOUT = 'user.logout';
    const USER_REGISTER = 'user.register';
    const VOD_VIEW = 'vod.view';
    const VOD_PLAY = 'vod.play';
    const COMMENT_POST = 'comment.post';
    const SCORE_RATE = 'score.rate';
    const ADMIN_LOGIN = 'admin.login';
    const SYSTEM_ERROR = 'system.error';
}