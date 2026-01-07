<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">VIP套餐管理</h1>
    <div class="space-x-2">
        <a href="/<?= $adminEntry ?>?s=vip/config" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">VIP配置</a>
        <a href="/<?= $adminEntry ?>?s=vip/add" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">+ 添加套餐</a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">套餐名称</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">价格</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">USDT价格</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">有效期</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">每日限制</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">赠送积分</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($packages)): ?>
            <tr>
                <td colspan="9" class="px-4 py-8 text-center text-gray-500">暂无VIP套餐</td>
            </tr>
            <?php else: ?>
            <?php foreach ($packages as $p): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?= $p['package_id'] ?></td>
                <td class="px-4 py-3 text-sm font-medium">
                    <?= htmlspecialchars($p['package_name']) ?>
                    <?php if ($p['is_hot']): ?>
                    <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs rounded">热门</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm">
                    <span class="text-red-500 font-medium">¥<?= number_format($p['price'], 2) ?></span>
                    <?php if ($p['original_price']): ?>
                    <span class="text-gray-400 line-through text-xs ml-1">¥<?= number_format($p['original_price'], 2) ?></span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm"><?= $p['price_usdt'] ? '$' . $p['price_usdt'] : '-' ?></td>
                <td class="px-4 py-3 text-sm"><?= $p['days'] ?>天</td>
                <td class="px-4 py-3 text-sm"><?= $p['daily_limit'] >= 9999 ? '无限' : $p['daily_limit'] . '次' ?></td>
                <td class="px-4 py-3 text-sm"><?= $p['bonus_points'] ?: '-' ?></td>
                <td class="px-4 py-3">
                    <button onclick="toggleStatus(<?= $p['package_id'] ?>)" 
                        class="px-2 py-1 rounded text-xs <?= $p['status'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $p['status'] ? '上架' : '下架' ?>
                    </button>
                </td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <a href="/<?= $adminEntry ?>?s=vip/edit/<?= $p['package_id'] ?>" class="text-blue-500 hover:underline">编辑</a>
                    <button onclick="deletePackage(<?= $p['package_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function toggleStatus(id) {
    fetch(adminUrl('/vip/toggle'), {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) location.reload();
        else xpkToast(data.msg, 'error');
    });
}

function deletePackage(id) {
    if (!confirm('确定删除该VIP套餐？')) return;
    fetch(adminUrl('/vip/delete'), {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) location.reload();
        else xpkToast(data.msg, 'error');
    });
}
</script>
