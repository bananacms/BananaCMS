<?php
/**
 * 视频转码器（支持广告合并）
 * Powered by https://xpornkit.com
 */

class XpkTranscoder
{
    private string $ffmpeg = 'ffmpeg';
    private string $ffprobe = 'ffprobe';
    private string $logFile = '';
    private array $allowedDirs = [];
    
    public function __construct()
    {
        // 设置允许的目录白名单
        $this->allowedDirs = [
            realpath(UPLOAD_PATH),
            realpath(RUNTIME_PATH),
        ];
        
        // 尝试常见路径
        $paths = [
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            'C:\\ffmpeg\\bin\\ffmpeg.exe',
            'ffmpeg',
        ];
        
        foreach ($paths as $path) {
            if ($this->commandExists($path)) {
                $this->ffmpeg = $path;
                $this->ffprobe = str_replace('ffmpeg', 'ffprobe', $path);
                break;
            }
        }
    }
    
    /**
     * 验证文件路径是否在允许的目录内
     */
    private function validatePath(string $path): bool
    {
        $realPath = realpath($path);
        if ($realPath === false) {
            return false;
        }
        
        foreach ($this->allowedDirs as $allowedDir) {
            if ($allowedDir && strpos($realPath, $allowedDir) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 安全地转义文件路径
     */
    private function escapePath(string $path): string
    {
        if (!$this->validatePath($path)) {
            throw new Exception('Invalid file path: path outside allowed directories');
        }
        return escapeshellarg($path);
    }
    
    /**
     * 记录命令执行日志
     */
    private function logCommand(string $cmd, int $returnCode): void
    {
        $logFile = RUNTIME_PATH . 'logs/transcode_' . date('Y-m-d') . '.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $log = sprintf(
            "[%s] Return Code: %d\nCommand: %s\n\n",
            date('Y-m-d H:i:s'),
            $returnCode,
            $cmd
        );
        
        @file_put_contents($logFile, $log, FILE_APPEND);
    }
    
    private function commandExists(string $cmd): bool
    {
        // 检查 shell_exec 是否可用
        if (!function_exists('shell_exec') || $this->isShellExecDisabled()) {
            // 如果是完整路径且文件存在，认为命令存在
            if (@file_exists($cmd) && @is_executable($cmd)) {
                return true;
            }
            return false;
        }
        
        if (PHP_OS_FAMILY === 'Windows') {
            $return = @shell_exec(sprintf("where %s 2>nul", escapeshellarg($cmd)));
        } else {
            $return = @shell_exec(sprintf("which %s 2>/dev/null", escapeshellarg($cmd)));
        }
        return !empty(trim($return ?? ''));
    }
    
    /**
     * 检查 shell_exec 是否被禁用
     */
    private function isShellExecDisabled(): bool
    {
        $disabled = ini_get('disable_functions');
        if (empty($disabled)) {
            return false;
        }
        $disabled = array_map('trim', explode(',', strtolower($disabled)));
        return in_array('shell_exec', $disabled);
    }
    
    public function isAvailable(): bool
    {
        // 检查 exec 是否可用
        if (!function_exists('exec') || $this->isExecDisabled()) {
            return false;
        }
        
        $output = [];
        $code = 0;
        @exec($this->ffmpeg . ' -version 2>&1', $output, $code);
        return $code === 0;
    }
    
    /**
     * 检查 exec 是否被禁用
     */
    private function isExecDisabled(): bool
    {
        $disabled = ini_get('disable_functions');
        if (empty($disabled)) {
            return false;
        }
        $disabled = array_map('trim', explode(',', strtolower($disabled)));
        return in_array('exec', $disabled);
    }
    
    public function getVersion(): string
    {
        if (!function_exists('exec') || $this->isExecDisabled()) {
            return 'unknown';
        }
        
        $output = [];
        @exec($this->ffmpeg . ' -version 2>&1', $output);
        if (!empty($output[0]) && preg_match('/ffmpeg version ([^\s]+)/', $output[0], $m)) {
            return $m[1];
        }
        return 'unknown';
    }

    /**
     * 获取视频信息
     */
    public function getVideoInfo(string $file): array
    {
        if (!file_exists($file)) {
            return ['error' => '文件不存在'];
        }
        
        $cmd = sprintf('%s -v quiet -print_format json -show_format -show_streams %s',
            $this->ffprobe, $this->escapePath($file));
        
        exec($cmd, $output, $code);
        
        if ($code !== 0) {
            return ['error' => '无法读取视频信息'];
        }
        
        $info = json_decode(implode('', $output), true);
        if (!$info) {
            return ['error' => '解析视频信息失败'];
        }
        
        $videoStream = null;
        $audioStream = null;
        
        foreach ($info['streams'] ?? [] as $stream) {
            if ($stream['codec_type'] === 'video' && !$videoStream) {
                $videoStream = $stream;
            }
            if ($stream['codec_type'] === 'audio' && !$audioStream) {
                $audioStream = $stream;
            }
        }
        
        return [
            'duration' => (int)($info['format']['duration'] ?? 0),
            'size' => (int)($info['format']['size'] ?? 0),
            'bitrate' => (int)($info['format']['bit_rate'] ?? 0),
            'format' => $info['format']['format_name'] ?? '',
            'width' => $videoStream['width'] ?? 0,
            'height' => $videoStream['height'] ?? 0,
            'video_codec' => $videoStream['codec_name'] ?? '',
            'audio_codec' => $audioStream['codec_name'] ?? '',
            'fps' => $this->parseFps($videoStream['r_frame_rate'] ?? '0/1'),
        ];
    }
    
    private function parseFps(string $fps): float
    {
        if (strpos($fps, '/') !== false) {
            [$num, $den] = explode('/', $fps);
            return $den > 0 ? round($num / $den, 2) : 0;
        }
        return (float)$fps;
    }

    /**
     * 转码为加密 HLS（支持广告合并）
     */
    public function transcodeToHLS(string $input, string $outputDir, array $options = []): array
    {
        $segmentTime = $options['segment_time'] ?? 10;
        $encrypt = $options['encrypt'] ?? true;
        $keyUrl = $options['key_url'] ?? '';
        $preset = $options['preset'] ?? 'fast';
        $crf = $options['crf'] ?? 23;
        $audioBitrate = $options['audio_bitrate'] ?? '128k';
        $ads = $options['ads'] ?? [];  // 广告配置
        
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0755, true)) {
                return ['success' => false, 'error' => '无法创建输出目录'];
            }
        }
        
