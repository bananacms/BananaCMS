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
            <a href="/admin.php/link/add" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">添加友链</a>
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
                        <a href="/admin.php/link/edit/<?= $item['link_id'] ?>" class="text-blue-500 hover:underline">编辑</a>
                        <button onclick="deleteLink(<?= $item['link_id'] ?>)" class="text-red-500 hover:underline">删除</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 分页 -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="/admin.php/link?page=<?= $i ?>&status=<?= $status ?>" 
           class="px-3 py-1 rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function saveSetting() {
    const autoApprove = document.getElementById('autoApprove').checked ? '1' : '0';
    fetch('/admin.php/link/saveSetting', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'link_auto_approve=' + autoApprove
    }).then(r => r.json()).then(data => {
        alert(data.msg);
    });
}

function audit(id, status) {
    if (!confirm(status == 1 ? '确定通过该友链？' : '确定拒绝该友链？')) return;
    fetch('/admin.php/link/audit', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id + '&status=' + status
    }).then(r => r.json()).then(data => {
        alert(data.msg);
        if (data.code === 0) location.reload();
    });
}

function checkLink(id) {
    fetch('/admin.php/link/check', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    }).then(r => r.json()).then(data => {
        alert(data.msg);
        location.reload();
    });
}

function batchCheck() {
    if (!confirm('确定批量检测所有已通过友链的回链状态？')) return;
    fetch('/admin.php/link/check', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=0'
    }).then(r => r.json()).then(data => {
        alert(data.msg);
        location.reload();
    });
}

function deleteLink(id) {
    if (!confirm('确定删除该友链？')) return;
    fetch('/admin.php/link/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    }).then(r => r.json()).then(data => {
        alert(data.msg);
        if (data.code === 0) location.reload();
    });
}
</script>
