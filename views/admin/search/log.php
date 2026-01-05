<h1 class="text-2xl font-bold mb-6">搜索日志</h1>

<!-- 搜索筛选 -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form method="GET" class="flex items-center gap-4">
        <div class="flex-1">
            <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" 
                placeholder="搜索关键词..." class="w-full border rounded px-3 py-2">
        </div>
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
            搜索
        </button>
        <?php if (!empty($keyword)): ?>
        <a href="/<?= $adminEntry ?>?s=search/log" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded">
            清除
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- 日志列表 -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">
            搜索记录 
            <?php if (!empty($logs['total'])): ?>
                <span class="text-sm text-gray-500">(共 <?= number_format($logs['total']) ?> 条)</span>
            <?php endif; ?>
        </h3>
        <button onclick="cleanLog()" class="text-red-600 hover:text-red-800 text-sm">
            清理日志
        </button>
    </div>

    <?php if (empty($logs['list'])): ?>
        <div class="p-8 text-center text-gray-500">
            暂无搜索日志
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            搜索词
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            IP地址
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            搜索时间
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($logs['list'] as $log): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($log['keyword']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= htmlspecialchars($log['search_ip']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('Y-m-d H:i:s', $log['search_time']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 分页 -->
        <?php if ($logs['totalPages'] > 1): ?>
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <?php if ($logs['page'] > 1): ?>
                    <a href="?page=<?= $logs['page'] - 1 ?><?= !empty($keyword) ? '&keyword=' . urlencode($keyword) : '' ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        上一页
                    </a>
                    <?php endif; ?>
                    <?php if ($logs['page'] < $logs['totalPages']): ?>
                    <a href="?page=<?= $logs['page'] + 1 ?><?= !empty($keyword) ? '&keyword=' . urlencode($keyword) : '' ?>" 
                       class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        下一页
                    </a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            显示第 <span class="font-medium"><?= ($logs['page'] - 1) * $logs['pageSize'] + 1 ?></span> 
                            到 <span class="font-medium"><?= min($logs['page'] * $logs['pageSize'], $logs['total']) ?></span> 
                            条，共 <span class="font-medium"><?= $logs['total'] ?></span> 条记录
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <?php for ($i = max(1, $logs['page'] - 2); $i <= min($logs['totalPages'], $logs['page'] + 2); $i++): ?>
                            <a href="?page=<?= $i ?><?= !empty($keyword) ? '&keyword=' . urlencode($keyword) : '' ?>" 
                               class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?= $i == $logs['page'] ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?>">
                                <?= $i ?>
                            </a>
                            <?php endfor; ?>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function cleanLog() {
    xpkConfirm('确定要清理90天前的搜索日志吗？', function() {
        fetch(adminUrl('/search/cleanLog'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=<?= $csrfToken ?>&keep_days=90'
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                xpkToast(data.msg, 'error');
            }
        })
        .catch(() => xpkToast('请求失败', 'error'));
    });
}
</script>