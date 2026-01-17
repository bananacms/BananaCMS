<?php
/**
 * 后台控制器基类
 * Powered by https://xpornkit.com
 */

class AdminBaseController
{
    protected array $data = [];
    protected ?array $admin = null;
    protected string $adminEntry;
    protected XpkDatabase $db;

    public function __construct()
    {
        $this->db = XpkDatabase::getInstance();
        $this->checkAuth();
        $this->admin = $_SESSION['admin'] ?? null;
        $this->adminEntry = $this->getAdminEntry();
        $this->data['admin'] = $this->admin;
        $this->data['siteName'] = SITE_NAME;
        $this->data['adminEntry'] = $this->adminEntry;
        $this->data['csrfToken'] = $this->csrfToken();
    }

    /**
     * 检查登录
     */
    protected function checkAuth(): void
    {
        if (!isset($_SESSION['admin'])) {
            $this->redirect('/' . $this->getAdminEntry() . '?s=login');
        }
    }

    /**
     * 获取当前后台入口文件名
     */
    protected function getAdminEntry(): string
    {
        // Priority: config constant > script name
        if (defined('ADMIN_ENTRY') && ADMIN_ENTRY !== '') {
            return ADMIN_ENTRY;
        }
        return basename($_SERVER['SCRIPT_NAME'], '.php');
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
     * 获取请求参数（GET或POST）
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $_REQUEST[$key] ?? $default;
    }

    /**
     * CSRF Token生成
     * 使用统一的 XpkSecurity 类
     */
    protected function csrfToken(): string
    {
        return XpkSecurity::generateToken();
    }

    /**
     * CSRF验证
     * 兼容旧的 _token 参数名和新的 csrf_token 参数名
     */
    protected function verifyCsrf(): bool
    {
        $token = $this->post('csrf_token') ?? $this->post('_token') ?? '';
        return XpkSecurity::validateToken($token);
    }

    /**
     * 要求CSRF验证
     */
    protected function requireCsrf(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('CSRF验证失败，请刷新页面后重试');
        }
    }

    /**
     * 获取CSRF Token HTML字段
     */
    protected function csrfField(): string
    {
        return XpkSecurity::getTokenField();
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

    /**
     * 验证输入数据
     * @param array $data 数据数组
     * @param array $rules 验证规则
     * @return array 错误信息数组（为空表示验证通过）
     */
    protected function validate(array $data, array $rules): array
    {
        require_once CORE_PATH . 'Validator.php';
        return XpkValidator::validate($data, $rules);
    }

    /**
     * 验证邮箱
     * @param string $email 邮箱地址
     * @return bool
     */
    protected function validateEmail(string $email): bool
    {
        require_once CORE_PATH . 'Validator.php';
        return XpkValidator::email($email);
    }

    /**
     * 验证URL
     * @param string $url URL地址
     * @return bool
     */
    protected function validateUrl(string $url): bool
    {
        require_once CORE_PATH . 'Validator.php';
        return XpkValidator::url($url);
    }

    /**
     * 验证整数
     * @param mixed $value 值
     * @param int|null $min 最小值
     * @param int|null $max 最大值
     * @return bool
     */
    protected function validateInt($value, ?int $min = null, ?int $max = null): bool
    {
        require_once CORE_PATH . 'Validator.php';
        return XpkValidator::int($value, $min, $max);
    }

    /**
     * 验证浮点数
     * @param mixed $value 值
     * @param float|null $min 最小值
     * @param float|null $max 最大值
     * @return bool
     */
    protected function validateFloat($value, ?float $min = null, ?float $max = null): bool
    {
        require_once CORE_PATH . 'Validator.php';
        return XpkValidator::float($value, $min, $max);
    }

    /**
     * 验证Slug格式
     * @param string $slug Slug字符串
     * @return bool
     */
    protected function validateSlug(string $slug): bool
    {
        require_once CORE_PATH . 'Validator.php';
        return XpkValidator::slug($slug);
    }

    /**
     * 验证用户名
     * @param string $username 用户名
     * @return bool
     */
    protected function validateUsername(string $username): bool
    {
        require_once CORE_PATH . 'Validator.php';
        return XpkValidator::username($username);
    }

    /**
     * 验证手机号码
     * @param string $phone 手机号码
     * @return bool
     */
    protected function validatePhone(string $phone): bool
    {
        require_once CORE_PATH . 'Validator.php';
        return XpkValidator::phone($phone);
    }

    /**
     * 验证字符串长度
     * @param string $str 字符串
     * @param int|null $min 最小长度
     * @param int|null $max 最大长度
     * @return bool
     */
    protected function validateLength(string $str, ?int $min = null, ?int $max = null): bool
    {
        require_once CORE_PATH . 'Validator.php';
        return XpkValidator::length($str, $min, $max);
    }

    /**
     * 验证值是否为空
     * @param mixed $value 值
     * @return bool
     */
    protected function validateRequired($value): bool
    {
        require_once CORE_PATH . 'Validator.php';
        return XpkValidator::required($value);
    }

    /**
     * 验证值是否在指定范围内
     * @param mixed $value 值
     * @param array $allowed 允许的值列表
     * @return bool
     */
    protected function validateIn($value, array $allowed): bool
    {
        require_once CORE_PATH . 'Validator.php';
        return XpkValidator::in($value, $allowed);
    }

    /**
     * 验证正则表达式匹配
     * @param string $str 字符串
     * @param string $pattern 正则表达式
     * @return bool
     */
    protected function validateRegex(string $str, string $pattern): bool
    {
        require_once CORE_PATH . 'Validator.php';
        return XpkValidator::regex($str, $pattern);
    }
}
