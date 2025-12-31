<h1 class="text-2xl font-bold mb-6">执行采集 - <?= htmlspecialchars($collect['collect_name']) ?></h1>

<?php 
try {
    require_once MODEL_PATH . 'CollectBind.php';
    $bindModel = new XpkCollectBind();
    $binds = $bindModel->getBinds($collect['collect_id']);
    $bindCount = count(array_filter($binds));
} catch (Exception $e) {
    $binds = [];
    $bindCount = 0;
}
$lastProgress = !empty($collect['collect_progress']) ? json_decode($collect['collect_progress'], true) : null;
$canResume = $lastProgress && !empty($lastProgress['page']) && $lastProgress['page'] > 1 && empty($lastProgress['done']);
?>

<?php if ($bindCount == 0): ?>
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <p class="text-red-800 font-medium">⚠️ 尚未绑定任何分类，采集将无法进行！</p>
    <p class="text-red-600 text-sm mt-1">请先 <a href="/admin.php/collect/bind/<?= $collect['collect_id'] ?>" class="underline">绑定分类</a> 后再执行采集</p>
</div>
<?php else: ?>
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
    <p class="text-green-800">✅ 已绑定 <?= $bindCount ?> 个分类，可以开始采集</p>
</div>
<?php endif; ?>

<?php if ($canResume): ?>
<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-yellow-800 font-medium">📌 检测到未完成的采集任务</p>
            <p class="text-yellow-700 text-sm mt-1">
                上次采集到第 <?= $lastProgress['page'] ?>/<?= $lastProgress['pagecount'] ?? '?' ?> 页
                <?php if (!empty($lastProgress['time'])): ?>
                （<?= date('Y-m-d H:i', $lastProgress['time']) ?>）
                <?php endif; ?>
                <?php if (!empty($lastProgress['type_id'])): ?>
                ，分类ID: <?= $lastProgress['type_id'] ?>
                <?php endif; ?>
                <?php if (!empty($lastProgress['mode'])): ?>
                ，模式: <?= $lastProgress['mode'] == 'add' ? '只采新' : ($lastProgress['mode'] == 'update' ? '只更新' : '全部') ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="flex gap-2">
            <button onclick="resumeCollect()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded font-bold">
                ▶️ 继续采集
            </button>
            <button onclick="clearProgress()" class="bg-gray-400 hover:bg-gray-500 text-white px-3 py-2 rounded text-sm">
                清除进度
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- 采集设置 -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-bold mb-4">采集设置</h3>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">采集分类</label>
                <select id="typeId" class="w-full border rounded px-3 py-2">
                    <option value="0">全部分类</option>
                    <?php foreach ($remoteCategories as $cat): ?>
                    <?php $isBound = isset($binds[$cat['id']]); ?>
                    <option value="<?= $cat['id'] ?>" <?= !$isBound ? 'disabled' : '' ?>
                        <?= ($lastProgress['type_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?><?= !$isBound ? ' (未绑定)' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">采集模式</label>
                <select id="mode" class="w-full border rounded px-3 py-2">
                    <option value="add" <?= ($lastProgress['mode'] ?? 'add') == 'add' ? 'selected' : '' ?>>只采新数据</option>
                    <option value="update" <?= ($lastProgress['mode'] ?? '') == 'update' ? 'selected' : '' ?>>只更新已有</option>
                    <option value="all" <?= ($lastProgress['mode'] ?? '') == 'all' ? 'selected' : '' ?>>全部采集</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">时间范围</label>
                <select id="hours" class="w-full border rounded px-3 py-2">
                    <option value="" <?= empty($lastProgress['hours']) ? 'selected' : '' ?>>不限</option>
                    <option value="1" <?= ($lastProgress['hours'] ?? '') == '1' ? 'selected' : '' ?>>1小时内</option>
                    <option value="6" <?= ($lastProgress['hours'] ?? '') == '6' ? 'selected' : '' ?>>6小时内</option>
                    <option value="12" <?= ($lastProgress['hours'] ?? '') == '12' ? 'selected' : '' ?>>12小时内</option>
                    <option value="24" <?= ($lastProgress['hours'] ?? '') == '24' ? 'selected' : '' ?>>24小时内</option>
                    <option value="72" <?= ($lastProgress['hours'] ?? '') == '72' ? 'selected' : '' ?>>3天内</option>
                    <option value="168" <?= ($lastProgress['hours'] ?? '') == '168' ? 'selected' : '' ?>>7天内</option>
                </select>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="downloadPic" class="w-4 h-4 rounded" <?= !empty($lastProgress['download_pic']) ? 'checked' : '' ?>>
                    <span class="text-sm font-medium text-gray-700">下载图片到本地</span>
                </label>
                <p class="text-xs text-gray-500 mt-1">勾选后会下载海报图片到服务器，速度较慢</p>
            </div>

            <button onclick="startCollect()" id="startBtn" class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded font-bold <?= $bindCount == 0 ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= $bindCount == 0 ? 'disabled' : '' ?>>
                🚀 从头开始采集
            </button>
            
            <button onclick="stopCollect()" id="stopBtn" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded font-bold hidden">
                ⏹ 停止采集
            </button>
        </div>
    </div>

    <!-- 采集进度 -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
        <h3 class="font-bold mb-4">采集进度</h3>
        
        <div class="mb-4">
            <div class="flex justify-between text-sm mb-1">
                <span>进度</span>
                <span id="progressText">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div id="progressBar" class="bg-green-500 h-3 rounded-full transition-all" style="width: 0%"></div>
            </div>
        </div>

        <div class="grid grid-cols-5 gap-3 mb-4">
            <div class="bg-blue-50 rounded p-3 text-center">
                <p class="text-2xl font-bold text-blue-600" id="statPage">0</p>
                <p class="text-xs text-gray-500">当前页</p>
            </div>
            <div class="bg-green-50 rounded p-3 text-center">
                <p class="text-2xl font-bold text-green-600" id="statAdded">0</p>
                <p class="text-xs text-gray-500">新增</p>
            </div>
            <div class="bg-orange-50 rounded p-3 text-center">
                <p class="text-2xl font-bold text-orange-600" id="statUpdated">0</p>
                <p class="text-xs text-gray-500">更新</p>
            </div>
            <div class="bg-gray-50 rounded p-3 text-center">
                <p class="text-2xl font-bold text-gray-600" id="statSkipped">0</p>
                <p class="text-xs text-gray-500">跳过</p>
            </div>
            <div class="bg-purple-50 rounded p-3 text-center">
                <p class="text-2xl font-bold text-purple-600" id="statEta">--</p>
                <p class="text-xs text-gray-500">预计剩余</p>
            </div>
        </div>

        <div class="bg-gray-900 rounded p-4 h-64 overflow-y-auto font-mono text-sm" id="logBox">
            <p class="text-gray-500">等待开始...</p>
        </div>
    </div>
</div>

<script>
let collecting = false;
let totalAdded = 0;
let totalUpdated = 0;
let totalSkipped = 0;
let startTime = 0;
let startPage = 1;
let totalPages = 0;
let abortController = null;
let currentLogId = 0;

const lastProgress = <?= json_encode($lastProgress) ?>;

function log(msg, type = 'info') {
    const box = document.getElementById('logBox');
    const colors = {info: 'text-gray-300', success: 'text-green-400', error: 'text-red-400', warning: 'text-yellow-400'};
    const time = new Date().toLocaleTimeString();
    box.innerHTML += `<p class="${colors[type]}">[${time}] ${msg}</p>`;
    box.scrollTop = box.scrollHeight;
}

function formatTime(seconds) {
    if (seconds < 60) return Math.round(seconds) + '秒';
    if (seconds < 3600) return Math.round(seconds / 60) + '分钟';
    const hours = seconds / 3600;
    if (hours < 24) return Math.round(hours * 10) / 10 + '小时';
    const days = Math.floor(hours / 24);
    const remainHours = Math.round((hours % 24) * 10) / 10;
    if (remainHours > 0) return days + '天' + remainHours + '小时';
    return days + '天';
}

function updateEta(currentPage, pagecount) {
    if (currentPage <= startPage || pagecount <= 0) {
        document.getElementById('statEta').textContent = '--';
        return;
    }
    const elapsed = (Date.now() - startTime) / 1000;
    const pagesCompleted = currentPage - startPage;
    const pagesRemaining = pagecount - currentPage;
    const avgTimePerPage = elapsed / pagesCompleted;
    const eta = pagesRemaining * avgTimePerPage;
    document.getElementById('statEta').textContent = formatTime(eta);
}

function startCollect() {
    beginCollect(1);
}

function resumeCollect() {
    if (!lastProgress || !lastProgress.page) {
        startCollect();
        return;
    }
    
    // 恢复上次的设置
    if (lastProgress.type_id) document.getElementById('typeId').value = lastProgress.type_id;
    if (lastProgress.mode) document.getElementById('mode').value = lastProgress.mode;
    if (lastProgress.hours) document.getElementById('hours').value = lastProgress.hours;
    if (lastProgress.download_pic) document.getElementById('downloadPic').checked = true;
    
    // 恢复统计数据
    totalAdded = lastProgress.total_added || 0;
    totalUpdated = lastProgress.total_updated || 0;
    totalSkipped = lastProgress.total_skipped || 0;
    
    beginCollect(lastProgress.page);
}

function clearProgress() {
    xpkConfirm('确定要清除采集进度吗？', function() {
        fetch('/admin.php/collect/clearProgress', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=<?= $collect['collect_id'] ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.code === 0) {
                xpkToast('进度已清除', 'success');
                location.reload();
            } else {
                xpkToast(data.msg, 'error');
            }
        });
    });
}

