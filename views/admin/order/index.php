<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">订单管理</h1>
    <a href="/<?= $adminEntry ?>?s=order/export<?= isset($_GET['status']) ? '&status=' . $_GET['status'] : '' ?><?= isset($_GET['start_date']) ? '&start_date=' . $_GET['start_date'] : '' ?><?= isset($_GET['end_date']) ? '&end_date=' . $_GET['end_date'] : '' ?>" 
        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">导出CSV</a>
</div>

<!-- 统计卡片 -->
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">总订单数</div>
        <div class="text-2xl font-bold"><?= number_format($stats['total_count']) ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">已支付</div>
        <div class="text-2xl font-bold text-green-600"><?= number_format($stats['paid_count']) ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">待支付</div>
        <div class="text-2xl font-bold text-yellow-600"><?= number_format($stats['pending_count']) ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">成交金额</div>
        <div class="text-2xl font-bold text-red-500">¥<?= number_format($stats['paid_amount'], 2) ?></div>
    </div>
</div>

<!-- 搜索 -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <input type="hidden" name="s" value="order">
        <div>
            <label class="block text-sm text-gray-600 mb-1">订单号</label>
            <input type="text" name="order_no" value="<?= htmlspecialchars($_GET['order_no'] ?? '') ?>" 
                class="border rounded px-3 py-2" placeholder="订单号">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">状态</label>
            <select name="status" class="border rounded px-3 py-2">
                <option value="">全部</option>
                <option value="0" <?= ($_GET['status'] ?? '') === '0' ? 'selected' : '' ?>>待支付</option>
                <option value="1" <?= ($_GET['status'] ?? '') === '1' ? 'selected' : '' ?>>已支付</option>
                <option value="2" <?= ($_GET['status'] ?? '') === '2' ? 'selected' : '' ?>>已取消</option>
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">支付方式</label>
            <select name="pay_method" class="border rounded px-3 py-2">
                <option value="">全部</option>
                <option value="alipay" <?= ($_GET['pay_method'] ?? '') === 'alipay' ? 'selected' : '' ?>>支付宝</option>
                <option value="wechat" <?= ($_GET['pay_method'] ?? '') === 'wechat' ? 'selected' : '' ?>>微信</option>
                <option value="usdt" <?= ($_GET['pay_method'] ?? '') === 'usdt' ? 'selected' : '' ?>>USDT</option>
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">开始日期</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>" class="border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">结束日期</label>
            <input type="date" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>" class="border rounded px-3 py-2">
        </div>
        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">搜索</button>
        <a href="/<?= $adminEntry ?>?s=order" class="text-gray-500 hover:text-gray-700 py-2">重置</a>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">订单号</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">用户</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">商品</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">金额</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">支付方式</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">创建时间</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($orders)): ?>
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">暂无订单</td>
            </tr>
            <?php else: ?>
            <?php foreach ($orders as $o): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm font-mono"><?= htmlspecialchars($o['order_no']) ?></td>
                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($o['user_name'] ?? '-') ?></td>
                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($o['product_name'] ?? '-') ?></td>
                <td class="px-4 py-3 text-sm">
                    <span class="text-red-500 font-medium">¥<?= number_format($o['pay_amount'], 2) ?></span>
                    <?php if ($o['pay_method'] === 'usdt' && $o['usdt_amount']): ?>
                    <span class="text-gray-400 text-xs block">$<?= $o['usdt_amount'] ?></span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm">
                    <?php 
                    $methodMap = ['alipay' => '支付宝', 'wechat' => '微信', 'usdt' => 'USDT'];
                    echo $methodMap[$o['pay_method']] ?? ($o['pay_method'] ?: '-');
                    ?>
                </td>
                <td class="px-4 py-3">
                    <?php 
                    $statusClass = ['bg-yellow-100 text-yellow-700', 'bg-green-100 text-green-700', 'bg-gray-100 text-gray-700', 'bg-red-100 text-red-700'];
                    $statusText = ['待支付', '已支付', '已取消', '已退款'];
                    ?>
                    <span class="px-2 py-1 rounded text-xs <?= $statusClass[$o['order_status']] ?? '' ?>">
                        <?= $statusText[$o['order_status']] ?? '未知' ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500"><?= date('Y-m-d H:i', $o['order_time']) ?></td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <a href="/<?= $adminEntry ?>?s=order/detail&id=<?= $o['order_id'] ?>" class="text-blue-500 hover:underline">详情</a>
                    <?php if ($o['order_status'] == 0): ?>
                    <button onclick="completeOrder(<?= $o['order_id'] ?>)" class="text-green-500 hover:underline">完成</button>
                    <button onclick="cancelOrder(<?= $o['order_id'] ?>)" class="text-red-500 hover:underline">取消</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 分页 -->
<?php 
$baseUrl = "/{$adminEntry}?s=order";
foreach (['order_no', 'status', 'pay_method', 'start_date', 'end_date'] as $k) {
    if (isset($_GET[$k]) && $_GET[$k] !== '') $baseUrl .= "&{$k}=" . urlencode($_GET[$k]);
}
include __DIR__ . '/../components/pagination.php'; 
?>

<script>
function completeOrder(id) {
    xpkConfirm('确定手动完成该订单？此操作将激活用户VIP', function() {
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
    });
}

function cancelOrder(id) {
    xpkConfirm('确定取消该订单？', function() {
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
    });
}
</script>
