<div class="mb-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="/<?= $adminEntry ?>/short" class="text-gray-500 hover:text-gray-700">← 返回列表</a>
        <h2 class="text-2xl font-bold"><?= isset($short) ? '编辑' : ($type === 'drama' ? '添加短剧' : '添加短视频') ?></h2>
    </div>
</div>

<!-- Quill 编辑器样式 -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

<form method="post" action="/<?= $adminEntry ?>/short/<?= isset($short) ? 'doEdit/' . $short['short_id'] : 'doAdd' ?>" class="bg-white rounded shadow p-6 max-w-3xl" data-no-ajax="true">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">
    <input type="hidden" name="short_type" value="<?= $type ?>">

    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">标题 <span class="text-red-500">*</span></label>
            <input type="text" name="short_name" value="<?= htmlspecialchars($short['short_name'] ?? '') ?>" 
                   class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">封面图</label>
                <input type="text" name="short_pic" value="<?= htmlspecialchars($short['short_pic'] ?? '') ?>" 
                       class="w-full border rounded px-3 py-2" placeholder="竖版封面 9:16">
                <p class="text-xs text-gray-400 mt-1">建议尺寸 720x1280</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">时长（秒）</label>
                <input type="number" name="short_duration" value="<?= $short['short_duration'] ?? 0 ?>" 
                       class="w-full border rounded px-3 py-2" min="0">
            </div>
        </div>

        <?php if ($type === 'video'): ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">视频地址</label>
            <input type="text" name="short_url" value="<?= htmlspecialchars($short['short_url'] ?? '') ?>" 
                   class="w-full border rounded px-3 py-2" placeholder="https://...mp4">
            <p class="text-xs text-gray-400 mt-1">支持 mp4/m3u8 格式</p>
        </div>
        <?php else: ?>
        <div class="bg-purple-50 p-4 rounded">
            <p class="text-purple-700 text-sm flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                短剧需要在保存后添加剧集
            </p>
        </div>
        <?php endif; ?>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">简介</label>
            <div id="editor" style="height: 150px;"><?= $short['short_desc'] ?? '' ?></div>
            <input type="hidden" name="short_desc" id="short_desc">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">标签</label>
            <input type="text" name="short_tags" value="<?= htmlspecialchars($short['short_tags'] ?? '') ?>" 
                   class="w-full border rounded px-3 py-2" placeholder="用逗号分隔，如：搞笑,剧情,甜宠">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
            <select name="short_status" class="border rounded px-3 py-2">
                <option value="1" <?= ($short['short_status'] ?? 1) == 1 ? 'selected' : '' ?>>上架</option>
                <option value="0" <?= ($short['short_status'] ?? 1) == 0 ? 'selected' : '' ?>>下架</option>
            </select>
        </div>
    </div>

    <div class="mt-6 pt-4 border-t flex gap-4">
        <button type="submit" class="bg-primary text-white px-6 py-2 rounded hover:bg-red-600">
            保存
        </button>
        <a href="/<?= $adminEntry ?>/short" class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
            取消
        </a>
    </div>
</form>

<!-- Quill 编辑器 -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'color': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });

    // 表单AJAX提交
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 同步编辑器内容
        document.getElementById('short_desc').value = quill.root.innerHTML;
        
        const form = this;
        const btn = form.querySelector('button[type="submit"]');
        const btnText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '保存中...';
        
        fetch(form.action || location.href, {
            method: 'POST',
            body: new FormData(form)
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
                setTimeout(() => {
                    // 使用服务器返回的跳转地址
                    if (data.data && data.data.url) {
                        location.href = data.data.url;
                    } else {
                        location.href = adminUrl('/short');
                    }
                }, 1000);
            } else {
                xpkToast(data.msg, 'error');
                btn.disabled = false;
                btn.textContent = btnText;
            }
        })
        .catch(err => {
            xpkToast('请求失败', 'error');
            btn.disabled = false;
            btn.textContent = btnText;
        });
    });
});
</script>
