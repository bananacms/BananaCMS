<?php
/**
 * 数据库类 - 单例模式
 * Powered by https://xpornkit.com
 */

class XpkDatabase
{
    private static ?XpkDatabase $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $this->pdo->exec("SET time_zone = '+08:00'");
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
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * 查询单条记录
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * 执行SQL（INSERT/UPDATE/DELETE）
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
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
