<?php
/**
 * 广告模型
 * Powered by https://xpornkit.com
 */

class XpkAd
{
    private XpkDatabase $db;
    private string $table;

    // 广告位置常量
    const POS_HOME_TOP = 'home_top';           // 首页顶部
    const POS_HOME_FLOAT = 'home_float';       // 首页悬浮
    const POS_DETAIL_TOP = 'detail_top';       // 详情页顶部
    const POS_DETAIL_BOTTOM = 'detail_bottom'; // 详情页底部
    const POS_PLAY_PAUSE = 'play_pause';       // 播放暂停
    const POS_PLAY_BEFORE = 'play_before';     // 片头广告
    const POS_SIDEBAR = 'sidebar';             // 侧边栏
    const POS_LIST_INSERT = 'list_insert';     // 列表页插入
    const POS_POPUP = 'popup';                 // 弹窗广告
    const POS_CUSTOM = 'custom';               // 自定义位置

    // 广告类型
    const TYPE_IMAGE = 'image';     // 图片广告
    const TYPE_CODE = 'code';       // 代码广告
    const TYPE_VIDEO = 'video';     // 视频广告
    const TYPE_TEXT = 'text';       // 文字广告

    public function __construct()
    {
        $this->db = XpkDatabase::getInstance();
        $this->table = DB_PREFIX . 'ad';
    }

    /**
     * 获取广告位置列表
     */
    public static function getPositions(): array
    {
        return [
            self::POS_HOME_TOP => '首页顶部横幅',
            self::POS_HOME_FLOAT => '首页悬浮广告',
            self::POS_DETAIL_TOP => '详情页顶部',
            self::POS_DETAIL_BOTTOM => '详情页底部',
            self::POS_PLAY_PAUSE => '播放器暂停广告',
            self::POS_PLAY_BEFORE => '片头广告',
            self::POS_SIDEBAR => '侧边栏广告',
            self::POS_LIST_INSERT => '列表页插入广告',
            self::POS_POPUP => '弹窗广告',
            self::POS_CUSTOM => '自定义位置',
        ];
    }

