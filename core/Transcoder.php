<?php
/**
 * 视频转码器
 * Powered by https://xpornkit.com
 */

class XpkTranscoder
{
    private string $ffmpeg = 'ffmpeg';
    private string $ffprobe = 'ffprobe';
    private string $logFile = '';
    
    public function __construct()
    {
        // 尝试常见路径
        $paths = [
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            'C:\\ffmpeg\\bin\\ffmpeg.exe',
            'ffmpeg', // PATH 中
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
     * 检查命令是否存在
     */
    private function commandExists(string $cmd): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $return = shell_exec(sprintf("where %s 2>nul", escapeshellarg($cmd)));
        } else {
            $return = shell_exec(sprintf("which %s 2>/dev/null", escapeshellarg($cmd)));
        }
        return !empty(trim($return ?? ''));
    }
    
    /**
     * 检查 FFmpeg 是否可用
     */
    public function isAvailable(): bool
    {
        exec($this->ffmpeg . ' -version 2>&1', $output, $code);
        return $code === 0;
    }
    
    /**
     * 获取 FFmpeg 版本
     */
    public function getVersion(): string
    {
        exec($this->ffmpeg . ' -version 2>&1', $output);
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
            $this->ffprobe, escapeshellarg($file));
        
        exec($cmd, $output, $code);
        
        if ($code !== 0) {
            return ['error' => '无法读取视频信息'];
        }
        
        $info = json_decode(implode('', $output), true);
        if (!$info) {
            return ['error' => '解析视频信息失败'];
        }
        
        // 提取关键信息
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
    
    /**
     * 解析帧率
     */
    private function parseFps(string $fps): float
    {
        if (strpos($fps, '/') !== false) {
            [$num, $den] = explode('/', $fps);
            return $den > 0 ? round($num / $den, 2) : 0;
        }
        return (float)$fps;
    }

    /**
     * 转码为加密 HLS
     */
    public function transcodeToHLS(string $input, string $outputDir, array $options = []): array
    {
        // 默认选项
        $segmentTime = $options['segment_time'] ?? 10;
        $encrypt = $options['encrypt'] ?? true;
        $keyUrl = $options['key_url'] ?? '';
        $preset = $options['preset'] ?? 'fast';
        $crf = $options['crf'] ?? 23;
        $audioBitrate = $options['audio_bitrate'] ?? '128k';
        
        // 创建输出目录
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
            
            // key_info 文件格式：
            // 第一行：key URL（播放器请求）
            // 第二行：key 文件路径（FFmpeg 读取）
            $keyInfoContent = $keyUrl . "\n" . $keyFile;
            if (file_put_contents($keyInfo, $keyInfoContent) === false) {
                return ['success' => false, 'error' => '无法写入密钥信息文件'];
            }
        }
        
        // 日志文件
        $this->logFile = $outputDir . '/transcode.log';
        
        // 构建 FFmpeg 命令
        $cmd = sprintf('%s -y -i %s ', $this->ffmpeg, escapeshellarg($input));
        $cmd .= sprintf('-c:v libx264 -preset %s -crf %d ', $preset, $crf);
        $cmd .= sprintf('-c:a aac -b:a %s ', $audioBitrate);
        $cmd .= sprintf('-hls_time %d ', $segmentTime);
        $cmd .= sprintf('-hls_segment_filename %s ', escapeshellarg($outputDir . '/%08x.ts'));
        $cmd .= '-hls_playlist_type vod ';
        $cmd .= '-hls_flags independent_segments ';
        
        if ($encrypt) {
            $cmd .= sprintf('-hls_key_info_file %s ', escapeshellarg($keyInfo));
        }
        
        $cmd .= escapeshellarg($outputDir . '/index.m3u8');
        $cmd .= sprintf(' 2>%s', escapeshellarg($this->logFile));
        
        // 执行转码
        exec($cmd, $output, $returnCode);
        
        // 清理临时文件
        if ($encrypt && file_exists($keyInfo)) {
            @unlink($keyInfo);
        }
        
        // 检查结果
        if ($returnCode === 0 && file_exists($outputDir . '/index.m3u8')) {
            $result['success'] = true;
            $result['key'] = $keyHex;
            $result['m3u8'] = $outputDir . '/index.m3u8';
            
            // 更新 m3u8 中的 key URL（如果需要动态 URL）
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
     * 更新 m3u8 文件中的 key URL
     */
    private function updateM3u8KeyUrl(string $m3u8File, string $keyUrl): void
    {
        $content = file_get_contents($m3u8File);
        if ($content === false) return;
        
        // 替换 key URL
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
        
        // 常见错误模式
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
        
        // 返回最后几行
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
        
        // 匹配 time=00:01:23.45 格式
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
            $this->ffmpeg, escapeshellarg($input), $time, escapeshellarg($output));
        
        exec($cmd, $out, $code);
        return $code === 0 && file_exists($output);
    }
}
