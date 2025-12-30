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
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        .scroll-hide::-webkit-scrollbar { display: none; }
        .scroll-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-white text-gray-800 min-h-screen">
    <!-- 顶部导航 -->
    <nav class="bg-white border-b sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4">
            <div class="h-14 flex items-center justify-between">
                <div class="flex items-center space-x-4 md:space-x-8">
                    <!-- 移动端菜单按钮 -->
                    <button class="md:hidden text-gray-600 p-2" onclick="toggleMobileMenu()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <a href="/" class="flex items-center">
                        <?php 
                        $siteLogo = $siteConfig['site_logo'] ?? '';
                        if ($siteLogo): 
                        ?>
                        <img src="<?php echo htmlspecialchars($siteLogo); ?>" alt="<?php echo SITE_NAME; ?>" class="h-6 md:h-8">
                        <?php else: ?>
                        <span class="text-green-700 text-lg md:text-xl font-serif font-bold"><?php echo SITE_NAME; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="hidden md:flex space-x-6 text-sm text-gray-600">
                        <a href="/" class="text-green-700">影视</a>
                        <a href="/hot" class="hover:text-green-700">热门</a>
                        <?php if (!empty($navTypes)): ?>
                            <?php foreach (array_slice($navTypes, 0, 4) as $type): ?>
                                <a href="<?php echo xpk_page_url('type', ['id' => $type['type_id'], 'slug' => $type['type_en']]); ?>" class="hover:text-green-700"><?php echo htmlspecialchars($type['type_name']); ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center space-x-3 md:space-x-4">
                    <!-- 搜索 -->
                    <div class="relative hidden sm:block">
                        <form action="/search" method="get" id="searchForm">
                            <input type="text" name="wd" placeholder="搜索影视" value="<?php echo htmlspecialchars($_GET['wd'] ?? ''); ?>" 
                                class="border border-gray-300 rounded px-3 py-1 text-sm w-36 md:w-48 focus:outline-none focus:border-green-700" id="searchInput">
                        </form>
                    </div>
                    <!-- 移动端搜索图标 -->
                    <button class="sm:hidden text-gray-600" onclick="toggleMobileSearch()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                    <?php if (isset($user)): ?>
                        <a href="/user/center" class="text-green-700 text-sm"><?php echo htmlspecialchars($user['user_nick_name']); ?></a>
                    <?php else: ?>
                        <a href="/user/login" class="text-green-700 text-sm">登录</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- 移动端菜单 -->
    <div id="mobileMenu" class="fixed inset-0 bg-white z-40 hidden">
        <div class="p-6 pt-16">
            <button class="absolute top-4 right-4 text-gray-600" onclick="toggleMobileMenu()">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="space-y-4">
                <a href="/" class="block text-lg text-green-700 py-2 border-b">首页</a>
                <a href="/hot" class="block text-lg text-gray-600 py-2 border-b hover:text-green-700">热门</a>
                <?php if (!empty($navTypes)): ?>
                    <?php foreach ($navTypes as $type): ?>
                        <a href="<?php echo xpk_page_url('type', ['id' => $type['type_id'], 'slug' => $type['type_en']]); ?>" class="block text-lg text-gray-600 py-2 border-b hover:text-green-700"><?php echo htmlspecialchars($type['type_name']); ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 移动端搜索 -->
    <div id="mobileSearch" class="fixed top-0 left-0 right-0 bg-white z-50 p-4 border-b hidden">
        <form action="/search" method="get" class="flex items-center">
            <input type="text" name="wd" placeholder="搜索影视..." class="flex-1 border border-gray-300 text-gray-800 px-4 py-2 rounded focus:outline-none focus:border-green-700" autofocus>
            <button type="submit" class="ml-2 bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800">搜索</button>
            <button type="button" class="ml-2 text-gray-600 p-2" onclick="toggleMobileSearch()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </form>
    </div>

    <!-- 主内容 -->
    <main>
