<?php
/**
 * 香蕉CMS 后台入口
 * Powered by https://xpornkit.com
 */

// 加载配置
require_once __DIR__ . '/config/config.php';

// 调试模式
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

// 注册错误处理
XpkErrorHandler::register();

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

// 登录/退出
$router->get('admin.php', fn() => (new AdminAuthController())->login());
$router->get('admin.php/login', fn() => (new AdminAuthController())->login());
$router->post('admin.php/login', fn() => (new AdminAuthController())->doLogin());
$router->get('admin.php/logout', fn() => (new AdminAuthController())->logout());

// 仪表盘
$router->get('admin.php/dashboard', fn() => (new AdminDashboardController())->index());

// 视频管理
$router->get('admin.php/vod', fn() => (new AdminVodController())->index());
$router->get('admin.php/vod/add', fn() => (new AdminVodController())->add());
$router->post('admin.php/vod/add', fn() => (new AdminVodController())->doAdd());
$router->get('admin.php/vod/edit/{id}', fn($id) => (new AdminVodController())->edit((int)$id));
$router->post('admin.php/vod/edit/{id}', fn($id) => (new AdminVodController())->doEdit((int)$id));
$router->post('admin.php/vod/delete', fn() => (new AdminVodController())->delete());
$router->post('admin.php/vod/status', fn() => (new AdminVodController())->status());

// 分类管理
$router->get('admin.php/type', fn() => (new AdminTypeController())->index());
$router->get('admin.php/type/get', fn() => (new AdminTypeController())->get());
$router->get('admin.php/type/add', fn() => (new AdminTypeController())->add());
$router->post('admin.php/type/add', fn() => (new AdminTypeController())->add());
$router->get('admin.php/type/edit/{id}', fn($id) => (new AdminTypeController())->edit((int)$id));
$router->post('admin.php/type/edit/{id}', fn($id) => (new AdminTypeController())->edit((int)$id));
$router->post('admin.php/type/delete', fn() => (new AdminTypeController())->delete());

// 演员管理
$router->get('admin.php/actor', fn() => (new AdminActorController())->index());
$router->get('admin.php/actor/get', fn() => (new AdminActorController())->get());
$router->get('admin.php/actor/add', fn() => (new AdminActorController())->add());
$router->post('admin.php/actor/add', fn() => (new AdminActorController())->add());
$router->get('admin.php/actor/edit/{id}', fn($id) => (new AdminActorController())->edit((int)$id));
$router->post('admin.php/actor/edit/{id}', fn($id) => (new AdminActorController())->edit((int)$id));
$router->post('admin.php/actor/delete', fn() => (new AdminActorController())->delete());

// 文章管理
$router->get('admin.php/art', fn() => (new AdminArtController())->index());
$router->get('admin.php/art/get', fn() => (new AdminArtController())->get());
$router->get('admin.php/art/add', fn() => (new AdminArtController())->add());
$router->post('admin.php/art/add', fn() => (new AdminArtController())->add());
$router->get('admin.php/art/edit/{id}', fn($id) => (new AdminArtController())->edit((int)$id));
$router->post('admin.php/art/edit/{id}', fn($id) => (new AdminArtController())->edit((int)$id));
$router->post('admin.php/art/delete', fn() => (new AdminArtController())->delete());

// 文章分类管理
$router->get('admin.php/art_type', fn() => (new AdminArtTypeController())->index());
$router->get('admin.php/art_type/get', fn() => (new AdminArtTypeController())->get());
$router->get('admin.php/art_type/add', fn() => (new AdminArtTypeController())->add());
$router->post('admin.php/art_type/add', fn() => (new AdminArtTypeController())->add());
$router->get('admin.php/art_type/edit/{id}', fn($id) => (new AdminArtTypeController())->edit((int)$id));
$router->post('admin.php/art_type/edit/{id}', fn($id) => (new AdminArtTypeController())->edit((int)$id));
$router->post('admin.php/art_type/delete', fn() => (new AdminArtTypeController())->delete());

// 用户管理
$router->get('admin.php/user', fn() => (new AdminUserController())->index());
$router->get('admin.php/user/edit/{id}', fn($id) => (new AdminUserController())->edit((int)$id));
$router->post('admin.php/user/edit/{id}', fn($id) => (new AdminUserController())->doEdit((int)$id));
$router->post('admin.php/user/delete', fn() => (new AdminUserController())->delete());

// 系统配置
$router->get('admin.php/config', fn() => (new AdminConfigController())->index());
$router->post('admin.php/config', fn() => (new AdminConfigController())->save());
$router->post('admin.php/config/upload', fn() => (new AdminConfigController())->upload());

// 采集管理
$router->get('admin.php/collect', fn() => (new AdminCollectController())->index());
$router->get('admin.php/collect/add', fn() => (new AdminCollectController())->add());
$router->post('admin.php/collect/add', fn() => (new AdminCollectController())->doAdd());
$router->get('admin.php/collect/edit/{id}', fn($id) => (new AdminCollectController())->edit((int)$id));
$router->post('admin.php/collect/edit/{id}', fn($id) => (new AdminCollectController())->doEdit((int)$id));
$router->post('admin.php/collect/delete', fn() => (new AdminCollectController())->delete());
$router->get('admin.php/collect/bind/{id}', fn($id) => (new AdminCollectController())->bind((int)$id));
$router->post('admin.php/collect/savebind/{id}', fn($id) => (new AdminCollectController())->saveBind((int)$id));
$router->post('admin.php/collect/syncCategories', fn() => (new AdminCollectController())->syncCategories());
$router->get('admin.php/collect/run/{id}', fn($id) => (new AdminCollectController())->run((int)$id));
$router->post('admin.php/collect/docollect', fn() => (new AdminCollectController())->doCollect());
$router->post('admin.php/collect/test', fn() => (new AdminCollectController())->test());

