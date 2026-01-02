<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">上传视频</h1>
    <a href="/<?= $adminEntry ?>/transcode" class="text-gray-500 hover:text-gray-700">← 返回列表</a>
</div>

<div class="grid grid-cols-3 gap-6">
    <div class="col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <!-- 上传区域 -->
            <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition">
                <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <p class="mt-4 text-lg text-gray-600">拖拽视频文件到此处，或点击选择文件</p>
                <p class="mt-2 text-sm text-gray-400">支持 MP4, AVI, MKV, MOV, WMV, FLV, WebM 格式，最大 10GB</p>
                <input type="file" id="fileInput" accept=".mp4,.avi,.mkv,.mov,.wmv,.flv,.webm,.m4v" class="hidden">
            </div>

            <!-- 上传进度 -->
            <div id="uploadProgress" class="mt-6 hidden">
                <div class="flex justify-between items-center mb-2">
                    <span id="fileName" class="text-sm text-gray-600 truncate max-w-md"></span>
                    <span id="uploadPercent" class="text-sm font-medium text-blue-600">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="progressBar" class="bg-blue-500 h-2 rounded-full transition-all" style="width: 0%"></div>
                </div>
                <div class="flex justify-between mt-2 text-xs text-gray-400">
                    <span id="uploadedSize">0 MB</span>
                    <span id="uploadSpeed">-- MB/s</span>
                    <span id="totalSize">0 MB</span>
                </div>
            </div>

            <!-- 上传完成 -->
            <div id="uploadComplete" class="mt-6 text-center hidden">
                <svg class="w-16 h-16 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-4 text-lg text-gray-700">上传完成！</p>
                <p class="mt-1 text-sm text-gray-500">转码任务已创建，任务ID: <span id="taskId" class="font-medium text-blue-600"></span></p>
                <div class="mt-6 flex justify-center gap-4">
                    <button onclick="location.reload()" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">继续上传</button>
                    <a href="/<?= $adminEntry ?>/transcode" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded">查看任务</a>
                </div>
            </div>

            <!-- 错误提示 -->
            <div id="uploadError" class="mt-6 bg-red-50 text-red-600 px-4 py-3 rounded hidden"></div>
        </div>
    </div>

    <div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-medium text-gray-700 mb-4">使用说明</h3>
            <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                <li>选择或拖拽视频文件上传</li>
                <li>上传完成后自动创建转码任务</li>
                <li>转码完成后可获取 m3u8 播放地址</li>
                <li>视频会自动加密，防止被下载</li>
            </ol>
            
            <hr class="my-4">
            
            <h3 class="font-medium text-gray-700 mb-2">支持格式</h3>
            <p class="text-sm text-gray-500">MP4, AVI, MKV, MOV, WMV, FLV, WebM, M4V</p>
            
            <hr class="my-4">
            
            <h3 class="font-medium text-gray-700 mb-2">转码参数</h3>
            <ul class="text-sm text-gray-500 space-y-1">
                <li>• 视频编码: H.264</li>
                <li>• 音频编码: AAC 128kbps</li>
                <li>• 切片时长: 10秒</li>
                <li>• 加密方式: AES-128</li>
            </ul>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js" defer></script>
<script>
const csrfToken = '<?= $csrfToken ?>';

// 初始化 Resumable.js
const r = new Resumable({
    target: adminUrl('/transcode/doUpload'),
    chunkSize: 5 * 1024 * 1024, // 5MB
    simultaneousUploads: 3,
    testChunks: true,
    throttleProgressCallbacks: 1,
    query: { _token: csrfToken },
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
dropZone.addEventListener('dragover', () => {
    dropZone.classList.add('border-blue-400', 'bg-blue-50');
});
dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
});
dropZone.addEventListener('drop', () => {
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
});

// 文件添加
r.on('fileAdded', function(file) {
    document.getElementById('dropZone').classList.add('hidden');
    document.getElementById('uploadProgress').classList.remove('hidden');
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
            document.getElementById('uploadProgress').classList.add('hidden');
            document.getElementById('uploadComplete').classList.remove('hidden');
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
    document.getElementById('uploadProgress').classList.add('hidden');
    document.getElementById('uploadError').classList.remove('hidden');
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
