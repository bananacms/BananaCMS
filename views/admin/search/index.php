<h1 class="text-2xl font-bold mb-6">搜索统计</h1>

<?php if (!empty($flash)): ?>
    <div class="mb-4 p-4 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
<?php endif; ?>

<!-- 统计概览 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">总搜索次数</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_searches']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">独立关键词</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['unique_keywords']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">独立用户</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['unique_ips']) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- 热门搜索词 -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">热门搜索词 (30天)</h3>
        </div>
        <div class="p-6">
            <?php if (empty($hotKeywords)): ?>
                <p class="text-gray-500 text-center py-8">暂无搜索数据</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($hotKeywords as $index => $item): ?>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-medium text-white bg-red-500 rounded-full mr-3">
                                <?= $index + 1 ?>
                            </span>
                            <span class="text-gray-900"><?= htmlspecialchars($item['keyword']) ?></span>
                        </div>
                        <span class="text-sm text-gray-500"><?= number_format($item['search_count']) ?>次</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 最新搜索词 -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">最新搜索词</h3>
        </div>
        <div class="p-6">
            <?php if (empty($recentKeywords)): ?>
                <p class="text-gray-500 text-center py-8">暂无搜索数据</p>
            <?php else: ?>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($recentKeywords as $item): ?>
                    <span class="inline-block px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full">
                        <?= htmlspecialchars($item['keyword']) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 操作按钮 -->
<div class="mt-8 flex space-x-4">
    <a href="/<?= $adminEntry ?>/search/log" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
        查看搜索日志
    </a>
    <button onclick="cleanLog()" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded">
        清理日志
    </button>
</div>

<script>
function cleanLog() {
    if (!xpkConfirm('确定要清理90天前的搜索日志吗？')) return;
    
    fetch(adminUrl('/search/cleanLog'), {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_token=<?= $csrfToken ?? '' ?>&keep_days=90'
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
}
</script>