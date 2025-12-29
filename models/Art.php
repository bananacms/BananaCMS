<?php
/**
 * 文章模型
 * Powered by https://xpornkit.com
 */

class XpkArt extends XpkModel
{
    protected string $table = DB_PREFIX . 'art';
    protected string $pk = 'art_id';

    /**
     * 获取文章列表
     */
    public function getList(int $num = 10, string $order = 'time'): array
    {
        $orderField = match($order) {
            'hits' => 'art_hits DESC',
            'time' => 'art_time DESC',
            default => 'art_id DESC',
        };

        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE art_status = 1 ORDER BY {$orderField} LIMIT ?",
            [$num]
        );
    }

    /**
     * 获取文章详情
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT a.*, t.type_name FROM {$this->table} a 
             LEFT JOIN " . DB_PREFIX . "art_type t ON a.art_type_id = t.type_id 
             WHERE a.art_id = ? AND a.art_status = 1",
            [$id]
        );
    }

    /**
     * 通过 slug 查找文章
     */
    public function findBySlug(string $slug): ?array
    {
        $art = $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE art_slug = ? AND art_status = 1",
            [$slug]
        );
        
        if (!$art && is_numeric($slug)) {
            $art = $this->find((int)$slug);
        }
        
        return $art;
    }

    /**
     * 按分类获取文章
     */
    public function getByType(int $typeId, int $page = 1, int $pageSize = 20): array
    {
        return $this->paginate($page, $pageSize, ['art_type_id' => $typeId, 'art_status' => 1], 'art_time DESC');
    }

    /**
     * 增加点击量
     */
    public function incHits(int $id): void
    {
        $this->db->execute(
            "UPDATE {$this->table} SET art_hits = art_hits + 1 WHERE art_id = ?",
            [$id]
        );
    }
}
