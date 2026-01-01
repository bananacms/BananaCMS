<?php include VIEW_PATH . 'admin/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">云转码</h4>
    <div>
        <?php if ($ffmpegAvailable): ?>
            <span class="badge bg-success me-2">FFmpeg <?= htmlspecialchars($ffmpegVersion) ?></span>
        <?php else: ?>
            <span class="badge bg-danger me-2">FFmpeg 未安装</span>
        <?php endif; ?>
        <a href="/admin.php/transcode/upload" class="btn btn-primary btn-sm <?= $ffmpegAvailable ? '' : 'disabled' ?>">
            <i class="bi bi-upload"></i> 上传视频
        </a>
    </div>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
    <?= htmlspecialchars($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- 统计卡片 -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">总任务</div>
                        <div class="fs-4 fw-bold"><?= $stats['total'] ?></div>
                    </div>
                    <i class="bi bi-collection-play fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-dark-50 small">待处理</div>
                        <div class="fs-4 fw-bold"><?= $stats['pending'] ?></div>
                    </div>
                    <i class="bi bi-hourglass-split fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">已完成</div>
                        <div class="fs-4 fw-bold"><?= $stats['completed'] ?></div>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">失败</div>
                        <div class="fs-4 fw-bold"><?= $stats['failed'] ?></div>
                    </div>
                    <i class="bi bi-x-circle fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 筛选 -->
<div class="card mb-4">
    <div class="card-body py-2">
        <form class="row g-2 align-items-center" method="get">
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">全部状态</option>
                    <option value="0" <?= $status === '0' ? 'selected' : '' ?>>待处理</option>
                    <option value="1" <?= $status === '1' ? 'selected' : '' ?>>处理中</option>
                    <option value="2" <?= $status === '2' ? 'selected' : '' ?>>已完成</option>
                    <option value="3" <?= $status === '3' ? 'selected' : '' ?>>失败</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary btn-sm">筛选</button>
                <a href="/admin.php/transcode" class="btn btn-outline-secondary btn-sm">重置</a>
            </div>
        </form>
    </div>
</div>

<!-- 任务列表 -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th width="50">
                        <input type="checkbox" class="form-check-input" id="checkAll">
                    </th>
                    <th width="60">ID</th>
                    <th>源文件</th>
                    <th width="100">时长</th>
                    <th width="100">分辨率</th>
                    <th width="120">状态</th>
                    <th width="150">创建时间</th>
                    <th width="150">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($list)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">暂无转码任务</td>
                </tr>
                <?php else: ?>
                <?php foreach ($list as $item): ?>
                <tr data-id="<?= $item['transcode_id'] ?>">
                    <td>
                        <input type="checkbox" class="form-check-input row-check" value="<?= $item['transcode_id'] ?>">
                    </td>
                    <td><?= $item['transcode_id'] ?></td>
                    <td>
                        <div class="text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($item['source_file']) ?>">
                            <?= htmlspecialchars(basename($item['source_file'])) ?>
                        </div>
                        <?php if (!empty($item['vod_name'])): ?>
                        <small class="text-muted">关联: <?= htmlspecialchars($item['vod_name']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= $item['duration'] > 0 ? gmdate('H:i:s', $item['duration']) : '-' ?></td>
                    <td><?= $item['resolution'] ?: '-' ?></td>
                    <td>
                        <?php
                        $statusClass = ['warning', 'info', 'success', 'danger'];
                        $statusText = ['待处理', '处理中', '已完成', '失败'];
                        $s = $item['transcode_status'];
                        ?>
                        <span class="badge bg-<?= $statusClass[$s] ?? 'secondary' ?>">
                            <?= $statusText[$s] ?? '未知' ?>
                        </span>
                        <?php if ($s == 1): ?>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar" style="width: <?= $item['transcode_progress'] ?>%"></div>
                        </div>
                        <small class="text-muted"><?= $item['transcode_progress'] ?>%</small>
                        <?php endif; ?>
                        <?php if ($s == 3 && !empty($item['error_msg'])): ?>
                        <div class="text-danger small text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($item['error_msg']) ?>">
                            <?= htmlspecialchars($item['error_msg']) ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td><?= date('Y-m-d H:i', $item['created_at']) ?></td>
                    <td>
                        <?php if ($s == 2): ?>
                        <button class="btn btn-sm btn-outline-primary" onclick="copyM3u8(<?= $item['transcode_id'] ?>)" title="复制播放地址">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="previewVideo(<?= $item['transcode_id'] ?>)" title="预览">
                            <i class="bi bi-play-circle"></i>
                        </button>
                        <?php endif; ?>
                        <?php if ($s == 3): ?>
                        <button class="btn btn-sm btn-outline-warning" onclick="retryTask(<?= $item['transcode_id'] ?>)" title="重试">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        <?php endif; ?>
                        <?php if ($s == 0): ?>
                        <button class="btn btn-sm btn-outline-info" onclick="processTask(<?= $item['transcode_id'] ?>)" title="立即转码">
                            <i class="bi bi-play-fill"></i>
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteTask(<?= $item['transcode_id'] ?>)" title="删除">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalPages > 1): ?>
    <div class="card-footer">
        <nav>
            <ul class="pagination pagination-sm mb-0 justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&status=<?= $status ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- 批量操作 -->
<div class="mt-3">
    <button class="btn btn-danger btn-sm" onclick="batchDelete()">
        <i class="bi bi-trash"></i> 批量删除
    </button>
</div>

<!-- 预览模态框 -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">视频预览</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <video id="previewPlayer" controls class="w-100" style="max-height: 70vh;"></video>
            </div>
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
            if (data.success) {
                navigator.clipboard.writeText(data.data.m3u8).then(() => {
                    alert('播放地址已复制');
                });
            } else {
                alert(data.error || '获取失败');
            }
        });
}

