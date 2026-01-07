<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">支付通道管理</h1>
    <div class="space-x-2">
        <a href="/<?= $adminEntry ?>?s=payment/usdt" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">USDT配置</a>
        <a href="/<?= $adminEntry ?>?s=payment/add" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">+ 添加通道</a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">通道编码</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">通道名称</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">支持方式</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">商户ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">权重</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($channels)): ?>
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">暂无支付通道</td>
            </tr>
            <?php else: ?>
            <?php foreach ($channels as $ch): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?= $ch['channel_id'] ?></td>
                <td class="px-4 py-3 text-sm font-mono"><?= htmlspecialchars($ch['channel_code']) ?></td>
                <td class="px-4 py-3 text-sm font-medium"><?= htmlspecialchars($ch['channel_name']) ?></td>
                <td class="px-4 py-3 text-sm">
                    <?php foreach (explode(',', $ch['support_methods']) as $m): ?>
                    <span class="inline-block px-2 py-0.5 rounded text-xs <?= $m === 'alipay' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' ?>">
                        <?= $m === 'alipay' ? '支付宝' : ($m === 'wechat' ? '微信' : $m) ?>
                    </span>
                    <?php endforeach; ?>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500"><?= htmlspecialchars($ch['merchant_id']) ?></td>
                <td class="px-4 py-3 text-sm"><?= $ch['weight'] ?></td>
                <td class="px-4 py-3">
                    <button onclick="toggleStatus(<?= $ch['channel_id'] ?>)" 
                        class="px-2 py-1 rounded text-xs <?= $ch['channel_status'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $ch['channel_status'] ? '启用' : '禁用' ?>
                    </button>
                </td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <a href="/<?= $adminEntry ?>?s=payment/edit/<?= $ch['channel_id'] ?>" class="text-blue-500 hover:underline">编辑</a>
                    <button onclick="deleteChannel(<?= $ch['channel_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function toggleStatus(id) {
    fetch(adminUrl('/payment/toggle'), {
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

function deleteChannel(id) {
    if (!confirm('确定删除该支付通道？')) return;
    fetch(adminUrl('/payment/delete'), {
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
