<div class="mb-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="/<?= $adminEntry ?>?s=short/episodes/<?= $short['short_id'] ?>" class="text-gray-500 hover:text-gray-700">← 返回剧集列表</a>
        <h2 class="text-2xl font-bold"><?= isset($episode) ? '编辑剧集' : '添加剧集' ?></h2>
    </div>
    <p class="text-gray-500">短剧：<?= htmlspecialchars($short['short_name']) ?></p>
</div>

<form method="post" action="/<?= $adminEntry ?>?s=short/<?= isset($episode) ? 'doEditEpisode/' . $episode['episode_id'] : 'doAddEpisode/' . $short['short_id'] ?>" class="bg-white rounded shadow p-6 max-w-2xl">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">集数标题</label>
            <input type="text" name="episode_name" value="<?= htmlspecialchars($episode['episode_name'] ?? '') ?>" 
                   class="w-full border rounded px-3 py-2" placeholder="如：第1集、大结局">
            <p class="text-xs text-gray-400 mt-1">留空则显示为"第X集"</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">视频地址 <span class="text-red-500">*</span></label>
            <input type="text" name="episode_url" value="<?= htmlspecialchars($episode['episode_url'] ?? '') ?>" 
                   class="w-full border rounded px-3 py-2" placeholder="https://...mp4" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">封面图</label>
            <input type="text" name="episode_pic" value="<?= htmlspecialchars($episode['episode_pic'] ?? '') ?>" 
                   class="w-full border rounded px-3 py-2" placeholder="留空则使用短剧封面">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">时长（秒）</label>
                <input type="number" name="episode_duration" value="<?= $episode['episode_duration'] ?? 0 ?>" 
                       class="w-full border rounded px-3 py-2" min="0">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                <input type="number" name="episode_sort" value="<?= $episode['episode_sort'] ?? 0 ?>" 
                       class="w-full border rounded px-3 py-2" min="0">
                <p class="text-xs text-gray-400 mt-1">数字越小越靠前</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">是否免费</label>
            <select name="episode_free" class="border rounded px-3 py-2">
                <option value="1" <?= ($episode['episode_free'] ?? 1) == 1 ? 'selected' : '' ?>>免费观看</option>
                <option value="0" <?= ($episode['episode_free'] ?? 1) == 0 ? 'selected' : '' ?>>付费观看</option>
            </select>
        </div>
    </div>

    <div class="mt-6 pt-4 border-t flex gap-4">
        <button type="submit" class="bg-primary text-white px-6 py-2 rounded hover:bg-red-600">
            保存
        </button>
        <a href="/<?= $adminEntry ?>?s=short/episodes/<?= $short['short_id'] ?>" class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
            取消
        </a>
    </div>
</form>
