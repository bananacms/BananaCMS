<div class="mb-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="/<?= $adminEntry ?>?s=ad" class="text-gray-500 hover:text-gray-700">← 返回列表</a>
        <h2 class="text-2xl font-bold"><?= isset($ad) ? '编辑广告' : '添加广告' ?></h2>
    </div>
</div>

<form method="post" action="/<?= $adminEntry ?>?s=ad/<?= isset($ad) ? 'doEdit/' . $ad['ad_id'] : 'doAdd' ?>" class="bg-white rounded shadow p-6 max-w-4xl">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="grid grid-cols-2 gap-6">
        <!-- 左列 -->
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">广告名称 <span class="text-red-500">*</span></label>
                <input type="text" name="ad_title" value="<?= htmlspecialchars($ad['ad_title'] ?? '') ?>" 
                       class="w-full border rounded px-3 py-2" required placeholder="用于后台识别">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">广告位置 <span class="text-red-500">*</span></label>
                <select name="ad_position" class="w-full border rounded px-3 py-2" required>
                    <?php foreach ($positions as $key => $name): ?>
                    <option value="<?= $key ?>" <?= ($ad['ad_position'] ?? '') === $key ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">广告类型</label>
                <select name="ad_type" id="adType" class="w-full border rounded px-3 py-2" onchange="toggleTypeFields()">
                    <?php foreach ($types as $key => $name): ?>
                    <option value="<?= $key ?>" <?= ($ad['ad_type'] ?? 'image') === $key ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="fieldImage" class="type-field">
                <label class="block text-sm font-medium text-gray-700 mb-1">图片地址</label>
                <input type="text" name="ad_image" value="<?= htmlspecialchars($ad['ad_image'] ?? '') ?>" 
                       class="w-full border rounded px-3 py-2" placeholder="https://...">
                <p class="text-xs text-gray-400 mt-1">建议尺寸：横幅 728x90，侧边栏 300x250</p>
            </div>

            <div id="fieldLink" class="type-field">
                <label class="block text-sm font-medium text-gray-700 mb-1">跳转链接</label>
                <input type="text" name="ad_link" value="<?= htmlspecialchars($ad['ad_link'] ?? '') ?>" 
                       class="w-full border rounded px-3 py-2" placeholder="https://...">
            </div>

            <div id="fieldCode" class="type-field" style="display:none;">
                <label class="block text-sm font-medium text-gray-700 mb-1">广告代码</label>
                <textarea name="ad_code" rows="6" class="w-full border rounded px-3 py-2 font-mono text-sm"
                          placeholder="粘贴第三方广告代码..."><?= htmlspecialchars($ad['ad_code'] ?? '') ?></textarea>
                <p class="text-xs text-gray-400 mt-1">支持 HTML/JS 代码，如 Google AdSense</p>
            </div>

            <div id="fieldVideo" class="type-field" style="display:none;">
                <label class="block text-sm font-medium text-gray-700 mb-1">视频地址</label>
                <input type="text" name="ad_video" value="<?= htmlspecialchars($ad['ad_video'] ?? '') ?>" 
                       class="w-full border rounded px-3 py-2" placeholder="https://...mp4">
            </div>
        </div>

        <!-- 右列 -->
        <div class="space-y-4">
            <div id="fieldDuration" class="type-field" style="display:none;">
                <label class="block text-sm font-medium text-gray-700 mb-1">视频时长（秒）</label>
                <input type="number" name="ad_duration" value="<?= $ad['ad_duration'] ?? 15 ?>" 
                       class="w-full border rounded px-3 py-2" min="0">
            </div>

            <div id="fieldSkip" class="type-field" style="display:none;">
                <label class="block text-sm font-medium text-gray-700 mb-1">跳过时间（秒）</label>
                <input type="number" name="ad_skip_time" value="<?= $ad['ad_skip_time'] ?? 5 ?>" 
                       class="w-full border rounded px-3 py-2" min="0">
                <p class="text-xs text-gray-400 mt-1">0 表示不可跳过</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                <input type="number" name="ad_sort" value="<?= $ad['ad_sort'] ?? 0 ?>" 
                       class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-400 mt-1">数字越小越靠前</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                <select name="ad_status" class="w-full border rounded px-3 py-2">
                    <option value="1" <?= ($ad['ad_status'] ?? 1) == 1 ? 'selected' : '' ?>>启用</option>
                    <option value="0" <?= ($ad['ad_status'] ?? 1) == 0 ? 'selected' : '' ?>>禁用</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">开始时间</label>
                <input type="datetime-local" name="ad_start_time" 
                       value="<?= !empty($ad['ad_start_time']) ? date('Y-m-d\TH:i', $ad['ad_start_time']) : '' ?>" 
                       class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-400 mt-1">留空表示立即生效</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">结束时间</label>
                <input type="datetime-local" name="ad_end_time" 
                       value="<?= !empty($ad['ad_end_time']) ? date('Y-m-d\TH:i', $ad['ad_end_time']) : '' ?>" 
                       class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-400 mt-1">留空表示永不过期</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">备注</label>
                <textarea name="ad_remark" rows="3" class="w-full border rounded px-3 py-2"
                          placeholder="内部备注..."><?= htmlspecialchars($ad['ad_remark'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="mt-6 pt-4 border-t flex gap-4">
        <button type="submit" class="bg-primary text-white px-6 py-2 rounded hover:bg-red-600">
            保存
        </button>
        <a href="/<?= $adminEntry ?>?s=ad" class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
            取消
        </a>
    </div>
</form>

<script>
function toggleTypeFields() {
    const type = document.getElementById('adType').value;
    
    // 隐藏所有特定字段
    document.getElementById('fieldImage').style.display = 'none';
    document.getElementById('fieldLink').style.display = 'none';
    document.getElementById('fieldCode').style.display = 'none';
    document.getElementById('fieldVideo').style.display = 'none';
    document.getElementById('fieldDuration').style.display = 'none';
    document.getElementById('fieldSkip').style.display = 'none';
    
    // 根据类型显示对应字段
    switch (type) {
        case 'image':
            document.getElementById('fieldImage').style.display = 'block';
            document.getElementById('fieldLink').style.display = 'block';
            break;
        case 'text':
            document.getElementById('fieldLink').style.display = 'block';
            break;
        case 'code':
            document.getElementById('fieldCode').style.display = 'block';
            break;
        case 'video':
            document.getElementById('fieldVideo').style.display = 'block';
            document.getElementById('fieldLink').style.display = 'block';
            document.getElementById('fieldDuration').style.display = 'block';
            document.getElementById('fieldSkip').style.display = 'block';
            break;
    }
}

// 初始化
toggleTypeFields();
</script>
