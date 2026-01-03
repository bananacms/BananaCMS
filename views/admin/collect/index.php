<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">采集管理</h1>
    <div class="flex gap-2">
        <a href="/<?= $adminEntry ?>/collect/log" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            采集日志
        </a>
        <a href="/<?= $adminEntry ?>/collect/cron" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
            ⏰ 定时采集
        </a>
        <a href="/<?= $adminEntry ?>/collect/add" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            + 添加采集站
        </a>
    </div>
</div>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
    <p class="text-yellow-800 text-sm">
        <strong>使用说明：</strong>
        1. 添加资源站API地址 → 2. 绑定分类 → 3. 执行采集
    </p>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">名称</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">API地址</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($collects)): ?>
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    暂无采集站，<a href="/<?= $adminEntry ?>/collect/add" class="text-blue-500 hover:underline">点击添加</a>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($collects as $c): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?= $c['collect_id'] ?></td>
                <td class="px-4 py-3 text-sm font-medium"><?= htmlspecialchars($c['collect_name']) ?></td>
                <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate"><?= htmlspecialchars($c['collect_api']) ?></td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded text-xs <?= $c['collect_status'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                        <?= $c['collect_status'] ? '启用' : '禁用' ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <a href="/<?= $adminEntry ?>/collect/bind/<?= $c['collect_id'] ?>" class="text-purple-500 hover:underline">绑定</a>
                    <a href="/<?= $adminEntry ?>/collect/run/<?= $c['collect_id'] ?>" class="text-green-500 hover:underline">采集</a>
                    <a href="/<?= $adminEntry ?>/collect/edit/<?= $c['collect_id'] ?>" class="text-blue-500 hover:underline">编辑</a>
                    <button onclick="deleteCollectVods(<?= $c['collect_id'] ?>, '<?= htmlspecialchars(addslashes($c['collect_name'])) ?>')" class="text-orange-500 hover:underline">清空视频</button>
                    <button onclick="deleteItem('/<?= $adminEntry ?>/collect/delete', <?= $c['collect_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h3 class="font-bold mb-4">资源站合作</h3>
    <div class="space-y-2 text-sm">
        <p class="text-gray-600">资源站合作内置联系：</p>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-800 font-medium flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Telegram: <a href="https://t.me/ddys_io" target="_blank" class="text-blue-600 hover:underline">@ddys_io</a>
            </p>
        </div>
    </div>
</div>

<script>
function deleteCollectVods(collectId, collectName) {
    xpkConfirm(`确定要删除采集站「${collectName}」的所有视频吗？\n\n此操作不可撤销！`, function() {
        fetch(adminUrl('/collect/deleteVods'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'collect_id=' + collectId + '&_token=' + window.CSRF_TOKEN
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
            } else {
                xpkToast(data.msg || '删除失败', 'error');
            }
        })
        .catch(() => xpkToast('请求失败', 'error'));
    });
}
</script>