// 预览视频
function previewVideo(id) {
    fetch('/admin.php/transcode/play?id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const video = document.getElementById('previewPlayer');
                if (Hls.isSupported()) {
                    const hls = new Hls();
                    hls.loadSource(data.data.m3u8);
                    hls.attachMedia(video);
                    hls.on(Hls.Events.MANIFEST_PARSED, () => video.play());
                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = data.data.m3u8;
                    video.play();
                }
                new bootstrap.Modal(document.getElementById('previewModal')).show();
            } else {
                alert(data.error || '获取失败');
            }
        });
}

// 关闭模态框时停止播放
document.getElementById('previewModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('previewPlayer').pause();
    document.getElementById('previewPlayer').src = '';
});

// 重试任务
function retryTask(id) {
    if (!confirm('确定重试此任务？')) return;
    
    fetch('/admin.php/transcode/retry', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_token=' + csrfToken + '&id=' + id
    })
    .then(r => r.json())
    .then(data => {
        alert(data.msg);
        if (data.code === 0) location.reload();
    });
}

// 立即转码
function processTask(id) {
    if (!confirm('确定立即开始转码？')) return;
    
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
    fetch('/admin.php/transcode/process', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_token=' + csrfToken + '&id=' + id
    })
    .then(r => r.json())
    .then(data => {
        alert(data.msg);
        location.reload();
    })
    .catch(() => {
        alert('请求失败');
        location.reload();
    });
}

// 删除任务
function deleteTask(id) {
    if (!confirm('确定删除此任务？相关文件也会被删除。')) return;
    
    fetch('/admin.php/transcode/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_token=' + csrfToken + '&id=' + id + '&delete_files=1'
    })
    .then(r => r.json())
    .then(data => {
        alert(data.msg);
        if (data.code === 0) location.reload();
    });
}

// 批量删除
function batchDelete() {
    const ids = Array.from(document.querySelectorAll('.row-check:checked')).map(cb => cb.value);
    if (ids.length === 0) {
        alert('请选择要删除的任务');
        return;
    }
    
    if (!confirm(`确定删除选中的 ${ids.length} 个任务？`)) return;
    
    const body = '_token=' + csrfToken + '&delete_files=1&' + ids.map(id => 'ids[]=' + id).join('&');
    
    fetch('/admin.php/transcode/batchDelete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: body
    })
    .then(r => r.json())
    .then(data => {
        alert(data.msg);
        if (data.code === 0) location.reload();
    });
}
</script>

<?php include VIEW_PATH . 'admin/layouts/footer.php'; ?>
