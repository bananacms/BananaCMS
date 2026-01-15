<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<h1 class="text-2xl font-bold mb-6">仪表盘</h1>

<!-- 统计卡片 -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-full">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">视频总数</p>
                <p class="text-2xl font-bold"><?= number_format($stats['vod_count']) ?></p>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">今日新增: <?= $stats['vod_today'] ?></p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-full">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">分类数量</p>
                <p class="text-2xl font-bold"><?= number_format($stats['type_count']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-full">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">演员数量</p>
                <p class="text-2xl font-bold"><?= number_format($stats['actor_count']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-full">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">用户数量</p>
                <p class="text-2xl font-bold"><?= number_format($stats['user_count']) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- 最新视频 -->
    <div class="bg-white rounded-lg shadow h-fit">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h2 class="font-bold">最新视频</h2>
            <a href="/<?= $adminEntry ?>?s=vod" class="text-sm text-blue-500 hover:underline">查看全部</a>
        </div>
        <div class="p-6">
            <?php if (empty($latestVods)): ?>
            <p class="text-gray-500 text-center py-4">暂无数据</p>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($latestVods as $vod): ?>
                <div class="flex items-center py-2 border-b last:border-0">
                    <div class="flex-1 min-w-0">
                        <a href="/<?= $adminEntry ?>?s=vod/edit/<?= $vod['vod_id'] ?>" class="text-sm font-medium truncate hover:text-blue-600 hover:underline block">
                            <?= htmlspecialchars($vod['vod_name']) ?>
                        </a>
                        <p class="text-xs text-gray-400"><?= htmlspecialchars($vod['type_name'] ?? '未分类') ?></p>
                    </div>
                    <span class="text-xs text-gray-400 ml-2 whitespace-nowrap"><?= date('m-d H:i', $vod['vod_time_add']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 热门视频 -->
    <div class="bg-white rounded-lg shadow h-fit">
        <div class="px-6 py-4 border-b">
            <h2 class="font-bold">热门视频 TOP10</h2>
        </div>
        <div class="p-6">
            <?php if (empty($hotVods)): ?>
            <p class="text-gray-500 text-center py-4">暂无数据</p>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($hotVods as $i => $vod): ?>
                <div class="flex items-center py-2 border-b last:border-0">
                    <span class="w-6 h-6 rounded-full <?= $i < 3 ? 'bg-red-500' : 'bg-gray-300' ?> text-white text-xs flex items-center justify-center flex-shrink-0">
                        <?= $i + 1 ?>
                    </span>
                    <div class="flex-1 ml-3 min-w-0">
                        <a href="/<?= $adminEntry ?>?s=vod/edit/<?= $vod['vod_id'] ?>" class="text-sm truncate hover:text-blue-600 hover:underline block">
                            <?= htmlspecialchars($vod['vod_name']) ?>
                        </a>
                        <p class="text-xs text-gray-400">点击量: <?= number_format($vod['vod_hits']) ?> 次</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 系统信息 -->
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h2 class="font-bold mb-4">系统信息</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div>
            <span class="text-gray-500">PHP版本:</span>
            <span class="ml-2"><?= PHP_VERSION ?></span>
        </div>
        <div>
            <span class="text-gray-500">服务器:</span>
            <span class="ml-2"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></span>
        </div>
        <div>
            <span class="text-gray-500">上传限制:</span>
            <span class="ml-2"><?= ini_get('upload_max_filesize') ?></span>
        </div>
        <div>
            <span class="text-gray-500">当前时间:</span>
            <span class="ml-2"><?= date('Y-m-d H:i:s') ?></span>
        </div>
    </div>
</div>
