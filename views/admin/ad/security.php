<?php
$title = '广告安全配置';
require_once VIEW_PATH . 'admin/layouts/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">广告安全配置</h1>
    <a href="/<?= ADMIN_ENTRY ?>/ad" class="text-gray-500 hover:text-gray-700">← 返回广告管理</a>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form method="POST" data-ajax class="space-y-6">
        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-yellow-800">安全提示</h3>
                    <p class="text-sm text-yellow-700 mt-1">启用安全检查可能会阻止某些广告显示，请根据实际需求配置白名单。</p>
                </div>
            </div>
        </div>
        
        <div>
            <label class="flex items-center">
                <input type="checkbox" name="ad_url_check" value="1" <?= ($config['ad_url_check'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded">
                <span class="ml-2 font-medium">启用广告URL安全检查</span>
            </label>
            <p class="text-sm text-gray-500 mt-1">开启后将验证广告中的所有URL，包括图片、视频和链接地址</p>
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-2">允许的域名白名单</label>
            <textarea name="ad_allowed_domains" rows="4" class="w-full border rounded px-3 py-2" 
                      placeholder="每行一个域名，例如：&#10;cdn.example.com&#10;ads.example.com&#10;static.example.com"><?= htmlspecialchars($config['ad_allowed_domains'] ?? '') ?></textarea>
            <p class="text-sm text-gray-500 mt-1">每行一个域名，留空表示允许所有域名。只有白名单中的域名才能用于广告资源。</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">允许的协议</label>
                <input type="text" name="ad_allowed_protocols" value="<?= htmlspecialchars($config['ad_allowed_protocols'] ?? 'https,http') ?>" 
                       class="w-full border rounded px-3 py-2" placeholder="https,http">
                <p class="text-sm text-gray-500 mt-1">用逗号分隔，建议只允许https</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2">URL最大长度</label>
                <input type="number" name="ad_max_url_length" value="<?= $config['ad_max_url_length'] ?? 500 ?>" 
                       class="w-full border rounded px-3 py-2" min="100" max="2000">
                <p class="text-sm text-gray-500 mt-1">防止过长的恶意URL</p>
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-2">禁止的文件扩展名</label>
            <input type="text" name="ad_blocked_extensions" value="<?= htmlspecialchars($config['ad_blocked_extensions'] ?? 'exe,bat,sh,php,js') ?>" 
                   class="w-full border rounded px-3 py-2" placeholder="exe,bat,sh,php,js">
            <p class="text-sm text-gray-500 mt-1">用逗号分隔，防止恶意文件类型</p>
        </div>
        
        <div>
            <label class="flex items-center">
                <input type="checkbox" name="ad_content_filter" value="1" <?= ($config['ad_content_filter'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded">
                <span class="ml-2 font-medium">启用广告内容过滤</span>
            </label>
            <p class="text-sm text-gray-500 mt-1">过滤广告代码中的潜在恶意内容</p>
        </div>
        
        <div class="flex gap-4 pt-4 border-t">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
                保存配置
            </button>
            <a href="/<?= ADMIN_ENTRY ?>/ad" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">
                取消
            </a>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow p-6 mt-6">
    <h3 class="font-bold mb-4">配置说明</h3>
    <div class="space-y-3 text-sm text-gray-600">
        <div>
            <strong>URL安全检查：</strong>验证广告中所有URL的格式、协议、域名和文件类型。
        </div>
        <div>
            <strong>域名白名单：</strong>只允许指定域名的资源，可以有效防止恶意广告。留空表示允许所有域名。
        </div>
        <div>
            <strong>协议限制：</strong>建议只允许https协议，提高安全性。
        </div>
        <div>
            <strong>文件类型过滤：</strong>阻止可执行文件和脚本文件，防止恶意代码执行。
        </div>
        <div>
            <strong>内容过滤：</strong>对广告代码进行基础的安全检查，移除潜在的恶意内容。
        </div>
    </div>
</div>

<?php require_once VIEW_PATH . 'admin/layouts/footer.php'; ?>