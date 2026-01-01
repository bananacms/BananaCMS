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
     * 优先返回顶级分类（type_pid = 0），且分类名称与slug相关
     */
    public function findBySlug(string $slug): ?array
    {
        // 先尝试精确匹配顶级分类
        $type = $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE type_en = ? AND type_pid = 0 AND type_status = 1 LIMIT 1",
            [$slug]
        );
        
        // 如果没有顶级分类，再查找子分类
        if (!$type) {
            $type = $this->db->queryOne(
                "SELECT * FROM {$this->table} WHERE type_en = ? AND type_status = 1 ORDER BY type_pid ASC, type_id ASC LIMIT 1",
                [$slug]
            );
        }
        
        // 兼容数字ID
        if (!$type && is_numeric($slug)) {
            $type = $this->getById((int)$slug);
        }
        
        return $type;
    }

    /**
     * 获取分类及其子分类ID
     * 优先使用父子关系，如果没有子分类则尝试名称匹配
     */
    public function getChildIds(int $pid): array
    {
        $ids = [$pid];
        
        // 先查找直接子分类
        $children = $this->db->query(
            "SELECT type_id FROM {$this->table} WHERE type_pid = ? AND type_status = 1",
            [$pid]
        );
        
        foreach ($children as $child) {
            $ids = array_merge($ids, $this->getChildIds($child['type_id']));
        }
        
        // 如果有子分类，直接返回
        if (count($ids) > 1) {
            return array_values(array_unique($ids));
        }
        
        // 没有子分类时，检查当前分类是否有视频
        $hasVideos = $this->db->queryOne(
            "SELECT 1 FROM " . DB_PREFIX . "vod WHERE vod_type_id = ? AND vod_status = 1 LIMIT 1",
            [$pid]
        );
        
        // 如果当前分类有视频，直接返回
        if ($hasVideos) {
            return $ids;
        }
        
        // 没有子分类也没有视频，尝试根据分类名称匹配相关分类
        $type = $this->getById($pid);
        if ($type) {
            $relatedIds = $this->getRelatedTypeIds($type['type_name']);
            if (!empty($relatedIds)) {
                $ids = array_merge($ids, $relatedIds);
            }
        }
        
        return array_values(array_unique($ids));
    }

    /**
     * 根据分类名称获取相关分类ID
     * 用于扁平分类结构时，根据父分类名称匹配子分类
     */
    private function getRelatedTypeIds(string $typeName): array
    {
        // 定义分类映射关系（父分类 => 子分类名称）
        // 整合自多个采集资源站的分类结构
        $categoryMap = [
            // 电影类
            '电影' => ['动作片', '喜剧片', '爱情片', '科幻片', '恐怖片', '剧情片', '战争片', '纪录片', '记录片', '动漫电影', '伦理片', '理论片', '动画片', '预告片'],
            '电影片' => ['动作片', '喜剧片', '爱情片', '科幻片', '恐怖片', '剧情片', '战争片', '纪录片', '记录片', '动漫电影', '伦理片', '理论片', '动画片', '预告片'],
            
            // 电视剧类
            '连续剧' => ['国产剧', '大陆剧', '港澳剧', '香港剧', '日剧', '日本剧', '欧美剧', '台湾剧', '泰剧', '泰国剧', '韩剧', '韩国剧', '海外剧', '短剧'],
            '电视剧' => ['国产剧', '大陆剧', '港澳剧', '香港剧', '日剧', '日本剧', '欧美剧', '台湾剧', '泰剧', '泰国剧', '韩剧', '韩国剧', '海外剧', '短剧'],
            
            // 综艺类
            '综艺' => ['大陆综艺', '港台综艺', '日韩综艺', '欧美综艺'],
            '综艺片' => ['大陆综艺', '港台综艺', '日韩综艺', '欧美综艺'],
            
            // 动漫类
            '动漫' => ['国产动漫', '日韩动漫', '欧美动漫', '港台动漫', '海外动漫', '动漫电影'],
            '动漫片' => ['国产动漫', '日韩动漫', '欧美动漫', '港台动漫', '海外动漫', '动漫电影'],
            
            // 体育类
            '体育赛事' => ['足球', '篮球', '网球', '斯诺克'],
            '体育' => ['足球', '篮球', '网球', '斯诺克'],
            
            // 短剧类
            '短剧' => ['重生民国', '穿越年代', '现代言情', '反转爽文', '女恋总裁', '闪婚离婚', '都市脑洞', '古装仙侠'],
            '短剧大全' => ['重生民国', '穿越年代', '现代言情', '反转爽文', '女恋总裁', '闪婚离婚', '都市脑洞', '古装仙侠'],
        ];
        
        if (!isset($categoryMap[$typeName])) {
            return [];
        }
        
        $relatedNames = $categoryMap[$typeName];
        $placeholders = implode(',', array_fill(0, count($relatedNames), '?'));
        
        $types = $this->db->query(
            "SELECT type_id FROM {$this->table} WHERE type_name IN ({$placeholders}) AND type_status = 1",
            $relatedNames
        );
        
        return array_column($types, 'type_id');
    }

    /**
     * 获取导航分类
     * @param int $limit 显示数量，0表示不限制
     */
    public function getNav(int $limit = 10): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE type_pid = 0 AND type_status = 1 ORDER BY type_sort ASC";
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
        return $this->db->query($sql);
    }

    /**
     * 获取分类的一级分类ID
     * 如果本身就是一级分类（pid=0），返回自身ID
     * 如果是子分类，返回其父分类ID
     */
    public function getTopLevelId(int $typeId): int
    {
        $type = $this->getById($typeId);
        if (!$type) return 0;
        
        // 如果是一级分类，返回自身ID
        if ($type['type_pid'] == 0) {
            return $typeId;
        }
        
        // 如果是子分类，返回父分类ID
        return (int)$type['type_pid'];
    }
}
