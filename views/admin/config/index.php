<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<h1 class="text-2xl font-bold mb-6 flex justify-between items-center">
    系统配置
    <a href="/<?= $adminEntry ?>?s=config/security" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 text-sm">
        🔒 安全配置
    </a>
</h1>

<form method="POST" action="/<?= $adminEntry ?>?s=config/save" class="bg-white rounded-lg shadow p-6">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <!-- 顶部固定保存按钮 -->
    <div class="sticky top-0 bg-white py-3 mb-4 border-b -mx-6 px-6 z-10 flex justify-between items-center">
        <span class="text-gray-500 text-sm">修改配置后请点击保存</span>
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded shadow flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            保存配置
        </button>
    </div>

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
                        <option value="4" <?= ($config['url_mode'] ?? '4') === '4' ? 'selected' : '' ?>>模式4：/video/slug（推荐SEO）</option>
                        <option value="5" <?= ($config['url_mode'] ?? '4') === '5' ? 'selected' : '' ?>>模式5：/video/slug.html</option>
                        <option value="1" <?= ($config['url_mode'] ?? '4') === '1' ? 'selected' : '' ?>>模式1：/vod/detail/123</option>
                        <option value="2" <?= ($config['url_mode'] ?? '4') === '2' ? 'selected' : '' ?>>模式2：/vod/123.html</option>
                        <option value="3" <?= ($config['url_mode'] ?? '4') === '3' ? 'selected' : '' ?>>模式3：自定义规则</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">模式4/5使用slug别名，需在内容编辑时填写slug字段</p>
                </div>
                <div id="custom-url-rules" class="space-y-3 <?= ($config['url_mode'] ?? '4') !== '3' ? 'hidden' : '' ?>">
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
                <p class="mt-2 text-orange-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    SEO标准：标题≤60字符，描述≤160字符
                </p>
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
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">统计代码</label>
                    <textarea name="site_analytics" rows="4" class="w-full border rounded px-3 py-2 font-mono text-sm" placeholder="粘贴第三方统计代码，如 Google Analytics、百度统计等"><?= htmlspecialchars($config['site_analytics'] ?? '') ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">支持 JS 代码，将输出在页面底部 &lt;/body&gt; 前</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">模板</label>
                    <select name="site_template" class="w-full border rounded px-3 py-2">
                        <?php foreach ($templates as $tpl): ?>
                        <option value="<?= htmlspecialchars($tpl['name']) ?>" <?= ($config['site_template'] ?? 'default') === $tpl['name'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tpl['name']) ?><?= !$tpl['valid'] ? ' (无效)' : '' ?>
                        </option>
                        <?php endforeach; ?>
                        <?php if (empty($templates)): ?>
                        <option value="default">默认模板</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- 模板管理 -->
        <div>
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">模板管理</h3>
            <div class="mb-4">
                <div class="flex items-center gap-4">
                    <input type="file" id="templateFile" accept=".zip" class="hidden" onchange="uploadTemplate(this)">
                    <button type="button" onclick="document.getElementById('templateFile').click()" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                        上传模板 (ZIP)
                    </button>
                    <span class="text-sm text-gray-500">支持ZIP格式，最大50MB</span>
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left">模板名称</th>
                            <th class="px-4 py-2 text-left">状态</th>
                            <th class="px-4 py-2 text-left">大小</th>
                            <th class="px-4 py-2 text-left">修改时间</th>
                            <th class="px-4 py-2 text-left">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($templates as $tpl): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="px-4 py-2 font-medium">
                                <?= htmlspecialchars($tpl['name']) ?>
                                <?php if (($config['site_template'] ?? 'default') === $tpl['name']): ?>
                                <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded">使用中</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2">
                                <?php if ($tpl['valid']): ?>
                                <span class="text-green-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    有效
                                </span>
                                <?php else: ?>
                                <span class="text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    无效
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2 text-gray-500">
                                <?= number_format($tpl['size'] / 1024, 1) ?> KB
                            </td>
                            <td class="px-4 py-2 text-gray-500">
                                <?= date('Y-m-d H:i', $tpl['mtime']) ?>
                            </td>
                            <td class="px-4 py-2">
                                <?php if ($tpl['name'] !== 'default' && ($config['site_template'] ?? 'default') !== $tpl['name']): ?>
                                <button type="button" onclick="deleteTemplate('<?= htmlspecialchars($tpl['name']) ?>')" class="text-red-500 hover:text-red-700">删除</button>
                                <?php elseif ($tpl['name'] === 'default'): ?>
                                <span class="text-gray-400">默认</span>
                                <?php else: ?>
                                <span class="text-gray-400">使用中</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($templates)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">暂无模板</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-500 mt-2">模板目录：template/，上传的ZIP文件会自动解压到该目录</p>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">调试模式</label>
                    <select name="app_debug" class="w-full border rounded px-3 py-2">
                        <option value="1" <?= (($config['app_debug'] ?? (APP_DEBUG ? '1' : '0')) == '1') ? 'selected' : '' ?>>开启</option>
                        <option value="0" <?= (($config['app_debug'] ?? (APP_DEBUG ? '1' : '0')) == '0') ? 'selected' : '' ?>>关闭</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">开启后显示详细错误信息，生产环境建议关闭</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">站点状态</label>
                    <select name="site_status" class="w-full border rounded px-3 py-2">
                        <option value="1" <?= (($config['site_status'] ?? '1') == '1') ? 'selected' : '' ?>>开启</option>
                        <option value="0" <?= (($config['site_status'] ?? '1') == '0') ? 'selected' : '' ?>>关闭</option>
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
                        <option value="1" <?= (($config['user_register'] ?? '1') == '1') ? 'selected' : '' ?>>开启</option>
                        <option value="0" <?= (($config['user_register'] ?? '1') == '0') ? 'selected' : '' ?>>关闭</option>
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

        <!-- 导航设置 -->
        <div>
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">导航设置</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">导航分类显示数量</label>
                    <input type="number" name="nav_type_limit" value="<?= htmlspecialchars($config['nav_type_limit'] ?? '10') ?>"
                        class="w-full border rounded px-3 py-2" min="0" max="50">
                    <p class="text-xs text-gray-500 mt-1">0表示显示全部顶级分类</p>
                </div>
            </div>
        </div>

        <!-- Redis 缓存配置 -->
        <div>
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">Redis 缓存配置</h3>
            <?php
            // 检测 Redis 扩展
            $redisExtLoaded = extension_loaded('redis');
            // 当前配置
            $cacheDriver = defined('CACHE_DRIVER') ? CACHE_DRIVER : 'file';
            $sessionDriver = defined('SESSION_DRIVER') ? SESSION_DRIVER : 'file';
            $redisHost = defined('REDIS_HOST') ? REDIS_HOST : '127.0.0.1';
            $redisPort = defined('REDIS_PORT') ? REDIS_PORT : 6379;
            $redisPass = defined('REDIS_PASS') ? REDIS_PASS : '';
            $redisDb = defined('REDIS_DB') ? REDIS_DB : 0;
            $redisSessionDb = defined('REDIS_SESSION_DB') ? REDIS_SESSION_DB : 1;
            $redisPrefix = defined('REDIS_PREFIX') ? REDIS_PREFIX : 'xpk:';
            ?>
            
            <?php if (!$redisExtLoaded): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-yellow-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800">Redis 扩展未安装</h4>
                        <p class="text-sm text-yellow-700 mt-1">请先安装 PHP Redis 扩展才能使用 Redis 缓存功能</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">缓存驱动</label>
                    <select name="cache_driver" class="w-full border rounded px-3 py-2">
                        <option value="file" <?= $cacheDriver === 'file' ? 'selected' : '' ?>>文件缓存</option>
                        <option value="redis" <?= $cacheDriver === 'redis' ? 'selected' : '' ?> <?= !$redisExtLoaded ? 'disabled' : '' ?>>Redis<?= !$redisExtLoaded ? '（未安装扩展）' : '' ?></option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Session 驱动</label>
                    <select name="session_driver" class="w-full border rounded px-3 py-2">
                        <option value="file" <?= $sessionDriver === 'file' ? 'selected' : '' ?>>文件</option>
                        <option value="redis" <?= $sessionDriver === 'redis' ? 'selected' : '' ?> <?= !$redisExtLoaded ? 'disabled' : '' ?>>Redis<?= !$redisExtLoaded ? '（未安装扩展）' : '' ?></option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4 p-4 bg-gray-50 rounded-lg" id="redisConfig">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Redis 连接配置</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">主机地址</label>
                        <input type="text" name="redis_host" value="<?= htmlspecialchars($redisHost) ?>"
                            class="w-full border rounded px-3 py-2" placeholder="127.0.0.1">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">端口</label>
                        <input type="number" name="redis_port" value="<?= htmlspecialchars($redisPort) ?>"
                            class="w-full border rounded px-3 py-2" placeholder="6379">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">密码</label>
                        <input type="password" name="redis_pass" value="<?= htmlspecialchars($redisPass) ?>"
                            class="w-full border rounded px-3 py-2" placeholder="无密码留空">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">缓存数据库</label>
                        <input type="number" name="redis_db" value="<?= htmlspecialchars($redisDb) ?>"
                            class="w-full border rounded px-3 py-2" min="0" max="15" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Session 数据库</label>
                        <input type="number" name="redis_session_db" value="<?= htmlspecialchars($redisSessionDb) ?>"
                            class="w-full border rounded px-3 py-2" min="0" max="15" placeholder="1">
                        <p class="text-xs text-gray-500 mt-1">建议与缓存分开</p>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">键前缀</label>
                        <input type="text" name="redis_prefix" value="<?= htmlspecialchars($redisPrefix) ?>"
                            class="w-full border rounded px-3 py-2" placeholder="xpk:">
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="button" onclick="testRedis()" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm" <?= !$redisExtLoaded ? 'disabled' : '' ?>>
                        测试连接
                    </button>
                    <span id="redisTestResult" class="text-sm leading-8"></span>
                </div>
            </div>
            
            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
                <p class="text-blue-800 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    修改 Redis 配置后需要重启 PHP 服务才能生效（配置会写入 config/config.php）
                </p>
            </div>
        </div>

        <!-- Sitemap 站点地图 -->
        <div>
            <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">Sitemap 站点地图</h3>
            <?php
            $siteUrl = rtrim($config['site_url'] ?? SITE_URL, '/');
            $sitemapUrl = $siteUrl . '/sitemap.xml';
            ?>
            <div class="bg-gray-50 rounded p-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="font-medium text-gray-700">Sitemap 地址</p>
                        <p class="text-sm text-gray-500 mt-1">提交此地址到搜索引擎（Google/Bing/百度）</p>
                    </div>
                    <button type="button" onclick="checkSitemap()" class="px-3 py-1.5 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm">
                        检测状态
                    </button>
                </div>
                <div class="flex items-center gap-2 bg-white border rounded p-3">
                    <code id="sitemapUrl" class="flex-1 text-sm text-blue-600"><?= htmlspecialchars($sitemapUrl) ?></code>
                    <button type="button" onclick="copySitemapUrl()" class="px-2 py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200 text-xs">复制</button>
                    <a href="<?= htmlspecialchars($sitemapUrl) ?>" target="_blank" class="px-2 py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200 text-xs">打开</a>
                </div>
                <div id="sitemapStatus" class="mt-3 hidden">
                    <div class="text-sm"></div>
                </div>
                <div class="mt-4 text-sm text-gray-600">
                    <p class="font-medium mb-2 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        分片说明：
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-gray-500">
                        <li><code class="bg-gray-200 px-1 rounded">/sitemap.xml</code> - 索引文件（自动列出所有分片）</li>
                        <li><code class="bg-gray-200 px-1 rounded">/sitemap.xml?type=main</code> - 首页、分类页</li>
                        <li><code class="bg-gray-200 px-1 rounded">/sitemap.xml?type=vod&page=1</code> - 视频分片（每片5000条）</li>
                        <li><code class="bg-gray-200 px-1 rounded">/sitemap.xml?type=actor&page=1</code> - 演员分片</li>
                        <li><code class="bg-gray-200 px-1 rounded">/sitemap.xml?type=art&page=1</code> - 文章分片</li>
                    </ul>
                </div>
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm">
                    <p class="text-yellow-800 font-medium flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        注意事项：
                    </p>
                    <ul class="list-disc list-inside mt-1 text-yellow-700 space-y-1">
                        <li>必须配置伪静态才能访问 /sitemap.xml（见 伪静态/ 目录）</li>
                        <li>提交给搜索引擎时使用 <strong>/sitemap.xml</strong>，不要用 sitemap.php</li>
                        <li>Sitemap 会自动根据数据量分片，无需手动生成</li>
                    </ul>
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
                        <?php $debugOn = xpk_config('app_debug', APP_DEBUG); ?>
                        <span class="ml-2 font-medium <?= $debugOn ? 'text-orange-600' : 'text-green-600' ?>">
                            <?= $debugOn ? '开启' : '关闭' ?>
                        </span>
                    </div>
                </div>
                
                <!-- 上传相关配置 -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="font-medium text-gray-700 mb-2">上传配置：</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <span class="text-gray-500">upload目录:</span>
                            <?php $uploadWritable = is_writable(UPLOAD_PATH); ?>
                            <span class="ml-2 font-medium <?= $uploadWritable ? 'text-green-600' : 'text-red-600' ?>">
                                <?= $uploadWritable ? '可写' : '不可写' ?>
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500">上传限制:</span>
                            <span class="ml-2 font-medium"><?= ini_get('upload_max_filesize') ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500">POST限制:</span>
                            <span class="ml-2 font-medium"><?= ini_get('post_max_size') ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500">finfo扩展:</span>
                            <span class="ml-2 font-medium <?= function_exists('finfo_open') ? 'text-green-600' : 'text-yellow-600' ?>">
                                <?= function_exists('finfo_open') ? '已启用' : '未启用' ?>
                            </span>
                        </div>
                    </div>
                    <?php if (!$uploadWritable): ?>
                    <p class="mt-2 text-red-600 text-xs flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        upload目录不可写，请设置权限为755或777
                    </p>
                    <?php endif; ?>
                </div>
                
                <p class="text-xs text-gray-400 mt-3">以上配置需在 config/config.php 或 php.ini 中修改</p>
            </div>
        </div>
    </div>

    <div class="mt-6 flex justify-end">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded shadow flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            保存配置
        </button>
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
        xpkToast('文件大小不能超过2MB', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'logo');
    formData.append('_token', '<?= $csrfToken ?>');
    
    fetch(adminUrl('/config/upload'), {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            document.getElementById('siteLogo').value = data.data.url;
            document.getElementById('logoPreview').innerHTML = '<img src="' + data.data.url + '" class="max-h-full max-w-full" alt="Logo">';
            xpkToast('上传成功', 'success');
        } else {
            xpkToast(data.msg || '上传失败', 'error');
        }
    })
    .catch(() => xpkToast('上传失败', 'error'));
}

