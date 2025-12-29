<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">采集管理</h1>
    <a href="/admin.php/collect/add" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
        + 添加采集站
    </a>
</div>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
    <p class="text-yellow-800 text-sm">
        <strong>使用说明：</strong>
        1. 添加资源站API地址 → 2. 绑定分类 → 3. 执行采集
    </p>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">名称</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">API地址</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($collects)): ?>
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    暂无采集站，<a href="/admin.php/collect/add" class="text-blue-500 hover:underline">点击添加</a>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($collects as $c): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?= $c['collect_id'] ?></td>
                <td class="px-4 py-3 text-sm font-medium"><?= htmlspecialchars($c['collect_name']) ?></td>
                <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate"><?= htmlspecialchars($c['collect_api']) ?></td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded text-xs <?= $c['collect_status'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                        <?= $c['collect_status'] ? '启用' : '禁用' ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <a href="/admin.php/collect/bind/<?= $c['collect_id'] ?>" class="text-purple-500 hover:underline">绑定</a>
                    <a href="/admin.php/collect/run/<?= $c['collect_id'] ?>" class="text-green-500 hover:underline">采集</a>
                    <a href="/admin.php/collect/edit/<?= $c['collect_id'] ?>" class="text-blue-500 hover:underline">编辑</a>
                    <button onclick="deleteItem('/admin.php/collect/delete', <?= $c['collect_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h3 class="font-bold mb-4">常用资源站</h3>
    <div class="space-y-2 text-sm">
        <p class="text-gray-600">以下是一些常用的资源站API（仅供参考，请自行验证）：</p>
        <ul class="list-disc list-inside text-gray-500 space-y-1">
            <li>淘片资源：https://taopianapi.com/home/cjapi/as/mc10/vod/xml</li>
            <li>红牛资源：https://www.hongniuzy2.com/api.php/provide/vod/</li>
            <li>光速资源：https://api.guangsuapi.com/api.php/provide/vod/</li>
            <li>量子资源：https://cj.lziapi.com/api.php/provide/vod/</li>
        </ul>
    </div>
</div>
