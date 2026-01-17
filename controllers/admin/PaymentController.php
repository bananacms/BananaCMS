<?php
/**
 * 支付通道管理控制器
 * Powered by https://xpornkit.com
 */

class AdminPaymentController extends AdminBaseController
{
    /**
     * 通道列表
     */
    public function index(): void
    {
        $channels = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "payment_channel ORDER BY channel_sort ASC, channel_id ASC"
        );
        
        $this->assign('channels', $channels);
        $this->render('payment/index', '支付通道管理');
    }
    
    /**
     * 添加通道
     */
    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->save();
            return;
        }
        
        $this->assign('channel', null);
        $this->render('payment/edit', '添加支付通道');
    }
    
    /**
     * 编辑通道
     */
    public function edit(int $id = 0): void
    {
        if ($id <= 0) {
            $id = (int)$this->get('id', 0);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->save($id);
            return;
        }
        
        $channel = $this->db->queryOne(
            "SELECT * FROM " . DB_PREFIX . "payment_channel WHERE channel_id = ?",
            [$id]
        );
        
        if (!$channel) {
            $this->error('通道不存在');
        }
        
        // 解析额外配置
        $channel['extra'] = json_decode($channel['extra_config'] ?? '{}', true) ?: [];
        
        $this->assign('channel', $channel);
        $this->render('payment/edit', '编辑支付通道');
    }
    
    /**
     * 保存通道
     */
    private function save(int $id = 0): void
    {
        $this->requireCsrf();
        
        $channelType = $this->post('channel_type', 'epay');
        
        $data = [
            'channel_code' => trim($this->post('channel_code', '')),
            'channel_name' => trim($this->post('channel_name', '')),
            'channel_type' => $channelType,
            'gateway_url' => trim($this->post('gateway_url', '')),
            'query_url' => trim($this->post('query_url', '')),
            'merchant_id' => trim($this->post('merchant_id', '')),
            'merchant_key' => trim($this->post('merchant_key', '')),
            'support_methods' => implode(',', $this->post('support_methods', ['alipay'])),
            'fee_rate' => floatval($this->post('fee_rate', 0)),
            'min_amount' => floatval($this->post('min_amount', 0.01)),
            'max_amount' => floatval($this->post('max_amount', 50000)),
            'daily_limit' => floatval($this->post('daily_limit', 0)),
            'weight' => (int)$this->post('weight', 100),
            'channel_status' => (int)$this->post('status', 1),
            'channel_sort' => (int)$this->post('sort', 0),
            'channel_update' => time(),
        ];
        
        // 验证必填
        if (empty($data['channel_code']) || empty($data['channel_name'])) {
            $this->error('通道编码和名称不能为空');
        }
        if (empty($data['gateway_url'])) {
            $this->error('网关地址不能为空');
        }
        if (empty($data['merchant_id']) || empty($data['merchant_key'])) {
            $this->error('商户ID和密钥不能为空');
        }
        
        // 构建额外配置(包含协议类型)
        $extra = ['protocol' => $channelType];
        
        // 熊熊支付额外参数
        if ($channelType === 'xiongxiong') {
            $extra['cid'] = trim($this->post('xiongxiong_cid', '1'));
            $extra['eparam'] = trim($this->post('xiongxiong_eparam', ''));
        }
        
        // 直连支付额外参数
        if ($channelType === 'zhilian') {
            $extra['channel_code'] = trim($this->post('zhilian_channel_code', ''));
        }
        
        $data['extra_config'] = json_encode($extra, JSON_UNESCAPED_UNICODE);
        
        // 检查编码唯一性
        $exists = $this->db->queryOne(
            "SELECT channel_id FROM " . DB_PREFIX . "payment_channel WHERE channel_code = ? AND channel_id != ?",
            [$data['channel_code'], $id]
        );
        if ($exists) {
            $this->error('通道编码已存在');
        }
        
        if ($id > 0) {
            $this->db->update(DB_PREFIX . 'payment_channel', $data, ['channel_id' => $id]);
            $this->log('edit', 'payment', "编辑支付通道: {$data['channel_name']}");
            $this->success('更新成功');
        } else {
            $data['channel_time'] = time();
            $this->db->insert(DB_PREFIX . 'payment_channel', $data);
            $this->log('add', 'payment', "添加支付通道: {$data['channel_name']}");
            $this->success('添加成功');
        }
    }
    
    /**
     * 切换状态
     */
    public function toggle(): void
    {
        // CSRF 验证
        $this->requireCsrf();
        
        $id = (int)$this->post('id', 0);
        
        $channel = $this->db->queryOne(
            "SELECT channel_name, channel_status FROM " . DB_PREFIX . "payment_channel WHERE channel_id = ?",
            [$id]
        );
        
        if (!$channel) {
            $this->error('通道不存在');
        }
        
        $newStatus = $channel['channel_status'] ? 0 : 1;
        $this->db->execute(
            "UPDATE " . DB_PREFIX . "payment_channel SET channel_status = ?, channel_update = ? WHERE channel_id = ?",
            [$newStatus, time(), $id]
        );
        
        $action = $newStatus ? '启用' : '禁用';
        $this->log('toggle', 'payment', "{$action}支付通道: {$channel['channel_name']}");
        $this->success('操作成功');
    }
    
    /**
     * 删除通道
     */
    public function delete(): void
    {
        // CSRF 验证
        $this->requireCsrf();
        
        $id = (int)$this->post('id', 0);
        
        $channel = $this->db->queryOne(
            "SELECT channel_name FROM " . DB_PREFIX . "payment_channel WHERE channel_id = ?",
            [$id]
        );
        
        if (!$channel) {
            $this->error('通道不存在');
        }
        
        // 检查是否有关联订单
        $orderCount = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "order WHERE channel_id = ?",
            [$id]
        )['cnt'] ?? 0;
        
        if ($orderCount > 0) {
            $this->error("该通道有 {$orderCount} 个关联订单，无法删除");
        }
        
        $this->db->execute(
            "DELETE FROM " . DB_PREFIX . "payment_channel WHERE channel_id = ?",
            [$id]
        );
        
        $this->log('delete', 'payment', "删除支付通道: {$channel['channel_name']}");
        $this->success('删除成功');
    }
    
    /**
     * USDT配置
     */
    public function usdt(): void
    {
        $configFile = CONFIG_PATH . 'payment.php';
        $config = require $configFile;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireCsrf();
            $config['usdt'] = [
                'enabled' => (bool)$this->post('enabled', false),
                'address' => trim($this->post('address', '')),
                'tron_api_key' => trim($this->post('tron_api_key', '')),
                'lock_time' => (int)$this->post('lock_time', 1800),
            ];
            
            // 写入配置文件
            $content = "<?php\n/**\n * 支付配置\n */\nreturn " . var_export($config, true) . ";\n";
            file_put_contents($configFile, $content);
            
            $this->log('edit', 'payment', 'USDT配置更新');
            $this->success('保存成功');
            return;
        }
        
        $this->assign('config', $config['usdt'] ?? []);
        $this->render('payment/usdt', 'USDT支付配置');
    }
}
