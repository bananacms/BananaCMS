<?php
/**
 * 控制器基类
 * Powered by https://xpornkit.com
 */

class BaseController
{
    protected XpkTemplate $view;
    protected array $data = [];

    public function __construct()
    {
        $this->view = new XpkTemplate();
        $this->initCommon();
        $this->checkSiteStatus();
        $this->logPageView();
    }

    /**
     * 检查站点状态
     */
    protected function checkSiteStatus(): void
    {
        $config = $this->data['siteConfig'] ?? [];
        $siteStatus = $config[XpkConfigKeys::SITE_STATUS] ?? '1';
        
        // 站点关闭时显示关闭提示
        if ($siteStatus == '0' || $siteStatus === 0) {
            $closeTip = $config[XpkConfigKeys::SITE_CLOSE_TIP] ?? '网站维护中，请稍后访问';
            http_response_code(503);
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>网站维护中</title>';
            echo '<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f5f5f5}';
            echo '.box{text-align:center;padding:40px;background:#fff;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1)}';
            echo 'h1{color:#333;font-size:24px;margin:0 0 20px}p{color:#666;margin:0}</style></head>';
            echo '<body><div class="box"><h1>🔧 网站维护中</h1><p>' . htmlspecialchars($closeTip) . '</p></div></body></html>';
            exit;
        }
    }

    /**
     * 记录页面访问统计
     */
    protected function logPageView(): void
    {
        // 只记录前端页面访问，排除管理后台和API
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $adminEntry = defined('ADMIN_ENTRY') ? ADMIN_ENTRY : 'admin';
        $adminPath = '/' . $adminEntry;
        if (strpos($uri, $adminPath) !== false || strpos($uri, XPK_API_PATH) !== false) {
            return;
        }
        
        try {
            require_once MODEL_PATH . 'Stats.php';
            $stats = new XpkStats();
            $stats->log('page', 0);
        } catch (Exception $e) {
            // 静默失败，不影响页面正常访问
        }
    }

    /**
     * 初始化公共数据
     */
    protected function initCommon(): void
    {
        // 先加载站点配置
        $this->loadSiteConfig();
        
        // 导航分类（从配置读取显示数量，0表示不限制）
        $typeModel = new XpkType();
        $navLimit = (int)($this->data['siteConfig'][XpkConfigKeys::NAV_TYPE_LIMIT] ?? XPK_PAGE_SIZE_SMALL);
        $this->data['navTypes'] = $typeModel->getNav($navLimit);
        
        // 当前用户
        $this->data['user'] = $_SESSION['user'] ?? null;
    }

    /**
     * 加载站点配置
     */
    protected function loadSiteConfig(): void
    {
        $cache = xpk_cache();
        $config = $cache->remember(XPK_CACHE_CONFIG, XPK_DEFAULT_CACHE_TIME, function() {
            $db = XpkDatabase::getInstance();
            $rows = $db->query("SELECT config_name, config_value FROM " . DB_PREFIX . "config");
            $map = [];
            foreach ($rows as $row) {
                $map[$row['config_name']] = $row['config_value'];
            }
            return $map;
        });
        
        $this->data['siteConfig'] = $config;
        $this->data['siteName'] = $config[XpkConfigKeys::SITE_NAME] ?? SITE_NAME;
        $this->data['siteUrl'] = $config[XpkConfigKeys::SITE_URL] ?? SITE_URL;
        $this->data['siteKeywords'] = $config[XpkConfigKeys::SITE_KEYWORDS] ?? SITE_KEYWORDS;
        $this->data['siteDescription'] = $config[XpkConfigKeys::SITE_DESCRIPTION] ?? SITE_DESCRIPTION;
        $this->data['urlMode'] = $config[XpkConfigKeys::URL_MODE] ?? '4';
    }

