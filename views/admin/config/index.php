<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<h1 class="text-2xl font-bold mb-6">系统配置</h1>

<form method="POST" class="bg-white rounded-lg shadow p-6">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="space-y-6">
        <!-- 基本设置 -->
        <div>
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">基本设置</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">站点名称</label>
                    <input type="text" name="site_name" value="<?= htmlspecialchars($config['site_name'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">站点URL</label>
                    <input type="text" name="site_url" value="<?= htmlspecialchars($config['site_url'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" placeholder="https://example.com">
                </div>
            </div>
        </div>

        <!-- SEO设置 -->
        <div>
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">SEO设置</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">首页关键词</label>
                    <input type="text" name="site_keywords" value="<?= htmlspecialchars($config['site_keywords'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" placeholder="多个关键词用逗号分隔">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">首页描述 <span class="text-gray-400">(≤160字符)</span></label>
                    <textarea name="site_description" rows="3" maxlength="160" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($config['site_description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- URL规则 -->
        <div>
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">URL规则（伪静态）</h3>
            <div class="bg-gray-50 rounded p-3 mb-4 text-sm text-gray-600">
                <p class="font-medium mb-1">可用变量：</p>
                <p>{id}ID {sid}播放源 {nid}集数 {type}分类ID {page}页码</p>
                <p class="mt-1">示例：<code class="bg-gray-200 px-1">vod/{id}.html</code> → <code class="bg-gray-200 px-1">/vod/123.html</code></p>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL模式</label>
                    <select name="url_mode" class="w-full border rounded px-3 py-2">
                        <option value="1" <?= ($config['url_mode'] ?? '1') === '1' ? 'selected' : '' ?>>模式1：/vod/detail/123</option>
                        <option value="2" <?= ($config['url_mode'] ?? '1') === '2' ? 'selected' : '' ?>>模式2：/vod/123.html</option>
                        <option value="4" <?= ($config['url_mode'] ?? '1') === '4' ? 'selected' : '' ?>>模式4：/video/slug（推荐SEO）</option>
                        <option value="5" <?= ($config['url_mode'] ?? '1') === '5' ? 'selected' : '' ?>>模式5：/video/slug.html</option>
                        <option value="3" <?= ($config['url_mode'] ?? '1') === '3' ? 'selected' : '' ?>>模式3：自定义规则</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">模式4/5使用slug别名，需在内容编辑时填写slug字段</p>
                </div>
                <div id="custom-url-rules" class="space-y-3 <?= ($config['url_mode'] ?? '1') !== '3' ? 'hidden' : '' ?>">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">视频详情</label>
                        <input type="text" name="url_vod_detail" value="<?= htmlspecialchars($config['url_vod_detail'] ?? 'vod/{id}.html') ?>"
                            class="w-full border rounded px-3 py-2 text-sm" placeholder="vod/{id}.html">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">视频播放</label>
                        <input type="text" name="url_vod_play" value="<?= htmlspecialchars($config['url_vod_play'] ?? 'play/{id}-{sid}-{nid}.html') ?>"
                            class="w-full border rounded px-3 py-2 text-sm" placeholder="play/{id}-{sid}-{nid}.html">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">分类页</label>
                        <input type="text" name="url_type" value="<?= htmlspecialchars($config['url_type'] ?? 'type/{id}.html') ?>"
                            class="w-full border rounded px-3 py-2 text-sm" placeholder="type/{id}.html">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">演员详情</label>
                        <input type="text" name="url_actor_detail" value="<?= htmlspecialchars($config['url_actor_detail'] ?? 'actor/{id}.html') ?>"
                            class="w-full border rounded px-3 py-2 text-sm" placeholder="actor/{id}.html">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">文章详情</label>
                        <input type="text" name="url_art_detail" value="<?= htmlspecialchars($config['url_art_detail'] ?? 'art/{id}.html') ?>"
                            class="w-full border rounded px-3 py-2 text-sm" placeholder="art/{id}.html">
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO模板 -->
        <div>
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">SEO模板</h3>
            <div class="bg-gray-50 rounded p-3 mb-4 text-sm text-gray-600">
                <p class="font-medium mb-1">可用变量：</p>
                <p>{name}名称 {actor}演员 {type}分类 {year}年份 {area}地区 {sitename}站点名 {description}简介</p>
                <p class="mt-2 text-orange-600">⚠️ SEO标准：标题≤60字符，描述≤160字符</p>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">视频详情页 - 标题 <span class="text-gray-400">(≤60字符)</span></label>
                    <input type="text" name="seo_title_vod_detail" value="<?= htmlspecialchars($config['seo_title_vod_detail'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" maxlength="80" placeholder="示例：{name}在线观看 - {sitename}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">视频详情页 - 关键词</label>
                    <input type="text" name="seo_keywords_vod_detail" value="<?= htmlspecialchars($config['seo_keywords_vod_detail'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" placeholder="示例：{name},{actor},{type}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">视频详情页 - 描述 <span class="text-gray-400">(≤160字符)</span></label>
                    <input type="text" name="seo_description_vod_detail" value="<?= htmlspecialchars($config['seo_description_vod_detail'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" maxlength="200" placeholder="示例：{name}由{actor}主演，{description}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">分类页 - 标题 <span class="text-gray-400">(≤60字符)</span></label>
                    <input type="text" name="seo_title_type" value="<?= htmlspecialchars($config['seo_title_type'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" maxlength="80" placeholder="示例：{name}大全 - {sitename}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">分类页 - 关键词</label>
                    <input type="text" name="seo_keywords_type" value="<?= htmlspecialchars($config['seo_keywords_type'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" placeholder="示例：{name},{name}大全,最新{name}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">演员详情页 - 标题 <span class="text-gray-400">(≤60字符)</span></label>
                    <input type="text" name="seo_title_actor_detail" value="<?= htmlspecialchars($config['seo_title_actor_detail'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" maxlength="80" placeholder="示例：{name}个人资料 - {sitename}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">文章详情页 - 标题 <span class="text-gray-400">(≤60字符)</span></label>
                    <input type="text" name="seo_title_art_detail" value="<?= htmlspecialchars($config['seo_title_art_detail'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2" maxlength="80" placeholder="示例：{name} - {sitename}">
                </div>
            </div>
        </div>

        <!-- 其他设置 -->
        <div>
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">其他设置</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ICP备案号</label>
                    <input type="text" name="site_icp" value="<?= htmlspecialchars($config['site_icp'] ?? '') ?>"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">模板</label>
                    <select name="site_template" class="w-full border rounded px-3 py-2">
                        <option value="default" <?= ($config['site_template'] ?? 'default') === 'default' ? 'selected' : '' ?>>默认模板</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">网站Logo</label>
                <div class="flex items-center gap-4">
                    <div id="logoPreview" class="w-32 h-10 border rounded flex items-center justify-center bg-gray-50">
                        <?php if (!empty($config['site_logo'])): ?>
                        <img src="<?= htmlspecialchars($config['site_logo']) ?>" class="max-h-full max-w-full" alt="Logo">
                        <?php else: ?>
                        <span class="text-gray-400 text-sm">无Logo</span>
                        <?php endif; ?>
                    </div>
                    <input type="file" id="logoFile" accept="image/*" class="hidden" onchange="uploadLogo(this)">
                    <button type="button" onclick="document.getElementById('logoFile').click()" class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm">上传Logo</button>
                    <?php if (!empty($config['site_logo'])): ?>
                    <button type="button" onclick="removeLogo()" class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">删除</button>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="site_logo" id="siteLogo" value="<?= htmlspecialchars($config['site_logo'] ?? '') ?>">
                <p class="text-xs text-gray-500 mt-1">建议尺寸：高度40px，PNG/JPG格式，留空则显示文字Logo</p>
            </div>
        </div>

        <!-- 站点状态 -->
        <div>
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">站点状态</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">站点状态</label>
                    <select name="site_status" class="w-full border rounded px-3 py-2">
                        <option value="1" <?= ($config['site_status'] ?? '1') === '1' ? 'selected' : '' ?>>开启</option>
                        <option value="0" <?= ($config['site_status'] ?? '1') === '0' ? 'selected' : '' ?>>关闭</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">关闭提示</label>
                    <textarea name="site_close_tip" rows="2" class="w-full border rounded px-3 py-2" placeholder="站点关闭时显示的提示信息"><?= htmlspecialchars($config['site_close_tip'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- 用户设置 -->
        <div>
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">用户设置</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">开放注册</label>
                    <select name="user_register" class="w-full border rounded px-3 py-2">
                        <option value="1" <?= ($config['user_register'] ?? '1') === '1' ? 'selected' : '' ?>>开启</option>
                        <option value="0" <?= ($config['user_register'] ?? '1') === '0' ? 'selected' : '' ?>>关闭</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">每日注册限制（同IP）</label>
                    <input type="number" name="user_register_limit" value="<?= htmlspecialchars($config['user_register_limit'] ?? '5') ?>"
                        class="w-full border rounded px-3 py-2" min="0" max="100">
                    <p class="text-xs text-gray-500 mt-1">0表示不限制，建议设置3-10</p>
                </div>
            </div>
        </div>

        <!-- 存储/缓存状态 -->
        <div>
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">系统状态（只读）</h3>
            <div class="bg-gray-50 rounded p-4 text-sm">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <span class="text-gray-500">存储驱动:</span>
                        <span class="ml-2 font-medium <?= defined('STORAGE_DRIVER') && STORAGE_DRIVER === 'r2' ? 'text-green-600' : 'text-gray-700' ?>">
                            <?= defined('STORAGE_DRIVER') && STORAGE_DRIVER === 'r2' ? 'Cloudflare R2' : '本地存储' ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500">缓存驱动:</span>
                        <span class="ml-2 font-medium <?= defined('CACHE_DRIVER') && CACHE_DRIVER === 'redis' ? 'text-green-600' : 'text-gray-700' ?>">
                            <?= defined('CACHE_DRIVER') && CACHE_DRIVER === 'redis' ? 'Redis' : '文件缓存' ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500">Session:</span>
                        <span class="ml-2 font-medium <?= defined('SESSION_DRIVER') && SESSION_DRIVER === 'redis' ? 'text-green-600' : 'text-gray-700' ?>">
                            <?= defined('SESSION_DRIVER') && SESSION_DRIVER === 'redis' ? 'Redis' : '文件' ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500">调试模式:</span>
                        <span class="ml-2 font-medium <?= APP_DEBUG ? 'text-orange-600' : 'text-green-600' ?>">
                            <?= APP_DEBUG ? '开启' : '关闭' ?>
                        </span>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-3">以上配置需在 config/config.php 文件中修改</p>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">保存配置</button>
    </div>
</form>

<script>
document.querySelector('select[name="url_mode"]').addEventListener('change', function() {
    document.getElementById('custom-url-rules').classList.toggle('hidden', this.value !== '3');
});

function uploadLogo(input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    if (file.size > 2 * 1024 * 1024) {
        alert('文件大小不能超过2MB');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'logo');
    
    fetch('/admin.php/config/upload', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            document.getElementById('siteLogo').value = data.data.url;
            document.getElementById('logoPreview').innerHTML = '<img src="' + data.data.url + '" class="max-h-full max-w-full" alt="Logo">';
            alert('上传成功');
        } else {
            alert(data.msg || '上传失败');
        }
    })
    .catch(() => alert('上传失败'));
}

function removeLogo() {
    if (!confirm('确定删除Logo？')) return;
    document.getElementById('siteLogo').value = '';
    document.getElementById('logoPreview').innerHTML = '<span class="text-gray-400 text-sm">无Logo</span>';
}
</script>
