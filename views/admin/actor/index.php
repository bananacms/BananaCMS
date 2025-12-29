<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">演员管理</h1>
    <a href="/admin.php/actor/add" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
        + 添加演员
    </a>
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
        <a href="/admin.php/actor" class="text-gray-500 hover:text-gray-700 py-2">重置</a>
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
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-400">👤</div>
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
                    <a href="/admin.php/actor/edit/<?= $actor['actor_id'] ?>" class="text-blue-500 hover:underline">编辑</a>
                    <button onclick="deleteItem('/admin.php/actor/delete', <?= $actor['actor_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 分页 -->
<?php if ($totalPages > 1): ?>
<div class="mt-4 flex justify-center">
    <nav class="flex space-x-1">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&keyword=<?= urlencode($keyword) ?>" class="px-3 py-2 border rounded hover:bg-gray-100">上一页</a>
        <?php endif; ?>
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <a href="?page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>" 
            class="px-3 py-2 border rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>&keyword=<?= urlencode($keyword) ?>" class="px-3 py-2 border rounded hover:bg-gray-100">下一页</a>
        <?php endif; ?>
    </nav>
</div>
<?php endif; ?>
