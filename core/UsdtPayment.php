<?php
/**
 * USDT/TRC20 支付处理
 * Powered by https://xpornkit.com
 */

class XpkUsdtPayment
{
    private XpkDatabase $db;
    private array $config;
    
    public function __construct()
    {
        $this->db = XpkDatabase::getInstance();
        $payConfig = require CONFIG_PATH . 'payment.php';
        $this->config = $payConfig['usdt'] ?? [];
    }
    
    /**
     * 是否启用USDT支付
     */
    public function isEnabled(): bool
    {
        return !empty($this->config['enabled']) && !empty($this->config['address']);
    }
    
    /**
     * 获取收款地址
     */
    public function getAddress(): string
    {
        return $this->config['address'] ?? '';
    }
    
    /**
     * 获取USDT合约地址
     */
    public function getContractAddress(): string
    {
        return $this->config['usdt_contract'] ?? 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
    }
    
    /**
     * 生成唯一USDT金额(4位小数)
     */
    public function generateAmount(float $baseAmount): float
    {
        $this->cleanExpiredLocks();
        
        // 基础金额保留2位小数
        $baseStr = number_format($baseAmount, 2, '.', '');
        
        // 尝试生成唯一金额(最多100次)
        for ($i = 0; $i < 100; $i++) {
            // 生成2位随机数作为后缀(01-99)
            $suffix = str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
            $amount = $baseStr . $suffix;
            
            // 检查是否已被锁定
            $exists = $this->db->queryOne(
                "SELECT lock_id FROM " . DB_PREFIX . "usdt_lock WHERE lock_amount = ? AND expire_time > ?",
                [$amount, time()]
            );
            
            if (!$exists) {
                return (float)$amount;
            }
        }
        
        // 兜底：使用时间戳后缀
        $suffix = substr(time(), -2);
        return (float)($baseStr . $suffix);
    }

    
    /**
     * 锁定金额
     */
    public function lockAmount(float $amount, int $orderId): void
    {
        $expireTime = time() + ($this->config['lock_time'] ?? 1800);
        
        // 使用 REPLACE 确保唯一性
        $this->db->execute(
            "REPLACE INTO " . DB_PREFIX . "usdt_lock (lock_amount, order_id, expire_time) VALUES (?, ?, ?)",
            [$amount, $orderId, $expireTime]
        );
    }
    
    /**
     * 解锁金额
     */
    public function unlockAmount(float $amount): void
    {
        $this->db->execute(
            "DELETE FROM " . DB_PREFIX . "usdt_lock WHERE lock_amount = ?",
            [$amount]
        );
    }
    
    /**
     * 清理过期锁定
     */
    public function cleanExpiredLocks(): int
    {
        $result = $this->db->execute(
            "DELETE FROM " . DB_PREFIX . "usdt_lock WHERE expire_time < ?",
            [time()]
        );
        return $result;
    }
    
    /**
     * 检查USDT支付(轮询TRON API)
     */
    public function checkPayment(float $amount, int $orderCreateTime): ?array
    {
        if (empty($this->config['tron_api_key']) || empty($this->config['address'])) {
            return null;
        }
        
        // 获取已使用的交易ID
        $usedTxids = $this->getUsedTxids();
        
        // 查询最近的转账记录
        $transfers = $this->getRecentTransfers(50);
        
        // USDT金额转换(6位精度)
        $targetAmount = bcmul((string)$amount, '1000000', 0);
        $orderCreateMs = $orderCreateTime * 1000;
        
        foreach ($transfers as $tx) {
            // 1. 验证是USDT代币
            if (($tx['token_info']['symbol'] ?? '') !== 'USDT') {
                continue;
            }
            
            // 2. 验证合约地址
            if (($tx['token_info']['address'] ?? '') !== $this->getContractAddress()) {
                continue;
            }
            
            // 3. 验证交易类型
            if (($tx['type'] ?? '') !== 'Transfer') {
                continue;
            }
            
            // 4. 验证接收地址
            if (($tx['to'] ?? '') !== $this->config['address']) {
                continue;
            }
            
            // 5. 验证金额(精确匹配)
            if ((string)($tx['value'] ?? '') !== (string)$targetAmount) {
                continue;
            }
            
            // 6. 验证时间(必须在订单创建之后)
            if (($tx['block_timestamp'] ?? 0) < $orderCreateMs) {
                continue;
            }
            
            // 7. 验证交易ID未被使用
            $txid = $tx['transaction_id'] ?? '';
            if (in_array($txid, $usedTxids)) {
                continue;
            }
            
            // 匹配成功
            return [
                'txid' => $txid,
                'amount' => bcdiv($tx['value'], '1000000', 4),
                'from' => $tx['from'] ?? '',
                'timestamp' => intval($tx['block_timestamp'] / 1000),
            ];
        }
        
        return null;
    }

    
    /**
     * 获取最近的USDT转账记录
     */
    private function getRecentTransfers(int $limit = 50): array
    {
        $address = $this->config['address'];
        $url = "https://api.trongrid.io/v1/accounts/{$address}/transactions/trc20";
        $url .= "?only_to=true&limit={$limit}";
        
        $response = $this->apiRequest($url);
        return $response['data'] ?? [];
    }
    
    /**
     * 获取已使用的交易ID
     */
    private function getUsedTxids(): array
    {
        $result = $this->db->query(
            "SELECT txid FROM " . DB_PREFIX . "order WHERE txid IS NOT NULL AND txid != ''"
        );
        return array_column($result, 'txid');
    }
    
    /**
     * TRON API请求
     */
    private function apiRequest(string $url, int $maxRetries = 3): array
    {
        $lastError = '';
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'TRON-PRO-API-KEY: ' . $this->config['tron_api_key'],
                    'Accept: application/json',
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode === 200 && $response !== false) {
                $data = json_decode($response, true);
                if ($data !== null) {
                    return $data;
                }
                $lastError = 'JSON decode failed';
            } else {
                $lastError = $curlError ?: "HTTP {$httpCode}";
            }
            
            // 4xx错误不重试
            if ($httpCode >= 400 && $httpCode < 500) {
                break;
            }
            
            // 重试前等待
            if ($attempt < $maxRetries) {
                usleep(pow(2, $attempt - 1) * 500000); // 0.5s, 1s, 2s
            }
        }
        
        return [];
    }
}
