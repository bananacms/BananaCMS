<?php
/**
 * 香蕉CMS API入口（APP/客户端专用）
 * Powered by https://xpornkit.com
 * 
 * 接口文档见 README.md
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
require_once CORE_PATH . 'Database.php';
require_once CORE_PATH . 'Cache.php';
require_once CORE_PATH . 'RedisSession.php';

// 初始化 Session（用于前端收藏等功能）
if (session_status() === PHP_SESSION_NONE) {
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

// 加载模型
require_once MODEL_PATH . 'Model.php';
require_once MODEL_PATH . 'Vod.php';
require_once MODEL_PATH . 'Type.php';
require_once MODEL_PATH . 'Actor.php';
require_once MODEL_PATH . 'Art.php';
require_once MODEL_PATH . 'User.php';

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Token');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// 初始化API
$api = new XpkApi();
$api->dispatch();

/**
 * API 类
 */
class XpkApi
{
    private ?array $user = null;
    private XpkDatabase $db;

    public function __construct()
    {
        $this->db = XpkDatabase::getInstance();
    }

    /**
     * 路由分发
     */
    public function dispatch(): void
    {
        $action = $_GET['action'] ?? '';
        
        // 需要登录的接口
        $authActions = [
            'user.info', 'user.update', 'user.password',
            'favorite.list', 'favorite.add', 'favorite.remove', 'favorite.check',
            'history.list', 'history.add', 'history.remove', 'history.clear',
            'comment.post', 'comment.delete',
        ];
        
        if (in_array($action, $authActions)) {
            $this->checkAuth();
        }

        switch ($action) {
            // 系统
            case 'config': $this->config(); break;
            case 'init': $this->appInit(); break;
            
            // 用户
            case 'user.register': $this->userRegister(); break;
            case 'user.login': $this->userLogin(); break;
            case 'user.info': $this->userInfo(); break;
            case 'user.update': $this->userUpdate(); break;
            case 'user.password': $this->userPassword(); break;
            
            // 视频
            case 'vod.list': $this->vodList(); break;
            case 'vod.detail': $this->vodDetail(); break;
            case 'vod.play': $this->vodPlay(); break;
            case 'vod.related': $this->vodRelated(); break;
            
            // 分类
            case 'type.list': $this->typeList(); break;
            case 'type.vods': $this->typeVods(); break;
            
            // 演员
            case 'actor.list': $this->actorList(); break;
            case 'actor.detail': $this->actorDetail(); break;
            
            // 文章
            case 'art.list': $this->artList(); break;
            case 'art.detail': $this->artDetail(); break;
            
            // 搜索
            case 'search': $this->search(); break;
            case 'search.hot': $this->searchHot(); break;
            case 'search.suggest': $this->searchSuggest(); break;
            
            // 收藏
            case 'favorite.list': $this->favoriteList(); break;
            case 'favorite.add': $this->favoriteAdd(); break;
            case 'favorite.remove': $this->favoriteRemove(); break;
            case 'favorite.check': $this->favoriteCheck(); break;
            
            // 历史
            case 'history.list': $this->historyList(); break;
            case 'history.add': $this->historyAdd(); break;
            case 'history.remove': $this->historyRemove(); break;
            case 'history.clear': $this->historyClear(); break;
            
            // 评论
            case 'comment.list': $this->commentList(); break;
            case 'comment.post': $this->commentPost(); break;
            case 'comment.delete': $this->commentDelete(); break;
            case 'comment.vote': $this->commentVote(); break;
            
            // 评分
            case 'score.submit': $this->scoreSubmit(); break;
            case 'score.stats': $this->scoreStats(); break;
            
            // 短视频
            case 'short.list': $this->shortList(); break;
            case 'short.detail': $this->shortDetail(); break;
            case 'short.like': $this->shortLike(); break;
            
            // 广告
            case 'ad.get': $this->adGet(); break;
            case 'ad.click': $this->adClick(); break;
            case 'ad.show': $this->adShow(); break;
            
            // 首页
            case 'home': $this->home(); break;
            
            // 前端收藏（简化版）
            case 'collect': $this->collect(); break;
            
            default: $this->error('未知接口'); break;
        }
    }

    // ==================== 系统接口 ====================
    
