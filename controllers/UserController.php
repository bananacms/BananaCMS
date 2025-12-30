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
            $this->redirect(xpk_url('user/center'));
            return;
        }
        
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
            $this->error('用户不存在');
            return;
        }

        if ($user['user_status'] != 1) {
            $this->error('账号已被禁用');
            return;
        }

        if (!$this->userModel->verifyPassword($password, $user['user_pwd'])) {
            $this->error('密码错误');
            return;
        }

        // 更新登录时间
        $this->userModel->updateLoginTime($user['user_id']);

        // 保存Session
        unset($user['user_pwd']);
        $_SESSION['user'] = $user;

        $this->success('登录成功', ['url' => xpk_url('user/center')]);
    }

    /**
     * 注册页
     */
    public function register(): void
    {
        if ($this->checkLogin()) {
            $this->redirect(xpk_url('user/center'));
            return;
        }
        
        // 检查是否开放注册
        $config = $this->data['siteConfig'] ?? [];
        if (($config['user_register'] ?? '1') !== '1') {
            $this->assign('title', '注册已关闭 - ' . SITE_NAME);
            $this->assign('error', '网站暂未开放注册');
            $this->render('user/register');
            return;
        }
        
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
        if (($config['user_register'] ?? '1') !== '1') {
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
            $this->success('注册成功', ['url' => xpk_url('user/login')]);
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
        $this->redirect(xpk_url());
    }

    /**
     * 用户中心
     */
    public function center(): void
    {
        $this->requireLogin();
        
        $this->assign('title', '用户中心 - ' . SITE_NAME);
        $this->assign('noindex', true);
        $this->render('user/center');
    }
}