function removeLogo() {
    xpkConfirm('确定删除Logo？', function() {
        document.getElementById('siteLogo').value = '';
        document.getElementById('logoPreview').innerHTML = '<span class="text-gray-400 text-sm">无Logo</span>';
    });
}

function uploadTemplate(input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    if (file.size > 50 * 1024 * 1024) {
        xpkToast('文件大小不能超过50MB', 'error');
        return;
    }
    
    if (!file.name.toLowerCase().endsWith('.zip')) {
        xpkToast('只支持ZIP格式', 'error');
        return;
    }
    
    xpkToast('正在上传模板...', 'info');
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', '<?= $csrfToken ?>');
    
    fetch(adminUrl('/config/uploadTemplate'), {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast('模板上传成功：' + data.data.name, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            xpkToast(data.msg || '上传失败', 'error');
        }
    })
    .catch(() => xpkToast('上传失败', 'error'))
    .finally(() => {
        input.value = '';
    });
}

function deleteTemplate(name) {
    xpkConfirm('确定删除模板 "' + name + '"？此操作不可恢复！', function() {
        fetch(adminUrl('/config/deleteTemplate'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=<?= $csrfToken ?>&name=' + encodeURIComponent(name)
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast('删除成功', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                xpkToast(data.msg || '删除失败', 'error');
            }
        })
        .catch(() => xpkToast('删除失败', 'error'));
    });
}

