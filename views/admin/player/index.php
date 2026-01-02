<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">播放器管理</h1>
    <a href="/<?= $adminEntry ?>/player/add" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        <i class="fas fa-plus mr-1"></i>添加播放器
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">名称</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">标识</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">解析接口</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">排序</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">状态</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($players)): ?>
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-play-circle text-4xl text-gray-300 mb-3"></i>
                        <p>暂无播放器数据</p>
                        <p class="text-sm mt-1">点击"添加播放器"创建新播放器</p>
                    </div>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($players as $player): ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?= $player['player_id'] ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-medium text-gray-900"><?= htmlspecialchars($player['player_name']) ?></div>
                    <?php if ($player['player_tip']): ?>
                    <div class="text-xs text-gray-500"><?= htmlspecialchars($player['player_tip']) ?></div>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <code class="px-2 py-1 bg-gray-100 rounded text-sm"><?= htmlspecialchars($player['player_code']) ?></code>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php if ($player['player_parse']): ?>
                    <span class="text-blue-600 flex items-center" title="<?= htmlspecialchars($player['player_parse']) ?>">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        解析接口
                    </span>
                    <?php else: ?>
                    <span class="text-green-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H15M9 10V9a2 2 0 012-2h2a2 2 0 012 2v1M9 10v5a2 2 0 002 2h2a2 2 0 002-2v-5m-6 0h6"></path>
                        </svg>
                        内置DPlayer
                    </span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?= $player['player_sort'] ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <button onclick="toggleStatus(<?= $player['player_id'] ?>)" class="<?= $player['player_status'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' ?> px-2 py-1 text-xs rounded-full cursor-pointer hover:opacity-80">
                        <?= $player['player_status'] ? '启用' : '禁用' ?>
                    </button>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <a href="/<?= $adminEntry ?>/player/edit/<?= $player['player_id'] ?>" class="text-blue-600 hover:text-blue-800 mr-3">编辑</a>
                    <button onclick="deletePlayer(<?= $player['player_id'] ?>, '<?= htmlspecialchars($player['player_name'], ENT_QUOTES) ?>')" class="text-red-600 hover:text-red-800">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
    <h3 class="font-medium text-blue-800 mb-2 flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        使用说明
    </h3>
    <ul class="text-sm text-blue-700 space-y-1">
        <li>• <strong>播放器标识</strong>：需与资源站返回的 vod_play_from 字段值一致</li>
        <li>• <strong>解析接口</strong>：填写第三方解析地址（如 https://jx.xxx.com/?url=），视频地址会追加到末尾</li>
        <li>• <strong>内置播放器</strong>：解析接口留空时，自动使用内置 DPlayer 播放器（支持 m3u8/mp4 直链）</li>
        <li>• 采集时只会入库已启用的播放器对应的播放源</li>
    </ul>
</div>

<script>
function deletePlayer(id, name) {
    xpkConfirm('确定要删除播放器"' + name + '"吗？', function() {
        fetch(adminUrl('/player/delete'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id + '&_token=<?= $csrfToken ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast('删除成功', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                xpkToast(data.msg || '删除失败', 'error');
            }
        })
        .catch(() => xpkToast('删除失败', 'error'));
    });
}

function toggleStatus(id) {
    fetch(adminUrl('/player/toggle'), {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id + '&_token=<?= $csrfToken ?>'
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            xpkToast(data.msg || '操作失败', 'error');
        }
    })
    .catch(() => xpkToast('操作失败', 'error'));
}
</script>
