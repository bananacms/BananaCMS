<?php
/**
 * 云存储类（支持 Local / Cloudflare R2）
 * R2 兼容 S3 API
 * Powered by https://xpornkit.com
 */

interface XpkStorageDriver
{
    public function upload(string $localPath, string $remotePath): string;
    public function uploadContent(string $content, string $remotePath): string;
    public function delete(string $remotePath): bool;
    public function exists(string $remotePath): bool;
    public function getUrl(string $remotePath): string;
}

/**
 * 本地存储驱动
 */
class XpkLocalStorage implements XpkStorageDriver
{
    private string $basePath;
    private string $baseUrl;

    public function __construct()
    {
        $this->basePath = UPLOAD_PATH;
        $this->baseUrl = rtrim(SITE_URL, '/') . '/upload/';
    }

    public function upload(string $localPath, string $remotePath): string
    {
        $targetPath = $this->basePath . ltrim($remotePath, '/');
        $dir = dirname($targetPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (copy($localPath, $targetPath)) return $this->getUrl($remotePath);
        throw new Exception('文件保存失败');
    }

    public function uploadContent(string $content, string $remotePath): string
    {
        $targetPath = $this->basePath . ltrim($remotePath, '/');
        $dir = dirname($targetPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (file_put_contents($targetPath, $content) !== false) return $this->getUrl($remotePath);
        throw new Exception('文件保存失败');
    }

    public function delete(string $remotePath): bool
    {
        $targetPath = $this->basePath . ltrim($remotePath, '/');
        return !file_exists($targetPath) || unlink($targetPath);
    }

    public function exists(string $remotePath): bool
    {
        return file_exists($this->basePath . ltrim($remotePath, '/'));
    }

    public function getUrl(string $remotePath): string
    {
        return $this->baseUrl . ltrim($remotePath, '/');
    }
}


/**
 * Cloudflare R2 存储驱动（S3兼容API）
 */
class XpkR2Storage implements XpkStorageDriver
{
    private string $accountId;
    private string $accessKeyId;
    private string $secretAccessKey;
    private string $bucket;
    private string $publicUrl;
    private string $endpoint;
    private string $region = 'auto';

    public function __construct(array $config = [])
    {
        $this->accountId = $config['account_id'] ?? (defined('R2_ACCOUNT_ID') ? R2_ACCOUNT_ID : '');
        $this->accessKeyId = $config['access_key_id'] ?? (defined('R2_ACCESS_KEY_ID') ? R2_ACCESS_KEY_ID : '');
        $this->secretAccessKey = $config['secret_access_key'] ?? (defined('R2_SECRET_ACCESS_KEY') ? R2_SECRET_ACCESS_KEY : '');
        $this->bucket = $config['bucket'] ?? (defined('R2_BUCKET') ? R2_BUCKET : '');
        $this->publicUrl = rtrim($config['public_url'] ?? (defined('R2_PUBLIC_URL') ? R2_PUBLIC_URL : ''), '/');
        $this->endpoint = "https://{$this->accountId}.r2.cloudflarestorage.com";

        if (empty($this->accountId) || empty($this->accessKeyId) || empty($this->secretAccessKey) || empty($this->bucket)) {
            throw new Exception('R2配置不完整');
        }
    }

    public function upload(string $localPath, string $remotePath): string
    {
        $content = file_get_contents($localPath);
        if ($content === false) throw new Exception('读取文件失败');
        $contentType = mime_content_type($localPath) ?: 'application/octet-stream';
        return $this->put($remotePath, $content, $contentType);
    }

    public function uploadContent(string $content, string $remotePath): string
    {
        $ext = strtolower(pathinfo($remotePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'gif' => 'image/gif', 'webp' => 'image/webp', 'svg' => 'image/svg+xml',
            'mp4' => 'video/mp4', 'webm' => 'video/webm',
        ];
        return $this->put($remotePath, $content, $mimeTypes[$ext] ?? 'application/octet-stream');
    }

    public function delete(string $remotePath): bool
    {
        $remotePath = ltrim($remotePath, '/');
        $date = gmdate('Ymd\THis\Z');
        $dateShort = gmdate('Ymd');
        $headers = [
            'host' => "{$this->accountId}.r2.cloudflarestorage.com",
            'x-amz-date' => $date,
            'x-amz-content-sha256' => hash('sha256', ''),
        ];
        $signedHeaders = $this->sign('DELETE', "/{$this->bucket}/{$remotePath}", $headers, '', $date, $dateShort);
        
        $ch = curl_init("{$this->endpoint}/{$this->bucket}/{$remotePath}");
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => $this->formatHeaders($signedHeaders),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }

    public function exists(string $remotePath): bool
    {
        $remotePath = ltrim($remotePath, '/');
        $date = gmdate('Ymd\THis\Z');
        $dateShort = gmdate('Ymd');
        $headers = [
            'host' => "{$this->accountId}.r2.cloudflarestorage.com",
            'x-amz-date' => $date,
            'x-amz-content-sha256' => hash('sha256', ''),
        ];
        $signedHeaders = $this->sign('HEAD', "/{$this->bucket}/{$remotePath}", $headers, '', $date, $dateShort);
        
        $ch = curl_init("{$this->endpoint}/{$this->bucket}/{$remotePath}");
        curl_setopt_array($ch, [CURLOPT_NOBODY => true, CURLOPT_HTTPHEADER => $this->formatHeaders($signedHeaders), CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code === 200;
    }

    public function getUrl(string $remotePath): string
    {
        $remotePath = ltrim($remotePath, '/');
        return $this->publicUrl ? "{$this->publicUrl}/{$remotePath}" : "{$this->endpoint}/{$this->bucket}/{$remotePath}";
    }


    private function put(string $remotePath, string $content, string $contentType): string
    {
        $remotePath = ltrim($remotePath, '/');
        $date = gmdate('Ymd\THis\Z');
        $dateShort = gmdate('Ymd');
        $contentHash = hash('sha256', $content);

        $headers = [
            'content-length' => strlen($content),
            'content-type' => $contentType,
            'host' => "{$this->accountId}.r2.cloudflarestorage.com",
            'x-amz-content-sha256' => $contentHash,
            'x-amz-date' => $date,
        ];

        $signedHeaders = $this->sign('PUT', "/{$this->bucket}/{$remotePath}", $headers, $content, $date, $dateShort);
        
        $ch = curl_init("{$this->endpoint}/{$this->bucket}/{$remotePath}");
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $content,
            CURLOPT_HTTPHEADER => $this->formatHeaders($signedHeaders),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300) return $this->getUrl($remotePath);
        throw new Exception("R2上传失败: HTTP {$code}");
    }

    private function sign(string $method, string $uri, array $headers, string $payload, string $date, string $dateShort): array
    {
        ksort($headers);
        $signedHeaderNames = implode(';', array_keys($headers));
        $canonicalHeaders = '';
        foreach ($headers as $k => $v) $canonicalHeaders .= "{$k}:{$v}\n";

        $payloadHash = hash('sha256', $payload);
        $canonicalRequest = "{$method}\n{$uri}\n\n{$canonicalHeaders}\n{$signedHeaderNames}\n{$payloadHash}";

        $scope = "{$dateShort}/{$this->region}/s3/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$date}\n{$scope}\n" . hash('sha256', $canonicalRequest);

        $kDate = hash_hmac('sha256', $dateShort, "AWS4{$this->secretAccessKey}", true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $headers['authorization'] = "AWS4-HMAC-SHA256 Credential={$this->accessKeyId}/{$scope}, SignedHeaders={$signedHeaderNames}, Signature={$signature}";
        return $headers;
    }

    private function formatHeaders(array $headers): array
    {
        $result = [];
        foreach ($headers as $k => $v) $result[] = "{$k}: {$v}";
        return $result;
    }
}


/**
 * 存储管理器
 */
class XpkStorage
{
    private static ?XpkStorage $instance = null;
    private XpkStorageDriver $driver;
    private string $driverType;

    private function __construct()
    {
        $this->initDriver();
    }

    private function initDriver(): void
    {
        if (defined('STORAGE_DRIVER') && STORAGE_DRIVER === 'r2') {
            $this->driver = new XpkR2Storage();
            $this->driverType = 'r2';
        } else {
            $this->driver = new XpkLocalStorage();
            $this->driverType = 'local';
        }
    }

    public static function getInstance(): XpkStorage
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function upload(string $localPath, string $remotePath = ''): string
    {
        if (empty($remotePath)) {
            $ext = pathinfo($localPath, PATHINFO_EXTENSION);
            $remotePath = date('Y/m/d/') . md5(uniqid() . microtime()) . '.' . $ext;
        }
        return $this->driver->upload($localPath, $remotePath);
    }

    public function uploadContent(string $content, string $remotePath): string
    {
        return $this->driver->uploadContent($content, $remotePath);
    }

    public function uploadBase64(string $base64, string $ext = 'png'): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            $ext = $matches[1];
            $base64 = substr($base64, strpos($base64, ',') + 1);
        }
        $content = base64_decode($base64);
        if ($content === false) throw new Exception('Base64解码失败');
        $remotePath = date('Y/m/d/') . md5(uniqid() . microtime()) . '.' . $ext;
        return $this->driver->uploadContent($content, $remotePath);
    }

    public function delete(string $remotePath): bool
    {
        return $this->driver->delete($remotePath);
    }

    public function exists(string $remotePath): bool
    {
        return $this->driver->exists($remotePath);
    }

    public function getUrl(string $remotePath): string
    {
        return $this->driver->getUrl($remotePath);
    }

    public function getDriverType(): string
    {
        return $this->driverType;
    }
}

/**
 * 快捷函数
 */
function xpk_storage(): XpkStorage
{
    return XpkStorage::getInstance();
}
