<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">支付通道管理</h1>
    <div class="space-x-2">
        <a href="/<?= $adminEntry ?>?s=payment/usdt" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">USDT配置</a>
        <button onclick="openEditModal(0)" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">+ 添加通道</button>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">通道编码</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">通道名称</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">支持方式</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">商户ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">权重</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php if (empty($channels)): ?>
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">暂无支付通道</td>
            </tr>
            <?php else: ?>
            <?php foreach ($channels as $ch): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm"><?= $ch['channel_id'] ?></td>
                <td class="px-4 py-3 text-sm font-mono"><?= htmlspecialchars($ch['channel_code']) ?></td>
                <td class="px-4 py-3 text-sm font-medium"><?= htmlspecialchars($ch['channel_name']) ?></td>
                <td class="px-4 py-3 text-sm">
                    <?php foreach (explode(',', $ch['support_methods']) as $m): ?>
                    <span class="inline-block px-2 py-0.5 rounded text-xs <?= $m === 'alipay' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' ?>">
                        <?= $m === 'alipay' ? '支付宝' : ($m === 'wechat' ? '微信' : $m) ?>
                    </span>
                    <?php endforeach; ?>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500"><?= htmlspecialchars($ch['merchant_id']) ?></td>
                <td class="px-4 py-3 text-sm"><?= $ch['weight'] ?></td>
                <td class="px-4 py-3">
                    <button onclick="toggleStatus(<?= $ch['channel_id'] ?>)" 
                        class="px-2 py-1 rounded text-xs <?= $ch['channel_status'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $ch['channel_status'] ? '启用' : '禁用' ?>
                    </button>
                </td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <button onclick='openEditModal(<?= json_encode($ch) ?>)' class="text-blue-500 hover:underline">编辑</button>
                    <button onclick="deleteChannel(<?= $ch['channel_id'] ?>)" class="text-red-500 hover:underline">删除</button>
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
            <h3 id="modalTitle" class="text-lg font-bold">添加支付通道</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <form id="editForm" class="p-6 space-y-4">
            <input type="hidden" name="channel_id" id="f_channel_id" value="0">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">通道编码 <span class="text-red-500">*</span></label>
                    <input type="text" name="channel_code" id="f_channel_code" class="w-full border rounded px-3 py-2" placeholder="如: mch_pay" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">通道名称 <span class="text-red-500">*</span></label>
                    <input type="text" name="channel_name" id="f_channel_name" class="w-full border rounded px-3 py-2" placeholder="如: MCH易支付" required>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">通道类型</label>
                <select name="channel_type" id="f_channel_type" class="w-full border rounded px-3 py-2" onchange="toggleExtraFields()">
                    <option value="epay">易支付协议 (MCH)</option>
                    <option value="xiongxiong">熊熊支付</option>
                    <option value="zhilian">直连支付</option>
                </select>
            </div>
            <div id="xiongxiong_fields" class="bg-yellow-50 border border-yellow-200 rounded p-4 space-y-3 hidden">
                <h4 class="font-medium text-yellow-800">熊熊支付配置</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">渠道编号 (cid)</label>
                        <input type="text" name="xiongxiong_cid" id="f_xiongxiong_cid" class="w-full border rounded px-3 py-2" value="1">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">扩展参数 (eparam)</label>
                        <input type="text" name="xiongxiong_eparam" id="f_xiongxiong_eparam" class="w-full border rounded px-3 py-2">
                    </div>
                </div>
            </div>
            <div id="zhilian_fields" class="bg-blue-50 border border-blue-200 rounded p-4 hidden">
                <h4 class="font-medium text-blue-800">直连支付配置</h4>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">渠道代码</label>
                    <input type="text" name="zhilian_channel_code" id="f_zhilian_channel_code" class="w-full border rounded px-3 py-2" placeholder="如: alipay_h5">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">支持的支付方式 <span class="text-red-500">*</span></label>
                <div class="flex gap-4">
                    <label class="flex items-center"><input type="checkbox" name="support_methods[]" value="alipay" id="f_method_alipay" checked class="mr-2">支付宝</label>
                    <label class="flex items-center"><input type="checkbox" name="support_methods[]" value="wechat" id="f_method_wechat" class="mr-2">微信支付</label>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">网关地址 <span class="text-red-500">*</span></label>
                    <input type="url" name="gateway_url" id="f_gateway_url" class="w-full border rounded px-3 py-2" placeholder="https://..." required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">查询地址</label>
                    <input type="url" name="query_url" id="f_query_url" class="w-full border rounded px-3 py-2" placeholder="https://...">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">商户ID <span class="text-red-500">*</span></label>
                    <input type="text" name="merchant_id" id="f_merchant_id" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">商户密钥 <span class="text-red-500">*</span></label>
                    <input type="text" name="merchant_key" id="f_merchant_key" class="w-full border rounded px-3 py-2" required>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">手续费率</label>
                    <input type="number" name="fee_rate" id="f_fee_rate" class="w-full border rounded px-3 py-2" step="0.0001" min="0" max="1" value="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">最小金额</label>
                    <input type="number" name="min_amount" id="f_min_amount" class="w-full border rounded px-3 py-2" step="0.01" min="0" value="0.01">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">最大金额</label>
                    <input type="number" name="max_amount" id="f_max_amount" class="w-full border rounded px-3 py-2" step="0.01" min="0" value="50000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">权重</label>
                    <input type="number" name="weight" id="f_weight" class="w-full border rounded px-3 py-2" min="1" value="100">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                    <input type="number" name="sort" id="f_sort" class="w-full border rounded px-3 py-2" min="0" value="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                    <select name="status" id="f_status" class="w-full border rounded px-3 py-2">
                        <option value="1">启用</option>
                        <option value="0">禁用</option>
                    </select>
                </div>
            </div>
            <div class="pt-4 border-t flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded hover:bg-gray-100">取消</button>
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleExtraFields() {
    var type = document.getElementById('f_channel_type').value;
    document.getElementById('xiongxiong_fields').classList.toggle('hidden', type !== 'xiongxiong');
    document.getElementById('zhilian_fields').classList.toggle('hidden', type !== 'zhilian');
}