        $result = [
            'success' => false,
            'key' => '',
            'm3u8' => '',
            'output' => '',
            'error' => '',
        ];
        
        // 生成加密密钥
        $keyHex = '';
        $keyFile = $outputDir . '/encrypt.key';
        $keyInfo = $outputDir . '/key_info.txt';
        
        if ($encrypt) {
            $key = random_bytes(16);
            $keyHex = bin2hex($key);
            
            if (file_put_contents($keyFile, $key) === false) {
                return ['success' => false, 'error' => '无法写入密钥文件'];
            }
            
            $keyInfoContent = $keyUrl . "\n" . $keyFile;
            if (file_put_contents($keyInfo, $keyInfoContent) === false) {
                return ['success' => false, 'error' => '无法写入密钥信息文件'];
            }
        }
        
        $this->logFile = $outputDir . '/transcode.log';
        
        // 判断是否需要合并广告
        $needMerge = !empty($ads['head']) || !empty($ads['middle']) || !empty($ads['tail']);
        
        if ($needMerge) {
            // 有广告，先合并再转码
            $mergedFile = $outputDir . '/merged_temp.mp4';
            $mergeResult = $this->mergeWithAds($input, $mergedFile, $ads, $outputDir);
            
            if (!$mergeResult['success']) {
                return $mergeResult;
            }
            
            $inputForHLS = $mergedFile;
        } else {
            $inputForHLS = $input;
        }
        
