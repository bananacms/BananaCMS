<?php
/**
 * 缓存类（支持 File / Redis）
 * Powered by https://xpornkit.com
 */

interface XpkCacheDriver
{
    public function get(string $key, mixed $default = null): mixed;
    public function set(string $key, mixed $value, int $ttl = 0): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
    public function has(string $key): bool;
    public function increment(string $key, int $step = 1): int;
    public function decrement(string $key, int $step = 1): int;
}

/**
 * 文件缓存驱动
 */
class XpkFileCache implements XpkCacheDriver
{
    private string $cachePath;
    private int $defaultTtl;

    public function __construct(int $defaultTtl = 3600)
    {
        $this->cachePath = RUNTIME_PATH . 'cache/data/';
        $this->defaultTtl = $defaultTtl;
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->getFile($key);
        if (!file_exists($file)) {
            return $default;
        }

        $content = file_get_contents($file);
        $data = @unserialize($content);

        if ($data === false || !isset($data['expire'], $data['value'])) {
            return $default;
        }

        if ($data['expire'] > 0 && $data['expire'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $file = $this->getFile($key);
        $ttl = $ttl ?: $this->defaultTtl;
        
        $data = [
            'expire' => $ttl > 0 ? time() + $ttl : 0,
            'value' => $value,
        ];

        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }

    public function delete(string $key): bool
    {
        $file = $this->getFile($key);
        return !file_exists($file) || unlink($file);
    }

    public function clear(): bool
    {
        $files = glob($this->cachePath . '*.cache');
        foreach ($files as $file) {
            @unlink($file);
        }
        return true;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function increment(string $key, int $step = 1): int
    {
        $value = (int)$this->get($key, 0) + $step;
        $this->set($key, $value);
        return $value;
    }

    public function decrement(string $key, int $step = 1): int
    {
        return $this->increment($key, -$step);
    }

    private function getFile(string $key): string
    {
        return $this->cachePath . md5($key) . '.cache';
    }
}

/**
 * Redis缓存驱动
 */
class XpkRedisCache implements XpkCacheDriver
{
    private ?Redis $redis = null;
    private string $prefix;
    private int $defaultTtl;

    public function __construct(array $config = [])
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 6379;
        $password = $config['password'] ?? '';
        $database = $config['database'] ?? 0;
        $this->prefix = $config['prefix'] ?? 'xpk:';
        $this->defaultTtl = $config['ttl'] ?? 3600;

        $this->redis = new Redis();
        
        if (!$this->redis->connect($host, $port, 3)) {
            throw new Exception("Redis连接失败: {$host}:{$port}");
        }

        if ($password) {
            $this->redis->auth($password);
        }

        if ($database > 0) {
            $this->redis->select($database);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->redis->get($this->prefix . $key);
        if ($value === false) {
            return $default;
        }
        $data = @unserialize($value);
        return $data !== false ? $data : $value;
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $ttl = $ttl ?: $this->defaultTtl;
        $value = serialize($value);
        
        if ($ttl > 0) {
            return $this->redis->setex($this->prefix . $key, $ttl, $value);
        }
        return $this->redis->set($this->prefix . $key, $value);
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($this->prefix . $key) >= 0;
    }

    public function clear(): bool
    {
        $keys = $this->redis->keys($this->prefix . '*');
        if (!empty($keys)) {
            $this->redis->del($keys);
        }
        return true;
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($this->prefix . $key) > 0;
    }

    public function increment(string $key, int $step = 1): int
    {
        return $this->redis->incrBy($this->prefix . $key, $step);
    }

    public function decrement(string $key, int $step = 1): int
    {
        return $this->redis->decrBy($this->prefix . $key, $step);
    }

    public function getRedis(): Redis
    {
        return $this->redis;
    }

    public function __destruct()
    {
        if ($this->redis) {
            $this->redis->close();
        }
    }
}

/**
 * 缓存管理器
 */
class XpkCache
{
    private static ?XpkCache $instance = null;
    private XpkCacheDriver $driver;
    private string $driverType;

    private function __construct()
    {
        $this->initDriver();
    }

    private function initDriver(): void
    {
        // 检查是否配置了Redis
        if (defined('CACHE_DRIVER') && CACHE_DRIVER === 'redis') {
            if (!extension_loaded('redis')) {
                throw new Exception('Redis扩展未安装');
            }
            
            $config = [
                'host' => defined('REDIS_HOST') ? REDIS_HOST : '127.0.0.1',
                'port' => defined('REDIS_PORT') ? REDIS_PORT : 6379,
                'password' => defined('REDIS_PASS') ? REDIS_PASS : '',
                'database' => defined('REDIS_DB') ? REDIS_DB : 0,
                'prefix' => defined('REDIS_PREFIX') ? REDIS_PREFIX : 'xpk:',
                'ttl' => defined('CACHE_TTL') ? CACHE_TTL : 3600,
            ];
            
            $this->driver = new XpkRedisCache($config);
            $this->driverType = 'redis';
        } else {
            $ttl = defined('CACHE_TTL') ? CACHE_TTL : 3600;
            $this->driver = new XpkFileCache($ttl);
            $this->driverType = 'file';
        }
    }

    public static function getInstance(): XpkCache
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->driver->get($key, $default);
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        return $this->driver->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->driver->delete($key);
    }

    public function clear(): bool
    {
        return $this->driver->clear();
    }

    public function has(string $key): bool
    {
        return $this->driver->has($key);
    }

    public function increment(string $key, int $step = 1): int
    {
        return $this->driver->increment($key, $step);
    }

    public function decrement(string $key, int $step = 1): int
    {
        return $this->driver->decrement($key, $step);
    }

    /**
     * 记住缓存（不存在时执行回调）
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    /**
     * 获取驱动类型
     */
    public function getDriverType(): string
    {
        return $this->driverType;
    }

    /**
     * 获取原始驱动（用于高级操作）
     */
    public function getDriver(): XpkCacheDriver
    {
        return $this->driver;
    }
}

/**
 * 快捷函数
 */
function xpk_cache(): XpkCache
{
    return XpkCache::getInstance();
}
