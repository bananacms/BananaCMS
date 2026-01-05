<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            评论管理
        </h2>
        <a href="/<?= $adminEntry ?>?s=comment/setting" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            评论设置
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
            <a href="/<?= $adminEntry ?>?s=comment" class="text-gray-500 hover:text-gray-700">重置</a>
        </form>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="mb-4 p-4 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= htmlspecialchars($flash['msg']) ?>
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
                    <div class="flex items-center space-x-3">
                        <span class="text-green-600 inline-flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                            </svg>
                            <?= $item['comment_up'] ?>
                        </span>
                        <span class="text-red-600 inline-flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018c.163 0 .326.02.485.06L17 4m-7 10v2a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/>
                            </svg>
                            <?= $item['comment_down'] ?>
                        </span>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <?php if ($item['comment_status'] == 0): ?>
                    <span class="text-yellow-600 text-sm inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        待审核
                    </span>
                    <?php elseif ($item['comment_status'] == 1): ?>
                    <span class="text-green-600 text-sm inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        已通过
                    </span>
                    <?php else: ?>
                    <span class="text-red-600 text-sm inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        已拒绝
                    </span>
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
                    <button onclick="deleteItem('/<?= $adminEntry ?>/comment/delete', <?= $item['comment_id'] ?>)" class="text-red-600 hover:underline">删除</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 分页 -->
<?php 
$baseUrl = "/{$adminEntry}?s=comment&status=" . urlencode($status) . "&type=" . urlencode($type);
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
    const url = action === 'approve' ? adminUrl('/comment/approve') : adminUrl('/comment/reject');
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id + '&_token=<?= $csrfToken ?>'
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
        fetch(adminUrl('/comment/batchAudit'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'ids=' + ids + '&action=' + action + '&_token=<?= $csrfToken ?>'
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
