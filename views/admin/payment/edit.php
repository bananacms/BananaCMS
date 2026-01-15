<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold"><?= $channel ? '编辑' : '添加' ?>支付通道</h1>
    <a href="/<?= $adminEntry ?>?s=payment" class="text-gray-500 hover:text-gray-700">← 返回列表</a>
</div>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" class="space-y-4">
        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">通道编码 <span class="text-red-500">*</span></label>
                <input type="text" name="channel_code" value="<?= htmlspecialchars($channel['channel_code'] ?? '') ?>" 
                    class="w-full border rounded px-3 py-2" placeholder="如: mch_pay" required>
                <p class="text-xs text-gray-400 mt-1">唯一标识，建议英文</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">通道名称 <span class="text-red-500">*</span></label>
                <input type="text" name="channel_name" value="<?= htmlspecialchars($channel['channel_name'] ?? '') ?>" 
                    class="w-full border rounded px-3 py-2" placeholder="如: MCH易支付" required>
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">通道类型</label>
            <?php $extraConfig = json_decode($channel['extra_config'] ?? '{}', true) ?: []; ?>
            <select name="channel_type" id="channel_type" class="w-full border rounded px-3 py-2" onchange="toggleExtraFields()">
                <option value="epay" <?= ($extraConfig['protocol'] ?? 'epay') === 'epay' ? 'selected' : '' ?>>易支付协议 (MCH)</option>
                <option value="xiongxiong" <?= ($extraConfig['protocol'] ?? '') === 'xiongxiong' ? 'selected' : '' ?>>熊熊支付</option>
                <option value="zhilian" <?= ($extraConfig['protocol'] ?? '') === 'zhilian' ? 'selected' : '' ?>>直连支付</option>
            </select>
        </div>
        
        <!-- 易支付说明 -->
        <div id="epay_fields" class="bg-gray-50 border border-gray-200 rounded p-4" style="display: none;">
            <p class="text-sm text-gray-600">易支付协议是最常见的第三方支付接口协议，兼容大多数支付平台。</p>
            <p class="text-xs text-gray-500 mt-2">开户联系: @tianguaer (Telegram)</p>
        </div>
        
        <!-- 熊熊支付额外配置 -->
        <div id="xiongxiong_fields" class="bg-yellow-50 border border-yellow-200 rounded p-4 space-y-3" style="display: none;">
            <h4 class="font-medium text-yellow-800">熊熊支付配置</h4>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">渠道编号 (cid)</label>
                    <input type="text" name="xiongxiong_cid" value="<?= htmlspecialchars($extraConfig['cid'] ?? '1') ?>" 
                        class="w-full border rounded px-3 py-2" placeholder="1">
                    <p class="text-xs text-gray-400 mt-1">从商户后台获取，如支付宝=1</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">扩展参数 (eparam)</label>
                    <input type="text" name="xiongxiong_eparam" value="<?= htmlspecialchars($extraConfig['eparam'] ?? '') ?>" 
                        class="w-full border rounded px-3 py-2" placeholder="一般留空">
                </div>
            </div>
            <p class="text-xs text-yellow-700">开户联系: @TPPAY_XX (Telegram)</p>
        </div>
        
        <!-- 直连支付额外配置 -->
        <div id="zhilian_fields" class="bg-blue-50 border border-blue-200 rounded p-4 space-y-3" style="display: none;">
            <h4 class="font-medium text-blue-800">直连支付配置</h4>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">渠道代码 (channel_code)</label>
                <input type="text" name="zhilian_channel_code" value="<?= htmlspecialchars($extraConfig['channel_code'] ?? '') ?>" 
                    class="w-full border rounded px-3 py-2" placeholder="如: alipay_h5">
            </div>
            <p class="text-xs text-blue-700">开户联系: @acc577 (Telegram)</p>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">支持的支付方式 <span class="text-red-500">*</span></label>
            <div class="flex gap-4">
                <?php $methods = explode(',', $channel['support_methods'] ?? 'alipay'); ?>
                <label class="flex items-center">
                    <input type="checkbox" name="support_methods[]" value="alipay" <?= in_array('alipay', $methods) ? 'checked' : '' ?> class="mr-2">
                    支付宝
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="support_methods[]" value="wechat" <?= in_array('wechat', $methods) ? 'checked' : '' ?> class="mr-2">
                    微信支付
                </label>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">网关地址 <span class="text-red-500">*</span></label>
                <input type="url" name="gateway_url" value="<?= htmlspecialchars($channel['gateway_url'] ?? '') ?>" 
                    class="w-full border rounded px-3 py-2" placeholder="https://..." required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">查询地址</label>
                <input type="url" name="query_url" value="<?= htmlspecialchars($channel['query_url'] ?? '') ?>" 
                    class="w-full border rounded px-3 py-2" placeholder="https://...">
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">商户ID <span class="text-red-500">*</span></label>
                <input type="text" name="merchant_id" value="<?= htmlspecialchars($channel['merchant_id'] ?? '') ?>" 
                    class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">商户密钥 <span class="text-red-500">*</span></label>
                <input type="text" name="merchant_key" value="<?= htmlspecialchars($channel['merchant_key'] ?? '') ?>" 
                    class="w-full border rounded px-3 py-2" required>
            </div>
        </div>
        
        <div class="grid grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">手续费率</label>
                <input type="number" name="fee_rate" value="<?= $channel['fee_rate'] ?? 0 ?>" 
                    class="w-full border rounded px-3 py-2" step="0.0001" min="0" max="1">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">最小金额</label>
                <input type="number" name="min_amount" value="<?= $channel['min_amount'] ?? 0.01 ?>" 
                    class="w-full border rounded px-3 py-2" step="0.01" min="0">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">最大金额</label>
                <input type="number" name="max_amount" value="<?= $channel['max_amount'] ?? 50000 ?>" 
                    class="w-full border rounded px-3 py-2" step="0.01" min="0">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">权重</label>
                <input type="number" name="weight" value="<?= $channel['weight'] ?? 100 ?>" 
                    class="w-full border rounded px-3 py-2" min="1">
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                <input type="number" name="sort" value="<?= $channel['channel_sort'] ?? 0 ?>" 
                    class="w-full border rounded px-3 py-2" min="0">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="1" <?= ($channel['channel_status'] ?? 1) == 1 ? 'selected' : '' ?>>启用</option>
                    <option value="0" <?= ($channel['channel_status'] ?? 1) == 0 ? 'selected' : '' ?>>禁用</option>
                </select>
            </div>
        </div>
        
        <div class="pt-4 border-t">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">保存</button>
        </div>
    </form>
</div>

<script>
function toggleExtraFields() {
    var type = document.getElementById('channel_type').value;
    document.getElementById('epay_fields').style.display = type === 'epay' ? 'block' : 'none';
    document.getElementById('xiongxiong_fields').style.display = type === 'xiongxiong' ? 'block' : 'none';
    document.getElementById('zhilian_fields').style.display = type === 'zhilian' ? 'block' : 'none';
}
// 页面加载时执行
toggleExtraFields();

document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    
    fetch(location.href, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg, 'success');
            setTimeout(() => location.href = adminUrl('/payment'), 1000);
        } else {
            xpkToast(data.msg, 'error');
        }
    });
});
</script>
