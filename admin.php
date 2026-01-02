<?php
/**
 * 香蕉CMS 后台入口
 * Powered by https://xpornkit.com
 */

// 加载配置
require_once __DIR__ . '/config/config.php';

// 初始调试模式（使用常量配置）
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// 加载核心类
require_once CORE_PATH . 'ErrorHandler.php';
require_once CORE_PATH . 'Database.php';
require_once CORE_PATH . 'Router.php';
require_once CORE_PATH . 'Cache.php';
require_once CORE_PATH . 'RedisSession.php';
require_once CORE_PATH . 'Pinyin.php';

// 注册错误处理
XpkErrorHandler::register();

// 从数据库读取调试模式配置并应用
$dbDebug = xpk_config('app_debug', null);
if ($dbDebug !== null) {
    if ($dbDebug) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    } else {
        error_reporting(0);
        ini_set('display_errors', 0);
    }
}

// 加载模型
require_once MODEL_PATH . 'Model.php';
require_once MODEL_PATH . 'Admin.php';
require_once MODEL_PATH . 'Vod.php';
require_once MODEL_PATH . 'Type.php';
require_once MODEL_PATH . 'Actor.php';
require_once MODEL_PATH . 'Art.php';
require_once MODEL_PATH . 'ArtType.php';
require_once MODEL_PATH . 'User.php';
require_once MODEL_PATH . 'AdminLog.php';

// 加载后台控制器
require_once CTRL_PATH . 'admin/AdminBaseController.php';
require_once CTRL_PATH . 'admin/AuthController.php';
require_once CTRL_PATH . 'admin/DashboardController.php';
require_once CTRL_PATH . 'admin/VodController.php';
require_once CTRL_PATH . 'admin/TypeController.php';
require_once CTRL_PATH . 'admin/ActorController.php';
require_once CTRL_PATH . 'admin/ArtController.php';
require_once CTRL_PATH . 'admin/ArtTypeController.php';
require_once CTRL_PATH . 'admin/UserController.php';
require_once CTRL_PATH . 'admin/ConfigController.php';
require_once CTRL_PATH . 'admin/CollectController.php';
require_once CTRL_PATH . 'admin/LogController.php';

