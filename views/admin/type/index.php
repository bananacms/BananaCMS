<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">分类管理</h1>
    <div class="flex gap-2">
        <button id="batchDeleteBtn" onclick="batchDelete()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded hidden">
            批量删除 (<span id="selectedCount">0</span>)
        </button>
        <button onclick="openTypeModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            + 添加分类
        </button>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 w-10">
                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="rounded">
                </th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">分类名称</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">英文名</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">排序</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($types)): ?>
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">暂无数据</td>
            </tr>
            <?php else: ?>
            <?php foreach ($types as $type): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <input type="checkbox" class="type-checkbox rounded" value="<?= $type['type_id'] ?>" onchange="updateSelectedCount()">
                </td>
                <td class="px-4 py-3 text-sm"><?= $type['type_id'] ?></td>
                <td class="px-4 py-3 text-sm">
                    <?= str_repeat('　├─ ', $type['level'] ?? 0) ?>
                    <?= htmlspecialchars($type['type_name']) ?>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500"><?= htmlspecialchars($type['type_en']) ?></td>
                <td class="px-4 py-3 text-sm"><?= $type['type_sort'] ?></td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded text-xs <?= $type['type_status'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                        <?= $type['type_status'] ? '启用' : '禁用' ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <button onclick="openTypeModal(<?= $type['type_id'] ?>)" class="text-blue-500 hover:underline">编辑</button>
                    <button onclick="deleteItem(adminUrl('/type/delete'), <?= $type['type_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 分类模态框 -->
<div id="typeModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h3 id="typeModalTitle" class="text-lg font-bold">添加分类</h3>
            <button onclick="closeTypeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="typeForm" onsubmit="saveType(event)" class="p-6">
            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="type_id" id="typeId" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">父级分类</label>
                    <select name="type_pid" id="typePid" class="w-full border rounded px-3 py-2">
                        <option value="0">顶级分类</option>
                        <?php foreach ($parentTypes ?? [] as $pt): ?>
                        <option value="<?= $pt['type_id'] ?>"><?= htmlspecialchars($pt['type_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">分类名称 *</label>
                    <input type="text" name="type_name" id="typeName" required class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">英文名</label>
                    <input type="text" name="type_en" id="typeEn" class="w-full border rounded px-3 py-2" placeholder="用于URL">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                        <input type="number" name="type_sort" id="typeSort" value="0" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                        <select name="type_status" id="typeStatus" class="w-full border rounded px-3 py-2">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SEO关键词</label>
                    <input type="text" name="type_key" id="typeKey" class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SEO描述</label>
                    <textarea name="type_des" id="typeDes" rows="3" class="w-full border rounded px-3 py-2"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeTypeModal()" class="px-4 py-2 border rounded hover:bg-gray-50">取消</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTypeModal(id = null) {
    const modal = document.getElementById('typeModal');
    const title = document.getElementById('typeModalTitle');
    
    // 重置表单
    document.getElementById('typeForm').reset();
    document.getElementById('typeId').value = '';
    
    if (id) {
        title.textContent = '编辑分类';
        // 加载数据
        fetch(adminUrl('/type/getOne?id=' + id))
            .then(r => r.json())
            .then(data => {
                if (data.code === 0) {
                    const t = data.data;
                    document.getElementById('typeId').value = t.type_id;
                    document.getElementById('typePid').value = t.type_pid;
                    document.getElementById('typeName').value = t.type_name;
                    document.getElementById('typeEn').value = t.type_en;
                    document.getElementById('typeSort').value = t.type_sort;
                    document.getElementById('typeStatus').value = t.type_status;
                    document.getElementById('typeKey').value = t.type_key || '';
                    document.getElementById('typeDes').value = t.type_des || '';
                }
            });
    } else {
        title.textContent = '添加分类';
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeTypeModal() {
    const modal = document.getElementById('typeModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function saveType(e) {
    e.preventDefault();
    const form = document.getElementById('typeForm');
    const formData = new FormData(form);
    const id = formData.get('type_id');
    const url = id ? adminUrl('/type/edit/' + id) : adminUrl('/type/add');
    
    fetch(url, {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg || '保存成功', 'success');
            closeTypeModal();
            location.reload();
        } else {
            xpkToast(data.msg || '保存失败', 'error');
        }
    })
    .catch(() => xpkToast('请求失败', 'error'));
}

// 点击遮罩关闭
document.getElementById('typeModal').addEventListener('click', function(e) {
    if (e.target === this) closeTypeModal();
});

// 全选/取消全选
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.type-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    updateSelectedCount();
}

// 更新选中数量
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.type-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('batchDeleteBtn').classList.toggle('hidden', count === 0);
    
    // 更新全选框状态
    const allCheckboxes = document.querySelectorAll('.type-checkbox');
    document.getElementById('selectAll').checked = count > 0 && count === allCheckboxes.length;
}

// 批量删除
function batchDelete() {
    const checkboxes = document.querySelectorAll('.type-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);
    
    if (ids.length === 0) {
        xpkToast('请选择要删除的分类', 'warning');
        return;
    }
    
    xpkConfirm('确定删除选中的 ' + ids.length + ' 个分类？\n注意：有子分类或视频的分类无法删除', function() {
        const formData = new FormData();
        ids.forEach(id => formData.append('ids[]', id));
        formData.append('_token', '<?= $csrfToken ?>');
        fetch(adminUrl('/type/batchDelete'), {
            method: 'POST',
            body: formData
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
</script>
