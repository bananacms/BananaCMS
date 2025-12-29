<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">播放器管理</h1>
    <a href="/admin.php/player/add" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
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
                    <span class="text-green-600" title="<?= htmlspecialchars($player['player_parse']) ?>">已配置</span>
                    <?php else: ?>
                    <span class="text-gray-400">未配置</span>
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
                    <a href="/admin.php/player/edit/<?= $player['player_id'] ?>" class="text-blue-600 hover:text-blue-800 mr-3">编辑</a>
                    <button onclick="deletePlayer(<?= $player['player_id'] ?>, '<?= htmlspecialchars($player['player_name'], ENT_QUOTES) ?>')" class="text-red-600 hover:text-red-800">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
    <h3 class="font-medium text-blue-800 mb-2">💡 提示</h3>
    <ul class="text-sm text-blue-700 space-y-1">
        <li>• 播放器标识需与资源站返回的 vod_play_from 字段值一致</li>
        <li>• 采集时只会入库已启用的播放器对应的播放源</li>
        <li>• 解析接口用于需要解析的播放源，如优酷、爱奇艺等</li>
        <li>• 排序数字越小越靠前</li>
    </ul>
</div>

<input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrfToken) ?>">

<script>
function deletePlayer(id, name) {
    xpkConfirm('确定要删除播放器"' + name + '"吗？', function() {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('_token', document.getElementById('csrfToken').value);
        
        fetch('/admin.php/player/delete', {
            method: 'POST',
            body: formData
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
    const formData = new FormData();
    formData.append('id', id);
    formData.append('_token', document.getElementById('csrfToken').value);
    
    fetch('/admin.php/player/toggle', {
        method: 'POST',
        body: formData
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
