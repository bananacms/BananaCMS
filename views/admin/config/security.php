<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">安全配置</h1>
    <a href="/<?= $adminEntry ?>/config" class="text-gray-500 hover:text-gray-700">← 返回系统配置</a>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form method="POST" class="space-y-8">
        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <svg class="w-5 h-5 text-blue-400 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-blue-800">安全提示</h3>
                    <p class="text-sm text-blue-700 mt-1">修改安全配置可能影响网站功能，请谨慎操作。建议在测试环境验证后再应用到生产环境。</p>
                </div>
            </div>
        </div>

        <!-- Content Security Policy -->
        <div class="border rounded-lg p-6">
            <h3 class="text-lg font-bold mb-4">Content Security Policy (CSP)</h3>
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="security_csp_enabled" value="1" <?= ($config['security_csp_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded">
                    <span class="ml-2 font-medium">启用 CSP</span>
                </label>
                <p class="text-sm text-gray-500 mt-1">Content Security Policy 可以防止 XSS 攻击和代码注入</p>
            </div>
            
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Script Sources</label>
                    <input type="text" name="security_csp_script_src" value="<?= htmlspecialchars($config['security_csp_script_src'] ?? '') ?>" 
                           class="w-full border rounded px-3 py-2" placeholder="'self' 'unsafe-inline'">
                    <p class="text-sm text-gray-500 mt-1">允许的脚本来源，用空格分隔</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Style Sources</label>
                    <input type="text" name="security_csp_style_src" value="<?= htmlspecialchars($config['security_csp_style_src'] ?? '') ?>" 
                           class="w-full border rounded px-3 py-2" placeholder="'self' 'unsafe-inline'">
                    <p class="text-sm text-gray-500 mt-1">允许的样式来源，用空格分隔</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Image Sources</label>
                    <input type="text" name="security_csp_img_src" value="<?= htmlspecialchars($config['security_csp_img_src'] ?? '') ?>" 
                           class="w-full border rounded px-3 py-2" placeholder="'self' data: https:">
                    <p class="text-sm text-gray-500 mt-1">允许的图片来源，用空格分隔</p>
                </div>
            </div>
        </div>

        <!-- 基础安全头 -->
        <div class="border rounded-lg p-6">
            <h3 class="text-lg font-bold mb-4">基础安全响应头</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">X-Frame-Options</label>
                    <select name="security_frame_options" class="w-full border rounded px-3 py-2">
                        <option value="DENY" <?= ($config['security_frame_options'] ?? '') === 'DENY' ? 'selected' : '' ?>>DENY</option>
                        <option value="SAMEORIGIN" <?= ($config['security_frame_options'] ?? 'SAMEORIGIN') === 'SAMEORIGIN' ? 'selected' : '' ?>>SAMEORIGIN</option>
                        <option value="" <?= ($config['security_frame_options'] ?? '') === '' ? 'selected' : '' ?>>不设置</option>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">防止点击劫持攻击</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Referrer Policy</label>
                    <select name="security_referrer_policy" class="w-full border rounded px-3 py-2">
                        <option value="no-referrer" <?= ($config['security_referrer_policy'] ?? '') === 'no-referrer' ? 'selected' : '' ?>>no-referrer</option>
                        <option value="strict-origin" <?= ($config['security_referrer_policy'] ?? '') === 'strict-origin' ? 'selected' : '' ?>>strict-origin</option>
                        <option value="strict-origin-when-cross-origin" <?= ($config['security_referrer_policy'] ?? 'strict-origin-when-cross-origin') === 'strict-origin-when-cross-origin' ? 'selected' : '' ?>>strict-origin-when-cross-origin</option>
                        <option value="same-origin" <?= ($config['security_referrer_policy'] ?? '') === 'same-origin' ? 'selected' : '' ?>>same-origin</option>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">控制 Referer 信息的发送</p>
                </div>
            </div>
            
            <div class="mt-4">
                <label class="flex items-center">
                    <input type="checkbox" name="security_xss_protection" value="1" <?= ($config['security_xss_protection'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded">
                    <span class="ml-2 font-medium">启用 X-XSS-Protection</span>
                </label>
                <p class="text-sm text-gray-500 mt-1">启用浏览器内置的 XSS 过滤器</p>
            </div>
            
            <div class="mt-4">
                <label class="flex items-center">
                    <input type="checkbox" name="security_hide_server_info" value="1" <?= ($config['security_hide_server_info'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded">
                    <span class="ml-2 font-medium">隐藏服务器信息</span>
                </label>
                <p class="text-sm text-gray-500 mt-1">移除 Server 和 X-Powered-By 响应头</p>
            </div>
        </div>

        <!-- HSTS -->
        <div class="border rounded-lg p-6">
            <h3 class="text-lg font-bold mb-4">HTTP Strict Transport Security (HSTS)</h3>
            <p class="text-sm text-gray-600 mb-4">仅在 HTTPS 环境下生效，强制浏览器使用安全连接</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Max Age (秒)</label>
                    <input type="number" name="security_hsts_max_age" value="<?= $config['security_hsts_max_age'] ?? 31536000 ?>" 
                           class="w-full border rounded px-3 py-2" min="0">
                    <p class="text-sm text-gray-500 mt-1">HSTS 策略的有效期，默认1年</p>
                </div>
                
                <div class="space-y-2 pt-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="security_hsts_include_subdomains" value="1" <?= ($config['security_hsts_include_subdomains'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded">
                        <span class="ml-2 text-sm">包含子域名</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="security_hsts_preload" value="1" <?= ($config['security_hsts_preload'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded">
                        <span class="ml-2 text-sm">启用 Preload</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Permissions Policy -->
        <div class="border rounded-lg p-6">
            <h3 class="text-lg font-bold mb-4">Permissions Policy</h3>
            
            <div>
                <label class="block text-sm font-medium mb-2">策略配置</label>
                <textarea name="security_permissions_policy" rows="3" class="w-full border rounded px-3 py-2" 
                          placeholder="camera=(), microphone=(), geolocation=(), payment=()"><?= htmlspecialchars($config['security_permissions_policy'] ?? '') ?></textarea>
                <p class="text-sm text-gray-500 mt-1">控制浏览器功能的访问权限，用逗号分隔</p>
            </div>
        </div>

        <!-- Cross-Origin 策略 -->
        <div class="border rounded-lg p-6">
            <h3 class="text-lg font-bold mb-4">Cross-Origin 策略</h3>
            <p class="text-sm text-gray-600 mb-4">高级安全策略，可能影响跨域功能，请谨慎启用</p>
            
            <div class="space-y-4">
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="security_coep_enabled" value="1" <?= ($config['security_coep_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded">
                        <span class="ml-2 font-medium">启用 Cross-Origin-Embedder-Policy</span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1">要求所有嵌入的资源都明确允许跨域</p>
                </div>
                
                <div>
                    <label class="flex items-center mb-2">
                        <input type="checkbox" name="security_coop_enabled" value="1" <?= ($config['security_coop_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded">
                        <span class="ml-2 font-medium">启用 Cross-Origin-Opener-Policy</span>
                    </label>
                    <select name="security_coop_policy" class="w-full border rounded px-3 py-2">
                        <option value="unsafe-none" <?= ($config['security_coop_policy'] ?? 'unsafe-none') === 'unsafe-none' ? 'selected' : '' ?>>unsafe-none</option>
                        <option value="same-origin-allow-popups" <?= ($config['security_coop_policy'] ?? '') === 'same-origin-allow-popups' ? 'selected' : '' ?>>same-origin-allow-popups</option>
                        <option value="same-origin" <?= ($config['security_coop_policy'] ?? '') === 'same-origin' ? 'selected' : '' ?>>same-origin</option>
                    </select>
                </div>
                
                <div>
                    <label class="flex items-center mb-2">
                        <input type="checkbox" name="security_corp_enabled" value="1" <?= ($config['security_corp_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded">
                        <span class="ml-2 font-medium">启用 Cross-Origin-Resource-Policy</span>
                    </label>
                    <select name="security_corp_policy" class="w-full border rounded px-3 py-2">
                        <option value="same-site" <?= ($config['security_corp_policy'] ?? 'same-site') === 'same-site' ? 'selected' : '' ?>>same-site</option>
                        <option value="same-origin" <?= ($config['security_corp_policy'] ?? '') === 'same-origin' ? 'selected' : '' ?>>same-origin</option>
                        <option value="cross-origin" <?= ($config['security_corp_policy'] ?? '') === 'cross-origin' ? 'selected' : '' ?>>cross-origin</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex gap-4 pt-4 border-t">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
                保存配置
            </button>
            <a href="/<?= $adminEntry ?>/config" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">
                取消
            </a>
        </div>
    </form>
</div>
