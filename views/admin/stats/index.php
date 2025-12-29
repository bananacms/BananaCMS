<div class="space-y-6">
    <!-- 页面标题 -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">📊 数据统计</h2>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-500">实时在线: <span class="text-green-500 font-bold"><?= $onlineCount ?></span> 人</span>
            <select id="daysSelect" onchange="changeDays(this.value)" class="border rounded px-3 py-1.5 text-sm">
                <option value="7" <?= $days == 7 ? 'selected' : '' ?>>最近7天</option>
                <option value="14" <?= $days == 14 ? 'selected' : '' ?>>最近14天</option>
                <option value="30" <?= $days == 30 ? 'selected' : '' ?>>最近30天</option>
            </select>
        </div>
    </div>

    <!-- 今日概览 -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">今日UV</p>
                    <p class="text-2xl font-bold text-blue-600"><?= number_format($overview['uv']) ?></p>
                </div>
                <div class="text-3xl">👥</div>
            </div>
            <div class="mt-2 text-xs <?= $overview['uv'] >= $overview['uv_yesterday'] ? 'text-green-500' : 'text-red-500' ?>">
                <?php $uvDiff = $overview['uv'] - $overview['uv_yesterday']; ?>
                <?= $uvDiff >= 0 ? '↑' : '↓' ?> <?= abs($uvDiff) ?> 较昨日
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">今日PV</p>
                    <p class="text-2xl font-bold text-green-600"><?= number_format($overview['pv']) ?></p>
                </div>
                <div class="text-3xl">👁️</div>
            </div>
            <div class="mt-2 text-xs <?= $overview['pv'] >= $overview['pv_yesterday'] ? 'text-green-500' : 'text-red-500' ?>">
                <?php $pvDiff = $overview['pv'] - $overview['pv_yesterday']; ?>
                <?= $pvDiff >= 0 ? '↑' : '↓' ?> <?= abs($pvDiff) ?> 较昨日
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">今日新用户</p>
                    <p class="text-2xl font-bold text-purple-600"><?= number_format($overview['new_users']) ?></p>
                </div>
                <div class="text-3xl">🆕</div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">今日播放</p>
                    <p class="text-2xl font-bold text-red-600"><?= number_format($overview['plays']) ?></p>
                </div>
                <div class="text-3xl">▶️</div>
            </div>
        </div>
    </div>

    <!-- 内容统计 -->
    <div class="grid grid-cols-3 md:grid-cols-6 gap-4">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow p-4 text-white">
            <p class="text-sm opacity-80">视频总数</p>
            <p class="text-xl font-bold"><?= number_format($contentStats['vods']) ?></p>
        </div>
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow p-4 text-white">
            <p class="text-sm opacity-80">演员总数</p>
            <p class="text-xl font-bold"><?= number_format($contentStats['actors']) ?></p>
        </div>
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow p-4 text-white">
            <p class="text-sm opacity-80">文章总数</p>
            <p class="text-xl font-bold"><?= number_format($contentStats['arts']) ?></p>
        </div>
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow p-4 text-white">
            <p class="text-sm opacity-80">用户总数</p>
            <p class="text-xl font-bold"><?= number_format($contentStats['users']) ?></p>
        </div>
        <div class="bg-gradient-to-r from-pink-500 to-pink-600 rounded-lg shadow p-4 text-white">
            <p class="text-sm opacity-80">评论总数</p>
            <p class="text-xl font-bold"><?= number_format($contentStats['comments']) ?></p>
        </div>
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow p-4 text-white">
            <p class="text-sm opacity-80">短视频</p>
            <p class="text-xl font-bold"><?= number_format($contentStats['shorts']) ?></p>
        </div>
    </div>

    <!-- 图表区域 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 访问趋势 -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-4">📈 访问趋势</h3>
            <canvas id="trendChart" height="200"></canvas>
        </div>
        <!-- 用户增长 -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-4">👤 用户增长</h3>
            <canvas id="userChart" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 来源统计 -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-4">🔗 流量来源</h3>
            <canvas id="refererChart" height="200"></canvas>
        </div>
        <!-- 设备统计 -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-4">📱 设备分布</h3>
            <canvas id="deviceChart" height="200"></canvas>
        </div>
        <!-- 热门视频 -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-4">🔥 热门视频</h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <?php foreach ($hotVideos as $i => $video): ?>
                <div class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded">
                    <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold <?= $i < 3 ? 'bg-red-500 text-white' : 'bg-gray-200' ?>">
                        <?= $i + 1 ?>
                    </span>
                    <div class="flex-1 truncate text-sm"><?= htmlspecialchars($video['vod_name']) ?></div>
                    <span class="text-xs text-gray-500"><?= number_format($video['period_pv'] ?? 0) ?> 次</span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($hotVideos)): ?>
                <p class="text-gray-400 text-center py-4">暂无数据</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 日志清理 -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold">🗑️ 日志清理</h3>
                <p class="text-sm text-gray-500">定期清理过期统计日志，释放数据库空间</p>
            </div>
            <div class="flex items-center gap-2">
                <select id="cleanDays" class="border rounded px-3 py-1.5 text-sm">
                    <option value="30">保留30天</option>
                    <option value="60">保留60天</option>
                    <option value="90" selected>保留90天</option>
                    <option value="180">保留180天</option>
                </select>
                <button onclick="cleanLogs()" class="bg-red-500 text-white px-4 py-1.5 rounded hover:bg-red-600 text-sm">
                    清理日志
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// 数据
const trendData = <?= json_encode($trend) ?>;
const userTrendData = <?= json_encode($userTrend) ?>;
const refererData = <?= json_encode($refererStats) ?>;
const deviceData = <?= json_encode($deviceStats) ?>;

// 访问趋势图
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: trendData.dates,
        datasets: [
            {
                label: 'UV',
                data: trendData.uv,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.3
            },
            {
                label: 'PV',
                data: trendData.pv,
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true } }
    }
});

// 用户增长图
new Chart(document.getElementById('userChart'), {
    type: 'bar',
    data: {
        labels: userTrendData.dates,
        datasets: [{
            label: '新增用户',
            data: userTrendData.counts,
            backgroundColor: '#8B5CF6',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// 来源统计图
new Chart(document.getElementById('refererChart'), {
    type: 'doughnut',
    data: {
        labels: refererData.map(r => r.source),
        datasets: [{
            data: refererData.map(r => r.cnt),
            backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#6B7280']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// 设备统计图
new Chart(document.getElementById('deviceChart'), {
    type: 'pie',
    data: {
        labels: deviceData.map(d => d.name),
        datasets: [{
            data: deviceData.map(d => d.value),
            backgroundColor: ['#3B82F6', '#10B981', '#F59E0B']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// 切换天数
function changeDays(days) {
    location.href = '/admin.php/stats?days=' + days;
}

// 清理日志
function cleanLogs() {
    const days = document.getElementById('cleanDays').value;
    xpkConfirm('确定要清理 ' + days + ' 天前的日志吗？', function() {
        fetch('/admin.php/stats/clean', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'days=' + days
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
            } else {
                xpkToast(data.msg, 'error');
            }
        });
    });
}
</script>
