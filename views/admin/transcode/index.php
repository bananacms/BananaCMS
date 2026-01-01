<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['message'] ?? $flash['msg'] ?? '') ?>
</div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">云转码</h1>
    <div class="flex items-center gap-3">
        <?php if ($ffmpegAvailable): ?>
            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">FFmpeg <?= htmlspecialchars($ffmpegVersion) ?></span>
        <?php else: ?>
            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">FFmpeg 未安装</span>
        <?php endif; ?>
        <a href="/admin.php/transcode/upload" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded <?= $ffmpegAvailable ? '' : 'opacity-50 pointer-events-none' ?>">
            + 上传视频
        </a>
    </div>
</div>

<!-- 统计卡片 -->
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-blue-500 text-white rounded-lg p-4">
        <div class="text-sm opacity-75">总任务</div>
        <div class="text-3xl font-bold"><?= $stats['total'] ?></div>
    </div>
    <div class="bg-yellow-500 text-white rounded-lg p-4">
        <div class="text-sm opacity-75">待处理</div>
        <div class="text-3xl font-bold"><?= $stats['pending'] ?></div>
    </div>
    <div class="bg-green-500 text-white rounded-lg p-4">
        <div class="text-sm opacity-75">已完成</div>
        <div class="text-3xl font-bold"><?= $stats['completed'] ?></div>
    </div>
    <div class="bg-red-500 text-white rounded-lg p-4">
        <div class="text-sm opacity-75">失败</div>
        <div class="text-3xl font-bold"><?= $stats['failed'] ?></div>
    </div>
</div>

<!-- 筛选 -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form class="flex items-center gap-4" method="get">
        <select name="status" class="border rounded px-3 py-2">
            <option value="">全部状态</option>
            <option value="0" <?= $status === '0' ? 'selected' : '' ?>>待处理</option>
            <option value="1" <?= $status === '1' ? 'selected' : '' ?>>处理中</option>
            <option value="2" <?= $status === '2' ? 'selected' : '' ?>>已完成</option>
            <option value="3" <?= $status === '3' ? 'selected' : '' ?>>失败</option>
        </select>
        <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">筛选</button>
        <a href="/admin.php/transcode" class="text-gray-500 hover:text-gray-700">重置</a>
    </form>
</div>

<!-- 任务列表 -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">
                    <input type="checkbox" id="checkAll" class="rounded">
                </th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">源文件</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">时长</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">分辨率</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">创建时间</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($list)): ?>
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">暂无转码任务</td>
            </tr>
            <?php else: ?>
            <?php foreach ($list as $item): ?>
            <tr class="hover:bg-gray-50" data-id="<?= $item['transcode_id'] ?>">
                <td class="px-4 py-3">
                    <input type="checkbox" class="row-check rounded" value="<?= $item['transcode_id'] ?>">
                </td>
                <td class="px-4 py-3 text-sm"><?= $item['transcode_id'] ?></td>
                <td class="px-4 py-3">
                    <div class="text-sm truncate max-w-xs" title="<?= htmlspecialchars($item['source_file']) ?>">
                        <?= htmlspecialchars(basename($item['source_file'])) ?>
                    </div>
                    <?php if (!empty($item['vod_name'])): ?>
                    <div class="text-xs text-gray-400">关联: <?= htmlspecialchars($item['vod_name']) ?></div>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm"><?= $item['duration'] > 0 ? gmdate('H:i:s', $item['duration']) : '-' ?></td>
                <td class="px-4 py-3 text-sm"><?= $item['resolution'] ?: '-' ?></td>
                <td class="px-4 py-3">
                    <?php
                    $statusClass = ['bg-yellow-100 text-yellow-700', 'bg-blue-100 text-blue-700', 'bg-green-100 text-green-700', 'bg-red-100 text-red-700'];
                    $statusText = ['待处理', '处理中', '已完成', '失败'];
                    $s = $item['transcode_status'];
                    ?>
                    <span class="px-2 py-1 rounded text-xs <?= $statusClass[$s] ?? 'bg-gray-100' ?>">
                        <?= $statusText[$s] ?? '未知' ?>
                    </span>
                    <?php if ($s == 1): ?>
                    <div class="mt-1 w-24 bg-gray-200 rounded-full h-1.5">
                        <div class="bg-blue-500 h-1.5 rounded-full" style="width: <?= $item['transcode_progress'] ?>%"></div>
                    </div>
                    <span class="text-xs text-gray-400"><?= $item['transcode_progress'] ?>%</span>
                    <?php endif; ?>
                    <?php if ($s == 3 && !empty($item['error_msg'])): ?>
                    <div class="text-xs text-red-500 truncate max-w-[150px]" title="<?= htmlspecialchars($item['error_msg']) ?>">
                        <?= htmlspecialchars($item['error_msg']) ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500"><?= date('Y-m-d H:i', $item['created_at']) ?></td>
                <td class="px-4 py-3">
                    <div class="flex gap-1">
                        <?php if ($s == 2): ?>
                        <button onclick="copyM3u8(<?= $item['transcode_id'] ?>)" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded" title="复制播放地址">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        </button>
                        <button onclick="previewVideo(<?= $item['transcode_id'] ?>)" class="p-1.5 text-green-500 hover:bg-green-50 rounded" title="预览">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </button>
                        <?php endif; ?>
                        <?php if ($s == 3): ?>
                        <button onclick="retryTask(<?= $item['transcode_id'] ?>)" class="p-1.5 text-yellow-500 hover:bg-yellow-50 rounded" title="重试">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        </button>
                        <?php endif; ?>
                        <?php if ($s == 0): ?>
                        <button onclick="processTask(<?= $item['transcode_id'] ?>)" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded" title="立即转码">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"></path></svg>
                        </button>
                        <?php endif; ?>
                        <button onclick="deleteTask(<?= $item['transcode_id'] ?>)" class="p-1.5 text-red-500 hover:bg-red-50 rounded" title="删除">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ($totalPages > 1): ?>
    <div class="px-4 py-3 border-t flex justify-center">
        <div class="flex gap-1">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&status=<?= $status ?>" class="px-3 py-1 rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- 批量操作 -->
