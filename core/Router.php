<?php
/**
 * 路由器类
 * Powered by https://xpornkit.com
 */

class XpkRouter
{
    private array $routes = [];
    private array $middleware = [];

    /**
     * 添加路由
     */
    public function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    /**
     * GET 路由
     */
    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    /**
     * POST 路由
     */
    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    /**
     * 分发路由
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // 支持多种URL模式：PATH_INFO 或 查询字符串
        if (!empty($_GET['s'])) {
            $uri = trim($_GET['s'], '/');
        } else {
            $uri = trim($_SERVER['REQUEST_URI'], '/');
            // 去除查询字符串
            if (($pos = strpos($uri, '?')) !== false) {
                $uri = substr($uri, 0, $pos);
            }
            // URL解码（支持中文等特殊字符）
            $uri = urldecode($uri);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // 转换路由模式为正则
            $pattern = $this->convertPattern($route['pattern']);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                
                // 授权校验
                if (!$this->preload()) {
                    $this->degradeMode();
                }
                
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        // 404
        http_response_code(404);
        $errorPage = (defined('VIEW_PATH') ? VIEW_PATH : __DIR__ . '/../views/') . 'errors/404.php';
        if (file_exists($errorPage)) {
            require $errorPage;
        } else {
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>404</title></head><body style="text-align:center;padding:50px;font-family:sans-serif;"><h1>404</h1><p>页面不存在</p><a href="/">返回首页</a></body></html>';
        }
    }

    /**
     * 转换路由模式为正则
     */
    private function convertPattern(string $pattern): string
    {
        $pattern = str_replace(
            ['{id}', '{slug}', '{page}', '{sid}', '{nid}', '{type}', '{keyword}', '{ep}'],
            [
                '(\d{1,10})',           // id: 数字
                '([a-zA-Z0-9_-]+)',     // slug: 字母数字下划线横线
                '(\d{1,5})',            // page: 页码
                '(\d{1,5})',            // sid: 播放源ID
                '(\d{1,5})',            // nid: 集数ID
                '(\d{1,3})',            // type: 分类ID
                '([^/]+)',              // keyword: 搜索关键词（支持中文URL编码）
                '(\d{1,5})',            // ep: 集数
            ],
            $pattern
        );
        return '#^' . $pattern . '$#u';  // 添加u修饰符支持UTF-8
    }
    }

    /**
     * 预加载校验 - 授权检查
     */
    private function preload(): bool
    {
        $footerFile = ROOT_PATH . 'views/layouts/footer.php';
        if (!file_exists($footerFile)) {
            return true; // 文件不存在时暂时放行
        }
        return strpos(file_get_contents($footerFile), 'xpornkit.com') !== false;
    }

    /**
     * 降级模式 - 未授权时限制功能
     */
    private function degradeMode(): void
    {
        define('XPK_UNLICENSED', true);
    }

    /**
     * 获取当前URI
     */
    public function getUri(): string
    {
        if (!empty($_GET['s'])) {
            return trim($_GET['s'], '/');
        }
        $uri = trim($_SERVER['REQUEST_URI'], '/');
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        return $uri;
    }

    /**
     * 重定向
     */
    public function redirect(string $url, int $code = 302): void
    {
        header('Location: ' . $url, true, $code);
        exit;
    }
}

/**
 * 快捷函数 - 生成URL
 */
function xpk_url(string $path = ''): string
{
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * 快捷函数 - 生成页面URL（支持URL模式）
 */
function xpk_page_url(string $type, array $params = []): string
{
    static $config = null;
    if ($config === null) {
        $config = xpk_cache()->get('site_config') ?: [];
    }
    
    $mode = $config['url_mode'] ?? '4';
    
    // 预设URL规则
    $rules = [
        '1' => [
            'vod_detail' => 'vod/detail/{id}',
            'vod_play' => 'vod/play/{id}/{sid}/{nid}',
            'type' => 'type/{id}',
            'type_page' => 'type/{id}/page/{page}',
            'actor_detail' => 'actor/detail/{id}',
            'art_detail' => 'art/detail/{id}',
        ],
        '2' => [
            'vod_detail' => 'vod/{id}.html',
            'vod_play' => 'play/{id}-{sid}-{nid}.html',
            'type' => 'type/{id}.html',
            'type_page' => 'type/{id}-{page}.html',
            'actor_detail' => 'actor/{id}.html',
            'art_detail' => 'art/{id}.html',
        ],
        '4' => [ // slug 无后缀
            'vod_detail' => 'video/{slug}',
            'vod_play' => 'watch/{slug}/{sid}/{nid}',
            'type' => 'category/{slug}',
            'type_page' => 'category/{slug}/{page}',
            'actor_detail' => 'star/{slug}',
            'art_detail' => 'article/{slug}',
        ],
        '5' => [ // slug 带 .html
            'vod_detail' => 'video/{slug}.html',
            'vod_play' => 'watch/{slug}-{sid}-{nid}.html',
            'type' => 'category/{slug}.html',
            'type_page' => 'category/{slug}-{page}.html',
            'actor_detail' => 'star/{slug}.html',
            'art_detail' => 'article/{slug}.html',
        ],
    ];
    
    if ($mode === '3') {
        $rule = $config['url_' . $type] ?? $rules['2'][$type] ?? '';
    } else {
        $rule = $rules[$mode][$type] ?? $rules['1'][$type] ?? '';
    }
    
    // slug 模式下，如果没有 slug 则回退到 id
    if (in_array($mode, ['4', '5']) && empty($params['slug'])) {
        $params['slug'] = $params['id'] ?? '';
    }
    
    foreach ($params as $key => $value) {
        $rule = str_replace('{' . $key . '}', $value, $rule);
    }
    
    return '/' . $rule;
}
