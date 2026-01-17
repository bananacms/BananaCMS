<?php
/**
 * 香蕉CMS 后台入口
 * 使用查询参数路由模式: admin.php?s=xxx
 * Powered by https://xpornkit.com
 */

// 启动输出缓冲，防止 header 错误
ob_start();

// 加载配置
require_once __DIR__ . '/config/config.php';

// 验证配置文件（仅在调试模式下显示警告）
if (APP_DEBUG) {
    require_once CORE_PATH . 'ConfigValidator.php';
    $validation = XpkConfigValidator::validate();
    if (!$validation['valid'] && !empty($validation['warnings'])) {
        // 在调试模式下记录警告
        error_log('配置验证警告: ' . implode(', ', $validation['warnings']));
    }
}

// 初始调试模式
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
require_once CORE_PATH . 'Security.php';
require_once CORE_PATH . 'Router.php';
require_once CORE_PATH . 'Cache.php';
require_once CORE_PATH . 'RedisSession.php';
require_once CORE_PATH . 'Pinyin.php';

// 注册错误处理
XpkErrorHandler::register();

// 设置安全响应头
XpkSecurity::setSecurityHeaders('admin');

// 从数据库读取调试模式配置
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
require_once CTRL_PATH . 'admin/AiController.php';
require_once CTRL_PATH . 'admin/TranscodeController.php';
require_once CTRL_PATH . 'admin/TranscodeAdController.php';
require_once CTRL_PATH . 'admin/SearchController.php';
require_once CTRL_PATH . 'admin/PlayerController.php';
require_once CTRL_PATH . 'admin/PageController.php';
require_once CTRL_PATH . 'admin/LinkController.php';
require_once CTRL_PATH . 'admin/AdController.php';
require_once CTRL_PATH . 'admin/CommentController.php';
require_once CTRL_PATH . 'admin/StatsController.php';
require_once CTRL_PATH . 'admin/ShortController.php';
require_once CTRL_PATH . 'admin/PaymentController.php';
require_once CTRL_PATH . 'admin/VipController.php';
require_once CTRL_PATH . 'admin/OrderController.php';

