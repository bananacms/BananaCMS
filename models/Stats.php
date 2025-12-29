<?php
/**
 * 数据统计模型
 * Powered by https://xpornkit.com
 */

class XpkStats
{
    private XpkDatabase $db;
    private string $logTable;

    public function __construct()
    {
        $this->db = XpkDatabase::getInstance();
        $this->logTable = DB_PREFIX . 'stats_log';
    }

    /**
     * 记录访问日志
     */
    public function log(string $type, int $targetId = 0, string $extra = ''): void
    {
        $ip = $this->getClientIp();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $today = date('Y-m-d');

        // 同一IP同一目标今天只记录一次UV
        $exists = $this->db->queryOne(
            "SELECT log_id FROM {$this->logTable} WHERE log_type = ? AND target_id = ? AND log_ip = ? AND log_date = ?",
            [$type, $targetId, $ip, $today]
        );

        if ($exists) {
            // 更新PV
            $this->db->execute(
                "UPDATE {$this->logTable} SET log_pv = log_pv + 1 WHERE log_id = ?",
                [$exists['log_id']]
            );
        } else {
            // 新增记录
            $this->db->execute(
                "INSERT INTO {$this->logTable} (log_type, target_id, log_ip, log_ua, log_referer, log_date, log_pv, log_time) VALUES (?, ?, ?, ?, ?, ?, 1, ?)",
                [$type, $targetId, $ip, mb_substr($ua, 0, 500), mb_substr($referer, 0, 500), $today, time()]
            );
        }
    }

    /**
     * 获取今日概览
     */
    public function getTodayOverview(): array
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // 今日数据
        $todayData = $this->db->queryOne(
            "SELECT COUNT(DISTINCT log_ip) as uv, SUM(log_pv) as pv FROM {$this->logTable} WHERE log_date = ?",
            [$today]
        );

        // 昨日数据（用于对比）
        $yesterdayData = $this->db->queryOne(
            "SELECT COUNT(DISTINCT log_ip) as uv, SUM(log_pv) as pv FROM {$this->logTable} WHERE log_date = ?",
            [$yesterday]
        );

