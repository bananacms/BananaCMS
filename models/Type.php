<?php
/**
 * 分类模型
 * Powered by https://xpornkit.com
 */

class XpkType extends XpkModel
{
    protected string $table = DB_PREFIX . 'type';
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
     * 获取所有分类（树形，带层级）
     */
    public function getTree(): array
    {
        $all = $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY type_sort ASC, type_id ASC"
        );

        return $this->buildFlatTree($all);
    }

    /**
     * 构建扁平树形结构（带层级）
     */
    private function buildFlatTree(array $items, int $pid = 0, int $level = 0): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['type_pid'] == $pid) {
                $item['level'] = $level;
                $tree[] = $item;
                $children = $this->buildFlatTree($items, $item['type_id'], $level + 1);
                $tree = array_merge($tree, $children);
            }
        }
        return $tree;
    }

    /**
     * 获取所有分类
     */
    public function getAll(array $where = []): array
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

        $sql .= ' ORDER BY type_sort ASC, type_id ASC';
        return $this->db->query($sql, $params);
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
     * 通过 slug 查找分类（使用 type_en 字段）
     */
    public function findBySlug(string $slug): ?array
    {
        $type = $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE type_en = ? AND type_status = 1",
            [$slug]
        );
        
        if (!$type && is_numeric($slug)) {
            $type = $this->getById((int)$slug);
        }
        
        return $type;
    }

    /**
     * 获取分类及其子分类ID
     */
    public function getChildIds(int $pid): array
    {
        $ids = [$pid];
        $children = $this->db->query(
            "SELECT type_id FROM {$this->table} WHERE type_pid = ? AND type_status = 1",
            [$pid]
        );
        foreach ($children as $child) {
            $ids = array_merge($ids, $this->getChildIds($child['type_id']));
        }
        return $ids;
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
