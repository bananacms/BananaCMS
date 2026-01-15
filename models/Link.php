<?php
/**
 * 友情链接模型
 * Powered by https://xpornkit.com
 */

class XpkLink
{
    private XpkDatabase $db;
    private string $table;

    public function __construct()
    {
        $this->db = XpkDatabase::getInstance();
        $this->table = DB_PREFIX . 'link';
    }

    /**
     * 获取所有已通过的友链（前台展示）
     */
    public function getActive(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE link_status = 1 ORDER BY link_sort ASC, link_id ASC"
        );
    }

    /**
     * 获取列表（后台）
     */
    public function getList(int $status = -1, int $page = 1, int $pageSize = 20): array
    {
        $where = '';
        $params = [];
        
        if ($status >= 0) {
            $where = 'WHERE link_status = ?';
            $params[] = $status;
        }
        
        $offset = ($page - 1) * $pageSize;
        
        $list = $this->db->query(
            "SELECT * FROM {$this->table} {$where} ORDER BY link_status ASC, link_sort ASC, link_id DESC LIMIT {$pageSize} OFFSET {$offset}",
            $params
        );
        
        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} {$where}",
            $params
        )['cnt'] ?? 0;
        
        return ['list' => $list, 'total' => $total];
    }

    /**
     * 查找单条
     */
    public function find(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE link_id = ?",
            [$id]
        );
    }

    /**
     * 添加
     */
    public function insert(array $data): int
    {
        $data['link_time'] = time();
        
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $this->db->execute(
            "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")",
            array_values($data)
        );
        
        return (int)$this->db->lastInsertId();
    }

    /**
     * 更新
     */
    public function update(int $id, array $data): bool
    {
        $sets = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $sets[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        
        return $this->db->execute(
            "UPDATE {$this->table} SET " . implode(',', $sets) . " WHERE link_id = ?",
            $params
        );
    }

    /**
     * 删除
     */
    public function delete(int $id): bool
    {
        return $this->db->execute(
            "DELETE FROM {$this->table} WHERE link_id = ?",
            [$id]
        );
    }

    /**
     * 检测对方网站是否有回链
     */
    public function checkBacklink(string $targetUrl): bool
    {
        $myUrl = rtrim(SITE_URL, '/');
        $myDomain = parse_url($myUrl, PHP_URL_HOST);
        
        // 获取对方网页内容
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (compatible; BananaCMS LinkChecker/1.0)'
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $html = @file_get_contents($targetUrl, false, $context);
        if ($html === false) {
            return false;
        }
        
        // 检查是否包含我方链接
        // 匹配 href 中包含我方域名的链接
        if (preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $href) {
                if (stripos($href, $myDomain) !== false || stripos($href, $myUrl) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * 批量检测回链状态
     */
    public function batchCheck(): array
    {
        $links = $this->db->query(
            "SELECT link_id, link_url FROM {$this->table} WHERE link_status = 1"
        );
        
        $results = ['success' => 0, 'fail' => 0];
        
        foreach ($links as $link) {
            $hasBacklink = $this->checkBacklink($link['link_url']);
            
            $this->update($link['link_id'], [
                'link_check_time' => time(),
                'link_check_status' => $hasBacklink ? 1 : 2
            ]);
            
            if ($hasBacklink) {
                $results['success']++;
            } else {
                $results['fail']++;
            }
        }
        
        return $results;
    }

    /**
     * 统计各状态数量
     */
    public function getStats(): array
    {
        $result = $this->db->query(
            "SELECT link_status, COUNT(*) as cnt FROM {$this->table} GROUP BY link_status"
        );
        
        $stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
        foreach ($result as $row) {
            if ($row['link_status'] == 0) $stats['pending'] = $row['cnt'];
            if ($row['link_status'] == 1) $stats['approved'] = $row['cnt'];
            if ($row['link_status'] == 2) $stats['rejected'] = $row['cnt'];
        }
        
        return $stats;
    }
}
