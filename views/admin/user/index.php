<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">用户管理</h1>
</div>

<!-- 搜索 -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm text-gray-600 mb-1">关键词</label>
            <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" 
                class="border rounded px-3 py-2" placeholder="用户名/邮箱">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">状态</label>
            <select name="status" class="border rounded px-3 py-2">
                <option value="">全部</option>
                <option value="1" <?= $status === '1' ? 'selected' : '' ?>>正常</option>
                <option value="0" <?= $status === '0' ? 'selected' : '' ?>>禁用</option>
            </select>
        </div>
        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">搜索</button>
        <a href="/<?= $adminEntry ?>/user" class="text-gray-500 hover:text-gray-700 py-2">重置</a>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">用户名</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">昵称</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">邮箱</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">注册时间</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($list)): ?>
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">暂无数据</td>
            </tr>
            <?php else: ?>
            <?php foreach ($list as $user): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?= $user['user_id'] ?></td>
                <td class="px-4 py-3 text-sm font-medium"><?= htmlspecialchars($user['user_name']) ?></td>
                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($user['user_nick_name'] ?: '-') ?></td>
                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($user['user_email'] ?: '-') ?></td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded text-xs <?= $user['user_status'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $user['user_status'] ? '正常' : '禁用' ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500"><?= date('Y-m-d H:i', $user['user_reg_time']) ?></td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <button onclick="openEditModal(<?= htmlspecialchars(json_encode($user)) ?>)" class="text-blue-500 hover:underline">编辑</button>
                    <button onclick="deleteItem(adminUrl('/user/delete'), <?= $user['user_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 分页 -->
<?php 
$baseUrl = "/<?= $adminEntry ?>/user?keyword=" . urlencode($keyword) . "&status={$status}";
include __DIR__ . '/../components/pagination.php'; 
?>


<!-- 编辑模态框 -->
<div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="text-lg font-bold">编辑用户</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form id="editForm" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">用户名</label>
                <input type="text" id="edit_user_name" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">昵称</label>
                <input type="text" name="user_nick_name" id="edit_user_nick_name" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">邮箱</label>
                <input type="email" name="user_email" id="edit_user_email" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">新密码 <span class="text-gray-400 font-normal">(留空不修改)</span></label>
                <input type="password" name="user_pwd" id="edit_user_pwd" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="留空不修改">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                <select name="user_status" id="edit_user_status" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="1">正常</option>
                    <option value="0">禁用</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border rounded hover:bg-gray-50">取消</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(user) {
    document.getElementById('edit_user_id').value = user.user_id;
    document.getElementById('edit_user_name').value = user.user_name;
    document.getElementById('edit_user_nick_name').value = user.user_nick_name || '';
    document.getElementById('edit_user_email').value = user.user_email || '';
    document.getElementById('edit_user_pwd').value = '';
    document.getElementById('edit_user_status').value = user.user_status;
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
}

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var userId = document.getElementById('edit_user_id').value;
    var formData = new FormData(this);
    
    fetch(adminUrl('/user/edit/' + userId), {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            location.reload();
        } else {
            alert(data.msg || '保存失败');
        }
    })
    .catch(() => alert('请求失败'));
});

// 点击遮罩关闭
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>
