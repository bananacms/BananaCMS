<?php
/**
 * 转码定时任务（支持广告合并）
 * Powered by https://xpornkit.com
 * 
 * Crontab 配置（每分钟执行）:
 * * * * * * php /www/site/cron_transcode.php >> /www/site/runtime/transcode.log 2>&1
 */

// 防止重复执行
$lockFile = __DIR__ . '/runtime/transcode.lock';
if (file_exists($lockFile)) {
    $lockTime = (int)file_get_contents($lockFile);
    if (time() - $lockTime < 1800) {
        echo date('Y-m-d H:i:s') . " - 任务正在执行中，跳过\n";
        exit;
    }
}

file_put_contents($lockFile, time());

register_shutdown_function(function() use ($lockFile) {
    @unlink($lockFile);
});

// 加载配置
require_once __DIR__ . '/config/config.php';
require_once CORE_PATH . 'Database.php';
require_once CORE_PATH . 'Cache.php';
require_once MODEL_PATH . 'Model.php';
require_once MODEL_PATH . 'Transcode.php';
require_once MODEL_PATH . 'TranscodeAd.php';
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

// 获取广告配置
$adModel = new XpkTranscodeAd();
$adConfig = $adModel->getConfig();
$ads = [];

if (!empty($adConfig['enable'])) {
    echo date('Y-m-d H:i:s') . " - 加载广告配置...\n";
    
    // 片头广告
    if (!empty($adConfig['head_enable'])) {
        $headAds = $adModel->getByPosition('head');
        if (!empty($headAds)) {
            $ads['head'] = [];
            foreach ($headAds as $ad) {
                $filePath = ROOT_PATH . ltrim($ad['ad_file'], '/');
                if (file_exists($filePath)) {
                    $ads['head'][] = ['file' => $filePath, 'duration' => $ad['ad_duration']];
                    echo "  片头广告: {$ad['ad_name']} ({$ad['ad_duration']}秒)\n";
                }
            }
        }
    }
    
    // 片中广告
    if (!empty($adConfig['middle_enable'])) {
        $middleAds = $adModel->getByPosition('middle');
        if (!empty($middleAds)) {
            $ads['middle'] = [];
            $ads['middle_interval'] = $adConfig['middle_interval'] ?? 300;
            foreach ($middleAds as $ad) {
                $filePath = ROOT_PATH . ltrim($ad['ad_file'], '/');
                if (file_exists($filePath)) {
                    $ads['middle'][] = ['file' => $filePath, 'duration' => $ad['ad_duration']];
                    echo "  片中广告: {$ad['ad_name']} ({$ad['ad_duration']}秒)\n";
                }
            }
            echo "  片中广告间隔: {$ads['middle_interval']}秒\n";
        }
    }
    
    // 片尾广告
    if (!empty($adConfig['tail_enable'])) {
        $tailAds = $adModel->getByPosition('tail');
        if (!empty($tailAds)) {
            $ads['tail'] = [];
            foreach ($tailAds as $ad) {
                $filePath = ROOT_PATH . ltrim($ad['ad_file'], '/');
                if (file_exists($filePath)) {
                    $ads['tail'][] = ['file' => $filePath, 'duration' => $ad['ad_duration']];
                    echo "  片尾广告: {$ad['ad_name']} ({$ad['ad_duration']}秒)\n";
                }
            }
        }
    }
}

// 执行转码
echo date('Y-m-d H:i:s') . " - 开始转码...\n";

$keyUrl = rtrim(SITE_URL, '/') . '/api.php?action=transcode.key&id=' . $task['transcode_id'];

$result = $transcoder->transcodeToHLS($task['source_file'], $task['output_dir'], [
    'key_url' => $keyUrl,
    'encrypt' => true,
    'segment_time' => 10,
    'preset' => 'fast',
    'crf' => 23,
    'ads' => $ads,  // 传入广告配置
]);

if ($result['success']) {
    $m3u8Url = str_replace(ROOT_PATH, '/', $result['m3u8']);
    
    $transcodeModel->updateStatus($task['transcode_id'], XpkTranscode::STATUS_COMPLETED, [
        'transcode_progress' => 100,
        'encrypt_key' => $result['key'],
        'm3u8_url' => $m3u8Url,
    ]);
    
    echo date('Y-m-d H:i:s') . " - 转码完成!\n";
    echo "  m3u8: {$m3u8Url}\n";
    
} else {
    $transcodeModel->updateStatus($task['transcode_id'], XpkTranscode::STATUS_FAILED, [
        'error_msg' => $result['error'] ?: '转码失败'
    ]);
    
    echo date('Y-m-d H:i:s') . " - 转码失败: {$result['error']}\n";
    exit(1);
}

echo date('Y-m-d H:i:s') . " - 任务完成\n";
