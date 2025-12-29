<h1 class="text-2xl font-bold mb-6">执行采集 - <?= htmlspecialchars($collect['collect_name']) ?></h1>

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
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">采集模式</label>
                <select id="mode" class="w-full border rounded px-3 py-2">
                    <option value="add">只采新数据</option>
                    <option value="update">只更新已有</option>
                    <option value="all">全部采集</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">时间范围</label>
                <select id="hours" class="w-full border rounded px-3 py-2">
                    <option value="">不限</option>
                    <option value="1">1小时内</option>
                    <option value="6">6小时内</option>
                    <option value="12">12小时内</option>
                    <option value="24">24小时内</option>
                    <option value="72">3天内</option>
                    <option value="168">7天内</option>
                </select>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="downloadPic" class="w-4 h-4 rounded">
                    <span class="text-sm font-medium text-gray-700">下载图片到本地</span>
                </label>
                <p class="text-xs text-gray-500 mt-1">勾选后会下载海报图片到服务器，速度较慢</p>
            </div>

            <button onclick="startCollect()" id="startBtn" class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded font-bold">
                🚀 开始采集
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

        <div class="grid grid-cols-3 gap-4 mb-4">
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

function log(msg, type = 'info') {
    const box = document.getElementById('logBox');
    const colors = {info: 'text-gray-300', success: 'text-green-400', error: 'text-red-400', warning: 'text-yellow-400'};
    const time = new Date().toLocaleTimeString();
    box.innerHTML += `<p class="${colors[type]}">[${time}] ${msg}</p>`;
    box.scrollTop = box.scrollHeight;
}

function startCollect() {
    if (collecting) return;
    collecting = true;
    totalAdded = 0;
    totalUpdated = 0;
    
    document.getElementById('startBtn').classList.add('hidden');
    document.getElementById('stopBtn').classList.remove('hidden');
    document.getElementById('logBox').innerHTML = '';
    
    log('开始采集...', 'info');
    doCollect(1);
}

function stopCollect() {
    collecting = false;
    document.getElementById('startBtn').classList.remove('hidden');
    document.getElementById('stopBtn').classList.add('hidden');
    log('采集已停止', 'warning');
}

function doCollect(page) {
    if (!collecting) return;
    
    const data = new URLSearchParams({
        id: <?= $collect['collect_id'] ?>,
        page: page,
        type_id: document.getElementById('typeId').value,
        mode: document.getElementById('mode').value,
        hours: document.getElementById('hours').value,
        download_pic: document.getElementById('downloadPic').checked ? 1 : 0
    });
    
    fetch('/admin.php/collect/docollect', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: data
    })
    .then(r => r.json())
    .then(res => {
        if (res.code !== 0) {
            log(res.msg, 'error');
            stopCollect();
            return;
        }
        
        const d = res.data;
        totalAdded += d.added;
        totalUpdated += d.updated;
        
        document.getElementById('statPage').textContent = d.page;
        document.getElementById('statAdded').textContent = totalAdded;
        document.getElementById('statUpdated').textContent = totalUpdated;
        
        const progress = d.pagecount > 0 ? Math.round(d.page / d.pagecount * 100) : 100;
        document.getElementById('progressBar').style.width = progress + '%';
        document.getElementById('progressText').textContent = progress + '%';
        
        log(`第 ${d.page}/${d.pagecount || '?'} 页，新增 ${d.added}，更新 ${d.updated}`, 'success');
        
        if (d.done) {
            log(`采集完成！共新增 ${totalAdded}，更新 ${totalUpdated}`, 'success');
            stopCollect();
        } else {
            setTimeout(() => doCollect(page + 1), 500);
        }
    })
    .catch(err => {
        log('请求失败: ' + err, 'error');
        stopCollect();
    });
}
</script>
