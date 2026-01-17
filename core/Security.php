<?php
/**
 * 安全响应头管理类
 * Powered by https://xpornkit.com
 */

class XpkSecurity
{
    private static ?array $config = null;

    /**
     * 设置安全响应头
     */
    public static function setSecurityHeaders(string $context = 'frontend'): void
    {
        $config = self::getSecurityConfig();
        
        // X-Frame-Options
        $frameOptions = $config['security_frame_options'] ?? 'SAMEORIGIN';
        if ($context === 'admin') {
            $frameOptions = 'DENY'; // 后台更严格
        }
        header("X-Frame-Options: $frameOptions");
        
        // X-Content-Type-Options
        header('X-Content-Type-Options: nosniff');
        
        // X-XSS-Protection
        $xssProtection = $config['security_xss_protection'] ?? '1';
        if ($xssProtection === '1') {
            header('X-XSS-Protection: 1; mode=block');
        }
        
        // Referrer-Policy
        $referrerPolicy = $config['security_referrer_policy'] ?? 'strict-origin-when-cross-origin';
        header("Referrer-Policy: $referrerPolicy");
        
        // Strict-Transport-Security (HSTS) - 仅在HTTPS下启用
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $hstsMaxAge = $config['security_hsts_max_age'] ?? '31536000'; // 1年
            $hstsIncludeSubdomains = ($config['security_hsts_include_subdomains'] ?? '1') === '1';
            $hstsPreload = ($config['security_hsts_preload'] ?? '0') === '1';
            
            $hsts = "max-age=$hstsMaxAge";
            if ($hstsIncludeSubdomains) {
                $hsts .= '; includeSubDomains';
            }
            if ($hstsPreload) {
                $hsts .= '; preload';
            }
            header("Strict-Transport-Security: $hsts");
        }
        
        // Permissions-Policy (替代Feature-Policy)
        $permissionsPolicy = $config['security_permissions_policy'] ?? 'camera=(), microphone=(), geolocation=(), payment=()';
        if (!empty($permissionsPolicy)) {
            header("Permissions-Policy: $permissionsPolicy");
        }
        
        // X-Permitted-Cross-Domain-Policies
        header('X-Permitted-Cross-Domain-Policies: none');
        
        // Cross-Origin-Embedder-Policy
        if (($config['security_coep_enabled'] ?? '0') === '1') {
            header('Cross-Origin-Embedder-Policy: require-corp');
        }
        
        // Cross-Origin-Opener-Policy
        if (($config['security_coop_enabled'] ?? '0') === '1') {
            $coopPolicy = $config['security_coop_policy'] ?? 'same-origin';
            header("Cross-Origin-Opener-Policy: $coopPolicy");
        }
        
        // Cross-Origin-Resource-Policy
        if (($config['security_corp_enabled'] ?? '0') === '1') {
            $corpPolicy = $config['security_corp_policy'] ?? 'same-origin';
            header("Cross-Origin-Resource-Policy: $corpPolicy");
        }
        
        // CSP
        if (($config['security_csp_enabled'] ?? '1') === '1') {
            $csp = self::buildCSP($context, $config);
            header("Content-Security-Policy: $csp");
        }
        