        // 今日新增用户
        $newUsers = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "user WHERE DATE(FROM_UNIXTIME(user_reg_time)) = ?",
            [$today]
        )['cnt'] ?? 0;

        // 今日播放量
        $todayPlays = $this->db->queryOne(
            "SELECT SUM(log_pv) as cnt FROM {$this->logTable} WHERE log_type = 'play' AND log_date = ?",
            [$today]
        )['cnt'] ?? 0;

        return [
            'uv' => (int)($todayData['uv'] ?? 0),
            'pv' => (int)($todayData['pv'] ?? 0),
            'uv_yesterday' => (int)($yesterdayData['uv'] ?? 0),
            'pv_yesterday' => (int)($yesterdayData['pv'] ?? 0),
            'new_users' => (int)$newUsers,
            'plays' => (int)$todayPlays,
        ];
    }

    /**
     * 获取趋势数据（最近N天）
     */
    public function getTrend(int $days = 7, string $type = ''): array
    {
        $dates = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dates[] = date('m-d', strtotime($date));
            $data[$date] = ['uv' => 0, 'pv' => 0];
        }

        $where = $type ? "AND log_type = ?" : "";
        $params = $type ? [date('Y-m-d', strtotime("-" . ($days - 1) . " days")), $type] : [date('Y-m-d', strtotime("-" . ($days - 1) . " days"))];

        $result = $this->db->query(
            "SELECT log_date, COUNT(DISTINCT log_ip) as uv, SUM(log_pv) as pv 
             FROM {$this->logTable} 
             WHERE log_date >= ? {$where}
             GROUP BY log_date",
            $params
        );

        foreach ($result as $row) {
            if (isset($data[$row['log_date']])) {
                $data[$row['log_date']] = [
                    'uv' => (int)$row['uv'],
                    'pv' => (int)$row['pv']
                ];
            }
        }

        return [
            'dates' => $dates,
            'uv' => array_column(array_values($data), 'uv'),
            'pv' => array_column(array_values($data), 'pv'),
        ];
    }

    /**
     * 获取用户增长趋势
     */
    public function getUserTrend(int $days = 7): array
    {
        $dates = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dates[] = date('m-d', strtotime($date));
            $data[$date] = 0;
        }

        $result = $this->db->query(
            "SELECT DATE(FROM_UNIXTIME(user_reg_time)) as reg_date, COUNT(*) as cnt 
             FROM " . DB_PREFIX . "user 
             WHERE user_reg_time >= ?
             GROUP BY reg_date",
            [strtotime("-" . ($days - 1) . " days")]
        );

        foreach ($result as $row) {
            if (isset($data[$row['reg_date']])) {
                $data[$row['reg_date']] = (int)$row['cnt'];
            }
        }

        return [
            'dates' => $dates,
            'counts' => array_values($data),
        ];
    }

    /**
     * 获取热门视频排行
     */
    public function getHotVideos(int $days = 7, int $limit = 10): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        return $this->db->query(
            "SELECT v.vod_id, v.vod_name, v.vod_pic, v.vod_hits,
                    COUNT(DISTINCT s.log_ip) as period_uv,
                    SUM(s.log_pv) as period_pv
             FROM " . DB_PREFIX . "vod v
             LEFT JOIN {$this->logTable} s ON s.target_id = v.vod_id AND s.log_type = 'vod' AND s.log_date >= ?
             WHERE v.vod_status = 1
             GROUP BY v.vod_id
             ORDER BY period_pv DESC, v.vod_hits DESC
             LIMIT {$limit}",
            [$startDate]
        );
    }

    /**
     * 获取热门搜索词
     */
    public function getHotKeywords(int $days = 7, int $limit = 20): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $result = $this->db->query(
            "SELECT log_referer as keyword, COUNT(*) as cnt
             FROM {$this->logTable}
             WHERE log_type = 'search' AND log_date >= ?
             GROUP BY keyword
             ORDER BY cnt DESC
             LIMIT {$limit}",
            [$startDate]
        );

        return $result;
    }

    /**
     * 获取来源统计
     */
    public function getRefererStats(int $days = 7): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $result = $this->db->query(
            "SELECT 
                CASE 
                    WHEN log_referer = '' THEN '直接访问'
                    WHEN log_referer LIKE '%baidu.com%' THEN '百度'
                    WHEN log_referer LIKE '%google.com%' THEN '谷歌'
                    WHEN log_referer LIKE '%bing.com%' THEN '必应'
                    WHEN log_referer LIKE '%so.com%' THEN '360搜索'
                    WHEN log_referer LIKE '%sogou.com%' THEN '搜狗'
                    ELSE '其他'
                END as source,
                COUNT(*) as cnt
             FROM {$this->logTable}
             WHERE log_date >= ?
             GROUP BY source
             ORDER BY cnt DESC",
            [$startDate]
        );

        return $result;
    }

    /**
     * 获取设备统计
     */
    public function getDeviceStats(int $days = 7): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $result = $this->db->query(
            "SELECT log_ua FROM {$this->logTable} WHERE log_date >= ?",
            [$startDate]
        );

        $devices = ['mobile' => 0, 'tablet' => 0, 'desktop' => 0];

        foreach ($result as $row) {
            $ua = strtolower($row['log_ua']);
            if (preg_match('/mobile|android|iphone|ipod/', $ua) && !preg_match('/ipad|tablet/', $ua)) {
                $devices['mobile']++;
            } elseif (preg_match('/ipad|tablet/', $ua)) {
                $devices['tablet']++;
            } else {
                $devices['desktop']++;
            }
        }

        return [
            ['name' => '手机', 'value' => $devices['mobile']],
            ['name' => '平板', 'value' => $devices['tablet']],
            ['name' => '电脑', 'value' => $devices['desktop']],
        ];
    }

    /**
     * 获取内容统计
     */
    public function getContentStats(): array
    {
        $vods = $this->db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "vod")['cnt'] ?? 0;
        $actors = $this->db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "actor")['cnt'] ?? 0;
        $arts = $this->db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "art")['cnt'] ?? 0;
        $users = $this->db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "user")['cnt'] ?? 0;
        $comments = $this->db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "comment WHERE comment_status = 1")['cnt'] ?? 0;

        // 短视频/短剧
        $shorts = 0;
        try {
            $shorts = $this->db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "short")['cnt'] ?? 0;
        } catch (Exception $e) {}

        return [
            'vods' => (int)$vods,
            'actors' => (int)$actors,
            'arts' => (int)$arts,
            'users' => (int)$users,
            'comments' => (int)$comments,
            'shorts' => (int)$shorts,
        ];
    }

    /**
     * 获取实时在线（最近5分钟活跃）
     */
    public function getOnlineCount(): int
    {
        $fiveMinAgo = time() - 300;
        return (int)($this->db->queryOne(
            "SELECT COUNT(DISTINCT log_ip) as cnt FROM {$this->logTable} WHERE log_time >= ?",
            [$fiveMinAgo]
        )['cnt'] ?? 0);
    }

    /**
     * 清理过期日志
     */
    public function cleanOldLogs(int $keepDays = 90): int
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$keepDays} days"));
        $this->db->execute("DELETE FROM {$this->logTable} WHERE log_date < ?", [$cutoffDate]);
        return $this->db->rowCount();
    }

    /**
     * 获取客户端IP
     */
    private function getClientIp(): string
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }
        return $ip;
    }
}
