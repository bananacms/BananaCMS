<h1 class="text-2xl font-bold mb-6"><?= isset($collect) ? '编辑采集站' : '添加采集站' ?></h1>

<form method="POST" class="bg-white rounded-lg shadow p-6">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 左侧：基本设置 -->
        <div class="space-y-4">
            <h3 class="font-bold text-gray-700 border-b pb-2">基本设置</h3>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">名称 *</label>
                <input type="text" name="collect_name" value="<?= htmlspecialchars($collect['collect_name'] ?? '') ?>" required
                    class="w-full border rounded px-3 py-2" placeholder="如：淘片资源">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">API地址 *</label>
                <div class="flex gap-2">
                    <input type="text" name="collect_api" id="apiUrl" value="<?= htmlspecialchars($collect['collect_api'] ?? '') ?>" required
                        class="flex-1 border rounded px-3 py-2" placeholder="https://example.com/api.php/provide/vod/">
                    <button type="button" onclick="testApi()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">测试</button>
                </div>
            </div>

            <div id="testResult" class="hidden"></div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">数据格式</label>
                    <select name="collect_type" class="w-full border rounded px-3 py-2">
                        <option value="json" <?= ($collect['collect_type'] ?? 'json') === 'json' ? 'selected' : '' ?>>JSON</option>
                        <option value="xml" <?= ($collect['collect_type'] ?? '') === 'xml' ? 'selected' : '' ?>>XML</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                    <select name="collect_status" class="w-full border rounded px-3 py-2">
                        <option value="1" <?= ($collect['collect_status'] ?? 1) == 1 ? 'selected' : '' ?>>启用</option>
                        <option value="0" <?= ($collect['collect_status'] ?? 1) == 0 ? 'selected' : '' ?>>禁用</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">过滤关键词</label>
                <input type="text" name="collect_filter" value="<?= htmlspecialchars($collect['collect_filter'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="多个用逗号分隔，如：伦理,福利">
                <p class="text-xs text-gray-400 mt-1">包含这些关键词的视频将被跳过</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">附加参数</label>
                <input type="text" name="collect_param" value="<?= htmlspecialchars($collect['collect_param'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="如：&key=xxx">
            </div>
        </div>

        <!-- 右侧：采集选项 -->
        <div class="space-y-4">
            <h3 class="font-bold text-gray-700 border-b pb-2">采集选项</h3>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">随机点击量</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="collect_opt_hits_start" value="<?= $collect['collect_opt_hits_start'] ?? 0 ?>"
                        class="w-24 border rounded px-3 py-2" min="0" placeholder="起始">
                    <span class="text-gray-500">~</span>
                    <input type="number" name="collect_opt_hits_end" value="<?= $collect['collect_opt_hits_end'] ?? 0 ?>"
                        class="w-24 border rounded px-3 py-2" min="0" placeholder="结束">
                </div>
                <p class="text-xs text-gray-400 mt-1">新增视频时随机生成点击量，0表示不生成</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">随机评分</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="collect_opt_score_start" value="<?= $collect['collect_opt_score_start'] ?? 0 ?>"
                        class="w-24 border rounded px-3 py-2" min="0" max="10" step="0.1" placeholder="起始">
                    <span class="text-gray-500">~</span>
                    <input type="number" name="collect_opt_score_end" value="<?= $collect['collect_opt_score_end'] ?? 0 ?>"
                        class="w-24 border rounded px-3 py-2" min="0" max="10" step="0.1" placeholder="结束">
                </div>
                <p class="text-xs text-gray-400 mt-1">新增视频时随机生成评分(1-10)，0表示使用资源站评分</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">重复判断规则</label>
                <select name="collect_opt_dup_rule" class="w-full border rounded px-3 py-2">
                    <option value="name" <?= ($collect['collect_opt_dup_rule'] ?? 'name') === 'name' ? 'selected' : '' ?>>仅按名称</option>
                    <option value="name_type" <?= ($collect['collect_opt_dup_rule'] ?? '') === 'name_type' ? 'selected' : '' ?>>名称 + 分类</option>
                    <option value="name_year" <?= ($collect['collect_opt_dup_rule'] ?? '') === 'name_year' ? 'selected' : '' ?>>名称 + 年份</option>
                    <option value="name_type_year" <?= ($collect['collect_opt_dup_rule'] ?? '') === 'name_type_year' ? 'selected' : '' ?>>名称 + 分类 + 年份</option>
                </select>
                <p class="text-xs text-gray-400 mt-1">判断视频是否已存在的规则</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">允许更新的字段</label>
                <div class="space-y-2">
                    <?php 
                    $updateFields = explode(',', $collect['collect_opt_update_fields'] ?? 'remarks,content,play');
                    $fieldOptions = [
                        'remarks' => '备注/集数',
                        'content' => '简介内容',
                        'play' => '播放地址',
                        'down' => '下载地址',
                        'pic' => '封面图片',
                        'actor' => '演员',
                        'director' => '导演',
                        'score' => '评分',
                        'extend' => '扩展字段(标签/状态/集数等)'
                    ];
                    ?>
                    <?php foreach ($fieldOptions as $field => $label): ?>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="collect_opt_update_fields[]" value="<?= $field ?>"
                            <?= in_array($field, $updateFields) ? 'checked' : '' ?> class="w-4 h-4 rounded">
                        <span class="text-sm"><?= $label ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <p class="text-xs text-gray-400 mt-1">更新已有视频时，只更新勾选的字段</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">播放地址处理</label>
                <select name="collect_opt_play_merge" class="w-full border rounded px-3 py-2">
                    <option value="0" <?= ($collect['collect_opt_play_merge'] ?? 0) == 0 ? 'selected' : '' ?>>覆盖 - 用新地址替换旧地址</option>
                    <option value="1" <?= ($collect['collect_opt_play_merge'] ?? 0) == 1 ? 'selected' : '' ?>>合并 - 新旧地址合并(按播放源)</option>
                </select>
                <p class="text-xs text-gray-400 mt-1">更新时如何处理播放地址</p>
            </div>
        </div>
    </div>

    <div class="mt-6 flex space-x-4 border-t pt-6">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">保存</button>
        <a href="/admin.php/collect" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">返回</a>
    </div>
</form>

<script>
function testApi() {
    const api = document.getElementById('apiUrl').value;
    const result = document.getElementById('testResult');
    
    if (!api) {
        result.className = 'bg-red-100 text-red-700 px-4 py-3 rounded';
        result.textContent = 'API地址不能为空';
        return;
    }
    
    result.className = 'bg-blue-100 text-blue-700 px-4 py-3 rounded';
    result.textContent = '测试中...';
    
    fetch('/admin.php/collect/test', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'api=' + encodeURIComponent(api)
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            const cats = data.data.categories;
            result.className = 'bg-green-100 text-green-700 px-4 py-3 rounded';
            result.innerHTML = '✓ 连接成功，获取到 ' + cats.length + ' 个分类：<br><span class="text-sm">' + 
                cats.map(c => c.name).join('、') + '</span>';
        } else {
            result.className = 'bg-red-100 text-red-700 px-4 py-3 rounded';
            result.textContent = '✗ ' + data.msg;
        }
    })
    .catch(() => {
        result.className = 'bg-red-100 text-red-700 px-4 py-3 rounded';
        result.textContent = '✗ 请求失败';
    });
}
</script>
