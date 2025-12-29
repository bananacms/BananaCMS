<?php
/**
 * 后台登录控制器
 * Powered by https://xpornkit.com
 */

class AdminAuthController
{
    /**
     * 登录页面
     */
    public function login(): void
    {
        if (isset($_SESSION['admin'])) {
            header('Location: /admin.php/dashboard');
            exit;
        }

        // 生成 CSRF Token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $error = $_SESSION['login_error'] ?? '';
        unset($_SESSION['login_error']);
        $csrfToken = $_SESSION['csrf_token'];

        require VIEW_PATH . 'admin/login.php';
    }

    /**
     * 处理登录
     */
    public function doLogin(): void
    {
        // CSRF 验证
        $token = $_POST['_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $_SESSION['login_error'] = '安全验证失败，请刷新页面重试';
            header('Location: /admin.php/login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = '请输入用户名和密码';
            header('Location: /admin.php/login');
            exit;
        }

        $adminModel = new XpkAdmin();
        $admin = $adminModel->findByUsername($username);

        if (!$admin) {
            $_SESSION['login_error'] = '用户名或密码错误';
            header('Location: /admin.php/login');
            exit;
        }

        if ($admin['admin_status'] != 1) {
            $_SESSION['login_error'] = '账号已被禁用';
            header('Location: /admin.php/login');
            exit;
        }

        if (!$adminModel->verifyPassword($password, $admin['admin_pwd'])) {
            $_SESSION['login_error'] = '用户名或密码错误';
            header('Location: /admin.php/login');
            exit;
        }

        // 更新登录信息
        $adminModel->updateLogin($admin['admin_id'], $_SERVER['REMOTE_ADDR'] ?? '');

        // 保存Session
        $_SESSION['admin'] = [
            'id' => $admin['admin_id'],
            'name' => $admin['admin_name'],
            'login_time' => time()
        ];

        header('Location: /admin.php/dashboard');
        exit;
    }

    /**
     * 退出登录
     */
    public function logout(): void
    {
        unset($_SESSION['admin']);
        header('Location: /admin.php/login');
        exit;
    }
}