    /**
     * 生成SEO标题（最大60字符）
     */
    protected function seoTitle(string $title, array $vars = []): string
    {
        $tpl = $this->data['siteConfig']['seo_title_' . $title] ?? '';
        if (empty($tpl)) {
            $result = ($vars['name'] ?? $title) . ' - ' . $this->data['siteName'];
        } else {
            $result = $this->parseSeoTpl($tpl, $vars);
        }
        
        // 清理和验证标题
        $result = trim($result);
        if (empty($result)) {
            $result = $this->data['siteName'];
        }
        
        // 移除HTML标签和特殊字符
        $result = strip_tags($result);
        $result = preg_replace('/\s+/', ' ', $result);
        
        return mb_substr($result, 0, XPK_MAX_TITLE_LENGTH);
    }

    /**
     * 生成SEO关键词
     */
    protected function seoKeywords(string $page, array $vars = []): string
    {
        $tpl = $this->data['siteConfig']['seo_keywords_' . $page] ?? '';
        if (empty($tpl)) {
            $result = $vars['name'] ?? $this->data['siteKeywords'];
        } else {
            $result = $this->parseSeoTpl($tpl, $vars);
        }
        
        // 清理关键词
        $result = trim($result);
        if (empty($result)) {
            $result = $this->data['siteKeywords'];
        }
        
        // 移除HTML标签，保留逗号分隔
        $result = strip_tags($result);
        $result = preg_replace('/\s+/', ' ', $result);
        
        // 限制关键词长度（建议不超过200字符）
        return mb_substr($result, 0, 200);
    }

    /**
     * 生成SEO描述（最大160字符）
     */
    protected function seoDescription(string $page, array $vars = []): string
    {
        $tpl = $this->data['siteConfig']['seo_description_' . $page] ?? '';
        if (empty($tpl)) {
            $result = $vars['description'] ?? $this->data['siteDescription'];
        } else {
            $result = $this->parseSeoTpl($tpl, $vars);
        }
        
        // 清理和验证描述
        $result = trim($result);
        if (empty($result)) {
            $result = $this->data['siteDescription'];
        }
        
        // 移除HTML标签和清理换行、多余空格
        $result = strip_tags($result);
        $result = preg_replace('/\s+/', ' ', $result);
        
        return mb_substr($result, 0, XPK_MAX_DESCRIPTION_LENGTH);
    }

    /**
     * 解析SEO模板变量
     */
    protected function parseSeoTpl(string $tpl, array $vars): string
    {
        // 添加默认变量
        $vars['sitename'] = $this->data['siteName'];
        $vars['year'] = date('Y');
        $vars['month'] = date('m');
        $vars['day'] = date('d');
        
        // 清理变量值
        foreach ($vars as $key => $value) {
            if (is_string($value)) {
                // 移除HTML标签和多余空格
                $value = strip_tags($value);
                $value = preg_replace('/\s+/', ' ', trim($value));
                $vars[$key] = $value;
            }
        }
        
        // 替换模板变量
        foreach ($vars as $key => $value) {
            $tpl = str_replace('{' . $key . '}', $value, $tpl);
        }
        
        // 清理未替换的变量标签
        $tpl = preg_replace('/\{[^}]+\}/', '', $tpl);
        
        // 清理多余空格和标点
        $tpl = preg_replace('/\s+/', ' ', $tpl);
        $tpl = preg_replace('/\s*[-,，]\s*$/', '', $tpl); // 移除末尾的分隔符
        
        return trim($tpl);
    }

