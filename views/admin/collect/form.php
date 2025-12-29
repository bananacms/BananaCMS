<h1 class="text-2xl font-bold mb-6"><?= isset($collect) ? '编辑采集站' : '添加采集站' ?></h1>

<form method="POST" class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="space-y-4">
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
            <p class="text-xs text-gray-400 mt-1">支持JSON和XML格式的资源站API</p>
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

    <div class="mt-6 flex space-x-4">
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
