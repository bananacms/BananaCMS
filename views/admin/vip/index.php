<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">VIP套餐管理</h1>
    <div class="space-x-2">
        <button onclick="openConfigModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">⚙️ VIP配置</button>
        <button onclick="openEditModal(0)" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">+ 添加套餐</button>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">套餐名称</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">价格</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">USDT价格</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">有效期</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">每日限制</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">赠送积分</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($packages)): ?>
            <tr>
                <td colspan="9" class="px-4 py-8 text-center text-gray-500">暂无VIP套餐</td>
            </tr>
            <?php else: ?>
            <?php foreach ($packages as $p): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?= $p['package_id'] ?></td>
                <td class="px-4 py-3 text-sm font-medium">
                    <?= htmlspecialchars($p['package_name']) ?>
                    <?php if ($p['package_hot']): ?>
                    <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs rounded">热门</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm">
                    <span class="text-red-500 font-medium">¥<?= number_format($p['package_price'], 2) ?></span>
                    <?php if ($p['package_original']): ?>
                    <span class="text-gray-400 line-through text-xs ml-1">¥<?= number_format($p['package_original'], 2) ?></span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm"><?= $p['package_price_usdt'] ? '$' . $p['package_price_usdt'] : '-' ?></td>
                <td class="px-4 py-3 text-sm"><?= $p['package_days'] ?>天</td>
                <td class="px-4 py-3 text-sm"><?= $p['package_daily_limit'] >= 9999 ? '无限' : $p['package_daily_limit'] . '次' ?></td>
                <td class="px-4 py-3 text-sm"><?= $p['package_bonus_points'] ?: '-' ?></td>
                <td class="px-4 py-3">
                    <button onclick="toggleStatus(<?= $p['package_id'] ?>)" 
                        class="px-2 py-1 rounded text-xs <?= $p['package_status'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $p['package_status'] ? '上架' : '下架' ?>
                    </button>
                </td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <button onclick='openEditModal(<?= json_encode($p) ?>)' class="text-blue-500 hover:underline">编辑</button>
                    <button onclick="deletePackage(<?= $p['package_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 id="modalTitle" class="text-lg font-bold">添加VIP套餐</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <form id="editForm" class="p-6 space-y-4">
            <input type="hidden" name="package_id" id="f_package_id" value="0">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">套餐名称 <span class="text-red-500">*</span></label>
                    <input type="text" name="package_name" id="f_package_name" class="w-full border rounded px-3 py-2" placeholder="如: 月卡" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">套餐编码 <span class="text-red-500">*</span></label>
                    <input type="text" name="package_code" id="f_package_code" class="w-full border rounded px-3 py-2" placeholder="如: month" required>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">价格(CNY) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" id="f_price" class="w-full border rounded px-3 py-2" step="0.01" min="0" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">USDT价格</label>
                    <input type="number" name="price_usdt" id="f_price_usdt" class="w-full border rounded px-3 py-2" step="0.01" min="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">原价(划线价)</label>
                    <input type="number" name="original_price" id="f_original_price" class="w-full border rounded px-3 py-2" step="0.01" min="0">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">有效天数 <span class="text-red-500">*</span></label>
                    <input type="number" name="days" id="f_days" class="w-full border rounded px-3 py-2" min="1" value="30" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">每日观看限制</label>
                    <input type="number" name="daily_limit" id="f_daily_limit" class="w-full border rounded px-3 py-2" min="1" value="9999">
                    <p class="text-xs text-gray-400 mt-1">9999表示无限</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">赠送天数</label>
                    <input type="number" name="bonus_days" id="f_bonus_days" class="w-full border rounded px-3 py-2" min="0" value="0">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">赠送积分</label>
                    <input type="number" name="bonus_points" id="f_bonus_points" class="w-full border rounded px-3 py-2" min="0" value="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                    <input type="number" name="sort" id="f_sort" class="w-full border rounded px-3 py-2" min="0" value="0">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">套餐描述</label>
                <input type="text" name="description" id="f_description" class="w-full border rounded px-3 py-2" placeholder="如: 热门推荐">
            </div>
            <div class="flex gap-6">
                <label class="flex items-center"><input type="checkbox" name="is_hot" id="f_is_hot" value="1" class="mr-2"><span>热门推荐</span></label>
                <label class="flex items-center"><input type="radio" name="status" value="1" checked class="mr-2"><span>上架</span></label>
                <label class="flex items-center"><input type="radio" name="status" value="0" class="mr-2"><span>下架</span></label>
            </div>
            <div class="pt-4 border-t flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded hover:bg-gray-100">取消</button>
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">保存</button>
            </div>
        </form>
    </div>
</div>

