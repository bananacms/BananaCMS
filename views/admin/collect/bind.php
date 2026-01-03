<h1 class="text-2xl font-bold mb-6">分类绑定 - <?= htmlspecialchars($collect['collect_name']) ?></h1>

<!-- 快捷操作 -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-blue-800">快捷操作</h3>
            <p class="text-sm text-blue-600 mt-1">本地没有分类？可以一键从资源站同步分类结构</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="syncCategories()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                一键同步分类
            </button>
            <button onclick="autoBindAll()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                智能绑定全部
            </button>
            <button onclick="unbindAll()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                全部不采集
            </button>
            <?php if (count($allCollects) > 1): ?>
            <select id="copyFromSelect" class="border rounded px-3 py-2 text-sm">
                <option value="">从其他采集站复制...</option>
                <?php foreach ($allCollects as $c): ?>
                <?php if ($c['collect_id'] != $collect['collect_id']): ?>
                <option value="<?= $c['collect_id'] ?>"><?= htmlspecialchars($c['collect_name']) ?></option>
                <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <button onclick="copyFromOther()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded text-sm flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                复制绑定
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (empty($localTypes)): ?>
<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
    <p class="text-yellow-800 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        本地暂无分类，请先点击"一键同步分类"从资源站导入分类
    </p>
</div>
<?php endif; ?>

<?php if (!empty($globalBinds)): ?>
<div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
    <p class="text-gray-700 text-sm flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        已有 <span class="font-bold"><?= count($globalBinds) ?></span> 条全局绑定，未设置专属绑定的分类将使用全局绑定
    </p>
</div>
<?php endif; ?>

<form method="POST" action="/<?= $adminEntry ?>/collect/savebind/<?= $collect['collect_id'] ?>" id="bindForm" class="bg-white rounded-lg shadow p-6">
    <input type="hidden" name="_token" value="<?= $csrfToken ?>">

    <div class="mb-4 flex items-center justify-between">
        <span class="text-sm text-gray-600">将远程分类绑定到本地分类，选择"不采集"的分类将被跳过。</span>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="save_as_global" value="1" class="w-4 h-4 rounded">
            <span class="text-sm text-gray-700">同时保存为全局绑定</span>
        </label>
    </div>

    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">远程分类</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 w-16">→</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">本地分类</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 w-24">来源</th>
            </tr>
        </thead>
        <tbody class="divide-y" id="bindTable">
            <?php foreach ($remoteCategories as $cat): ?>
            <?php 
            $currentBind = $binds[$cat['id']] ?? 0;
            $isGlobal = isset($globalBinds[$cat['id']]) && (!isset($binds[$cat['id']]) || $binds[$cat['id']] == $globalBinds[$cat['id']]);
            ?>
            <tr data-remote-id="<?= $cat['id'] ?>" data-remote-name="<?= htmlspecialchars($cat['name']) ?>">
                <td class="px-4 py-3">
                    <span class="text-sm font-medium"><?= htmlspecialchars($cat['name']) ?></span>
                    <span class="text-xs text-gray-400 ml-2">ID: <?= $cat['id'] ?></span>
                    <input type="hidden" name="remote_name[<?= $cat['id'] ?>]" value="<?= htmlspecialchars($cat['name']) ?>">
                </td>
                <td class="px-4 py-3 text-gray-400 text-center">→</td>
                <td class="px-4 py-3">
                    <select name="bind[<?= $cat['id'] ?>]" class="bind-select border rounded px-3 py-1.5 text-sm w-full max-w-xs">
                        <option value="0">-- 不采集 --</option>
                        <?php foreach ($localTypes as $type): ?>
                        <option value="<?= $type['type_id'] ?>" data-name="<?= htmlspecialchars($type['type_name']) ?>" <?= $currentBind == $type['type_id'] ? 'selected' : '' ?>>
                            <?= str_repeat('　', $type['level'] ?? 0) ?><?= htmlspecialchars($type['type_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td class="px-4 py-3">
                    <?php if ($currentBind > 0): ?>
                    <span class="text-xs px-2 py-1 rounded <?= $isGlobal ? 'bg-gray-100 text-gray-600' : 'bg-blue-100 text-blue-600' ?>">
                        <?= $isGlobal ? '全局' : '专属' ?>
                    </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mt-6 flex space-x-4">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            保存绑定
        </button>
        <a href="/<?= $adminEntry ?>/collect" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">返回</a>
    </div>
</form>

<script>
// 一键同步分类
function syncCategories() {
    xpkConfirm('将从资源站同步分类到本地，已存在的同名分类会跳过，确定继续？', function() {
        const btn = event.target;
        btn.disabled = true;
        btn.textContent = '同步中...';
        
        fetch(adminUrl('/collect/syncCategories'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=<?= $collect['collect_id'] ?>&_token=<?= $csrfToken ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                xpkToast(data.msg, 'error');
                btn.disabled = false;
                btn.textContent = '一键同步分类';
            }
        })
        .catch(() => {
            xpkToast('请求失败', 'error');
            btn.disabled = false;
            btn.textContent = '一键同步分类';
        });
    });
}

// 智能绑定全部（按名称匹配）
function autoBindAll() {
    const rows = document.querySelectorAll('#bindTable tr');
    let matched = 0;
    
    rows.forEach(row => {
        const remoteName = row.dataset.remoteName;
        const select = row.querySelector('.bind-select');
        if (!select) return;
        
        // 查找名称匹配的本地分类
        const options = select.querySelectorAll('option');
        for (let opt of options) {
            if (opt.dataset.name && opt.dataset.name === remoteName) {
                select.value = opt.value;
                matched++;
                break;
            }
        }
    });
    
    xpkToast(`智能匹配了 ${matched} 个分类`, 'success');
}

// 全部设为不采集
function unbindAll() {
    document.querySelectorAll('.bind-select').forEach(select => {
        select.value = '0';
    });
    xpkToast('已全部设为不采集', 'info');
}

// 从其他采集站复制绑定
function copyFromOther() {
    const select = document.getElementById('copyFromSelect');
    const fromId = select.value;
    
    if (!fromId) {
        xpkToast('请选择要复制的采集站', 'warning');
        return;
    }
    
    xpkConfirm('将覆盖当前绑定设置，确定要复制吗？', function() {
        fetch(adminUrl('/collect/copyBind'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'from_id=' + fromId + '&to_id=<?= $collect['collect_id'] ?>&_token=<?= $csrfToken ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                xpkToast(data.msg, 'error');
            }
        })
        .catch(() => xpkToast('请求失败', 'error'));
    });
}
</script>
