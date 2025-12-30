<h1 class="text-2xl font-bold mb-6"><?= isset($player) ? '编辑播放器' : '添加播放器' ?></h1>

<form method="POST" class="bg-white rounded-lg shadow p-6">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">播放器名称 *</label>
                <input type="text" name="player_name" value="<?= htmlspecialchars($player['player_name'] ?? '') ?>" required
                    class="w-full border rounded px-3 py-2" placeholder="如：量子云播">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">播放器标识 *</label>
                <input type="text" name="player_code" value="<?= htmlspecialchars($player['player_code'] ?? '') ?>" required
                    class="w-full border rounded px-3 py-2" placeholder="如：lzm3u8"
                    <?= isset($player) ? '' : '' ?>>
                <p class="text-xs text-gray-500 mt-1">需与资源站返回的 vod_play_from 值一致，只能包含字母、数字和下划线</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">解析接口</label>
            <input type="text" name="player_parse" value="<?= htmlspecialchars($player['player_parse'] ?? '') ?>"
                class="w-full border rounded px-3 py-2" placeholder="如：https://jx.example.com/?url=">
            <p class="text-xs text-gray-500 mt-1">需要解析的播放源填写解析接口地址，直链播放留空</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                <input type="number" name="player_sort" value="<?= $player['player_sort'] ?? 0 ?>"
                    class="w-full border rounded px-3 py-2" min="0">
                <p class="text-xs text-gray-500 mt-1">数字越小越靠前</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                <select name="player_status" class="w-full border rounded px-3 py-2">
                    <option value="1" <?= ($player['player_status'] ?? 1) == 1 ? 'selected' : '' ?>>启用</option>
                    <option value="0" <?= ($player['player_status'] ?? 1) == 0 ? 'selected' : '' ?>>禁用</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">备注说明</label>
            <input type="text" name="player_tip" value="<?= htmlspecialchars($player['player_tip'] ?? '') ?>"
                class="w-full border rounded px-3 py-2" placeholder="可选，用于备注说明">
        </div>
    </div>

    <div class="mt-6 flex space-x-4">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">保存</button>
        <a href="/admin.php/player" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">返回</a>
    </div>
</form>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    
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
                location.href = '/admin.php/player';
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
