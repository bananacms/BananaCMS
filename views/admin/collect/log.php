<h1 class="text-2xl font-bold mb-6">采集日志</h1>

<!-- 统计卡片 -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-sm text-gray-500">近7天采集次数</p>
        <p class="text-2xl font-bold text-blue-600"><?= number_format($stats['total']['count']) ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-sm text-gray-500">新增视频</p>
        <p class="text-2xl font-bold text-green-600"><?= number_format($stats['total']['added']) ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-sm text-gray-500">更新视频</p>
        <p class="text-2xl font-bold text-orange-600"><?= number_format($stats['total']['updated']) ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-sm text-gray-500">跳过视频</p>
        <p class="text-2xl font-bold text-gray-600"><?= number_format($stats['total']['skipped']) ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- 按天统计 -->
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-bold mb-3">每日采集统计</h3>
        <?php if (empty($stats['daily'])): ?>
        <p class="text-gray-500 text-sm">暂无数据</p>
        <?php else: ?>
        <div class="space-y-2 max-h-48 overflow-y-auto">
            <?php foreach ($stats['daily'] as $day): ?>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600"><?= $day['date'] ?></span>
                <span>
                    <span class="text-green-600">+<?= $day['added'] ?></span>
                    <span class="text-orange-600 ml-2">↻<?= $day['updated'] ?></span>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- 按采集站统计 -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow p-4">
        <h3 class="font-bold mb-3">采集站统计（近7天）</h3>
        <?php if (empty($stats['byCollect'])): ?>
        <p class="text-gray-500 text-sm">暂无数据</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="pb-2">采集站</th>
                        <th class="pb-2">次数</th>
                        <th class="pb-2">新增</th>
                        <th class="pb-2">更新</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['byCollect'] as $row): ?>
                    <tr>
                        <td class="py-1"><?= htmlspecialchars($row['collect_name']) ?></td>
                        <td class="py-1"><?= $row['count'] ?></td>
                        <td class="py-1 text-green-600"><?= $row['added'] ?></td>
                        <td class="py-1 text-orange-600"><?= $row['updated'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- 筛选和操作 -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex items-center gap-4">
            <select name="collect_id" class="border rounded px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">全部采集站</option>
                <?php foreach ($collects as $c): ?>
                <option value="<?= $c['collect_id'] ?>" <?= $collectId == $c['collect_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['collect_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
        <button onclick="cleanLogs()" class="text-red-500 hover:text-red-700 text-sm flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            清理30天前的日志
        </button>
    </div>
</div>

<!-- 日志列表 -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">时间</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">采集站</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">类型</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">模式</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">页数</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">新增</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">更新</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">跳过</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">耗时</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($logs['list'])): ?>
            <tr>
                <td colspan="10" class="px-4 py-8 text-center text-gray-500">暂无采集日志</td>
            </tr>
            <?php else: ?>
            <?php foreach ($logs['list'] as $log): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-500"><?= date('m-d H:i', $log['log_time']) ?></td>
                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($log['collect_name']) ?></td>
                <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-0.5 rounded text-xs <?= $log['log_type'] == 'cron' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                        <?= $log['log_type'] == 'cron' ? '定时' : '手动' ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">
                    <?= $log['log_mode'] == 'add' ? '新增' : ($log['log_mode'] == 'update' ? '更新' : '全部') ?>
                </td>
                <td class="px-4 py-3 text-sm"><?= $log['log_pages'] ?></td>
                <td class="px-4 py-3 text-sm text-green-600"><?= $log['log_added'] ?></td>
                <td class="px-4 py-3 text-sm text-orange-600"><?= $log['log_updated'] ?></td>
                <td class="px-4 py-3 text-sm text-gray-500"><?= $log['log_skipped'] ?></td>
                <td class="px-4 py-3 text-sm text-gray-500"><?= $log['log_duration'] ?>秒</td>
                <td class="px-4 py-3 text-sm">
                    <?php if ($log['log_status'] == 1): ?>
                    <span class="text-green-600 flex items-center" title="<?= htmlspecialchars($log['log_message']) ?>">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        成功
                    </span>
                    <?php elseif ($log['log_status'] == 2): ?>
                    <span class="text-blue-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        进行中
                    </span>
                    <?php else: ?>
                    <span class="text-red-600 flex items-center" title="<?= htmlspecialchars($log['log_message']) ?>">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        失败
                    </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 分页 -->
<?php if ($logs['totalPages'] > 1): ?>
<?php 
$baseUrl = "/{$adminEntry}?s=collect/log" . ($collectId ? "&collect_id={$collectId}" : "");
$page = $logs['page'];
$totalPages = $logs['totalPages'];
include __DIR__ . '/../components/pagination.php'; 
?>
<?php endif; ?>

<script>
function cleanLogs() {
    xpkConfirm('确定要清理30天前的采集日志吗？', function() {
        fetch(adminUrl('/collect/cleanLog'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=<?= $csrfToken ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                xpkToast(data.msg, 'error');
            }
        });
    });
}
</script>