    /**
     * 获取配置
     */
    private function config(): void
    {
        $this->success([
            'site_name' => $this->getConfig('site_name', SITE_NAME),
            'site_logo' => $this->getConfig('site_logo', ''),
            'site_notice' => $this->getConfig('site_notice', ''),
            'player_config' => [
                'autoplay' => true,
                'muted' => false,
            ],
        ]);
    }

    /**
     * APP初始化（一次性获取多项数据）
     */
    private function appInit(): void
    {
        $typeModel = new XpkType();
        $vodModel = new XpkVod();
        
        $this->success([
            'config' => [
                'site_name' => $this->getConfig('site_name', SITE_NAME),
                'site_logo' => $this->getConfig('site_logo', ''),
            ],
            'types' => $typeModel->getTree(),
            'banners' => $this->getBanners(),
            'hot_search' => $this->getHotSearch(10),
        ]);
    }

    // ==================== 用户接口 ====================

    /**
     * 用户注册
     */
    private function userRegister(): void
    {
        $username = trim($this->input('username', ''));
        $password = $this->input('password', '');
        $email = trim($this->input('email', ''));

        if (strlen($username) < 3 || strlen($username) > 20) {
            $this->error('用户名3-20个字符');
        }
        if (strlen($password) < 6) {
            $this->error('密码至少6位');
        }
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('邮箱格式错误');
        }

        $userModel = new XpkUser();
        if ($userModel->findByUsername($username)) {
            $this->error('用户名已存在');
        }

        $userId = $userModel->register($username, $password, $email);
        if (!$userId) {
            $this->error('注册失败');
        }

