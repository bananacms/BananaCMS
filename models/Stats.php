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
        
        // 如果无法获取IP，使用占位符
        if (empty($ip) || $ip === '::1') {
            $ip = '127.0.0.1';
        }
        
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $today = date('Y-m-d');

        // 同一IP同一目标今天只记录一次UV
        $exists = $this->db->queryOne(
            "SELECT log_id FROM {$this->logTable} WHERE log_type = ? AND target_id = ? AND log_ip = ? AND log_date = ?",
            [$type, $targetId, $ip, $today]
        );

        if ($exists) {
            // 更新PV和最后访问时间
            $this->db->execute(
                "UPDATE {$this->logTable} SET log_pv = log_pv + 1, log_time = ? WHERE log_id = ?",
                [time(), $exists['log_id']]
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

        $result = $this->db->query(
            "SELECT v.vod_id, v.vod_name, v.vod_pic, v.vod_hits,
                    COUNT(DISTINCT s.log_ip) as period_uv,
                    COALESCE(SUM(s.log_pv), 0) as period_pv
             FROM " . DB_PREFIX . "vod v
             LEFT JOIN {$this->logTable} s ON s.target_id = v.vod_id AND s.log_type = 'vod' AND s.log_date >= ?
             WHERE v.vod_status = 1
             GROUP BY v.vod_id
             ORDER BY period_pv DESC, v.vod_hits DESC
             LIMIT {$limit}",
            [$startDate]
        );
        
        // 如果没有统计数据，按点击量排序返回
        $hasStats = false;
        foreach ($result as $row) {
            if ($row['period_pv'] > 0) {
                $hasStats = true;
                break;
            }
        }
        
        if (!$hasStats) {
            return $this->db->query(
                "SELECT vod_id, vod_name, vod_pic, vod_hits, 0 as period_uv, vod_hits as period_pv
                 FROM " . DB_PREFIX . "vod
                 WHERE vod_status = 1
                 ORDER BY vod_hits DESC
                 LIMIT {$limit}"
            );
        }
        
        return $result;
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
        try {
            $this->db->execute("DELETE FROM {$this->logTable} WHERE log_date < ?", [$cutoffDate]);
            return $this->db->rowCount();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * 清理所有日志表
     */
    public function cleanAllLogs(array $options = []): array
    {
        $results = [];
        $defaultDays = 90;

        // 辅助函数：安全执行删除
        $safeDelete = function($table, $field, $value) {
            try {
                $this->db->execute("DELETE FROM {$table} WHERE {$field} < ?", [$value]);
                return $this->db->rowCount();
            } catch (Exception $e) {
                return 0;
            }
        };

        // 1. 清理统计日志
        $statsDays = $options['stats_days'] ?? $defaultDays;
        $cutoffDate = date('Y-m-d', strtotime("-{$statsDays} days"));
        $results['stats_log'] = $safeDelete($this->logTable, 'log_date', $cutoffDate);

        // 2. 清理操作日志
        $adminDays = $options['admin_days'] ?? 30;
        $cutoffTime = time() - ($adminDays * 86400);
        $results['admin_log'] = $safeDelete(DB_PREFIX . "admin_log", 'log_time', $cutoffTime);

        // 3. 清理搜索日志
        $searchDays = $options['search_days'] ?? $defaultDays;
        $cutoffTime = time() - ($searchDays * 86400);
        $results['search_log'] = $safeDelete(DB_PREFIX . "search_log", 'search_time', $cutoffTime);

        // 4. 清理采集日志
        $collectDays = $options['collect_days'] ?? 30;
        $cutoffTime = time() - ($collectDays * 86400);
        $results['collect_log'] = $safeDelete(DB_PREFIX . "collect_log", 'log_time', $cutoffTime);

        // 5. 清理评论投票记录
        $voteDays = $options['vote_days'] ?? 180;
        $cutoffTime = time() - ($voteDays * 86400);
        $results['comment_vote'] = $safeDelete(DB_PREFIX . "comment_vote", 'vote_time', $cutoffTime);

        // 6. 清理评分记录
        $scoreDays = $options['score_days'] ?? 365;
        $cutoffTime = time() - ($scoreDays * 86400);
        $results['score'] = $safeDelete(DB_PREFIX . "score", 'score_time', $cutoffTime);

        // 7. 清理用户观看历史
        $historyDays = $options['history_days'] ?? 365;
        $cutoffTime = time() - ($historyDays * 86400);
        $results['user_history'] = $safeDelete(DB_PREFIX . "user_history", 'watch_time', $cutoffTime);

        // 8. 清理上传分片临时文件
        $chunkDays = $options['chunk_days'] ?? 7;
        $cutoffTime = time() - ($chunkDays * 86400);
        $results['upload_chunk'] = $safeDelete(DB_PREFIX . "upload_chunk", 'created_at', $cutoffTime);

        return $results;
    }

    /**
     * 获取各表的记录统计
     */
    public function getLogStats(): array
    {
        $stats = [];

        // 辅助函数：安全获取表记录数
        $safeCount = function($table) {
            try {
                return $this->db->queryOne("SELECT COUNT(*) as count FROM {$table}")['count'] ?? 0;
            } catch (Exception $e) {
                return 0;
            }
        };

        // 统计日志
        $stats['stats_log'] = $safeCount($this->logTable);
        
        // 操作日志
        $stats['admin_log'] = $safeCount(DB_PREFIX . "admin_log");
        
        // 搜索日志
        $stats['search_log'] = $safeCount(DB_PREFIX . "search_log");
        
        // 采集日志
        $stats['collect_log'] = $safeCount(DB_PREFIX . "collect_log");
        
        // 评论投票
        $stats['comment_vote'] = $safeCount(DB_PREFIX . "comment_vote");
        
        // 评分记录
        $stats['score'] = $safeCount(DB_PREFIX . "score");
        
        // 观看历史
        $stats['user_history'] = $safeCount(DB_PREFIX . "user_history");
        
        // 上传分片
        $stats['upload_chunk'] = $safeCount(DB_PREFIX . "upload_chunk");

        return $stats;
    }

    /**
     * 获取诊断信息
     */
    public function getDiagnostics(): array
    {
        $info = [
            'table_exists' => false,
            'total_records' => 0,
            'today_records' => 0,
            'last_record_time' => null,
            'current_ip' => $this->getClientIp(),
        ];
        
        try {
            // 检查表是否存在
            $result = $this->db->query("SHOW TABLES LIKE '{$this->logTable}'");
            $info['table_exists'] = !empty($result);
            
            if ($info['table_exists']) {
                // 总记录数
                $info['total_records'] = (int)($this->db->queryOne(
                    "SELECT COUNT(*) as cnt FROM {$this->logTable}"
                )['cnt'] ?? 0);
                
                // 今日记录数
                $info['today_records'] = (int)($this->db->queryOne(
                    "SELECT COUNT(*) as cnt FROM {$this->logTable} WHERE log_date = ?",
                    [date('Y-m-d')]
                )['cnt'] ?? 0);
                
                // 最后记录时间
                $lastRecord = $this->db->queryOne(
                    "SELECT log_time FROM {$this->logTable} ORDER BY log_id DESC LIMIT 1"
                );
                if ($lastRecord && $lastRecord['log_time']) {
                    $info['last_record_time'] = date('Y-m-d H:i:s', $lastRecord['log_time']);
                }
            }
        } catch (Exception $e) {
            $info['error'] = $e->getMessage();
        }
        
        return $info;
    }

    /**
     * 获取客户端IP
     */
    private function getClientIp(): string
    {
        // 按优先级尝试获取真实IP
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_REAL_IP',            // Nginx代理
            'HTTP_X_FORWARDED_FOR',      // 标准代理头
            'HTTP_CLIENT_IP',            // 某些代理
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',               // 直连
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // X-Forwarded-For 可能包含多个IP，取第一个
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // 验证IP格式
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
}
