<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">演员管理</h1>
    <button onclick="openActorModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
        + 添加演员
    </button>
</div>

<!-- 搜索 -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="flex gap-4 items-end">
        <div>
            <label class="block text-sm text-gray-600 mb-1">关键词</label>
            <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" 
                class="border rounded px-3 py-2" placeholder="演员姓名">
        </div>
        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">搜索</button>
        <a href="/<?= $adminEntry ?>/actor" class="text-gray-500 hover:text-gray-700 py-2">重置</a>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">头像</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">姓名</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">性别</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">地区</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">点击</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($list)): ?>
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">暂无数据</td>
            </tr>
            <?php else: ?>
            <?php foreach ($list as $actor): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?= $actor['actor_id'] ?></td>
                <td class="px-4 py-3">
                    <?php if ($actor['actor_pic']): ?>
                    <img src="<?= htmlspecialchars($actor['actor_pic']) ?>" class="w-10 h-10 rounded-full object-cover">
                    <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm font-medium"><?= htmlspecialchars($actor['actor_name']) ?></td>
                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($actor['actor_sex'] ?: '-') ?></td>
                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($actor['actor_area'] ?: '-') ?></td>
                <td class="px-4 py-3 text-sm"><?= number_format($actor['actor_hits']) ?></td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded text-xs <?= $actor['actor_status'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                        <?= $actor['actor_status'] ? '启用' : '禁用' ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <button onclick="openActorModal(<?= $actor['actor_id'] ?>)" class="text-blue-500 hover:underline">编辑</button>
                    <button onclick="deleteItem('/<?= $adminEntry ?>/actor/delete', <?= $actor['actor_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 分页 -->
<?php 
$baseUrl = "/<?= $adminEntry ?>/actor?keyword=" . urlencode($keyword);
include __DIR__ . '/../components/pagination.php'; 
?>

<!-- 演员模态框 -->
<div id="actorModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-6 py-4 border-b sticky top-0 bg-white">
            <h3 id="actorModalTitle" class="text-lg font-bold">添加演员</h3>
            <button onclick="closeActorModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="actorForm" onsubmit="saveActor(event)" class="p-6">
            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="actor_id" id="actorId" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">姓名 *</label>
                    <input type="text" name="actor_name" id="actorName" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">英文名</label>
                    <input type="text" name="actor_en" id="actorEn" class="w-full border rounded px-3 py-2">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">头像</label>
                    <input type="text" name="actor_pic" id="actorPic" class="w-full border rounded px-3 py-2" placeholder="图片URL">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">性别</label>
                    <select name="actor_sex" id="actorSex" class="w-full border rounded px-3 py-2">
                        <option value="">未知</option>
                        <option value="男">男</option>
                        <option value="女">女</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">地区</label>
                    <input type="text" name="actor_area" id="actorArea" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">血型</label>
                    <select name="actor_blood" id="actorBlood" class="w-full border rounded px-3 py-2">
                        <option value="">未知</option>
                        <option value="A">A型</option>
                        <option value="B">B型</option>
                        <option value="AB">AB型</option>
                        <option value="O">O型</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">生日</label>
                    <input type="text" name="actor_birthday" id="actorBirthday" class="w-full border rounded px-3 py-2" placeholder="1990-01-01">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">身高</label>
                    <input type="text" name="actor_height" id="actorHeight" class="w-full border rounded px-3 py-2" placeholder="170cm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">体重</label>
                    <input type="text" name="actor_weight" id="actorWeight" class="w-full border rounded px-3 py-2" placeholder="60kg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                    <select name="actor_status" id="actorStatus" class="w-full border rounded px-3 py-2">
                        <option value="1">启用</option>
                        <option value="0">禁用</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">简介</label>
                    <textarea name="actor_content" id="actorContent" rows="3" class="w-full border rounded px-3 py-2"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeActorModal()" class="px-4 py-2 border rounded hover:bg-gray-50">取消</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
function openActorModal(id = null) {
    const modal = document.getElementById('actorModal');
    const title = document.getElementById('actorModalTitle');
    
    document.getElementById('actorForm').reset();
    document.getElementById('actorId').value = '';
    
    if (id) {
        title.textContent = '编辑演员';
        fetch(adminUrl('/actor/getOne?id=' + id))
            .then(r => r.json())
            .then(data => {
                if (data.code === 0) {
                    const a = data.data;
                    document.getElementById('actorId').value = a.actor_id;
                    document.getElementById('actorName').value = a.actor_name || '';
                    document.getElementById('actorEn').value = a.actor_en || '';
                    document.getElementById('actorPic').value = a.actor_pic || '';
                    document.getElementById('actorSex').value = a.actor_sex || '';
                    document.getElementById('actorArea').value = a.actor_area || '';
                    document.getElementById('actorBlood').value = a.actor_blood || '';
                    document.getElementById('actorBirthday').value = a.actor_birthday || '';
                    document.getElementById('actorHeight').value = a.actor_height || '';
                    document.getElementById('actorWeight').value = a.actor_weight || '';
                    document.getElementById('actorStatus').value = a.actor_status;
                    document.getElementById('actorContent').value = a.actor_content || '';
                }
            });
    } else {
        title.textContent = '添加演员';
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeActorModal() {
    const modal = document.getElementById('actorModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function saveActor(e) {
    e.preventDefault();
    const form = document.getElementById('actorForm');
    const formData = new FormData(form);
    const id = formData.get('actor_id');
    const url = id ? adminUrl('/actor/edit/' + id) : adminUrl('/actor/add');
    
    fetch(url, {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg || '保存成功', 'success');
            closeActorModal();
            location.reload();
        } else {
            xpkToast(data.msg || '保存失败', 'error');
        }
    })
    .catch(() => xpkToast('请求失败', 'error'));
}

document.getElementById('actorModal').addEventListener('click', function(e) {
    if (e.target === this) closeActorModal();
});
</script>
