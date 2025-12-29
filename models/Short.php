<?php
/**
 * 短视频/短剧模型
 * Powered by https://xpornkit.com
 */

class XpkShort
{
    private XpkDatabase $db;
    private string $table;
    private string $episodeTable;

    // 类型
    const TYPE_VIDEO = 'video';  // 短视频（单集）
    const TYPE_DRAMA = 'drama';  // 短剧（多集）

    public function __construct()
    {
        $this->db = XpkDatabase::getInstance();
        $this->table = DB_PREFIX . 'short';
        $this->episodeTable = DB_PREFIX . 'short_episode';
    }

    /**
     * 获取短视频列表（前台，瀑布流/滑动）
     */
    public function getVideos(int $page = 1, int $pageSize = 10, int $categoryId = 0): array
    {
        $where = 'short_type = ? AND short_status = 1';
        $params = [self::TYPE_VIDEO];

        if ($categoryId > 0) {
            $where .= ' AND category_id = ?';
            $params[] = $categoryId;
        }

        $offset = ($page - 1) * $pageSize;

        $list = $this->db->query(
            "SELECT * FROM {$this->table} WHERE {$where} ORDER BY short_id DESC LIMIT {$pageSize} OFFSET {$offset}",
            $params
        );

        return $list;
    }

    /**
     * 获取短剧列表（前台）
     */
    public function getDramas(int $page = 1, int $pageSize = 20, int $categoryId = 0): array
    {
        $where = 'short_type = ? AND short_status = 1';
        $params = [self::TYPE_DRAMA];

        if ($categoryId > 0) {
            $where .= ' AND category_id = ?';
            $params[] = $categoryId;
        }

        $offset = ($page - 1) * $pageSize;

        $list = $this->db->query(
            "SELECT s.*, (SELECT COUNT(*) FROM {$this->episodeTable} WHERE short_id = s.short_id) as episode_count
             FROM {$this->table} s WHERE {$where} ORDER BY short_time DESC LIMIT {$pageSize} OFFSET {$offset}",
            $params
        );

        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} WHERE {$where}",
            $params
        )['cnt'] ?? 0;

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 获取随机短视频（滑动播放用）
     */
    public function getRandom(int $limit = 10, int $excludeId = 0): array
    {
        $where = 'short_type = ? AND short_status = 1';
        $params = [self::TYPE_VIDEO];

        if ($excludeId > 0) {
            $where .= ' AND short_id != ?';
            $params[] = $excludeId;
        }

        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE {$where} ORDER BY RAND() LIMIT {$limit}",
            $params
        );
    }

    /**
     * 获取详情
     */
    public function getDetail(int $id): ?array
    {
        $short = $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE short_id = ?",
            [$id]
        );

        if (!$short) return null;

        // 如果是短剧，获取剧集列表
        if ($short['short_type'] === self::TYPE_DRAMA) {
            $short['episodes'] = $this->getEpisodes($id);
        }

        return $short;
    }

    /**
     * 获取剧集列表
     */
    public function getEpisodes(int $shortId): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->episodeTable} WHERE short_id = ? ORDER BY episode_sort ASC, episode_id ASC",
            [$shortId]
        );
    }

    /**
     * 获取单集详情
     */
    public function getEpisode(int $episodeId): ?array
    {
        return $this->db->queryOne(
            "SELECT e.*, s.short_name, s.short_pic 
             FROM {$this->episodeTable} e
             LEFT JOIN {$this->table} s ON e.short_id = s.short_id
             WHERE e.episode_id = ?",
            [$episodeId]
        );
    }

    /**
     * 增加播放量
     */
    public function incHits(int $id): void
    {
        $this->db->execute(
            "UPDATE {$this->table} SET short_hits = short_hits + 1 WHERE short_id = ?",
            [$id]
        );
    }

    /**
     * 增加剧集播放量
     */
    public function incEpisodeHits(int $episodeId): void
    {
        $this->db->execute(
            "UPDATE {$this->episodeTable} SET episode_hits = episode_hits + 1 WHERE episode_id = ?",
            [$episodeId]
        );
    }

    /**
     * 点赞
     */
    public function like(int $id): int
    {
        $this->db->execute(
            "UPDATE {$this->table} SET short_likes = short_likes + 1 WHERE short_id = ?",
            [$id]
        );
        $short = $this->find($id);
        return $short['short_likes'] ?? 0;
    }

    // ========== 后台方法 ==========

    /**
     * 获取列表（后台）
     */
    public function getList(string $type = '', int $status = -1, int $page = 1, int $pageSize = 20): array
    {
        $where = '1=1';
        $params = [];

        if ($type) {
            $where .= ' AND short_type = ?';
            $params[] = $type;
        }

        if ($status >= 0) {
            $where .= ' AND short_status = ?';
            $params[] = $status;
        }

        $offset = ($page - 1) * $pageSize;

        $list = $this->db->query(
            "SELECT s.*, (SELECT COUNT(*) FROM {$this->episodeTable} WHERE short_id = s.short_id) as episode_count
             FROM {$this->table} s WHERE {$where} ORDER BY short_id DESC LIMIT {$pageSize} OFFSET {$offset}",
            $params
        );

        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} WHERE {$where}",
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
            "SELECT * FROM {$this->table} WHERE short_id = ?",
            [$id]
        );
    }

    /**
     * 添加
     */
    public function insert(array $data): int
    {
        $data['short_time'] = time();
        $data['short_time_add'] = time();

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
        $data['short_time'] = time();

        $sets = [];
        $params = [];

        foreach ($data as $key => $value) {
            $sets[] = "{$key} = ?";
            $params[] = $value;
        }

        $params[] = $id;

        return $this->db->execute(
            "UPDATE {$this->table} SET " . implode(',', $sets) . " WHERE short_id = ?",
            $params
        );
    }

    /**
     * 删除
     */
    public function delete(int $id): bool
    {
        // 删除剧集
        $this->db->execute("DELETE FROM {$this->episodeTable} WHERE short_id = ?", [$id]);
        // 删除主记录
        return $this->db->execute("DELETE FROM {$this->table} WHERE short_id = ?", [$id]);
    }

    // ========== 剧集管理 ==========

    /**
     * 添加剧集
     */
    public function addEpisode(array $data): int
    {
        $data['episode_time'] = time();

        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $this->db->execute(
            "INSERT INTO {$this->episodeTable} (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")",
            array_values($data)
        );

        return (int)$this->db->lastInsertId();
    }

    /**
     * 更新剧集
     */
    public function updateEpisode(int $id, array $data): bool
    {
        $sets = [];
        $params = [];

        foreach ($data as $key => $value) {
            $sets[] = "{$key} = ?";
            $params[] = $value;
        }

        $params[] = $id;

        return $this->db->execute(
            "UPDATE {$this->episodeTable} SET " . implode(',', $sets) . " WHERE episode_id = ?",
            $params
        );
    }

    /**
     * 删除剧集
     */
    public function deleteEpisode(int $id): bool
    {
        return $this->db->execute("DELETE FROM {$this->episodeTable} WHERE episode_id = ?", [$id]);
    }

    /**
     * 统计
     */
    public function getStats(): array
    {
        $videos = $this->db->queryOne("SELECT COUNT(*) as cnt FROM {$this->table} WHERE short_type = 'video'")['cnt'] ?? 0;
        $dramas = $this->db->queryOne("SELECT COUNT(*) as cnt FROM {$this->table} WHERE short_type = 'drama'")['cnt'] ?? 0;
        $episodes = $this->db->queryOne("SELECT COUNT(*) as cnt FROM {$this->episodeTable}")['cnt'] ?? 0;

        return [
            'videos' => $videos,
            'dramas' => $dramas,
            'episodes' => $episodes,
            'total' => $videos + $dramas
        ];
    }
}
