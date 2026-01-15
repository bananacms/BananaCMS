<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">USDT支付配置</h1>
    <a href="/<?= $adminEntry ?>?s=payment" class="text-gray-500 hover:text-gray-700">← 返回列表</a>
</div>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" class="space-y-4">
        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
        <div>
            <label class="flex items-center">
                <input type="checkbox" name="enabled" value="1" <?= !empty($config['enabled']) ? 'checked' : '' ?> class="mr-2">
                <span class="font-medium">启用USDT/TRC20支付</span>
            </label>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">USDT收款地址 (TRC20)</label>
            <input type="text" name="address" value="<?= htmlspecialchars($config['address'] ?? '') ?>" 
                class="w-full border rounded px-3 py-2 font-mono" placeholder="T...">
            <p class="text-xs text-gray-400 mt-1">请确保是TRC20网络的USDT地址</p>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">TronGrid API Key</label>
            <input type="text" name="tron_api_key" value="<?= htmlspecialchars($config['tron_api_key'] ?? '') ?>" 
                class="w-full border rounded px-3 py-2">
            <p class="text-xs text-gray-400 mt-1">
                从 <a href="https://www.trongrid.io/" target="_blank" class="text-blue-500">TronGrid</a> 获取API Key
            </p>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">金额锁定时间 (秒)</label>
            <input type="number" name="lock_time" value="<?= $config['lock_time'] ?? 1800 ?>" 
                class="w-full border rounded px-3 py-2" min="300" max="7200">
            <p class="text-xs text-gray-400 mt-1">订单过期时间，建议1800秒(30分钟)</p>
        </div>
        
        <div class="pt-4 border-t">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">保存配置</button>
        </div>
    </form>
</div>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6 max-w-2xl">
    <h3 class="font-medium text-yellow-800 mb-2">USDT支付说明</h3>
    <ul class="text-sm text-yellow-700 space-y-1">
        <li>• 用户支付时会生成唯一的USDT金额（精确到4位小数）</li>
        <li>• 系统通过TronGrid API轮询检测到账</li>
        <li>• 金额锁定期间，同一金额不会分配给其他订单</li>
        <li>• 请确保收款地址正确，转错无法找回</li>
    </ul>
</div>

<script>
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
        } else {
            xpkToast(data.msg, 'error');
        }
    });
});
</script>
