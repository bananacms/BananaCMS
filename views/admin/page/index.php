<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">单页管理</h1>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">页面</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">状态</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">前台链接</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($pageList as $page): ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-medium text-gray-900"><?= htmlspecialchars($page['title']) ?></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <?php if ($page['has_content']): ?>
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">已配置</span>
                    <?php else: ?>
                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">未配置</span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <a href="/<?= $page['key'] ?>" target="_blank" class="text-blue-600 hover:underline text-sm">/<?= $page['key'] ?></a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                    <a href="/admin.php/page/edit/<?= $page['key'] ?>" class="text-blue-600 hover:text-blue-800">编辑</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
    <h3 class="font-medium text-blue-800 mb-2">💡 提示</h3>
    <ul class="text-sm text-blue-700 space-y-1">
        <li>• 单页内容支持 HTML 格式，可以使用富文本编辑</li>
        <li>• 页面链接固定为 /about、/contact、/disclaimer</li>
        <li>• 底部导航已自动链接到这些页面</li>
    </ul>
</div>
