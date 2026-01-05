<?php
/**
 * 后台登录控制器
 * Powered by https://xpornkit.com
 */

class AdminAuthController
{
    /**
     * 获取当前后台入口文件名
     */
    private function getAdminEntry(): string
    {
        return basename($_SERVER['SCRIPT_NAME'], '.php');
    }
    
    /**
     * 生成后台URL
     */
    private function adminUrl(string $path = ''): string
    {
        $entry = $this->getAdminEntry();
        return '/' . $entry . ($path ? '?s=' . ltrim($path, '/') : '');
    }

    /**
     * 生成伪造IP地址（保护管理员真实IP）
     */
    private function generateFakeIP(int $adminId): string
    {
        $salt = defined('ADMIN_IP_SALT') ? ADMIN_IP_SALT : 'BananaCMS_Admin_IP_Salt_2024';
        $date = date('Y-m-d');
        $hash = md5($adminId . $date . 'login' . $salt);
        
        $ip1 = 192;
        $ip2 = 168;
        $ip3 = (hexdec(substr($hash, 0, 2)) % 254) + 1;
        $ip4 = (hexdec(substr($hash, 2, 2)) % 254) + 1;
        
        return "$ip1.$ip2.$ip3.$ip4";
    }

    /**
     * 登录页面
     */
    public function login(): void
    {
        if (isset($_SESSION['admin'])) {
            header('Location: ' . $this->adminUrl('dashboard'));
            exit;
        }

        // 生成 CSRF Token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $error = $_SESSION['login_error'] ?? '';
        $success = $_SESSION['login_success'] ?? '';
        unset($_SESSION['login_error'], $_SESSION['login_success']);
        $csrfToken = $_SESSION['csrf_token'];
        $adminEntry = $this->getAdminEntry();

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
            header('Location: ' . $this->adminUrl('login'));
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = '请输入用户名和密码';
            header('Location: ' . $this->adminUrl('login'));
            exit;
        }

        $adminModel = new XpkAdmin();
        $admin = $adminModel->findByUsername($username);

        if (!$admin) {
            $_SESSION['login_error'] = '用户名或密码错误';
            header('Location: ' . $this->adminUrl('login'));
            exit;
        }

        if ($admin['admin_status'] != 1) {
            $_SESSION['login_error'] = '账号已被禁用';
            header('Location: ' . $this->adminUrl('login'));
            exit;
        }

        if (!$adminModel->verifyPassword($password, $admin['admin_pwd'])) {
            $_SESSION['login_error'] = '用户名或密码错误';
            header('Location: ' . $this->adminUrl('login'));
            exit;
        }

        // 更新登录信息（使用伪造IP保护管理员隐私）
        $fakeIP = $this->generateFakeIP($admin['admin_id']);
        $adminModel->updateLogin($admin['admin_id'], $fakeIP);

        // 保存Session
        $_SESSION['admin'] = [
            'id' => $admin['admin_id'],
            'name' => $admin['admin_name'],
            'login_time' => time()
        ];

        header('Location: ' . $this->adminUrl('dashboard'));
        exit;
    }

    /**
     * 退出登录
     */
    public function logout(): void
    {
        unset($_SESSION['admin']);
        header('Location: ' . $this->adminUrl('login'));
        exit;
    }

    /**
     * 修改密码页面
     */
    public function password(): void
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: ' . $this->adminUrl('login'));
            exit;
        }

        // 生成 CSRF Token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $error = $_SESSION['password_error'] ?? '';
        $success = $_SESSION['password_success'] ?? '';
        unset($_SESSION['password_error'], $_SESSION['password_success']);
        
        $csrfToken = $_SESSION['csrf_token'];
        $admin = $_SESSION['admin'];
        $adminEntry = $this->getAdminEntry();

        require VIEW_PATH . 'admin/password.php';
    }

    /**
     * 处理修改密码
     */
    public function doPassword(): void
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: ' . $this->adminUrl('login'));
            exit;
        }

        // CSRF 验证
        $token = $_POST['_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $_SESSION['password_error'] = '安全验证失败，请刷新页面重试';
            header('Location: ' . $this->adminUrl('password'));
            exit;
        }

        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($oldPassword)) {
            $_SESSION['password_error'] = '请输入原密码';
            header('Location: ' . $this->adminUrl('password'));
            exit;
        }

        if (empty($newPassword) || strlen($newPassword) < 6) {
            $_SESSION['password_error'] = '新密码至少6个字符';
            header('Location: ' . $this->adminUrl('password'));
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['password_error'] = '两次输入的新密码不一致';
            header('Location: ' . $this->adminUrl('password'));
            exit;
        }

        $adminModel = new XpkAdmin();
        $admin = $adminModel->find($_SESSION['admin']['id']);

        if (!$admin) {
            $_SESSION['password_error'] = '管理员不存在';
            header('Location: ' . $this->adminUrl('password'));
            exit;
        }

        if (!$adminModel->verifyPassword($oldPassword, $admin['admin_pwd'])) {
            $_SESSION['password_error'] = '原密码错误';
            header('Location: ' . $this->adminUrl('password'));
            exit;
        }

        // 更新密码
        $adminModel->updatePassword($admin['admin_id'], $newPassword);

        // 清除登录状态，要求重新登录
        unset($_SESSION['admin']);
        
        $_SESSION['login_success'] = '密码修改成功，请重新登录';
        header('Location: ' . $this->adminUrl('login'));
        exit;
    }
}
