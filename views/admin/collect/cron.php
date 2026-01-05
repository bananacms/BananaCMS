<h1 class="text-2xl font-bold mb-6">定时采集配置</h1>

<?php
// 获取 PHP CLI 路径
$phpBinary = PHP_BINARY;
// 如果是 php-fpm，转换为对应的 php cli 路径
if (strpos($phpBinary, 'php-fpm') !== false || strpos($phpBinary, 'fpm') !== false) {
    // 宝塔面板: /www/server/php/83/sbin/php-fpm -> /www/server/php/83/bin/php
    $phpBinary = str_replace(['/sbin/php-fpm', '/sbin/php-fpm83'], '/bin/php', $phpBinary);
}
?>

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
                    <label class="block text-sm font-medium text-gray-700 mb-1">采集频率</label>
                    <select name="interval" class="w-full border rounded px-3 py-2">
                        <option value="30" <?= ($config['interval'] ?? 60) == 30 ? 'selected' : '' ?>>每30分钟</option>
                        <option value="60" <?= ($config['interval'] ?? 60) == 60 ? 'selected' : '' ?>>每1小时</option>
                        <option value="120" <?= ($config['interval'] ?? 60) == 120 ? 'selected' : '' ?>>每2小时</option>
                        <option value="360" <?= ($config['interval'] ?? 60) == 360 ? 'selected' : '' ?>>每6小时</option>
                        <option value="720" <?= ($config['interval'] ?? 60) == 720 ? 'selected' : '' ?>>每12小时</option>
                        <option value="1440" <?= ($config['interval'] ?? 60) == 1440 ? 'selected' : '' ?>>每24小时</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">宝塔定时任务的执行周期需与此一致</p>
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
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="download_pic" value="1" <?= !empty($config['download_pic']) ? 'checked' : '' ?> class="w-4 h-4 rounded">
                        <span class="text-sm font-medium text-gray-700">下载图片到本地</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">勾选后会下载海报图片到服务器，速度较慢</p>
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
                        <div id="collectList">
                        <?php foreach ($collects as $c): ?>
                        <label class="flex items-center gap-2 cursor-pointer <?= empty($selectedIds) ? 'opacity-50' : '' ?>">
                            <input type="checkbox" name="collect_ids[]" value="<?= $c['collect_id'] ?>" class="collect-check w-4 h-4 rounded"
                                <?= empty($selectedIds) ? 'checked disabled' : (in_array($c['collect_id'], $selectedIds) ? 'checked' : '') ?>>
                            <span class="text-sm"><?= htmlspecialchars($c['collect_name']) ?></span>
                            <?php if (!$c['collect_status']): ?>
                            <span class="text-xs text-gray-400">(已禁用)</span>
                            <?php endif; ?>
                        </label>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">选择"全部"则采集所有启用的采集站</p>
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
            <h3 class="font-bold mb-4">定时任务设置（宝塔面板）</h3>
            
            <div class="text-sm text-gray-600 mb-4 space-y-1">
                <p>1. 进入宝塔面板 → 计划任务 → 添加任务</p>
                <p>2. 任务类型选择：<span class="font-bold text-gray-800">Shell脚本</span></p>
                <p>3. 执行周期：<span class="font-bold text-gray-800">与左侧"执行间隔"保持一致</span></p>
                <p>4. 脚本内容粘贴下方命令：</p>
            </div>
            
            <div class="relative">
                <div id="cronCmd" class="bg-gray-900 rounded p-4 pr-12 font-mono text-sm text-green-400 overflow-x-auto"><?= $phpBinary ?> <?= ROOT_PATH ?>cron.php auto</div>
                <button onclick="copyCommand('cronCmd')" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-white rounded hover:bg-gray-700" title="复制命令">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>
            
            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-800">
                <p class="font-medium flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    重要说明：
                </p>
                <p class="mt-1">启用自动采集后，必须在宝塔面板添加定时任务才能生效。</p>
                <p class="mt-1">左侧"采集频率"与宝塔执行周期需保持一致，例如：</p>
                <p class="mt-1">• 采集频率选每1小时 → 宝塔设置每1小时执行</p>
                <p>• 采集频率选每6小时 → 宝塔设置每6小时执行</p>
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
                其他命令参考
            </h4>
            <div class="text-sm text-blue-700 space-y-2">
                <div class="flex items-center justify-between bg-blue-100 rounded px-2 py-1">
                    <code id="cmd1"><?= $phpBinary ?> <?= ROOT_PATH ?>cron.php collect</code>
                    <button onclick="copyCommand('cmd1')" class="ml-2 text-blue-600 hover:text-blue-800" title="复制">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-xs pl-2">采集所有启用的采集站</p>
                
                <div class="flex items-center justify-between bg-blue-100 rounded px-2 py-1">
                    <code id="cmd2"><?= $phpBinary ?> <?= ROOT_PATH ?>cron.php collect --hours=24</code>
                    <button onclick="copyCommand('cmd2')" class="ml-2 text-blue-600 hover:text-blue-800" title="复制">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-xs pl-2">只采24小时内更新</p>
                
                <div class="flex items-center justify-between bg-blue-100 rounded px-2 py-1">
                    <code id="cmd3"><?= $phpBinary ?> <?= ROOT_PATH ?>cron.php collect --id=1</code>
                    <button onclick="copyCommand('cmd3')" class="ml-2 text-blue-600 hover:text-blue-800" title="复制">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-xs pl-2">只采集指定采集站</p>
            </div>
        </div>
    </div>
</div>

<script>
function copyCommand(elementId) {
    const text = document.getElementById(elementId).textContent;
    navigator.clipboard.writeText(text).then(() => {
        xpkToast('已复制到剪贴板', 'success');
    }).catch(() => {
        // 降级方案
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        xpkToast('已复制到剪贴板', 'success');
    });
}

document.getElementById('selectAllCollects').addEventListener('change', function() {
    const collectList = document.getElementById('collectList');
    const checkboxes = document.querySelectorAll('.collect-check');
    if (this.checked) {
        // 全选：禁用并勾选所有
        collectList.classList.add('opacity-50');
        checkboxes.forEach(cb => {
            cb.checked = true;
            cb.disabled = true;
        });
    } else {
        // 取消全选：启用并取消勾选
        collectList.classList.remove('opacity-50');
        checkboxes.forEach(cb => {
            cb.checked = false;
            cb.disabled = false;
        });
    }
});

document.querySelectorAll('.collect-check').forEach(cb => {
    cb.addEventListener('change', function() {
        // 如果有任何一个被手动选中，取消"全部"
        const anyChecked = document.querySelectorAll('.collect-check:checked').length > 0;
        if (anyChecked) {
            document.getElementById('selectAllCollects').checked = false;
        }
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
