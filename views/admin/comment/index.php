<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">💬 评论管理</h2>
        <a href="/admin.php/comment/setting" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
            ⚙️ 评论设置
        </a>
    </div>

    <!-- 统计卡片 -->
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">全部评论</div>
            <div class="text-2xl font-bold"><?= $stats['total'] ?></div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">待审核</div>
            <div class="text-2xl font-bold text-yellow-600"><?= $stats['pending'] ?></div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">已通过</div>
            <div class="text-2xl font-bold text-green-600"><?= $stats['approved'] ?></div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500 text-sm">已拒绝</div>
            <div class="text-2xl font-bold text-red-600"><?= $stats['rejected'] ?></div>
        </div>
    </div>

    <!-- 筛选 -->
    <div class="bg-white p-4 rounded shadow mb-4">
        <form method="get" class="flex gap-4 items-center">
            <select name="status" class="border rounded px-3 py-2">
                <option value="">全部状态</option>
                <option value="0" <?= $status === '0' ? 'selected' : '' ?>>待审核</option>
                <option value="1" <?= $status === '1' ? 'selected' : '' ?>>已通过</option>
                <option value="2" <?= $status === '2' ? 'selected' : '' ?>>已拒绝</option>
            </select>
            <select name="type" class="border rounded px-3 py-2">
                <option value="">全部类型</option>
                <option value="vod" <?= $type === 'vod' ? 'selected' : '' ?>>视频评论</option>
                <option value="art" <?= $type === 'art' ? 'selected' : '' ?>>文章评论</option>
            </select>
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">筛选</button>
            <a href="/admin.php/comment" class="text-gray-500 hover:text-gray-700">重置</a>
        </form>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="mb-4 p-4 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <!-- 批量操作 -->
    <div class="bg-white p-3 rounded shadow mb-2 flex gap-2 items-center">
        <input type="checkbox" id="checkAll" onchange="toggleAll(this)">
        <label for="checkAll" class="text-sm text-gray-600">全选</label>
        <span class="text-gray-300">|</span>
        <button onclick="batchAction('approve')" class="text-sm text-green-600 hover:underline">批量通过</button>
        <button onclick="batchAction('reject')" class="text-sm text-yellow-600 hover:underline">批量拒绝</button>
        <button onclick="batchAction('delete')" class="text-sm text-red-600 hover:underline">批量删除</button>
    </div>
</div>

<!-- 列表 -->
<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 w-10"></th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">用户</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">内容</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">类型</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">赞/踩</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">状态</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">时间</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($list)): ?>
            <tr>
                <td colspan="9" class="px-4 py-8 text-center text-gray-500">暂无数据</td>
            </tr>
            <?php else: ?>
            <?php foreach ($list as $item): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <input type="checkbox" class="comment-check" value="<?= $item['comment_id'] ?>">
                </td>
                <td class="px-4 py-3 text-sm"><?= $item['comment_id'] ?></td>
                <td class="px-4 py-3 text-sm">
                    <?= htmlspecialchars($item['user_nick_name'] ?: $item['user_name'] ?: '游客') ?>
                    <div class="text-xs text-gray-400"><?= $item['comment_ip'] ?></div>
                </td>
                <td class="px-4 py-3">
                    <div class="max-w-md">
                        <?php if ($item['parent_id'] > 0): ?>
                        <span class="text-xs bg-blue-100 text-blue-600 px-1 rounded">回复</span>
                        <?php endif; ?>
                        <span class="text-sm"><?= htmlspecialchars(mb_substr($item['comment_content'], 0, 100)) ?><?= mb_strlen($item['comment_content']) > 100 ? '...' : '' ?></span>
                    </div>
                </td>
                <td class="px-4 py-3 text-sm">
                    <?php if ($item['comment_type'] === 'vod'): ?>
                    <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs">视频</span>
                    <?php else: ?>
                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">文章</span>
                    <?php endif; ?>
                    <span class="text-gray-400 text-xs">#<?= $item['target_id'] ?></span>
                </td>
                <td class="px-4 py-3 text-sm">
                    <span class="text-green-600">👍 <?= $item['comment_up'] ?></span>
                    <span class="text-red-600 ml-2">👎 <?= $item['comment_down'] ?></span>
                </td>
                <td class="px-4 py-3">
                    <?php if ($item['comment_status'] == 0): ?>
                    <span class="text-yellow-600 text-sm">⏳ 待审核</span>
                    <?php elseif ($item['comment_status'] == 1): ?>
                    <span class="text-green-600 text-sm">✓ 已通过</span>
                    <?php else: ?>
                    <span class="text-red-600 text-sm">✗ 已拒绝</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">
                    <?= date('m-d H:i', $item['comment_time']) ?>
                </td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <?php if ($item['comment_status'] == 0): ?>
                    <button onclick="auditComment(<?= $item['comment_id'] ?>, 'approve')" class="text-green-600 hover:underline">通过</button>
                    <button onclick="auditComment(<?= $item['comment_id'] ?>, 'reject')" class="text-yellow-600 hover:underline">拒绝</button>
                    <?php endif; ?>
                    <button onclick="deleteItem('/admin.php/comment/delete', <?= $item['comment_id'] ?>)" class="text-red-600 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 分页 -->
<?php 
$baseUrl = "/admin.php/comment?status=" . urlencode($status) . "&type=" . urlencode($type);
include __DIR__ . '/../components/pagination.php'; 
?>

<script>
function toggleAll(el) {
    document.querySelectorAll('.comment-check').forEach(cb => cb.checked = el.checked);
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.comment-check:checked')).map(cb => cb.value).join(',');
}

function auditComment(id, action) {
    const url = action === 'approve' ? '/admin.php/comment/approve' : '/admin.php/comment/reject';
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast(data.msg, 'success');
            location.reload();
        } else {
            xpkToast(data.msg, 'error');
        }
    });
}

function batchAction(action) {
    const ids = getSelectedIds();
    if (!ids) {
        xpkToast('请选择评论', 'warning');
        return;
    }
    
    const msg = action === 'delete' ? '确定删除选中的评论？' : '确定执行此操作？';
    xpkConfirm(msg, function() {
        fetch('/admin.php/comment/batchAudit', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'ids=' + ids + '&action=' + action
        })
        .then(r => r.json())
        .then(data => {
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
