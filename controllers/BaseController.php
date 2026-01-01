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
        $siteStatus = $config['site_status'] ?? '1';
        
        // 站点关闭时显示关闭提示
        if ($siteStatus == '0' || $siteStatus === 0) {
            $closeTip = $config['site_close_tip'] ?? '网站维护中，请稍后访问';
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
        if (strpos($uri, '/admin') !== false || strpos($uri, '/api') !== false) {
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
        $navLimit = (int)($this->data['siteConfig']['nav_type_limit'] ?? 10);
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
        $config = $cache->remember('site_config', 600, function() {
            $db = XpkDatabase::getInstance();
            $rows = $db->query("SELECT config_name, config_value FROM " . DB_PREFIX . "config");
            $map = [];
            foreach ($rows as $row) {
                $map[$row['config_name']] = $row['config_value'];
            }
            return $map;
        });
        
        $this->data['siteConfig'] = $config;
        $this->data['siteName'] = $config['site_name'] ?? SITE_NAME;
        $this->data['siteUrl'] = $config['site_url'] ?? SITE_URL;
        $this->data['siteKeywords'] = $config['site_keywords'] ?? SITE_KEYWORDS;
        $this->data['siteDescription'] = $config['site_description'] ?? SITE_DESCRIPTION;
        $this->data['urlMode'] = $config['url_mode'] ?? '4';
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
        return mb_substr($result, 0, 60);
    }

    /**
     * 生成SEO关键词
     */
    protected function seoKeywords(string $page, array $vars = []): string
    {
        $tpl = $this->data['siteConfig']['seo_keywords_' . $page] ?? '';
        if (empty($tpl)) {
            return $vars['name'] ?? $this->data['siteKeywords'];
        }
        return $this->parseSeoTpl($tpl, $vars);
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
        // 清理换行和多余空格
        $result = preg_replace('/\s+/', ' ', trim($result));
        return mb_substr($result, 0, 160);
    }

    /**
     * 解析SEO模板变量
     */
    protected function parseSeoTpl(string $tpl, array $vars): string
    {
        $vars['sitename'] = $this->data['siteName'];
        $vars['year'] = date('Y');
        foreach ($vars as $key => $value) {
            $tpl = str_replace('{' . $key . '}', $value, $tpl);
        }
        return $tpl;
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
    protected function success(string $msg = 'success', array $data = []): void
    {
        $this->json(['code' => 0, 'msg' => $msg, 'data' => $data]);
    }

    /**
     * 错误响应
     */
    protected function error(string $msg = 'error', int $code = 1): void
    {
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
            $this->redirect(xpk_url('user/login'));
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
            $this->error('安全验证失败，请刷新页面重试');
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
    protected function error404(string $msg = '页面不存在'): void
    {
        http_response_code(404);
        $this->assign('errorMsg', $msg);
        $this->render('error/404');
    }
}
