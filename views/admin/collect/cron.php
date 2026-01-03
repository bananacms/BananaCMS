<h1 class="text-2xl font-bold mb-6">定时采集配置</h1>

<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- 配置表单 -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-bold mb-4">自动采集设置</h3>
        
        <form method="POST" id="cronForm" data-no-ajax="true">
            <input type="hidden" name="_token" value="<?= $csrfToken ?>">
            
            <div class="space-y-4">
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="enabled" value="1" <?= !empty($config['enabled']) ? 'checked' : '' ?> class="w-4 h-4 rounded">
                        <span class="font-medium">启用自动采集</span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">执行间隔（分钟）</label>
                    <select name="interval" class="w-full border rounded px-3 py-2">
                        <option value="30" <?= ($config['interval'] ?? 60) == 30 ? 'selected' : '' ?>>30分钟</option>
                        <option value="60" <?= ($config['interval'] ?? 60) == 60 ? 'selected' : '' ?>>1小时</option>
                        <option value="120" <?= ($config['interval'] ?? 60) == 120 ? 'selected' : '' ?>>2小时</option>
                        <option value="360" <?= ($config['interval'] ?? 60) == 360 ? 'selected' : '' ?>>6小时</option>
                        <option value="720" <?= ($config['interval'] ?? 60) == 720 ? 'selected' : '' ?>>12小时</option>
                        <option value="1440" <?= ($config['interval'] ?? 60) == 1440 ? 'selected' : '' ?>>24小时</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">采集模式</label>
                    <select name="mode" class="w-full border rounded px-3 py-2">
                        <option value="add" <?= ($config['mode'] ?? 'add') == 'add' ? 'selected' : '' ?>>只采新数据</option>
                        <option value="all" <?= ($config['mode'] ?? '') == 'all' ? 'selected' : '' ?>>新增+更新</option>
                        <option value="update" <?= ($config['mode'] ?? '') == 'update' ? 'selected' : '' ?>>只更新已有</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">时间范围</label>
                    <select name="hours" class="w-full border rounded px-3 py-2">
                        <option value="" <?= empty($config['hours']) ? 'selected' : '' ?>>不限</option>
                        <option value="6" <?= ($config['hours'] ?? '') == '6' ? 'selected' : '' ?>>6小时内更新</option>
                        <option value="12" <?= ($config['hours'] ?? '') == '12' ? 'selected' : '' ?>>12小时内更新</option>
                        <option value="24" <?= ($config['hours'] ?? '') == '24' ? 'selected' : '' ?>>24小时内更新</option>
                        <option value="72" <?= ($config['hours'] ?? '') == '72' ? 'selected' : '' ?>>3天内更新</option>
                        <option value="168" <?= ($config['hours'] ?? '') == '168' ? 'selected' : '' ?>>7天内更新</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">采集站选择</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto border rounded p-3">
                        <?php $selectedIds = $config['collect_ids'] ?? []; ?>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="selectAllCollects" class="w-4 h-4 rounded" <?= empty($selectedIds) ? 'checked' : '' ?>>
                            <span class="text-sm font-medium">全部采集站</span>
                        </label>
                        <hr class="my-2">
                        <?php foreach ($collects as $c): ?>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="collect_ids[]" value="<?= $c['collect_id'] ?>" class="collect-check w-4 h-4 rounded"
                                <?= in_array($c['collect_id'], $selectedIds) ? 'checked' : '' ?>>
                            <span class="text-sm"><?= htmlspecialchars($c['collect_name']) ?></span>
                            <?php if (!$c['collect_status']): ?>
                            <span class="text-xs text-gray-400">(已禁用)</span>
                            <?php endif; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">不选择则采集所有启用的采集站</p>
                </div>

                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-bold flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    保存配置
                </button>
            </div>
        </form>
    </div>

    <!-- 使用说明 -->
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold mb-4">定时任务设置</h3>
            
            <div class="bg-gray-900 rounded p-4 font-mono text-sm text-green-400 overflow-x-auto">
                <p class="text-gray-500"># 每小时执行一次自动采集</p>
                <p>0 * * * * php <?= ROOT_PATH ?>cron.php auto >> <?= ROOT_PATH ?>runtime/cron.log 2>&1</p>
            </div>
            
            <div class="mt-4 text-sm text-gray-600 space-y-2">
                <p class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                    将上面的命令添加到服务器的 crontab 中
                </p>
                <p class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                    执行 <code class="bg-gray-100 px-1 rounded">crontab -e</code> 编辑定时任务
                </p>
                <p class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                    建议设置每小时执行，实际采集间隔由上方配置控制
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold mb-4">执行状态</h3>
            
            <?php if ($lastRun): ?>
            <div class="flex items-center gap-3 mb-4">
                <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                <span class="text-sm">上次执行: <?= date('Y-m-d H:i:s', $lastRun) ?></span>
            </div>
            <?php else: ?>
            <div class="flex items-center gap-3 mb-4">
                <span class="w-3 h-3 bg-gray-400 rounded-full"></span>
                <span class="text-sm text-gray-500">尚未执行过</span>
            </div>
            <?php endif; ?>

            <button onclick="testCron()" class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                立即执行一次
            </button>
            <p class="text-xs text-gray-500 mt-2 text-center">手动触发采集任务（后台执行）</p>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-medium text-blue-800 mb-2 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                命令行用法
            </h4>
            <div class="text-sm text-blue-700 space-y-1">
                <p><code>php cron.php collect</code> - 采集所有启用的采集站</p>
                <p><code>php cron.php collect --hours=24</code> - 只采24小时内更新</p>
                <p><code>php cron.php collect --id=1</code> - 只采集指定采集站</p>
                <p><code>php cron.php auto</code> - 按后台配置执行</p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('selectAllCollects').addEventListener('change', function() {
    document.querySelectorAll('.collect-check').forEach(cb => {
        cb.checked = false;
    });
});

document.querySelectorAll('.collect-check').forEach(cb => {
    cb.addEventListener('change', function() {
        const anyChecked = document.querySelectorAll('.collect-check:checked').length > 0;
        document.getElementById('selectAllCollects').checked = !anyChecked;
    });
});

document.getElementById('cronForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = '保存中...';
    
    fetch(adminUrl('/collect/saveCron'), {
        method: 'POST',
        body: new FormData(this)
    })
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            xpkToast('保存成功', 'success');
        } else {
            xpkToast(data.msg, 'error');
        }
        btn.disabled = false;
        btn.textContent = '保存配置';
    })
    .catch(() => {
        xpkToast('请求失败', 'error');
        btn.disabled = false;
        btn.textContent = '保存配置';
    });
});

function testCron() {
    xpkConfirm('确定要立即执行采集任务吗？', function() {
        xpkToast('采集任务已在后台启动', 'info');
        
        fetch(adminUrl('/collect/runCron'), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=<?= $csrfToken ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast(data.msg, 'success');
            } else {
                xpkToast(data.msg, 'error');
            }
        })
        .catch(() => xpkToast('请求失败', 'error'));
    });
}
</script>
