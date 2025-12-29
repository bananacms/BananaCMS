<div class="space-y-6">
    <!-- 统计和设置 -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-500"><?= $stats['pending'] ?></div>
                    <div class="text-sm text-gray-500">待审核</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-500"><?= $stats['approved'] ?></div>
                    <div class="text-sm text-gray-500">已通过</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-500"><?= $stats['rejected'] ?></div>
                    <div class="text-sm text-gray-500">已拒绝</div>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="autoApprove" <?= $linkAutoApprove === '1' ? 'checked' : '' ?> 
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                           onchange="saveSetting()">
                    <span class="text-sm">自动换链（检测到回链自动通过）</span>
                </label>
                <button onclick="batchCheck()" class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm">
                    批量检测回链
                </button>
            </div>
        </div>
    </div>

    <!-- 筛选和操作 -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex gap-2">
                <a href="/admin.php/link" class="px-3 py-1.5 rounded <?= $status === '' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">全部</a>
                <a href="/admin.php/link?status=0" class="px-3 py-1.5 rounded <?= $status === '0' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">待审核</a>
                <a href="/admin.php/link?status=1" class="px-3 py-1.5 rounded <?= $status === '1' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">已通过</a>
                <a href="/admin.php/link?status=2" class="px-3 py-1.5 rounded <?= $status === '2' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">已拒绝</a>
            </div>
            <button onclick="openLinkModal()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">添加友链</button>
        </div>
    </div>

    <!-- 列表 -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">网站</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">联系方式</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">回链状态</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">状态</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">排序</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">时间</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($list)): ?>
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">暂无数据</td></tr>
                <?php else: ?>
                <?php foreach ($list as $item): ?>
                <tr>
                    <td class="px-4 py-3 text-sm"><?= $item['link_id'] ?></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <?php if ($item['link_logo']): ?>
                            <img src="<?= htmlspecialchars($item['link_logo']) ?>" class="w-6 h-6 rounded" alt="">
                            <?php endif; ?>
                            <div>
                                <div class="font-medium"><?= htmlspecialchars($item['link_name']) ?></div>
                                <a href="<?= htmlspecialchars($item['link_url']) ?>" target="_blank" class="text-xs text-blue-500 hover:underline"><?= htmlspecialchars($item['link_url']) ?></a>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500"><?= htmlspecialchars($item['link_contact']) ?: '-' ?></td>
                    <td class="px-4 py-3">
                        <?php if ($item['link_check_status'] == 0): ?>
                        <span class="text-gray-400 text-sm">未检测</span>
                        <?php elseif ($item['link_check_status'] == 1): ?>
                        <span class="text-green-500 text-sm">✓ 有回链</span>
                        <?php else: ?>
                        <span class="text-red-500 text-sm">✗ 无回链</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($item['link_status'] == 0): ?>
                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">待审核</span>
                        <?php elseif ($item['link_status'] == 1): ?>
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">已通过</span>
                        <?php else: ?>
                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">已拒绝</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm"><?= $item['link_sort'] ?></td>
                    <td class="px-4 py-3 text-sm text-gray-500"><?= date('Y-m-d', $item['link_time']) ?></td>
                    <td class="px-4 py-3 text-sm space-x-2">
                        <?php if ($item['link_status'] == 0): ?>
                        <button onclick="audit(<?= $item['link_id'] ?>, 1)" class="text-green-500 hover:underline">通过</button>
                        <button onclick="audit(<?= $item['link_id'] ?>, 2)" class="text-red-500 hover:underline">拒绝</button>
                        <?php endif; ?>
                        <button onclick="checkLink(<?= $item['link_id'] ?>)" class="text-blue-500 hover:underline">检测</button>
                        <button onclick="openLinkModal(<?= $item['link_id'] ?>)" class="text-blue-500 hover:underline">编辑</button>
                        <button onclick="deleteLink(<?= $item['link_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 分页 -->
    <?php 
    $baseUrl = "/admin.php/link?status={$status}";
    include __DIR__ . '/../components/pagination.php'; 
    ?>
</div>