        // 移除服务器信息泄露
        if (($config['security_hide_server_info'] ?? '1') === '1') {
            header_remove('Server');
            header_remove('X-Powered-By');
        }
    }

    /**
     * 构建CSP策略
     */
    private static function buildCSP(string $context, array $config): string
    {
        switch ($context) {
            case 'admin':
                return "default-src 'self'; " .
                       "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://static.cloudflareinsights.com; " .
                       "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; " .
                       "img-src 'self' data: https: http:; " .
                       "font-src 'self' data: https://cdn.jsdelivr.net; " .
                       "connect-src 'self' https://cdn.jsdelivr.net https://cloudflareinsights.com; " .
                       "media-src 'self' https: http:; " .
                       "object-src 'none'; " .
                       "base-uri 'self'; " .
                       "form-action 'self'";
                       
            case 'api':
                return "default-src 'none'; " .
                       "script-src 'none'; " .
                       "style-src 'none'; " .
                       "img-src 'none'; " .
                       "font-src 'none'; " .
                       "connect-src 'self'; " .
                       "object-src 'none'; " .
                       "base-uri 'none'; " .
                       "form-action 'none'";
                       
            default: // frontend
                $scriptSrc = $config['security_csp_script_src'] ?? "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://static.cloudflareinsights.com";
                $styleSrc = $config['security_csp_style_src'] ?? "'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://fonts.googleapis.com";
                $imgSrc = $config['security_csp_img_src'] ?? "'self' data: https: http:";
                
                return "default-src 'self'; " .
                       "script-src $scriptSrc; " .
                       "style-src $styleSrc; " .
                       "img-src $imgSrc; " .
                       "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; " .
                       "connect-src 'self' https://cdn.jsdelivr.net https://cloudflareinsights.com; " .
                       "media-src 'self' https: http:; " .
                       "object-src 'none'; " .
                       "base-uri 'self'; " .
                       "form-action 'self'";
        }
    }

    /**
     * 获取安全配置
     */
    private static function getSecurityConfig(): array
    {
        if (self::$config === null) {
            try {
                $db = XpkDatabase::getInstance();
                $rows = $db->query("SELECT config_name, config_value FROM " . DB_PREFIX . "config WHERE config_name LIKE 'security_%'");
                self::$config = [];
                foreach ($rows as $row) {
                    self::$config[$row['config_name']] = $row['config_value'];
                }
            } catch (Exception $e) {
                // 数据库异常时使用默认配置
                self::$config = [
                    'security_csp_enabled' => '1',
                    'security_frame_options' => 'SAMEORIGIN',
                    'security_xss_protection' => '1'
                ];
            }
        }
        
        return self::$config;
    }

    /**
     * 验证CSP nonce
     */
    public static function generateNonce(): string
    {
        return base64_encode(random_bytes(16));
    }

    /**
     * 清理配置缓存
     */
    public static function clearConfigCache(): void
    {
        self::$config = null;
    }

    /**
     * 生成 CSRF Token
     * 如果 Session 中不存在 Token，则生成新的
     * 
     * @return string CSRF Token
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * 验证 CSRF Token
     * 使用时间常数比较防止时序攻击
     * 
     * @param string $token 待验证的 Token
     * @return bool 验证是否通过
     */
    public static function validateToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // 检查 Token 是否存在
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        // 检查 Token 是否过期（24小时）
        $tokenTime = $_SESSION['csrf_token_time'] ?? 0;
        if (time() - $tokenTime > 86400) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
        
        // 使用时间常数比较防止时序攻击
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * 强制验证 CSRF Token
     * 验证失败时返回 403 错误并终止执行
     * 
     * @param bool $fromPost 是否从 POST 获取 Token（默认 true）
     * @return void
     */
    public static function requireToken(bool $fromPost = true): void
    {
        $token = '';
        
        if ($fromPost) {
            $token = $_POST['csrf_token'] ?? '';
        } else {
            $token = $_GET['csrf_token'] ?? $_POST['csrf_token'] ?? '';
        }
        
        if (!self::validateToken($token)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => 'CSRF token 验证失败，请刷新页面后重试'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    /**
     * 刷新 CSRF Token
     * 在敏感操作后调用，生成新的 Token
     * 
     * @return string 新的 CSRF Token
     */
    public static function refreshToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        
        return self::generateToken();
    }

    /**
     * 获取 CSRF Token 的 HTML input 标签
     * 方便在表单中使用
     * 
     * @return string HTML input 标签
     */
    public static function getTokenField(): string
    {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * 获取 CSRF Token 的 meta 标签
     * 方便在 AJAX 请求中使用
     * 
     * @return string HTML meta 标签
     */
    public static function getTokenMeta(): string
    {
        $token = self::generateToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}