// Session
if (session_status() === PHP_SESSION_NONE) {
    // 初始化Redis Session（如果配置）
    xpk_init_redis_session();
    
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// 初始化路由
$router = new XpkRouter();

// 获取当前后台入口文件名（不含.php）
$adminEntry = basename($_SERVER['SCRIPT_NAME'], '.php');

// 登录/退出
$router->get($adminEntry . '.php', fn() => (new AdminAuthController())->login());
$router->get($adminEntry . '.php/login', fn() => (new AdminAuthController())->login());
$router->post($adminEntry . '.php/login', fn() => (new AdminAuthController())->doLogin());
$router->get($adminEntry . '.php/logout', fn() => (new AdminAuthController())->logout());

// 仪表盘
$router->get($adminEntry . '.php/dashboard', fn() => (new AdminDashboardController())->index());

// 视频管理
$router->get($adminEntry . '.php/vod', fn() => (new AdminVodController())->index());
$router->get($adminEntry . '.php/vod/add', fn() => (new AdminVodController())->add());
$router->post($adminEntry . '.php/vod/add', fn() => (new AdminVodController())->doAdd());
$router->get($adminEntry . '.php/vod/edit/{id}', fn($id) => (new AdminVodController())->edit((int)$id));
$router->post($adminEntry . '.php/vod/edit/{id}', fn($id) => (new AdminVodController())->doEdit((int)$id));
$router->post($adminEntry . '.php/vod/delete', fn() => (new AdminVodController())->delete());
$router->post($adminEntry . '.php/vod/status', fn() => (new AdminVodController())->status());
$router->post($adminEntry . '.php/vod/lock', fn() => (new AdminVodController())->lock());
$router->post($adminEntry . '.php/vod/batchLock', fn() => (new AdminVodController())->batchLock());
$router->get($adminEntry . '.php/vod/replace', fn() => (new AdminVodController())->replace());
$router->post($adminEntry . '.php/vod/replace', fn() => (new AdminVodController())->doReplace());
$router->get($adminEntry . '.php/vod/sources', fn() => (new AdminVodController())->sources());
$router->post($adminEntry . '.php/vod/deleteSource', fn() => (new AdminVodController())->deleteSource());
$router->post($adminEntry . '.php/vod/renameSource', fn() => (new AdminVodController())->renameSource());

// 分类管理
$router->get($adminEntry . '.php/type', fn() => (new AdminTypeController())->index());
$router->get($adminEntry . '.php/type/get', fn() => (new AdminTypeController())->get());
$router->get($adminEntry . '.php/type/getOne', fn() => (new AdminTypeController())->getOne());
$router->get($adminEntry . '.php/type/add', fn() => (new AdminTypeController())->add());
$router->post($adminEntry . '.php/type/add', fn() => (new AdminTypeController())->add());
$router->get($adminEntry . '.php/type/edit/{id}', fn($id) => (new AdminTypeController())->edit((int)$id));
$router->post($adminEntry . '.php/type/edit/{id}', fn($id) => (new AdminTypeController())->edit((int)$id));
$router->post($adminEntry . '.php/type/delete', fn() => (new AdminTypeController())->delete());
$router->post($adminEntry . '.php/type/batchDelete', fn() => (new AdminTypeController())->batchDelete());

// 演员管理
$router->get($adminEntry . '.php/actor', fn() => (new AdminActorController())->index());
$router->get($adminEntry . '.php/actor/get', fn() => (new AdminActorController())->get());
$router->get($adminEntry . '.php/actor/add', fn() => (new AdminActorController())->add());
$router->post($adminEntry . '.php/actor/add', fn() => (new AdminActorController())->add());
$router->get($adminEntry . '.php/actor/edit/{id}', fn($id) => (new AdminActorController())->edit((int)$id));
$router->post($adminEntry . '.php/actor/edit/{id}', fn($id) => (new AdminActorController())->edit((int)$id));
$router->post($adminEntry . '.php/actor/delete', fn() => (new AdminActorController())->delete());

// 文章管理
$router->get($adminEntry . '.php/art', fn() => (new AdminArtController())->index());
$router->get($adminEntry . '.php/art/get', fn() => (new AdminArtController())->get());
$router->get($adminEntry . '.php/art/add', fn() => (new AdminArtController())->add());
$router->post($adminEntry . '.php/art/add', fn() => (new AdminArtController())->add());
$router->get($adminEntry . '.php/art/edit/{id}', fn($id) => (new AdminArtController())->edit((int)$id));
$router->post($adminEntry . '.php/art/edit/{id}', fn($id) => (new AdminArtController())->edit((int)$id));
$router->post($adminEntry . '.php/art/delete', fn() => (new AdminArtController())->delete());

// 文章分类管理
$router->get($adminEntry . '.php/art_type', fn() => (new AdminArtTypeController())->index());
$router->get($adminEntry . '.php/art_type/get', fn() => (new AdminArtTypeController())->get());
$router->get($adminEntry . '.php/art_type/add', fn() => (new AdminArtTypeController())->add());
$router->post($adminEntry . '.php/art_type/add', fn() => (new AdminArtTypeController())->add());
$router->get($adminEntry . '.php/art_type/edit/{id}', fn($id) => (new AdminArtTypeController())->edit((int)$id));
$router->post($adminEntry . '.php/art_type/edit/{id}', fn($id) => (new AdminArtTypeController())->edit((int)$id));
$router->post($adminEntry . '.php/art_type/delete', fn() => (new AdminArtTypeController())->delete());

// 用户管理
$router->get($adminEntry . '.php/user', fn() => (new AdminUserController())->index());
$router->get($adminEntry . '.php/user/edit/{id}', fn($id) => (new AdminUserController())->edit((int)$id));
$router->post($adminEntry . '.php/user/edit/{id}', fn($id) => (new AdminUserController())->doEdit((int)$id));
$router->post($adminEntry . '.php/user/delete', fn() => (new AdminUserController())->delete());

// 系统配置
$router->get($adminEntry . '.php/config', fn() => (new AdminConfigController())->index());
$router->post($adminEntry . '.php/config', fn() => (new AdminConfigController())->save());
$router->post($adminEntry . '.php/config/save', fn() => (new AdminConfigController())->save());
$router->post($adminEntry . '.php/config/upload', fn() => (new AdminConfigController())->upload());
$router->post($adminEntry . '.php/config/uploadTemplate', fn() => (new AdminConfigController())->uploadTemplate());
$router->post($adminEntry . '.php/config/deleteTemplate', fn() => (new AdminConfigController())->deleteTemplate());

// 采集管理
$router->get($adminEntry . '.php/collect', fn() => (new AdminCollectController())->index());
$router->get($adminEntry . '.php/collect/add', fn() => (new AdminCollectController())->add());
$router->post($adminEntry . '.php/collect/add', fn() => (new AdminCollectController())->doAdd());
$router->get($adminEntry . '.php/collect/edit/{id}', fn($id) => (new AdminCollectController())->edit((int)$id));
$router->post($adminEntry . '.php/collect/edit/{id}', fn($id) => (new AdminCollectController())->doEdit((int)$id));
$router->post($adminEntry . '.php/collect/delete', fn() => (new AdminCollectController())->delete());
$router->post($adminEntry . '.php/collect/deleteVods', fn() => (new AdminCollectController())->deleteVods());
$router->get($adminEntry . '.php/collect/bind/{id}', fn($id) => (new AdminCollectController())->bind((int)$id));
$router->post($adminEntry . '.php/collect/savebind/{id}', fn($id) => (new AdminCollectController())->saveBind((int)$id));
$router->post($adminEntry . '.php/collect/copyBind', fn() => (new AdminCollectController())->copyBind());
$router->post($adminEntry . '.php/collect/syncCategories', fn() => (new AdminCollectController())->syncCategories());
$router->get($adminEntry . '.php/collect/run/{id}', fn($id) => (new AdminCollectController())->run((int)$id));
$router->post($adminEntry . '.php/collect/docollect', fn() => (new AdminCollectController())->doCollect());
$router->post($adminEntry . '.php/collect/test', fn() => (new AdminCollectController())->test());
$router->post($adminEntry . '.php/collect/clearProgress', fn() => (new AdminCollectController())->clearProgress());
$router->get($adminEntry . '.php/collect/cron', fn() => (new AdminCollectController())->cron());
$router->post($adminEntry . '.php/collect/saveCron', fn() => (new AdminCollectController())->saveCron());
$router->post($adminEntry . '.php/collect/runCron', fn() => (new AdminCollectController())->runCron());
$router->get($adminEntry . '.php/collect/log', fn() => (new AdminCollectController())->logList());
$router->post($adminEntry . '.php/collect/cleanLog', fn() => (new AdminCollectController())->cleanLog());

// 云转码
require_once CTRL_PATH . 'admin/TranscodeController.php';
$router->get($adminEntry . '.php/transcode', fn() => (new AdminTranscodeController())->index());
$router->get($adminEntry . '.php/transcode/upload', fn() => (new AdminTranscodeController())->upload());
$router->get($adminEntry . '.php/transcode/doUpload', fn() => (new AdminTranscodeController())->doUpload());
$router->post($adminEntry . '.php/transcode/doUpload', fn() => (new AdminTranscodeController())->doUpload());
$router->get($adminEntry . '.php/transcode/status', fn() => (new AdminTranscodeController())->status());
$router->post($adminEntry . '.php/transcode/retry', fn() => (new AdminTranscodeController())->retry());
$router->post($adminEntry . '.php/transcode/delete', fn() => (new AdminTranscodeController())->delete());
$router->post($adminEntry . '.php/transcode/batchDelete', fn() => (new AdminTranscodeController())->batchDelete());
$router->post($adminEntry . '.php/transcode/process', fn() => (new AdminTranscodeController())->process());
$router->get($adminEntry . '.php/transcode/play', fn() => (new AdminTranscodeController())->play());

// 转码广告管理
require_once CTRL_PATH . 'admin/TranscodeAdController.php';
$router->get($adminEntry . '.php/transcode/ad', fn() => (new AdminTranscodeAdController())->index());
$router->get($adminEntry . '.php/transcode/ad/add', fn() => (new AdminTranscodeAdController())->add());
$router->post($adminEntry . '.php/transcode/ad/add', fn() => (new AdminTranscodeAdController())->add());
$router->get($adminEntry . '.php/transcode/ad/edit/{id}', fn($id) => (new AdminTranscodeAdController())->edit((int)$id));
$router->post($adminEntry . '.php/transcode/ad/edit/{id}', fn($id) => (new AdminTranscodeAdController())->edit((int)$id));
$router->post($adminEntry . '.php/transcode/ad/delete', fn() => (new AdminTranscodeAdController())->delete());
$router->post($adminEntry . '.php/transcode/ad/toggle', fn() => (new AdminTranscodeAdController())->toggle());
$router->post($adminEntry . '.php/transcode/ad/saveConfig', fn() => (new AdminTranscodeAdController())->saveConfig());
$router->post($adminEntry . '.php/transcode/ad/upload', fn() => (new AdminTranscodeAdController())->upload());

// 操作日志
$router->get($adminEntry . '.php/log', fn() => (new AdminLogController())->index());
$router->post($adminEntry . '.php/log/clean', fn() => (new AdminLogController())->clean());

// 搜索管理
require_once CTRL_PATH . 'admin/SearchController.php';
$router->get($adminEntry . '.php/search', fn() => (new AdminSearchController())->index());
$router->get($adminEntry . '.php/search/log', fn() => (new AdminSearchController())->logList());
$router->post($adminEntry . '.php/search/cleanLog', fn() => (new AdminSearchController())->cleanLog());

// 播放器管理
require_once CTRL_PATH . 'admin/PlayerController.php';
$router->get($adminEntry . '.php/player', fn() => (new AdminPlayerController())->index());
$router->get($adminEntry . '.php/player/add', fn() => (new AdminPlayerController())->add());
$router->post($adminEntry . '.php/player/add', fn() => (new AdminPlayerController())->doAdd());
$router->get($adminEntry . '.php/player/edit/{id}', fn($id) => (new AdminPlayerController())->edit((int)$id));
$router->post($adminEntry . '.php/player/edit/{id}', fn($id) => (new AdminPlayerController())->doEdit((int)$id));
$router->post($adminEntry . '.php/player/delete', fn() => (new AdminPlayerController())->delete());
$router->post($adminEntry . '.php/player/toggle', fn() => (new AdminPlayerController())->toggle());

// 单页管理
require_once CTRL_PATH . 'admin/PageController.php';
$router->get($adminEntry . '.php/page', fn() => (new AdminPageController())->index());
$router->get($adminEntry . '.php/page/add', fn() => (new AdminPageController())->add());
$router->post($adminEntry . '.php/page/add', fn() => (new AdminPageController())->doAdd());
$router->get($adminEntry . '.php/page/edit/{id}', fn($id) => (new AdminPageController())->edit((int)$id));
$router->post($adminEntry . '.php/page/edit/{id}', fn($id) => (new AdminPageController())->doEdit((int)$id));
$router->post($adminEntry . '.php/page/delete', fn() => (new AdminPageController())->delete());
$router->post($adminEntry . '.php/page/init', fn() => (new AdminPageController())->init());

// 友链管理
require_once CTRL_PATH . 'admin/LinkController.php';
$router->get($adminEntry . '.php/link', fn() => (new AdminLinkController())->index());
$router->get($adminEntry . '.php/link/get', fn() => (new AdminLinkController())->get());
$router->get($adminEntry . '.php/link/add', fn() => (new AdminLinkController())->add());
$router->post($adminEntry . '.php/link/add', fn() => (new AdminLinkController())->add());
$router->get($adminEntry . '.php/link/edit/{id}', fn($id) => (new AdminLinkController())->edit((int)$id));
$router->post($adminEntry . '.php/link/edit/{id}', fn($id) => (new AdminLinkController())->edit((int)$id));
$router->post($adminEntry . '.php/link/delete', fn() => (new AdminLinkController())->delete());
$router->post($adminEntry . '.php/link/audit', fn() => (new AdminLinkController())->audit());
$router->post($adminEntry . '.php/link/check', fn() => (new AdminLinkController())->check());
$router->post($adminEntry . '.php/link/saveSetting', fn() => (new AdminLinkController())->saveSetting());

// 广告管理
require_once CTRL_PATH . 'admin/AdController.php';
$router->get($adminEntry . '.php/ad', fn() => (new AdminAdController())->index());
$router->get($adminEntry . '.php/ad/get', fn() => (new AdminAdController())->getOne());
$router->get($adminEntry . '.php/ad/getOne', fn() => (new AdminAdController())->getOne());
$router->get($adminEntry . '.php/ad/add', fn() => (new AdminAdController())->add());
$router->post($adminEntry . '.php/ad/add', fn() => (new AdminAdController())->add());
$router->get($adminEntry . '.php/ad/edit/{id}', fn($id) => (new AdminAdController())->edit((int)$id));
$router->post($adminEntry . '.php/ad/edit/{id}', fn($id) => (new AdminAdController())->edit((int)$id));
$router->post($adminEntry . '.php/ad/delete', fn() => (new AdminAdController())->delete());
$router->post($adminEntry . '.php/ad/toggle', fn() => (new AdminAdController())->toggle());
$router->post($adminEntry . '.php/ad/click', fn() => (new AdminAdController())->click());

// 评论管理
require_once CTRL_PATH . 'admin/CommentController.php';
$router->get($adminEntry . '.php/comment', fn() => (new AdminCommentController())->index());
$router->post($adminEntry . '.php/comment/approve', fn() => (new AdminCommentController())->approve());
$router->post($adminEntry . '.php/comment/reject', fn() => (new AdminCommentController())->reject());
$router->post($adminEntry . '.php/comment/delete', fn() => (new AdminCommentController())->delete());
$router->post($adminEntry . '.php/comment/batchAudit', fn() => (new AdminCommentController())->batchAudit());
$router->get($adminEntry . '.php/comment/setting', fn() => (new AdminCommentController())->setting());
$router->post($adminEntry . '.php/comment/saveSetting', fn() => (new AdminCommentController())->saveSetting());

// 数据统计
require_once CTRL_PATH . 'admin/StatsController.php';
$router->get($adminEntry . '.php/stats', fn() => (new AdminStatsController())->index());
$router->get($adminEntry . '.php/stats/trend', fn() => (new AdminStatsController())->trend());
$router->get($adminEntry . '.php/stats/hot', fn() => (new AdminStatsController())->hot());
$router->post($adminEntry . '.php/stats/clean', fn() => (new AdminStatsController())->clean());
$router->post($adminEntry . '.php/stats/cleanAll', fn() => (new AdminStatsController())->cleanAll());

// 短视频管理
require_once CTRL_PATH . 'admin/ShortController.php';
$router->get($adminEntry . '.php/short', fn() => (new AdminShortController())->index());
$router->get($adminEntry . '.php/short/add', fn() => (new AdminShortController())->add());
$router->post($adminEntry . '.php/short/doAdd', fn() => (new AdminShortController())->doAdd());
$router->get($adminEntry . '.php/short/edit/{id}', fn($id) => (new AdminShortController())->edit((int)$id));
$router->post($adminEntry . '.php/short/doEdit/{id}', fn($id) => (new AdminShortController())->doEdit((int)$id));
$router->post($adminEntry . '.php/short/delete', fn() => (new AdminShortController())->delete());
$router->post($adminEntry . '.php/short/toggle', fn() => (new AdminShortController())->toggle());
$router->get($adminEntry . '.php/short/episodes/{id}', fn($id) => (new AdminShortController())->episodes((int)$id));
$router->get($adminEntry . '.php/short/addEpisode/{id}', fn($id) => (new AdminShortController())->addEpisode((int)$id));
$router->post($adminEntry . '.php/short/doAddEpisode/{id}', fn($id) => (new AdminShortController())->doAddEpisode((int)$id));
$router->get($adminEntry . '.php/short/editEpisode/{id}', fn($id) => (new AdminShortController())->editEpisode((int)$id));
$router->post($adminEntry . '.php/short/doEditEpisode/{id}', fn($id) => (new AdminShortController())->doEditEpisode((int)$id));
$router->post($adminEntry . '.php/short/deleteEpisode', fn() => (new AdminShortController())->deleteEpisode());

// 分发路由
$router->dispatch();
