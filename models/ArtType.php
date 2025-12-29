<?php
/**
 * 文章分类模型
 * Powered by https://xpornkit.com
 */

class XpkArtType extends XpkModel
{
    protected string $table = DB_PREFIX . 'art_type';
    protected string $pk = 'type_id';

    /**
     * 获取分类列表
     */
    public function getList(int $pid = 0): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE type_pid = ? AND type_status = 1 ORDER BY type_sort ASC, type_id ASC",
            [$pid]
        );
    }

    /**
     * 获取所有分类
     */
    public function getAll(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY type_sort ASC, type_id ASC"
        );
    }

    /**
     * 根据ID获取分类
     */
    public function getById(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE type_id = ?",
            [$id]
        );
    }

    /**
     * 获取导航分类
     */
    public function getNav(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE type_pid = 0 AND type_status = 1 ORDER BY type_sort ASC LIMIT 10"
        );
    }
}
