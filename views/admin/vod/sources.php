<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">播放源管理</h1>
    <div class="flex gap-2">
        <a href="/admin.php/vod/replace" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            🔄 地址替换
        </a>
        <a href="/admin.php/vod" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            ← 返回视频列表
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- 播放源列表 -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <h3 class="font-bold">当前播放源</h3>
            <p class="text-sm text-gray-500">共 <?= count($sourceStats) ?> 个播放源</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">播放源名称</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">视频数量</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($sourceStats)): ?>
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">暂无播放源数据</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($sourceStats as $name => $count): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="font-medium"><?= htmlspecialchars($name) ?></span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            <?= number_format($count) ?> 个视频
                        </td>
                        <td class="px-4 py-3 space-x-2">
                            <button onclick="showRenameModal('<?= htmlspecialchars(addslashes($name)) ?>')" class="text-blue-500 hover:text-blue-700">重命名</button>
                            <button onclick="deleteSource('<?= htmlspecialchars(addslashes($name)) ?>')" class="text-red-500 hover:text-red-700">删除</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 操作说明 -->
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold mb-4">📋 功能说明</h3>
            <div class="space-y-3 text-sm text-gray-600">
                <div>
                    <p class="font-medium text-gray-700">重命名播放源</p>
                    <p class="text-gray-500">修改播放源的显示名称，不影响播放地址</p>
                </div>
                <div>
                    <p class="font-medium text-gray-700">删除播放源</p>
                    <p class="text-gray-500">从所有视频中移除该播放源及其地址</p>
                </div>
            </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h4 class="font-medium text-yellow-800 mb-2">⚠️ 注意事项</h4>
            <ul class="text-sm text-yellow-700 space-y-1">
                <li>• 删除播放源会同时删除该源的所有播放地址</li>
                <li>• 操作不可撤销，请先备份数据库</li>
                <li>• 如果视频只有一个播放源，删除后将无法播放</li>
            </ul>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-medium text-blue-800 mb-2">💡 提示</h4>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• 播放源名称来自采集的资源站</li>
                <li>• 可在"播放器管理"中配置播放器</li>
                <li>• 如需替换播放地址，请使用"地址替换"功能</li>
            </ul>
        </div>
    </div>
</div>

<!-- 重命名弹窗 -->
<div id="renameModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-bold mb-4">重命名播放源</h3>
        <form id="renameForm">
            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="old_name" id="renameOldName">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">原名称</label>
                <input type="text" id="renameOldNameDisplay" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">新名称</label>
                <input type="text" name="new_name" id="renameNewName" class="w-full border rounded px-3 py-2" required>
            </div>
            
            <div class="flex justify-end gap-2">
                <button type="button" onclick="hideRenameModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">取消</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">确定</button>
            </div>
        </form>
    </div>
</div>

<script>
function showRenameModal(name) {
    document.getElementById('renameOldName').value = name;
    document.getElementById('renameOldNameDisplay').value = name;
    document.getElementById('renameNewName').value = name;
    document.getElementById('renameModal').classList.remove('hidden');
    document.getElementById('renameModal').classList.add('flex');
    document.getElementById('renameNewName').focus();
    document.getElementById('renameNewName').select();
}

function hideRenameModal() {
    document.getElementById('renameModal').classList.add('hidden');
    document.getElementById('renameModal').classList.remove('flex');
}

document.getElementById('renameForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/admin.php/vod/renameSource', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            xpkToast(data.msg || '操作失败', 'error');
        }
    })
    .catch(() => xpkToast('请求失败', 'error'));
});

function deleteSource(name) {
    xpkConfirm(`确定要删除播放源 "${name}" 吗？\n\n这将从所有视频中移除该播放源及其地址，操作不可撤销！`, () => {
        fetch('/admin.php/vod/deleteSource', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=<?= $csrfToken ?>&source=' + encodeURIComponent(name)
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                xpkToast(data.msg || '删除失败', 'error');
            }
        })
        .catch(() => xpkToast('请求失败', 'error'));
    });
}

// 点击弹窗外部关闭
document.getElementById('renameModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideRenameModal();
    }
});
</script>
