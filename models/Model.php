<?php
/**
 * 模型基类
 * Powered by https://xpornkit.com
 */

abstract class XpkModel
{
    protected XpkDatabase $db;
    protected string $table;
    protected string $pk = 'id';

    public function __construct()
    {
        $this->db = XpkDatabase::getInstance();
    }

    /**
     * 根据ID查找
     */
    public function find(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE {$this->pk} = ?",
            [$id]
        );
    }

    /**
     * 根据字段查找
     */
    public function findBy(string $field, mixed $value): ?array
    {
        $field = $this->sanitizeField($field);
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE {$field} = ?",
            [$value]
        );
    }

    /**
     * 查询所有
     */
    public function all(array $where = [], string $order = 'id DESC', int $limit = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $field => $value) {
                $field = $this->sanitizeField($field);
                $conditions[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY ' . $this->sanitizeOrder($order);
        
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->db->query($sql, $params);
    }

    /**
     * 分页查询
     */
    public function paginate(int $page = 1, int $pageSize = 20, array $where = [], string $order = 'id DESC'): array
    {
        $offset = ($page - 1) * $pageSize;
        
        $sql = "SELECT * FROM {$this->table}";
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $field => $value) {
                $field = $this->sanitizeField($field);
                $conditions[] = "{$field} = ?";
                $params[] = $value;
            }
            $whereStr = ' WHERE ' . implode(' AND ', $conditions);
            $sql .= $whereStr;
            $countSql .= $whereStr;
        }

        $sql .= ' ORDER BY ' . $this->sanitizeOrder($order);
        $sql .= " LIMIT {$pageSize} OFFSET {$offset}";

        $list = $this->db->query($sql, $params);
        $total = $this->db->queryOne($countSql, $params)['total'] ?? 0;

        return [
            'list' => $list,
            'total' => (int)$total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize),
        ];
    }

    /**
     * 插入数据
     */
    public function insert(array $data): int
    {
        $fields = [];
        $placeholders = [];
        $values = [];

        foreach ($data as $field => $value) {
            $fields[] = $this->sanitizeField($field);
            $placeholders[] = '?';
            $values[] = $value;
        }

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $this->db->execute($sql, $values);
        
        return (int)$this->db->lastInsertId();
    }

    /**
     * 更新数据
     */
    public function update(int $id, array $data): int
    {
        $sets = [];
        $values = [];

        foreach ($data as $field => $value) {
            $sets[] = $this->sanitizeField($field) . ' = ?';
            $values[] = $value;
        }
        $values[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$this->pk} = ?";
        return $this->db->execute($sql, $values);
    }

    /**
     * 删除数据
     */
    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM {$this->table} WHERE {$this->pk} = ?",
            [$id]
        );
    }

    /**
     * 统计数量
     */
    public function count(array $where = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $field => $value) {
                $field = $this->sanitizeField($field);
                $conditions[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $result = $this->db->queryOne($sql, $params);
        return (int)($result['total'] ?? 0);
    }

    /**
     * 字段名安全过滤
     */
    protected function sanitizeField(string $field): string
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $field)) {
            throw new InvalidArgumentException("Invalid field name: {$field}");
        }
        return $field;
    }

    /**
     * 排序安全过滤
     */
    protected function sanitizeOrder(string $order): string
    {
        $parts = preg_split('/\s+/', trim($order), 2);
        $field = $parts[0] ?? 'id';
        $direction = strtoupper($parts[1] ?? 'DESC');

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $field)) {
            $field = 'id';
        }
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }

        return "{$field} {$direction}";
    }

    /**
     * 授权初始化
     */
    protected function xpk_init(): bool
    {
        if (defined('XPK_UNLICENSED') && XPK_UNLICENSED) {
            return false;
        }
        return true;
    }
}
