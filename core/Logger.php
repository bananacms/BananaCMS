<?php
/**
 * 日志记录类
 * Powered by https://xpornkit.com
 */

class XpkLogger
{
    // 日志级别
    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_CRITICAL = 'CRITICAL';

    /**
     * 记录错误日志
     * 
     * @param string $message 错误消息
     * @param array $context 上下文信息
     * @param Exception|null $exception 异常对象
     */
    public static function error(string $message, array $context = [], ?Exception $exception = null): void
    {
        self::log(self::LEVEL_ERROR, $message, $context, $exception);
    }

    /**
     * 记录警告日志
     * 
     * @param string $message 警告消息
     * @param array $context 上下文信息
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log(self::LEVEL_WARNING, $message, $context);
    }

    /**
     * 记录信息日志
     * 
     * @param string $message 信息消息
     * @param array $context 上下文信息
     */
    public static function info(string $message, array $context = []): void
    {
        self::log(self::LEVEL_INFO, $message, $context);
    }

    /**
     * 记录调试日志
     * 
     * @param string $message 调试消息
     * @param array $context 上下文信息
     */
    public static function debug(string $message, array $context = []): void
    {
        // 只在调试模式下记录
        if (defined('APP_DEBUG') && APP_DEBUG) {
            self::log(self::LEVEL_DEBUG, $message, $context);
        }
    }

    /**
     * 记录严重错误日志
     * 
     * @param string $message 错误消息
     * @param array $context 上下文信息
     * @param Exception|null $exception 异常对象
     */
    public static function critical(string $message, array $context = [], ?Exception $exception = null): void
    {
        self::log(self::LEVEL_CRITICAL, $message, $context, $exception);
    }

    /**
     * 统一日志记录方法
     * 
     * @param string $level 日志级别
     * @param string $message 日志消息
     * @param array $context 上下文信息
     * @param Exception|null $exception 异常对象
     */
    private static function log(string $level, string $message, array $context = [], ?Exception $exception = null): void
    {
        $logDir = RUNTIME_PATH . 'logs/';
        
        // 确保日志目录存在
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        // 按日期和级别分文件
        $logFile = $logDir . strtolower($level) . '_' . date('Y-m-d') . '.log';

        // 过滤敏感信息
        $context = self::filterSensitive($context);

        // 构建日志内容
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        // 添加异常信息
        if ($exception) {
            $logEntry['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => self::formatTrace($exception->getTrace())
            ];
        }

        // 添加请求信息
        $logEntry['request'] = [
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'ip' => self::getClientIp(),
            'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200)
        ];

        // 添加用户信息（如果已登录）
        if (isset($_SESSION['user']['user_id'])) {
            $logEntry['user_id'] = $_SESSION['user']['user_id'];
        } elseif (isset($_SESSION['admin']['admin_id'])) {
            $logEntry['admin_id'] = $_SESSION['admin']['admin_id'];
        }

        // 格式化为 JSON（便于解析）
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

        // 写入日志文件
        @file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);

        // 严重错误额外记录到单独的文件
        if ($level === self::LEVEL_CRITICAL || $level === self::LEVEL_ERROR) {
            $criticalFile = $logDir . 'critical_' . date('Y-m-d') . '.log';
            @file_put_contents($criticalFile, $logLine, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * 过滤敏感信息
     * 
     * @param array $data 数据数组
     * @return array 过滤后的数据
     */
    private static function filterSensitive(array $data): array
    {
        $sensitive = [
            'password', 'pwd', 'passwd', 'pass',
            'token', 'secret', 'key', 'api_key',
            'access_token', 'refresh_token',
            'credit_card', 'card_number', 'cvv',
            'user_pwd', 'admin_pwd'
        ];

        foreach ($data as $key => $value) {
            // 检查键名是否包含敏感词
            $lowerKey = strtolower($key);
            foreach ($sensitive as $word) {
                if (strpos($lowerKey, $word) !== false) {
                    $data[$key] = '***FILTERED***';
                    break;
                }
            }

            // 递归处理数组
            if (is_array($value)) {
                $data[$key] = self::filterSensitive($value);
            }
        }

        return $data;
    }

    /**
     * 格式化异常堆栈
     * 
     * @param array $trace 堆栈数组
     * @return array 格式化后的堆栈
     */
    private static function formatTrace(array $trace): array
    {
        $formatted = [];
        $maxTraces = 10; // 只保留前10层堆栈

        foreach (array_slice($trace, 0, $maxTraces) as $item) {
            $formatted[] = [
                'file' => $item['file'] ?? 'unknown',
                'line' => $item['line'] ?? 0,
                'function' => ($item['class'] ?? '') . ($item['type'] ?? '') . ($item['function'] ?? ''),
            ];
        }

        return $formatted;
    }

    /**
     * 获取客户端 IP
     * 
     * @return string IP 地址
     */
    private static function getClientIp(): string
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        
        // 取第一个 IP（可能有多个）
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }

    /**
     * 清理过期日志
     * 
     * @param int $days 保留天数
     * @return int 删除的文件数
     */
    public static function cleanOldLogs(int $days = 30): int
    {
        $logDir = RUNTIME_PATH . 'logs/';
        
        if (!is_dir($logDir)) {
            return 0;
        }

        $deleted = 0;
        $cutoffTime = time() - ($days * 86400);

        $files = glob($logDir . '*.log');
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                if (@unlink($file)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * 获取日志统计
     * 
     * @param int $days 统计天数
     * @return array 统计信息
     */
    public static function getStats(int $days = 7): array
    {
        $logDir = RUNTIME_PATH . 'logs/';
        
        if (!is_dir($logDir)) {
            return [
                'total_files' => 0,
                'total_size' => 0,
                'by_level' => []
            ];
        }

        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'by_level' => [
                'debug' => 0,
                'info' => 0,
                'warning' => 0,
                'error' => 0,
                'critical' => 0
            ]
        ];

        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        $files = glob($logDir . '*.log');

        foreach ($files as $file) {
            // 只统计指定天数内的日志
            $filename = basename($file);
            if (preg_match('/_(\d{4}-\d{2}-\d{2})\.log$/', $filename, $matches)) {
                if ($matches[1] < $cutoffDate) {
                    continue;
                }
            }

            $stats['total_files']++;
            $stats['total_size'] += filesize($file);

            // 统计各级别日志数量
            foreach ($stats['by_level'] as $level => $count) {
                if (strpos($filename, $level . '_') === 0) {
                    $stats['by_level'][$level] = count(file($file));
                    break;
                }
            }
        }

        return $stats;
    }
}

