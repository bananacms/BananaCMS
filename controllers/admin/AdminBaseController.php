<?php
/**
 * 后台控制器基类
 * Powered by https://xpornkit.com
 */

class AdminBaseController
{
    protected array $data = [];
    protected ?array $admin = null;

    public function __construct()
    {
        $this->checkAuth();
        $this->admin = $_SESSION['admin'] ?? null;
        $this->data['admin'] = $this->admin;
        $this->data['siteName'] = SITE_NAME;
    }

    /**
     * 检查登录
     */
    protected function checkAuth(): void
    {
        if (!isset($_SESSION['admin'])) {
            $this->redirect('/admin.php/login');
        }
    }

    /**
     * 渲染视图
     */
    protected function render(string $view, string $title = ''): void
    {
        $this->data['pageTitle'] = $title;
        extract($this->data);
        
        ob_start();
        require VIEW_PATH . 'admin/' . $view . '.php';
        $content = ob_get_clean();
        
        require VIEW_PATH . 'admin/layout.php';
    }

    /**
     * 分配变量
     */
    protected function assign(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * JSON响应
     */
    protected function json(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 成功响应
     */
    protected function success(string $msg = '操作成功', array $data = []): void
    {
        $this->json(['code' => 0, 'msg' => $msg, 'data' => $data]);
    }

    /**
     * 错误响应
     */
    protected function error(string $msg = '操作失败'): void
    {
        $this->json(['code' => 1, 'msg' => $msg]);
    }

    /**
     * 重定向
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * 获取POST参数
     */
    protected function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * 获取GET参数
     */
    protected function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * CSRF Token生成
     */
    protected function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * CSRF验证
     */
    protected function verifyCsrf(): bool
    {
        $token = $this->post('_token') ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    /**
     * 设置Flash消息
     */
    protected function flash(string $type, string $msg): void
    {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    /**
     * 获取Flash消息
     */
    protected function getFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    /**
     * 记录操作日志
     */
    protected function log(string $action, string $module, string $content = ''): void
    {
        XpkAdminLog::log($action, $module, $content);
    }
}
