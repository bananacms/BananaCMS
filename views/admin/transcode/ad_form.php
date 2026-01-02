<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold"><?= $ad ? '编辑' : '添加' ?>转码广告</h1>
    <a href="/admin.php/transcode/ad" class="text-gray-500 hover:text-gray-700">← 返回列表</a>
</div>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="post" class="space-y-6" id="adForm" onsubmit="return submitForm(event)">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">广告名称 <span class="text-red-500">*</span></label>
            <input type="text" name="ad_name" value="<?= htmlspecialchars($ad['ad_name'] ?? '') ?>" required
                   class="w-full border rounded px-3 py-2" placeholder="如：片头广告1">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">广告位置 <span class="text-red-500">*</span></label>
            <select name="ad_position" class="w-full border rounded px-3 py-2">
                <option value="head" <?= ($ad['ad_position'] ?? '') === 'head' ? 'selected' : '' ?>>片头广告</option>
                <option value="middle" <?= ($ad['ad_position'] ?? '') === 'middle' ? 'selected' : '' ?>>片中广告</option>
                <option value="tail" <?= ($ad['ad_position'] ?? '') === 'tail' ? 'selected' : '' ?>>片尾广告</option>
            </select>
            <p class="text-xs text-gray-400 mt-1">片头：视频开始前播放 | 片中：按间隔时间插入 | 片尾：视频结束后播放</p>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">广告视频 <span class="text-red-500">*</span></label>
            <div class="flex gap-2">
                <input type="text" name="ad_file" id="adFile" value="<?= htmlspecialchars($ad['ad_file'] ?? '') ?>" required
                       class="flex-1 border rounded px-3 py-2" placeholder="上传或输入视频路径">
                <label class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded cursor-pointer">
                    上传
                    <input type="file" accept="video/*" onchange="uploadVideo(this)" class="hidden">
                </label>
            </div>
            <div id="uploadProgress" class="hidden mt-2">
                <div class="bg-gray-200 rounded-full h-2">
                    <div id="progressBar" class="bg-blue-500 h-2 rounded-full transition-all" style="width: 0%"></div>
                </div>
                <p id="progressText" class="text-xs text-gray-500 mt-1">上传中...</p>
            </div>
            <?php if (!empty($ad['ad_file'])): ?>
            <div class="mt-2">
                <video src="<?= htmlspecialchars($ad['ad_file']) ?>" controls class="max-w-sm rounded"></video>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">时长（秒）</label>
                <input type="number" name="ad_duration" id="adDuration" value="<?= $ad['ad_duration'] ?? 0 ?>" min="0"
                       class="w-full border rounded px-3 py-2" placeholder="自动获取">
                <p class="text-xs text-gray-400 mt-1">留空自动获取</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                <input type="number" name="ad_sort" value="<?= $ad['ad_sort'] ?? 0 ?>" min="0"
                       class="w-full border rounded px-3 py-2" placeholder="数字越小越靠前">
            </div>
        </div>
        
        <div>
            <label class="flex items-center">
                <input type="hidden" name="ad_status" value="0">
                <input type="checkbox" name="ad_status" value="1" <?= ($ad['ad_status'] ?? 1) ? 'checked' : '' ?> class="mr-2">
                <span>启用此广告</span>
            </label>
        </div>
        
        <div class="flex gap-4 pt-4">
            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded" id="submitBtn">保存</button>
            <a href="/admin.php/transcode/ad" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">取消</a>
        </div>
    </form>
</div>

<script>
function submitForm(e) {
    e.preventDefault();
    
    const form = document.getElementById('adForm');
    const btn = document.getElementById('submitBtn');
    const formData = new FormData(form);
    
    btn.disabled = true;
    btn.textContent = '保存中...';
    
    fetch(form.action || window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg || '保存成功', 'success');
            setTimeout(() => {
                window.location.href = data.data?.url || '/admin.php/transcode/ad';
            }, 500);
        } else {
            xpkToast(data.msg || '保存失败', 'error');
            btn.disabled = false;
            btn.textContent = '保存';
        }
    })
    .catch(() => {
        xpkToast('请求失败', 'error');
        btn.disabled = false;
        btn.textContent = '保存';
    });
    
    return false;
}

function uploadVideo(input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    const formData = new FormData();
    formData.append('file', file);
    
    const progress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    
    progress.classList.remove('hidden');
    progressBar.style.width = '0%';
    progressText.textContent = '上传中...';
    
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
            progressText.textContent = '上传中... ' + percent + '%';
        }
    });
    
    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    document.getElementById('adFile').value = data.url;
                    if (data.duration) {
                        document.getElementById('adDuration').value = data.duration;
                    }
                    progressText.textContent = '上传成功！';
                    progressBar.classList.remove('bg-blue-500');
                    progressBar.classList.add('bg-green-500');
                } else {
                    progressText.textContent = '上传失败：' + (data.error || '未知错误');
                    progressBar.classList.remove('bg-blue-500');
                    progressBar.classList.add('bg-red-500');
                }
            } catch (e) {
                progressText.textContent = '上传失败：解析响应错误';
            }
        } else {
            progressText.textContent = '上传失败：服务器错误';
        }
    });
    
    xhr.addEventListener('error', function() {
        progressText.textContent = '上传失败：网络错误';
        progressBar.classList.remove('bg-blue-500');
        progressBar.classList.add('bg-red-500');
    });
    
    xhr.open('POST', '/admin.php/transcode/ad/upload');
    xhr.send(formData);
}
</script>
