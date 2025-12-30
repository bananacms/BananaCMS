<?php
/**
 * 演员模型
 * Powered by https://xpornkit.com
 */

class XpkActor extends XpkModel
{
    protected string $table = DB_PREFIX . 'actor';
    protected string $pk = 'actor_id';

    /**
     * 获取演员列表
     */
    public function getList(int $num = 10, string $order = 'id'): array
    {
        $orderField = match($order) {
            'hits' => 'actor_hits DESC',
            'time' => 'actor_time DESC',
            'name' => 'actor_name ASC',
            default => 'actor_id DESC',
        };

        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE actor_status = 1 ORDER BY {$orderField} LIMIT ?",
            [$num]
        );
    }

    /**
     * 获取演员详情
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE actor_id = ? AND actor_status = 1",
            [$id]
        );
    }

    /**
     * 搜索演员
     */
    public function search(string $keyword, int $page = 1, int $pageSize = 20): array
    {
        $offset = ($page - 1) * $pageSize;
        $keyword = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $keyword) . '%';

        $sql = "SELECT * FROM {$this->table} 
                WHERE actor_status = 1 AND actor_name LIKE ?
                ORDER BY actor_hits DESC LIMIT {$pageSize} OFFSET {$offset}";
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                     WHERE actor_status = 1 AND actor_name LIKE ?";

        $list = $this->db->query($sql, [$keyword]);
        $total = $this->db->queryOne($countSql, [$keyword])['total'] ?? 0;

        return [
            'list' => $list,
            'total' => (int)$total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize),
        ];
    }

    /**
     * 通过 slug 查找演员
     */
    public function findBySlug(string $slug): ?array
    {
        $actor = $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE actor_slug = ? AND actor_status = 1",
            [$slug]
        );
        
        if (!$actor && is_numeric($slug)) {
            $actor = $this->find((int)$slug);
        }
        
        return $actor;
    }

    /**
     * 增加点击量
     */
    public function incHits(int $id): void
    {
        $this->db->execute(
            "UPDATE {$this->table} SET actor_hits = actor_hits + 1 WHERE actor_id = ?",
            [$id]
        );
    }

    /**
     * 获取热门演员
     */
    public function getHot(int $num = 10): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE actor_status = 1 ORDER BY actor_hits DESC LIMIT ?",
            [$num]
        );
    }
}
