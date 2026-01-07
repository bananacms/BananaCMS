<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">订单详情</h1>
    <a href="/<?= $adminEntry ?>?s=order" class="text-gray-500 hover:text-gray-700">← 返回列表</a>
</div>

<?php 
$statusClass = ['bg-yellow-100 text-yellow-700', 'bg-green-100 text-green-700', 'bg-gray-100 text-gray-700', 'bg-red-100 text-red-700'];
$statusText = ['待支付', '已支付', '已取消', '已退款'];
?>

<div class="grid grid-cols-2 gap-6">
    <!-- 订单信息 -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium mb-4 pb-2 border-b">订单信息</h3>
        <dl class="space-y-3">
            <div class="flex">
                <dt class="w-24 text-gray-500">订单号</dt>
                <dd class="font-mono"><?= htmlspecialchars($order['order_no']) ?></dd>
            </div>
            <div class="flex">
                <dt class="w-24 text-gray-500">订单状态</dt>
                <dd>
                    <span class="px-2 py-1 rounded text-xs <?= $statusClass[$order['order_status']] ?? '' ?>">
                        <?= $statusText[$order['order_status']] ?? '未知' ?>
                    </span>
                </dd>
            </div>
            <div class="flex">
                <dt class="w-24 text-gray-500">订单类型</dt>
                <dd><?= $order['order_type'] === 'vip' ? 'VIP购买' : $order['order_type'] ?></dd>
            </div>
            <div class="flex">
                <dt class="w-24 text-gray-500">商品名称</dt>
                <dd><?= htmlspecialchars($order['product_name'] ?? '-') ?></dd>
            </div>
            <div class="flex">
                <dt class="w-24 text-gray-500">订单金额</dt>
                <dd class="text-red-500 font-medium">¥<?= number_format($order['order_amount'], 2) ?></dd>
            </div>
            <div class="flex">
                <dt class="w-24 text-gray-500">实付金额</dt>
                <dd class="text-red-500 font-medium">¥<?= number_format($order['pay_amount'], 2) ?></dd>
            </div>
            <?php if ($order['pay_method'] === 'usdt' && $order['usdt_amount']): ?>
            <div class="flex">
                <dt class="w-24 text-gray-500">USDT金额</dt>
                <dd class="font-mono">$<?= $order['usdt_amount'] ?></dd>
            </div>
            <?php endif; ?>
            <div class="flex">
                <dt class="w-24 text-gray-500">创建时间</dt>
                <dd><?= date('Y-m-d H:i:s', $order['order_time']) ?></dd>
            </div>
            <?php if ($order['pay_time']): ?>
            <div class="flex">
                <dt class="w-24 text-gray-500">支付时间</dt>
                <dd><?= date('Y-m-d H:i:s', $order['pay_time']) ?></dd>
            </div>
            <?php endif; ?>
            <?php if ($order['expire_time']): ?>
            <div class="flex">
                <dt class="w-24 text-gray-500">过期时间</dt>
                <dd><?= date('Y-m-d H:i:s', $order['expire_time']) ?></dd>
            </div>
            <?php endif; ?>
        </dl>
    </div>
    
    <!-- 支付信息 -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium mb-4 pb-2 border-b">支付信息</h3>
        <dl class="space-y-3">
            <div class="flex">
                <dt class="w-24 text-gray-500">支付方式</dt>
                <dd>
                    <?php 
                    $methodMap = ['alipay' => '支付宝', 'wechat' => '微信支付', 'usdt' => 'USDT/TRC20'];
                    echo $methodMap[$order['pay_method']] ?? ($order['pay_method'] ?: '-');
                    ?>
                </dd>
            </div>
            <div class="flex">
                <dt class="w-24 text-gray-500">支付通道</dt>
                <dd><?= htmlspecialchars($order['channel_name'] ?? '-') ?></dd>
            </div>
            <?php if ($order['trade_no']): ?>
            <div class="flex">
                <dt class="w-24 text-gray-500">交易号</dt>
                <dd class="font-mono text-sm break-all"><?= htmlspecialchars($order['trade_no']) ?></dd>
            </div>
            <?php endif; ?>
            <?php if ($order['txid']): ?>
            <div class="flex">
                <dt class="w-24 text-gray-500">TXID</dt>
                <dd class="font-mono text-sm break-all">
                    <a href="https://tronscan.org/#/transaction/<?= htmlspecialchars($order['txid']) ?>" 
                        target="_blank" class="text-blue-500 hover:underline"><?= htmlspecialchars($order['txid']) ?></a>
                </dd>
            </div>
            <?php endif; ?>
            <div class="flex">
                <dt class="w-24 text-gray-500">客户端IP</dt>
                <dd><?= htmlspecialchars($order['client_ip'] ?? '-') ?></dd>
            </div>
        </dl>
    </div>
    
    <!-- 用户信息 -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium mb-4 pb-2 border-b">用户信息</h3>
        <dl class="space-y-3">
            <div class="flex">
                <dt class="w-24 text-gray-500">用户ID</dt>
                <dd><?= $order['user_id'] ?></dd>
            </div>
            <div class="flex">
                <dt class="w-24 text-gray-500">用户名</dt>
                <dd><?= htmlspecialchars($order['user_name'] ?? '-') ?></dd>
            </div>
            <div class="flex">
                <dt class="w-24 text-gray-500">昵称</dt>
                <dd><?= htmlspecialchars($order['user_nick_name'] ?? '-') ?></dd>
            </div>
        </dl>
    </div>
    
    <!-- 套餐信息 -->
    <?php if ($package): ?>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium mb-4 pb-2 border-b">套餐信息</h3>
        <dl class="space-y-3">
            <div class="flex">
                <dt class="w-24 text-gray-500">套餐名称</dt>
                <dd><?= htmlspecialchars($package['package_name']) ?></dd>
            </div>
            <div class="flex">
                <dt class="w-24 text-gray-500">有效天数</dt>
                <dd><?= $package['package_days'] ?>天</dd>
            </div>
            <div class="flex">
                <dt class="w-24 text-gray-500">赠送天数</dt>
                <dd><?= $package['package_bonus_days'] ?: 0 ?>天</dd>
            </div>
            <div class="flex">
                <dt class="w-24 text-gray-500">赠送积分</dt>
                <dd><?= $package['package_bonus_points'] ?: 0 ?></dd>
            </div>
        </dl>
    </div>
    <?php endif; ?>
</div>

<!-- 操作按钮 -->
<?php if ($order['order_status'] == 0): ?>
<div class="mt-6 flex gap-4">
    <button onclick="completeOrder(<?= $order['order_id'] ?>)" 
        class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">手动完成订单</button>
    <button onclick="cancelOrder(<?= $order['order_id'] ?>)" 
        class="bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600">取消订单</button>
</div>

<script>
function completeOrder(id) {
    if (!confirm('确定手动完成该订单？此操作将激活用户VIP')) return;
    fetch(adminUrl('/order/complete'), {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id + '&_token=<?= $csrfToken ?>'
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) location.reload();
        else xpkToast(data.msg, 'error');
    });
}

function cancelOrder(id) {
    if (!confirm('确定取消该订单？')) return;
    fetch(adminUrl('/order/cancel'), {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id + '&_token=<?= $csrfToken ?>'
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) location.reload();
        else xpkToast(data.msg, 'error');
    });
}
</script>
<?php endif; ?>
