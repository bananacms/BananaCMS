<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🍌</text></svg>">
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
        // 全局后台入口变量
        window.ADMIN_ENTRY = '<?= $adminEntry ?>';
        // 全局CSRF Token
        window.CSRF_TOKEN = '<?= $csrfToken ?? '' ?>';
        
        // 辅助函数：构建后台URL
        window.adminUrl = function(path) {
            path = path.startsWith('/') ? path.substring(1) : path;
            return '/' + window.ADMIN_ENTRY + '?s=' + path;
        };
        
        // 页面加载完成后，自动替换所有硬编码的admin.php链接
        document.addEventListener('DOMContentLoaded', function() {
            // 替换所有链接 (旧格式 /admin.php/xxx 改为 /admin.php?s=xxx)
            document.querySelectorAll('a[href*="/admin.php/"]').forEach(function(link) {
                link.href = link.href.replace(/\/admin\.php\//, '/' + window.ADMIN_ENTRY + '?s=');
            });
            
            // 替换所有表单action
            document.querySelectorAll('form[action*="/admin.php/"]').forEach(function(form) {
                form.action = form.action.replace(/\/admin\.php\//, '/' + window.ADMIN_ENTRY + '?s=');
            });
        });
    </script>
    <style>
        .sidebar-link.active { background-color: #374151; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex">
        <!-- 侧边栏 -->
        <aside class="w-64 bg-gray-900 min-h-screen text-white fixed left-0 top-0 bottom-0 flex flex-col">
            <div class="p-4 border-b border-gray-800 flex-shrink-0">
                <h1 class="text-xl font-bold text-yellow-400 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.5 3C19.43 3 21 4.57 21 6.5C21 8.43 19.43 10 17.5 10C16.37 10 15.37 9.5 14.71 8.71L12 11.41L9.29 8.71C8.63 9.5 7.63 10 6.5 10C4.57 10 3 8.43 3 6.5C3 4.57 4.57 3 6.5 3C7.63 3 8.63 3.5 9.29 4.29L12 7L14.71 4.29C15.37 3.5 16.37 3 17.5 3M6.5 5C5.67 5 5 5.67 5 6.5C5 7.33 5.67 8 6.5 8C7.33 8 8 7.33 8 6.5C8 5.67 7.33 5 6.5 5M17.5 5C16.67 5 16 5.67 16 6.5C16 7.33 16.67 8 17.5 8C18.33 8 19 7.33 19 6.5C19 5.67 18.33 5 17.5 5M12 13.5C10.89 13.5 9.85 13.93 9.06 14.66L6.5 17.22C5.57 18.15 5.57 19.65 6.5 20.58C7.43 21.51 8.93 21.51 9.86 20.58L12 18.44L14.14 20.58C15.07 21.51 16.57 21.51 17.5 20.58C18.43 19.65 18.43 18.15 17.5 17.22L14.94 14.66C14.15 13.93 13.11 13.5 12 13.5Z"/>
                    </svg>
                    香蕉CMS
                </h1>
                <p class="text-xs text-gray-400 mt-1">轻量级影视CMS</p>
            </div>
            <nav class="flex-1 overflow-y-auto p-4 space-y-1">
                <a href="/<?= $adminEntry ?>?s=dashboard" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    仪表盘
                </a>
                <a href="/<?= $adminEntry ?>?s=vod" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 'vod') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    视频管理
                </a>
                <a href="/<?= $adminEntry ?>?s=type" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 'type') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                    </svg>
                    分类管理
                </a>
                <a href="/<?= $adminEntry ?>?s=actor" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 'actor') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    演员管理
                </a>
                <a href="/<?= $adminEntry ?>?s=art" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 'art') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    文章管理
                </a>
                <a href="/<?= $adminEntry ?>?s=user" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 'user') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    用户管理
                </a>
                <a href="/<?= $adminEntry ?>?s=collect" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 'collect') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                    </svg>
                    采集管理
                </a>
                <a href="/<?= $adminEntry ?>?s=ai" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 's=ai') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    AI 改写
                </a>
                <a href="/<?= $adminEntry ?>?s=transcode" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 'transcode') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    云转码
                </a>
                <a href="/<?= $adminEntry ?>?s=player" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 'player') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    播放器管理
                </a>
                <a href="/<?= $adminEntry ?>?s=link" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 'link') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                    友链管理
                </a>
                <a href="/<?= $adminEntry ?>?s=ad" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 's=ad') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                    </svg>
                    广告管理
                </a>
                <a href="/<?= $adminEntry ?>?s=comment" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 'comment') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    评论管理
                </a>
                <a href="/<?= $adminEntry ?>?s=short" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 'short') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    短视频
                </a>
                
                <!-- 支付管理分组 -->
                <div class="pt-2 mt-2 border-t border-gray-700">
                    <span class="px-4 text-xs text-gray-500 uppercase">支付管理</span>
                </div>
                <a href="/<?= $adminEntry ?>?s=payment" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 's=payment') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    支付通道
                </a>
                <a href="/<?= $adminEntry ?>?s=vip" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 's=vip') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                    VIP套餐
                </a>
                <a href="/<?= $adminEntry ?>?s=order" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 's=order') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    订单管理
                </a>
                
                <a href="/<?= $adminEntry ?>?s=stats" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 'stats') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    数据统计
                </a>
                <a href="/<?= $adminEntry ?>?s=log" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 's=log') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    操作日志
                </a>
                <a href="/<?= $adminEntry ?>?s=page" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 's=page') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    单页管理
                </a>
                <a href="/<?= $adminEntry ?>?s=config" class="sidebar-link block px-4 py-2 rounded hover:bg-gray-800 <?= strpos($_SERVER['REQUEST_URI'], 'config') !== false ? 'active' : '' ?>">
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    系统配置
                </a>
            </nav>
            <div class="flex-shrink-0 p-4 border-t border-gray-800">
                <div class="text-sm text-gray-400 mb-2">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <?= htmlspecialchars($admin['name'] ?? 'Admin') ?>
                </div>
                <a href="/<?= $adminEntry ?>?s=password" class="block text-center px-4 py-2 mb-2 bg-gray-700 rounded hover:bg-gray-600 text-sm">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    修改密码
                </a>
                <a href="/<?= $adminEntry ?>?s=logout" class="block text-center px-4 py-2 bg-red-600 rounded hover:bg-red-700 text-sm">
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
    
    // 别名，兼容旧代码
    function showToast(msg, type = 'success') {
        xpkToast(msg, type);
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
                body: 'id=' + id + '&_token=' + (window.CSRF_TOKEN || '')
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

    // 全局表单AJAX拦截器
    // 自动将POST表单转为AJAX提交，处理JSON响应
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form[method="POST"], form[method="post"]').forEach(function(form) {
            // 跳过已有onsubmit处理的表单
            if (form.hasAttribute('onsubmit') || form.dataset.noAjax) return;
            // 跳过文件上传表单（有file input的）
            if (form.querySelector('input[type="file"]:not(.hidden)')) return;
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                const originalText = submitBtn ? submitBtn.textContent : '';
                
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = '处理中...';
                }
                
                const formData = new FormData(form);
                
                fetch(form.action || window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(r => {
                    const contentType = r.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return r.json();
                    }
                    // 非JSON响应，可能是重定向或HTML
                    return r.text().then(text => {
                        // 如果是HTML，直接刷新页面
                        if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                            window.location.reload();
                            return null;
                        }
                        // 尝试解析为JSON
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            window.location.reload();
                            return null;
                        }
                    });
                })
                .then(data => {
                    if (!data) return;
                    
                    if (data.code === 0) {
                        xpkToast(data.msg || '操作成功', 'success');
                        // 如果返回了跳转URL
                        if (data.data && data.data.url) {
                            setTimeout(() => window.location.href = data.data.url, 500);
                        } else {
                            // 默认返回列表页或刷新
                            setTimeout(() => {
                                // 尝试找返回按钮的链接
                                const backLink = form.querySelector('a[href*="/' + window.ADMIN_ENTRY + '"]');
                                if (backLink) {
                                    window.location.href = backLink.href;
                                } else {
                                    window.location.reload();
                                }
                            }, 500);
                        }
                    } else {
                        xpkToast(data.msg || '操作失败', 'error');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalText;
                        }
                    }
                })
                .catch(err => {
                    console.error('Form submit error:', err);
                    xpkToast('请求失败，请重试', 'error');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                });
            });
        });
    });
    </script>
</body>
</html>