        $token = $this->generateToken($userId);
        $this->success([
            'user_id' => $userId,
            'token' => $token,
        ]);
    }

    /**
     * 用户登录
     */
    private function userLogin(): void
    {
        $username = trim($this->input('username', ''));
        $password = $this->input('password', '');

        if (empty($username) || empty($password)) {
            $this->error('请输入用户名和密码');
        }

        $userModel = new XpkUser();
        $user = $userModel->login($username, $password);
        
        if (!$user) {
            $this->error('用户名或密码错误');
        }
        if ($user['user_status'] != 1) {
            $this->error('账号已被禁用');
        }

        $token = $this->generateToken($user['user_id']);
        
        $this->success([
            'user_id' => $user['user_id'],
            'username' => $user['user_name'],
            'nickname' => $user['user_nick_name'] ?: $user['user_name'],
            'avatar' => $user['user_pic'],
            'token' => $token,
        ]);
    }

    /**
     * 获取用户信息
     */
    private function userInfo(): void
    {
        $this->success([
            'user_id' => $this->user['user_id'],
            'username' => $this->user['user_name'],
            'nickname' => $this->user['user_nick_name'] ?: $this->user['user_name'],
            'email' => $this->user['user_email'],
            'avatar' => $this->user['user_pic'],
            'points' => $this->user['user_points'],
            'reg_time' => date('Y-m-d', $this->user['user_reg_time']),
        ]);
    }

    /**
     * 更新用户信息
     */
    private function userUpdate(): void
    {
        $nickname = trim($this->input('nickname', ''));
        $avatar = trim($this->input('avatar', ''));

        $data = [];
        if ($nickname) $data['user_nick_name'] = mb_substr($nickname, 0, 20);
        if ($avatar) $data['user_pic'] = $avatar;

        if (empty($data)) {
            $this->error('无更新内容');
        }

        $userModel = new XpkUser();
        $userModel->update($this->user['user_id'], $data);
        $this->success('更新成功');
    }

    /**
     * 修改密码
     */
    private function userPassword(): void
    {
        $oldPwd = $this->input('old_password', '');
        $newPwd = $this->input('new_password', '');

        if (strlen($newPwd) < 6) {
            $this->error('新密码至少6位');
        }

        if (!password_verify($oldPwd, $this->user['user_pwd'])) {
            $this->error('原密码错误');
        }

        $userModel = new XpkUser();
        $userModel->update($this->user['user_id'], [
            'user_pwd' => password_hash($newPwd, PASSWORD_DEFAULT)
        ]);
        
        $this->success('密码修改成功');
    }

    // ==================== 视频接口 ====================

    /**
     * 视频列表
     */
    private function vodList(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $typeId = (int)($_GET['type'] ?? 0) ?: null;
        $order = in_array($_GET['order'] ?? '', ['time', 'hits', 'score', 'up']) ? $_GET['order'] : 'time';
        $year = $_GET['year'] ?? '';
        $area = $_GET['area'] ?? '';

        $vodModel = new XpkVod();
        
        $where = ['vod_status' => 1];
        if ($typeId) $where['vod_type_id'] = $typeId;
        if ($year) $where['vod_year'] = $year;
        if ($area) $where['vod_area'] = $area;

        $list = $vodModel->getList($limit, $order, $typeId, $page);
        $total = $vodModel->count($where);

        $this->success([
            'list' => array_map([$this, 'formatVod'], $list),
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit),
        ]);
    }

    /**
     * 视频详情
     */
    private function vodDetail(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->error('参数错误');

        $vodModel = new XpkVod();
        $vod = $vodModel->getDetail($id);
        
        if (!$vod) $this->error('视频不存在');

        $vodModel->incHits($id);

        // 获取评分
        $scoreStats = $this->getScoreStats('vod', $id);

        $this->success([
            'vod' => $this->formatVodDetail($vod),
            'score' => $scoreStats,
            'is_favorite' => $this->user ? $this->isFavorite($id) : false,
        ]);
    }

    /**
     * 获取播放信息
     */
    private function vodPlay(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $sid = (int)($_GET['sid'] ?? 1);
        $nid = (int)($_GET['nid'] ?? 1);

        if ($id <= 0) $this->error('参数错误');

        $vodModel = new XpkVod();
        $vod = $vodModel->getDetail($id);
        
        if (!$vod) $this->error('视频不存在');

        $playData = $vodModel->parsePlayUrl($vod['vod_play_from'], $vod['vod_play_url']);
        
        if (!isset($playData[$sid - 1][$nid - 1])) {
            $this->error('播放源不存在');
        }

        $current = $playData[$sid - 1][$nid - 1];
        
        // 获取用户播放进度
        $progress = 0;
        if ($this->user) {
            $history = $this->getHistory($id);
            if ($history) $progress = $history['progress'];
        }

        $this->success([
            'vod_id' => $id,
            'vod_name' => $vod['vod_name'],
            'episode' => $current['name'],
            'play_url' => $current['url'],
            'sid' => $sid,
            'nid' => $nid,
            'progress' => $progress,
            'has_next' => isset($playData[$sid - 1][$nid]),
            'has_prev' => $nid > 1,
        ]);
    }

    /**
     * 相关推荐
     */
    private function vodRelated(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $limit = min(20, max(1, (int)($_GET['limit'] ?? 10)));

        if ($id <= 0) $this->error('参数错误');

        $vodModel = new XpkVod();
        $vod = $vodModel->find($id);
        
        if (!$vod) $this->error('视频不存在');

        $list = $vodModel->getRelated($vod['vod_type_id'], $id, $limit);
        
        $this->success(array_map([$this, 'formatVod'], $list));
    }

    // ==================== 分类接口 ====================

    /**
     * 分类列表
     */
    private function typeList(): void
    {
        $typeModel = new XpkType();
        $this->success($typeModel->getTree());
    }

    /**
     * 分类下的视频
     */
    private function typeVods(): void
    {
        $typeId = (int)($_GET['id'] ?? 0);
        if ($typeId <= 0) $this->error('参数错误');

        $_GET['type'] = $typeId;
        $this->vodList();
    }

    // ==================== 演员接口 ====================

    /**
     * 演员列表
     */
    private function actorList(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));

        $actorModel = new XpkActor();
        $list = $actorModel->getList($limit, 'id', $page);
        $total = $actorModel->count(['actor_status' => 1]);

        $this->success([
            'list' => $list,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit),
        ]);
    }

    /**
     * 演员详情
     */
    private function actorDetail(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->error('参数错误');

        $actorModel = new XpkActor();
        $actor = $actorModel->find($id);
        
        if (!$actor || $actor['actor_status'] != 1) {
            $this->error('演员不存在');
        }

        // 获取演员作品
        $vodModel = new XpkVod();
        $vods = $vodModel->getByActor($actor['actor_name'], 20);

        $this->success([
            'actor' => $actor,
            'vods' => array_map([$this, 'formatVod'], $vods),
        ]);
    }

    // ==================== 文章接口 ====================

    /**
     * 文章列表
     */
    private function artList(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $typeId = (int)($_GET['type'] ?? 0) ?: null;

        $artModel = new XpkArt();
        $list = $artModel->getList($limit, $typeId, $page);
        $total = $artModel->count($typeId ? ['art_type_id' => $typeId, 'art_status' => 1] : ['art_status' => 1]);

        $this->success([
            'list' => $list,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit),
        ]);
    }

    /**
     * 文章详情
     */
    private function artDetail(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->error('参数错误');

        $artModel = new XpkArt();
        $art = $artModel->getDetail($id);
        
        if (!$art) $this->error('文章不存在');

        $artModel->incHits($id);
        $this->success($art);
    }

    // ==================== 搜索接口 ====================

    /**
     * 搜索
     */
    private function search(): void
    {
        $keyword = trim($_GET['wd'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $type = $_GET['type'] ?? 'vod'; // vod/actor/art

        if (empty($keyword)) $this->error('请输入搜索关键词');

        // 记录搜索词
        $this->recordSearch($keyword);

        if ($type === 'actor') {
            $actorModel = new XpkActor();
            $result = $actorModel->search($keyword, $page, $limit);
        } elseif ($type === 'art') {
            $artModel = new XpkArt();
            $result = $artModel->search($keyword, $page, $limit);
        } else {
            $vodModel = new XpkVod();
            $result = $vodModel->search($keyword, $page, $limit);
            $result['list'] = array_map([$this, 'formatVod'], $result['list']);
        }

        $this->success([
            'list' => $result['list'],
            'page' => $page,
            'limit' => $limit,
            'total' => $result['total'],
            'pages' => ceil($result['total'] / $limit),
        ]);
    }

    /**
     * 热门搜索
     */
    private function searchHot(): void
    {
        $limit = min(20, max(1, (int)($_GET['limit'] ?? 10)));
        $this->success($this->getHotSearch($limit));
    }

    /**
     * 搜索建议
     */
    private function searchSuggest(): void
    {
        $keyword = trim($_GET['wd'] ?? '');
        $limit = min(10, max(1, (int)($_GET['limit'] ?? 5)));

        if (empty($keyword)) {
            $this->success([]);
            return;
        }

        $vodModel = new XpkVod();
        $list = $this->db->query(
            "SELECT vod_id, vod_name FROM " . DB_PREFIX . "vod 
             WHERE vod_status = 1 AND vod_name LIKE ? 
             ORDER BY vod_hits DESC LIMIT {$limit}",
            ['%' . $keyword . '%']
        );

        $this->success(array_column($list, 'vod_name'));
    }

    // ==================== 收藏接口 ====================

    /**
     * 收藏列表
     */
    private function favoriteList(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $table = DB_PREFIX . 'user_favorite';
        
        $list = $this->db->query(
            "SELECT f.*, v.vod_name, v.vod_pic, v.vod_remarks, v.vod_score 
             FROM {$table} f 
             LEFT JOIN " . DB_PREFIX . "vod v ON f.vod_id = v.vod_id 
             WHERE f.user_id = ? 
             ORDER BY f.fav_time DESC 
             LIMIT {$limit} OFFSET {$offset}",
            [$this->user['user_id']]
        );

        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM {$table} WHERE user_id = ?",
            [$this->user['user_id']]
        )['cnt'] ?? 0;

        $this->success([
            'list' => $list,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit),
        ]);
    }

    /**
     * 添加收藏
     */
    private function favoriteAdd(): void
    {
        $vodId = (int)$this->input('vod_id', 0);
        if ($vodId <= 0) $this->error('参数错误');

        if ($this->isFavorite($vodId)) {
            $this->error('已收藏');
        }

        $table = DB_PREFIX . 'user_favorite';
        $this->db->execute(
            "INSERT INTO {$table} (user_id, vod_id, fav_time) VALUES (?, ?, ?)",
            [$this->user['user_id'], $vodId, time()]
        );

        $this->success('收藏成功');
    }

    /**
     * 取消收藏
     */
    private function favoriteRemove(): void
    {
        $vodId = (int)$this->input('vod_id', 0);
        if ($vodId <= 0) $this->error('参数错误');

        $table = DB_PREFIX . 'user_favorite';
        $this->db->execute(
            "DELETE FROM {$table} WHERE user_id = ? AND vod_id = ?",
            [$this->user['user_id'], $vodId]
        );

        $this->success('已取消收藏');
    }

    /**
     * 检查是否收藏
     */
    private function favoriteCheck(): void
    {
        $vodId = (int)($_GET['vod_id'] ?? 0);
        $this->success(['is_favorite' => $this->isFavorite($vodId)]);
    }

    // ==================== 历史记录接口 ====================

    /**
     * 历史列表
     */
    private function historyList(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $table = DB_PREFIX . 'user_history';
        
        $list = $this->db->query(
            "SELECT h.*, v.vod_name, v.vod_pic, v.vod_remarks 
             FROM {$table} h 
             LEFT JOIN " . DB_PREFIX . "vod v ON h.vod_id = v.vod_id 
             WHERE h.user_id = ? 
             ORDER BY h.watch_time DESC 
             LIMIT {$limit} OFFSET {$offset}",
            [$this->user['user_id']]
        );

        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM {$table} WHERE user_id = ?",
            [$this->user['user_id']]
        )['cnt'] ?? 0;

        $this->success([
            'list' => $list,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit),
        ]);
    }

    /**
     * 添加/更新历史
     */
    private function historyAdd(): void
    {
        $vodId = (int)$this->input('vod_id', 0);
        $sid = (int)$this->input('sid', 1);
        $nid = (int)$this->input('nid', 1);
        $progress = (int)$this->input('progress', 0);
        $duration = (int)$this->input('duration', 0);

        if ($vodId <= 0) $this->error('参数错误');

        $table = DB_PREFIX . 'user_history';
        $exists = $this->db->queryOne(
            "SELECT history_id FROM {$table} WHERE user_id = ? AND vod_id = ?",
            [$this->user['user_id'], $vodId]
        );

        if ($exists) {
            $this->db->execute(
                "UPDATE {$table} SET sid = ?, nid = ?, progress = ?, duration = ?, watch_time = ? WHERE history_id = ?",
                [$sid, $nid, $progress, $duration, time(), $exists['history_id']]
            );
        } else {
            $this->db->execute(
                "INSERT INTO {$table} (user_id, vod_id, sid, nid, progress, duration, watch_time) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$this->user['user_id'], $vodId, $sid, $nid, $progress, $duration, time()]
            );
        }

        $this->success('ok');
    }

    /**
     * 删除历史
     */
    private function historyRemove(): void
    {
        $vodId = (int)$this->input('vod_id', 0);
        if ($vodId <= 0) $this->error('参数错误');

        $table = DB_PREFIX . 'user_history';
        $this->db->execute(
            "DELETE FROM {$table} WHERE user_id = ? AND vod_id = ?",
            [$this->user['user_id'], $vodId]
        );

        $this->success('已删除');
    }

    /**
     * 清空历史
     */
    private function historyClear(): void
    {
        $table = DB_PREFIX . 'user_history';
        $this->db->execute(
            "DELETE FROM {$table} WHERE user_id = ?",
            [$this->user['user_id']]
        );

        $this->success('已清空');
    }

    // ==================== 评论接口 ====================

    /**
     * 评论列表
     */
    private function commentList(): void
    {
        $type = $_GET['type'] ?? 'vod';
        $targetId = (int)($_GET['id'] ?? 0);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));

        if ($targetId <= 0) $this->error('参数错误');

        require_once MODEL_PATH . 'Comment.php';
        $commentModel = new XpkComment();
        $result = $commentModel->getList($type, $targetId, $page, $limit);

        $this->success($result);
    }

    /**
     * 发表评论
     */
    private function commentPost(): void
    {
        $type = $this->input('type', 'vod');
        $targetId = (int)$this->input('target_id', 0);
        $content = trim($this->input('content', ''));
        $parentId = (int)$this->input('parent_id', 0);

        if ($targetId <= 0) $this->error('参数错误');
        if (empty($content)) $this->error('请输入评论内容');

        require_once MODEL_PATH . 'Comment.php';
        $commentModel = new XpkComment();
        
        $commentId = $commentModel->add([
            'comment_type' => $type,
            'target_id' => $targetId,
            'user_id' => $this->user['user_id'],
            'parent_id' => $parentId,
            'comment_content' => $content,
        ]);

        $this->success(['comment_id' => $commentId]);
    }

    /**
     * 删除评论
     */
    private function commentDelete(): void
    {
        $id = (int)$this->input('id', 0);
        if ($id <= 0) $this->error('参数错误');

        require_once MODEL_PATH . 'Comment.php';
        $commentModel = new XpkComment();
        $comment = $commentModel->find($id);

        if (!$comment || $comment['user_id'] != $this->user['user_id']) {
            $this->error('无权删除');
        }

        $commentModel->delete($id);
        $this->success('已删除');
    }

    /**
     * 评论投票
     */
    private function commentVote(): void
    {
        $id = (int)$this->input('id', 0);
        $action = $this->input('action', 'up');

        if ($id <= 0) $this->error('参数错误');

        require_once MODEL_PATH . 'Comment.php';
        $commentModel = new XpkComment();
        
        $userId = $this->user ? $this->user['user_id'] : 0;
        $result = $commentModel->vote($id, $userId, $action);

        $this->success($result);
    }

    // ==================== 评分接口 ====================

    /**
     * 提交评分
     */
    private function scoreSubmit(): void
    {
        $type = $this->input('type', 'vod');
        $targetId = (int)$this->input('target_id', 0);
        $score = (int)$this->input('score', 0);

        if ($targetId <= 0) $this->error('参数错误');
        if ($score < 1 || $score > 10) $this->error('评分1-10分');

        require_once MODEL_PATH . 'Score.php';
        $scoreModel = new XpkScore();
        
        $userId = $this->user ? $this->user['user_id'] : 0;
        $result = $scoreModel->rate($type, $targetId, $userId, $score);

        $this->success($result);
    }

    /**
     * 评分统计
     */
    private function scoreStats(): void
    {
        $type = $_GET['type'] ?? 'vod';
        $targetId = (int)($_GET['id'] ?? 0);

        if ($targetId <= 0) $this->error('参数错误');

        $this->success($this->getScoreStats($type, $targetId));
    }

    // ==================== 短视频接口 ====================

    /**
     * 短视频列表
     */
    private function shortList(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(20, max(1, (int)($_GET['limit'] ?? 10)));
        $type = $_GET['type'] ?? ''; // video/drama

        require_once MODEL_PATH . 'Short.php';
        $shortModel = new XpkShort();
        $result = $shortModel->getList($type, $page, $limit);

        $this->success($result);
    }

    /**
     * 短视频详情
     */
    private function shortDetail(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->error('参数错误');

        require_once MODEL_PATH . 'Short.php';
        $shortModel = new XpkShort();
        $short = $shortModel->getDetail($id);

        if (!$short) $this->error('不存在');

        $this->success($short);
    }

    /**
     * 短视频点赞
     */
    private function shortLike(): void
    {
        $id = (int)$this->input('id', 0);
        if ($id <= 0) $this->error('参数错误');

        require_once MODEL_PATH . 'Short.php';
        $shortModel = new XpkShort();
        $shortModel->like($id);

        $this->success('ok');
    }

    // ==================== 广告接口 ====================

    /**
     * 获取广告
     */
    private function adGet(): void
    {
        $position = trim($_GET['position'] ?? '');
        if (empty($position)) $this->error('参数错误');

        require_once MODEL_PATH . 'Ad.php';
        $adModel = new XpkAd();
        $this->success($adModel->getByPosition($position));
    }

    /**
     * 广告点击
     */
    private function adClick(): void
    {
        $id = (int)($this->input('id', 0));
        if ($id > 0) {
            require_once MODEL_PATH . 'Ad.php';
            (new XpkAd())->incrementClick($id);
        }
        $this->success('ok');
    }

    /**
     * 广告展示统计
     */
    private function adShow(): void
    {
        $id = (int)($this->input('id', 0));
        if ($id > 0) {
            require_once MODEL_PATH . 'Ad.php';
            (new XpkAd())->incrementShow($id);
        }
        $this->success('ok');
    }

    // ==================== 首页接口 ====================

    /**
     * 首页数据
     */
    private function home(): void
    {
        $vodModel = new XpkVod();
        $typeModel = new XpkType();

        $cache = xpk_cache();
        
        $data = $cache->remember('api_home', 300, function() use ($vodModel, $typeModel) {
            $types = $typeModel->getAll();
            $sections = [];

            // 轮播/推荐
            $sections['recommend'] = array_map([$this, 'formatVod'], $vodModel->getList(6, 'hits'));

            // 最新更新
            $sections['latest'] = array_map([$this, 'formatVod'], $vodModel->getList(12, 'time'));

            // 各分类数据
            foreach ($types as $type) {
                if ($type['type_pid'] == 0) {
                    $sections['type_' . $type['type_id']] = [
                        'type_id' => $type['type_id'],
                        'type_name' => $type['type_name'],
                        'list' => array_map([$this, 'formatVod'], $vodModel->getList(6, 'time', $type['type_id'])),
                    ];
                }
            }

            return $sections;
        });

        $data['banners'] = $this->getBanners();

        $this->success($data);
    }

    // ==================== 前端收藏接口 ====================

    /**
     * 前端收藏/取消收藏（基于Session）
     */
    private function collect(): void
    {
        // 检查Session登录
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            $this->error('请先登录', 2);
        }

        $type = $this->input('type', 'vod');
        $id = (int)$this->input('id', 0);

        if ($id <= 0) {
            $this->error('参数错误');
        }

        $table = DB_PREFIX . 'collect';
        
        // 检查是否已收藏
        $exists = $this->db->queryOne(
            "SELECT collect_id FROM {$table} WHERE user_id = ? AND collect_type = ? AND collect_rel_id = ?",
            [$userId, $type, $id]
        );

        if ($exists) {
            // 取消收藏
            $this->db->execute(
                "DELETE FROM {$table} WHERE collect_id = ?",
                [$exists['collect_id']]
            );
            $this->success('已取消收藏');
        } else {
            // 添加收藏
            $this->db->execute(
                "INSERT INTO {$table} (user_id, collect_type, collect_rel_id, collect_time) VALUES (?, ?, ?, ?)",
                [$userId, $type, $id, time()]
            );
            $this->success('收藏成功');
        }
    }

    // ==================== 辅助方法 ====================

    /**
     * 验证登录
     */
    private function checkAuth(): void
    {
        $token = $_SERVER['HTTP_X_TOKEN'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_GET['token'] ?? '';
        $token = str_replace('Bearer ', '', $token);

        if (empty($token)) {
            $this->error('请先登录', 401);
        }

        $userId = $this->verifyToken($token);
        if (!$userId) {
            $this->error('登录已过期', 401);
        }

        $userModel = new XpkUser();
        $this->user = $userModel->find($userId);

        if (!$this->user || $this->user['user_status'] != 1) {
            $this->error('账号不可用', 401);
        }
    }

    /**
     * 生成Token
     */
    private function generateToken(int $userId): string
    {
        $payload = [
            'uid' => $userId,
            'exp' => time() + 86400 * 30, // 30天
            'iat' => time(),
        ];
        
        $data = base64_encode(json_encode($payload));
        $sign = hash_hmac('sha256', $data, APP_SECRET);
        
        return $data . '.' . $sign;
    }

    /**
     * 验证Token
     */
    private function verifyToken(string $token): ?int
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) return null;

        [$data, $sign] = $parts;
        
        if (hash_hmac('sha256', $data, APP_SECRET) !== $sign) {
            return null;
        }

        $payload = json_decode(base64_decode($data), true);
        if (!$payload || !isset($payload['uid'], $payload['exp'])) {
            return null;
        }

        if ($payload['exp'] < time()) {
            return null;
        }

        return (int)$payload['uid'];
    }

    /**
     * 获取输入
     */
    private function input(string $key, mixed $default = null): mixed
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = json_decode(file_get_contents('php://input'), true);
            if ($json && isset($json[$key])) {
                return $json[$key];
            }
            return $_POST[$key] ?? $default;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * 获取配置
     */
    private function getConfig(string $key, mixed $default = ''): mixed
    {
        static $configs = null;
        if ($configs === null) {
            $list = $this->db->query("SELECT config_name, config_value FROM " . DB_PREFIX . "config");
            $configs = array_column($list, 'config_value', 'config_name');
        }
        return $configs[$key] ?? $default;
    }

    /**
     * 格式化视频（列表用）
     */
    private function formatVod(array $vod): array
    {
        return [
            'vod_id' => $vod['vod_id'],
            'vod_name' => $vod['vod_name'],
            'vod_pic' => $vod['vod_pic'],
            'vod_remarks' => $vod['vod_remarks'],
            'vod_score' => $vod['vod_score'],
            'vod_year' => $vod['vod_year'],
            'vod_area' => $vod['vod_area'],
            'type_name' => $vod['type_name'] ?? '',
        ];
    }

    /**
     * 格式化视频详情
     */
    private function formatVodDetail(array $vod): array
    {
        $vodModel = new XpkVod();
        $playData = $vodModel->parsePlayUrl($vod['vod_play_from'], $vod['vod_play_url']);
        
        return [
            'vod_id' => $vod['vod_id'],
            'vod_name' => $vod['vod_name'],
            'vod_sub' => $vod['vod_sub'],
            'vod_pic' => $vod['vod_pic'],
            'vod_actor' => $vod['vod_actor'],
            'vod_director' => $vod['vod_director'],
            'vod_year' => $vod['vod_year'],
            'vod_area' => $vod['vod_area'],
            'vod_lang' => $vod['vod_lang'],
            'vod_score' => $vod['vod_score'],
            'vod_hits' => $vod['vod_hits'],
            'vod_remarks' => $vod['vod_remarks'],
            'vod_content' => strip_tags($vod['vod_content']),
            'type_name' => $vod['type_name'] ?? '',
            'play_from' => explode('$$$', $vod['vod_play_from']),
            'play_list' => $playData,
        ];
    }

    /**
     * 是否已收藏
     */
    private function isFavorite(int $vodId): bool
    {
        if (!$this->user) return false;
        
        $table = DB_PREFIX . 'user_favorite';
        $row = $this->db->queryOne(
            "SELECT fav_id FROM {$table} WHERE user_id = ? AND vod_id = ?",
            [$this->user['user_id'], $vodId]
        );
        return !empty($row);
    }

    /**
     * 获取历史记录
     */
    private function getHistory(int $vodId): ?array
    {
        if (!$this->user) return null;
        
        $table = DB_PREFIX . 'user_history';
        return $this->db->queryOne(
            "SELECT * FROM {$table} WHERE user_id = ? AND vod_id = ?",
            [$this->user['user_id'], $vodId]
        );
    }

    /**
     * 获取评分统计
     */
    private function getScoreStats(string $type, int $targetId): array
    {
        require_once MODEL_PATH . 'Score.php';
        $scoreModel = new XpkScore();
        return $scoreModel->getStats($type, $targetId);
    }

    /**
     * 获取轮播图
     */
    private function getBanners(): array
    {
        require_once MODEL_PATH . 'Ad.php';
        $adModel = new XpkAd();
        return $adModel->getByPosition('home_top');
    }

    /**
     * 获取热门搜索
     */
    private function getHotSearch(int $limit = 10): array
    {
        $table = DB_PREFIX . 'search_log';
        
        // 检查表是否存在
        try {
            $list = $this->db->query(
                "SELECT keyword, COUNT(*) as cnt FROM {$table} 
                 WHERE search_time > ? 
                 GROUP BY keyword 
                 ORDER BY cnt DESC 
                 LIMIT {$limit}",
                [time() - 86400 * 7]
            );
            return array_column($list, 'keyword');
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 记录搜索
     */
    private function recordSearch(string $keyword): void
    {
        $table = DB_PREFIX . 'search_log';
        try {
            $this->db->execute(
                "INSERT INTO {$table} (keyword, search_time, search_ip) VALUES (?, ?, ?)",
                [$keyword, time(), $_SERVER['REMOTE_ADDR'] ?? '']
            );
        } catch (Exception $e) {
            // 表不存在则忽略
        }
    }

    /**
     * 成功响应
     */
    private function success(mixed $data): void
    {
        echo json_encode([
            'code' => 0,
            'msg' => 'success',
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 错误响应
     */
    private function error(string $msg, int $code = 1): void
    {
        http_response_code($code === 401 ? 401 : 200);
        echo json_encode([
            'code' => $code,
            'msg' => $msg,
            'data' => null,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
