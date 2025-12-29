<?php
/**
 * 操作日志模型
 * Powered by https://xpornkit.com
 */

class XpkAdminLog extends XpkModel
{
    protected string $table = DB_PREFIX . 'admin_log';
    protected string $pk = 'log_id';

    /**
     * 记录操作日志
     */
    public static function log(string $action, string $module, string $content = ''): void
    {
        $admin = $_SESSION['admin'] ?? null;
        if (!$admin) {
            return;
        }

        $db = XpkDatabase::getInstance();
        $db->execute(
            "INSERT INTO " . DB_PREFIX . "admin_log (admin_id, admin_name, log_action, log_module, log_content, log_ip, log_time) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $admin['id'],
                $admin['name'],
                $action,
                $module,
                mb_substr($content, 0, 500),
                $_SERVER['REMOTE_ADDR'] ?? '',
                time()
            ]
        );
    }

    /**
     * 获取日志列表
     */
    public function getList(int $page = 1, int $pageSize = 20, array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['admin_id'])) {
            $where[] = 'admin_id = ?';
            $params[] = $filters['admin_id'];
        }

        if (!empty($filters['module'])) {
            $where[] = 'log_module = ?';
            $params[] = $filters['module'];
        }

        if (!empty($filters['start_time'])) {
            $where[] = 'log_time >= ?';
            $params[] = $filters['start_time'];
        }

        if (!empty($filters['end_time'])) {
            $where[] = 'log_time <= ?';
            $params[] = $filters['end_time'];
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;

        $list = $this->db->query(
            "SELECT * FROM {$this->table} {$whereStr} ORDER BY log_id DESC LIMIT {$pageSize} OFFSET {$offset}",
            $params
        );

        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} {$whereStr}",
            $params
        )['cnt'] ?? 0;

        return [
            'list' => $list,
            'total' => (int)$total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize),
        ];
    }

    /**
     * 清理旧日志（保留最近30天）
     */
    public function clean(int $days = 30): int
    {
        $time = time() - ($days * 86400);
        return $this->db->execute(
            "DELETE FROM {$this->table} WHERE log_time < ?",
            [$time]
        );
    }
}
