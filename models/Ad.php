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
            self::POS_HOME_FLOAT => '全站悬浮广告',
            self::POS_DETAIL_TOP => '详情页顶部',
            self::POS_DETAIL_BOTTOM => '详情页底部',
            self::POS_PLAY_PAUSE => '播放器暂停广告（需自建播放器）',
            self::POS_PLAY_BEFORE => '片头广告',
            self::POS_SIDEBAR => '侧边栏广告',
            self::POS_LIST_INSERT => '列表页插入广告',
            self::POS_POPUP => '全站弹窗广告（每天一次）',
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
        // 安全检查
        if (!empty($ad['ad_image'])) {
            $validation = $this->validateAdUrl($ad['ad_image']);
            if (!$validation['valid']) {
                error_log("广告图片URL安全检查失败: " . $validation['error'] . " URL: " . $ad['ad_image']);
                return '<!-- 广告内容被安全策略阻止 -->';
            }
        }
        
        if (!empty($ad['ad_video'])) {
            $validation = $this->validateAdUrl($ad['ad_video']);
            if (!$validation['valid']) {
                error_log("广告视频URL安全检查失败: " . $validation['error'] . " URL: " . $ad['ad_video']);
                return '<!-- 广告内容被安全策略阻止 -->';
            }
        }
        
        if (!empty($ad['ad_link'])) {
            $validation = $this->validateAdUrl($ad['ad_link']);
            if (!$validation['valid']) {
                error_log("广告链接URL安全检查失败: " . $validation['error'] . " URL: " . $ad['ad_link']);
                $ad['ad_link'] = ''; // 移除不安全的链接
            }
        }
        $type = $ad['ad_type'];
        $id = $ad['ad_id'];
        $position = $ad['ad_position'];
        
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
            $linkHtml = $ad['ad_link'] 
                ? '<a href="' . htmlspecialchars($ad['ad_link']) . '" target="_blank" rel="nofollow" onclick="xpkAdClick(' . $id . ')">' . $img . '</a>'
                : $img;
            
            // 悬浮广告特殊处理
            if ($position === self::POS_HOME_FLOAT) {
                return '<div class="xpk-ad-float right" data-id="' . $id . '" id="floatAd' . $id . '">
                    <span class="xpk-ad-close" onclick="this.parentElement.style.display=\'none\'">&times;</span>
                    ' . $linkHtml . '
                </div>';
            }
            
            // 弹窗广告特殊处理
            if ($position === self::POS_POPUP) {
                return '<div class="xpk-ad-popup" data-id="' . $id . '" id="popupAd' . $id . '">
                    <div class="xpk-ad-popup-content">
                        <span class="xpk-ad-popup-close" onclick="this.parentElement.parentElement.style.display=\'none\'">&times;</span>
                        ' . $linkHtml . '
                    </div>
                </div>
                <script>
                // 弹窗广告只显示一次（每天）
                (function(){
                    var key = "popup_ad_' . $id . '_" + new Date().toDateString();
                    if (xpkGetCookie && xpkGetCookie(key)) {
                        document.getElementById("popupAd' . $id . '").style.display = "none";
                    } else {
                        if (xpkSetCookie) {
                            xpkSetCookie(key, "1", 1); // 1天过期
                        }
                    }
                })();
                </script>';
            }
            
            return '<div class="xpk-ad xpk-ad-image" data-id="' . $id . '">' . $linkHtml . '</div>';
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

    /**
     * 验证广告URL安全性
     */
    public function validateAdUrl(string $url): array
    {
        $result = ['valid' => false, 'error' => ''];
        
        // 获取安全配置
        $config = $this->getSecurityConfig();
        
        if (!$config['ad_url_check']) {
            return ['valid' => true, 'error' => ''];
        }
        
        // URL格式验证
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ['valid' => false, 'error' => 'URL格式无效'];
        }
        
        // URL长度检查
        if (strlen($url) > $config['ad_max_url_length']) {
            return ['valid' => false, 'error' => 'URL长度超出限制'];
        }
        
        // 协议检查
        $protocol = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($protocol, $config['allowed_protocols'])) {
            return ['valid' => false, 'error' => '不允许的协议: ' . $protocol];
        }
        
        // 域名白名单检查
        $host = parse_url($url, PHP_URL_HOST);
        if (!empty($config['allowed_domains']) && !in_array($host, $config['allowed_domains'])) {
            return ['valid' => false, 'error' => '域名不在白名单中: ' . $host];
        }
        
        // 文件扩展名检查
        $path = parse_url($url, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!empty($extension) && in_array($extension, $config['blocked_extensions'])) {
            return ['valid' => false, 'error' => '禁止的文件类型: ' . $extension];
        }
        
        return ['valid' => true, 'error' => ''];
    }

    /**
     * 获取广告安全配置
     */
    private function getSecurityConfig(): array
    {
        static $config = null;
        
        if ($config === null) {
            $rows = $this->db->query("SELECT config_name, config_value FROM " . DB_PREFIX . "config WHERE config_name LIKE 'ad_%'");
            $config = [];
            foreach ($rows as $row) {
                $config[$row['config_name']] = $row['config_value'];
            }
            
            // 处理数组类型的配置
            $config['allowed_domains'] = !empty($config['ad_allowed_domains']) 
                ? array_filter(array_map('trim', explode(',', $config['ad_allowed_domains'])))
                : [];
            $config['allowed_protocols'] = !empty($config['ad_allowed_protocols'])
                ? array_filter(array_map('trim', explode(',', $config['ad_allowed_protocols'])))
                : ['https', 'http'];
            $config['blocked_extensions'] = !empty($config['ad_blocked_extensions'])
                ? array_filter(array_map('trim', explode(',', $config['ad_blocked_extensions'])))
                : [];
            $config['ad_url_check'] = ($config['ad_url_check'] ?? '1') === '1';
            $config['ad_max_url_length'] = (int)($config['ad_max_url_length'] ?? 500);
        }
        
        return $config;
    }
}
