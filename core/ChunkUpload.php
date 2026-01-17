<?php
/**
 * 分片上传处理
 * Powered by https://xpornkit.com
 */

class XpkChunkUpload
{
    private string $chunkDir;
    private string $uploadDir;
    private XpkDatabase $db;
    
    public function __construct()
    {
        $this->chunkDir = RUNTIME_PATH . 'chunks/';
        $this->uploadDir = ROOT_PATH . 'upload/transcode/';
        $this->db = XpkDatabase::getInstance();
        
        // 确保目录存在
        if (!is_dir($this->chunkDir)) {
            mkdir($this->chunkDir, 0755, true);
        }
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * 处理分片上传
     */
    public function handleChunk(array $params, array $file): array
    {
        $uploadId = $params['resumableIdentifier'] ?? '';
        $chunkNumber = (int)($params['resumableChunkNumber'] ?? 0);
        $totalChunks = (int)($params['resumableTotalChunks'] ?? 0);
        $fileName = $params['resumableFilename'] ?? '';
        $totalSize = (int)($params['resumableTotalSize'] ?? 0);
        
        if (empty($uploadId) || $chunkNumber <= 0 || $totalChunks <= 0) {
            return ['success' => false, 'error' => '参数错误'];
        }
        
        // 验证文件大小（5GB 限制）
        $maxSize = 5 * 1024 * 1024 * 1024;
        if ($totalSize > $maxSize) {
            return ['success' => false, 'error' => '文件过大，最大支持 5GB'];
        }
        
        // 清理文件名，防止路径遍历
        $fileName = basename($fileName);
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        
        // 验证文件扩展名
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', 'm4v'];
        if (!in_array($ext, $allowedExts)) {
            return ['success' => false, 'error' => '不支持的文件格式'];
        }
        
        // 验证 MIME 类型
        if (!$this->validateMimeType($file['tmp_name'])) {
            return ['success' => false, 'error' => '文件类型验证失败'];
        }
        
        // 创建分片目录
        $chunkPath = $this->chunkDir . $uploadId . '/';
        if (!is_dir($chunkPath)) {
            mkdir($chunkPath, 0755, true);
        }
        
        // 保存分片
        $chunkFile = $chunkPath . $chunkNumber;
        if (!move_uploaded_file($file['tmp_name'], $chunkFile)) {
            return ['success' => false, 'error' => '保存分片失败'];
        }
        
        // 记录分片信息
        $this->saveChunkInfo($uploadId, $chunkNumber, $totalChunks, $fileName, $totalSize);
        
        // 检查是否全部上传完成
        if ($this->isUploadComplete($uploadId, $totalChunks)) {
            $finalPath = $this->mergeChunks($uploadId, $fileName);
            if ($finalPath) {
                return [
                    'success' => true,
                    'complete' => true,
                    'file' => $finalPath,
                    'filename' => $fileName,
                ];
            } else {
                return ['success' => false, 'error' => '合并分片失败'];
            }
        }
        
        return [
            'success' => true,
            'complete' => false,
            'chunk' => $chunkNumber,
            'total' => $totalChunks,
        ];
    }
    
    /**
     * 验证文件 MIME 类型
     */
    private function validateMimeType(string $filePath): bool
    {
        if (!function_exists('finfo_open')) {
            // 如果 fileinfo 扩展不可用，跳过验证
            return true;
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        $allowedMimes = [
            'video/mp4',
            'video/x-msvideo',
            'video/x-matroska',
            'video/quicktime',
            'video/x-ms-wmv',
            'video/x-flv',
            'video/webm',
            'application/octet-stream', // 某些视频文件可能返回此类型
        ];
        
        return in_array($mimeType, $allowedMimes);
    }
    
    /**
     * 检查分片是否已存在（断点续传）
     */
    public function checkChunk(array $params): bool
    {
        $uploadId = $params['resumableIdentifier'] ?? '';
        $chunkNumber = (int)($params['resumableChunkNumber'] ?? 0);
        
        if (empty($uploadId) || $chunkNumber <= 0) {
            return false;
        }
        
        $chunkFile = $this->chunkDir . $uploadId . '/' . $chunkNumber;
        return file_exists($chunkFile);
    }
    
    /**
     * 保存分片信息到数据库
     */
    private function saveChunkInfo(string $uploadId, int $chunkIndex, int $totalChunks, string $fileName, int $fileSize): void
    {
        $table = DB_PREFIX . 'upload_chunk';
        
        // 检查是否已存在
        $exists = $this->db->queryOne(
            "SELECT chunk_id FROM {$table} WHERE upload_id = ? AND chunk_index = ?",
            [$uploadId, $chunkIndex]
        );
        
        if ($exists) {
            // 更新
            $this->db->execute(
                "UPDATE {$table} SET chunk_path = ?, total_chunks = ?, file_name = ?, file_size = ? WHERE chunk_id = ?",
                [$this->chunkDir . $uploadId . '/' . $chunkIndex, $totalChunks, $fileName, $fileSize, $exists['chunk_id']]
            );
        } else {
            // 插入
            $this->db->execute(
                "INSERT INTO {$table} (upload_id, chunk_index, chunk_path, total_chunks, file_name, file_size, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$uploadId, $chunkIndex, $this->chunkDir . $uploadId . '/' . $chunkIndex, $totalChunks, $fileName, $fileSize, time()]
            );
        }
    }
    
    /**
     * 检查上传是否完成
     */
    private function isUploadComplete(string $uploadId, int $totalChunks): bool
    {
        $chunkPath = $this->chunkDir . $uploadId . '/';
        
        for ($i = 1; $i <= $totalChunks; $i++) {
            if (!file_exists($chunkPath . $i)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 合并分片
     */
    private function mergeChunks(string $uploadId, string $fileName): ?string
    {
        $chunkPath = $this->chunkDir . $uploadId . '/';
        
        // 获取分片信息
        $table = DB_PREFIX . 'upload_chunk';
        $info = $this->db->queryOne(
            "SELECT * FROM {$table} WHERE upload_id = ? LIMIT 1",
            [$uploadId]
        );
        
        if (!$info) {
            return null;
        }
        
        $totalChunks = $info['total_chunks'];
        
        // 生成唯一文件名
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = date('Ymd') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $finalPath = $this->uploadDir . $newFileName;
        
        // 设置更长的超时时间和内存限制
        set_time_limit(0);
        $originalMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '512M');
        
        try {
            // 使用流式合并，避免内存溢出
            $fp = fopen($finalPath, 'wb');
            if (!$fp) {
                return null;
            }
            
            for ($i = 1; $i <= $totalChunks; $i++) {
                $chunkFile = $chunkPath . $i;
                if (!file_exists($chunkFile)) {
                    fclose($fp);
                    @unlink($finalPath);
                    return null;
                }
                
                // 使用流式复制，避免一次性读取整个分片
                $chunkFp = fopen($chunkFile, 'rb');
                if ($chunkFp) {
                    stream_copy_to_stream($chunkFp, $fp, 1024 * 1024); // 每次复制 1MB
                    fclose($chunkFp);
                } else {
                    fclose($fp);
                    @unlink($finalPath);
                    return null;
                }
            }
            
            fclose($fp);
            
            // 清理分片
            $this->cleanupChunks($uploadId);
            
            return $finalPath;
        } finally {
            // 恢复原始内存限制
            ini_set('memory_limit', $originalMemoryLimit);
        }
    }
    
    /**
     * 清理分片文件
     */
    public function cleanupChunks(string $uploadId): void
    {
        $chunkPath = $this->chunkDir . $uploadId . '/';
        
        if (is_dir($chunkPath)) {
            $files = glob($chunkPath . '*');
            foreach ($files as $file) {
                @unlink($file);
            }
            @rmdir($chunkPath);
        }
        
        // 删除数据库记录
        $table = DB_PREFIX . 'upload_chunk';
        $this->db->execute("DELETE FROM {$table} WHERE upload_id = ?", [$uploadId]);
    }
    
    /**
     * 清理过期分片（超过24小时）
     */
    public function cleanupExpired(): int
    {
        $table = DB_PREFIX . 'upload_chunk';
        $expireTime = time() - 86400;
        
        // 获取过期的上传
        $expired = $this->db->query(
            "SELECT DISTINCT upload_id FROM {$table} WHERE created_at < ?",
            [$expireTime]
        );
        
        $count = 0;
        foreach ($expired as $row) {
            $this->cleanupChunks($row['upload_id']);
            $count++;
        }
        
        return $count;
    }
}
