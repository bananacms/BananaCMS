<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">VIP配置</h1>
    <a href="/<?= $adminEntry ?>?s=vip" class="text-gray-500 hover:text-gray-700">← 返回列表</a>
</div>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" class="space-y-6">
        <!-- VIP功能开关 -->
        <div>
            <h3 class="text-lg font-medium mb-3 pb-2 border-b">VIP功能开关</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-800">启用VIP限制</label>
                        <p class="text-sm text-gray-500 mt-1">关闭后所有视频免费观看，无需登录或购买VIP</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="vip_enabled" value="1" <?= !empty($config['vip_enabled']) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                    </label>
                </div>
                <div id="vipSettings" class="<?= empty($config['vip_enabled']) ? 'opacity-50 pointer-events-none' : '' ?>">
        </div>
        </div>
        
        <!-- 免费用户配置 -->
        <div>
            <h3 class="text-lg font-medium mb-3 pb-2 border-b">免费用户限制</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">每日免费观看次数</label>
                    <input type="number" name="free_daily_limit" value="<?= $config['free_user']['daily_limit'] ?? 3 ?>" 
                        class="w-full border rounded px-3 py-2" min="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">注册赠送观看次数</label>
                    <input type="number" name="register_gift" value="<?= $config['free_user']['register_gift'] ?? 5 ?>" 
                        class="w-full border rounded px-3 py-2" min="0">
                </div>
            </div>
        </div>
        
        <!-- 积分配置 -->
        <div>
            <h3 class="text-lg font-medium mb-3 pb-2 border-b">积分配置</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">积分解锁单个视频消耗</label>
                    <input type="number" name="unlock_cost" value="<?= $config['points']['unlock_cost'] ?? 10 ?>" 
                        class="w-full border rounded px-3 py-2" min="1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">每日签到奖励积分</label>
                    <input type="number" name="daily_sign" value="<?= $config['points']['daily_sign'] ?? 5 ?>" 
                        class="w-full border rounded px-3 py-2" min="0">
                </div>
            </div>
        </div>
        
        <!-- 邀请奖励 -->
        <div>
            <h3 class="text-lg font-medium mb-3 pb-2 border-b">邀请奖励</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">邀请注册奖励积分</label>
                    <input type="number" name="invite_register_points" value="<?= $config['invite']['register_points'] ?? 50 ?>" 
                        class="w-full border rounded px-3 py-2" min="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">首次付费返佣比例</label>
                    <input type="number" name="first_pay_rate" value="<?= $config['invite']['first_pay_rate'] ?? 0.10 ?>" 
                        class="w-full border rounded px-3 py-2" step="0.01" min="0" max="1">
                    <p class="text-xs text-gray-400 mt-1">0.10 = 10%</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">续费返佣比例</label>
                    <input type="number" name="renew_rate" value="<?= $config['invite']['renew_rate'] ?? 0.05 ?>" 
                        class="w-full border rounded px-3 py-2" step="0.01" min="0" max="1">
                    <p class="text-xs text-gray-400 mt-1">0.05 = 5%</p>
                </div>
            </div>
        </div>
        
        <div class="pt-4 border-t">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">保存配置</button>
        </div>
                </div><!-- end vipSettings -->
    </form>
</div>

<script>
// VIP开关联动
document.querySelector('input[name="vip_enabled"]').addEventListener('change', function() {
    var settings = document.getElementById('vipSettings');
    if (this.checked) {
        settings.classList.remove('opacity-50', 'pointer-events-none');
    } else {
        settings.classList.add('opacity-50', 'pointer-events-none');
    }
});

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