function copySitemapUrl() {
    const url = document.getElementById('sitemapUrl').textContent;
    navigator.clipboard.writeText(url).then(() => {
        xpkToast('已复制到剪贴板', 'success');
    }).catch(() => {
        // 降级方案
        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        xpkToast('已复制到剪贴板', 'success');
    });
}

function checkSitemap() {
    const statusDiv = document.getElementById('sitemapStatus');
    const statusContent = statusDiv.querySelector('div');
    statusDiv.classList.remove('hidden');
    statusContent.innerHTML = '<span class="text-gray-500">检测中...</span>';
    
    const sitemapUrl = document.getElementById('sitemapUrl').textContent;
    
    fetch(sitemapUrl, { method: 'HEAD' })
        .then(response => {
            if (response.ok) {
                const contentType = response.headers.get('content-type') || '';
                if (contentType.includes('xml')) {
                    statusContent.innerHTML = '<span class="text-green-600 flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Sitemap 正常工作！Content-Type: ' + contentType + '</span>';
                } else {
                    statusContent.innerHTML = '<span class="text-yellow-600 flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>可访问但Content-Type不是XML: ' + contentType + '</span>';
                }
            } else {
                statusContent.innerHTML = '<span class="text-red-600 flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>无法访问 (HTTP ' + response.status + ')，请检查伪静态配置</span>';
            }
        })
        .catch(error => {
            statusContent.innerHTML = '<span class="text-red-600 flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>请求失败，可能是跨域限制。请直接点击"打开"按钮测试</span>';
        });
}

function testRedis() {
    const result = document.getElementById('redisTestResult');
    result.innerHTML = '<span class="text-gray-500">测试中...</span>';
    
    const data = {
        _token: '<?= $csrfToken ?>',
        redis_host: document.querySelector('input[name="redis_host"]').value,
        redis_port: document.querySelector('input[name="redis_port"]').value,
        redis_pass: document.querySelector('input[name="redis_pass"]').value,
        redis_db: document.querySelector('input[name="redis_db"]').value
    };
    
    fetch(adminUrl('/config/testRedis'), {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(r => r.json())
    .then(res => {
        if (res.code === 0) {
            result.innerHTML = '<span class="text-green-600">✓ ' + res.msg + '</span>';
        } else {
            result.innerHTML = '<span class="text-red-600">✗ ' + res.msg + '</span>';
        }
    })
    .catch(() => {
        result.innerHTML = '<span class="text-red-600">✗ 请求失败</span>';
    });
}
</script>
