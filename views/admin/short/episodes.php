<div class="mb-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="/<?= $adminEntry ?>/short" class="text-gray-500 hover:text-gray-700">← 返回列表</a>
        <h2 class="text-2xl font-bold flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            剧集管理
        </h2>
    </div>

    <!-- 短剧信息 -->
    <div class="bg-white p-4 rounded shadow mb-4 flex gap-4">
        <?php if ($short['short_pic']): ?>
        <img src="<?= htmlspecialchars($short['short_pic']) ?>" class="w-20 h-28 object-cover rounded">
        <?php endif; ?>
        <div>
            <h3 class="text-lg font-bold"><?= htmlspecialchars($short['short_name']) ?></h3>
            <p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars($short['short_desc'] ?: '暂无简介') ?></p>
            <p class="text-gray-400 text-xs mt-2">共 <?= count($episodes) ?> 集</p>
        </div>
    </div>

    <div class="flex justify-between items-center mb-4">
        <span class="text-gray-500">剧集列表</span>
        <a href="/<?= $adminEntry ?>/short/addEpisode/<?= $short['short_id'] ?>" class="bg-primary text-white px-4 py-2 rounded hover:bg-red-600">
            + 添加剧集
        </a>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="mb-4 p-4 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>
</div>

<!-- 剧集列表 -->
<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">排序</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">集数</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">标题</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">时长</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">播放量</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">免费</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($episodes)): ?>
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">暂无剧集，点击上方按钮添加</td>
            </tr>
            <?php else: ?>
            <?php foreach ($episodes as $index => $ep): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-500"><?= $ep['episode_sort'] ?: ($index + 1) ?></td>
                <td class="px-4 py-3">
                    <span class="bg-gray-100 px-2 py-1 rounded text-sm">第 <?= $index + 1 ?> 集</span>
                </td>
                <td class="px-4 py-3">
                    <div class="font-medium"><?= htmlspecialchars($ep['episode_name'] ?: '第' . ($index + 1) . '集') ?></div>
                    <div class="text-xs text-gray-400 truncate max-w-xs"><?= htmlspecialchars($ep['episode_url']) ?></div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">
                    <?= $ep['episode_duration'] ? gmdate('i:s', $ep['episode_duration']) : '-' ?>
                </td>
                <td class="px-4 py-3 text-sm"><?= number_format($ep['episode_hits']) ?></td>
                <td class="px-4 py-3">
                    <?php if ($ep['episode_free']): ?>
                    <span class="text-green-600 text-sm">免费</span>
                    <?php else: ?>
                    <span class="text-yellow-600 text-sm">付费</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <a href="/<?= $adminEntry ?>/short/editEpisode/<?= $ep['episode_id'] ?>" class="text-blue-600 hover:underline">编辑</a>
                    <button onclick="deleteItem(adminUrl('/short/deleteEpisode'), <?= $ep['episode_id'] ?>)" class="text-red-600 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4">
    <a href="/<?= $adminEntry ?>/short/edit/<?= $short['short_id'] ?>" class="text-gray-500 hover:text-gray-700">
        ← 编辑短剧信息
    </a>
</div>
