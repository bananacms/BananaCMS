<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">分类管理</h1>
    <a href="/admin.php/type/add" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
        + 添加分类
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
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
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">暂无数据</td>
            </tr>
            <?php else: ?>
            <?php foreach ($types as $type): ?>
            <tr class="hover:bg-gray-50">
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
                    <a href="/admin.php/type/edit/<?= $type['type_id'] ?>" class="text-blue-500 hover:underline">编辑</a>
                    <button onclick="deleteItem('/admin.php/type/delete', <?= $type['type_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
