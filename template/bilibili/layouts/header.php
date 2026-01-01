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
        body { background-color: #f4f4f4; }
        .card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .card { transition: box-shadow 0.3s; }
        .scroll-hide::-webkit-scrollbar { display: none; }
        .scroll-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen pb-16 md:pb-0">
    <!-- 顶部导航 -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="h-14 flex items-center justify-between">
                <div class="flex items-center space-x-4 md:space-x-6">
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
                        <span class="text-pink-500 text-xl md:text-2xl font-bold">( ゜- ゜)つロ <?php echo SITE_NAME; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="hidden md:flex space-x-4 text-sm">
                        <a href="/" class="text-pink-500 border-b-2 border-pink-500 pb-4 pt-4 nav-link" data-page="home">首页</a>
                        <a href="/hot" class="hover:text-pink-500 pb-4 pt-4 nav-link" data-page="hot">热门</a>
                        <?php if (!empty($navTypes)): ?>
                            <?php foreach (array_slice($navTypes, 0, 5) as $type): ?>
                                <a href="<?php echo xpk_page_url('type', ['id' => $type['type_id'], 'slug' => $type['type_en']]); ?>" class="hover:text-pink-500 pb-4 pt-4"><?php echo htmlspecialchars($type['type_name']); ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- 搜索 -->
                    <div class="relative hidden sm:block">
                        <form action="/search" method="get" id="searchForm">
                            <input type="text" name="wd" placeholder="搜索" value="<?php echo htmlspecialchars($_GET['wd'] ?? ''); ?>" 
                                class="bg-gray-100 rounded-full px-4 py-1.5 text-sm w-40 md:w-48 focus:outline-none focus:ring-2 focus:ring-pink-500" id="searchInput">
                            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-pink-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                    <!-- 移动端搜索图标 -->
                    <button class="sm:hidden text-gray-600" onclick="toggleMobileSearch()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                    <?php if (isset($user)): ?>
                        <a href="/user/center" class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-pink-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                <?php echo mb_substr($user['user_nick_name'], 0, 1); ?>
                            </div>
                        </a>
                    <?php else: ?>
                        <a href="/user/login" class="bg-pink-500 text-white px-4 py-1 rounded-full text-sm hover:bg-pink-600">登录</a>
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
                <a href="/" class="block text-xl text-pink-500 py-2">首页</a>
                <a href="/hot" class="block text-xl text-gray-600 py-2 hover:text-pink-500">热门</a>
                <?php if (!empty($navTypes)): ?>
                    <?php foreach ($navTypes as $type): ?>
                        <a href="<?php echo xpk_page_url('type', ['id' => $type['type_id'], 'slug' => $type['type_en']]); ?>" class="block text-xl text-gray-600 py-2 hover:text-pink-500"><?php echo htmlspecialchars($type['type_name']); ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 移动端搜索 -->
    <div id="mobileSearch" class="fixed top-0 left-0 right-0 bg-white z-50 p-4 shadow-lg hidden">
        <form action="/search" method="get" class="flex items-center">
            <input type="text" name="wd" placeholder="搜索视频..." class="flex-1 bg-gray-100 text-gray-800 px-4 py-3 rounded-l-full focus:outline-none" autofocus>
            <button type="submit" class="bg-pink-500 text-white px-6 py-3 rounded-r-full">搜索</button>
            <button type="button" class="ml-2 text-gray-600 p-2" onclick="toggleMobileSearch()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </form>
    </div>

    <!-- 主内容 -->
    <main>
