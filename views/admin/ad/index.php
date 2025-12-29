<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">📢 广告管理</h2>
        <a href="/admin.php/ad/add" class="bg-primary text-white px-4 py-2 rounded hover:bg-red-600">
            + 添加广告
        </a>
    </div>

    <!-- 统计卡片 -->
    <div class="grid grid-cols-5 gap-4 mb-6">
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">广告总数</div>
            <div class="text-2xl font-bold"><?= $stats['total'] ?></div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">启用中</div>
            <div class="text-2xl font-bold text-green-600"><?= $stats['active'] ?></div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">总展示</div>
            <div class="text-2xl font-bold text-blue-600"><?= number_format($stats['shows']) ?></div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">总点击</div>
            <div class="text-2xl font-bold text-purple-600"><?= number_format($stats['clicks']) ?></div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">点击率</div>
            <div class="text-2xl font-bold text-orange-600"><?= $stats['ctr'] ?>%</div>
        </div>
    </div>

    <!-- 筛选 -->
    <div class="bg-white p-4 rounded shadow mb-4">
        <form method="get" class="flex gap-4 items-center">
            <select name="position" class="border rounded px-3 py-2">
                <option value="">全部位置</option>
                <?php foreach ($positions as $key => $name): ?>
                <option value="<?= $key ?>" <?= $position === $key ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">筛选</button>
            <a href="/admin.php/ad" class="text-gray-500 hover:text-gray-700">重置</a>
        </form>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="mb-4 p-4 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>
</div>

<!-- 列表 -->
<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">广告名称</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">位置</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">类型</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">展示/点击</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">点击率</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">排序</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($list)): ?>
            <tr>
                <td colspan="9" class="px-4 py-8 text-center text-gray-500">暂无数据</td>
            </tr>
            <?php else: ?>
            <?php foreach ($list as $item): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?= $item['ad_id'] ?></td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <?php if ($item['ad_image']): ?>
                        <img src="<?= htmlspecialchars($item['ad_image']) ?>" class="w-16 h-10 object-cover rounded">
                        <?php endif; ?>
                        <span class="font-medium"><?= htmlspecialchars($item['ad_title']) ?></span>
                    </div>
                </td>
                <td class="px-4 py-3 text-sm">
                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">
                        <?= $positions[$item['ad_position']] ?? $item['ad_position'] ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-sm">
                    <?= $types[$item['ad_type']] ?? $item['ad_type'] ?>
                </td>
                <td class="px-4 py-3 text-sm">
                    <?= number_format($item['ad_shows']) ?> / <?= number_format($item['ad_clicks']) ?>
                </td>
                <td class="px-4 py-3 text-sm">
                    <?php 
                    $ctr = $item['ad_shows'] > 0 ? round($item['ad_clicks'] / $item['ad_shows'] * 100, 2) : 0;
                    ?>
                    <span class="<?= $ctr > 1 ? 'text-green-600' : 'text-gray-500' ?>"><?= $ctr ?>%</span>
                </td>
                <td class="px-4 py-3">
                    <?php
                    $now = time();
                    $expired = ($item['ad_end_time'] > 0 && $item['ad_end_time'] < $now);
                    $notStarted = ($item['ad_start_time'] > 0 && $item['ad_start_time'] > $now);
                    ?>
                    <?php if ($expired): ?>
                    <span class="text-gray-400 text-sm">已过期</span>
                    <?php elseif ($notStarted): ?>
                    <span class="text-yellow-600 text-sm">未开始</span>
                    <?php elseif ($item['ad_status']): ?>
                    <span class="text-green-600 text-sm">● 启用</span>
                    <?php else: ?>
                    <span class="text-gray-400 text-sm">○ 禁用</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500"><?= $item['ad_sort'] ?></td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <a href="/admin.php/ad/edit/<?= $item['ad_id'] ?>" class="text-blue-600 hover:underline">编辑</a>
                    <button onclick="toggleAd(<?= $item['ad_id'] ?>)" class="text-yellow-600 hover:underline">
                        <?= $item['ad_status'] ? '禁用' : '启用' ?>
                    </button>
                    <button onclick="deleteItem('/admin.php/ad/delete', <?= $item['ad_id'] ?>)" class="text-red-600 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 分页 -->
<?php if ($totalPages > 1): ?>
<div class="mt-4 flex justify-center gap-2">
    <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>&position=<?= urlencode($position) ?>" class="px-3 py-1 bg-white border rounded hover:bg-gray-50">上一页</a>
    <?php endif; ?>
    
    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
    <a href="?page=<?= $i ?>&position=<?= urlencode($position) ?>" 
       class="px-3 py-1 border rounded <?= $i === $page ? 'bg-primary text-white' : 'bg-white hover:bg-gray-50' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
    
    <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page + 1 ?>&position=<?= urlencode($position) ?>" class="px-3 py-1 bg-white border rounded hover:bg-gray-50">下一页</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
function toggleAd(id) {
    fetch('/admin.php/ad/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg, 'success');
            location.reload();
        } else {
            xpkToast(data.msg, 'error');
        }
    });
}
</script>