<!-- Config Modal -->
<div id="configModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-bold">VIP配置</h3>
            <button onclick="closeConfigModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <form id="configForm" class="p-6 space-y-6">
            <div>
                <h4 class="font-medium mb-3 pb-2 border-b">VIP功能开关</h4>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                    <div>
                        <span class="font-medium">启用VIP限制</span>
                        <p class="text-sm text-gray-500">关闭后所有视频免费观看</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="vip_enabled" id="c_vip_enabled" value="1" <?= !empty($vipConfig['vip_enabled']) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                    </label>
                </div>
            </div>
            <div>
                <h4 class="font-medium mb-3 pb-2 border-b">免费用户限制</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">每日免费观看次数</label>
                        <input type="number" name="free_daily_limit" value="<?= $vipConfig['free_user']['daily_limit'] ?? 3 ?>" class="w-full border rounded px-3 py-2" min="0">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">注册赠送观看次数</label>
                        <input type="number" name="register_gift" value="<?= $vipConfig['free_user']['register_gift'] ?? 5 ?>" class="w-full border rounded px-3 py-2" min="0">
                    </div>
                </div>
            </div>
            <div>
                <h4 class="font-medium mb-3 pb-2 border-b">积分配置</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">积分解锁视频消耗</label>
                        <input type="number" name="unlock_cost" value="<?= $vipConfig['points']['unlock_cost'] ?? 10 ?>" class="w-full border rounded px-3 py-2" min="1">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">每日签到奖励积分</label>
                        <input type="number" name="daily_sign" value="<?= $vipConfig['points']['daily_sign'] ?? 5 ?>" class="w-full border rounded px-3 py-2" min="0">
                    </div>
                </div>
            </div>
            <div>
                <h4 class="font-medium mb-3 pb-2 border-b">邀请奖励</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">邀请注册奖励积分</label>
                        <input type="number" name="invite_register_points" value="<?= $vipConfig['invite']['register_points'] ?? 50 ?>" class="w-full border rounded px-3 py-2" min="0">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">首次付费返佣比例</label>
                        <input type="number" name="first_pay_rate" value="<?= $vipConfig['invite']['first_pay_rate'] ?? 0.10 ?>" class="w-full border rounded px-3 py-2" step="0.01" min="0" max="1">
                        <p class="text-xs text-gray-400">0.10 = 10%</p>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">续费返佣比例</label>
                        <input type="number" name="renew_rate" value="<?= $vipConfig['invite']['renew_rate'] ?? 0.05 ?>" class="w-full border rounded px-3 py-2" step="0.01" min="0" max="1">
                        <p class="text-xs text-gray-400">0.05 = 5%</p>
                    </div>
                </div>
            </div>
            <div class="pt-4 border-t flex justify-end space-x-3">
                <button type="button" onclick="closeConfigModal()" class="px-4 py-2 border rounded hover:bg-gray-100">取消</button>
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">保存配置</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(data) {
    var isEdit = data && data.package_id;
    document.getElementById('modalTitle').textContent = isEdit ? '编辑VIP套餐' : '添加VIP套餐';
    document.getElementById('f_package_id').value = isEdit ? data.package_id : 0;
    document.getElementById('f_package_name').value = isEdit ? data.package_name : '';
    document.getElementById('f_package_code').value = isEdit ? data.package_code : '';
    document.getElementById('f_price').value = isEdit ? data.package_price : '';
    document.getElementById('f_price_usdt').value = isEdit && data.package_price_usdt ? data.package_price_usdt : '';
    document.getElementById('f_original_price').value = isEdit && data.package_original ? data.package_original : '';
    document.getElementById('f_days').value = isEdit ? data.package_days : 30;
    document.getElementById('f_daily_limit').value = isEdit ? data.package_daily_limit : 9999;
    document.getElementById('f_bonus_days').value = isEdit ? data.package_bonus_days : 0;
    document.getElementById('f_bonus_points').value = isEdit ? data.package_bonus_points : 0;
    document.getElementById('f_sort').value = isEdit ? data.package_sort : 0;
    document.getElementById('f_description').value = isEdit ? data.package_desc : '';
    document.getElementById('f_is_hot').checked = isEdit && data.package_hot == 1;
    var statusRadios = document.querySelectorAll('#editForm input[name="status"]');
    statusRadios.forEach(function(r) { r.checked = r.value == (isEdit ? data.package_status : 1); });
    document.getElementById('editModal').classList.remove('hidden');
}

function closeModal() { document.getElementById('editModal').classList.add('hidden'); }

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var id = formData.get('package_id');
    var url = id && id != '0' ? adminUrl('/vip/edit&id=' + id) : adminUrl('/vip/add');
    fetch(url, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) { xpkToast(data.msg, 'success'); closeModal(); setTimeout(() => location.reload(), 500); }
        else { xpkToast(data.msg, 'error'); }
    }).catch(() => xpkToast('请求失败', 'error'));
});

document.getElementById('editModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });

function openConfigModal() { document.getElementById('configModal').classList.remove('hidden'); }
function closeConfigModal() { document.getElementById('configModal').classList.add('hidden'); }

document.getElementById('configForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    fetch(adminUrl('/vip/config'), { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) { xpkToast(data.msg, 'success'); closeConfigModal(); }
        else { xpkToast(data.msg, 'error'); }
    }).catch(() => xpkToast('请求失败', 'error'));
});

document.getElementById('configModal').addEventListener('click', function(e) { if (e.target === this) closeConfigModal(); });

function toggleStatus(id) {
    fetch(adminUrl('/vip/toggle'), { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'id=' + id })
    .then(r => r.json()).then(data => { if (data.code === 0) location.reload(); else xpkToast(data.msg, 'error'); });
}

function deletePackage(id) {
    xpkConfirm('确定删除该VIP套餐？', function() {
        fetch(adminUrl('/vip/delete'), { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'id=' + id })
        .then(r => r.json()).then(data => { if (data.code === 0) location.reload(); else xpkToast(data.msg, 'error'); });
    });
}
</script>
