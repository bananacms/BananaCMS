<?php
/**
 * IndexNow Protocol Implementation
 * Instantly notify search engines about content changes
 * Powered by https://xpornkit.com
 */

class XpkIndexNow
{
    private $apiKey;
    private $host;
    private $enabled;
    
    // IndexNow API endpoints
    const ENDPOINT_BING = 'https://api.bing.microsoft.com/indexnow';
    const ENDPOINT_YANDEX = 'https://yandex.com/indexnow';
    
    public function __construct()
    {
        $db = XpkDatabase::getInstance();
        $config = $db->query("SELECT config_name, config_value FROM " . DB_PREFIX . "config WHERE config_name LIKE 'indexnow_%'");
        
        $configMap = [];
        foreach ($config as $item) {
            $configMap[$item['config_name']] = $item['config_value'];
        }
        
        $this->enabled = !empty($configMap['indexnow_enabled']) && $configMap['indexnow_enabled'] == '1';
        $this->apiKey = $configMap['indexnow_api_key'] ?? '';
        $this->host = $configMap['indexnow_host'] ?? SITE_URL;
        
        // Remove protocol and path from host
        $this->host = preg_replace('#^https?://#', '', $this->host);
        $this->host = preg_replace('#/.*$#', '', $this->host); // Remove path
        $this->host = rtrim($this->host, '/');
    }
    
    /**
     * Submit single URL to IndexNow
     */
    public function submitUrl(string $url): bool
    {
        if (!$this->enabled || empty($this->apiKey)) {
            return false;
        }
        
        return $this->submitUrls([$url]);
    }
    
    /**
     * Submit multiple URLs to IndexNow (batch)
     */
    public function submitUrls(array $urls): bool
    {
        if (!$this->enabled || empty($this->apiKey) || empty($urls)) {
            return false;
        }
        
        // Normalize URLs
        $normalizedUrls = [];
        foreach ($urls as $url) {
            // Ensure full URL
            if (!preg_match('#^https?://#', $url)) {
                $url = 'https://' . $this->host . '/' . ltrim($url, '/');
            }
            $normalizedUrls[] = $url;
        }
        
        // Prepare payload
        $payload = [
            'host' => $this->host,
            'key' => $this->apiKey,
            'urlList' => $normalizedUrls
        ];
        
        // Submit to Bing (will propagate to other search engines)
        return $this->sendRequest(self::ENDPOINT_BING, $payload);
    }
    
    /**
     * Send HTTP request to IndexNow API
     */
    private function sendRequest(string $endpoint, array $payload): bool
    {
        if (!function_exists('curl_init')) {
            error_log('IndexNow: cURL extension not available');
            return false;
        }
        
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json; charset=utf-8'
            ],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // HTTP 200 or 202 means success
        if ($httpCode == 200 || $httpCode == 202) {
            error_log('IndexNow: Successfully submitted ' . count($payload['urlList']) . ' URLs');
            return true;
        }
        
        error_log("IndexNow: Failed with HTTP {$httpCode}. Error: {$error}");
        return false;
    }
    
    /**
     * Generate API key file content
     */
    public function generateKeyFileContent(): string
    {
        return $this->apiKey;
    }
    
    /**
     * Get key file path
     */
    public function getKeyFilePath(): string
    {
        return ROOT_PATH . $this->apiKey . '.txt';
    }
    
    /**
     * Create or update key file
     */
    public function createKeyFile(): bool
    {
        if (empty($this->apiKey)) {
            return false;
        }
        
        // Validate API key format (prevent path traversal)
        if (!preg_match('/^[a-f0-9]{32}$/i', $this->apiKey)) {
            error_log('IndexNow: Invalid API key format');
            return false;
        }
        
        $filePath = $this->getKeyFilePath();
        
        // Additional security check: ensure file is in root directory
        $realPath = realpath(dirname($filePath));
        $rootPath = realpath(ROOT_PATH);
        if ($realPath !== $rootPath) {
            error_log('IndexNow: Security violation - attempted path traversal');
            return false;
        }
        
        $result = file_put_contents($filePath, $this->apiKey);
        if ($result !== false) {
            @chmod($filePath, 0644); // Ensure readable by web server
            return true;
        }
        return false;
    }
    
    /**
     * Generate random API key
     */
    public static function generateApiKey(): string
    {
        return bin2hex(random_bytes(16));
    }
}

/**
 * Helper function to submit URL to IndexNow
 */
function xpk_indexnow_submit($url)
{
    try {
        $indexNow = new XpkIndexNow();
        return $indexNow->submitUrl($url);
    } catch (Exception $e) {
        error_log('IndexNow error: ' . $e->getMessage());
        return false;
    }
}
