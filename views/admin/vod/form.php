<h1 class="text-2xl font-bold mb-6"><?= isset($vod) ? '编辑视频' : '添加视频' ?></h1>

<!-- Quill 编辑器样式 -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

<form method="POST" class="bg-white rounded-lg shadow p-6">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- 基本信息 -->
        <div class="space-y-4">
            <h3 class="font-bold text-gray-700 border-b pb-2">基本信息</h3>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">视频名称 *</label>
                <input type="text" name="vod_name" value="<?= htmlspecialchars($vod['vod_name'] ?? '') ?>" required
                    class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">副标题</label>
                <input type="text" name="vod_sub" value="<?= htmlspecialchars($vod['vod_sub'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">分类 *</label>
                <select name="vod_type_id" required class="w-full border rounded px-3 py-2">
                    <option value="">请选择分类</option>
                    <?php foreach ($types as $t): ?>
                    <option value="<?= $t['type_id'] ?>" <?= ($vod['vod_type_id'] ?? 0) == $t['type_id'] ? 'selected' : '' ?>>
                        <?= str_repeat('　', $t['level'] ?? 0) ?><?= htmlspecialchars($t['type_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">封面图</label>
                <input type="text" name="vod_pic" value="<?= htmlspecialchars($vod['vod_pic'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="图片URL">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">年份</label>
                    <input type="text" name="vod_year" value="<?= htmlspecialchars($vod['vod_year'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" placeholder="<?= date('Y') ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">评分</label>
                    <input type="number" name="vod_score" value="<?= $vod['vod_score'] ?? 0 ?>" step="0.1" min="0" max="10"
                        class="w-full border rounded px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">地区</label>
                    <input type="text" name="vod_area" value="<?= htmlspecialchars($vod['vod_area'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" placeholder="大陆/香港/美国">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">语言</label>
                    <input type="text" name="vod_lang" value="<?= htmlspecialchars($vod['vod_lang'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" placeholder="国语/英语">
                </div>
            </div>
        </div>

        <!-- 人员信息 -->
        <div class="space-y-4">
            <h3 class="font-bold text-gray-700 border-b pb-2">人员信息</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">演员</label>
                <input type="text" name="vod_actor" value="<?= htmlspecialchars($vod['vod_actor'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="多个用逗号分隔">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">导演</label>
                <input type="text" name="vod_director" value="<?= htmlspecialchars($vod['vod_director'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">备注</label>
                <input type="text" name="vod_remarks" value="<?= htmlspecialchars($vod['vod_remarks'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="更新至第X集/HD高清">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                <select name="vod_status" class="w-full border rounded px-3 py-2">
                    <option value="1" <?= ($vod['vod_status'] ?? 1) == 1 ? 'selected' : '' ?>>发布</option>
                    <option value="0" <?= ($vod['vod_status'] ?? 1) == 0 ? 'selected' : '' ?>>草稿</option>
                </select>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="vod_lock" value="1" <?= !empty($vod['vod_lock']) ? 'checked' : '' ?> class="w-4 h-4 rounded">
                    <span class="text-sm font-medium text-gray-700">🔒 锁定视频</span>
                </label>
                <p class="text-xs text-gray-400 mt-1">锁定后采集时将跳过此视频，防止手动编辑的内容被覆盖</p>
            </div>
        </div>
    </div>

    <!-- 扩展信息 -->
    <div class="mt-6 space-y-4">
        <h3 class="font-bold text-gray-700 border-b pb-2">扩展信息</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">标签</label>
                <input type="text" name="vod_tag" value="<?= htmlspecialchars($vod['vod_tag'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="多个用逗号分隔">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">扩展分类</label>
                <input type="text" name="vod_class" value="<?= htmlspecialchars($vod['vod_class'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="如：古装,武侠">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">是否完结</label>
                <select name="vod_isend" class="w-full border rounded px-3 py-2">
                    <option value="0" <?= ($vod['vod_isend'] ?? 0) == 0 ? 'selected' : '' ?>>连载中</option>
                    <option value="1" <?= ($vod['vod_isend'] ?? 0) == 1 ? 'selected' : '' ?>>已完结</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">连载集数</label>
                <input type="text" name="vod_serial" value="<?= htmlspecialchars($vod['vod_serial'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="如：更新至第10集">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">总集数</label>
                <input type="number" name="vod_total" value="<?= $vod['vod_total'] ?? 0 ?>" min="0"
                    class="w-full border rounded px-3 py-2" placeholder="0表示未知">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">更新日</label>
                <input type="text" name="vod_weekday" value="<?= htmlspecialchars($vod['vod_weekday'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="如：每周一">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">资源状态</label>
                <input type="text" name="vod_state" value="<?= htmlspecialchars($vod['vod_state'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="如：蓝光,抢先版">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">版本</label>
                <input type="text" name="vod_version" value="<?= htmlspecialchars($vod['vod_version'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="如：国语,粤语">
            </div>
        </div>
    </div>

    <!-- 播放信息 -->
    <div class="mt-6 space-y-4">
        <h3 class="font-bold text-gray-700 border-b pb-2">播放信息</h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">播放来源</label>
            <input type="text" name="vod_play_from" value="<?= htmlspecialchars($vod['vod_play_from'] ?? '') ?>"
                class="w-full border rounded px-3 py-2" placeholder="多个来源用$$$分隔，如：线路1$$$线路2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">播放地址</label>
            <textarea name="vod_play_url" rows="4" class="w-full border rounded px-3 py-2"
                placeholder="格式：第1集$url#第2集$url&#10;多个来源用$$$分隔"><?= htmlspecialchars($vod['vod_play_url'] ?? '') ?></textarea>
            <p class="text-xs text-gray-400 mt-1">格式说明：集名$播放地址，多集用#分隔，多来源用$$$分隔</p>
        </div>
    </div>

    <!-- 下载信息 -->
    <div class="mt-6 space-y-4">
        <h3 class="font-bold text-gray-700 border-b pb-2">下载信息</h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">下载来源</label>
            <input type="text" name="vod_down_from" value="<?= htmlspecialchars($vod['vod_down_from'] ?? '') ?>"
                class="w-full border rounded px-3 py-2" placeholder="多个来源用$$$分隔，如：迅雷$$$百度网盘">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">下载地址</label>
            <textarea name="vod_down_url" rows="3" class="w-full border rounded px-3 py-2"
                placeholder="格式：第1集$url#第2集$url&#10;多个来源用$$$分隔"><?= htmlspecialchars($vod['vod_down_url'] ?? '') ?></textarea>
            <p class="text-xs text-gray-400 mt-1">格式说明：集名$下载地址，多集用#分隔，多来源用$$$分隔</p>
        </div>
    </div>

    <!-- 简介 -->
    <div class="mt-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">简介</label>
        <div id="editor" style="height: 200px;"><?= $vod['vod_content'] ?? '' ?></div>
        <input type="hidden" name="vod_content" id="vod_content">
    </div>

    <!-- 提交按钮 -->
    <div class="mt-6 flex space-x-4">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
            保存
        </button>
        <a href="/admin.php/vod" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">
            返回
        </a>
    </div>
</form>

<!-- Quill 编辑器 -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
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
    document.getElementById('vod_content').value = quill.root.innerHTML;
    
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
                location.href = '/admin.php/vod';
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
</script>
