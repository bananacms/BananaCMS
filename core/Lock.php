<?php
/**
 * 分布式锁管理
 * 用于防止并发操作冲突
 * Powered by https://xpornkit.com
 */

class XpkLock
{
    private static string $lockDir = '';
    private static int $lockTimeout = 3600; // 1小时默认超时

    /**
     * 初始化锁目录
     */
    public static function init(): void
    {
        if (empty(self::$lockDir)) {
            self::$lockDir = RUNTIME_PATH . 'locks/';
            if (!is_dir(self::$lockDir)) {
                mkdir(self::$lockDir, 0755, true);
            }
        }
    }

    /**
     * 获取锁
     * 
     * @param string $key 锁键名
     * @param int $timeout 超时时间（秒）
     * @param int $maxWait 最大等待时间（秒）
     * @return bool 是否成功获取锁
     */
    public static function acquire(string $key, int $timeout = 3600, int $maxWait = 30): bool
    {
        self::init();
        
        $lockFile = self::$lockDir . md5($key) . '.lock';
        $startTime = time();
        
        while (true) {
            // 尝试创建锁文件（原子操作）
            $fp = @fopen($lockFile, 'x');
            
            if ($fp !== false) {
                // 成功创建锁文件
                fwrite($fp, json_encode([
                    'pid' => getmypid(),
                    'time' => time(),
                    'key' => $key,
                    'timeout' => $timeout
                ]));
                fclose($fp);
                
                // 设置文件权限
                @chmod($lockFile, 0644);
                
                return true;
            }
            
            // 检查锁是否过期
            if (file_exists($lockFile)) {
                $lockData = @json_decode(file_get_contents($lockFile), true);
                $lockTime = $lockData['time'] ?? 0;
                $lockTimeout = $lockData['timeout'] ?? self::$lockTimeout;
                
                if (time() - $lockTime > $lockTimeout) {
                    // 锁已过期，删除并重试
                    @unlink($lockFile);
                    continue;
                }
            }
            
            // 检查是否超过最大等待时间
            if (time() - $startTime > $maxWait) {
                return false;
            }
            
            // 等待后重试
            usleep(100000); // 等待 0.1 秒
        }
    }

    /**
     * 释放锁
     * 
     * @param string $key 锁键名
     * @return bool 是否成功释放
     */
    public static function release(string $key): bool
    {
        self::init();
        
        $lockFile = self::$lockDir . md5($key) . '.lock';
        
        if (file_exists($lockFile)) {
            return @unlink($lockFile);
        }
        
        return true;
    }

    /**
     * 检查锁是否存在
     * 
     * @param string $key 锁键名
     * @return bool
     */
    public static function exists(string $key): bool
    {
        self::init();
        
        $lockFile = self::$lockDir . md5($key) . '.lock';
        
        if (!file_exists($lockFile)) {
            return false;
        }
        
        // 检查锁是否过期
        $lockData = @json_decode(file_get_contents($lockFile), true);
        $lockTime = $lockData['time'] ?? 0;
        $lockTimeout = $lockData['timeout'] ?? self::$lockTimeout;
        
        if (time() - $lockTime > $lockTimeout) {
            // 锁已过期，删除
            @unlink($lockFile);
            return false;
        }
        
        return true;
    }

    /**
     * 获取锁信息
     * 
     * @param string $key 锁键名
     * @return array|null 锁信息或 null
     */
    public static function getInfo(string $key): ?array
    {
        self::init();
        
        $lockFile = self::$lockDir . md5($key) . '.lock';
        
        if (!file_exists($lockFile)) {
            return null;
        }
        
        $lockData = @json_decode(file_get_contents($lockFile), true);
        
        if (!$lockData) {
            return null;
        }
        
        // 检查锁是否过期
        $lockTime = $lockData['time'] ?? 0;
        $lockTimeout = $lockData['timeout'] ?? self::$lockTimeout;
        
        if (time() - $lockTime > $lockTimeout) {
            // 锁已过期，删除
            @unlink($lockFile);
            return null;
        }
        
        return $lockData;
    }

    /**
     * 强制释放锁（管理员操作）
     * 
     * @param string $key 锁键名
     * @return bool
     */
    public static function forceRelease(string $key): bool
    {
        self::init();
        
        $lockFile = self::$lockDir . md5($key) . '.lock';
        
        if (file_exists($lockFile)) {
            return @unlink($lockFile);
        }
        
        return true;
    }

    /**
     * 清理过期的锁
     * 
     * @param int $maxAge 最大年龄（秒）
     * @return int 清理的锁数量
     */
    public static function cleanup(int $maxAge = 86400): int
    {
        self::init();
        
        $count = 0;
        $files = glob(self::$lockDir . '*.lock');
        
        foreach ($files as $file) {
            $lockData = @json_decode(file_get_contents($file), true);
            $lockTime = $lockData['time'] ?? 0;
            $lockTimeout = $lockData['timeout'] ?? self::$lockTimeout;
            
            if (time() - $lockTime > max($lockTimeout, $maxAge)) {
                if (@unlink($file)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }

    /**
     * 使用锁执行回调函数
     * 
     * @param string $key 锁键名
     * @param callable $callback 回调函数
     * @param int $timeout 锁超时时间（秒）
     * @param int $maxWait 最大等待时间（秒）
     * @return mixed 回调函数的返回值
     * @throws Exception 如果无法获取锁
     */
    public static function execute(string $key, callable $callback, int $timeout = 3600, int $maxWait = 30)
    {
        if (!self::acquire($key, $timeout, $maxWait)) {
            throw new Exception("无法获取锁: {$key}");
        }
        
        try {
            return $callback();
        } finally {
            self::release($key);
        }
    }

    /**
     * 获取所有活跃的锁
     * 
     * @return array 锁列表
     */
    public static function getActiveLocks(): array
    {
        self::init();
        
        $locks = [];
        $files = glob(self::$lockDir . '*.lock');
        
        foreach ($files as $file) {
            $lockData = @json_decode(file_get_contents($file), true);
            
            if (!$lockData) {
                continue;
            }
            
            // 检查锁是否过期
            $lockTime = $lockData['time'] ?? 0;
            $lockTimeout = $lockData['timeout'] ?? self::$lockTimeout;
            
            if (time() - $lockTime > $lockTimeout) {
                // 锁已过期，删除
                @unlink($file);
                continue;
            }
            
            $locks[] = [
                'key' => $lockData['key'] ?? '',
                'pid' => $lockData['pid'] ?? 0,
                'time' => $lockData['time'] ?? 0,
                'age' => time() - ($lockData['time'] ?? 0),
                'timeout' => $lockTimeout,
                'file' => basename($file)
            ];
        }
        
        return $locks;
    }
}