    /**
     * 生成URL
     */
    protected function buildUrl(string $type, array $params = []): string
    {
        $config = $this->data['siteConfig'] ?? [];
        $mode = $config['url_mode'] ?? '4';
        
        // 预设URL规则
        $rules = [
            '1' => [ // 模式1：原始
                'vod_detail' => 'vod/detail/{id}',
                'vod_play' => 'vod/play/{id}/{sid}/{nid}',
                'type' => 'type/{id}',
                'type_page' => 'type/{id}/page/{page}',
                'actor_detail' => 'actor/detail/{id}',
                'art_detail' => 'art/detail/{id}',
            ],
            '2' => [ // 模式2：.html后缀
                'vod_detail' => 'vod/{id}.html',
                'vod_play' => 'play/{id}-{sid}-{nid}.html',
                'type' => 'type/{id}.html',
                'type_page' => 'type/{id}-{page}.html',
                'actor_detail' => 'actor/{id}.html',
                'art_detail' => 'art/{id}.html',
            ],
        ];
        
        // 模式3使用自定义规则
        if ($mode === '3') {
            $rule = $config['url_' . $type] ?? $rules['2'][$type] ?? '';
        } else {
            $rule = $rules[$mode][$type] ?? $rules['1'][$type] ?? '';
        }
        
        // 替换变量
        foreach ($params as $key => $value) {
            $rule = str_replace('{' . $key . '}', $value, $rule);
        }
        
        return '/' . $rule;
    }

    /**
     * 渲染视图
     */
    protected function render(string $template): void
    {
        $this->view->assignArray($this->data);
        $this->view->render($template);
    }

    /**
     * 渲染视图（display别名）
     */
    protected function display(string $template, array $data = []): void
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
        $this->render($template);
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
    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 成功响应
     */
    protected function success(string $msg = '', array $data = []): void
    {
        if (empty($msg)) {
            $msg = XpkSuccessMessages::SAVE_SUCCESS;
        }
        $this->json(['code' => XPK_API_SUCCESS, 'msg' => $msg, 'data' => $data]);
    }

    /**
     * 错误响应
     */
    protected function error(string $msg = '', int $code = XPK_API_ERROR): void
    {
        if (empty($msg)) {
            $msg = XpkErrorMessages::SERVER_ERROR;
        }
        $this->json(['code' => $code, 'msg' => $msg]);
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
     * 获取GET参数
     */
    protected function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * 获取POST参数
     */
    protected function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * 获取请求参数
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $_REQUEST[$key] ?? $default;
    }

    /**
     * 检查登录
     */
    protected function checkLogin(): bool
    {
        return isset($_SESSION['user']);
    }

    /**
     * 需要登录
     */
    protected function requireLogin(): void
    {
        if (!$this->checkLogin()) {
            $this->redirect(xpk_url(XPK_USER_LOGIN_PATH));
        }
    }

