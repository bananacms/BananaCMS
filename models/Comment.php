<?php
/**
 * 评论模型
 * Powered by https://xpornkit.com
 */

class XpkComment
{
    private XpkDatabase $db;
    private string $table;

    // 评论类型
    const TYPE_VOD = 'vod';     // 视频评论
    const TYPE_ART = 'art';     // 文章评论

    // 评论状态
    const STATUS_PENDING = 0;   // 待审核
    const STATUS_APPROVED = 1;  // 已通过
    const STATUS_REJECTED = 2;  // 已拒绝

    // 敏感词（可从配置读取）
    private array $sensitiveWords = [];

    public function __construct()
    {
        $this->db = XpkDatabase::getInstance();
        $this->table = DB_PREFIX . 'comment';
        $this->loadSensitiveWords();
    }

    /**
     * 加载敏感词
     */
    private function loadSensitiveWords(): void
    {
        $config = xpk_cache()->get('site_config') ?: [];
        $words = $config['comment_sensitive_words'] ?? '';
        if ($words) {
            $this->sensitiveWords = array_filter(array_map('trim', explode("\n", $words)));
        }
    }

    /**
     * 获取评论列表（前台，树形结构）
     */
    public function getListByTarget(string $type, int $targetId, int $page = 1, int $pageSize = 20): array
    {
        $offset = ($page - 1) * $pageSize;
        
        // 获取顶级评论
        $list = $this->db->query(
            "SELECT c.*, u.user_name, u.user_nick_name, u.user_pic 
             FROM {$this->table} c
             LEFT JOIN " . DB_PREFIX . "user u ON c.user_id = u.user_id
             WHERE c.comment_type = ? AND c.target_id = ? AND c.parent_id = 0 AND c.comment_status = 1
             ORDER BY c.comment_id DESC
             LIMIT {$pageSize} OFFSET {$offset}",
            [$type, $targetId]
        );
        
        // 获取每条评论的回复
        foreach ($list as &$item) {
            $item['replies'] = $this->getReplies($item['comment_id']);
            $item['reply_count'] = count($item['replies']);
        }
        
        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} 
             WHERE comment_type = ? AND target_id = ? AND parent_id = 0 AND comment_status = 1",
            [$type, $targetId]
        )['cnt'] ?? 0;
        
        return ['list' => $list, 'total' => $total];
    }

    /**
     * 获取回复列表
     */
    public function getReplies(int $parentId, int $limit = 10): array
    {
        return $this->db->query(
            "SELECT c.*, u.user_name, u.user_nick_name, u.user_pic,
                    ru.user_nick_name as reply_to_name
             FROM {$this->table} c
             LEFT JOIN " . DB_PREFIX . "user u ON c.user_id = u.user_id
             LEFT JOIN " . DB_PREFIX . "comment rc ON c.reply_id = rc.comment_id
             LEFT JOIN " . DB_PREFIX . "user ru ON rc.user_id = ru.user_id
             WHERE c.parent_id = ? AND c.comment_status = 1
             ORDER BY c.comment_id ASC
             LIMIT {$limit}",
            [$parentId]
        );
    }

    /**
     * 获取更多回复
     */
    public function getMoreReplies(int $parentId, int $offset = 0, int $limit = 10): array
    {
        return $this->db->query(
            "SELECT c.*, u.user_name, u.user_nick_name, u.user_pic,
                    ru.user_nick_name as reply_to_name
             FROM {$this->table} c
             LEFT JOIN " . DB_PREFIX . "user u ON c.user_id = u.user_id
             LEFT JOIN " . DB_PREFIX . "comment rc ON c.reply_id = rc.comment_id
             LEFT JOIN " . DB_PREFIX . "user ru ON rc.user_id = ru.user_id
             WHERE c.parent_id = ? AND c.comment_status = 1
             ORDER BY c.comment_id ASC
             LIMIT {$limit} OFFSET {$offset}",
            [$parentId]
        );
    }

    /**
     * 发表评论
     */
    public function add(array $data): array
    {
        // 敏感词过滤
        $filtered = $this->filterSensitive($data['comment_content']);
        $data['comment_content'] = $filtered['content'];
        $hasSensitive = $filtered['has_sensitive'];

        // 获取审核设置
        $config = xpk_cache()->get('site_config') ?: [];
        $needAudit = ($config['comment_audit'] ?? '0') === '1';
        
        // 有敏感词强制审核
        if ($hasSensitive) {
            $needAudit = true;
        }

        $data['comment_status'] = $needAudit ? self::STATUS_PENDING : self::STATUS_APPROVED;
        $data['comment_time'] = time();
        $data['comment_ip'] = $this->getClientIp();

        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $this->db->execute(
            "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")",
            array_values($data)
        );
        
        $id = (int)$this->db->lastInsertId();

        return [
            'id' => $id,
            'status' => $data['comment_status'],
            'need_audit' => $needAudit,
            'has_sensitive' => $hasSensitive
        ];
    }

    /**
     * 敏感词过滤
     */
    public function filterSensitive(string $content): array
    {
        $hasSensitive = false;
        $filtered = $content;

        foreach ($this->sensitiveWords as $word) {
            if (empty($word)) continue;
            if (mb_stripos($filtered, $word) !== false) {
                $hasSensitive = true;
                // 替换为星号
                $replacement = str_repeat('*', mb_strlen($word));
                $filtered = str_ireplace($word, $replacement, $filtered);
            }
        }

        return [
            'content' => $filtered,
            'has_sensitive' => $hasSensitive,
            'original' => $content
        ];
    }

    /**
     * 点赞/踩
     */
    public function vote(int $commentId, int $userId, string $action): array
    {
        // 检查是否已投票
        $voteTable = DB_PREFIX . 'comment_vote';
        $existing = $this->db->queryOne(
            "SELECT * FROM {$voteTable} WHERE comment_id = ? AND user_id = ?",
            [$commentId, $userId]
        );

        if ($existing) {
            if ($existing['vote_type'] === $action) {
                // 取消投票
                $this->db->execute(
                    "DELETE FROM {$voteTable} WHERE vote_id = ?",
                    [$existing['vote_id']]
                );
                $field = $action === 'up' ? 'comment_up' : 'comment_down';
                $this->db->execute(
                    "UPDATE {$this->table} SET {$field} = {$field} - 1 WHERE comment_id = ?",
                    [$commentId]
                );
                return ['action' => 'cancel', 'type' => $action];
            } else {
                // 切换投票
                $this->db->execute(
                    "UPDATE {$voteTable} SET vote_type = ? WHERE vote_id = ?",
                    [$action, $existing['vote_id']]
                );
                $oldField = $existing['vote_type'] === 'up' ? 'comment_up' : 'comment_down';
                $newField = $action === 'up' ? 'comment_up' : 'comment_down';
                $this->db->execute(
                    "UPDATE {$this->table} SET {$oldField} = {$oldField} - 1, {$newField} = {$newField} + 1 WHERE comment_id = ?",
                    [$commentId]
                );
                return ['action' => 'switch', 'type' => $action];
            }
        }

        // 新投票
        $this->db->execute(
            "INSERT INTO {$voteTable} (comment_id, user_id, vote_type, vote_time) VALUES (?, ?, ?, ?)",
            [$commentId, $userId, $action, time()]
        );
        $field = $action === 'up' ? 'comment_up' : 'comment_down';
        $this->db->execute(
            "UPDATE {$this->table} SET {$field} = {$field} + 1 WHERE comment_id = ?",
            [$commentId]
        );

        return ['action' => 'vote', 'type' => $action];
    }

    /**
     * 获取用户投票状态
     */
    public function getUserVotes(int $userId, array $commentIds): array
    {
        if (empty($commentIds)) return [];
        
        $voteTable = DB_PREFIX . 'comment_vote';
        $placeholders = implode(',', array_fill(0, count($commentIds), '?'));
        $params = array_merge($commentIds, [$userId]);
        
        $votes = $this->db->query(
            "SELECT comment_id, vote_type FROM {$voteTable} WHERE comment_id IN ({$placeholders}) AND user_id = ?",
            $params
        );

        $result = [];
        foreach ($votes as $vote) {
            $result[$vote['comment_id']] = $vote['vote_type'];
        }
        return $result;
    }

    /**
     * 删除评论（软删除/硬删除）
     */
    public function delete(int $id, bool $hard = false): bool
    {
        if ($hard) {
            // 删除回复
            $this->db->execute("DELETE FROM {$this->table} WHERE parent_id = ?", [$id]);
            // 删除评论
            return $this->db->execute("DELETE FROM {$this->table} WHERE comment_id = ?", [$id]);
        }
        
        return $this->db->execute(
            "UPDATE {$this->table} SET comment_status = ? WHERE comment_id = ?",
            [self::STATUS_REJECTED, $id]
        );
    }

    /**
     * 审核评论
     */
    public function audit(int $id, int $status): bool
    {
        return $this->db->execute(
            "UPDATE {$this->table} SET comment_status = ? WHERE comment_id = ?",
            [$status, $id]
        );
    }

    /**
     * 批量审核
     */
    public function batchAudit(array $ids, int $status): int
    {
        if (empty($ids)) return 0;
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([$status], $ids);
        
        $this->db->execute(
            "UPDATE {$this->table} SET comment_status = ? WHERE comment_id IN ({$placeholders})",
            $params
        );
        
        return count($ids);
    }

    /**
     * 获取列表（后台）
     */
    public function getList(int $status = -1, string $type = '', int $page = 1, int $pageSize = 20): array
    {
        $where = '1=1';
        $params = [];
        
        if ($status >= 0) {
            $where .= ' AND c.comment_status = ?';
            $params[] = $status;
        }
        
        if ($type) {
            $where .= ' AND c.comment_type = ?';
            $params[] = $type;
        }
        
        $offset = ($page - 1) * $pageSize;
        
        $list = $this->db->query(
            "SELECT c.*, u.user_name, u.user_nick_name
             FROM {$this->table} c
             LEFT JOIN " . DB_PREFIX . "user u ON c.user_id = u.user_id
             WHERE {$where}
             ORDER BY c.comment_id DESC
             LIMIT {$pageSize} OFFSET {$offset}",
            $params
        );
        
        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} c WHERE {$where}",
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
            "SELECT * FROM {$this->table} WHERE comment_id = ?",
            [$id]
        );
    }

    /**
     * 统计
     */
    public function getStats(): array
    {
        $result = $this->db->query(
            "SELECT comment_status, COUNT(*) as cnt FROM {$this->table} GROUP BY comment_status"
        );
        
        $stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0];
        foreach ($result as $row) {
            $stats['total'] += $row['cnt'];
            if ($row['comment_status'] == 0) $stats['pending'] = $row['cnt'];
            if ($row['comment_status'] == 1) $stats['approved'] = $row['cnt'];
            if ($row['comment_status'] == 2) $stats['rejected'] = $row['cnt'];
        }
        
        return $stats;
    }

    /**
     * 获取目标评论数
     */
    public function getCount(string $type, int $targetId): int
    {
        return (int)($this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} WHERE comment_type = ? AND target_id = ? AND comment_status = 1",
            [$type, $targetId]
        )['cnt'] ?? 0);
    }

    /**
     * 检查发言频率
     */
    public function checkFrequency(int $userId, string $ip): bool
    {
        $config = xpk_cache()->get('site_config') ?: [];
        $interval = (int)($config['comment_interval'] ?? 60);
        
        if ($interval <= 0) return true;
        
        $lastTime = $this->db->queryOne(
            "SELECT comment_time FROM {$this->table} WHERE (user_id = ? OR comment_ip = ?) ORDER BY comment_id DESC LIMIT 1",
            [$userId, $ip]
        )['comment_time'] ?? 0;
        
        return (time() - $lastTime) >= $interval;
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
