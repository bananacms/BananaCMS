<?php
/**
 * 转码任务模型
 * Powered by https://xpornkit.com
 */

class XpkTranscode extends XpkModel
{
    protected string $table = DB_PREFIX . 'transcode';
    protected string $pk = 'transcode_id';

    // 状态常量
    const STATUS_PENDING = 0;    // 待处理
    const STATUS_PROCESSING = 1; // 处理中
    const STATUS_COMPLETED = 2;  // 已完成
    const STATUS_FAILED = 3;     // 失败

    /**
     * 获取任务列表（分页）
     */
    public function getListPaged(int $page = 1, int $pageSize = 20, ?int $status = null): array
    {
        $offset = ($page - 1) * $pageSize;
        
        $where = '';
        $params = [];
        
        if ($status !== null) {
            $where = 'WHERE t.transcode_status = ?';
            $params[] = $status;
        }
        
        $sql = "SELECT t.*, v.vod_name FROM {$this->table} t 
                LEFT JOIN " . DB_PREFIX . "vod v ON t.vod_id = v.vod_id 
                {$where} ORDER BY t.transcode_id DESC LIMIT {$pageSize} OFFSET {$offset}";
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} t {$where}";
        
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
     * 创建转码任务
     */
    public function createTask(string $sourceFile, string $outputDir, int $vodId = 0): int
    {
        return $this->insert([
            'vod_id' => $vodId,
            'source_file' => $sourceFile,
            'output_dir' => $outputDir,
            'transcode_status' => self::STATUS_PENDING,
            'transcode_progress' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    /**
     * 获取待处理任务
     */
    public function getPendingTask(): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE transcode_status = ? ORDER BY transcode_id ASC LIMIT 1",
            [self::STATUS_PENDING]
        );
    }

    /**
     * 更新任务状态
     */
    public function updateStatus(int $id, int $status, array $extra = []): bool
    {
        $data = array_merge([
            'transcode_status' => $status,
            'updated_at' => time(),
        ], $extra);
        
        if ($status === self::STATUS_COMPLETED || $status === self::STATUS_FAILED) {
            $data['finished_at'] = time();
        }
        
        return $this->update($id, $data) !== false;
    }

    /**
     * 更新进度
     */
    public function updateProgress(int $id, int $progress): bool
    {
        return $this->update($id, [
            'transcode_progress' => min(99, $progress),
            'updated_at' => time(),
        ]) !== false;
    }

    /**
     * 获取统计数据
     */
    public function getStats(): array
    {
        $stats = $this->db->query(
            "SELECT transcode_status, COUNT(*) as count FROM {$this->table} GROUP BY transcode_status"
        );
        
        $result = [
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'failed' => 0,
            'total' => 0,
        ];
        
        foreach ($stats as $row) {
            $result['total'] += $row['count'];
            switch ($row['transcode_status']) {
                case self::STATUS_PENDING: $result['pending'] = $row['count']; break;
                case self::STATUS_PROCESSING: $result['processing'] = $row['count']; break;
                case self::STATUS_COMPLETED: $result['completed'] = $row['count']; break;
                case self::STATUS_FAILED: $result['failed'] = $row['count']; break;
            }
        }
        
        return $result;
    }

    /**
     * 重试失败任务
     */
    public function retry(int $id): bool
    {
        $task = $this->find($id);
        if (!$task || $task['transcode_status'] != self::STATUS_FAILED) {
            return false;
        }
        
        return $this->update($id, [
            'transcode_status' => self::STATUS_PENDING,
            'transcode_progress' => 0,
            'error_msg' => '',
            'updated_at' => time(),
        ]) !== false;
    }

    /**
     * 删除任务（包括文件）
     */
    public function deleteTask(int $id, bool $deleteFiles = true): bool
    {
        $task = $this->find($id);
        if (!$task) {
            return false;
        }
        
        // 删除输出文件
        if ($deleteFiles && !empty($task['output_dir']) && is_dir($task['output_dir'])) {
            $this->deleteDirectory($task['output_dir']);
        }
        
        // 删除源文件
        if ($deleteFiles && !empty($task['source_file']) && file_exists($task['source_file'])) {
            @unlink($task['source_file']);
        }
        
        return $this->delete($id);
    }

    /**
     * 递归删除目录
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
