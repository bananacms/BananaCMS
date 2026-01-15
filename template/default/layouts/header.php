<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? $siteName ?? SITE_NAME); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars($keywords ?? $siteKeywords ?? SITE_KEYWORDS); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($description ?? $siteDescription ?? SITE_DESCRIPTION); ?>">
    <?php if (isset($csrfToken)): ?><meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>"><?php endif; ?>
    <link rel="icon" href="/static/favicon.svg" type="image/svg+xml">
    <?php if (!empty($noindex)): ?><meta name="robots" content="noindex, nofollow"><?php endif; ?>
    
    <!-- IE11兼容性检测和提示 -->
    <!--[if IE]>
    <div class="browser-warning" style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:12px 16px;margin:10px;border-radius:6px;text-align:center;">
        <strong>⚠️ 浏览器版本过旧</strong><br>
        您的浏览器版本过旧，可能无法正常显示网站内容。建议升级到最新版本的 Chrome、Firefox、Safari 或 Edge 浏览器以获得最佳体验。
    </div>
    <![endif]-->
    
    <!-- 优化的CSS加载 -->
    <link rel="preload" href="https://cdn.tailwindcss.com" as="script">
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <script src="https://cdn.tailwindcss.com" defer></script>
    
    <!-- 本地CSS优先加载 -->
    <link rel="stylesheet" href="/static/css/xpk.css">
    
    <!-- 内联关键CSS -->
    <style>
        /* 关键样式内联以避免FOUC */
        body { 
            background-color: #f8fafc; 
            font-family: system-ui, -apple-system, sans-serif;
        }
        .card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .card { transition: box-shadow 0.3s; }
        .scroll-hide::-webkit-scrollbar { display: none; }
        .scroll-hide { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* 加载状态样式 */
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        /* CSS变量兼容性 - 提供备用值 */
        .text-primary {
            color: #3b82f6; /* 备用值 */
            color: var(--color-primary, #3b82f6);
        }
        .bg-primary {
            background-color: #3b82f6; /* 备用值 */
            background-color: var(--color-primary, #3b82f6);
        }
        .text-secondary {
            color: #6b7280; /* 备用值 */
            color: var(--color-secondary, #6b7280);
        }
        
        /* Grid布局兼容性 - 提供Flexbox备用方案 */
        .grid-fallback {
            display: flex;
            flex-wrap: wrap;
        }
        .grid-fallback > * {
            flex: 1 1 300px;
            margin: 8px;
        }
        
        /* 现代浏览器使用Grid */
        @supports (display: grid) {
            .grid-fallback {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 16px;
            }
            .grid-fallback > * {
                margin: 0;
            }
        }
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
        }
        .browser-warning a {
            color: #dc2626;
            text-decoration: underline;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen pb-16 md:pb-0">
    <!-- IE11兼容性提示 -->
    <!--[if IE]>
    <div class="browser-warning">
        <strong>浏览器兼容性提示：</strong>您当前使用的浏览器版本过旧，可能无法正常显示网站内容。
        建议升级到 <a href="https://www.microsoft.com/edge" target="_blank">Microsoft Edge</a>、
        <a href="https://www.google.com/chrome" target="_blank">Chrome</a> 或 
        <a href="https://www.mozilla.org/firefox" target="_blank">Firefox</a> 最新版本以获得最佳体验。
    </div>
    <![endif]-->
    
    <script>
    // JavaScript检测旧版浏览器
    (function() {
        var isOldBrowser = false;
        
        // 检测IE11及以下
        if (navigator.userAgent.indexOf('MSIE') !== -1 || navigator.userAgent.indexOf('Trident/') !== -1) {
            isOldBrowser = true;
        }
        
        // 检测CSS Grid支持
        if (!window.CSS || !CSS.supports || !CSS.supports('display', 'grid')) {
            isOldBrowser = true;
        }
        
        if (isOldBrowser && !sessionStorage.getItem('browserWarningShown')) {
            var warning = document.createElement('div');
            warning.className = 'browser-warning';
            warning.innerHTML = '<strong>兼容性提示：</strong>检测到您的浏览器可能不支持某些现代功能，建议升级浏览器以获得最佳体验。 <button onclick="this.parentNode.remove(); sessionStorage.setItem(\'browserWarningShown\', \'1\');" style="float:right; background:none; border:none; color:#dc2626; cursor:pointer;">×</button>';
            document.body.insertBefore(warning, document.body.firstChild);
        }
    })();
    </script>
    
    <!-- 跳转链接 -->
    <a href="#main-content" class="skip-link">跳转到主要内容</a>
    <a href="#main-nav" class="skip-link">跳转到导航</a>
    <!-- 顶部导航 -->
    <nav class="bg-white shadow-sm sticky top-0 z-50" id="main-nav" role="navigation" aria-label="主导航">
        <div class="max-w-7xl mx-auto px-4">
            <div class="h-16 flex items-center justify-between">
                <div class="flex items-center space-x-4 md:space-x-8">
                    <!-- 移动端菜单按钮 -->
                    <button class="md:hidden text-gray-600 p-2" 
                            onclick="toggleMobileMenu()"
                            aria-label="打开菜单"
                            aria-expanded="false"
                            aria-controls="mobileMenu"
                            id="mobileMenuBtn">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" role="img" aria-label="菜单图标">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <a href="/" class="flex items-center">
                        <?php 
                        $siteLogo = $siteConfig['site_logo'] ?? '';
                        if ($siteLogo): 
                        ?>
                        <img src="<?php echo htmlspecialchars($siteLogo); ?>" alt="<?php echo $siteName ?? SITE_NAME; ?>" class="h-8 md:h-10">
                        <?php else: ?>
                        <span class="text-blue-600 text-xl md:text-2xl font-bold"><?php echo $siteName ?? SITE_NAME; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="hidden md:flex space-x-6 text-sm">
                        <a href="/" class="<?php echo empty($type) ? 'text-blue-600 border-b-2 border-blue-600' : 'hover:text-blue-600'; ?> pb-4 pt-4 nav-link" data-page="home">首页</a>
                        <a href="/hot" class="hover:text-blue-600 pb-4 pt-4 nav-link" data-page="hot">热门</a>
                        <?php if (!empty($navTypes)): ?>
                            <?php 
                            $currentTopId = isset($type) ? (($type['type_pid'] == 0) ? $type['type_id'] : $type['type_pid']) : 0;
                            ?>
                            <?php foreach (array_slice($navTypes, 0, 5) as $navType): ?>
                                <a href="<?php echo xpk_page_url('type', ['id' => $navType['type_id'], 'slug' => $navType['type_en']]); ?>" class="<?php echo ($navType['type_id'] == $currentTopId) ? 'text-blue-600 border-b-2 border-blue-600' : 'hover:text-blue-600'; ?> pb-4 pt-4"><?php echo htmlspecialchars($navType['type_name']); ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- 搜索 -->
                    <div class="relative hidden sm:block">
                        <form action="/search" method="get" id="searchForm" role="search">
                            <label for="searchInput" class="sr-only">搜索视频</label>
                            <input type="text" 
                                   name="wd" 
                                   placeholder="搜索" 
                                   value="<?php echo htmlspecialchars($_GET['wd'] ?? ''); ?>" 
                                   class="bg-gray-100 rounded-lg px-4 py-2 text-sm w-48 md:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   id="searchInput"
                                   aria-label="搜索视频内容">
                            <button type="submit" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600"
                                    aria-label="执行搜索">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" role="img" aria-label="搜索图标">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                    <!-- 移动端搜索图标 -->
                    <button class="sm:hidden text-gray-600" 
                            onclick="toggleMobileSearch()"
                            aria-label="打开搜索">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" role="img" aria-label="搜索图标">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                    <?php if (isset($user)): ?>
                        <a href="/user/center" class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                <?php echo mb_substr($user['user_nick_name'], 0, 1); ?>
                            </div>
                        </a>
                    <?php else: ?>
                        <a href="/user/login" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">登录</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- 移动端菜单 -->
    <div id="mobileMenu" 
         class="fixed inset-0 bg-white z-40 hidden overflow-y-auto"
         role="dialog"
         aria-labelledby="mobileMenuTitle"
         aria-modal="true">
        <div class="p-6 pt-16 pb-20">
            <h2 id="mobileMenuTitle" class="sr-only">网站导航菜单</h2>
            <button class="absolute top-4 right-4 text-gray-600" 
                    onclick="toggleMobileMenu()"
                    aria-label="关闭菜单">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" role="img" aria-label="关闭图标">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <nav role="navigation" aria-label="主导航">
                <div class="space-y-4">
                    <a href="/" class="block text-xl text-blue-600 py-2">首页</a>
                    <a href="/hot" class="block text-xl text-gray-600 py-2 hover:text-blue-600">热门</a>
                    <div class="border-t pt-4 mt-4">
                        <p class="text-sm text-gray-400 mb-3">分类</p>
                        <?php if (!empty($navTypes)): ?>
                            <?php foreach ($navTypes as $navItem): ?>
                                <a href="<?php echo xpk_page_url('type', ['id' => $navItem['type_id'], 'slug' => $navItem['type_en']]); ?>" class="block text-lg text-gray-600 py-2 hover:text-blue-600"><?php echo htmlspecialchars($navItem['type_name']); ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <a href="/type/all" class="block text-lg text-blue-600 py-2 mt-2">查看全部分类 →</a>
                    </div>
                </div>
            </nav>
        </div>
    </div>

    <!-- 移动端搜索 -->
    <div id="mobileSearch" 
         class="fixed top-0 left-0 right-0 bg-white z-50 p-4 shadow-lg hidden"
         role="dialog"
         aria-labelledby="mobileSearchTitle"
         aria-modal="true">
        <h2 id="mobileSearchTitle" class="sr-only">搜索</h2>
        <form action="/search" method="get" class="flex items-center" role="search">
            <label for="mobileSearchInput" class="sr-only">搜索视频</label>
            <input type="text" 
                   name="wd" 
                   placeholder="搜索视频..." 
                   class="flex-1 bg-gray-100 text-gray-800 px-4 py-3 rounded-l-lg focus:outline-none" 
                   autofocus
                   id="mobileSearchInput"
                   aria-label="搜索视频内容">
            <button type="submit" 
                    class="bg-blue-600 text-white px-6 py-3 rounded-r-lg"
                    aria-label="执行搜索">搜索</button>
            <button type="button" 
                    class="ml-2 text-gray-600 p-2" 
                    onclick="toggleMobileSearch()"
                    aria-label="关闭搜索">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" role="img" aria-label="关闭图标">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </form>
    </div>

    <!-- 主内容 -->
    <main class="container mx-auto px-4 pt-4 pb-6" id="main-content" role="main">