<?php
/**
 * 评分模型
 * Powered by https://xpornkit.com
 */

class XpkScore
{
    private XpkDatabase $db;
    private string $table;

    public function __construct()
    {
        $this->db = XpkDatabase::getInstance();
        $this->table = DB_PREFIX . 'score';
    }

    /**
     * 用户评分
     */
    public function rate(string $type, int $targetId, int $userId, int $score): array
    {
        // 验证分数范围
        $score = max(1, min(10, $score));

        // 检查是否已评分
        $existing = $this->getUserScore($type, $targetId, $userId);

        if ($existing) {
            // 更新评分
            $oldScore = $existing['score'];
            $this->db->execute(
                "UPDATE {$this->table} SET score = ?, score_time = ? WHERE score_id = ?",
                [$score, time(), $existing['score_id']]
            );
            $this->updateTargetScore($type, $targetId);
            
            return [
                'action' => 'update',
                'old_score' => $oldScore,
                'new_score' => $score
            ];
        }

        // 新评分
        $this->db->execute(
            "INSERT INTO {$this->table} (score_type, target_id, user_id, score, score_ip, score_time) VALUES (?, ?, ?, ?, ?, ?)",
            [$type, $targetId, $userId, $score, $this->getClientIp(), time()]
        );

        $this->updateTargetScore($type, $targetId);

        return [
            'action' => 'new',
            'score' => $score
        ];
    }

    /**
     * 游客评分（基于IP）
     */
    public function rateByIp(string $type, int $targetId, int $score): array
    {
        $ip = $this->getClientIp();
        $score = max(1, min(10, $score));

        // 检查IP是否已评分
        $existing = $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE score_type = ? AND target_id = ? AND user_id = 0 AND score_ip = ?",
            [$type, $targetId, $ip]
        );

        if ($existing) {
            return [
                'action' => 'exists',
                'message' => '您已经评过分了'
            ];
        }

        // 新评分
        $this->db->execute(
            "INSERT INTO {$this->table} (score_type, target_id, user_id, score, score_ip, score_time) VALUES (?, ?, 0, ?, ?, ?)",
            [$type, $targetId, $score, $ip, time()]
        );

        $this->updateTargetScore($type, $targetId);

        return [
            'action' => 'new',
            'score' => $score
        ];
    }

    /**
     * 获取用户评分
     */
    public function getUserScore(string $type, int $targetId, int $userId): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE score_type = ? AND target_id = ? AND user_id = ?",
            [$type, $targetId, $userId]
        );
    }

    /**
     * 获取评分统计
     */
    public function getStats(string $type, int $targetId): array
    {
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as count, AVG(score) as avg, SUM(score) as total FROM {$this->table} WHERE score_type = ? AND target_id = ?",
            [$type, $targetId]
        );

        // 获取分数分布
        $distribution = $this->db->query(
            "SELECT score, COUNT(*) as count FROM {$this->table} WHERE score_type = ? AND target_id = ? GROUP BY score ORDER BY score DESC",
            [$type, $targetId]
        );

        $dist = array_fill(1, 10, 0);
        foreach ($distribution as $row) {
            $dist[$row['score']] = (int)$row['count'];
        }

        return [
            'count' => (int)($result['count'] ?? 0),
            'average' => round((float)($result['avg'] ?? 0), 1),
            'total' => (int)($result['total'] ?? 0),
            'distribution' => $dist
        ];
    }

    /**
     * 更新目标评分（同步到视频/文章表）
     */
    private function updateTargetScore(string $type, int $targetId): void
    {
        $stats = $this->getStats($type, $targetId);
        
        if ($type === 'vod') {
            $this->db->execute(
                "UPDATE " . DB_PREFIX . "vod SET vod_score = ? WHERE vod_id = ?",
                [$stats['average'], $targetId]
            );
        } elseif ($type === 'art') {
            // 如果文章表有评分字段
            // $this->db->execute("UPDATE " . DB_PREFIX . "art SET art_score = ? WHERE art_id = ?", [$stats['average'], $targetId]);
        }
    }

    /**
     * 获取最近评分记录
     */
    public function getRecent(string $type, int $targetId, int $limit = 10): array
    {
        return $this->db->query(
            "SELECT s.*, u.user_name, u.user_nick_name, u.user_pic 
             FROM {$this->table} s
             LEFT JOIN " . DB_PREFIX . "user u ON s.user_id = u.user_id
             WHERE s.score_type = ? AND s.target_id = ?
             ORDER BY s.score_time DESC
             LIMIT {$limit}",
            [$type, $targetId]
        );
    }

    /**
     * 获取用户评分历史
     */
    public function getUserHistory(int $userId, int $page = 1, int $pageSize = 20): array
    {
        $offset = ($page - 1) * $pageSize;
        
        $list = $this->db->query(
            "SELECT s.*, v.vod_name, v.vod_pic 
             FROM {$this->table} s
             LEFT JOIN " . DB_PREFIX . "vod v ON s.target_id = v.vod_id AND s.score_type = 'vod'
             WHERE s.user_id = ?
             ORDER BY s.score_time DESC
             LIMIT {$pageSize} OFFSET {$offset}",
            [$userId]
        );

        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} WHERE user_id = ?",
            [$userId]
        )['cnt'] ?? 0;

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 检查用户是否已评分
     */
    public function hasRated(string $type, int $targetId, int $userId): bool
    {
        if ($userId > 0) {
            return $this->getUserScore($type, $targetId, $userId) !== null;
        }
        
        // 游客检查IP
        $ip = $this->getClientIp();
        $existing = $this->db->queryOne(
            "SELECT score_id FROM {$this->table} WHERE score_type = ? AND target_id = ? AND user_id = 0 AND score_ip = ?",
            [$type, $targetId, $ip]
        );
        return $existing !== null;
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

    /**
     * 删除评分
     */
    public function delete(int $scoreId): bool
    {
        $score = $this->db->queryOne("SELECT * FROM {$this->table} WHERE score_id = ?", [$scoreId]);
        if (!$score) return false;

        $this->db->execute("DELETE FROM {$this->table} WHERE score_id = ?", [$scoreId]);
        $this->updateTargetScore($score['score_type'], $score['target_id']);
        
        return true;
    }
}
