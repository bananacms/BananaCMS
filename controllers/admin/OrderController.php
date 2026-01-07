<?php
/**
 * 订单管理控制器
 * Powered by https://xpornkit.com
 */

class AdminOrderController extends AdminBaseController
{
    /**
     * 订单列表
     */
    public function index(): void
    {
        $page = max(1, (int)$this->get('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $where = '1=1';
        $params = [];
        
        // 筛选条件
        if ($orderNo = trim($this->get('order_no', ''))) {
            $where .= ' AND o.order_no LIKE ?';
            $params[] = '%' . $orderNo . '%';
        }
        if (($status = $this->get('status', '')) !== '') {
            $where .= ' AND o.order_status = ?';
            $params[] = (int)$status;
        }
        if ($payMethod = $this->get('pay_method', '')) {
            $where .= ' AND o.pay_method = ?';
            $params[] = $payMethod;
        }
        if ($userId = (int)$this->get('user_id', 0)) {
            $where .= ' AND o.user_id = ?';
            $params[] = $userId;
        }
        if ($startDate = $this->get('start_date', '')) {
            $where .= ' AND o.order_time >= ?';
            $params[] = strtotime($startDate);
        }
        if ($endDate = $this->get('end_date', '')) {
            $where .= ' AND o.order_time <= ?';
            $params[] = strtotime($endDate . ' 23:59:59');
        }
        
        $orders = $this->db->query(
            "SELECT o.*, u.user_name, c.channel_name
             FROM " . DB_PREFIX . "order o
             LEFT JOIN " . DB_PREFIX . "user u ON o.user_id = u.user_id
             LEFT JOIN " . DB_PREFIX . "payment_channel c ON o.channel_id = c.channel_id
             WHERE {$where}
             ORDER BY o.order_id DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );
        
        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "order o WHERE {$where}",
            $params
        )['cnt'] ?? 0;
        
        // 统计数据
        $stats = $this->getStats($where, $params);
        
        $this->assign('orders', $orders);
        $this->assign('total', $total);
        $this->assign('page', $page);
        $this->assign('pages', ceil($total / $limit));
        $this->assign('stats', $stats);
        $this->render('order/index', '订单管理');
    }
    
    /**
     * 获取统计数据
     */
    private function getStats(string $where, array $params): array
    {
        $row = $this->db->queryOne(
            "SELECT 
                COUNT(*) as total_count,
                SUM(CASE WHEN order_status = 1 THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN order_status = 1 THEN pay_amount ELSE 0 END) as paid_amount,
                SUM(CASE WHEN order_status = 0 THEN 1 ELSE 0 END) as pending_count
             FROM " . DB_PREFIX . "order o WHERE {$where}",
            $params
        );
        
        return [
            'total_count' => $row['total_count'] ?? 0,
            'paid_count' => $row['paid_count'] ?? 0,
            'paid_amount' => $row['paid_amount'] ?? 0,
            'pending_count' => $row['pending_count'] ?? 0,
        ];
    }
    
    /**
     * 订单详情
     */
    public function detail(int $id = 0): void
    {
        if ($id <= 0) {
            $id = (int)$this->get('id', 0);
        }
        
        $order = $this->db->queryOne(
            "SELECT o.*, u.user_name, u.user_nick_name, c.channel_name
             FROM " . DB_PREFIX . "order o
             LEFT JOIN " . DB_PREFIX . "user u ON o.user_id = u.user_id
             LEFT JOIN " . DB_PREFIX . "payment_channel c ON o.channel_id = c.channel_id
             WHERE o.order_id = ?",
            [$id]
        );
        
        if (!$order) {
            $this->error('订单不存在');
        }
        
        // 获取套餐信息
        $package = null;
        if ($order['order_type'] === 'vip' && $order['product_id']) {
            $package = $this->db->queryOne(
                "SELECT * FROM " . DB_PREFIX . "vip_package WHERE package_id = ?",
                [$order['product_id']]
            );
        }
        
        $this->assign('order', $order);
        $this->assign('package', $package);
        $this->render('order/detail', '订单详情');
    }
    
    /**
     * 手动完成订单
     */
    public function complete(): void
    {
        $id = (int)$this->post('id', 0);
        
        $order = $this->db->queryOne(
            "SELECT * FROM " . DB_PREFIX . "order WHERE order_id = ?",
            [$id]
        );
        
        if (!$order) {
            $this->error('订单不存在');
        }
        
        if ($order['order_status'] != 0) {
            $this->error('只能完成待支付订单');
        }
        
        require_once CORE_PATH . 'Payment.php';
        $payment = XpkPayment::getInstance();
        
        $result = $payment->completeOrder($id, [
            'trade_no' => 'MANUAL_' . time(),
        ]);
        
        if (!$result['success']) {
            $this->error($result['error'] ?? '操作失败');
        }
        
        $this->log('complete', 'order', "手动完成订单: {$order['order_no']}");
        $this->success('订单已完成');
    }
    
    /**
     * 取消订单
     */
    public function cancel(): void
    {
        $id = (int)$this->post('id', 0);
        
        $order = $this->db->queryOne(
            "SELECT * FROM " . DB_PREFIX . "order WHERE order_id = ?",
            [$id]
        );
        
        if (!$order) {
            $this->error('订单不存在');
        }
        
        if ($order['order_status'] != 0) {
            $this->error('只能取消待支付订单');
        }
        
        $this->db->execute(
            "UPDATE " . DB_PREFIX . "order SET order_status = 2, order_update = ? WHERE order_id = ?",
            [time(), $id]
        );
        
        // 如果是USDT订单，解锁金额
        if ($order['pay_method'] === 'usdt' && $order['usdt_amount']) {
            require_once CORE_PATH . 'UsdtPayment.php';
            $usdt = new XpkUsdtPayment();
            $usdt->unlockAmount($order['usdt_amount']);
        }
        
        $this->log('cancel', 'order', "取消订单: {$order['order_no']}");
        $this->success('订单已取消');
    }
    
    /**
     * 导出订单
     */
    public function export(): void
    {
        $where = '1=1';
        $params = [];
        
        if ($startDate = $this->get('start_date', '')) {
            $where .= ' AND o.order_time >= ?';
            $params[] = strtotime($startDate);
        }
        if ($endDate = $this->get('end_date', '')) {
            $where .= ' AND o.order_time <= ?';
            $params[] = strtotime($endDate . ' 23:59:59');
        }
        if (($status = $this->get('status', '')) !== '') {
            $where .= ' AND o.order_status = ?';
            $params[] = (int)$status;
        }
        
        $orders = $this->db->query(
            "SELECT o.*, u.user_name
             FROM " . DB_PREFIX . "order o
             LEFT JOIN " . DB_PREFIX . "user u ON o.user_id = u.user_id
             WHERE {$where}
             ORDER BY o.order_id DESC
             LIMIT 10000",
            $params
        );
        
        $statusMap = ['待支付', '已支付', '已取消', '已退款'];
        
        // CSV输出
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=orders_' . date('Ymd') . '.csv');
        
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo "订单号,用户名,商品名称,订单金额,实付金额,支付方式,状态,创建时间,支付时间\n";
        
        foreach ($orders as $o) {
            echo implode(',', [
                $o['order_no'],
                $o['user_name'],
                $o['product_name'],
                $o['order_amount'],
                $o['pay_amount'],
                $o['pay_method'] ?: '-',
                $statusMap[$o['order_status']] ?? '未知',
                date('Y-m-d H:i:s', $o['order_time']),
                $o['pay_time'] ? date('Y-m-d H:i:s', $o['pay_time']) : '-',
            ]) . "\n";
        }
        
        exit;
    }
}
