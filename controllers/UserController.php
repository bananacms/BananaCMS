<?php
/**
 * 用户控制器
 * Powered by https://xpornkit.com
 */

class UserController extends BaseController
{
    private XpkUser $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new XpkUser();
    }

    /**
     * 登录页
     */
    public function login(): void
    {
        if ($this->checkLogin()) {
            $redirect = $_GET['redirect'] ?? '';
            $this->redirect($redirect ?: xpk_url('user/center'));
            return;
        }
        
        // 记录来源页面
        $redirect = $_GET['redirect'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
        // 过滤掉登录/注册页面本身
        if ($redirect && (strpos($redirect, '/user/login') !== false || strpos($redirect, '/user/register') !== false)) {
            $redirect = '';
        }
        
        $this->assign('redirect', $redirect);
        $this->assign('csrfToken', $this->generateCsrfToken());
        $this->assign('title', '用户登录 - ' . SITE_NAME);
        $this->assign('noindex', true);
        $this->render('user/login');
    }

    /**
     * 处理登录
     */
    public function doLogin(): void
    {
        $this->requireCsrf();
        
        $username = trim($this->post('username', ''));
        $password = $this->post('password', '');

        if (empty($username) || empty($password)) {
            $this->error('用户名和密码不能为空');
            return;
        }

        $user = $this->userModel->findByUsername($username);
        if (!$user) {
            // 记录用户不存在的登录尝试
            $this->logSecurityEvent('login.failed', [
                'username' => $username,
                'reason' => 'user_not_found'
            ]);
            $this->error('用户不存在');
            return;
        }

        if ($user['user_status'] != 1) {
            // 记录被禁用账户的登录尝试
            $this->logSecurityEvent('login.failed', [
                'username' => $username,
                'user_id' => $user['user_id'],
                'reason' => 'account_disabled'
            ]);
            $this->error('账号已被禁用');
            return;
        }

        if (!$this->userModel->verifyPassword($password, $user['user_pwd'])) {
            // 记录登录失败事件
            $this->logSecurityEvent('login.failed', [
                'username' => $username,
                'user_id' => $user['user_id'],
                'reason' => 'wrong_password'
            ]);
            $this->error('密码错误');
            return;
        }

        // 更新登录时间
        $this->userModel->updateLoginTime($user['user_id']);

        // 保存Session
        unset($user['user_pwd']);
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['user_id']; // 兼容API收藏等功能

        // 记录登录成功事件
        $this->logUserAction(XpkEventTypes::USER_LOGIN, [
            'user_id' => $user['user_id'],
            'username' => $user['user_name']
        ]);

        // 跳转到来源页面或用户中心
        $redirect = $this->post('redirect', '');
        if (empty($redirect) || strpos($redirect, '/user/login') !== false || strpos($redirect, '/user/register') !== false) {
            $redirect = xpk_url('user/center');
        }
        
        $this->success('登录成功', ['url' => $redirect]);
    }

    /**
     * 注册页
     */
    public function register(): void
    {
        if ($this->checkLogin()) {
            $redirect = $_GET['redirect'] ?? '';
            $this->redirect($redirect ?: xpk_url('user/center'));
            return;
        }
        
        // 检查是否开放注册
        $config = $this->data['siteConfig'] ?? [];
        $registerOpen = ($config['user_register'] ?? '1');
        if ($registerOpen != '1' && $registerOpen != 1) {
            $this->assign('title', '注册已关闭 - ' . SITE_NAME);
            $this->assign('registerClosed', true);
            $this->assign('csrfToken', '');
            $this->assign('redirect', '');
            $this->render('user/register');
            return;
        }
        
        // 记录来源页面
        $redirect = $_GET['redirect'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
        if ($redirect && (strpos($redirect, '/user/login') !== false || strpos($redirect, '/user/register') !== false)) {
            $redirect = '';
        }
        
        $this->assign('registerClosed', false);
        $this->assign('redirect', $redirect);
        $this->assign('csrfToken', $this->generateCsrfToken());
        $this->assign('title', '用户注册 - ' . SITE_NAME);
        $this->assign('noindex', true);
        $this->render('user/register');
    }

    /**
     * 处理注册
     */
    public function doRegister(): void
    {
        $this->requireCsrf();
        
        // 检查是否开放注册
        $config = $this->data['siteConfig'] ?? [];
        $registerOpen = ($config['user_register'] ?? '1');
        if ($registerOpen != '1' && $registerOpen != 1) {
            $this->error('网站暂未开放注册');
            return;
        }
        
        $username = trim($this->post('username', ''));
        $password = $this->post('password', '');
        $email = trim($this->post('email', ''));

        if (empty($username) || empty($password)) {
            $this->error('用户名和密码不能为空');
            return;
        }

        if (strlen($username) < 3 || strlen($username) > 20) {
            $this->error('用户名长度3-20个字符');
            return;
        }

        if (strlen($password) < 6) {
            $this->error('密码至少6个字符');
            return;
        }

        // IP注册限制
        $ip = $this->getClientIp();
        $limitPerDay = (int)($config['user_register_limit'] ?? 5);
        if ($limitPerDay > 0) {
            $todayCount = $this->userModel->countTodayRegisterByIp($ip);
            if ($todayCount >= $limitPerDay) {
                $this->error('今日注册次数已达上限');
                return;
            }
        }

        if ($this->userModel->findByUsername($username)) {
            $this->error('用户名已存在');
            return;
        }

        if ($email && $this->userModel->findByEmail($email)) {
            $this->error('邮箱已被注册');
            return;
        }

        $userId = $this->userModel->register([
            'user_name' => $username,
            'user_pwd' => $password,
            'user_email' => $email,
            'user_nick_name' => $username,
            'user_reg_ip' => $ip,
        ]);

        if ($userId) {
            // 注册成功后自动登录
            $user = $this->userModel->find($userId);
            unset($user['user_pwd']);
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $userId;
            
            // 跳转到来源页面或用户中心
            $redirect = $this->post('redirect', '');
            if (empty($redirect) || strpos($redirect, '/user/login') !== false || strpos($redirect, '/user/register') !== false) {
                $redirect = xpk_url('user/center');
            }
            
            $this->success('注册成功', ['url' => $redirect]);
        } else {
            $this->error('注册失败');
        }
    }

    /**
     * 获取客户端IP
     */
    private function getClientIp(): string
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        // 取第一个IP（可能有多个）
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }

    /**
     * 退出登录
     */
    public function logout(): void
    {
        unset($_SESSION['user']);
        unset($_SESSION['user_id']);
        $this->redirect('/');
    }

    /**
     * 用户中心
     */
    public function center(): void
    {
        $this->requireLogin();
        
        $userId = $_SESSION['user']['user_id'];
        $db = XpkDatabase::getInstance();
        $tab = $_GET['tab'] ?? 'favorite';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        // 统计数量
        $favCount = (int)($db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "user_favorite WHERE user_id = ?",
            [$userId]
        )['cnt'] ?? 0);
        
        $historyCount = (int)($db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "user_history WHERE user_id = ?",
            [$userId]
        )['cnt'] ?? 0);
        
        $commentCount = (int)($db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "comment WHERE user_id = ?",
            [$userId]
        )['cnt'] ?? 0);
        
        // 根据tab获取对应数据
        $list = [];
        $total = 0;
        
        if ($tab === 'history') {
            $total = $historyCount;
            $list = $db->query(
                "SELECT h.*, v.vod_name, v.vod_pic, v.vod_remarks, v.vod_slug 
                 FROM " . DB_PREFIX . "user_history h 
                 LEFT JOIN " . DB_PREFIX . "vod v ON h.vod_id = v.vod_id 
                 WHERE h.user_id = ? 
                 ORDER BY h.watch_time DESC 
                 LIMIT {$limit} OFFSET {$offset}",
                [$userId]
            );
        } elseif ($tab === 'comment') {
            $total = $commentCount;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            $list = $db->query(
                "SELECT c.*, v.vod_name, v.vod_slug 
                 FROM " . DB_PREFIX . "comment c 
                 LEFT JOIN " . DB_PREFIX . "vod v ON c.target_id = v.vod_id AND c.comment_type = 'vod'
                 WHERE c.user_id = ? 
                 ORDER BY c.comment_time DESC 
                 LIMIT {$limit} OFFSET {$offset}",
                [$userId]
            );
        } else {
            $tab = 'favorite';
            $total = $favCount;
            $list = $db->query(
                "SELECT f.*, v.vod_name, v.vod_pic, v.vod_remarks, v.vod_slug 
                 FROM " . DB_PREFIX . "user_favorite f 
                 LEFT JOIN " . DB_PREFIX . "vod v ON f.vod_id = v.vod_id 
                 WHERE f.user_id = ? 
                 ORDER BY f.fav_time DESC 
                 LIMIT {$limit} OFFSET {$offset}",
                [$userId]
            );
        }
        
        $totalPages = ceil($total / $limit);
        
        $this->assign('tab', $tab);
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('totalPages', $totalPages);
        $this->assign('total', $total);
        $this->assign('favCount', $favCount);
        $this->assign('historyCount', $historyCount);
        $this->assign('commentCount', $commentCount);
        
        $this->assign('title', '用户中心 - ' . SITE_NAME);
        $this->assign('noindex', true);
        $this->render('user/center');
    }

    /**
     * 忘记密码页
     */
    public function forgot(): void
    {
        if ($this->checkLogin()) {
            $this->redirect(xpk_url('user/center'));
            return;
        }
        
        $this->assign('csrfToken', $this->generateCsrfToken());
        $this->assign('title', '找回密码 - ' . SITE_NAME);
        $this->assign('noindex', true);
        $this->render('user/forgot');
    }

    /**
     * 处理找回密码（暂不支持，提示联系管理员）
     */
    public function doForgot(): void
    {
        $this->error('暂不支持在线找回密码，请联系管理员');
    }

    /**
     * VIP购买页面
     */
    public function vip(): void
    {
        require_once CORE_PATH . 'Vip.php';
        require_once CORE_PATH . 'Payment.php';
        require_once CORE_PATH . 'UsdtPayment.php';
        
        $vip = new XpkVip();
        $payment = XpkPayment::getInstance();
        $usdt = new XpkUsdtPayment();
        
        // 获取VIP套餐并格式化
        $rawPackages = $vip->getPackages();
        $packages = [];
        foreach ($rawPackages as $pkg) {
            $packages[] = [
                'package_id' => $pkg['package_id'],
                'package_name' => $pkg['package_name'],
                'price' => $pkg['package_price'],
                'price_usdt' => $pkg['package_price_usdt'],
                'original_price' => $pkg['package_original'],
                'days' => $pkg['package_days'],
                'daily_limit' => $pkg['package_daily_limit'],
                'bonus_points' => $pkg['package_bonus_points'],
                'description' => $pkg['package_desc'],
                'is_hot' => $pkg['package_hot'],
            ];
        }
        
        // 获取支付通道
        $channels = $payment->getEnabledChannels();
        $formattedChannels = [];
        foreach ($channels as $ch) {
            $formattedChannels[] = [
                'channel_id' => $ch['channel_id'],
                'channel_code' => $ch['channel_code'],
                'channel_name' => $ch['channel_name'],
                'support_methods' => explode(',', $ch['support_methods']),
            ];
        }
        
        // 用户VIP状态
        $vipStatus = null;
        if ($this->checkLogin()) {
            $user = $_SESSION['user'];
            // 刷新用户数据
            $user = $this->userModel->find($user['user_id']);
            $vipStatus = [
                'is_vip' => $vip->isVip($user),
                'vip_expire' => $user['user_vip_expire'] ? date('Y-m-d', $user['user_vip_expire']) : null,
                'remaining_views' => max(0, $vip->getDailyLimit($user) - $vip->getTodayViews($user['user_id'])),
                'points' => $user['user_points'] ?? 0,
            ];
        }
        
        $this->assign('packages', $packages);
        $this->assign('channels', $formattedChannels);
        $this->assign('usdtEnabled', $usdt->isEnabled());
        $this->assign('vipStatus', $vipStatus);
        $this->assign('title', 'VIP会员 - ' . SITE_NAME);
        $this->render('user/vip');
    }

    /**
     * USDT支付页面
     */
    public function payUsdt(): void
    {
        $this->requireLogin();
        
        $orderNo = $_GET['order_no'] ?? '';
        if (empty($orderNo)) {
            $this->redirect(xpk_url('user/vip'));
            return;
        }
        
        require_once CORE_PATH . 'Payment.php';
        require_once CORE_PATH . 'UsdtPayment.php';
        
        $payment = XpkPayment::getInstance();
        $usdt = new XpkUsdtPayment();
        
        $order = $payment->getOrderByNo($orderNo);
        if (!$order || $order['user_id'] != $_SESSION['user']['user_id']) {
            $this->redirect(xpk_url('user/vip'));
            return;
        }
        
        // 已支付则跳转结果页
        if ($order['order_status'] == 1) {
            $this->redirect(xpk_url('user/pay/result') . '?order_no=' . $orderNo);
            return;
        }
        
        $this->assign('order', $order);
        $this->assign('usdtAddress', $usdt->getAddress());
        $this->assign('title', 'USDT支付 - ' . SITE_NAME);
        $this->assign('noindex', true);
        $this->render('user/pay_usdt');
    }

    /**
     * 支付结果页面
     */
    public function payResult(): void
    {
        $orderNo = $_GET['order_no'] ?? '';
        if (empty($orderNo)) {
            $this->redirect('/');
            return;
        }
        
        require_once CORE_PATH . 'Payment.php';
        require_once CORE_PATH . 'Vip.php';
        
        $payment = XpkPayment::getInstance();
        $order = $payment->getOrderByNo($orderNo);
        
        if (!$order) {
            $this->redirect('/');
            return;
        }
        
        // 获取套餐信息并格式化
        $package = null;
        if ($order['order_type'] === 'vip' && $order['product_id']) {
            $vip = new XpkVip();
            $rawPackage = $vip->getPackage($order['product_id']);
            if ($rawPackage) {
                $package = [
                    'package_id' => $rawPackage['package_id'],
                    'package_name' => $rawPackage['package_name'],
                    'days' => $rawPackage['package_days'],
                    'bonus_points' => $rawPackage['package_bonus_points'],
                ];
            }
        }
        
        $this->assign('order', $order);
        $this->assign('package', $package);
        $this->assign('title', '支付结果 - ' . SITE_NAME);
        $this->assign('noindex', true);
        $this->render('user/pay_result');
    }
}
