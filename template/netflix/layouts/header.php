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
        body { background-color: #141414; }
        .card:hover img { transform: scale(1.05); }
        .card img { transition: transform 0.3s; }
        .card:hover .card-overlay { opacity: 1; }
        .card-overlay { transition: opacity 0.3s; }
        .scroll-hide::-webkit-scrollbar { display: none; }
        .scroll-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .gradient-mask { mask-image: linear-gradient(to right, black 90%, transparent); -webkit-mask-image: linear-gradient(to right, black 90%, transparent); }
    </style>
</head>
<body class="bg-[#141414] text-white min-h-screen pb-16 md:pb-0">
    <!-- 顶部导航 -->
    <nav class="fixed w-full z-50 transition-all duration-300" id="navbar">
        <div class="bg-gradient-to-b from-black/80 to-transparent">
            <div class="max-w-7xl mx-auto px-4 md:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center space-x-4 md:space-x-8">
                    <!-- 移动端菜单按钮 -->
                    <button class="md:hidden text-white p-2" onclick="toggleMobileMenu()">
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
                        <span class="text-red-600 text-2xl md:text-3xl font-bold tracking-wider"><?php echo SITE_NAME; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="hidden md:flex space-x-6 text-sm">
                        <a href="/" class="text-white font-medium hover:text-gray-300">首页</a>
                        <a href="/hot" class="text-gray-300 hover:text-white">热门</a>
                        <?php if (!empty($navTypes)): ?>
                            <?php foreach (array_slice($navTypes, 0, 5) as $type): ?>
                                <a href="<?php echo xpk_page_url('type', ['id' => $type['type_id'], 'slug' => $type['type_en']]); ?>" class="text-gray-300 hover:text-white"><?php echo htmlspecialchars($type['type_name']); ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- 搜索 -->
                    <div class="relative hidden sm:block">
                        <form action="/search" method="get" id="searchForm">
                            <input type="text" name="wd" placeholder="搜索..." value="<?php echo htmlspecialchars($_GET['wd'] ?? ''); ?>" 
                                class="bg-black/50 border border-gray-600 text-white px-4 py-1.5 rounded text-sm w-40 md:w-56 focus:outline-none focus:border-white focus:w-64 transition-all" id="searchInput">
                        </form>
                    </div>
                    <!-- 移动端搜索图标 -->
                    <button class="sm:hidden text-white" onclick="toggleMobileSearch()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                    <?php if (isset($user)): ?>
                        <a href="/user/center" class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-red-600 rounded flex items-center justify-center text-sm font-bold">
                                <?php echo mb_substr($user['user_nick_name'], 0, 1); ?>
                            </div>
                        </a>
                    <?php else: ?>
                        <a href="/user/login" class="bg-red-600 text-white px-4 py-1.5 rounded text-sm font-medium hover:bg-red-700">登录</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- 移动端菜单 -->
    <div id="mobileMenu" class="fixed inset-0 bg-black/95 z-40 hidden">
        <div class="p-6 pt-20">
            <button class="absolute top-4 right-4 text-white" onclick="toggleMobileMenu()">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="space-y-4">
                <a href="/" class="block text-2xl text-white py-2">首页</a>
                <a href="/hot" class="block text-2xl text-gray-400 py-2 hover:text-white">热门</a>
                <?php if (!empty($navTypes)): ?>
                    <?php foreach ($navTypes as $type): ?>
                        <a href="<?php echo xpk_page_url('type', ['id' => $type['type_id'], 'slug' => $type['type_en']]); ?>" class="block text-2xl text-gray-400 py-2 hover:text-white"><?php echo htmlspecialchars($type['type_name']); ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 移动端搜索 -->
    <div id="mobileSearch" class="fixed top-0 left-0 right-0 bg-black z-50 p-4 hidden">
        <form action="/search" method="get" class="flex items-center">
            <input type="text" name="wd" placeholder="搜索视频..." class="flex-1 bg-gray-800 text-white px-4 py-3 rounded-l focus:outline-none" autofocus>
            <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-r">搜索</button>
            <button type="button" class="ml-2 text-white p-2" onclick="toggleMobileSearch()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </form>
    </div>

    <!-- 主内容 -->
    <main>
