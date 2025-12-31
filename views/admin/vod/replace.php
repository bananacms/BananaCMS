<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">播放地址替换</h1>
    <div class="flex gap-2">
        <a href="/admin.php/vod/sources" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
            📺 播放源管理
        </a>
        <a href="/admin.php/vod" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            ← 返回视频列表
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- 域名替换 -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-bold text-lg mb-4">🔄 域名/地址替换</h3>
        <p class="text-sm text-gray-500 mb-4">批量替换播放地址中的域名或任意字符串</p>
        
        <form id="replaceForm" class="space-y-4">
            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">替换范围</label>
                <select name="field" class="w-full border rounded px-3 py-2">
                    <option value="play">仅播放地址</option>
                    <option value="down">仅下载地址</option>
                    <option value="both">播放+下载地址</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">指定播放源（可选）</label>
                <select name="play_from" class="w-full border rounded px-3 py-2">
                    <option value="">全部播放源</option>
                    <?php foreach ($playFromList as $from): ?>
                    <option value="<?= htmlspecialchars($from) ?>"><?= htmlspecialchars($from) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">留空则替换所有播放源的地址</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">查找内容 <span class="text-red-500">*</span></label>
                <input type="text" name="old_str" class="w-full border rounded px-3 py-2" placeholder="例如：http://old-domain.com" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">替换为</label>
                <input type="text" name="new_str" class="w-full border rounded px-3 py-2" placeholder="例如：http://new-domain.com">
                <p class="text-xs text-gray-500 mt-1">留空则删除匹配的内容</p>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded p-3 text-sm text-yellow-800">
                <p class="font-medium">⚠️ 注意事项：</p>
                <ul class="list-disc list-inside mt-1 space-y-1">
                    <li>此操作不可撤销，请先备份数据库</li>
                    <li>替换区分大小写</li>
                    <li>建议先在少量数据上测试</li>
                </ul>
            </div>

            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-bold">
                🔄 执行替换
            </button>
        </form>
    </div>

    <!-- 常用替换示例 -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-bold text-lg mb-4">📋 常用替换示例</h3>
        
        <div class="space-y-4">
            <div class="border rounded p-4">
                <h4 class="font-medium text-gray-700 mb-2">域名更换</h4>
                <p class="text-sm text-gray-500 mb-2">资源站域名变更时使用</p>
                <div class="bg-gray-50 rounded p-2 text-sm font-mono">
                    <p>查找：<span class="text-red-600">http://old.com</span></p>
                    <p>替换：<span class="text-green-600">http://new.com</span></p>
                </div>
                <button type="button" onclick="fillExample('http://old.com', 'http://new.com')" class="mt-2 text-blue-500 text-sm hover:underline">使用此示例</button>
            </div>

            <div class="border rounded p-4">
                <h4 class="font-medium text-gray-700 mb-2">HTTP 转 HTTPS</h4>
                <p class="text-sm text-gray-500 mb-2">升级为安全连接</p>
                <div class="bg-gray-50 rounded p-2 text-sm font-mono">
                    <p>查找：<span class="text-red-600">http://</span></p>
                    <p>替换：<span class="text-green-600">https://</span></p>
                </div>
                <button type="button" onclick="fillExample('http://', 'https://')" class="mt-2 text-blue-500 text-sm hover:underline">使用此示例</button>
            </div>

            <div class="border rounded p-4">
                <h4 class="font-medium text-gray-700 mb-2">路径替换</h4>
                <p class="text-sm text-gray-500 mb-2">资源路径变更时使用</p>
                <div class="bg-gray-50 rounded p-2 text-sm font-mono">
                    <p>查找：<span class="text-red-600">/old-path/</span></p>
                    <p>替换：<span class="text-green-600">/new-path/</span></p>
                </div>
                <button type="button" onclick="fillExample('/old-path/', '/new-path/')" class="mt-2 text-blue-500 text-sm hover:underline">使用此示例</button>
            </div>

            <div class="border rounded p-4">
                <h4 class="font-medium text-gray-700 mb-2">删除无效参数</h4>
                <p class="text-sm text-gray-500 mb-2">清理地址中的无效参数</p>
                <div class="bg-gray-50 rounded p-2 text-sm font-mono">
                    <p>查找：<span class="text-red-600">?token=xxx</span></p>
                    <p>替换：<span class="text-gray-400">（留空）</span></p>
                </div>
                <button type="button" onclick="fillExample('?token=xxx', '')" class="mt-2 text-blue-500 text-sm hover:underline">使用此示例</button>
            </div>
        </div>

        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded">
            <h4 class="font-medium text-blue-800 mb-2">💡 提示</h4>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• 如需管理播放源（重命名/删除），请点击"播放源管理"</li>
                <li>• 替换操作会直接修改数据库，建议先备份</li>
                <li>• 大量数据替换可能需要一些时间</li>
            </ul>
        </div>
    </div>
</div>

<script>
function fillExample(oldStr, newStr) {
    document.querySelector('input[name="old_str"]').value = oldStr;
    document.querySelector('input[name="new_str"]').value = newStr;
}

document.getElementById('replaceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const oldStr = this.querySelector('input[name="old_str"]').value;
    const newStr = this.querySelector('input[name="new_str"]').value;
    
    xpkConfirm(`确定要将 "${oldStr}" 替换为 "${newStr || '(空)'}" 吗？\n\n此操作不可撤销！`, () => {
        const formData = new FormData(this);
        
        fetch('/admin.php/vod/replace', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
            } else {
                xpkToast(data.msg || '替换失败', 'error');
            }
        })
        .catch(() => xpkToast('请求失败', 'error'));
    });
});
</script>
