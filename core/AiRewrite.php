<?php
/**
 * AI 内容改写服务
 * 支持 OpenAI 兼容格式的 API
 * Powered by https://xpornkit.com
 */

class XpkAiRewrite
{
    private string $apiUrl;
    private string $apiKey;
    private string $model;
    private string $systemPrompt;
    private string $userPrompt;
    private float $temperature;
    private int $maxTokens;
    private int $timeout;

    /**
     * 默认系统提示词
     */
    private const DEFAULT_SYSTEM_PROMPT = '你是一位资深的影视编辑，负责撰写影视作品的剧情简介。你的文风自然流畅，像真人写作一样有个人风格，避免使用AI常见的套话和模板化表达。';

    /**
     * 默认用户提示词
     */
    private const DEFAULT_USER_PROMPT = '请用你自己的话重新描述以下影视简介。要求：
1. 保持原意，但换一种说法
2. 语言自然，像人写的，不要有AI味
3. 不要用"本片"、"该剧"、"讲述了"这类套话开头
4. 字数与原文相近
5. 直接输出改写后的内容，不要任何解释

原文：
{content}';

    public function __construct(array $config = [])
    {
        $this->apiUrl = rtrim($config['api_url'] ?? '', '/');
        $this->apiKey = $config['api_key'] ?? '';
        $this->model = $config['model'] ?? 'gpt-4o-mini';
        $this->systemPrompt = $config['system_prompt'] ?? self::DEFAULT_SYSTEM_PROMPT;
        $this->userPrompt = $config['user_prompt'] ?? self::DEFAULT_USER_PROMPT;
        $this->temperature = (float)($config['temperature'] ?? 0.8);
        $this->maxTokens = (int)($config['max_tokens'] ?? 500);
        $this->timeout = (int)($config['timeout'] ?? 30);
    }

    /**
     * 从数据库加载配置
     */
    public static function fromDatabase(): ?self
    {
        $db = XpkDatabase::getInstance();
        $rows = $db->query("SELECT config_name, config_value FROM " . DB_PREFIX . "config WHERE config_name LIKE 'ai_%'");
        
        $config = [];
        foreach ($rows as $row) {
            $key = str_replace('ai_', '', $row['config_name']);
            $config[$key] = $row['config_value'];
        }
        
        // 检查必填项
        if (empty($config['api_url']) || empty($config['api_key'])) {
            return null;
        }
        
        return new self($config);
    }

    /**
     * 检查 AI 改写功能是否启用且配置完整
     */
    public static function isEnabled(): bool
    {
        $db = XpkDatabase::getInstance();
        $enabled = $db->queryOne(
            "SELECT config_value FROM " . DB_PREFIX . "config WHERE config_name = 'ai_enabled'"
        );
        
        if (!$enabled || $enabled['config_value'] !== '1') {
            return false;
        }
        
        $apiUrl = $db->queryOne(
            "SELECT config_value FROM " . DB_PREFIX . "config WHERE config_name = 'ai_api_url'"
        );
        $apiKey = $db->queryOne(
            "SELECT config_value FROM " . DB_PREFIX . "config WHERE config_name = 'ai_api_key'"
        );
        
        return !empty($apiUrl['config_value']) && !empty($apiKey['config_value']);
    }

    /**
     * 获取默认系统提示词
     */
    public static function getDefaultSystemPrompt(): string
    {
        return self::DEFAULT_SYSTEM_PROMPT;
    }

    /**
     * 获取默认用户提示词
     */
    public static function getDefaultUserPrompt(): string
    {
        return self::DEFAULT_USER_PROMPT;
    }

    /**
     * 使用 AI 改写内容
     */
    public function rewrite(string $content): ?string
    {
        if (empty($content) || mb_strlen($content) < 10) {
            return null;
        }

        // 构建用户消息，替换内容占位符
        $userMessage = str_replace('{content}', $content, $this->userPrompt);

        // 构建消息数组
        $messages = [];
        if (!empty($this->systemPrompt)) {
            $messages[] = ['role' => 'system', 'content' => $this->systemPrompt];
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        // 构建请求体
        $requestBody = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens
        ];

        // 发送 API 请求
        $response = $this->request($requestBody);
        
        if (!$response) {
            return null;
        }

        // 从响应中提取内容
        return $response['choices'][0]['message']['content'] ?? null;
    }

    /**
     * 发送 HTTP 请求到 AI API
     */
    private function request(array $body): ?array
    {
        $url = $this->apiUrl . '/chat/completions';
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->logError("cURL 错误: {$error}");
            return null;
        }

        if ($httpCode !== 200) {
            $this->logError("HTTP {$httpCode}: {$response}");
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logError("JSON 解析错误: " . json_last_error_msg());
            return null;
        }

        return $data;
    }

    /**
     * 测试 API 连接
     */
    public function testConnection(): array
    {
        $testContent = '一个普通上班族意外获得超能力，从此开始了不平凡的冒险之旅。';
        
        $startTime = microtime(true);
        $result = $this->rewrite($testContent);
        $duration = round((microtime(true) - $startTime) * 1000);
        
        if ($result) {
            return [
                'success' => true,
                'message' => '连接成功',
                'duration' => $duration . 'ms',
                'original' => $testContent,
                'rewritten' => $result
            ];
        }
        
        return [
            'success' => false,
            'message' => '连接失败，请检查 API 配置',
            'duration' => $duration . 'ms'
        ];
    }

    /**
     * 记录错误日志
     */
    private function logError(string $message): void
    {
        $logDir = RUNTIME_PATH . 'logs/';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . date('Y-m-d') . '_ai.log';
        $logMsg = "[" . date('Y-m-d H:i:s') . "] {$message}\n";
        @file_put_contents($logFile, $logMsg, FILE_APPEND);
    }
}
