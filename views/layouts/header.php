<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? SITE_NAME); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars($keywords ?? SITE_KEYWORDS); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($description ?? SITE_DESCRIPTION); ?>">
    <link rel="icon" href="/static/favicon.svg" type="image/svg+xml">
    <?php if (!empty($noindex)): ?><meta name="robots" content="noindex, nofollow"><?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/static/css/xpk.css">
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen pb-16 md:pb-0">
    <!-- 顶部导航 -->
    <nav class="bg-white border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button class="p-2 hover:bg-gray-100 rounded-full lg:hidden" onclick="toggleSidebar()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <a href="/" class="flex items-center">
                    <?php 
                    $siteLogo = $siteConfig['site_logo'] ?? '';
                    if ($siteLogo): 
                    ?>
                    <img src="<?php echo htmlspecialchars($siteLogo); ?>" alt="<?php echo SITE_NAME; ?>" class="h-8 max-w-[160px]">
                    <?php else: ?>
                    <span class="text-red-600 text-2xl font-bold">🍌</span>
                    <span class="text-xl font-semibold ml-1 hidden sm:inline"><?php echo SITE_NAME; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <div class="flex-1 max-w-xl mx-4 hidden md:block">
                <form action="/search" method="get" class="flex">
                    <input type="text" name="wd" placeholder="搜索视频..." value="<?php echo htmlspecialchars($_GET['wd'] ?? ''); ?>" class="flex-1 border border-gray-300 px-4 py-2 rounded-l-full focus:outline-none focus:border-red-500">
                    <button type="submit" class="bg-gray-100 px-6 border border-l-0 border-gray-300 rounded-r-full hover:bg-gray-200">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </form>
            </div>
            <div class="flex items-center space-x-2">
                <?php if (isset($user)): ?>
                    <a href="/user/center" class="flex items-center space-x-2 hover:bg-gray-100 rounded-full px-3 py-1.5">
                        <div class="w-7 h-7 bg-red-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            <?php echo mb_substr($user['user_nick_name'] ?? $user['user_name'], 0, 1); ?>
                        </div>
                        <span class="text-sm hidden sm:inline"><?php echo htmlspecialchars($user['user_nick_name'] ?? $user['user_name']); ?></span>
                    </a>
                <?php else: ?>
                    <a href="/user/login" class="bg-red-600 text-white px-4 py-1.5 rounded-full text-sm font-medium hover:bg-red-700">登录</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- 移动端侧边栏遮罩 -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden" onclick="toggleSidebar()"></div>

    <!-- 移动端侧边栏 -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white z-50 transform -translate-x-full transition-transform duration-300 overflow-y-auto lg:hidden">
        <div class="flex items-center justify-between p-4 border-b">
            <span class="font-bold">菜单</span>
            <button onclick="toggleSidebar()" class="p-2 hover:bg-gray-100 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="py-3">
            <a href="/" class="flex items-center px-6 py-2.5 <?php echo ($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/index.html') ? 'bg-red-50 text-red-600' : 'hover:bg-gray-100'; ?>">
                <svg class="w-5 h-5 mr-4" fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                <span class="text-sm">首页</span>
            </a>
            <a href="/hot" class="flex items-center px-6 py-2.5 hover:bg-gray-100">
                <svg class="w-5 h-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg>
                <span class="text-sm">热门</span>
            </a>
            <hr class="my-3">
            <div class="px-6 py-2">
                <p class="text-xs font-medium text-gray-400 uppercase">分类</p>
            </div>
            <?php if (!empty($navTypes)): ?>
                <?php foreach ($navTypes as $navType): ?>
                    <a href="<?php echo xpk_page_url('type', ['id' => $navType['type_id'], 'slug' => $navType['type_en']]); ?>" class="flex items-center px-6 py-2.5 hover:bg-gray-100 text-sm">
                        <?php echo htmlspecialchars($navType['type_name']); ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </aside>

    <!-- 主内容 -->
    <main class="max-w-7xl mx-auto p-4 lg:p-6">