function beginCollect(fromPage) {
    if (collecting) return;
    collecting = true;
    
    if (fromPage === 1) {
        totalAdded = 0;
        totalUpdated = 0;
        totalSkipped = 0;
        currentLogId = 0;
    } else {
        currentLogId = lastProgress?.log_id || 0;
    }
    
    startTime = Date.now();
    startPage = fromPage;
    totalPages = lastProgress?.pagecount || 0;
    
    document.getElementById('startBtn').classList.add('hidden');
    document.getElementById('stopBtn').classList.remove('hidden');
    document.getElementById('logBox').innerHTML = '';
    document.getElementById('statEta').textContent = '计算中...';
    document.getElementById('statAdded').textContent = totalAdded;
    document.getElementById('statUpdated').textContent = totalUpdated;
    document.getElementById('statSkipped').textContent = totalSkipped;
    
    log(fromPage > 1 ? `从第 ${fromPage} 页继续采集...` : '开始采集...', 'info');
    doCollect(fromPage);
}

function stopCollect() {
    collecting = false;
    if (abortController) {
        abortController.abort();
        abortController = null;
    }
    document.getElementById('startBtn').classList.remove('hidden');
    document.getElementById('stopBtn').classList.add('hidden');
    document.getElementById('statEta').textContent = '--';
    log('采集已停止，可点击"继续采集"恢复', 'warning');
}

