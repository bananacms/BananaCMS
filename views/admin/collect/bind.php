<h1 class="text-2xl font-bold mb-6">分类绑定 - <?= htmlspecialchars($collect['collect_name']) ?></h1>

<!-- 快捷操作 -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-blue-800">快捷操作</h3>
            <p class="text-sm text-blue-600 mt-1">本地没有分类？可以一键从资源站同步分类结构</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="syncCategories()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                🔄 一键同步分类
            </button>
            <button onclick="autoBindAll()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                ⚡ 智能绑定全部
            </button>
            <button onclick="unbindAll()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm">
                ✖ 全部不采集
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
            <button onclick="copyFromOther()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded text-sm">
                📋 复制绑定
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (empty($localTypes)): ?>
<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
    <p class="text-yellow-800">⚠️ 本地暂无分类，请先点击"一键同步分类"从资源站导入分类</p>
</div>
<?php endif; ?>

<?php if (!empty($globalBinds)): ?>
<div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
    <p class="text-gray-700 text-sm">
        💡 已有 <span class="font-bold"><?= count($globalBinds) ?></span> 条全局绑定，未设置专属绑定的分类将使用全局绑定
    </p>
</div>
<?php endif; ?>

<form method="POST" action="/admin.php/collect/savebind/<?= $collect['collect_id'] ?>" id="bindForm" class="bg-white rounded-lg shadow p-6">
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
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">💾 保存绑定</button>
        <a href="/admin.php/collect" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">返回</a>
    </div>
</form>

<script>
// 一键同步分类
function syncCategories() {
    xpkConfirm('将从资源站同步分类到本地，已存在的同名分类会跳过，确定继续？', function() {
        const btn = event.target;
        btn.disabled = true;
        btn.textContent = '同步中...';
        
        fetch('/admin.php/collect/syncCategories', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=<?= $collect['collect_id'] ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                xpkToast(data.msg, 'error');
                btn.disabled = false;
                btn.textContent = '🔄 一键同步分类';
            }
        })
        .catch(() => {
            xpkToast('请求失败', 'error');
            btn.disabled = false;
            btn.textContent = '🔄 一键同步分类';
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
        fetch('/admin.php/collect/copyBind', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'from_id=' + fromId + '&to_id=<?= $collect['collect_id'] ?>'
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
