<div class="mb-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="/<?= $adminEntry ?>/comment" class="text-gray-500 hover:text-gray-700">← 返回列表</a>
        <h2 class="text-2xl font-bold flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            评论设置
        </h2>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="mb-4 p-4 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>
</div>

<form method="post" action="/<?= $adminEntry ?>/comment/saveSetting" class="bg-white rounded shadow p-6 max-w-2xl">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">评论功能</label>
            <div class="flex gap-4">
                <label class="flex items-center">
                    <input type="radio" name="comment_enabled" value="1" <?= ($config['comment_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="mr-2">
                    开启
                </label>
                <label class="flex items-center">
                    <input type="radio" name="comment_enabled" value="0" <?= ($config['comment_enabled'] ?? '1') === '0' ? 'checked' : '' ?> class="mr-2">
                    关闭
                </label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">评论审核</label>
            <div class="flex gap-4">
                <label class="flex items-center">
                    <input type="radio" name="comment_audit" value="0" <?= ($config['comment_audit'] ?? '0') === '0' ? 'checked' : '' ?> class="mr-2">
                    无需审核
                </label>
                <label class="flex items-center">
                    <input type="radio" name="comment_audit" value="1" <?= ($config['comment_audit'] ?? '0') === '1' ? 'checked' : '' ?> class="mr-2">
                    先审后发
                </label>
            </div>
            <p class="text-xs text-gray-400 mt-1">包含敏感词的评论会强制进入审核</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">游客评论</label>
            <div class="flex gap-4">
                <label class="flex items-center">
                    <input type="radio" name="comment_guest" value="0" <?= ($config['comment_guest'] ?? '0') === '0' ? 'checked' : '' ?> class="mr-2">
                    需要登录
                </label>
                <label class="flex items-center">
                    <input type="radio" name="comment_guest" value="1" <?= ($config['comment_guest'] ?? '0') === '1' ? 'checked' : '' ?> class="mr-2">
                    允许游客
                </label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">发言间隔（秒）</label>
            <input type="number" name="comment_interval" value="<?= $config['comment_interval'] ?? 60 ?>" 
                   class="w-32 border rounded px-3 py-2" min="0">
            <p class="text-xs text-gray-400 mt-1">0 表示不限制，建议设置 30-60 秒防止刷屏</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">最少字数</label>
                <input type="number" name="comment_min_length" value="<?= $config['comment_min_length'] ?? 1 ?>" 
                       class="w-full border rounded px-3 py-2" min="1">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">最多字数</label>
                <input type="number" name="comment_max_length" value="<?= $config['comment_max_length'] ?? 500 ?>" 
                       class="w-full border rounded px-3 py-2" min="1">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">敏感词过滤</label>
            <textarea name="comment_sensitive_words" rows="6" class="w-full border rounded px-3 py-2"
                      placeholder="每行一个敏感词..."><?= htmlspecialchars($config['comment_sensitive_words'] ?? '') ?></textarea>
            <p class="text-xs text-gray-400 mt-1">每行一个敏感词，匹配到的词会被替换为 ***</p>
        </div>
    </div>

    <div class="mt-6 pt-4 border-t">
        <button type="submit" class="bg-primary text-white px-6 py-2 rounded hover:bg-red-600">
            保存设置
        </button>
    </div>
</form>
