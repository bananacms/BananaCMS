<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">文章分类管理</h1>
    <button onclick="openArtTypeModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
        + 添加分类
    </button>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">分类名称</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">排序</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($list)): ?>
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">暂无数据</td>
            </tr>
            <?php else: ?>
            <?php foreach ($list as $type): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?= $type['type_id'] ?></td>
                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($type['type_name']) ?></td>
                <td class="px-4 py-3 text-sm"><?= $type['type_sort'] ?></td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded text-xs <?= $type['type_status'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                        <?= $type['type_status'] ? '启用' : '禁用' ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <button onclick="openArtTypeModal(<?= $type['type_id'] ?>)" class="text-blue-500 hover:underline">编辑</button>
                    <button onclick="deleteItem('/<?= $adminEntry ?>?s=art_type/delete', <?= $type['type_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 文章分类模态框 -->
<div id="artTypeModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h3 id="artTypeModalTitle" class="text-lg font-bold">添加分类</h3>
            <button onclick="closeArtTypeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="artTypeForm" onsubmit="saveArtType(event)" class="p-6">
            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="type_id" id="artTypeId" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">分类名称 *</label>
                    <input type="text" name="type_name" id="artTypeName" required class="w-full border rounded px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                        <input type="number" name="type_sort" id="artTypeSort" value="0" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                        <select name="type_status" id="artTypeStatus" class="w-full border rounded px-3 py-2">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeArtTypeModal()" class="px-4 py-2 border rounded hover:bg-gray-50">取消</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
function openArtTypeModal(id = null) {
    const modal = document.getElementById('artTypeModal');
    const title = document.getElementById('artTypeModalTitle');
    
    document.getElementById('artTypeForm').reset();
    document.getElementById('artTypeId').value = '';
    
    if (id) {
        title.textContent = '编辑分类';
        fetch(adminUrl('/art_type/getOne?id=' + id))
            .then(r => r.json())
            .then(data => {
                if (data.code === 0) {
                    const t = data.data;
                    document.getElementById('artTypeId').value = t.type_id;
                    document.getElementById('artTypeName').value = t.type_name;
                    document.getElementById('artTypeSort').value = t.type_sort;
                    document.getElementById('artTypeStatus').value = t.type_status;
                }
            });
    } else {
        title.textContent = '添加分类';
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeArtTypeModal() {
    const modal = document.getElementById('artTypeModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function saveArtType(e) {
    e.preventDefault();
    const form = document.getElementById('artTypeForm');
    const formData = new FormData(form);
    const id = formData.get('type_id');
    const url = id ? adminUrl('/art_type/edit/' + id) : adminUrl('/art_type/add');
    
    fetch(url, {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg || '保存成功', 'success');
            closeArtTypeModal();
            location.reload();
        } else {
            xpkToast(data.msg || '保存失败', 'error');
        }
    })
    .catch(() => xpkToast('请求失败', 'error'));
}

document.getElementById('artTypeModal').addEventListener('click', function(e) {
    if (e.target === this) closeArtTypeModal();
});
</script>
