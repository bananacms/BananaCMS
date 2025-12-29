<?php
/**
 * 视频模型
 * Powered by https://xpornkit.com
 */

class XpkVod extends XpkModel
{
    protected string $table = DB_PREFIX . 'vod';
    protected string $pk = 'vod_id';

    /**
     * 获取视频列表
     */
    public function getList(int $num = 10, string $order = 'time', ?int $typeId = null): array
    {
        // 授权校验
        if (!$this->xpk_init()) {
            $num = min($num, 5); // 未授权限制数量
        }

        $orderField = match($order) {
            'time' => 'vod_time DESC',
            'hits' => 'vod_hits DESC',
            'score' => 'vod_score DESC',
            'up' => 'vod_up DESC',
            'down' => 'vod_down ASC',
            default => 'vod_id DESC',
        };

        $sql = "SELECT * FROM {$this->table} WHERE vod_status = 1";
        $params = [];

        if ($typeId) {
            $sql .= " AND vod_type_id = ?";
            $params[] = $typeId;
        }

        $sql .= " ORDER BY {$orderField} LIMIT {$num}";

        return $this->db->query($sql, $params);
    }

    /**
     * 获取热门视频
     */
    public function getHot(int $num = 10): array
    {
        if (!$this->xpk_init()) {
            $num = min($num, 5);
        }

        // 按点击量排序（包括0点击的）
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE vod_status = 1 ORDER BY vod_hits DESC, vod_time DESC LIMIT ?",
            [$num]
        );
    }

    /**
     * 获取视频详情
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT v.*, t.type_name FROM {$this->table} v 
             LEFT JOIN " . DB_PREFIX . "type t ON v.vod_type_id = t.type_id 
             WHERE v.vod_id = ? AND v.vod_status = 1",
            [$id]
        );
    }

    /**
     * 通过 slug 查找视频
     */
    public function findBySlug(string $slug): ?array
    {
        // 先尝试作为 slug 查找
        $vod = $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE vod_slug = ? AND vod_status = 1",
            [$slug]
        );
        
        // 如果没找到，尝试作为 ID 查找（兼容数字 slug）
        if (!$vod && is_numeric($slug)) {
            $vod = $this->find((int)$slug);
        }
        
        return $vod;
    }

    /**
     * 搜索视频（优先全文搜索，降级LIKE）
     */
    public function search(string $keyword, int $page = 1, int $pageSize = 20): array
    {
        if (!$this->xpk_init()) {
            return ['list' => [], 'total' => 0, 'page' => 1, 'pageSize' => $pageSize, 'totalPages' => 0];
        }

        $offset = ($page - 1) * $pageSize;
        
        // 尝试全文搜索
        try {
            $sql = "SELECT *, MATCH(vod_name, vod_sub, vod_actor) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance 
                    FROM {$this->table} 
                    WHERE vod_status = 1 AND MATCH(vod_name, vod_sub, vod_actor) AGAINST(? IN NATURAL LANGUAGE MODE)
                    ORDER BY relevance DESC, vod_time DESC LIMIT {$pageSize} OFFSET {$offset}";
            
            $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                         WHERE vod_status = 1 AND MATCH(vod_name, vod_sub, vod_actor) AGAINST(? IN NATURAL LANGUAGE MODE)";

            $list = $this->db->query($sql, [$keyword, $keyword]);
            $total = $this->db->queryOne($countSql, [$keyword])['total'] ?? 0;
            
            // 全文搜索无结果时降级到LIKE
            if (empty($list)) {
                return $this->searchByLike($keyword, $page, $pageSize);
            }
        } catch (\Exception $e) {
            // 全文索引不存在时降级到LIKE
            return $this->searchByLike($keyword, $page, $pageSize);
        }

        return [
            'list' => $list,
            'total' => (int)$total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize),
        ];
    }

    /**
     * LIKE搜索（降级方案）
     */
    private function searchByLike(string $keyword, int $page, int $pageSize): array
    {
        $offset = ($page - 1) * $pageSize;
        $keyword = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $keyword) . '%';

        $sql = "SELECT * FROM {$this->table} 
                WHERE vod_status = 1 AND (vod_name LIKE ? OR vod_sub LIKE ? OR vod_actor LIKE ?)
                ORDER BY vod_time DESC LIMIT {$pageSize} OFFSET {$offset}";
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                     WHERE vod_status = 1 AND (vod_name LIKE ? OR vod_sub LIKE ? OR vod_actor LIKE ?)";

        $params = [$keyword, $keyword, $keyword];
        
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
     * 增加点击量
     */
    public function incHits(int $id): void
    {
        $this->db->execute(
            "UPDATE {$this->table} SET vod_hits = vod_hits + 1 WHERE vod_id = ?",
            [$id]
        );
    }

    /**
     * 获取相关视频
     */
    public function getRelated(int $typeId, int $excludeId, int $num = 6): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE vod_status = 1 AND vod_type_id = ? AND vod_id != ?
             ORDER BY vod_hits DESC LIMIT ?",
            [$typeId, $excludeId, $num]
        );
    }

    /**
     * 按分类获取视频
     */
    public function getByType(int $typeId, int $page = 1, int $pageSize = 20): array
    {
        return $this->paginate($page, $pageSize, ['vod_type_id' => $typeId, 'vod_status' => 1], 'vod_time DESC');
    }

    /**
     * 按演员获取视频
     */
    public function getByActor(string $actorName, int $num = 12): array
    {
        if (empty($actorName)) {
            return [];
        }
        $keyword = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $actorName) . '%';
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE vod_status = 1 AND vod_actor LIKE ? ORDER BY vod_time DESC LIMIT ?",
            [$keyword, $num]
        );
    }
}
