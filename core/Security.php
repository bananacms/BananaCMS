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
                $scriptSrc = $config['security_csp_script_src'] ?? "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com";
                $styleSrc = $config['security_csp_style_src'] ?? "'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com";
                $imgSrc = $config['security_csp_img_src'] ?? "'self' data: https: http:";
                
                return "default-src 'self'; " .
                       "script-src $scriptSrc; " .
                       "style-src $styleSrc; " .
                       "img-src $imgSrc; " .
                       "font-src 'self' https://fonts.gstatic.com data:; " .
                       "connect-src 'self'; " .
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
}