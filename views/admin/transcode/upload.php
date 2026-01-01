<?php include VIEW_PATH . 'admin/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">上传视频</h4>
    <a href="/admin.php/transcode" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> 返回列表
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <!-- 上传区域 -->
                <div id="dropZone" class="border-2 border-dashed rounded-3 p-5 text-center bg-light" style="cursor: pointer;">
                    <i class="bi bi-cloud-upload fs-1 text-muted"></i>
                    <p class="mt-3 mb-1">拖拽视频文件到此处，或点击选择文件</p>
                    <p class="text-muted small">支持 MP4, AVI, MKV, MOV, WMV, FLV, WebM 格式，最大 10GB</p>
                    <input type="file" id="fileInput" accept=".mp4,.avi,.mkv,.mov,.wmv,.flv,.webm,.m4v" style="display: none;">
                </div>

                <!-- 上传进度 -->
                <div id="uploadProgress" class="mt-4" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span id="fileName" class="text-truncate" style="max-width: 70%;"></span>
                        <span id="uploadPercent">0%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 small text-muted">
                        <span id="uploadedSize">0 MB</span>
                        <span id="uploadSpeed">-- MB/s</span>
                        <span id="totalSize">0 MB</span>
                    </div>
                </div>

                <!-- 上传完成 -->
                <div id="uploadComplete" class="mt-4 text-center" style="display: none;">
                    <i class="bi bi-check-circle text-success fs-1"></i>
                    <p class="mt-2 mb-1">上传完成！</p>
                    <p class="text-muted small">转码任务已创建，任务ID: <span id="taskId"></span></p>
                    <div class="mt-3">
                        <button class="btn btn-primary" onclick="location.reload()">继续上传</button>
                        <a href="/admin.php/transcode" class="btn btn-outline-secondary">查看任务</a>
                    </div>
                </div>

                <!-- 错误提示 -->
                <div id="uploadError" class="alert alert-danger mt-4" style="display: none;"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">使用说明</h6>
            </div>
            <div class="card-body">
                <ol class="mb-0 ps-3">
                    <li class="mb-2">选择或拖拽视频文件上传</li>
                    <li class="mb-2">上传完成后自动创建转码任务</li>
                    <li class="mb-2">转码完成后可获取 m3u8 播放地址</li>
                    <li class="mb-2">视频会自动加密，防止被下载</li>
                </ol>
                <hr>
                <h6>支持格式</h6>
                <p class="text-muted small mb-0">MP4, AVI, MKV, MOV, WMV, FLV, WebM, M4V</p>
                <hr>
                <h6>转码参数</h6>
                <ul class="text-muted small mb-0 ps-3">
                    <li>视频编码: H.264</li>
                    <li>音频编码: AAC 128kbps</li>
                    <li>切片时长: 10秒</li>
                    <li>加密方式: AES-128</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
<script>
const csrfToken = '<?= $csrfToken ?>';

// 初始化 Resumable.js
const r = new Resumable({
    target: '/admin.php/transcode/doUpload',
    chunkSize: 5 * 1024 * 1024, // 5MB
    simultaneousUploads: 3,
    testChunks: true,
    throttleProgressCallbacks: 1,
    query: { _token: csrfToken },
    headers: {},
    maxFiles: 1,
    fileType: ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', 'm4v'],
    maxFileSize: 10 * 1024 * 1024 * 1024, // 10GB
});

// 绑定拖拽区域
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');

r.assignDrop(dropZone);
r.assignBrowse(fileInput);

dropZone.addEventListener('click', () => fileInput.click());

// 拖拽样式
dropZone.addEventListener('dragover', () => dropZone.classList.add('border-primary', 'bg-primary', 'bg-opacity-10'));
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10'));
dropZone.addEventListener('drop', () => dropZone.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10'));

// 文件添加
r.on('fileAdded', function(file) {
    document.getElementById('dropZone').style.display = 'none';
    document.getElementById('uploadProgress').style.display = 'block';
    document.getElementById('fileName').textContent = file.fileName;
    document.getElementById('totalSize').textContent = formatSize(file.size);
    r.upload();
});

// 上传进度
let lastLoaded = 0;
let lastTime = Date.now();

r.on('fileProgress', function(file) {
    const progress = Math.floor(file.progress() * 100);
    document.getElementById('progressBar').style.width = progress + '%';
    document.getElementById('uploadPercent').textContent = progress + '%';
    
    const loaded = file.progress() * file.size;
    document.getElementById('uploadedSize').textContent = formatSize(loaded);
    
    // 计算速度
    const now = Date.now();
    const timeDiff = (now - lastTime) / 1000;
    if (timeDiff > 0.5) {
        const speed = (loaded - lastLoaded) / timeDiff;
        document.getElementById('uploadSpeed').textContent = formatSize(speed) + '/s';
        lastLoaded = loaded;
        lastTime = now;
    }
});

// 上传完成
r.on('fileSuccess', function(file, response) {
    try {
        const data = JSON.parse(response);
        if (data.success && data.complete) {
            document.getElementById('uploadProgress').style.display = 'none';
            document.getElementById('uploadComplete').style.display = 'block';
            document.getElementById('taskId').textContent = data.task_id;
        } else if (!data.success) {
            showError(data.error || '上传失败');
        }
    } catch (e) {
        showError('解析响应失败');
    }
});

// 上传错误
r.on('fileError', function(file, message) {
    showError('上传失败: ' + message);
});

function showError(msg) {
    document.getElementById('uploadProgress').style.display = 'none';
    document.getElementById('uploadError').style.display = 'block';
    document.getElementById('uploadError').textContent = msg;
}

function formatSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>

<?php include VIEW_PATH . 'admin/layouts/footer.php'; ?>
