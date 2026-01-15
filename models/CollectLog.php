<?php
/**
 * 采集日志模型
 * Powered by https://xpornkit.com
 */

class XpkCollectLog extends XpkModel
{
    protected string $table = DB_PREFIX . 'collect_log';
    protected string $pk = 'log_id';

    /**
     * 开始采集日志
     */
    public function start(int $collectId, string $collectName, string $type = 'manual', string $mode = 'add'): int
    {
        return $this->insert([
            'collect_id' => $collectId,
            'collect_name' => $collectName,
            'log_type' => $type,
            'log_mode' => $mode,
            'log_status' => 2, // 进行中
            'log_time' => time()
        ]);
    }

    /**
     * 完成采集日志
     */
    public function finish(int $logId, int $pages, int $added, int $updated, int $skipped, string $message = ''): void
    {
        $log = $this->find($logId);
        if (!$log) return;

        $duration = time() - $log['log_time'];
        
        $this->update($logId, [
            'log_pages' => $pages,
            'log_added' => $added,
            'log_updated' => $updated,
            'log_skipped' => $skipped,
            'log_duration' => $duration,
            'log_status' => 1,
            'log_message' => $message ?: '采集完成'
        ]);
    }

    /**
     * 采集失败
     */
    public function fail(int $logId, string $message): void
    {
        $log = $this->find($logId);
        if (!$log) return;

        $duration = time() - $log['log_time'];
        
        $this->update($logId, [
            'log_duration' => $duration,
            'log_status' => 0,
            'log_message' => $message
        ]);
    }

    /**
     * 获取日志列表（分页）
     */
    public function getList(int $page = 1, int $pageSize = 20, ?int $collectId = null): array
    {
        $where = '';
        $params = [];
        
        if ($collectId) {
            $where = 'WHERE collect_id = ?';
            $params[] = $collectId;
        }

        $offset = ($page - 1) * $pageSize;
        
        $list = $this->db->query(
            "SELECT * FROM {$this->table} {$where} ORDER BY log_id DESC LIMIT {$pageSize} OFFSET {$offset}",
            $params
        );
        
        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} {$where}",
            $params
        )['cnt'] ?? 0;

        return [
            'list' => $list,
            'total' => (int)$total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize)
        ];
    }

    /**
     * 获取统计数据
     */
    public function getStats(?int $collectId = null, int $days = 7): array
    {
        $startTime = strtotime("-{$days} days");
        
        $where = 'WHERE log_time >= ?';
        $params = [$startTime];
        
        if ($collectId) {
            $where .= ' AND collect_id = ?';
            $params[] = $collectId;
        }

        // 总计
        $total = $this->db->queryOne(
            "SELECT COUNT(*) as count, SUM(log_added) as added, SUM(log_updated) as updated, SUM(log_skipped) as skipped 
             FROM {$this->table} {$where} AND log_status = 1",
            $params
        );

        // 按天统计
        $daily = $this->db->query(
            "SELECT DATE(FROM_UNIXTIME(log_time)) as date, 
                    COUNT(*) as count, 
                    SUM(log_added) as added, 
                    SUM(log_updated) as updated
             FROM {$this->table} {$where} AND log_status = 1
             GROUP BY DATE(FROM_UNIXTIME(log_time))
             ORDER BY date DESC",
            $params
        );

        // 按采集站统计
        $byCollect = $this->db->query(
            "SELECT collect_id, collect_name, 
                    COUNT(*) as count, 
                    SUM(log_added) as added, 
                    SUM(log_updated) as updated
             FROM {$this->table} {$where} AND log_status = 1
             GROUP BY collect_id, collect_name
             ORDER BY added DESC",
            $params
        );

        return [
            'total' => [
                'count' => (int)($total['count'] ?? 0),
                'added' => (int)($total['added'] ?? 0),
                'updated' => (int)($total['updated'] ?? 0),
                'skipped' => (int)($total['skipped'] ?? 0)
            ],
            'daily' => $daily,
            'byCollect' => $byCollect
        ];
    }

    /**
     * 清理旧日志
     */
    public function clean(int $days = 30): int
    {
        $time = strtotime("-{$days} days");
        return $this->db->execute(
            "DELETE FROM {$this->table} WHERE log_time < ?",
            [$time]
        );
    }

    /**
     * 获取最近的采集记录
     */
    public function getRecent(int $limit = 10): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY log_id DESC LIMIT ?",
            [$limit]
        );
    }
}
