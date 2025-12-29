<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? SITE_NAME); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars($keywords ?? SITE_KEYWORDS); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($description ?? SITE_DESCRIPTION); ?>">
    <?php if (!empty($noindex)): ?><meta name="robots" content="noindex, nofollow"><?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/static/css/xpk.css">
    <script>function toggleSidebar(){}</script>
</head>
<body class="bg-white text-gray-900 min-h-screen pb-14 md:pb-0">
    <!-- 顶部导航 -->
    <nav class="bg-white border-b sticky top-0 z-50">
        <div class="px-4 py-2 flex items-center justify-between">
            <div class="flex items-center space-x-4">
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
                    <span class="text-xl font-semibold ml-1"><?php echo SITE_NAME; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <div class="flex-1 max-w-2xl mx-8 hidden md:block">
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
                    <span class="text-gray-600 text-sm hidden sm:inline"><?php echo htmlspecialchars($user['user_nick_name']); ?></span>
                    <a href="/user/center" class="p-2 hover:bg-gray-100 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </a>
                <?php else: ?>
                    <a href="/user/login" class="bg-red-600 text-white px-4 py-1.5 rounded-full text-sm font-medium hover:bg-red-700">登录</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- 侧边栏 -->
        <aside id="sidebar" class="hidden lg:block w-60 h-[calc(100vh-56px)] sticky top-14 overflow-y-auto border-r bg-white">
            <div class="py-3">
                <a href="/" class="flex items-center px-6 py-2 <?php echo ($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/index.html') ? 'bg-gray-100' : 'hover:bg-gray-100'; ?>">
                    <svg class="w-6 h-6 mr-6" fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                    <span class="text-sm">首页</span>
                </a>
                <a href="/search?wd=热门" class="flex items-center px-6 py-2 hover:bg-gray-100">
                    <svg class="w-6 h-6 mr-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg>
                    <span class="text-sm">热门</span>
                </a>
                <hr class="my-3">
                <div class="px-6 py-2">
                    <p class="text-sm font-medium mb-2">分类</p>
                </div>
                <?php if (!empty($navTypes)): ?>
                    <?php foreach ($navTypes as $type): ?>
                        <a href="/type/<?php echo $type['type_id']; ?>" class="flex items-center px-6 py-2 hover:bg-gray-100">
                            <span class="text-sm"><?php echo htmlspecialchars($type['type_name']); ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>

        <!-- 主内容 -->
        <main class="flex-1 p-4 lg:p-6">
