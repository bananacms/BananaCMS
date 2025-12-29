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
    }

    /**
     * 初始化公共数据
     */
    protected function initCommon(): void
    {
        // 导航分类
        $typeModel = new XpkType();
        $this->data['navTypes'] = $typeModel->getNav();
        
        // 加载站点配置
        $this->loadSiteConfig();
        
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
}