// Session
if (session_status() === PHP_SESSION_NONE) {
    xpk_init_redis_session();
    session_set_cookie_params([
        'lifetime' => 7200,  // 2小时
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
    
    // 设置 Session 超时时间
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 7200) {
        // Session 超时，销毁并重新创建
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// 获取当前后台入口文件名（支持从 index.php 转发）
$adminEntry = defined('ADMIN_ENTRY') ? ADMIN_ENTRY : pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_FILENAME);
$adminRoute = trim($_GET['s'] ?? '', '/');
$method = $_SERVER['REQUEST_METHOD'];

// 路由表 - 静态路由
$routes = [
    // 登录/退出
    'GET:'           => fn() => (new AdminAuthController())->login(),
    'GET:login'      => fn() => (new AdminAuthController())->login(),
    'POST:login'     => fn() => (new AdminAuthController())->doLogin(),
    'GET:logout'     => fn() => (new AdminAuthController())->logout(),
    
    // 修改密码
    'GET:password'   => fn() => (new AdminAuthController())->password(),
    'POST:password'  => fn() => (new AdminAuthController())->doPassword(),
    
    // 仪表盘
    'GET:dashboard'  => fn() => (new AdminDashboardController())->index(),

    // 视频管理
    'GET:vod'              => fn() => (new AdminVodController())->index(),
    'GET:vod/add'          => fn() => (new AdminVodController())->add(),
    'POST:vod/add'         => fn() => (new AdminVodController())->doAdd(),
    'POST:vod/delete'      => fn() => (new AdminVodController())->delete(),
    'POST:vod/status'      => fn() => (new AdminVodController())->status(),
    'POST:vod/lock'        => fn() => (new AdminVodController())->lock(),
    'POST:vod/batchLock'   => fn() => (new AdminVodController())->batchLock(),
    'GET:vod/replace'      => fn() => (new AdminVodController())->replace(),
    'POST:vod/replace'     => fn() => (new AdminVodController())->doReplace(),
    'GET:vod/sources'      => fn() => (new AdminVodController())->sources(),
    'POST:vod/deleteSource'=> fn() => (new AdminVodController())->deleteSource(),
    'POST:vod/renameSource'=> fn() => (new AdminVodController())->renameSource(),
    
    // 分类管理
    'GET:type'             => fn() => (new AdminTypeController())->index(),
    'GET:type/get'         => fn() => (new AdminTypeController())->get(),
    'GET:type/getOne'      => fn() => (new AdminTypeController())->getOne(),
    'GET:type/add'         => fn() => (new AdminTypeController())->add(),
    'POST:type/add'        => fn() => (new AdminTypeController())->add(),
    'POST:type/delete'     => fn() => (new AdminTypeController())->delete(),
    'POST:type/batchDelete'=> fn() => (new AdminTypeController())->batchDelete(),
    'POST:type/fixVodTypeId1' => fn() => (new AdminTypeController())->fixVodTypeId1(),
    
    // 演员管理
    'GET:actor'            => fn() => (new AdminActorController())->index(),
    'GET:actor/get'        => fn() => (new AdminActorController())->get(),
    'GET:actor/add'        => fn() => (new AdminActorController())->add(),
    'POST:actor/add'       => fn() => (new AdminActorController())->add(),
    'POST:actor/delete'    => fn() => (new AdminActorController())->delete(),
    
    // 文章管理
    'GET:art'              => fn() => (new AdminArtController())->index(),
    'GET:art/get'          => fn() => (new AdminArtController())->get(),
    'GET:art/add'          => fn() => (new AdminArtController())->add(),
    'POST:art/add'         => fn() => (new AdminArtController())->add(),
    'POST:art/delete'      => fn() => (new AdminArtController())->delete(),
    
    // 文章分类管理
    'GET:art_type'         => fn() => (new AdminArtTypeController())->index(),
    'GET:art_type/get'     => fn() => (new AdminArtTypeController())->get(),
    'GET:art_type/add'     => fn() => (new AdminArtTypeController())->add(),
    'POST:art_type/add'    => fn() => (new AdminArtTypeController())->add(),
    'POST:art_type/delete' => fn() => (new AdminArtTypeController())->delete(),
    
    // 用户管理
    'GET:user'             => fn() => (new AdminUserController())->index(),
    'POST:user/delete'     => fn() => (new AdminUserController())->delete(),
    
    // 系统配置
    'GET:config'           => fn() => (new AdminConfigController())->index(),
    'POST:config'          => fn() => (new AdminConfigController())->save(),
    'POST:config/save'     => fn() => (new AdminConfigController())->save(),
    'POST:config/upload'   => fn() => (new AdminConfigController())->upload(),
    'POST:config/uploadTemplate' => fn() => (new AdminConfigController())->uploadTemplate(),
    'POST:config/deleteTemplate' => fn() => (new AdminConfigController())->deleteTemplate(),
    'GET:config/security'  => fn() => (new AdminConfigController())->security(),
    'POST:config/security' => fn() => (new AdminConfigController())->security(),
    'POST:config/testRedis'=> fn() => (new AdminConfigController())->testRedis(),
    
    // 采集管理
    'GET:collect'          => fn() => (new AdminCollectController())->index(),
    'GET:collect/add'      => fn() => (new AdminCollectController())->add(),
    'POST:collect/add'     => fn() => (new AdminCollectController())->doAdd(),
    'POST:collect/delete'  => fn() => (new AdminCollectController())->delete(),
    'POST:collect/deleteVods' => fn() => (new AdminCollectController())->deleteVods(),
    'POST:collect/copyBind'=> fn() => (new AdminCollectController())->copyBind(),
    'POST:collect/syncCategories' => fn() => (new AdminCollectController())->syncCategories(),
    'POST:collect/docollect' => fn() => (new AdminCollectController())->doCollect(),
    'POST:collect/test'    => fn() => (new AdminCollectController())->test(),
    'POST:collect/clearProgress' => fn() => (new AdminCollectController())->clearProgress(),
    'GET:collect/cron'     => fn() => (new AdminCollectController())->cron(),
    'POST:collect/saveCron'=> fn() => (new AdminCollectController())->saveCron(),
    'POST:collect/runCron' => fn() => (new AdminCollectController())->runCron(),
    'GET:collect/log'      => fn() => (new AdminCollectController())->logList(),
    'POST:collect/cleanLog'=> fn() => (new AdminCollectController())->cleanLog(),

    // AI 内容改写
    'GET:ai'               => fn() => (new AdminAiController())->index(),
    'POST:ai/save'         => fn() => (new AdminAiController())->save(),
    'POST:ai/test'         => fn() => (new AdminAiController())->test(),
    'POST:ai/run'          => fn() => (new AdminAiController())->run(),
    'POST:ai/reset'        => fn() => (new AdminAiController())->reset(),
    
    // 云转码
    'GET:transcode'        => fn() => (new AdminTranscodeController())->index(),
    'GET:transcode/upload' => fn() => (new AdminTranscodeController())->upload(),
    'GET:transcode/doUpload' => fn() => (new AdminTranscodeController())->doUpload(),
    'POST:transcode/doUpload' => fn() => (new AdminTranscodeController())->doUpload(),
    'GET:transcode/status' => fn() => (new AdminTranscodeController())->status(),
    'POST:transcode/retry' => fn() => (new AdminTranscodeController())->retry(),
    'POST:transcode/delete'=> fn() => (new AdminTranscodeController())->delete(),
    'POST:transcode/batchDelete' => fn() => (new AdminTranscodeController())->batchDelete(),
    'POST:transcode/process' => fn() => (new AdminTranscodeController())->process(),
    'GET:transcode/play'   => fn() => (new AdminTranscodeController())->play(),
    
    // 转码广告管理
    'GET:transcode/ad'     => fn() => (new AdminTranscodeAdController())->index(),
    'GET:transcode/ad/add' => fn() => (new AdminTranscodeAdController())->add(),
    'POST:transcode/ad/add'=> fn() => (new AdminTranscodeAdController())->add(),
    'POST:transcode/ad/delete' => fn() => (new AdminTranscodeAdController())->delete(),
    'POST:transcode/ad/toggle' => fn() => (new AdminTranscodeAdController())->toggle(),
    'POST:transcode/ad/saveConfig' => fn() => (new AdminTranscodeAdController())->saveConfig(),
    'POST:transcode/ad/upload' => fn() => (new AdminTranscodeAdController())->upload(),
    
    // 操作日志
    'GET:log'              => fn() => (new AdminLogController())->index(),
    'POST:log/clean'       => fn() => (new AdminLogController())->clean(),
    
    // 搜索管理
    'GET:search'           => fn() => (new AdminSearchController())->index(),
    'GET:search/log'       => fn() => (new AdminSearchController())->logList(),
    'POST:search/cleanLog' => fn() => (new AdminSearchController())->cleanLog(),
    
    // 播放器管理
    'GET:player'           => fn() => (new AdminPlayerController())->index(),
    'GET:player/add'       => fn() => (new AdminPlayerController())->add(),
    'POST:player/add'      => fn() => (new AdminPlayerController())->doAdd(),
    'POST:player/delete'   => fn() => (new AdminPlayerController())->delete(),
    'POST:player/toggle'   => fn() => (new AdminPlayerController())->toggle(),
    
    // 单页管理
    'GET:page'             => fn() => (new AdminPageController())->index(),
    'GET:page/add'         => fn() => (new AdminPageController())->add(),
    'POST:page/add'        => fn() => (new AdminPageController())->doAdd(),
    'POST:page/delete'     => fn() => (new AdminPageController())->delete(),
    'POST:page/init'       => fn() => (new AdminPageController())->init(),
    
    // 友链管理
    'GET:link'             => fn() => (new AdminLinkController())->index(),
    'GET:link/get'         => fn() => (new AdminLinkController())->get(),
    'GET:link/add'         => fn() => (new AdminLinkController())->add(),
    'POST:link/add'        => fn() => (new AdminLinkController())->add(),
    'POST:link/delete'     => fn() => (new AdminLinkController())->delete(),
    'POST:link/audit'      => fn() => (new AdminLinkController())->audit(),
    'POST:link/check'      => fn() => (new AdminLinkController())->check(),
    'POST:link/saveSetting'=> fn() => (new AdminLinkController())->saveSetting(),
    
    // 广告管理
    'GET:ad'               => fn() => (new AdminAdController())->index(),
    'GET:ad/get'           => fn() => (new AdminAdController())->getOne(),
    'GET:ad/getOne'        => fn() => (new AdminAdController())->getOne(),
    'GET:ad/add'           => fn() => (new AdminAdController())->add(),
    'POST:ad/add'          => fn() => (new AdminAdController())->add(),
    'POST:ad/delete'       => fn() => (new AdminAdController())->delete(),
    'POST:ad/toggle'       => fn() => (new AdminAdController())->toggle(),
    'POST:ad/click'        => fn() => (new AdminAdController())->click(),
    'GET:ad/securityConfig'=> fn() => (new AdminAdController())->securityConfig(),
    'POST:ad/securityConfig' => fn() => (new AdminAdController())->securityConfig(),
    
    // 评论管理
    'GET:comment'          => fn() => (new AdminCommentController())->index(),
    'POST:comment/approve' => fn() => (new AdminCommentController())->approve(),
    'POST:comment/reject'  => fn() => (new AdminCommentController())->reject(),
    'POST:comment/delete'  => fn() => (new AdminCommentController())->delete(),
    'POST:comment/batchAudit' => fn() => (new AdminCommentController())->batchAudit(),
    'GET:comment/setting'  => fn() => (new AdminCommentController())->setting(),
    'POST:comment/saveSetting' => fn() => (new AdminCommentController())->saveSetting(),
    
    // 数据统计
    'GET:stats'            => fn() => (new AdminStatsController())->index(),
    'GET:stats/trend'      => fn() => (new AdminStatsController())->trend(),
    'GET:stats/hot'        => fn() => (new AdminStatsController())->hot(),
    'POST:stats/clean'     => fn() => (new AdminStatsController())->clean(),
    'POST:stats/cleanAll'  => fn() => (new AdminStatsController())->cleanAll(),
    
    // 短视频管理
    'GET:short'            => fn() => (new AdminShortController())->index(),
    'GET:short/add'        => fn() => (new AdminShortController())->add(),
    'POST:short/doAdd'     => fn() => (new AdminShortController())->doAdd(),
    'POST:short/delete'    => fn() => (new AdminShortController())->delete(),
    'POST:short/toggle'    => fn() => (new AdminShortController())->toggle(),
    'POST:short/deleteEpisode' => fn() => (new AdminShortController())->deleteEpisode(),
    
    // 支付通道管理
    'GET:payment'          => fn() => (new AdminPaymentController())->index(),
    'GET:payment/add'      => fn() => (new AdminPaymentController())->add(),
    'POST:payment/add'     => fn() => (new AdminPaymentController())->add(),
    'POST:payment/toggle'  => fn() => (new AdminPaymentController())->toggle(),
    'POST:payment/delete'  => fn() => (new AdminPaymentController())->delete(),
    'GET:payment/usdt'     => fn() => (new AdminPaymentController())->usdt(),
    'POST:payment/usdt'    => fn() => (new AdminPaymentController())->usdt(),
    
    // VIP套餐管理
    'GET:vip'              => fn() => (new AdminVipController())->index(),
    'GET:vip/add'          => fn() => (new AdminVipController())->add(),
    'POST:vip/add'         => fn() => (new AdminVipController())->add(),
    'POST:vip/toggle'      => fn() => (new AdminVipController())->toggle(),
    'POST:vip/delete'      => fn() => (new AdminVipController())->delete(),
    'GET:vip/config'       => fn() => (new AdminVipController())->config(),
    'POST:vip/config'      => fn() => (new AdminVipController())->config(),
    
    // 订单管理
    'GET:order'            => fn() => (new AdminOrderController())->index(),
    'POST:order/complete'  => fn() => (new AdminOrderController())->complete(),
    'POST:order/cancel'    => fn() => (new AdminOrderController())->cancel(),
    'GET:order/export'     => fn() => (new AdminOrderController())->export(),
];

// 带 ID 参数的路由
$idRoutes = [
    'GET:vod/edit'     => fn($id) => (new AdminVodController())->edit($id),
    'POST:vod/edit'    => fn($id) => (new AdminVodController())->doEdit($id),
    'GET:type/edit'    => fn($id) => (new AdminTypeController())->edit($id),
    'POST:type/edit'   => fn($id) => (new AdminTypeController())->edit($id),
    'GET:actor/edit'   => fn($id) => (new AdminActorController())->edit($id),
    'POST:actor/edit'  => fn($id) => (new AdminActorController())->edit($id),
    'GET:art/edit'     => fn($id) => (new AdminArtController())->edit($id),
    'POST:art/edit'    => fn($id) => (new AdminArtController())->edit($id),
    'GET:art_type/edit'=> fn($id) => (new AdminArtTypeController())->edit($id),
    'POST:art_type/edit' => fn($id) => (new AdminArtTypeController())->edit($id),
    'GET:user/edit'    => fn($id) => (new AdminUserController())->edit($id),
    'POST:user/edit'   => fn($id) => (new AdminUserController())->doEdit($id),
    'GET:collect/edit' => fn($id) => (new AdminCollectController())->edit($id),
    'POST:collect/edit'=> fn($id) => (new AdminCollectController())->doEdit($id),
    'GET:collect/bind' => fn($id) => (new AdminCollectController())->bind($id),
    'POST:collect/savebind' => fn($id) => (new AdminCollectController())->saveBind($id),
    'GET:collect/run'  => fn($id) => (new AdminCollectController())->run($id),
    'GET:transcode/ad/edit' => fn($id) => (new AdminTranscodeAdController())->edit($id),
    'POST:transcode/ad/edit' => fn($id) => (new AdminTranscodeAdController())->edit($id),
    'GET:player/edit'  => fn($id) => (new AdminPlayerController())->edit($id),
    'POST:player/edit' => fn($id) => (new AdminPlayerController())->doEdit($id),
    'GET:page/edit'    => fn($id) => (new AdminPageController())->edit($id),
    'POST:page/edit'   => fn($id) => (new AdminPageController())->doEdit($id),
    'GET:link/edit'    => fn($id) => (new AdminLinkController())->edit($id),
    'POST:link/edit'   => fn($id) => (new AdminLinkController())->edit($id),
    'GET:ad/edit'      => fn($id) => (new AdminAdController())->edit($id),
    'POST:ad/edit'     => fn($id) => (new AdminAdController())->edit($id),
    'GET:short/edit'   => fn($id) => (new AdminShortController())->edit($id),
    'POST:short/doEdit'=> fn($id) => (new AdminShortController())->doEdit($id),
    'GET:short/episodes' => fn($id) => (new AdminShortController())->episodes($id),
    'GET:short/addEpisode' => fn($id) => (new AdminShortController())->addEpisode($id),
    'POST:short/doAddEpisode' => fn($id) => (new AdminShortController())->doAddEpisode($id),
    'GET:short/editEpisode' => fn($id) => (new AdminShortController())->editEpisode($id),
    'POST:short/doEditEpisode' => fn($id) => (new AdminShortController())->doEditEpisode($id),
    
    // 支付通道编辑
    'GET:payment/edit'     => fn($id) => (new AdminPaymentController())->edit($id),
    'POST:payment/edit'    => fn($id) => (new AdminPaymentController())->edit($id),
    
    // VIP套餐编辑
    'GET:vip/edit'         => fn($id) => (new AdminVipController())->edit($id),
    'POST:vip/edit'        => fn($id) => (new AdminVipController())->edit($id),
    
    // 订单详情
    'GET:order/detail'     => fn($id) => (new AdminOrderController())->detail($id),
];

// 路由分发
$routeKey = $method . ':' . $adminRoute;

// 1. 先尝试精确匹配
if (isset($routes[$routeKey])) {
    $routes[$routeKey]();
    exit;
}

// 2. 尝试匹配带 ID 参数的路由 (如 vod/edit/123)
if (preg_match('#^(.+)/(\d+)$#', $adminRoute, $matches)) {
    $baseRoute = $matches[1];
    $id = (int)$matches[2];
    $routeKey = $method . ':' . $baseRoute;
    
    if (isset($idRoutes[$routeKey])) {
        $idRoutes[$routeKey]($id);
        exit;
    }
}

// 404
http_response_code(404);
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>404</title></head>';
echo '<body style="text-align:center;padding:50px;font-family:sans-serif;">';
echo '<h1>404</h1><p>页面不存在</p>';
echo '<a href="/' . htmlspecialchars($adminEntry) . '?s=dashboard">返回后台</a>';
echo '</body></html>';