        // 构建 HLS 转码命令
        $cmd = sprintf('%s -y -i %s ', $this->ffmpeg, $this->escapePath($inputForHLS));
        $cmd .= sprintf('-c:v libx264 -preset %s -crf %d ', $preset, $crf);
        $cmd .= sprintf('-c:a aac -b:a %s ', $audioBitrate);
        $cmd .= sprintf('-hls_time %d ', $segmentTime);
        $cmd .= sprintf('-hls_segment_filename %s ', $this->escapePath($outputDir . '/%08d.ts'));
        $cmd .= '-hls_playlist_type vod ';
        $cmd .= '-hls_flags independent_segments ';
        
        if ($encrypt) {
            $cmd .= sprintf('-hls_key_info_file %s ', $this->escapePath($keyInfo));
        }
        
        $cmd .= $this->escapePath($outputDir . '/index.m3u8');
        $cmd .= sprintf(' 2>>%s', $this->escapePath($this->logFile));
        
        exec($cmd, $output, $returnCode);
        
        // 记录命令执行
        $this->logCommand($cmd, $returnCode);
        
        // 清理临时文件
        if ($encrypt && file_exists($keyInfo)) {
            @unlink($keyInfo);
        }
        if ($needMerge && file_exists($mergedFile)) {
            @unlink($mergedFile);
        }
        
        if ($returnCode === 0 && file_exists($outputDir . '/index.m3u8')) {
            $result['success'] = true;
            $result['key'] = $keyHex;
            $result['m3u8'] = $outputDir . '/index.m3u8';
            
            if ($encrypt && !empty($keyUrl)) {
                $this->updateM3u8KeyUrl($outputDir . '/index.m3u8', $keyUrl);
            }
        } else {
            $result['error'] = file_exists($this->logFile) 
                ? $this->parseErrorFromLog($this->logFile)
                : '转码失败，未知错误';
        }
        
        $result['output'] = implode("\n", $output);
        
