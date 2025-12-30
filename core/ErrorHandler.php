<?php
/**
 * 统一错误处理类
 * Powered by https://xpornkit.com
 */

class XpkErrorHandler
{
    private static bool $registered = false;

    /**
     * 注册错误处理
     */
    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
        
        self::$registered = true;
    }

    /**
     * 处理错误
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $error = [
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
        ];

        self::logError($error);

        if (APP_DEBUG) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }

        return true;
    }

    /**
     * 处理异常
     */
    public static function handleException(\Throwable $e): void
    {
        $error = [
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];

        self::logError($error);

        if (APP_DEBUG) {
            self::renderDebugError($error);
        } else {
            self::renderProductionError();
        }
    }

    /**
     * 处理致命错误
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::logError($error);
            
            if (!APP_DEBUG) {
                self::renderProductionError();
            }
        }
    }

    /**
     * 记录错误日志
     */
    private static function logError(array $error): void
    {
        $logDir = RUNTIME_PATH . 'logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . date('Y-m-d') . '.log';
        $time = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $uri = $_SERVER['REQUEST_URI'] ?? 'CLI';
        
        $log = "[{$time}] [{$ip}] [{$uri}]\n";
        $log .= "Type: " . ($error['type'] ?? 'Unknown') . "\n";
        $log .= "Message: " . ($error['message'] ?? '') . "\n";
        $log .= "File: " . ($error['file'] ?? '') . " Line: " . ($error['line'] ?? '') . "\n";
        if (!empty($error['trace'])) {
            $log .= "Trace:\n" . $error['trace'] . "\n";
        }
        $log .= str_repeat('-', 80) . "\n";

        file_put_contents($logFile, $log, FILE_APPEND | LOCK_EX);
    }

    /**
     * 渲染调试错误页面
     */
    private static function renderDebugError(array $error): void
    {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }

        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>错误</title>';
        echo '<style>body{font-family:sans-serif;padding:20px;background:#f5f5f5}';
        echo '.error{background:#fff;border-left:4px solid #e74c3c;padding:20px;margin:20px 0;box-shadow:0 2px 5px rgba(0,0,0,0.1)}';
        echo 'h1{color:#e74c3c;margin:0 0 10px}pre{background:#2d2d2d;color:#f8f8f2;padding:15px;overflow:auto;border-radius:4px}</style></head>';
        echo '<body><div class="error">';
        echo '<h1>' . htmlspecialchars($error['type'] ?? 'Error') . '</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($error['message'] ?? '') . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($error['file'] ?? '') . ' <strong>Line:</strong> ' . ($error['line'] ?? '') . '</p>';
        if (!empty($error['trace'])) {
            echo '<pre>' . htmlspecialchars($error['trace']) . '</pre>';
        }
        echo '</div></body></html>';
        exit;
    }

    /**
     * 渲染生产环境错误页面
     */
    private static function renderProductionError(): void
    {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }

        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>系统错误</title>';
        echo '<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f5f5f5}';
        echo '.box{text-align:center;padding:40px}h1{color:#333;font-size:48px;margin:0}p{color:#666;margin:20px 0}</style></head>';
        echo '<body><div class="box"><h1>500</h1><p>系统繁忙，请稍后再试</p></div></body></html>';
        exit;
    }
}
