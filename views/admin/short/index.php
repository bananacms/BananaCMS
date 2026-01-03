<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            短视频管理
        </h2>
        <div class="flex gap-2">
            <a href="/<?= $adminEntry ?>/short/add?type=video" class="bg-primary text-white px-4 py-2 rounded hover:bg-red-600">
                + 添加短视频
            </a>
            <a href="/<?= $adminEntry ?>/short/add?type=drama" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                + 添加短剧
            </a>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">总数</div>
            <div class="text-2xl font-bold"><?= $stats['total'] ?></div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">短视频</div>
            <div class="text-2xl font-bold text-blue-600"><?= $stats['videos'] ?></div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">短剧</div>
            <div class="text-2xl font-bold text-purple-600"><?= $stats['dramas'] ?></div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">剧集数</div>
            <div class="text-2xl font-bold text-green-600"><?= $stats['episodes'] ?></div>
        </div>
    </div>

    <!-- 筛选 -->
    <div class="bg-white p-4 rounded shadow mb-4">
        <form method="get" class="flex gap-4 items-center">
            <select name="type" class="border rounded px-3 py-2">
                <option value="">全部类型</option>
                <option value="video" <?= $type === 'video' ? 'selected' : '' ?>>短视频</option>
                <option value="drama" <?= $type === 'drama' ? 'selected' : '' ?>>短剧</option>
            </select>
            <select name="status" class="border rounded px-3 py-2">
                <option value="">全部状态</option>
                <option value="1" <?= $status === '1' ? 'selected' : '' ?>>已上架</option>
                <option value="0" <?= $status === '0' ? 'selected' : '' ?>>已下架</option>
            </select>
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">筛选</button>
            <a href="/<?= $adminEntry ?>/short" class="text-gray-500 hover:text-gray-700">重置</a>
        </form>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="mb-4 p-4 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>
</div>

<!-- 列表 -->
<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">封面</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">标题</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">类型</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">集数</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">播放/点赞</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">时间</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($list)): ?>
            <tr>
                <td colspan="9" class="px-4 py-8 text-center text-gray-500">暂无数据</td>
            </tr>
            <?php else: ?>
            <?php foreach ($list as $item): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?= $item['short_id'] ?></td>
                <td class="px-4 py-3">
                    <?php if ($item['short_pic']): ?>
                    <img src="<?= htmlspecialchars($item['short_pic']) ?>" class="w-12 h-16 object-cover rounded">
                    <?php else: ?>
                    <div class="w-12 h-16 bg-gray-200 rounded flex items-center justify-center text-gray-400">无</div>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3">
                    <div class="font-medium"><?= htmlspecialchars($item['short_name']) ?></div>
                    <?php if ($item['short_tags']): ?>
                    <div class="text-xs text-gray-400 mt-1"><?= htmlspecialchars($item['short_tags']) ?></div>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm">
                    <?php if ($item['short_type'] === 'drama'): ?>
                    <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs">短剧</span>
                    <?php else: ?>
                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">短视频</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm">
                    <?php if ($item['short_type'] === 'drama'): ?>
                    <a href="/<?= $adminEntry ?>/short/episodes/<?= $item['short_id'] ?>" class="text-blue-600 hover:underline">
                        <?= $item['episode_count'] ?? 0 ?> 集
                    </a>
                    <?php else: ?>
                    <span class="text-gray-400">-</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm">
                    <div class="flex items-center text-gray-600">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"></path>
                        </svg>
                        <?= number_format($item['short_hits']) ?>
                    </div>
                    <div class="flex items-center text-red-500 mt-1">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                        </svg>
                        <?= number_format($item['short_likes']) ?>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <?php if ($item['short_status']): ?>
                    <span class="text-green-600 text-sm">● 上架</span>
                    <?php else: ?>
                    <span class="text-gray-400 text-sm">○ 下架</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">
                    <?= date('m-d H:i', $item['short_time']) ?>
                </td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <a href="/<?= $adminEntry ?>/short/edit/<?= $item['short_id'] ?>" class="text-blue-600 hover:underline">编辑</a>
                    <?php if ($item['short_type'] === 'drama'): ?>
                    <a href="/<?= $adminEntry ?>/short/episodes/<?= $item['short_id'] ?>" class="text-purple-600 hover:underline">剧集</a>
                    <?php endif; ?>
                    <button onclick="toggleStatus(<?= $item['short_id'] ?>)" class="text-yellow-600 hover:underline">
                        <?= $item['short_status'] ? '下架' : '上架' ?>
                    </button>
                    <button onclick="deleteItem(adminUrl('/short/delete'), <?= $item['short_id'] ?>)" class="text-red-600 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 分页 -->
<?php 
$baseUrl = "/{$adminEntry}/short?type=" . urlencode($type) . "&status=" . urlencode($status);
include __DIR__ . '/../components/pagination.php'; 
?>

<script>
function toggleStatus(id) {
    fetch(adminUrl('/short/toggle'), {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id + '&_token=' + window.CSRF_TOKEN
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg, 'success');
            location.reload();
        } else {
            xpkToast(data.msg, 'error');
        }
    });
}
</script>