        return $result;
    }

    /**
     * 合并广告和正片
     * @param string $mainVideo 正片路径
     * @param string $output 输出路径
     * @param array $ads 广告配置 ['head' => [...], 'middle' => [...], 'tail' => [...]]
     * @param string $tempDir 临时目录
     */
    public function mergeWithAds(string $mainVideo, string $output, array $ads, string $tempDir): array
    {
        // 获取正片信息
        $mainInfo = $this->getVideoInfo($mainVideo);
        if (isset($mainInfo['error'])) {
            return ['success' => false, 'error' => '无法读取正片信息: ' . $mainInfo['error']];
        }
        
        $mainDuration = $mainInfo['duration'];
        $targetWidth = $mainInfo['width'];
        $targetHeight = $mainInfo['height'];
        
        // 确保分辨率有效
        if ($targetWidth <= 0 || $targetHeight <= 0) {
            return ['success' => false, 'error' => '无法获取视频分辨率'];
        }
        
        // 收集所有需要合并的视频片段
        $segments = [];
        $hasMiddleAd = !empty($ads['middle']) && $mainDuration > 0;
        
        // 1. 片头广告
        if (!empty($ads['head'])) {
            foreach ($ads['head'] as $ad) {
                if (!empty($ad['file']) && file_exists($ad['file'])) {
                    $segments[] = ['type' => 'ad', 'file' => $ad['file']];
                }
            }
        }
        
        // 2. 处理片中广告（需要切割正片）
        if ($hasMiddleAd) {
            $interval = (int)($ads['middle_interval'] ?? 300);
            $middleAds = $ads['middle'];
            
            if ($interval > 0 && !empty($middleAds)) {
                // 计算插入点（至少保留30秒结尾）
                $insertPoints = [];
                for ($t = $interval; $t < $mainDuration - 30; $t += $interval) {
                    $insertPoints[] = $t;
                }
                
                if (!empty($insertPoints)) {
                    $lastEnd = 0;
                    $adIndex = 0;
                    
                    foreach ($insertPoints as $i => $point) {
                        // 正片片段
                        $segmentFile = $tempDir . '/main_part_' . $i . '.mp4';
                        if (!$this->cutVideo($mainVideo, $segmentFile, $lastEnd, $point, $targetWidth, $targetHeight)) {
                            return ['success' => false, 'error' => '切割视频失败: ' . $lastEnd . '-' . $point];
                        }
                        $segments[] = ['type' => 'main', 'file' => $segmentFile, 'temp' => true];
                        
                        // 插入广告（循环使用）
                        $ad = $middleAds[$adIndex % count($middleAds)];
                        if (!empty($ad['file']) && file_exists($ad['file'])) {
                            $segments[] = ['type' => 'ad', 'file' => $ad['file']];
                        }
                        $adIndex++;
                        
                        $lastEnd = $point;
                    }
                    
                    // 最后一段正片
                    $segmentFile = $tempDir . '/main_part_final.mp4';
                    if (!$this->cutVideo($mainVideo, $segmentFile, $lastEnd, $mainDuration, $targetWidth, $targetHeight)) {
                        return ['success' => false, 'error' => '切割视频失败: 最后片段'];
                    }
                    $segments[] = ['type' => 'main', 'file' => $segmentFile, 'temp' => true];
                } else {
                    $segments[] = ['type' => 'main', 'file' => $mainVideo];
                }
            } else {
                $segments[] = ['type' => 'main', 'file' => $mainVideo];
            }
        } else {
            // 没有片中广告，直接添加正片
            $segments[] = ['type' => 'main', 'file' => $mainVideo];
        }
        
        // 3. 片尾广告
        if (!empty($ads['tail'])) {
            foreach ($ads['tail'] as $ad) {
                if (!empty($ad['file']) && file_exists($ad['file'])) {
                    $segments[] = ['type' => 'ad', 'file' => $ad['file']];
                }
            }
        }
        
        // 如果只有正片没有广告，直接复制
        if (count($segments) === 1 && $segments[0]['file'] === $mainVideo) {
            if (copy($mainVideo, $output)) {
                return ['success' => true];
            }
            return ['success' => false, 'error' => '复制文件失败'];
        }
        
        // 使用 concat 协议合并
        $concatResult = $this->concatVideos($segments, $output, $targetWidth, $targetHeight, $tempDir);
        
        // 清理临时切片文件
        foreach ($segments as $seg) {
            if (!empty($seg['temp']) && file_exists($seg['file'])) {
                @unlink($seg['file']);
            }
        }
        
        return $concatResult;
    }

    /**
     * 切割视频片段
     */
    private function cutVideo(string $input, string $output, float $start, float $end, int $width, int $height): bool
    {
        $duration = $end - $start;
        
        // 使用 scale 确保分辨率一致，pad 处理不同宽高比
        $cmd = sprintf(
            '%s -y -ss %.2f -i %s -t %.2f -vf "scale=%d:%d:force_original_aspect_ratio=decrease,pad=%d:%d:(ow-iw)/2:(oh-ih)/2" -c:v libx264 -preset fast -crf 23 -c:a aac -b:a 128k %s 2>&1',
            $this->ffmpeg,
            $start,
            $this->escapePath($input),
            $duration,
            $width, $height, $width, $height,
            $this->escapePath($output)
        );
        
        exec($cmd, $out, $code);
        return $code === 0 && file_exists($output);
    }

    /**
     * 合并多个视频（使用 concat demuxer）
     */
    private function concatVideos(array $segments, string $output, int $width, int $height, string $tempDir): array
    {
        // 先将所有视频统一格式和分辨率
        $normalizedFiles = [];
        $listFile = $tempDir . '/concat_list.txt';
        $listContent = '';
        
        foreach ($segments as $i => $seg) {
            $normalizedFile = $tempDir . '/normalized_' . $i . '.ts';
            
            // 转换为统一格式的 TS（便于无缝拼接）
            $cmd = sprintf(
                '%s -y -i %s -vf "scale=%d:%d:force_original_aspect_ratio=decrease,pad=%d:%d:(ow-iw)/2:(oh-ih)/2,setsar=1" -c:v libx264 -preset fast -crf 23 -c:a aac -b:a 128k -f mpegts %s 2>&1',
                $this->ffmpeg,
                $this->escapePath($seg['file']),
                $width, $height, $width, $height,
                $this->escapePath($normalizedFile)
            );
            
            exec($cmd, $out, $code);
            
            if ($code !== 0 || !file_exists($normalizedFile)) {
                // 清理已创建的文件
                foreach ($normalizedFiles as $f) {
                    @unlink($f);
                }
                return ['success' => false, 'error' => '视频格式转换失败: ' . $seg['file']];
            }
            
            $normalizedFiles[] = $normalizedFile;
            $listContent .= "file '" . str_replace("'", "'\\''", $normalizedFile) . "'\n";
        }
        
        // 写入 concat 列表文件
        file_put_contents($listFile, $listContent);
        
        // 使用 concat demuxer 合并
        $cmd = sprintf(
            '%s -y -f concat -safe 0 -i %s -c copy %s 2>&1',
            $this->ffmpeg,
            $this->escapePath($listFile),
            $this->escapePath($output)
        );
        
        exec($cmd, $out, $code);
        
        // 清理临时文件
        foreach ($normalizedFiles as $f) {
            @unlink($f);
        }
        @unlink($listFile);
        
        if ($code === 0 && file_exists($output)) {
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => '视频合并失败'];
    }

    /**
     * 更新 m3u8 文件中的 key URL
     */
    private function updateM3u8KeyUrl(string $m3u8File, string $keyUrl): void
    {
        $content = file_get_contents($m3u8File);
        if ($content === false) return;
        
        $content = preg_replace(
            '/#EXT-X-KEY:METHOD=AES-128,URI="[^"]*"/',
            '#EXT-X-KEY:METHOD=AES-128,URI="' . $keyUrl . '"',
            $content
        );
        
        file_put_contents($m3u8File, $content);
    }
    
    /**
     * 从日志解析错误信息
     */
    private function parseErrorFromLog(string $logFile): string
    {
        if (!file_exists($logFile)) {
            return '日志文件不存在';
        }
        
        $log = file_get_contents($logFile);
        
        $patterns = [
            '/Invalid data found when processing input/' => '无效的输入文件',
            '/No such file or directory/' => '文件不存在',
            '/Permission denied/' => '权限不足',
            '/Encoder .* not found/' => '编码器不可用',
            '/Error opening input/' => '无法打开输入文件',
            '/Output file is empty/' => '输出文件为空',
        ];
        
        foreach ($patterns as $pattern => $message) {
            if (preg_match($pattern, $log)) {
                return $message;
            }
        }
        
        $lines = explode("\n", trim($log));
        return implode("\n", array_slice($lines, -3));
    }

    /**
     * 获取转码进度
     */
    public function getProgress(string $logFile, int $totalDuration): int
    {
        if (!file_exists($logFile) || $totalDuration <= 0) {
            return 0;
        }
        
        $log = file_get_contents($logFile);
        
        if (preg_match_all('/time=(\d{2}):(\d{2}):(\d{2})\.?\d*/', $log, $matches, PREG_SET_ORDER)) {
            $lastMatch = end($matches);
            $currentTime = $lastMatch[1] * 3600 + $lastMatch[2] * 60 + $lastMatch[3];
            return min(99, (int)($currentTime / $totalDuration * 100));
        }
        
        return 0;
    }
    
    /**
     * 生成视频缩略图
     */
    public function generateThumbnail(string $input, string $output, int $time = 5): bool
    {
        $cmd = sprintf('%s -y -i %s -ss %d -vframes 1 -q:v 2 %s 2>&1',
            $this->ffmpeg, $this->escapePath($input), $time, $this->escapePath($output));
        
        exec($cmd, $out, $code);
        return $code === 0 && file_exists($output);
    }
}
