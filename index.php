<?php
/**
 * 香蕉CMS 前台入口
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

// 检查安装
if (!file_exists(ROOT_PATH . 'config/install.lock')) {
    header('Location: install.php');
    exit;
}

// 加载核心类
require_once CORE_PATH . 'ErrorHandler.php';
require_once CORE_PATH . 'Database.php';
require_once CORE_PATH . 'Router.php';
require_once CORE_PATH . 'Template.php';
require_once CORE_PATH . 'Cache.php';
require_once CORE_PATH . 'RedisSession.php';

// 注册错误处理
XpkErrorHandler::register();

// 加载模型
require_once MODEL_PATH . 'Model.php';
require_once MODEL_PATH . 'Vod.php';
require_once MODEL_PATH . 'Type.php';
require_once MODEL_PATH . 'Actor.php';
require_once MODEL_PATH . 'Art.php';
require_once MODEL_PATH . 'User.php';
require_once MODEL_PATH . 'Stats.php';

// 加载控制器
require_once CTRL_PATH . 'BaseController.php';
require_once CTRL_PATH . 'HomeController.php';
require_once CTRL_PATH . 'VodController.php';
require_once CTRL_PATH . 'TypeController.php';
require_once CTRL_PATH . 'ActorController.php';
require_once CTRL_PATH . 'ArtController.php';
require_once CTRL_PATH . 'UserController.php';
require_once CTRL_PATH . 'SearchController.php';

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

// 首页
$router->get('', fn() => (new HomeController())->index());
$router->get('index.html', fn() => (new HomeController())->index());
$router->get('page/{page}', fn($page) => (new HomeController())->index((int)$page));

// 视频 - 模式1
$router->get('vod/type/{type}', fn($type) => (new VodController())->type((int)$type));
$router->get('vod/type/{type}/page/{page}', fn($type, $page) => (new VodController())->type((int)$type, (int)$page));
$router->get('vod/detail/{id}', fn($id) => (new VodController())->detail((int)$id));
$router->get('vod/play/{id}/{sid}/{nid}', fn($id, $sid, $nid) => (new VodController())->play((int)$id, (int)$sid, (int)$nid));
// 视频 - 模式2 (.html)
$router->get('vod/{id}.html', fn($id) => (new VodController())->detail((int)$id));
$router->get('play/{id}-{sid}-{nid}.html', fn($id, $sid, $nid) => (new VodController())->play((int)$id, (int)$sid, (int)$nid));
// 视频 - slug模式
$router->get('video/{slug}', fn($slug) => (new VodController())->detailBySlug($slug));
$router->get('video/{slug}.html', fn($slug) => (new VodController())->detailBySlug($slug));
$router->get('watch/{slug}/{sid}/{nid}', fn($slug, $sid, $nid) => (new VodController())->playBySlug($slug, (int)$sid, (int)$nid));
$router->get('watch/{slug}-{sid}-{nid}.html', fn($slug, $sid, $nid) => (new VodController())->playBySlug($slug, (int)$sid, (int)$nid));

// 分类 - 模式1
$router->get('type/{id}', fn($id) => (new TypeController())->index((int)$id));
$router->get('type/{id}/page/{page}', fn($id, $page) => (new TypeController())->index((int)$id, (int)$page));
// 分类 - 模式2 (.html)
$router->get('type/{id}.html', fn($id) => (new TypeController())->index((int)$id));
$router->get('type/{id}-{page}.html', fn($id, $page) => (new TypeController())->index((int)$id, (int)$page));
// 分类 - slug模式
$router->get('category/{slug}', fn($slug) => (new TypeController())->indexBySlug($slug));
$router->get('category/{slug}.html', fn($slug) => (new TypeController())->indexBySlug($slug));
$router->get('category/{slug}/{page}', fn($slug, $page) => (new TypeController())->indexBySlug($slug, (int)$page));

// 演员 - 模式1
$router->get('actor', fn() => (new ActorController())->index());
$router->get('actor/page/{page}', fn($page) => (new ActorController())->index((int)$page));
$router->get('actor/detail/{id}', fn($id) => (new ActorController())->detail((int)$id));
// 演员 - 模式2 (.html)
$router->get('actor/{id}.html', fn($id) => (new ActorController())->detail((int)$id));
// 演员 - slug模式
$router->get('star/{slug}', fn($slug) => (new ActorController())->detailBySlug($slug));
$router->get('star/{slug}.html', fn($slug) => (new ActorController())->detailBySlug($slug));

// 文章 - 模式1
$router->get('art', fn() => (new ArtController())->index());
$router->get('art/page/{page}', fn($page) => (new ArtController())->index((int)$page));
$router->get('art/detail/{id}', fn($id) => (new ArtController())->detail((int)$id));
// 文章 - 模式2 (.html)
$router->get('art/{id}.html', fn($id) => (new ArtController())->detail((int)$id));
// 文章 - slug模式
$router->get('article/{slug}', fn($slug) => (new ArtController())->detailBySlug($slug));
$router->get('article/{slug}.html', fn($slug) => (new ArtController())->detailBySlug($slug));

// 搜索
$router->get('search', fn() => (new SearchController())->index());
$router->get('search/page/{page}', fn($page) => (new SearchController())->index((int)$page));
$router->get('search/{keyword}', fn($keyword) => (new SearchController())->index(1, $keyword));
$router->get('search/{keyword}/page/{page}', fn($keyword, $page) => (new SearchController())->index((int)$page, $keyword));

// 用户
$router->get('user/login', fn() => (new UserController())->login());
$router->post('user/login', fn() => (new UserController())->doLogin());
$router->get('user/register', fn() => (new UserController())->register());
$router->post('user/register', fn() => (new UserController())->doRegister());
$router->get('user/logout', fn() => (new UserController())->logout());
$router->get('user/center', fn() => (new UserController())->center());

// 友链
require_once CTRL_PATH . 'LinkController.php';
$router->get('link', fn() => (new LinkController())->index());
$router->post('link/apply', fn() => (new LinkController())->apply());

// 单页面
require_once CTRL_PATH . 'PageController.php';
$router->get('page/{slug}', fn($slug) => (new PageController())->show($slug));
$router->get('about', fn() => (new PageController())->show('about'));
$router->get('contact', fn() => (new PageController())->show('contact'));
$router->get('disclaimer', fn() => (new PageController())->show('disclaimer'));

// 评论
require_once CTRL_PATH . 'CommentController.php';
$router->get('comment/list', fn() => (new CommentController())->list());
$router->get('comment/replies', fn() => (new CommentController())->replies());
$router->post('comment/post', fn() => (new CommentController())->postComment());
$router->post('comment/vote', fn() => (new CommentController())->vote());
$router->post('comment/delete', fn() => (new CommentController())->delete());

// 评分
require_once CTRL_PATH . 'ScoreController.php';
$router->post('score/rate', fn() => (new ScoreController())->rate());
$router->get('score/stats', fn() => (new ScoreController())->stats());

// 短视频/短剧
require_once CTRL_PATH . 'ShortController.php';
$router->get('short', fn() => (new ShortController())->index());
$router->get('short/drama', fn() => (new ShortController())->drama());
$router->get('short/drama/page/{page}', fn($page) => (new ShortController())->drama((int)$page));
$router->get('short/detail/{id}', fn($id) => (new ShortController())->detail((int)$id));
$router->get('short/play/{id}', fn($id) => (new ShortController())->play((int)$id));
$router->get('short/play/{id}/{ep}', fn($id, $ep) => (new ShortController())->play((int)$id, (int)$ep));
$router->get('short/api/list', fn() => (new ShortController())->apiList());
$router->get('short/api/random', fn() => (new ShortController())->apiRandom());
$router->get('short/api/detail', fn() => (new ShortController())->apiDetail());
$router->post('short/api/like', fn() => (new ShortController())->apiLike());

// 分发路由
$router->dispatch();
