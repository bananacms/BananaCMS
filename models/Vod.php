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
     * @param int $num 数量
     * @param string $order 排序方式
     * @param int|null $typeId 分类ID
     * @param bool $includeChildren 是否包含子分类（一级分类时使用 vod_type_id_1 查询）
     */
    public function getList(int $num = 10, string $order = 'time', ?int $typeId = null, bool $includeChildren = true): array
    {
        // 授权校验
        if (!$this->xpk_init()) {
            $num = min($num, 5); // 未授权限制数量
        }

        $orderField = 'vod_id DESC';
        switch ($order) {
            case 'time': $orderField = 'vod_time DESC'; break;
            case 'hits': $orderField = 'vod_hits DESC'; break;
            case 'score': $orderField = 'vod_score DESC'; break;
            case 'up': $orderField = 'vod_up DESC'; break;
            case 'down': $orderField = 'vod_down ASC'; break;
        }

        $sql = "SELECT * FROM {$this->table} WHERE vod_status = 1";
        $params = [];

        if ($typeId) {
            if ($includeChildren) {
                // 检查是否为一级分类（type_pid = 0）
                $typeModel = new XpkType();
                $type = $typeModel->getById($typeId);
                
                if ($type && $type['type_pid'] == 0) {
                    // 一级分类：使用 vod_type_id_1 查询，包含所有子分类的视频
                    $sql .= " AND vod_type_id_1 = ?";
                } else {
                    // 子分类：精确匹配 vod_type_id
                    $sql .= " AND vod_type_id = ?";
                }
            } else {
                // 不包含子分类，精确匹配
                $sql .= " AND vod_type_id = ?";
            }
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
     * 获取热门视频（分页）
     */
    public function getHotPaged(int $page = 1, int $pageSize = 20): array
    {
        if (!$this->xpk_init()) {
            return ['list' => [], 'total' => 0, 'page' => 1, 'pageSize' => $pageSize, 'totalPages' => 0];
        }

        $offset = ($page - 1) * $pageSize;
        
        $sql = "SELECT * FROM {$this->table} WHERE vod_status = 1 ORDER BY vod_hits DESC, vod_time DESC LIMIT {$pageSize} OFFSET {$offset}";
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE vod_status = 1";
        
        $list = $this->db->query($sql);
        $total = $this->db->queryOne($countSql)['total'] ?? 0;
        
        return [
            'list' => $list,
            'total' => (int)$total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize),
        ];
    }

    /**
     * 获取视频列表（分页）
     */
    public function getListPaged(int $page = 1, int $pageSize = 20, string $order = 'time', ?int $typeId = null): array
    {
        if (!$this->xpk_init()) {
            return ['list' => [], 'total' => 0, 'page' => 1, 'pageSize' => $pageSize, 'totalPages' => 0];
        }

        $orderField = 'vod_id DESC';
        switch ($order) {
            case 'time': $orderField = 'vod_time DESC'; break;
            case 'hits': $orderField = 'vod_hits DESC'; break;
            case 'score': $orderField = 'vod_score DESC'; break;
            case 'up': $orderField = 'vod_up DESC'; break;
        }

        $offset = ($page - 1) * $pageSize;
        
        $sql = "SELECT * FROM {$this->table} WHERE vod_status = 1";
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE vod_status = 1";
        $params = [];

        if ($typeId) {
            $sql .= " AND vod_type_id = ?";
            $countSql .= " AND vod_type_id = ?";
            $params[] = $typeId;
        }

        $sql .= " ORDER BY {$orderField} LIMIT {$pageSize} OFFSET {$offset}";
        
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
     * 获取视频详情
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT v.*, t.type_name, t.type_id, t.type_en FROM {$this->table} v 
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
     * 解析播放列表
     * 将 vod_play_from 和 vod_play_url 解析成结构化数据
     * 
     * @param string $playFrom 播放源，多个用$$$分隔
     * @param string $playUrl 播放地址，多个用$$$分隔，每个源内集数用#分隔，集名和地址用$分隔
     * @return array 结构化的播放列表
     */
    public static function parsePlayList(string $playFrom, string $playUrl): array
    {
        if (empty($playFrom) || empty($playUrl)) {
            return [];
        }

        $fromArr = explode('$$$', $playFrom);
        $urlArr = explode('$$$', $playUrl);
        
        $playList = [];
        
        foreach ($fromArr as $index => $from) {
            $from = trim($from);
            if (empty($from)) continue;
            
            $urls = $urlArr[$index] ?? '';
            $episodes = [];
            
            if (!empty($urls)) {
                $items = explode('#', $urls);
                $episodeNum = 1;
                
                foreach ($items as $item) {
                    $item = trim($item);
                    if (empty($item)) continue;
                    
                    if (strpos($item, '$') !== false) {
                        [$name, $url] = explode('$', $item, 2);
                    } else {
                        $name = '第' . $episodeNum . '集';
                        $url = $item;
                    }
                    
                    $episodes[] = [
                        'name' => trim($name),
                        'url' => trim($url),
                        'nid' => $episodeNum
                    ];
                    $episodeNum++;
                }
            }
            
            if (!empty($episodes)) {
                $playList[] = [
                    'from' => $from,
                    'sid' => $index + 1,
                    'episodes' => $episodes,
                    'count' => count($episodes)
                ];
            }
        }
        
        return $playList;
    }

    /**
     * 获取视频详情（包含解析后的播放列表）
     */
    public function getDetailWithPlayList(int $id): ?array
    {
        $vod = $this->getDetail($id);
        if (!$vod) return null;
        
        // 解析播放列表
        $vod['play_list'] = self::parsePlayList(
            $vod['vod_play_from'] ?? '',
            $vod['vod_play_url'] ?? ''
        );
        
        // 解析下载列表
        $vod['down_list'] = self::parsePlayList(
            $vod['vod_down_from'] ?? '',
            $vod['vod_down_url'] ?? ''
        );
        
        return $vod;
    }

    /**
     * 按分类获取视频（包含子分类）
     */
    public function getByType(int $typeId, int $page = 1, int $pageSize = 20): array
    {
        $typeModel = new XpkType();
        $type = $typeModel->getById($typeId);
        
        $offset = ($page - 1) * $pageSize;
        
        // 如果是一级分类（type_pid = 0），检查是否有子分类
        if ($type && $type['type_pid'] == 0) {
            // 检查是否有子分类
            $hasChildren = $typeModel->hasChildren($typeId);
            
            if ($hasChildren) {
                // 有子分类：使用 vod_type_id_1 查询（包含所有子分类的视频）
                $sql = "SELECT * FROM {$this->table} 
                        WHERE vod_status = 1 AND vod_type_id_1 = ?
                        ORDER BY vod_time DESC LIMIT {$pageSize} OFFSET {$offset}";
                
                $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                             WHERE vod_status = 1 AND vod_type_id_1 = ?";
                
                $list = $this->db->query($sql, [$typeId]);
                $total = $this->db->queryOne($countSql, [$typeId])['total'] ?? 0;
            } else {
                // 无子分类：直接用 vod_type_id 查询（扁平分类结构）
                $sql = "SELECT * FROM {$this->table} 
                        WHERE vod_status = 1 AND vod_type_id = ?
                        ORDER BY vod_time DESC LIMIT {$pageSize} OFFSET {$offset}";
                
                $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                             WHERE vod_status = 1 AND vod_type_id = ?";
                
                $list = $this->db->query($sql, [$typeId]);
                $total = $this->db->queryOne($countSql, [$typeId])['total'] ?? 0;
            }
        } else {
            // 子分类：获取该分类及其所有子分类的ID
            $typeIds = $typeModel->getChildIds($typeId);
            $typeIds = array_values($typeIds);
            
            $placeholders = implode(',', array_fill(0, count($typeIds), '?'));
            
            $sql = "SELECT * FROM {$this->table} 
                    WHERE vod_status = 1 AND vod_type_id IN ({$placeholders})
                    ORDER BY vod_time DESC LIMIT {$pageSize} OFFSET {$offset}";
            
            $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                         WHERE vod_status = 1 AND vod_type_id IN ({$placeholders})";
            
            $list = $this->db->query($sql, $typeIds);
            $total = $this->db->queryOne($countSql, $typeIds)['total'] ?? 0;
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

    /**
     * 按一级分类获取视频（分页）
     */
    public function getByTopLevelType(int $topLevelTypeId, int $page = 1, int $pageSize = 20): array
    {
        $offset = ($page - 1) * $pageSize;
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE vod_status = 1 AND vod_type_id_1 = ?
                ORDER BY vod_time DESC LIMIT {$pageSize} OFFSET {$offset}";
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                     WHERE vod_status = 1 AND vod_type_id_1 = ?";
        
        $list = $this->db->query($sql, [$topLevelTypeId]);
        $total = $this->db->queryOne($countSql, [$topLevelTypeId])['total'] ?? 0;
        
        return [
            'list' => $list,
            'total' => (int)$total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize),
        ];
    }

    /**
     * 按首字母筛选视频
     */
    public function getByLetter(string $letter, int $page = 1, int $pageSize = 20): array
    {
        $offset = ($page - 1) * $pageSize;
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE vod_status = 1 AND vod_letter = ?
                ORDER BY vod_time DESC LIMIT {$pageSize} OFFSET {$offset}";
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                     WHERE vod_status = 1 AND vod_letter = ?";
        
        $list = $this->db->query($sql, [$letter]);
        $total = $this->db->queryOne($countSql, [$letter])['total'] ?? 0;
        
        return [
            'list' => $list,
            'total' => (int)$total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize),
        ];
    }

    /**
     * 按完结状态筛选视频
     */
    public function getByEndStatus(int $isEnd, int $page = 1, int $pageSize = 20): array
    {
        $offset = ($page - 1) * $pageSize;
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE vod_status = 1 AND vod_isend = ?
                ORDER BY vod_time DESC LIMIT {$pageSize} OFFSET {$offset}";
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                     WHERE vod_status = 1 AND vod_isend = ?";
        
        $list = $this->db->query($sql, [$isEnd]);
        $total = $this->db->queryOne($countSql, [$isEnd])['total'] ?? 0;
        
        return [
            'list' => $list,
            'total' => (int)$total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize),
        ];
    }
}