<div class="mt-4">
    <button onclick="batchDelete()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm">
        批量删除
    </button>
</div>

<!-- 预览模态框 -->
<div id="previewModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 overflow-hidden">
        <div class="flex justify-between items-center px-4 py-3 border-b">
            <h3 class="font-medium">视频预览</h3>
            <button onclick="closePreview()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-0">
            <video id="previewPlayer" controls class="w-full" style="max-height: 70vh;"></video>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
const csrfToken = '<?= $csrfToken ?>';

// 全选
document.getElementById('checkAll').addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
});

// 复制 m3u8 地址
function copyM3u8(id) {
    fetch('/admin.php/transcode/play?id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                navigator.clipboard.writeText(data.data.m3u8).then(() => {
                    xpkToast('播放地址已复制', 'success');
                });
            } else {
                xpkToast(data.msg || '获取失败', 'error');
            }
        });
}

// 预览视频
let hls = null;
function previewVideo(id) {
    fetch('/admin.php/transcode/play?id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                const video = document.getElementById('previewPlayer');
                if (Hls.isSupported()) {
                    if (hls) hls.destroy();
                    hls = new Hls();
                    hls.loadSource(data.data.m3u8);
                    hls.attachMedia(video);
                    hls.on(Hls.Events.MANIFEST_PARSED, () => video.play());
                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = data.data.m3u8;
                    video.play();
                }
                document.getElementById('previewModal').classList.remove('hidden');
                document.getElementById('previewModal').classList.add('flex');
            } else {
                xpkToast(data.msg || '获取失败', 'error');
            }
        });
}

function closePreview() {
    document.getElementById('previewModal').classList.add('hidden');
    document.getElementById('previewModal').classList.remove('flex');
    document.getElementById('previewPlayer').pause();
    if (hls) {
        hls.destroy();
        hls = null;
    }
}

// 重试任务
function retryTask(id) {
    xpkConfirm('确定重试此任务？', function() {
        fetch('/admin.php/transcode/retry', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=' + csrfToken + '&id=' + id
        })
        .then(r => r.json())
        .then(data => {
            xpkToast(data.msg, data.code === 0 ? 'success' : 'error');
            if (data.code === 0) location.reload();
        });
    });
}

// 立即转码（后台执行 + 轮询进度）
let pollingTimer = null;

function processTask(id) {
    xpkConfirm('确定立即开始转码？', function() {
        xpkToast('正在启动转码...', 'info');
        fetch('/admin.php/transcode/process', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=' + csrfToken + '&id=' + id
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
                // 开始轮询进度
                startPolling(id);
            } else {
                xpkToast(data.msg || '启动失败', 'error');
            }
        })
        .catch(() => {
            xpkToast('请求失败', 'error');
        });
    });
}

function startPolling(id) {
    if (pollingTimer) clearInterval(pollingTimer);
    
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (!row) return;
    
    pollingTimer = setInterval(() => {
        fetch('/admin.php/transcode/progress?id=' + id)
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                
                const d = data.data;
                // 更新状态显示
                const statusCell = row.querySelector('td:nth-child(6)');
                if (statusCell) {
                    if (d.status == 1) {
                        statusCell.innerHTML = `
                            <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700">处理中</span>
                            <div class="mt-1 w-24 bg-gray-200 rounded-full h-1.5">
                                <div class="bg-blue-500 h-1.5 rounded-full" style="width: ${d.progress}%"></div>
                            </div>
                            <span class="text-xs text-gray-400">${d.progress}%</span>
                        `;
                    } else if (d.status == 2) {
                        clearInterval(pollingTimer);
                        pollingTimer = null;
                        xpkToast('转码完成！', 'success');
                        location.reload();
                    } else if (d.status == 3) {
                        clearInterval(pollingTimer);
                        pollingTimer = null;
                        xpkToast('转码失败: ' + (d.error_msg || '未知错误'), 'error');
                        location.reload();
                    }
                }
            });
    }, 2000); // 每2秒轮询一次
}

// 删除任务
function deleteTask(id) {
    xpkConfirm('确定删除此任务？相关文件也会被删除。', function() {
        fetch('/admin.php/transcode/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=' + csrfToken + '&id=' + id + '&delete_files=1'
        })
        .then(r => r.json())
        .then(data => {
            xpkToast(data.msg, data.code === 0 ? 'success' : 'error');
            if (data.code === 0) location.reload();
        });
    });
}

// 批量删除
function batchDelete() {
    const ids = Array.from(document.querySelectorAll('.row-check:checked')).map(cb => cb.value);
    if (ids.length === 0) {
        xpkToast('请选择要删除的任务', 'warning');
        return;
    }
    
    xpkConfirm(`确定删除选中的 ${ids.length} 个任务？`, function() {
        const body = '_token=' + csrfToken + '&delete_files=1&' + ids.map(id => 'ids[]=' + id).join('&');
        
        fetch('/admin.php/transcode/batchDelete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: body
        })
        .then(r => r.json())
        .then(data => {
            xpkToast(data.msg, data.code === 0 ? 'success' : 'error');
            if (data.code === 0) location.reload();
        });
    });
}
</script>