function openEditModal(data) {
    var isEdit = data && data.channel_id;
    document.getElementById('modalTitle').textContent = isEdit ? '编辑支付通道' : '添加支付通道';
    
    var extra = {};
    if (isEdit && data.extra_config) {
        try { extra = JSON.parse(data.extra_config); } catch(e) {}
    }
    
    document.getElementById('f_channel_id').value = isEdit ? data.channel_id : 0;
    document.getElementById('f_channel_code').value = isEdit ? data.channel_code : '';
    document.getElementById('f_channel_name').value = isEdit ? data.channel_name : '';
    document.getElementById('f_channel_type').value = extra.protocol || 'epay';
    document.getElementById('f_gateway_url').value = isEdit ? data.gateway_url : '';
    document.getElementById('f_query_url').value = isEdit ? (data.query_url || '') : '';
    document.getElementById('f_merchant_id').value = isEdit ? data.merchant_id : '';
    document.getElementById('f_merchant_key').value = isEdit ? data.merchant_key : '';
    document.getElementById('f_fee_rate').value = isEdit ? data.fee_rate : 0;
    document.getElementById('f_min_amount').value = isEdit ? data.min_amount : 0.01;
    document.getElementById('f_max_amount').value = isEdit ? data.max_amount : 50000;
    document.getElementById('f_weight').value = isEdit ? data.weight : 100;
    document.getElementById('f_sort').value = isEdit ? data.channel_sort : 0;
    document.getElementById('f_status').value = isEdit ? data.channel_status : 1;
    document.getElementById('f_xiongxiong_cid').value = extra.cid || '1';
    document.getElementById('f_xiongxiong_eparam').value = extra.eparam || '';
    document.getElementById('f_zhilian_channel_code').value = extra.channel_code || '';
    
    var methods = isEdit ? data.support_methods.split(',') : ['alipay'];
    document.getElementById('f_method_alipay').checked = methods.includes('alipay');
    document.getElementById('f_method_wechat').checked = methods.includes('wechat');
    
    toggleExtraFields();
    document.getElementById('editModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var id = formData.get('channel_id');
    var url = id && id != '0' ? adminUrl('/payment/edit&id=' + id) : adminUrl('/payment/add');
    
    fetch(url, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg, 'success');
            closeModal();
            setTimeout(() => location.reload(), 500);
        } else {
            xpkToast(data.msg, 'error');
        }
    })
    .catch(err => xpkToast('请求失败', 'error'));
});

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

function toggleStatus(id) {
    fetch(adminUrl('/payment/toggle'), {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id + '&_token=<?= $csrfToken ?>'
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) location.reload();
        else xpkToast(data.msg, 'error');
    });
}

function deleteChannel(id) {
    xpkConfirm('确定删除该支付通道？', function() {
        fetch(adminUrl('/payment/delete'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id + '&_token=<?= $csrfToken ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) location.reload();
            else xpkToast(data.msg, 'error');
        });
    });
}
</script>