    /**
     * 获取广告类型列表
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_IMAGE => '图片广告',
            self::TYPE_CODE => '代码广告',
            self::TYPE_VIDEO => '视频广告',
            self::TYPE_TEXT => '文字广告',
        ];
    }

    /**
     * 获取指定位置的广告（前台调用）
     */
    public function getByPosition(string $position): array
    {
        $now = time();
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE ad_position = ? AND ad_status = 1 
             AND (ad_start_time = 0 OR ad_start_time <= ?) 
             AND (ad_end_time = 0 OR ad_end_time >= ?)
             ORDER BY ad_sort ASC, ad_id ASC",
            [$position, $now, $now]
        );
    }

    /**
     * 获取单个广告（随机或第一个）
     */
    public function getOne(string $position, bool $random = false): ?array
    {
        $ads = $this->getByPosition($position);
        if (empty($ads)) {
            return null;
        }
        
        if ($random) {
            return $ads[array_rand($ads)];
        }
        
        return $ads[0];
    }

    /**
     * 渲染广告HTML
     */
    public function render(string $position, bool $random = false): string
    {
        $ad = $this->getOne($position, $random);
        if (!$ad) {
            return '';
        }

        // 记录展示次数
        $this->incrementShow($ad['ad_id']);

        return $this->buildHtml($ad);
    }

    /**
     * 渲染多个广告
     */
    public function renderAll(string $position): string
    {
        $ads = $this->getByPosition($position);
        if (empty($ads)) {
            return '';
        }

        $html = '';
        foreach ($ads as $ad) {
            $this->incrementShow($ad['ad_id']);
            $html .= $this->buildHtml($ad);
        }
        
        return $html;
    }

    /**
     * 构建广告HTML
     */
    private function buildHtml(array $ad): string
    {
        $type = $ad['ad_type'];
        $id = $ad['ad_id'];
        
        // 代码广告直接输出
        if ($type === self::TYPE_CODE) {
            return '<div class="xpk-ad xpk-ad-code" data-id="' . $id . '">' . $ad['ad_code'] . '</div>';
        }

        // 文字广告
        if ($type === self::TYPE_TEXT) {
            $link = $ad['ad_link'] ? ' href="' . htmlspecialchars($ad['ad_link']) . '" target="_blank" onclick="xpkAdClick(' . $id . ')"' : '';
            $tag = $ad['ad_link'] ? 'a' : 'span';
            return '<div class="xpk-ad xpk-ad-text" data-id="' . $id . '"><' . $tag . $link . '>' . htmlspecialchars($ad['ad_title']) . '</' . $tag . '></div>';
        }

        // 图片广告
        if ($type === self::TYPE_IMAGE) {
            $img = '<img src="' . htmlspecialchars($ad['ad_image']) . '" alt="' . htmlspecialchars($ad['ad_title']) . '" class="xpk-ad-img">';
            if ($ad['ad_link']) {
                return '<div class="xpk-ad xpk-ad-image" data-id="' . $id . '"><a href="' . htmlspecialchars($ad['ad_link']) . '" target="_blank" onclick="xpkAdClick(' . $id . ')">' . $img . '</a></div>';
            }
            return '<div class="xpk-ad xpk-ad-image" data-id="' . $id . '">' . $img . '</div>';
        }

        // 视频广告
        if ($type === self::TYPE_VIDEO) {
            return '<div class="xpk-ad xpk-ad-video" data-id="' . $id . '">
                <video src="' . htmlspecialchars($ad['ad_video']) . '" 
                       autoplay muted playsinline 
                       data-duration="' . (int)$ad['ad_duration'] . '"
                       data-skip="' . (int)$ad['ad_skip_time'] . '"
                       data-link="' . htmlspecialchars($ad['ad_link']) . '"
                       onclick="xpkAdClick(' . $id . ')">
                </video>
                <span class="xpk-ad-skip" style="display:none;">跳过广告</span>
            </div>';
        }

        return '';
    }

    /**
     * 增加展示次数
     */
    public function incrementShow(int $id): void
    {
        $this->db->execute(
            "UPDATE {$this->table} SET ad_shows = ad_shows + 1 WHERE ad_id = ?",
            [$id]
        );
    }

    /**
     * 增加点击次数
     */
    public function incrementClick(int $id): void
    {
        $this->db->execute(
            "UPDATE {$this->table} SET ad_clicks = ad_clicks + 1 WHERE ad_id = ?",
            [$id]
        );
    }

    /**
     * 获取列表（后台）
     */
    public function getList(string $position = '', int $page = 1, int $pageSize = 20): array
    {
        $where = '1=1';
        $params = [];
        
        if ($position) {
            $where .= ' AND ad_position = ?';
            $params[] = $position;
        }
        
        $offset = ($page - 1) * $pageSize;
        
        $list = $this->db->query(
            "SELECT * FROM {$this->table} WHERE {$where} ORDER BY ad_position ASC, ad_sort ASC, ad_id DESC LIMIT {$pageSize} OFFSET {$offset}",
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
            "SELECT * FROM {$this->table} WHERE ad_id = ?",
            [$id]
        );
    }

    /**
     * 添加
     */
    public function insert(array $data): int
    {
        $data['ad_time'] = time();
        
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
            "UPDATE {$this->table} SET " . implode(',', $sets) . " WHERE ad_id = ?",
            $params
        );
    }

    /**
     * 删除
     */
    public function delete(int $id): bool
    {
        return $this->db->execute(
            "DELETE FROM {$this->table} WHERE ad_id = ?",
            [$id]
        );
    }

    /**
     * 统计数据
     */
    public function getStats(): array
    {
        $total = $this->db->queryOne("SELECT COUNT(*) as cnt FROM {$this->table}")['cnt'] ?? 0;
        $active = $this->db->queryOne("SELECT COUNT(*) as cnt FROM {$this->table} WHERE ad_status = 1")['cnt'] ?? 0;
        $shows = $this->db->queryOne("SELECT SUM(ad_shows) as cnt FROM {$this->table}")['cnt'] ?? 0;
        $clicks = $this->db->queryOne("SELECT SUM(ad_clicks) as cnt FROM {$this->table}")['cnt'] ?? 0;
        
        return [
            'total' => $total,
            'active' => $active,
            'shows' => $shows,
            'clicks' => $clicks,
            'ctr' => $shows > 0 ? round($clicks / $shows * 100, 2) : 0
        ];
    }
}