<!-- 友链模态框 -->
<div id="linkModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h3 id="linkModalTitle" class="text-lg font-bold">添加友链</h3>
            <button onclick="closeLinkModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="linkForm" onsubmit="saveLink(event)" class="p-6">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="link_id" id="linkId" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">网站名称 *</label>
                    <input type="text" name="link_name" id="linkName" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">网站地址 *</label>
                    <input type="url" name="link_url" id="linkUrl" required placeholder="https://" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo图片</label>
                    <input type="url" name="link_logo" id="linkLogo" placeholder="https://" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">联系方式</label>
                    <input type="text" name="link_contact" id="linkContact" placeholder="QQ/邮箱/Telegram" class="w-full border rounded px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">排序</label>
                        <input type="number" name="link_sort" id="linkSort" value="0" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                        <select name="link_status" id="linkStatus" class="w-full border rounded px-3 py-2">
                            <option value="0">待审核</option>
                            <option value="1">已通过</option>
                            <option value="2">已拒绝</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeLinkModal()" class="px-4 py-2 border rounded hover:bg-gray-50">取消</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
function openLinkModal(id = null) {
    const modal = document.getElementById('linkModal');
    const title = document.getElementById('linkModalTitle');
    
    document.getElementById('linkForm').reset();
    document.getElementById('linkId').value = '';
    
    if (id) {
        title.textContent = '编辑友链';
        fetch('/admin.php/link/get?id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.code === 0) {
                    const l = data.data;
                    document.getElementById('linkId').value = l.link_id;
                    document.getElementById('linkName').value = l.link_name || '';
                    document.getElementById('linkUrl').value = l.link_url || '';
                    document.getElementById('linkLogo').value = l.link_logo || '';
                    document.getElementById('linkContact').value = l.link_contact || '';
                    document.getElementById('linkSort').value = l.link_sort || 0;
                    document.getElementById('linkStatus').value = l.link_status;
                }
            });
    } else {
        title.textContent = '添加友链';
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeLinkModal() {
    const modal = document.getElementById('linkModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function saveLink(e) {
    e.preventDefault();
    const form = document.getElementById('linkForm');
    const formData = new FormData(form);
    const id = formData.get('link_id');
    const url = id ? '/admin.php/link/edit/' + id : '/admin.php/link/add';
    
    fetch(url, {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg || '保存成功', 'success');
            closeLinkModal();
            location.reload();
        } else {
            xpkToast(data.msg || '保存失败', 'error');
        }
    })
    .catch(() => xpkToast('请求失败', 'error'));
}

document.getElementById('linkModal').addEventListener('click', function(e) {
    if (e.target === this) closeLinkModal();
});

function saveSetting() {
    const autoApprove = document.getElementById('autoApprove').checked ? '1' : '0';
    fetch('/admin.php/link/saveSetting', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'link_auto_approve=' + autoApprove
    }).then(r => r.json()).then(data => {
        xpkToast(data.msg, data.code === 0 ? 'success' : 'error');
    });
}

function audit(id, status) {
    xpkConfirm(status == 1 ? '确定通过该友链？' : '确定拒绝该友链？', function() {
        fetch('/admin.php/link/audit', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id + '&status=' + status
        }).then(r => r.json()).then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
                location.reload();
            } else {
                xpkToast(data.msg, 'error');
            }
        });
    });
}

function checkLink(id) {
    fetch('/admin.php/link/check', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    }).then(r => r.json()).then(data => {
        xpkToast(data.msg, data.code === 0 ? 'success' : 'warning');
        location.reload();
    });
}

function batchCheck() {
    xpkConfirm('确定批量检测所有已通过友链的回链状态？', function() {
        fetch('/admin.php/link/check', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=0'
        }).then(r => r.json()).then(data => {
            xpkToast(data.msg, data.code === 0 ? 'success' : 'warning');
            location.reload();
        });
    });
}

function deleteLink(id) {
    xpkConfirm('确定删除该友链？', function() {
        fetch('/admin.php/link/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id
        }).then(r => r.json()).then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
                location.reload();
            } else {
                xpkToast(data.msg, 'error');
            }
        });
    });
}
</script>
