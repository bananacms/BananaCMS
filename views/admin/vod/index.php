<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">视频管理</h1>
    <div class="flex gap-2">
        <a href="/<?= $adminEntry ?>/vod/replace" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            地址替换
        </a>
        <a href="/<?= $adminEntry ?>/vod/sources" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            播放源
        </a>
        <a href="/<?= $adminEntry ?>/vod/add" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            + 添加视频
        </a>
    </div>
</div>

<!-- 搜索筛选 -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm text-gray-600 mb-1">分类</label>
            <select name="type" class="border rounded px-3 py-2">
                <option value="">全部分类</option>
                <?php foreach ($types as $t): ?>
                <option value="<?= $t['type_id'] ?>" <?= $typeId == $t['type_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['type_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">采集站</label>
            <select name="collect_id" class="border rounded px-3 py-2">
                <option value="">全部来源</option>
                <option value="0" <?= $collectId === '0' ? 'selected' : '' ?>>手动添加</option>
                <?php foreach ($collects as $c): ?>
                <option value="<?= $c['collect_id'] ?>" <?= $collectId == $c['collect_id'] && $collectId !== '' ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['collect_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">状态</label>
            <select name="status" class="border rounded px-3 py-2">
                <option value="">全部状态</option>
                <option value="1" <?= $status === '1' ? 'selected' : '' ?>>已发布</option>
                <option value="0" <?= $status === '0' ? 'selected' : '' ?>>未发布</option>
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">关键词</label>
            <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" 
                class="border rounded px-3 py-2" placeholder="名称/演员">
        </div>
        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">搜索</button>
        <a href="/<?= $adminEntry ?>/vod" class="text-gray-500 hover:text-gray-700 py-2">重置</a>
    </form>
</div>

<!-- 列表 -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <!-- 批量操作栏 -->
    <div class="px-4 py-2 bg-gray-50 border-b flex items-center gap-4" id="batchBar" style="display:none;">
        <span class="text-sm text-gray-600">已选 <span id="selectedCount">0</span> 项</span>
        <button onclick="batchLock(1)" class="text-sm text-yellow-600 hover:underline">批量锁定</button>
        <button onclick="batchLock(0)" class="text-sm text-gray-600 hover:underline">批量解锁</button>
        <button onclick="batchDelete()" class="text-sm text-red-600 hover:underline">批量删除</button>
    </div>
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">
                    <input type="checkbox" id="checkAll" class="w-4 h-4 rounded">
                </th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">名称</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">分类</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">点击</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">更新时间</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($list)): ?>
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">暂无数据</td>
            </tr>
            <?php else: ?>
            <?php foreach ($list as $vod): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <input type="checkbox" class="vod-check w-4 h-4 rounded" value="<?= $vod['vod_id'] ?>">
                </td>
                <td class="px-4 py-3 text-sm"><?= $vod['vod_id'] ?></td>
                <td class="px-4 py-3">
                    <div class="flex items-center">
                        <?php if ($vod['vod_pic']): ?>
                        <img src="<?= htmlspecialchars($vod['vod_pic']) ?>" class="w-12 h-16 object-cover rounded mr-3">
                        <?php endif; ?>
                        <div>
                            <div class="flex items-center gap-2">
                                <a href="<?= xpk_page_url('vod_detail', ['id' => $vod['vod_id'], 'slug' => $vod['vod_slug']]) ?>" target="_blank" class="text-sm font-medium hover:text-blue-600 hover:underline">
                                    <?= htmlspecialchars($vod['vod_name']) ?>
                                </a>
                                <?php if (!empty($vod['vod_lock'])): ?>
                                <span class="text-yellow-500 flex items-center" title="已锁定，采集时跳过">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($vod['vod_remarks']): ?>
                            <p class="text-xs text-gray-400"><?= htmlspecialchars($vod['vod_remarks']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($vod['type_name'] ?? '-') ?></td>
                <td class="px-4 py-3 text-sm"><?= number_format($vod['vod_hits']) ?></td>
                <td class="px-4 py-3">
                    <button onclick="toggleStatus(<?= $vod['vod_id'] ?>, <?= $vod['vod_status'] ? 0 : 1 ?>)"
                        class="px-2 py-1 rounded text-xs <?= $vod['vod_status'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                        <?= $vod['vod_status'] ? '已发布' : '未发布' ?>
                    </button>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500"><?= date('Y-m-d H:i', $vod['vod_time']) ?></td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <a href="/<?= $adminEntry ?>/vod/edit/<?= $vod['vod_id'] ?>" class="text-blue-500 hover:underline">编辑</a>
                    <button onclick="toggleLock(<?= $vod['vod_id'] ?>)" class="<?= !empty($vod['vod_lock']) ? 'text-yellow-500' : 'text-gray-400' ?> hover:underline"><?= !empty($vod['vod_lock']) ? '解锁' : '锁定' ?></button>
                    <button onclick="deleteSingleVod(<?= $vod['vod_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 分页 -->
<?php 
$baseUrl = "/<?= $adminEntry ?>/vod?type={$typeId}&status={$status}&collect_id={$collectId}&keyword=" . urlencode($keyword);
include __DIR__ . '/../components/pagination.php'; 
?>

<script>
// 全选/取消全选
document.getElementById('checkAll').addEventListener('change', function() {
    document.querySelectorAll('.vod-check').forEach(cb => cb.checked = this.checked);
    updateBatchBar();
});

// 单个选择
document.querySelectorAll('.vod-check').forEach(cb => {
    cb.addEventListener('change', updateBatchBar);
});

function updateBatchBar() {
    const checked = document.querySelectorAll('.vod-check:checked');
    const bar = document.getElementById('batchBar');
    document.getElementById('selectedCount').textContent = checked.length;
    bar.style.display = checked.length > 0 ? 'flex' : 'none';
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.vod-check:checked')).map(cb => cb.value);
}

function toggleStatus(id, status) {
    fetch(adminUrl('/vod/status'), {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id + '&status=' + status + '&_token=<?= $csrfToken ?>'
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            location.reload();
        } else {
            xpkToast(data.msg, 'error');
        }
    });
}

function toggleLock(id) {
    fetch(adminUrl('/vod/lock'), {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id + '&_token=<?= $csrfToken ?>'
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

function batchLock(lock) {
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    
    fetch(adminUrl('/vod/batchLock'), {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'ids[]=' + ids.join('&ids[]=') + '&lock=' + lock + '&_token=<?= $csrfToken ?>'
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

function batchDelete() {
    const ids = getSelectedIds();
    if (ids.length === 0) {
        xpkToast('请选择要删除的视频', 'warning');
        return;
    }
    xpkConfirm('确定要删除选中的 ' + ids.length + ' 个视频吗？', function() {
        const formData = new FormData();
        ids.forEach(id => formData.append('ids[]', id));
        formData.append('_token', '<?= $csrfToken ?>');
        fetch(adminUrl('/vod/delete'), {
            method: 'POST',
            body: formData
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

function deleteSingleVod(id) {
    xpkConfirm('确定要删除吗？', function() {
        fetch(adminUrl('/vod/delete'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id + '&_token=<?= $csrfToken ?>'
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
