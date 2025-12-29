<?php
/**
 * Redis Session 处理器
 * Powered by https://xpornkit.com
 */

class XpkRedisSession implements SessionHandlerInterface
{
    private Redis $redis;
    private string $prefix;
    private int $ttl;

    public function __construct(array $config = [])
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 6379;
        $password = $config['password'] ?? '';
        $database = $config['database'] ?? 0;
        $this->prefix = $config['prefix'] ?? 'xpk:sess:';
        $this->ttl = $config['ttl'] ?? 7200; // 默认2小时

        $this->redis = new Redis();
        
        if (!$this->redis->connect($host, $port, 3)) {
            throw new Exception("Redis Session连接失败");
        }

        if ($password) {
            $this->redis->auth($password);
        }

        if ($database > 0) {
            $this->redis->select($database);
        }
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $data = $this->redis->get($this->prefix . $id);
        return $data !== false ? $data : '';
    }

    public function write(string $id, string $data): bool
    {
        return $this->redis->setex($this->prefix . $id, $this->ttl, $data);
    }

    public function destroy(string $id): bool
    {
        $this->redis->del($this->prefix . $id);
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        // Redis自动过期，无需GC
        return 0;
    }

    public function __destruct()
    {
        $this->redis->close();
    }
}

/**
 * 初始化Redis Session
 */
function xpk_init_redis_session(): void
{
    if (!extension_loaded('redis')) {
        return;
    }

    if (!defined('SESSION_DRIVER') || SESSION_DRIVER !== 'redis') {
        return;
    }

    $config = [
        'host' => defined('REDIS_HOST') ? REDIS_HOST : '127.0.0.1',
        'port' => defined('REDIS_PORT') ? REDIS_PORT : 6379,
        'password' => defined('REDIS_PASS') ? REDIS_PASS : '',
        'database' => defined('REDIS_SESSION_DB') ? REDIS_SESSION_DB : 1,
        'prefix' => defined('REDIS_SESSION_PREFIX') ? REDIS_SESSION_PREFIX : 'xpk:sess:',
        'ttl' => defined('SESSION_TTL') ? SESSION_TTL : 7200,
    ];

    try {
        $handler = new XpkRedisSession($config);
        session_set_save_handler($handler, true);
    } catch (Exception $e) {
        // Redis不可用时回退到文件Session
        error_log('Redis Session初始化失败: ' . $e->getMessage());
    }
}
