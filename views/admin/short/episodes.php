<div class="mb-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="/<?= $adminEntry ?>/short" class="text-gray-500 hover:text-gray-700">← 返回列表</a>
        <h2 class="text-2xl font-bold flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            剧集管理
        </h2>
    </div>

    <!-- 短剧信息 -->
    <div class="bg-white p-4 rounded shadow mb-4 flex gap-4">
        <?php if ($short['short_pic']): ?>
        <img src="<?= htmlspecialchars($short['short_pic']) ?>" class="w-20 h-28 object-cover rounded">
        <?php endif; ?>
        <div>
            <h3 class="text-lg font-bold"><?= htmlspecialchars($short['short_name']) ?></h3>
            <p class="text-gray-500 text-sm mt-1"><?= strip_tags($short['short_desc']) ?: '暂无简介' ?></p>
            <p class="text-gray-400 text-xs mt-2">共 <?= count($episodes) ?> 集</p>
        </div>
    </div>

    <div class="flex justify-between items-center mb-4">
        <span class="text-gray-500">剧集列表</span>
        <button onclick="openEpisodeModal()" class="bg-primary text-white px-4 py-2 rounded hover:bg-red-600">
            + 添加剧集
        </button>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="mb-4 p-4 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>
</div>

<!-- 剧集列表 -->
<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">排序</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">集数</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">标题</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">时长</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">播放量</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($episodes)): ?>
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">暂无剧集，点击上方按钮添加</td>
            </tr>
            <?php else: ?>
            <?php foreach ($episodes as $index => $ep): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-500"><?= $ep['episode_sort'] ?: ($index + 1) ?></td>
                <td class="px-4 py-3">
                    <span class="bg-gray-100 px-2 py-1 rounded text-sm">第 <?= $index + 1 ?> 集</span>
                </td>
                <td class="px-4 py-3">
                    <div class="font-medium"><?= htmlspecialchars($ep['episode_name'] ?: '第' . ($index + 1) . '集') ?></div>
                    <div class="text-xs text-gray-400 truncate max-w-xs"><?= htmlspecialchars($ep['episode_url']) ?></div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">
                    <?= $ep['episode_duration'] ? gmdate('i:s', $ep['episode_duration']) : '-' ?>
                </td>
                <td class="px-4 py-3 text-sm"><?= number_format($ep['episode_hits']) ?></td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <button onclick="openEpisodeModal(<?= htmlspecialchars(json_encode($ep)) ?>)" class="text-blue-600 hover:underline">编辑</button>
                    <button onclick="deleteItem(adminUrl('/short/deleteEpisode'), <?= $ep['episode_id'] ?>)" class="text-red-600 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4">
    <a href="/<?= $adminEntry ?>/short/edit/<?= $short['short_id'] ?>" class="text-gray-500 hover:text-gray-700">
        ← 编辑短剧信息
    </a>
</div>

<!-- 剧集模态框 -->
<div id="episodeModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="text-lg font-bold" id="modalTitle">添加剧集</h3>
            <button onclick="closeEpisodeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <form id="episodeForm" class="p-6 space-y-4">
            <input type="hidden" name="_token" value="<?= $csrfToken ?? '' ?>">
            <input type="hidden" name="episode_id" id="episode_id" value="">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">集数标题</label>
                <input type="text" name="episode_name" id="episode_name" class="w-full border rounded px-3 py-2" placeholder="如：第1集、大结局">
                <p class="text-xs text-gray-400 mt-1">留空则显示为"第X集"</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">视频地址 <span class="text-red-500">*</span></label>
                <input type="text" name="episode_url" id="episode_url" class="w-full border rounded px-3 py-2" placeholder="https://...mp4" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">封面图</label>
                <input type="text" name="episode_pic" id="episode_pic" class="w-full border rounded px-3 py-2" placeholder="留空则使用短剧封面">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">时长（秒）</label>
                    <input type="number" name="episode_duration" id="episode_duration" class="w-full border rounded px-3 py-2" min="0" value="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                    <input type="number" name="episode_sort" id="episode_sort" class="w-full border rounded px-3 py-2" min="0" value="0">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeEpisodeModal()" class="px-4 py-2 border rounded hover:bg-gray-50">取消</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-red-600">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
const shortId = <?= $short['short_id'] ?>;

function openEpisodeModal(episode = null) {
    const modal = document.getElementById('episodeModal');
    const title = document.getElementById('modalTitle');
    
    if (episode) {
        title.textContent = '编辑剧集';
        document.getElementById('episode_id').value = episode.episode_id;
        document.getElementById('episode_name').value = episode.episode_name || '';
        document.getElementById('episode_url').value = episode.episode_url || '';
        document.getElementById('episode_pic').value = episode.episode_pic || '';
        document.getElementById('episode_duration').value = episode.episode_duration || 0;
        document.getElementById('episode_sort').value = episode.episode_sort || 0;
        document.getElementById('episode_free').value = episode.episode_free;
    } else {
        title.textContent = '添加剧集';
        document.getElementById('episodeForm').reset();
        document.getElementById('episode_id').value = '';
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEpisodeModal() {
    const modal = document.getElementById('episodeModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// 点击遮罩关闭
document.getElementById('episodeModal').addEventListener('click', function(e) {
    if (e.target === this) closeEpisodeModal();
});

// 表单提交
document.getElementById('episodeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const episodeId = document.getElementById('episode_id').value;
    const url = episodeId 
        ? adminUrl('/short/doEditEpisode/' + episodeId)
        : adminUrl('/short/doAddEpisode/' + shortId);
    
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = '保存中...';
    
    fetch(url, {
        method: 'POST',
        body: new FormData(this)
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            xpkToast(data.msg, 'error');
            btn.disabled = false;
            btn.textContent = '保存';
        }
    })
    .catch(() => {
        xpkToast('请求失败', 'error');
        btn.disabled = false;
        btn.textContent = '保存';
    });
});
</script>
