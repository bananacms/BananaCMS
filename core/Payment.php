<?php
/**
 * 支付管理类
 * Powered by https://xpornkit.com
 */

class XpkPayment
{
    private XpkDatabase $db;
    private array $config;
    private static ?XpkPayment $instance = null;
    
    private function __construct()
    {
        $this->db = XpkDatabase::getInstance();
        $this->config = require CONFIG_PATH . 'payment.php';
    }
    
    public static function getInstance(): XpkPayment
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 获取支付配置
     */
    public function getConfig(?string $key = null)
    {
        if ($key === null) {
            return $this->config;
        }
        return $this->config[$key] ?? null;
    }
    
    /**
     * 获取所有启用的支付通道
     */
    public function getEnabledChannels(?string $method = null): array
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "payment_channel WHERE channel_status = 1";
        $params = [];
        
        if ($method) {
            $sql .= " AND FIND_IN_SET(?, support_methods)";
            $params[] = $method;
        }
        
        $sql .= " ORDER BY channel_sort ASC, weight DESC";
        return $this->db->query($sql, $params);
    }

    
    /**
     * 获取单个通道
     */
    public function getChannel(int $channelId): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM " . DB_PREFIX . "payment_channel WHERE channel_id = ?",
            [$channelId]
        );
    }
    
    /**
     * 根据编码获取通道
     */
    public function getChannelByCode(string $code): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM " . DB_PREFIX . "payment_channel WHERE channel_code = ? AND channel_status = 1",
            [$code]
        );
    }
    
    /**
     * 智能选择通道(根据权重)
     */
    public function selectChannel(string $method = 'alipay'): ?array
    {
        $channels = $this->getEnabledChannels($method);
        if (empty($channels)) {
            return null;
        }
        
        // 按权重随机选择
        $totalWeight = array_sum(array_column($channels, 'weight'));
        if ($totalWeight <= 0) {
            return $channels[0];
        }
        
        $random = mt_rand(1, $totalWeight);
        $current = 0;
        
        foreach ($channels as $channel) {
            $current += (int)$channel['weight'];
            if ($random <= $current) {
                return $channel;
            }
        }
        
        return $channels[0];
    }
    
    /**
     * 生成订单号
     */
    public function generateOrderNo(): string
    {
        $prefix = $this->config['order']['no_prefix'] ?? 'XPK';
        return $prefix . date('YmdHis') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    
    /**
     * 创建支付订单
     */
    public function createOrder(array $data): ?array
    {
        $orderNo = $this->generateOrderNo();
        $expireTime = time() + ($this->config['order']['expire_time'] ?? 1800);
        
        $insertData = [
            'order_no' => $orderNo,
            'user_id' => $data['user_id'],
            'order_type' => $data['order_type'] ?? 'vip',
            'product_id' => $data['product_id'] ?? null,
            'product_name' => $data['product_name'] ?? '',
            'order_amount' => $data['amount'],
            'pay_amount' => $data['pay_amount'] ?? $data['amount'],
            'pay_method' => $data['pay_method'] ?? null,
            'channel_id' => $data['channel_id'] ?? null,
            'channel_code' => $data['channel_code'] ?? null,
            'usdt_amount' => $data['usdt_amount'] ?? null,
            'order_status' => 0,
            'expire_time' => $expireTime,
            'client_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'order_time' => time(),
            'order_update' => time(),
        ];
        
        $orderId = $this->db->insert(DB_PREFIX . 'order', $insertData);
        if (!$orderId) {
            return null;
        }
        
        $insertData['order_id'] = $orderId;
        return $insertData;
    }
    
    /**
     * 获取订单
     */
    public function getOrder(int $orderId): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM " . DB_PREFIX . "order WHERE order_id = ?",
            [$orderId]
        );
    }
    
    /**
     * 根据订单号获取订单
     */
    public function getOrderByNo(string $orderNo): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM " . DB_PREFIX . "order WHERE order_no = ?",
            [$orderNo]
        );
    }

    
    /**
     * 生成支付参数
     */
    public function buildPayParams(array $order, array $channel): array
    {
        $extra = json_decode($channel['extra_config'] ?? '{}', true) ?: [];
        $protocol = $extra['protocol'] ?? 'epay';
        
        // 根据协议类型生成参数
        return match($protocol) {
            'xiongxiong' => $this->buildXiongxiongParams($order, $channel, $extra),
            'zhilian' => $this->buildZhilianParams($order, $channel, $extra),
            default => $this->buildEpayParams($order, $channel, $extra),
        };
    }
    
    /**
     * 易支付协议参数
     */
    private function buildEpayParams(array $order, array $channel, array $extra): array
    {
        $params = [
            'pid' => $channel['merchant_id'],
            'type' => $this->convertPayMethod($order['pay_method']),
            'out_trade_no' => $order['order_no'],
            'notify_url' => $this->config['notify_url'],
            'return_url' => $this->config['return_url'],
            'name' => $order['product_name'],
            'money' => number_format($order['pay_amount'], 2, '.', ''),
        ];
        
        $params['sign'] = $this->generateEpaySign($params, $channel['merchant_key']);
        $params['sign_type'] = 'MD5';
        
        return [
            'gateway' => $channel['gateway_url'],
            'params' => $params,
            'method' => 'GET',
        ];
    }
    
    /**
     * 直连支付协议参数
     */
    private function buildZhilianParams(array $order, array $channel, array $extra): array
    {
        $params = [
            'mch_id' => $channel['merchant_id'],
            'trade_type' => $extra['channel_code'] ?? '',
            'out_trade_no' => $order['order_no'],
            'amount' => number_format($order['pay_amount'], 2, '.', ''),
            'notify_url' => $this->config['notify_url'],
            'return_url' => $this->config['return_url'],
            'body' => $order['product_name'],
        ];
        
        $params['sign'] = $this->generateZhilianSign($params, $channel['merchant_key']);
        
        return [
            'gateway' => $channel['gateway_url'],
            'params' => $params,
            'method' => 'POST',
        ];
    }
    
    /**
     * 熊熊支付协议参数
     * 金额单位：分，签名小写MD5，空值也参与签名
     */
    private function buildXiongxiongParams(array $order, array $channel, array $extra): array
    {
        // 金额转分
        $amountFen = (int)round($order['pay_amount'] * 100);
        
        $params = [
            'pid' => $channel['merchant_id'],
            'cid' => $extra['cid'] ?? '1',  // 渠道编号
            'type' => 'wap',
            'oid' => $order['order_no'],
            'uid' => (string)$order['user_id'],
            'amount' => $amountFen,
            'sname' => $order['product_name'],
            'burl' => $this->config['return_url'],
            'nurl' => $this->config['notify_url'],
            'eparam' => $extra['eparam'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'stype' => 'MD5',
        ];
        
        $params['sign'] = $this->generateXiongxiongSign($params, $channel['merchant_key']);
        
        // 请求JSON格式返回
        $params['format'] = 'json';
        
        return [
            'gateway' => $channel['gateway_url'],
            'params' => $params,
            'method' => 'POST',
            'protocol' => 'xiongxiong',
        ];
    }

    
    /**
     * 易支付签名生成
     */
    private function generateEpaySign(array $params, string $key): string
    {
        unset($params['sign'], $params['sign_type']);
        $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
        ksort($params);
        return md5(urldecode(http_build_query($params)) . $key);
    }
    
    /**
     * 直连支付签名生成(大写MD5)
     */
    private function generateZhilianSign(array $params, string $key): string
    {
        unset($params['sign'], $params['sign_type']);
        $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
        ksort($params);
        
        $parts = [];
        foreach ($params as $k => $v) {
            $parts[] = $k . '=' . $v;
        }
        
        return strtoupper(md5(implode('&', $parts) . '&key=' . $key));
    }
    
    /**
     * 验证易支付签名
     */
    public function verifyEpaySign(array $params, string $key): bool
    {
        $sign = $params['sign'] ?? '';
        if (empty($sign)) {
            return false;
        }
        return $sign === $this->generateEpaySign($params, $key);
    }
    
    /**
     * 验证直连支付签名
     */
    public function verifyZhilianSign(array $params, string $key): bool
    {
        $sign = $params['sign'] ?? '';
        if (empty($sign)) {
            return false;
        }
        return $sign === $this->generateZhilianSign($params, $key);
    }
    
    /**
     * 熊熊支付签名生成(小写MD5，空值也参与签名，ASCII排序)
     */
    private function generateXiongxiongSign(array $params, string $key): string
    {
        // 参与签名的字段(按ASCII排序)
        $signFields = ['amount', 'burl', 'cid', 'eparam', 'ip', 'nurl', 'oid', 'pid', 'stype', 'type', 'uid'];
        
        $parts = [];
        foreach ($signFields as $field) {
            $parts[] = $field . '=' . ($params[$field] ?? '');
        }
        $parts[] = 'key=' . $key;
        
        return md5(implode('&', $parts));
    }
    
    /**
     * 熊熊支付回调签名验证(固定顺序，不排序)
     */
    private function generateXiongxiongNotifySign(array $params, string $key): string
    {
        // 固定顺序：pid,cid,oid,sid,uid,amount,ramount,stime,code
        $signStr = sprintf(
            'pid=%s&cid=%s&oid=%s&sid=%s&uid=%s&amount=%s&ramount=%s&stime=%s&code=%s&key=%s',
            $params['pid'] ?? '',
            $params['cid'] ?? '',
            $params['oid'] ?? '',
            $params['sid'] ?? '',
            $params['uid'] ?? '',
            $params['amount'] ?? '',
            $params['ramount'] ?? '',
            $params['stime'] ?? '',
            $params['code'] ?? '',
            $key
        );
        
        return md5($signStr);
    }
    
    /**
     * 验证熊熊支付回调签名
     */
    public function verifyXiongxiongSign(array $params, string $key): bool
    {
        $sign = $params['sign'] ?? '';
        if (empty($sign)) {
            return false;
        }
        return $sign === $this->generateXiongxiongNotifySign($params, $key);
    }
    
    /**
     * 转换支付方式名称
     */
    private function convertPayMethod(string $method): string
    {
        return match($method) {
            'wechat' => 'wxpay',
            default => $method,
        };
    }

    
    /**
     * 处理支付回调
     */
    public function handleNotify(array $params): array
    {
        // 根据参数判断协议类型并获取订单号
        $orderNo = $params['oid'] ?? $params['out_trade_no'] ?? '';
        $order = $this->getOrderByNo($orderNo);
        
        if (!$order) {
            return ['success' => false, 'error' => '订单不存在'];
        }
        
        if ($order['order_status'] == 1) {
            return ['success' => true, 'message' => '已处理'];
        }
        
        // 获取通道
        $channel = $this->getChannel($order['channel_id']);
        if (!$channel) {
            return ['success' => false, 'error' => '通道不存在'];
        }
        
        // 根据协议验证签名
        $extra = json_decode($channel['extra_config'] ?? '{}', true) ?: [];
        $protocol = $extra['protocol'] ?? 'epay';
        
        if ($protocol === 'xiongxiong') {
            // 熊熊支付回调
            if (!$this->verifyXiongxiongSign($params, $channel['merchant_key'])) {
                return ['success' => false, 'error' => '签名验证失败'];
            }
            // 金额单位是分，转换为元
            $notifyAmount = floatval($params['amount'] ?? 0) / 100;
            $tradeStatus = ($params['code'] ?? '') == '101';  // 101=成功
            $tradeNo = $params['sid'] ?? '';
        } elseif ($protocol === 'zhilian') {
            if (!$this->verifyZhilianSign($params, $channel['merchant_key'])) {
                return ['success' => false, 'error' => '签名验证失败'];
            }
            $notifyAmount = floatval($params['amount'] ?? 0);
            $tradeStatus = ($params['status'] ?? '') == '1';
            $tradeNo = $params['sys_order_no'] ?? '';
        } else {
            if (!$this->verifyEpaySign($params, $channel['merchant_key'])) {
                return ['success' => false, 'error' => '签名验证失败'];
            }
            $notifyAmount = floatval($params['money'] ?? 0);
            $tradeStatus = ($params['trade_status'] ?? '') === 'TRADE_SUCCESS';
            $tradeNo = $params['trade_no'] ?? '';
        }
        
        // 验证金额
        if (abs($notifyAmount - $order['pay_amount']) > 0.01) {
            return ['success' => false, 'error' => '金额不匹配'];
        }
        
        // 验证支付状态
        if (!$tradeStatus) {
            return ['success' => false, 'error' => '支付未成功'];
        }
        
        // 完成订单
        return $this->completeOrder($order['order_id'], ['trade_no' => $tradeNo]);
    }
    
    /**
     * 获取回调成功响应(不同协议返回不同)
     */
    public function getNotifySuccessResponse(string $protocol): string
    {
        return match($protocol) {
            'xiongxiong' => 'Success',  // 熊熊支付要求大写S
            default => 'success',
        };
    }

    
    /**
     * 完成订单
     */
    public function completeOrder(int $orderId, array $extra = []): array
    {
        $order = $this->getOrder($orderId);
        if (!$order || $order['order_status'] != 0) {
            return ['success' => false, 'error' => '订单状态异常'];
        }
        
        $this->db->beginTransaction();
        
        try {
            // 更新订单状态
            $updateData = [
                'order_status' => 1,
                'pay_time' => time(),
                'order_update' => time(),
            ];
            if (!empty($extra['trade_no'])) {
                $updateData['trade_no'] = $extra['trade_no'];
            }
            if (!empty($extra['txid'])) {
                $updateData['txid'] = $extra['txid'];
            }
            
            $this->db->update(DB_PREFIX . 'order', $updateData, ['order_id' => $orderId]);
            
            // 处理业务逻辑
            if ($order['order_type'] === 'vip') {
                $this->activateVip($order);
            }
            
            $this->db->commit();
            return ['success' => true, 'order' => $order];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * 激活VIP
     */
    private function activateVip(array $order): void
    {
        // 获取套餐信息
        $package = $this->db->queryOne(
            "SELECT * FROM " . DB_PREFIX . "vip_package WHERE package_id = ?",
            [$order['product_id']]
        );
        
        if (!$package) {
            throw new Exception('套餐不存在');
        }
        
        // 获取用户当前VIP状态
        $user = $this->db->queryOne(
            "SELECT user_vip_level, user_vip_expire, user_points FROM " . DB_PREFIX . "user WHERE user_id = ?",
            [$order['user_id']]
        );
        
        if (!$user) {
            throw new Exception('用户不存在');
        }
        
        $now = time();
        $currentExpire = (int)($user['user_vip_expire'] ?? 0);
        
        // 计算新的过期时间
        $totalDays = $package['package_days'] + ($package['package_bonus_days'] ?? 0);
        if ($currentExpire > $now) {
            // 叠加时间
            $newExpire = $currentExpire + $totalDays * 86400;
        } else {
            // 从现在开始
            $newExpire = $now + $totalDays * 86400;
        }
        
        // 更新用户VIP状态
        $newPoints = (int)$user['user_points'] + (int)($package['package_bonus_points'] ?? 0);
        $this->db->execute(
            "UPDATE " . DB_PREFIX . "user SET user_vip_level = 1, user_vip_expire = ?, user_points = ? WHERE user_id = ?",
            [$newExpire, $newPoints, $order['user_id']]
        );
        
        // 记录积分变动
        if ($package['package_bonus_points'] > 0) {
            $this->addPointLog(
                $order['user_id'], 
                'gift', 
                $package['package_bonus_points'], 
                $newPoints,
                '购买VIP赠送', 
                $order['order_id']
            );
        }
    }

    
    /**
     * 添加积分记录
     */
    public function addPointLog(int $userId, string $type, int $amount, int $balance, string $remark, ?int $relatedId = null): void
    {
        $this->db->insert(DB_PREFIX . 'point_log', [
            'user_id' => $userId,
            'log_type' => $type,
            'log_amount' => $amount,
            'log_balance' => $balance,
            'log_remark' => $remark,
            'related_id' => $relatedId,
            'log_time' => time(),
        ]);
    }
    
    /**
     * 获取用户订单列表
     */
    public function getUserOrders(int $userId, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        
        $list = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "order WHERE user_id = ? ORDER BY order_id DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
        
        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "order WHERE user_id = ?",
            [$userId]
        )['cnt'] ?? 0;
        
        return [
            'list' => $list,
            'total' => (int)$total,
            'page' => $page,
            'pages' => ceil($total / $limit),
        ];
    }
    
    /**
     * 取消过期订单
     */
    public function cancelExpiredOrders(): int
    {
        $result = $this->db->execute(
            "UPDATE " . DB_PREFIX . "order SET order_status = 2, order_update = ? WHERE order_status = 0 AND expire_time < ?",
            [time(), time()]
        );
        return $result;
    }
}