// 操作日志
$router->get('admin.php/log', fn() => (new AdminLogController())->index());
$router->post('admin.php/log/clean', fn() => (new AdminLogController())->clean());

// 友链管理
require_once CTRL_PATH . 'admin/LinkController.php';
$router->get('admin.php/link', fn() => (new AdminLinkController())->index());
$router->get('admin.php/link/get', fn() => (new AdminLinkController())->get());
$router->get('admin.php/link/add', fn() => (new AdminLinkController())->add());
$router->post('admin.php/link/add', fn() => (new AdminLinkController())->add());
$router->get('admin.php/link/edit/{id}', fn($id) => (new AdminLinkController())->edit((int)$id));
$router->post('admin.php/link/edit/{id}', fn($id) => (new AdminLinkController())->edit((int)$id));
$router->post('admin.php/link/delete', fn() => (new AdminLinkController())->delete());
$router->post('admin.php/link/audit', fn() => (new AdminLinkController())->audit());
$router->post('admin.php/link/check', fn() => (new AdminLinkController())->check());
$router->post('admin.php/link/saveSetting', fn() => (new AdminLinkController())->saveSetting());

// 广告管理
require_once CTRL_PATH . 'admin/AdController.php';
$router->get('admin.php/ad', fn() => (new AdminAdController())->index());
$router->get('admin.php/ad/get', fn() => (new AdminAdController())->get());
$router->get('admin.php/ad/add', fn() => (new AdminAdController())->add());
$router->post('admin.php/ad/add', fn() => (new AdminAdController())->add());
$router->get('admin.php/ad/edit/{id}', fn($id) => (new AdminAdController())->edit((int)$id));
$router->post('admin.php/ad/edit/{id}', fn($id) => (new AdminAdController())->edit((int)$id));
$router->post('admin.php/ad/delete', fn() => (new AdminAdController())->delete());
$router->post('admin.php/ad/toggle', fn() => (new AdminAdController())->toggle());
$router->post('admin.php/ad/click', fn() => (new AdminAdController())->click());

// 评论管理
require_once CTRL_PATH . 'admin/CommentController.php';
$router->get('admin.php/comment', fn() => (new AdminCommentController())->index());
$router->post('admin.php/comment/approve', fn() => (new AdminCommentController())->approve());
$router->post('admin.php/comment/reject', fn() => (new AdminCommentController())->reject());
$router->post('admin.php/comment/delete', fn() => (new AdminCommentController())->delete());
$router->post('admin.php/comment/batchAudit', fn() => (new AdminCommentController())->batchAudit());
$router->get('admin.php/comment/setting', fn() => (new AdminCommentController())->setting());
$router->post('admin.php/comment/saveSetting', fn() => (new AdminCommentController())->saveSetting());

// 数据统计
require_once CTRL_PATH . 'admin/StatsController.php';
$router->get('admin.php/stats', fn() => (new AdminStatsController())->index());
$router->get('admin.php/stats/trend', fn() => (new AdminStatsController())->trend());
$router->get('admin.php/stats/hot', fn() => (new AdminStatsController())->hot());
$router->post('admin.php/stats/clean', fn() => (new AdminStatsController())->clean());

// 短视频管理
require_once CTRL_PATH . 'admin/ShortController.php';
$router->get('admin.php/short', fn() => (new AdminShortController())->index());
$router->get('admin.php/short/add', fn() => (new AdminShortController())->add());
$router->post('admin.php/short/doAdd', fn() => (new AdminShortController())->doAdd());
$router->get('admin.php/short/edit/{id}', fn($id) => (new AdminShortController())->edit((int)$id));
$router->post('admin.php/short/doEdit/{id}', fn($id) => (new AdminShortController())->doEdit((int)$id));
$router->post('admin.php/short/delete', fn() => (new AdminShortController())->delete());
$router->post('admin.php/short/toggle', fn() => (new AdminShortController())->toggle());
$router->get('admin.php/short/episodes/{id}', fn($id) => (new AdminShortController())->episodes((int)$id));
$router->get('admin.php/short/addEpisode/{id}', fn($id) => (new AdminShortController())->addEpisode((int)$id));
$router->post('admin.php/short/doAddEpisode/{id}', fn($id) => (new AdminShortController())->doAddEpisode((int)$id));
$router->get('admin.php/short/editEpisode/{id}', fn($id) => (new AdminShortController())->editEpisode((int)$id));
$router->post('admin.php/short/doEditEpisode/{id}', fn($id) => (new AdminShortController())->doEditEpisode((int)$id));
$router->post('admin.php/short/deleteEpisode', fn() => (new AdminShortController())->deleteEpisode());

// 分发路由
$router->dispatch();
