<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">文章管理</h1>
    <div class="space-x-2">
        <a href="/<?= $adminEntry ?>/art_type" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            分类管理
        </a>
        <a href="/<?= $adminEntry ?>/art/add" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            + 添加文章
        </a>
    </div>
</div>

<!-- 搜索 -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="flex gap-4 items-end flex-wrap">
        <div>
            <label class="block text-sm text-gray-600 mb-1">分类</label>
            <select name="type_id" class="border rounded px-3 py-2">
                <option value="">全部分类</option>
                <?php foreach ($types as $type): ?>
                <option value="<?= $type['type_id'] ?>" <?= $typeId == $type['type_id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['type_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">关键词</label>
            <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" 
                class="border rounded px-3 py-2" placeholder="文章标题">
        </div>
        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">搜索</button>
        <a href="/<?= $adminEntry ?>/art" class="text-gray-500 hover:text-gray-700 py-2">重置</a>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">标题</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">分类</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">作者</th>
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
            <?php foreach ($list as $art): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?= $art['art_id'] ?></td>
                <td class="px-4 py-3 text-sm font-medium"><?= htmlspecialchars($art['art_name']) ?></td>
                <td class="px-4 py-3 text-sm text-gray-500"><?= htmlspecialchars($art['type_name'] ?? '-') ?></td>
                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($art['art_author'] ?: '-') ?></td>
                <td class="px-4 py-3 text-sm"><?= number_format($art['art_hits']) ?></td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded text-xs <?= $art['art_status'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                        <?= $art['art_status'] ? '已发布' : '未发布' ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500"><?= date('Y-m-d H:i', $art['art_time']) ?></td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <a href="/<?= $adminEntry ?>/art/edit/<?= $art['art_id'] ?>" class="text-blue-500 hover:underline">编辑</a>
                    <button onclick="deleteItem('/<?= $adminEntry ?>/art/delete', <?= $art['art_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 分页 -->
<?php 
$baseUrl = "/{$adminEntry}/art?type_id={$typeId}&keyword=" . urlencode($keyword);
include __DIR__ . '/../components/pagination.php'; 
?>
