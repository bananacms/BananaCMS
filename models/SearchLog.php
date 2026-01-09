<?php
/**
 * 搜索日志模型
 * Powered by https://xpornkit.com
 */

class XpkSearchLog extends XpkModel
{
    protected string $table = DB_PREFIX . 'search_log';
    protected string $pk = 'log_id';

    /**
     * 记录搜索日志
     */
    public function log(string $keyword, string $ip = ''): void
    {
        if (empty($keyword)) return;
        
        // 过滤敏感词和无效搜索
        $keyword = trim($keyword);
        if (strlen($keyword) < 2 || strlen($keyword) > 50) return;
        
        // 获取IP
        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        
        $this->db->execute(
            "INSERT INTO {$this->table} (keyword, search_time, search_ip) VALUES (?, ?, ?)",
            [$keyword, time(), $ip]
        );
    }

    /**
     * 获取热门搜索词
     */
    public function getHotKeywords(int $limit = 10, int $days = 7): array
    {
        $timeLimit = time() - ($days * 24 * 3600);
        
        return $this->db->query(
            "SELECT keyword, COUNT(*) as search_count 
             FROM {$this->table} 
             WHERE search_time > ? AND LENGTH(keyword) >= 2
             GROUP BY keyword 
             ORDER BY search_count DESC, MAX(search_time) DESC 
             LIMIT ?",
            [$timeLimit, $limit]
        );
    }

    /**
     * 获取最新搜索词
     */
    public function getRecentKeywords(int $limit = 10): array
    {
        return $this->db->query(
            "SELECT DISTINCT keyword 
             FROM {$this->table} 
             WHERE LENGTH(keyword) >= 2
             ORDER BY search_time DESC 
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * 获取搜索统计
     */
    public function getStats(int $days = 30): array
    {
        $timeLimit = time() - ($days * 24 * 3600);
        
        $stats = $this->db->queryOne(
            "SELECT 
                COUNT(*) as total_searches,
                COUNT(DISTINCT keyword) as unique_keywords,
                COUNT(DISTINCT search_ip) as unique_ips
             FROM {$this->table} 
             WHERE search_time > ?",
            [$timeLimit]
        );
        
        return $stats ?: ['total_searches' => 0, 'unique_keywords' => 0, 'unique_ips' => 0];
    }

    /**
     * 清理旧日志
     */
    public function clean(int $keepDays = 90): int
    {
        $timeLimit = time() - ($keepDays * 24 * 3600);
        
        return $this->db->execute(
            "DELETE FROM {$this->table} WHERE search_time < ?",
            [$timeLimit]
        );
    }

    /**
     * 搜索关键词建议（自动补全）
     */
    public function getSuggestions(string $prefix, int $limit = 8): array
    {
        if (strlen($prefix) < 2) return [];
        
        $prefix = str_replace(['%', '_'], ['\\%', '\\_'], $prefix);
        
        return $this->db->query(
            "SELECT keyword, COUNT(*) as search_count
             FROM {$this->table} 
             WHERE keyword LIKE ? AND LENGTH(keyword) >= 2
             GROUP BY keyword 
             ORDER BY search_count DESC, MAX(search_time) DESC 
             LIMIT ?",
            [$prefix . '%', $limit]
        );
    }

    /**
     * 获取搜索日志列表（分页）
     */
    public function getLogList(int $page = 1, int $pageSize = 50, string $keyword = ''): array
    {
        $offset = ($page - 1) * $pageSize;
        $params = [];
        
        $sql = "SELECT * FROM {$this->table}";
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($keyword)) {
            $keyword = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $keyword) . '%';
            $sql .= " WHERE keyword LIKE ?";
            $countSql .= " WHERE keyword LIKE ?";
            $params[] = $keyword;
        }
        
        $sql .= " ORDER BY search_time DESC LIMIT {$pageSize} OFFSET {$offset}";
        
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
}