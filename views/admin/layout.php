<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? '后台管理') ?> - <?= htmlspecialchars($siteName ?? '香蕉CMS') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#EF4444',
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar-link.active { background-color: #374151; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex">
        <!-- 侧边栏 -->
        <aside class="w-64 bg-gray-900 min-h-screen text-white fixed left-0 top-0 bottom-0">
            <div class="p-4 border-b border-gray-800">
                <h1 class="text-xl font-bold text-yellow-400">🍌 香蕉CMS</h1>
                <p class="text-xs text-gray-400 mt-1">轻量级影视CMS</p>
            </div>
            <nav class="p-4 space-y-1">
                <a href="/admin.php/dashboard" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'active' : '' ?>">
                    📊 仪表盘
                </a>
                <a href="/admin.php/vod" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/vod') !== false ? 'active' : '' ?>">
                    🎬 视频管理
                </a>
                <a href="/admin.php/type" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/type') !== false ? 'active' : '' ?>">
                    📁 分类管理
                </a>
                <a href="/admin.php/actor" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/actor') !== false ? 'active' : '' ?>">
                    👤 演员管理
                </a>
                <a href="/admin.php/art" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/art') !== false ? 'active' : '' ?>">
                    📝 文章管理
                </a>
                <a href="/admin.php/user" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/user') !== false ? 'active' : '' ?>">
                    👥 用户管理
                </a>
                <a href="/admin.php/collect" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/collect') !== false ? 'active' : '' ?>">
                    📥 采集管理
                </a>
                <a href="/admin.php/player" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/player') !== false ? 'active' : '' ?>">
                    ▶️ 播放器管理
                </a>
                <a href="/admin.php/link" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/link') !== false ? 'active' : '' ?>">
                    🔗 友链管理
                </a>
                <a href="/admin.php/ad" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/ad') !== false && strpos($_SERVER['REQUEST_URI'], '/admin') === false ? 'active' : '' ?>">
                    📢 广告管理
                </a>
                <a href="/admin.php/comment" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/comment') !== false ? 'active' : '' ?>">
                    💬 评论管理
                </a>
                <a href="/admin.php/short" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/short') !== false ? 'active' : '' ?>">
                    📱 短视频
                </a>
                <a href="/admin.php/stats" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/stats') !== false ? 'active' : '' ?>">
                    📊 数据统计
                </a>
                <a href="/admin.php/log" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/log') !== false ? 'active' : '' ?>">
                    📋 操作日志
                </a>
                <a href="/admin.php/page" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/page') !== false ? 'active' : '' ?>">
                    📄 单页管理
                </a>
                <a href="/admin.php/config" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], '/config') !== false ? 'active' : '' ?>">
                    ⚙️ 系统配置
                </a>
            </nav>
            <div class="absolute bottom-0 w-64 p-4 border-t border-gray-800">
                <div class="text-sm text-gray-400 mb-2">
                    👋 <?= htmlspecialchars($admin['name'] ?? 'Admin') ?>
                </div>
                <a href="/admin.php/logout" class="block text-center px-4 py-2 bg-red-600 rounded hover:bg-red-700 text-sm">
                    退出登录
                </a>
            </div>
        </aside>

        <!-- 主内容区 -->
        <main class="flex-1 ml-64 p-6">
            <?= $content ?>
        </main>
    </div>

    <script>
    // Toast提示
    function xpkToast(msg, type = 'success') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded shadow-lg z-50 transition-opacity`;
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // 确认框（美化版）
    function xpkConfirm(msg, callback, cancelCallback) {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
        overlay.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl max-w-sm w-full mx-4 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <svg class="w-6 h-6 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <h3 class="text-lg font-medium">确认操作</h3>
                    </div>
                    <p class="text-gray-600">${msg}</p>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-3">
                    <button class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded" onclick="this.closest('.fixed').remove()">取消</button>
                    <button class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600" id="xpkConfirmBtn">确定</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
        overlay.querySelector('#xpkConfirmBtn').onclick = function() {
            overlay.remove();
            if (callback) callback();
        };
        overlay.onclick = function(e) {
            if (e.target === overlay) {
                overlay.remove();
                if (cancelCallback) cancelCallback();
            }
        };
    }

    // Alert提示（美化版）
    function xpkAlert(msg, type = 'info', callback) {
        const icons = {
            success: '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
            error: '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
            warning: '<svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
            info: '<svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        };
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
        overlay.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl max-w-sm w-full mx-4 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        ${icons[type] || icons.info}
                        <h3 class="text-lg font-medium ml-3">提示</h3>
                    </div>
                    <p class="text-gray-600">${msg}</p>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end">
                    <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600" onclick="this.closest('.fixed').remove()">确定</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
        if (callback) {
            overlay.querySelector('button').onclick = function() {
                overlay.remove();
                callback();
            };
        }
    }

    // AJAX删除
    function deleteItem(url, id, callback) {
        xpkConfirm('确定要删除吗？', function() {
            fetch(url, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + id
            })
            .then(r => r.json())
            .then(data => {
                if (data.code === 0) {
                    xpkToast(data.msg, 'success');
                    if (callback) callback();
                    else location.reload();
                } else {
                    xpkToast(data.msg, 'error');
                }
            });
        });
    }

    // 分页跳转
    function goToPage(baseUrl, totalPages) {
        const input = document.getElementById('pageJumpInput');
        let page = parseInt(input.value);
        if (isNaN(page) || page < 1) page = 1;
        if (page > totalPages) page = totalPages;
        const separator = baseUrl.includes('?') ? '&' : '?';
        window.location.href = baseUrl + separator + 'page=' + page;
    }
    </script>
</body>
</html>
