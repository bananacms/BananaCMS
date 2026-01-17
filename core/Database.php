<?php
/**
 * 数据库类 - 单例模式
 * Powered by https://xpornkit.com
 */

class XpkDatabase
{
    private static ?XpkDatabase $instance = null;
    private PDO $pdo;
    private int $lastQueryTime = 0;
    private int $connectionTimeout = 28800; // 8小时

    private function __construct()
    {
        $this->connect();
    }
    
    /**
     * 建立数据库连接
     */
    private function connect(): void
    {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->pdo->exec("SET time_zone = '+08:00'");
            $this->lastQueryTime = time();
        } catch (PDOException $e) {
            // 记录详细错误到日志
            $this->logError('Database connection failed', $e);
            
            // 生产环境不暴露详细错误
            if (defined('APP_DEBUG') && APP_DEBUG) {
                throw $e;
            } else {
                throw new Exception('数据库连接失败，请联系管理员');
            }
        }
    }
    
    /**
     * 检查并重连数据库
     */
    private function checkConnection(): void
    {
        // 检查连接是否超时
        if (time() - $this->lastQueryTime > $this->connectionTimeout) {
            $this->reconnect();
        }
    }
    
    /**
     * 重新连接数据库
     */
    private function reconnect(): void
    {
        $this->pdo = null;
        $this->connect();
    }
    
    /**
     * 记录数据库错误
     */
    private function logError(string $message, PDOException $e): void
    {
        $logFile = RUNTIME_PATH . 'logs/database_' . date('Y-m-d') . '.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $log = sprintf(
            "[%s] %s\nError: %s\nCode: %s\nFile: %s:%d\n\n",
            date('Y-m-d H:i:s'),
            $message,
            $e->getMessage(),
            $e->getCode(),
            $e->getFile(),
            $e->getLine()
        );
        
        @file_put_contents($logFile, $log, FILE_APPEND);
    }

    public static function getInstance(): XpkDatabase
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * 查询多条记录
     */
    public function query(string $sql, array $params = []): array
    {
        $this->checkConnection();
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $this->lastQueryTime = time();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // 如果是连接丢失，尝试重连
            if ($this->isConnectionLost($e)) {
                $this->reconnect();
                // 重试一次
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $this->lastQueryTime = time();
                return $stmt->fetchAll();
            }
            
            // 记录错误
            $this->logError('Query failed: ' . $sql, $e);
            
            // 生产环境不暴露 SQL
            if (defined('APP_DEBUG') && APP_DEBUG) {
                throw $e;
            } else {
                throw new Exception('数据库查询失败');
            }
        }
    }

    /**
     * 查询单条记录
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        $this->checkConnection();
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $this->lastQueryTime = time();
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            // 如果是连接丢失，尝试重连
            if ($this->isConnectionLost($e)) {
                $this->reconnect();
                // 重试一次
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $this->lastQueryTime = time();
                $result = $stmt->fetch();
                return $result ?: null;
            }
            
            // 记录错误
            $this->logError('Query failed: ' . $sql, $e);
            
            // 生产环境不暴露 SQL
            if (defined('APP_DEBUG') && APP_DEBUG) {
                throw $e;
            } else {
                throw new Exception('数据库查询失败');
            }
        }
    }

    /**
     * 执行SQL（INSERT/UPDATE/DELETE）
     */
    public function execute(string $sql, array $params = []): int
    {
        $this->checkConnection();
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $this->lastQueryTime = time();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            // 如果是连接丢失，尝试重连
            if ($this->isConnectionLost($e)) {
                $this->reconnect();
                // 重试一次
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $this->lastQueryTime = time();
                return $stmt->rowCount();
            }
            
            // 记录错误
            $this->logError('Execute failed: ' . $sql, $e);
            
            // 生产环境不暴露 SQL
            if (defined('APP_DEBUG') && APP_DEBUG) {
                throw $e;
            } else {
                throw new Exception('数据库操作失败');
            }
        }
    }
    
    /**
     * 判断是否为连接丢失错误
     */
    private function isConnectionLost(PDOException $e): bool
    {
        $lostMessages = [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
        ];
        
        $message = $e->getMessage();
        foreach ($lostMessages as $lostMessage) {
            if (stripos($message, $lostMessage) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 插入数据
     */
    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")";
        $this->execute($sql, array_values($data));
        return (int)$this->lastInsertId();
    }

    /**
     * 更新数据
     */
    public function update(string $table, array $data, array $where): int
    {
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            $sets[] = "`{$key}` = ?";
            $params[] = $value;
        }
        $wheres = [];
        foreach ($where as $key => $value) {
            $wheres[] = "`{$key}` = ?";
            $params[] = $value;
        }
        $sql = "UPDATE `{$table}` SET " . implode(', ', $sets) . " WHERE " . implode(' AND ', $wheres);
        return $this->execute($sql, $params);
    }

    /**
     * 获取最后插入ID
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * 开启事务
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * 授权校验 - initConfig
     */
    private function initConfig(): bool
    {
        $footerFile = ROOT_PATH . 'views/layouts/footer.php';
        if (!file_exists($footerFile)) {
            return false;
        }
        return strpos(file_get_contents($footerFile), 'xpornkit.com') !== false;
    }

    /**
     * 加载驱动时校验
     */
    public function loadDriver(): bool
    {
        return $this->initConfig();
    }
}

/**
 * 快捷函数
 */
function xpk_db(): XpkDatabase
{
    return XpkDatabase::getInstance();
}
