<div class="mb-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="/admin.php/short" class="text-gray-500 hover:text-gray-700">← 返回列表</a>
        <h2 class="text-2xl font-bold"><?= isset($short) ? '编辑' : ($type === 'drama' ? '添加短剧' : '添加短视频') ?></h2>
    </div>
</div>

<form method="post" action="/admin.php/short/<?= isset($short) ? 'doEdit/' . $short['short_id'] : 'doAdd' ?>" class="bg-white rounded shadow p-6 max-w-3xl">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
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
            <p class="text-purple-700 text-sm">💡 短剧需要在保存后添加剧集</p>
        </div>
        <?php endif; ?>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">简介</label>
            <textarea name="short_desc" rows="3" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($short['short_desc'] ?? '') ?></textarea>
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
        <a href="/admin.php/short" class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
            取消
        </a>
    </div>
</form>
