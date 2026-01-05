<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">操作日志</h2>
        <div class="flex items-center gap-3">
            <div class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded flex items-center">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                IP已混淆保护
            </div>
            <button onclick="cleanLogs()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                清理旧日志
            </button>
        </div>
    </div>

    <!-- IP混淆说明 -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-blue-800 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    IP地址安全保护
                </h4>
                <div class="mt-2 text-sm text-blue-700">
                    <p class="mb-2"><?= htmlspecialchars($ipMaskingInfo['description']) ?></p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        <?php foreach ($ipMaskingInfo['features'] as $feature): ?>
                        <li><?= htmlspecialchars($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="mt-2 text-xs">
                        <strong>算法:</strong> <?= htmlspecialchars($ipMaskingInfo['algorithm']) ?> | 
                        <strong>格式:</strong> <?= htmlspecialchars($ipMaskingInfo['format']) ?> | 
                        <strong>安全级别:</strong> <span class="text-green-600 font-medium"><?= htmlspecialchars($ipMaskingInfo['security_level']) ?></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 筛选 -->
    <form method="get" class="flex gap-4 items-end">
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
        <a href="?page=<?= $i ?>&module=<?= urlencode($filters['module'] ?? '') ?>" 
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
