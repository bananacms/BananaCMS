<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold flex items-center">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
        </svg>
        转码广告管理
    </h1>
    <div class="flex gap-2">
        <a href="/<?= $adminEntry ?>/transcode" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">← 返回转码</a>
        <a href="/<?= $adminEntry ?>/transcode/ad/add" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">+ 添加广告</a>
    </div>
</div>

<?php if (!empty($flash)): ?>
<div class="mb-4 p-4 rounded <?= $flash['type'] === 'error' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<!-- 广告配置 -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-lg font-bold mb-4">广告配置</h2>
    <form id="configForm" class="space-y-4" data-no-ajax="true">
        <div class="flex items-center gap-6">
            <label class="flex items-center">
                <input type="checkbox" name="enable" value="1" <?= !empty($config['enable']) ? 'checked' : '' ?> class="mr-2">
                <span class="font-medium">启用转码广告</span>
            </label>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <label class="flex items-center p-3 bg-gray-50 rounded">
                <input type="checkbox" name="head_enable" value="1" <?= !empty($config['head_enable']) ? 'checked' : '' ?> class="mr-2">
                <span>片头广告</span>
            </label>
            
            <label class="flex items-center p-3 bg-gray-50 rounded">
                <input type="checkbox" name="middle_enable" value="1" <?= !empty($config['middle_enable']) ? 'checked' : '' ?> class="mr-2">
                <span>片中广告</span>
            </label>
            
            <label class="flex items-center p-3 bg-gray-50 rounded">
                <input type="checkbox" name="tail_enable" value="1" <?= !empty($config['tail_enable']) ? 'checked' : '' ?> class="mr-2">
                <span>片尾广告</span>
            </label>
        </div>
        
        <div class="flex items-center gap-2">
            <label class="text-gray-600">片中广告间隔：</label>
            <input type="number" name="middle_interval" value="<?= $config['middle_interval'] ?? 300 ?>" min="60" class="border rounded px-3 py-2 w-24">
            <span class="text-gray-500">秒（最小60秒）</span>
        </div>
        
        <div class="pt-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">保存配置</button>
        </div>
    </form>
</div>

<!-- 广告列表 -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">名称</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">位置</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">时长</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">排序</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($list)): ?>
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">暂无广告，点击右上角添加</td>
            </tr>
            <?php else: ?>
            <?php foreach ($list as $ad): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?= $ad['ad_id'] ?></td>
                <td class="px-4 py-3">
                    <div class="font-medium"><?= htmlspecialchars($ad['ad_name']) ?></div>
                    <div class="text-xs text-gray-400 truncate max-w-xs"><?= htmlspecialchars($ad['ad_file']) ?></div>
                </td>
                <td class="px-4 py-3">
                    <?php
                    $posLabels = ['head' => '片头', 'middle' => '片中', 'tail' => '片尾'];
                    $posColors = ['head' => 'bg-blue-100 text-blue-700', 'middle' => 'bg-yellow-100 text-yellow-700', 'tail' => 'bg-green-100 text-green-700'];
                    ?>
                    <span class="px-2 py-1 rounded text-xs <?= $posColors[$ad['ad_position']] ?? 'bg-gray-100' ?>">
                        <?= $posLabels[$ad['ad_position']] ?? $ad['ad_position'] ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-sm"><?= gmdate('i:s', $ad['ad_duration']) ?></td>
                <td class="px-4 py-3 text-sm"><?= $ad['ad_sort'] ?></td>
                <td class="px-4 py-3">
                    <button onclick="toggleStatus(<?= $ad['ad_id'] ?>)" class="px-2 py-1 rounded text-xs <?= $ad['ad_status'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                        <?= $ad['ad_status'] ? '启用' : '禁用' ?>
                    </button>
                </td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <?php if (!empty($ad['ad_file'])): ?>
                    <button onclick="previewAd('<?= htmlspecialchars($ad['ad_file']) ?>')" class="text-green-500 hover:underline">预览</button>
                    <?php endif; ?>
                    <a href="/<?= $adminEntry ?>/transcode/ad/edit/<?= $ad['ad_id'] ?>" class="text-blue-500 hover:underline">编辑</a>
                    <button onclick="deleteAd(<?= $ad['ad_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 预览弹窗 -->
<div id="previewModal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50" onclick="closePreview()">
    <div class="bg-black rounded-lg overflow-hidden max-w-3xl w-full mx-4" onclick="event.stopPropagation()">
        <video id="previewVideo" controls class="w-full"></video>
    </div>
</div>

<script>
// 保存配置
document.getElementById('configForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('_token', '<?= $csrfToken ?>');
    
    fetch(adminUrl('/transcode/ad/saveConfig'), {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        xpkToast(data.msg || (data.code === 0 ? '保存成功' : '保存失败'), data.code === 0 ? 'success' : 'error');
        if (data.code === 0) setTimeout(() => location.reload(), 500);
    })
    .catch(err => {
        console.error(err);
        xpkToast('请求失败', 'error');
    });
});

// 切换状态
function toggleStatus(id) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('_token', '<?= $csrfToken ?>');
    
    fetch(adminUrl('/transcode/ad/toggle'), {
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
    });
}

// 删除广告
function deleteAd(id) {
    xpkConfirm('确定删除此广告？', function() {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('_token', '<?= $csrfToken ?>');
        
        fetch(adminUrl('/transcode/ad/delete'), {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                xpkToast(data.msg || '删除失败', 'error');
            }
        });
    });
}

// 预览广告
function previewAd(url) {
    const modal = document.getElementById('previewModal');
    const video = document.getElementById('previewVideo');
    video.src = url;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    video.play();
}

function closePreview() {
    const modal = document.getElementById('previewModal');
    const video = document.getElementById('previewVideo');
    video.pause();
    video.src = '';
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
