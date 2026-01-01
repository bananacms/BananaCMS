<?php
/**
 * 后台转码管理控制器
 * Powered by https://xpornkit.com
 */

class AdminTranscodeController extends AdminBaseController
{
    private XpkTranscode $transcodeModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Transcode.php';
        $this->transcodeModel = new XpkTranscode();
    }

    /**
     * 转码任务列表
     */
    public function index(): void
    {
        $page = (int)($this->get('page', 1));
        $status = $this->get('status', '');
        
        $statusFilter = $status !== '' ? (int)$status : null;
        $result = $this->transcodeModel->getListPaged($page, 20, $statusFilter);
        
        // 获取统计数据
        $stats = $this->transcodeModel->getStats();
        
        // 检查 FFmpeg 状态
        require_once CORE_PATH . 'Transcoder.php';
        $transcoder = new XpkTranscoder();
        $ffmpegAvailable = $transcoder->isAvailable();
        $ffmpegVersion = $ffmpegAvailable ? $transcoder->getVersion() : '';

        $this->assign('list', $result['list']);
        $this->assign('total', $result['total']);
        $this->assign('page', $result['page']);
        $this->assign('pageSize', $result['pageSize']);
        $this->assign('totalPages', $result['totalPages']);
        $this->assign('status', $status);
        $this->assign('stats', $stats);
        $this->assign('ffmpegAvailable', $ffmpegAvailable);
        $this->assign('ffmpegVersion', $ffmpegVersion);
        $this->assign('flash', $this->getFlash());
        $this->assign('csrfToken', $this->csrfToken());

        $this->render('transcode/index', '云转码');
    }

    /**
     * 上传页面
     */
    public function upload(): void
    {
        // 检查 FFmpeg
        require_once CORE_PATH . 'Transcoder.php';
        $transcoder = new XpkTranscoder();
        
        if (!$transcoder->isAvailable()) {
            $this->flash('error', 'FFmpeg 未安装或不可用，请先安装 FFmpeg');
            $this->redirect('/admin.php/transcode');
            return;
        }

        $this->assign('csrfToken', $this->csrfToken());
        $this->render('transcode/upload', '上传视频');
    }

    /**
     * 处理分片上传
     */
    public function doUpload(): void
    {
        require_once CORE_PATH . 'ChunkUpload.php';
        $uploader = new XpkChunkUpload();
        
        // GET 请求检查分片是否存在（断点续传）
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $exists = $uploader->checkChunk($_GET);
            if ($exists) {
                http_response_code(200);
            } else {
                http_response_code(204);
            }
            exit;
        }
        
        // POST 请求处理上传
        if (empty($_FILES['file'])) {
            $this->json(['success' => false, 'error' => '没有上传文件']);
            return;
        }
        
        $result = $uploader->handleChunk($_POST, $_FILES['file']);
        
        // 如果上传完成，创建转码任务
        if ($result['success'] && !empty($result['complete'])) {
            $sourceFile = $result['file'];
            $outputDir = ROOT_PATH . 'upload/hls/' . date('Ymd') . '/' . bin2hex(random_bytes(8)) . '/';
            
            $taskId = $this->transcodeModel->createTask($sourceFile, $outputDir);
            $result['task_id'] = $taskId;
            
            $this->log('上传', '转码', "创建转码任务 ID:{$taskId}");
        }
        
        $this->json($result);
    }

    /**
     * 获取任务状态
     */
    public function status(): void
    {
        $id = (int)$this->get('id', 0);
        
        if ($id <= 0) {
            $this->json(['success' => false, 'error' => '参数错误']);
            return;
        }
        
        $task = $this->transcodeModel->find($id);
        if (!$task) {
            $this->json(['success' => false, 'error' => '任务不存在']);
            return;
        }
        
        // 如果正在处理，尝试获取实时进度
        if ($task['transcode_status'] == XpkTranscode::STATUS_PROCESSING) {
            $logFile = $task['output_dir'] . 'transcode.log';
            if (file_exists($logFile) && $task['duration'] > 0) {
                require_once CORE_PATH . 'Transcoder.php';
                $transcoder = new XpkTranscoder();
                $progress = $transcoder->getProgress($logFile, $task['duration']);
                if ($progress > $task['transcode_progress']) {
                    $task['transcode_progress'] = $progress;
                }
            }
        }
        
        $this->json([
            'success' => true,
            'data' => [
                'id' => $task['transcode_id'],
                'status' => $task['transcode_status'],
                'progress' => $task['transcode_progress'],
                'm3u8_url' => $task['m3u8_url'],
                'error_msg' => $task['error_msg'],
            ]
        ]);
    }

    /**
     * 重试失败任务
     */
    public function retry(): void
    {
        $id = (int)$this->post('id', 0);
        
        if ($id <= 0) {
            $this->error('参数错误');
        }
        
        if ($this->transcodeModel->retry($id)) {
            $this->log('重试', '转码', "任务 ID:{$id}");
            $this->success('已加入队列');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 删除任务
     */
    public function delete(): void
    {
        $id = (int)$this->post('id', 0);
        $deleteFiles = (bool)$this->post('delete_files', true);
        
        if ($id <= 0) {
            $this->error('参数错误');
        }
        
        if ($this->transcodeModel->deleteTask($id, $deleteFiles)) {
            $this->log('删除', '转码', "任务 ID:{$id}");
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 批量删除
     */
    public function batchDelete(): void
    {
        $ids = $_POST['ids'] ?? [];
        $deleteFiles = (bool)$this->post('delete_files', true);
        
        if (empty($ids) || !is_array($ids)) {
            $this->error('请选择任务');
        }
        
        $count = 0;
        foreach ($ids as $id) {
            if ($this->transcodeModel->deleteTask((int)$id, $deleteFiles)) {
                $count++;
            }
        }
        
        $this->log('批量删除', '转码', "删除 {$count} 个任务");
        $this->success("删除成功，共 {$count} 个");
    }

    /**
     * 手动触发转码（通过后台执行 cron 脚本，避免 PHP 超时）
     */
    public function process(): void
    {
        $id = (int)$this->post('id', 0);
        
        $task = $id > 0 
            ? $this->transcodeModel->find($id)
            : $this->transcodeModel->getPendingTask();
        
        if (!$task) {
            $this->error('没有待处理的任务');
        }
        
        // 检查任务状态
        if ($task['transcode_status'] == XpkTranscode::STATUS_PROCESSING) {
            $this->error('任务正在处理中');
        }
        if ($task['transcode_status'] == XpkTranscode::STATUS_COMPLETED) {
            $this->error('任务已完成');
        }
        
        // 确保任务状态为待处理
        if ($task['transcode_status'] != XpkTranscode::STATUS_PENDING) {
            $this->transcodeModel->updateStatus($task['transcode_id'], XpkTranscode::STATUS_PENDING, [
                'error_msg' => ''
            ]);
        }
        
        // 后台执行 cron 脚本（不等待结果）
        $cronScript = ROOT_PATH . 'cron_transcode.php';
        $logFile = RUNTIME_PATH . 'transcode_manual.log';
        
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows: 使用 start /B 后台执行
            $cmd = sprintf('start /B php "%s" >> "%s" 2>&1', $cronScript, $logFile);
            pclose(popen($cmd, 'r'));
        } else {
            // Linux: 使用 nohup 后台执行
            $cmd = sprintf('nohup php "%s" >> "%s" 2>&1 &', $cronScript, $logFile);
            exec($cmd);
        }
        
        $this->log('触发转码', '转码', "任务 ID:{$task['transcode_id']}");
        $this->success('已触发转码，请稍后刷新查看状态');
    }
    
    /**
     * 轮询获取转码进度（供前端 JS 调用）
     */
    public function progress(): void
    {
        $id = (int)$this->get('id', 0);
        
        if ($id <= 0) {
            $this->json(['success' => false, 'error' => '参数错误']);
            return;
        }
        
        $task = $this->transcodeModel->find($id);
        if (!$task) {
            $this->json(['success' => false, 'error' => '任务不存在']);
            return;
        }
        
        // 如果正在处理，尝试从日志获取实时进度
        $progress = $task['transcode_progress'];
        if ($task['transcode_status'] == XpkTranscode::STATUS_PROCESSING && $task['duration'] > 0) {
            $logFile = $task['output_dir'] . 'transcode.log';
            if (file_exists($logFile)) {
                require_once CORE_PATH . 'Transcoder.php';
                $transcoder = new XpkTranscoder();
                $realProgress = $transcoder->getProgress($logFile, $task['duration']);
                if ($realProgress > $progress) {
                    $progress = $realProgress;
                }
            }
        }
        
        $this->json([
            'success' => true,
            'data' => [
                'id' => $task['transcode_id'],
                'status' => $task['transcode_status'],
                'progress' => $progress,
                'error_msg' => $task['error_msg'],
                'm3u8_url' => $task['m3u8_url'],
            ]
        ]);
    }

    /**
     * 获取播放地址（带签名）
     */
    public function play(): void
    {
        $id = (int)$this->get('id', 0);
        
        $task = $this->transcodeModel->find($id);
        if (!$task || $task['transcode_status'] != XpkTranscode::STATUS_COMPLETED) {
            $this->json(['success' => false, 'error' => '视频不存在或未完成转码']);
            return;
        }
        
        // 生成带签名的 m3u8 URL
        $time = time();
        $token = md5($id . $time . (defined('ENCRYPT_SECRET') ? ENCRYPT_SECRET : 'xpk_secret'));
        $m3u8Url = rtrim(SITE_URL, '/') . '/api.php?action=transcode.m3u8&id=' . $id . '&t=' . $time . '&token=' . $token;
        
        $this->json([
            'success' => true,
            'data' => [
                'm3u8' => $m3u8Url,
                'duration' => $task['duration'],
                'resolution' => $task['resolution'],
            ]
        ]);
    }
}