function doCollect(page) {
    if (!collecting) return;
    
    abortController = new AbortController();
    
    const data = new URLSearchParams({
        id: <?= $collect['collect_id'] ?>,
        page: page,
        type_id: document.getElementById('typeId').value,
        mode: document.getElementById('mode').value,
        hours: document.getElementById('hours').value,
        download_pic: document.getElementById('downloadPic').checked ? 1 : 0,
        total_added: totalAdded,
        total_updated: totalUpdated,
        total_skipped: totalSkipped,
        log_id: currentLogId
    });
    
    fetch('/admin.php/collect/docollect', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: data,
        signal: abortController.signal
    })
    .then(r => r.json())
    .then(res => {
        if (!collecting) return;
        
        if (res.code !== 0) {
            log(res.msg, 'error');
            stopCollect();
            return;
        }
        
        const d = res.data;
        totalAdded += d.added;
        totalUpdated += d.updated;
        totalSkipped += d.skipped || 0;
        if (d.pagecount > 0) totalPages = d.pagecount;
        if (d.log_id) currentLogId = d.log_id;
        
        document.getElementById('statPage').textContent = d.page + '/' + (totalPages || '?');
        document.getElementById('statAdded').textContent = totalAdded;
        document.getElementById('statUpdated').textContent = totalUpdated;
        document.getElementById('statSkipped').textContent = totalSkipped;
        
        const progress = totalPages > 0 ? Math.round(d.page / totalPages * 100) : 0;
        document.getElementById('progressBar').style.width = progress + '%';
        document.getElementById('progressText').textContent = progress + '%';
        
        updateEta(d.page, totalPages);
        
        log(`第 ${d.page}/${totalPages || '?'} 页，新增 ${d.added}，更新 ${d.updated}，跳过 ${d.skipped || 0}`, 'success');
        
        if (d.done) {
            const elapsed = formatTime((Date.now() - startTime) / 1000);
            log(`采集完成！共新增 ${totalAdded}，更新 ${totalUpdated}，跳过 ${totalSkipped}，耗时 ${elapsed}`, 'success');
            document.getElementById('statEta').textContent = '完成';
            collecting = false;
            document.getElementById('startBtn').classList.remove('hidden');
            document.getElementById('stopBtn').classList.add('hidden');
        } else {
            setTimeout(() => doCollect(page + 1), 500);
        }
    })
    .catch(err => {
        if (err.name === 'AbortError') return;
        log('请求失败: ' + err, 'error');
        stopCollect();
    });
}
</script>