    /**
     * 生成CSRF Token
     */
    protected function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * 验证CSRF Token
     */
    protected function verifyCsrfToken(): bool
    {
        $token = $this->post('_token') ?? $this->get('_token') ?? '';
        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    /**
     * 要求CSRF验证
     */
    protected function requireCsrf(): void
    {
        if (!$this->verifyCsrfToken()) {
            $this->error(XpkErrorMessages::INVALID_REQUEST);
        }
    }

    /**
     * 获取CSRF Token（别名）
     */
    protected function csrfToken(): string
    {
        return $this->generateCsrfToken();
    }

    /**
     * API JSON响应
     */
    protected function apiJson(int $code, string $msg, array $data = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['code' => $code, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 404错误页面
     */
    protected function error404(string $msg = ''): void
    {
        if (empty($msg)) {
            $msg = XpkErrorMessages::NOT_FOUND;
        }
        http_response_code(404);
        $this->assign('errorMsg', $msg);
        $this->render('error/404');
    }

    /**
     * 速率限制检查
     * @param string $key 限制键名（如用户ID、IP等）
     * @param int $limit 限制次数
     * @param int $window 时间窗口（秒）
     * @return bool 是否允许继续
     */
    protected function rateLimit(string $key, int $limit = 10, int $window = 60): bool
    {
        $cache = xpk_cache();
        $cacheKey = 'rate_limit_' . $key;
        
        // 获取当前计数
        $count = $cache->get($cacheKey) ?? 0;
        
        if ($count >= $limit) {
            return false;
        }
        
        // 增加计数
        $cache->set($cacheKey, $count + 1, $window);
        return true;
    }

    /**
     * 用户速率限制（基于用户ID或IP）
     * @param string $action 操作类型
     * @param int $limit 限制次数
     * @param int $window 时间窗口（秒）
     * @return bool 是否允许继续
     */
    protected function userRateLimit(string $action, int $limit = 10, int $window = 60): bool
    {
        $userId = $_SESSION['user']['user_id'] ?? 0;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // 优先使用用户ID，未登录用户使用IP
        $key = $userId > 0 ? "user_{$userId}_{$action}" : "ip_{$ip}_{$action}";
        
        return $this->rateLimit($key, $limit, $window);
    }

    /**
     * 要求速率限制检查
     * @param string $action 操作类型
     * @param int $limit 限制次数
     * @param int $window 时间窗口（秒）
     */
    protected function requireRateLimit(string $action, int $limit = 10, int $window = 60): void
    {
        if (!$this->userRateLimit($action, $limit, $window)) {
            $this->error('操作过于频繁，请稍后再试', XPK_API_ERROR);
        }
    }

    /**
     * 记录用户操作日志
     * @param string $action 操作类型
     * @param array $data 操作数据
     * @param string $level 日志级别
     */
    protected function logUserAction(string $action, array $data = [], string $level = XpkLogLevel::INFO): void
    {
        $userId = $_SESSION['user']['user_id'] ?? 0;
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        $logData = [
            'timestamp' => date(XPK_DATETIME_FORMAT),
            'level' => $level,
            'user_id' => $userId,
            'ip' => $ip,
            'action' => $action,
            'uri' => $uri,
            'user_agent' => mb_substr($userAgent, 0, 200),
            'data' => $data
        ];
        
        // 记录到缓存（用于实时监控）
        $cache = xpk_cache();
        $cacheKey = 'user_actions_' . date('Y-m-d-H');
        $actions = $cache->get($cacheKey) ?? [];
        $actions[] = $logData;
        
        // 只保留最近100条记录
        if (count($actions) > 100) {
            $actions = array_slice($actions, -100);
        }
        
        $cache->set($cacheKey, $actions, 3600); // 1小时过期
        
        // 异步写入数据库（如果有日志表）
        $this->asyncLogToDatabase($logData);
    }

    /**
     * 异步写入数据库日志
     * @param array $logData 日志数据
     */
    private function asyncLogToDatabase(array $logData): void
    {
        try {
            // 检查是否存在日志表
            $db = XpkDatabase::getInstance();
            $tableExists = $db->queryOne("SHOW TABLES LIKE '" . DB_PREFIX . "user_logs'");
            
            if ($tableExists) {
                $db->insert(DB_PREFIX . 'user_logs', [
                    'log_time' => $logData['timestamp'],
                    'log_level' => $logData['level'],
                    'user_id' => $logData['user_id'],
                    'user_ip' => $logData['ip'],
                    'log_action' => $logData['action'],
                    'log_uri' => $logData['uri'],
                    'log_data' => json_encode($logData['data'], JSON_UNESCAPED_UNICODE),
                    'user_agent' => $logData['user_agent']
                ]);
            }
        } catch (Exception $e) {
            // 静默失败，不影响正常业务
        }
    }

    /**
     * 记录安全事件
     * @param string $event 事件类型
     * @param array $data 事件数据
     */
    protected function logSecurityEvent(string $event, array $data = []): void
    {
        $this->logUserAction($event, $data, XpkLogLevel::WARNING);
        
        // 安全事件额外记录到专门的缓存键
        $cache = xpk_cache();
        $cacheKey = 'security_events_' . date('Y-m-d');
        $events = $cache->get($cacheKey) ?? [];
        
        $events[] = [
            'timestamp' => time(),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_id' => $_SESSION['user']['user_id'] ?? 0,
            'data' => $data
        ];
        
        // 只保留最近50条安全事件
        if (count($events) > 50) {
            $events = array_slice($events, -50);
        }
        
        $cache->set($cacheKey, $events, 86400); // 24小时过期
    }
}
