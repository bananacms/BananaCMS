<?php
// 获取 PHP CLI 路径
$phpBinary = PHP_BINARY;
// 如果是 php-fpm，尝试找到对应的 php cli
if (strpos($phpBinary, 'php-fpm') !== false || strpos($phpBinary, 'fpm') !== false) {
    // 宝塔面板常见路径
    $possiblePaths = [
        dirname(dirname($phpBinary)) . '/bin/php',  // /www/server/php/82/bin/php
        '/usr/bin/php',
        '/usr/local/bin/php',
    ];
    foreach ($possiblePaths as $path) {
        if (file_exists($path) && is_executable($path)) {
            $phpBinary = $path;
            break;
        }
    }
}
?>

<?php if (!empty($flash)): ?>
<div class="mb-4 px-4 py-3 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<h1 class="text-2xl font-bold mb-6">AI 内容改写</h1>

<!-- 统计卡片 -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">视频总数</div>
        <div class="text-2xl font-bold text-gray-800"><?= number_format($stats['total']) ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">待处理</div>
        <div class="text-2xl font-bold text-blue-600"><?= number_format($stats['pending']) ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">已改写</div>
        <div class="text-2xl font-bold text-green-600"><?= number_format($stats['rewritten']) ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">改写失败</div>
        <div class="text-2xl font-bold text-red-600"><?= number_format($stats['failed']) ?></div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">

    <!-- API 配置 -->
    <div class="mb-8">
        <h3 class="font-bold text-gray-700 border-b pb-2 mb-4 flex items-center justify-between">
            <span>API 配置</span>
            <label class="flex items-center cursor-pointer">
                <span class="mr-2 text-sm font-normal text-gray-600">启用 AI 改写</span>
                <input type="checkbox" id="ai_enabled" <?= ($config['ai_enabled'] ?? '0') === '1' ? 'checked' : '' ?> 
                    class="w-5 h-5 text-blue-600 rounded">
            </label>
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">API 地址</label>
                <input type="text" id="ai_api_url" value="<?= htmlspecialchars($config['ai_api_url'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="https://api.openai.com/v1">
                <p class="text-xs text-gray-500 mt-1">支持 OpenAI 兼容格式的 API</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                <input type="password" id="ai_api_key" value="<?= htmlspecialchars($config['ai_api_key'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="sk-xxx">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">模型名称</label>
                <input type="text" id="ai_model" value="<?= htmlspecialchars($config['ai_model'] ?? 'gpt-4o-mini') ?>"
                    class="w-full border rounded px-3 py-2" placeholder="gpt-4o-mini">
                <p class="text-xs text-gray-500 mt-1">如：gpt-4o-mini、deepseek-chat、qwen-turbo</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">每批处理数量</label>
                <input type="number" id="ai_batch_size" value="<?= htmlspecialchars($config['ai_batch_size'] ?? '10') ?>"
                    class="w-full border rounded px-3 py-2" min="1" max="50">
                <p class="text-xs text-gray-500 mt-1">定时任务每次处理的视频数量</p>
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            <button onclick="testApi()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                测试连接
            </button>
            <button onclick="saveConfig()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                保存配置
            </button>
        </div>
    </div>

    <!-- 高级参数 -->
    <div class="mb-8">
        <h3 class="font-bold text-gray-700 border-b pb-2 mb-4 cursor-pointer flex items-center justify-between" onclick="toggleAdvanced()">
            <span>高级参数</span>
            <svg id="advancedIcon" class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </h3>
        
        <div id="advancedSection" class="hidden space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Temperature</label>
                    <input type="number" id="ai_temperature" value="<?= htmlspecialchars($config['ai_temperature'] ?? '0.8') ?>"
                        class="w-full border rounded px-3 py-2" min="0" max="2" step="0.1">
                    <p class="text-xs text-gray-500 mt-1">0-2，越高越随机</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Tokens</label>
                    <input type="number" id="ai_max_tokens" value="<?= htmlspecialchars($config['ai_max_tokens'] ?? '500') ?>"
                        class="w-full border rounded px-3 py-2" min="100" max="4000">
                    <p class="text-xs text-gray-500 mt-1">最大输出长度</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">超时时间（秒）</label>
                    <input type="number" id="ai_timeout" value="<?= htmlspecialchars($config['ai_timeout'] ?? '30') ?>"
                        class="w-full border rounded px-3 py-2" min="10" max="120">
                </div>
            </div>
        </div>
    </div>

    <!-- Prompt 配置 -->
    <div class="mb-8">
        <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">Prompt 配置</h3>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    系统提示词 (System Prompt)
                    <span class="text-gray-400 font-normal">- 设定 AI 的角色和风格</span>
                </label>
                <textarea id="ai_system_prompt" rows="3" class="w-full border rounded px-3 py-2"
                    placeholder="你是一位资深的影视编辑..."><?= htmlspecialchars($config['ai_system_prompt'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    用户提示词 (User Prompt)
                    <span class="text-gray-400 font-normal">- 具体的改写指令，用 {content} 表示原文</span>
                </label>
                <textarea id="ai_user_prompt" rows="6" class="w-full border rounded px-3 py-2"
                    placeholder="请用你自己的话重新描述以下影视简介..."><?= htmlspecialchars($config['ai_user_prompt'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">提示：好的 Prompt 应该明确要求避免 AI 味，不要用套话开头</p>
            </div>
        </div>
    </div>

    <!-- 操作按钮 -->
    <div class="border-t pt-6">
        <h3 class="font-bold text-gray-700 mb-4">手动执行</h3>
        <div class="flex flex-wrap gap-3">
            <button onclick="runRewrite()" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                执行一批改写
            </button>
            <button onclick="resetFailed()" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                重置失败状态
            </button>
            <button onclick="resetAll()" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                重置全部状态
            </button>
        </div>
        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm">
            <p class="font-medium text-yellow-800 mb-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                定时任务配置（宝塔/服务器）
            </p>
            <p class="text-yellow-700 mb-2">命令必须使用<span class="font-bold">绝对路径</span>，否则会报错 "Could not open input file"</p>
            <div class="relative">
                <code id="aiCronCmd" class="block bg-yellow-100 px-3 py-2 pr-10 rounded text-yellow-900"><?= $phpBinary ?> <?= ROOT_PATH ?>cron.php ai_rewrite</code>
                <button onclick="copyAiCmd()" class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-yellow-700 hover:text-yellow-900 rounded hover:bg-yellow-200" title="复制命令">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 测试结果弹窗 -->
<div id="testModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto">
        <div class="p-6">
            <h3 class="text-lg font-bold mb-4">API 测试结果</h3>
            <div id="testResult"></div>
            <div class="mt-4 text-right">
                <button onclick="closeTestModal()" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">关闭</button>
            </div>
        </div>
    </div>
</div>

<script>
const adminEntry = '<?= $adminEntry ?>';

function copyAiCmd() {
    const text = document.getElementById('aiCronCmd').textContent;
    navigator.clipboard.writeText(text).then(() => {
        showToast('已复制到剪贴板', 'success');
    }).catch(() => {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToast('已复制到剪贴板', 'success');
    });
}

function getFormData() {
    return {
        _token: document.getElementById('csrfToken').value,
        ai_enabled: document.getElementById('ai_enabled').checked ? '1' : '0',
        ai_api_url: document.getElementById('ai_api_url').value,
        ai_api_key: document.getElementById('ai_api_key').value,
        ai_model: document.getElementById('ai_model').value,
        ai_system_prompt: document.getElementById('ai_system_prompt').value,
        ai_user_prompt: document.getElementById('ai_user_prompt').value,
        ai_temperature: document.getElementById('ai_temperature').value,
        ai_max_tokens: document.getElementById('ai_max_tokens').value,
        ai_timeout: document.getElementById('ai_timeout').value,
        ai_batch_size: document.getElementById('ai_batch_size').value,
    };
}

function toggleAdvanced() {
    const section = document.getElementById('advancedSection');
    const icon = document.getElementById('advancedIcon');
    section.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}

function saveConfig() {
    const data = getFormData();
    
    fetch('/' + adminEntry + '/ai/save', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(r => r.json())
    .then(res => {
        if (res.code === 0) {
            showToast(res.msg, 'success');
        } else {
            showToast(res.msg, 'error');
        }
    })
    .catch(() => showToast('请求失败', 'error'));
}

function testApi() {
    const data = getFormData();
    
    showToast('正在测试...', 'info');
    
    fetch('/' + adminEntry + '/ai/test', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(r => r.json())
    .then(res => {
        const modal = document.getElementById('testModal');
        const result = document.getElementById('testResult');
        
        if (res.code === 0) {
            result.innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded p-4 mb-4">
                    <div class="flex items-center text-green-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        ${res.msg} (${res.data.duration})
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <div class="text-sm font-medium text-gray-500 mb-1">原文：</div>
                        <div class="bg-gray-50 rounded p-3 text-sm">${res.data.original}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500 mb-1">改写后：</div>
                        <div class="bg-blue-50 rounded p-3 text-sm">${res.data.rewritten}</div>
                    </div>
                </div>
            `;
        } else {
            result.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded p-4">
                    <div class="flex items-center text-red-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        ${res.msg}
                    </div>
                </div>
            `;
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    })
    .catch(() => showToast('请求失败', 'error'));
}

function closeTestModal() {
    const modal = document.getElementById('testModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function runRewrite() {
    xpkConfirm('确定要执行一批改写任务吗？', function() {
        showToast('正在执行...', 'info');
        
        fetch('/' + adminEntry + '/ai/run', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=' + document.getElementById('csrfToken').value
        })
        .then(r => r.json())
        .then(res => {
            if (res.code === 0) {
                showToast(res.msg, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(res.msg, 'error');
            }
        })
        .catch(() => showToast('请求失败', 'error'));
    });
}

function resetFailed() {
    xpkConfirm('确定要重置所有失败状态吗？', function() {
        fetch('/' + adminEntry + '/ai/reset', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=' + document.getElementById('csrfToken').value + '&type=failed'
        })
        .then(r => r.json())
        .then(res => {
            showToast(res.msg, res.code === 0 ? 'success' : 'error');
            if (res.code === 0) setTimeout(() => location.reload(), 1000);
        })
        .catch(() => showToast('请求失败', 'error'));
    });
}

function resetAll() {
    xpkConfirm('确定要重置全部改写状态吗？这将导致所有视频重新改写！', function() {
        fetch('/' + adminEntry + '/ai/reset', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=' + document.getElementById('csrfToken').value + '&type=all'
        })
        .then(r => r.json())
        .then(res => {
            showToast(res.msg, res.code === 0 ? 'success' : 'error');
            if (res.code === 0) setTimeout(() => location.reload(), 1000);
        })
        .catch(() => showToast('请求失败', 'error'));
    });
}
</script>
