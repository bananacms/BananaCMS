<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">单页管理</h1>
    <div class="flex space-x-2">
        <button onclick="initPages()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            <i class="fas fa-magic mr-1"></i>初始化默认
        </button>
        <a href="/<?= $adminEntry ?>?s=page/add" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-1"></i>添加页面
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">标题</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">标识</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">排序</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">状态</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">底部显示</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">前台链接</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($pages)): ?>
            <tr>
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-file-alt text-4xl text-gray-300 mb-3"></i>
                        <p>暂无单页数据</p>
                        <p class="text-sm mt-1">点击"初始化默认"添加默认页面，或点击"添加页面"创建新页面</p>
                    </div>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($pages as $page): ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?= $page['page_id'] ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-medium text-gray-900"><?= htmlspecialchars($page['page_title']) ?></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <code class="px-2 py-1 bg-gray-100 rounded text-sm"><?= htmlspecialchars($page['page_slug']) ?></code>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?= $page['page_sort'] ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <?php if ($page['page_status']): ?>
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">启用</span>
                    <?php else: ?>
                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">禁用</span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <?php if ($page['page_footer'] ?? 1): ?>
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">显示</span>
                    <?php else: ?>
                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">隐藏</span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <a href="/page/<?= htmlspecialchars($page['page_slug']) ?>" target="_blank" class="text-blue-600 hover:underline text-sm">
                        /page/<?= htmlspecialchars($page['page_slug']) ?>
                    </a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <a href="/<?= $adminEntry ?>?s=page/edit/<?= $page['page_id'] ?>" class="text-blue-600 hover:text-blue-800 mr-3">编辑</a>
                    <button onclick="deletePage(<?= $page['page_id'] ?>, '<?= htmlspecialchars($page['page_title'], ENT_QUOTES) ?>')" class="text-red-600 hover:text-red-800">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
    <h3 class="font-medium text-blue-800 mb-2 flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        提示
    </h3>
    <ul class="text-sm text-blue-700 space-y-1">
        <li>• 单页内容支持 HTML 格式，可以使用富文本编辑</li>
        <li>• 页面链接格式为 /page/{标识}，如 /page/about</li>
        <li>• 开启"底部显示"后，页面会自动出现在前台底部导航</li>
        <li>• 排序数字越小越靠前</li>
    </ul>
</div>

<script>
function deletePage(id, title) {
    xpkConfirm('确定要删除页面"' + title + '"吗？', function() {
        fetch(adminUrl('/page/delete'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id + '&_token=<?= $csrfToken ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast('删除成功', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                xpkToast(data.msg || '删除失败', 'error');
            }
        })
        .catch(() => xpkToast('删除失败', 'error'));
    });
}

function initPages() {
    xpkConfirm('确定要初始化默认页面吗？已存在的页面不会被覆盖。', function() {
        fetch(adminUrl('/page/init'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=<?= $csrfToken ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg || '初始化成功', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                xpkToast(data.msg || '初始化失败', 'error');
            }
        })
        .catch(() => xpkToast('初始化失败', 'error'));
    });
}
</script>
