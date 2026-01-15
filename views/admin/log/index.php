<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">操作日志</h2>
        <div class="flex items-center gap-3">
            <button onclick="cleanLogs()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                清理旧日志
            </button>
        </div>
    </div>

    <!-- 筛选 -->
    <form method="get" class="flex gap-4 items-end">
        <input type="hidden" name="s" value="log">
        <div>
            <label class="block text-sm text-gray-600 mb-1">模块</label>
            <select name="module" class="border rounded px-3 py-2">
                <option value="">全部</option>
                <option value="视频" <?= ($filters['module'] ?? '') === '视频' ? 'selected' : '' ?>>视频</option>
                <option value="分类" <?= ($filters['module'] ?? '') === '分类' ? 'selected' : '' ?>>分类</option>
                <option value="演员" <?= ($filters['module'] ?? '') === '演员' ? 'selected' : '' ?>>演员</option>
                <option value="文章" <?= ($filters['module'] ?? '') === '文章' ? 'selected' : '' ?>>文章</option>
                <option value="用户" <?= ($filters['module'] ?? '') === '用户' ? 'selected' : '' ?>>用户</option>
                <option value="采集" <?= ($filters['module'] ?? '') === '采集' ? 'selected' : '' ?>>采集</option>
                <option value="配置" <?= ($filters['module'] ?? '') === '配置' ? 'selected' : '' ?>>配置</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">筛选</button>
        <a href="/<?= $adminEntry ?>?s=log" class="text-gray-500 hover:text-gray-700 px-4 py-2">重置</a>
    </form>

    <!-- 列表 -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">ID</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">管理员</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">操作</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">模块</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">内容</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">IP</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">时间</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($list)): ?>
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">暂无日志</td>
                </tr>
                <?php else: ?>
                <?php foreach ($list as $item): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm"><?= $item['log_id'] ?></td>
                    <td class="px-4 py-3 text-sm"><?= htmlspecialchars($item['admin_name']) ?></td>
                    <td class="px-4 py-3 text-sm">
                        <span class="px-2 py-1 rounded text-xs <?= $item['log_action'] === '删除' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' ?>">
                            <?= htmlspecialchars($item['log_action']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm"><?= htmlspecialchars($item['log_module']) ?></td>
                    <td class="px-4 py-3 text-sm max-w-xs truncate" title="<?= htmlspecialchars($item['log_content']) ?>">
                        <?= htmlspecialchars($item['log_content']) ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500"><?= htmlspecialchars($item['log_ip']) ?></td>
                    <td class="px-4 py-3 text-sm text-gray-500"><?= date('Y-m-d H:i:s', $item['log_time']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 分页 -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?s=log&page=<?= $i ?>&module=<?= urlencode($filters['module'] ?? '') ?>" 
           class="px-3 py-1 rounded <?= $i === $page ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function cleanLogs() {
    xpkConfirm('确定要清理30天前的日志吗？', function() {
        fetch(adminUrl('/log/clean'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=<?= $csrfToken ?>&days=30'
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
    });
}
</script>
