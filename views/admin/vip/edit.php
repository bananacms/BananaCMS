<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold"><?= $package ? '编辑' : '添加' ?>VIP套餐</h1>
    <a href="/<?= $adminEntry ?>?s=vip" class="text-gray-500 hover:text-gray-700">← 返回列表</a>
</div>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">套餐名称 <span class="text-red-500">*</span></label>
                <input type="text" name="package_name" value="<?= htmlspecialchars($package['package_name'] ?? '') ?>" 
                    class="w-full border rounded px-3 py-2" placeholder="如: 月卡" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">套餐编码 <span class="text-red-500">*</span></label>
                <input type="text" name="package_code" value="<?= htmlspecialchars($package['package_code'] ?? '') ?>" 
                    class="w-full border rounded px-3 py-2" placeholder="如: month" required>
            </div>
        </div>
        
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">价格(CNY) <span class="text-red-500">*</span></label>
                <input type="number" name="price" value="<?= $package['price'] ?? '' ?>" 
                    class="w-full border rounded px-3 py-2" step="0.01" min="0" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">USDT价格</label>
                <input type="number" name="price_usdt" value="<?= $package['price_usdt'] ?? '' ?>" 
                    class="w-full border rounded px-3 py-2" step="0.01" min="0">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">原价(划线价)</label>
                <input type="number" name="original_price" value="<?= $package['original_price'] ?? '' ?>" 
                    class="w-full border rounded px-3 py-2" step="0.01" min="0">
            </div>
        </div>
        
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">有效天数 <span class="text-red-500">*</span></label>
                <input type="number" name="days" value="<?= $package['days'] ?? 30 ?>" 
                    class="w-full border rounded px-3 py-2" min="1" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">每日观看限制</label>
                <input type="number" name="daily_limit" value="<?= $package['daily_limit'] ?? 9999 ?>" 
                    class="w-full border rounded px-3 py-2" min="1">
                <p class="text-xs text-gray-400 mt-1">9999表示无限</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">赠送天数</label>
                <input type="number" name="bonus_days" value="<?= $package['bonus_days'] ?? 0 ?>" 
                    class="w-full border rounded px-3 py-2" min="0">
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">赠送积分</label>
                <input type="number" name="bonus_points" value="<?= $package['bonus_points'] ?? 0 ?>" 
                    class="w-full border rounded px-3 py-2" min="0">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                <input type="number" name="sort" value="<?= $package['sort'] ?? 0 ?>" 
                    class="w-full border rounded px-3 py-2" min="0">
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">套餐描述</label>
            <input type="text" name="description" value="<?= htmlspecialchars($package['description'] ?? '') ?>" 
                class="w-full border rounded px-3 py-2" placeholder="如: 热门推荐">
        </div>
        
        <div class="flex gap-6">
            <label class="flex items-center">
                <input type="checkbox" name="is_hot" value="1" <?= !empty($package['is_hot']) ? 'checked' : '' ?> class="mr-2">
                <span>热门推荐</span>
            </label>
            <label class="flex items-center">
                <input type="radio" name="status" value="1" <?= ($package['status'] ?? 1) == 1 ? 'checked' : '' ?> class="mr-2">
                <span>上架</span>
            </label>
            <label class="flex items-center">
                <input type="radio" name="status" value="0" <?= ($package['status'] ?? 1) == 0 ? 'checked' : '' ?> class="mr-2">
                <span>下架</span>
            </label>
        </div>
        
        <div class="pt-4 border-t">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">保存</button>
        </div>
    </form>
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
            setTimeout(() => location.href = adminUrl('/vip'), 1000);
        } else {
            xpkToast(data.msg, 'error');
        }
    });
});
</script>
