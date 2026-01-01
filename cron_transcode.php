<?php
/**
 * 转码定时任务
 * Powered by https://xpornkit.com
 * 
 * Crontab 配置（每分钟执行）:
 * * * * * * php /www/site/cron_transcode.php >> /www/site/runtime/transcode.log 2>&1
 * 
 * 或使用宝塔面板的计划任务
 */

// 防止重复执行
$lockFile = __DIR__ . '/runtime/transcode.lock';
if (file_exists($lockFile)) {
    $lockTime = (int)file_get_contents($lockFile);
    // 锁定超过30分钟则认为是异常，清除锁
    if (time() - $lockTime < 1800) {
        echo date('Y-m-d H:i:s') . " - 任务正在执行中，跳过\n";
        exit;
    }
}

// 创建锁
file_put_contents($lockFile, time());

// 注册退出时清除锁
register_shutdown_function(function() use ($lockFile) {
    @unlink($lockFile);
});

// 加载配置
require_once __DIR__ . '/config/config.php';
require_once CORE_PATH . 'Database.php';
require_once MODEL_PATH . 'Model.php';
require_once MODEL_PATH . 'Transcode.php';
require_once CORE_PATH . 'Transcoder.php';

echo date('Y-m-d H:i:s') . " - 开始检查转码任务\n";

$db = XpkDatabase::getInstance();
$transcodeModel = new XpkTranscode();
$transcoder = new XpkTranscoder();

// 检查 FFmpeg
if (!$transcoder->isAvailable()) {
    echo date('Y-m-d H:i:s') . " - 错误: FFmpeg 不可用\n";
    exit(1);
}

// 获取待处理任务
$task = $transcodeModel->getPendingTask();

if (!$task) {
    echo date('Y-m-d H:i:s') . " - 没有待处理的任务\n";
    exit(0);
}

echo date('Y-m-d H:i:s') . " - 开始处理任务 ID:{$task['transcode_id']}\n";
echo "  源文件: {$task['source_file']}\n";

// 检查源文件
if (!file_exists($task['source_file'])) {
    $transcodeModel->updateStatus($task['transcode_id'], XpkTranscode::STATUS_FAILED, [
        'error_msg' => '源文件不存在'
    ]);
    echo date('Y-m-d H:i:s') . " - 错误: 源文件不存在\n";
    exit(1);
}

// 标记为处理中
$transcodeModel->updateStatus($task['transcode_id'], XpkTranscode::STATUS_PROCESSING);

// 获取视频信息
echo date('Y-m-d H:i:s') . " - 获取视频信息...\n";
$info = $transcoder->getVideoInfo($task['source_file']);

if (isset($info['error'])) {
    $transcodeModel->updateStatus($task['transcode_id'], XpkTranscode::STATUS_FAILED, [
        'error_msg' => $info['error']
    ]);
    echo date('Y-m-d H:i:s') . " - 错误: {$info['error']}\n";
    exit(1);
}

echo "  时长: " . gmdate('H:i:s', $info['duration']) . "\n";
echo "  分辨率: {$info['width']}x{$info['height']}\n";
echo "  编码: {$info['video_codec']}/{$info['audio_codec']}\n";

// 更新视频信息
$transcodeModel->update($task['transcode_id'], [
    'duration' => $info['duration'],
    'resolution' => $info['width'] . 'x' . $info['height'],
    'bitrate' => $info['bitrate'],
]);

// 执行转码
echo date('Y-m-d H:i:s') . " - 开始转码...\n";

$keyUrl = rtrim(SITE_URL, '/') . '/api.php?action=transcode.key&id=' . $task['transcode_id'];

$result = $transcoder->transcodeToHLS($task['source_file'], $task['output_dir'], [
    'key_url' => $keyUrl,
    'encrypt' => true,
    'segment_time' => 10,
    'preset' => 'fast',
    'crf' => 23,
]);

if ($result['success']) {
    // 计算相对路径
    $m3u8Url = str_replace(ROOT_PATH, '/', $result['m3u8']);
    
    $transcodeModel->updateStatus($task['transcode_id'], XpkTranscode::STATUS_COMPLETED, [
        'transcode_progress' => 100,
        'encrypt_key' => $result['key'],
        'm3u8_url' => $m3u8Url,
    ]);
    
    echo date('Y-m-d H:i:s') . " - 转码完成!\n";
    echo "  m3u8: {$m3u8Url}\n";
    
    // 可选：删除源文件节省空间
    // @unlink($task['source_file']);
    
} else {
    $transcodeModel->updateStatus($task['transcode_id'], XpkTranscode::STATUS_FAILED, [
        'error_msg' => $result['error'] ?: '转码失败'
    ]);
    
    echo date('Y-m-d H:i:s') . " - 转码失败: {$result['error']}\n";
    exit(1);
}

echo date('Y-m-d H:i:s') . " - 任务完成\n";
