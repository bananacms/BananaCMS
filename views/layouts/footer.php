        </main>

    <!-- 页脚 -->
    <footer class="border-t mt-8 bg-white">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <?php
            // 获取友情链接
            if (!class_exists('XpkLink')) {
                require_once MODEL_PATH . 'Link.php';
            }
            $footerLinks = (new XpkLink())->getActive();
            $footerLinkCount = count($footerLinks);
            $footerLinkMax = 10;
            if (!empty($footerLinks)):
            ?>
            <div class="mb-4 pb-4 border-b border-gray-200">
                <div class="text-gray-600 text-sm mb-2">友情链接</div>
                <div class="flex flex-wrap gap-x-4 gap-y-2">
                    <?php foreach (array_slice($footerLinks, 0, $footerLinkMax) as $flink): ?>
                    <a href="<?= htmlspecialchars($flink['link_url']) ?>" target="_blank" rel="noopener nofollow" class="text-gray-500 hover:text-red-600 text-sm"><?= htmlspecialchars($flink['link_name']) ?></a>
                    <?php endforeach; ?>
                    <?php if ($footerLinkCount > $footerLinkMax): ?>
                    <a href="/link" class="text-red-600 hover:text-red-700 text-sm">更多 &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-500">
                <div>
                    <?php if (!empty($siteConfig['site_icp'])): ?>
                    <a href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow" class="hover:text-red-600 mr-4"><?= htmlspecialchars($siteConfig['site_icp']) ?></a>
                    <?php endif; ?>
                    Powered by <a href="https://xpornkit.com" class="text-red-600 hover:underline" target="_blank">香蕉CMS</a>
                </div>
                <div class="flex flex-wrap justify-center gap-4 mt-3 md:mt-0">
                    <a href="/link" class="hover:text-gray-700">友情链接</a>
                    <?php
                    if (!class_exists('XpkPage')) {
                        require_once MODEL_PATH . 'Page.php';
                    }
                    $footerPages = (new XpkPage())->getEnabled();
                    foreach ($footerPages as $fpage):
                        if (!empty($fpage['page_footer'])):
                    ?>
                    <a href="/page/<?= htmlspecialchars($fpage['page_slug']) ?>" class="hover:text-gray-700"><?= htmlspecialchars($fpage['page_title']) ?></a>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
        </div>
    </footer>

    <!-- 悬浮广告 -->
    <?= xpk_ad('home_float') ?>
    
    <!-- 弹窗广告 -->
    <?= xpk_ad('popup') ?>

    <!-- 移动端底部导航 -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t z-30">
        <div class="flex justify-around py-2">
            <a href="/" class="flex flex-col items-center py-1 px-3 <?php echo ($_SERVER['REQUEST_URI'] == '/') ? 'text-red-600' : 'text-gray-500'; ?>">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                <span class="text-xs mt-1">首页</span>
            </a>
            <a href="/hot" class="flex flex-col items-center py-1 px-3 text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg>
                <span class="text-xs mt-1">热门</span>
            </a>
            <a href="/type/all" class="flex flex-col items-center py-1 px-3 text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="text-xs mt-1">分类</span>
            </a>
            <button onclick="openMobileSearch()" class="flex flex-col items-center py-1 px-3 text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <span class="text-xs mt-1">搜索</span>
            </button>
            <a href="/user/center" class="flex flex-col items-center py-1 px-3 text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="text-xs mt-1">我的</span>
            </a>
        </div>
    </nav>

    <!-- 移动端搜索弹窗 -->
    <div id="mobileSearchModal" class="fixed inset-0 bg-black/50 z-50 hidden">
        <div class="bg-white p-4">
            <form action="/search" method="get" class="flex gap-2">
                <input type="text" name="wd" placeholder="搜索视频..." class="flex-1 border border-gray-300 px-4 py-3 rounded-lg focus:outline-none focus:border-red-500" autofocus>
                <button type="submit" class="bg-red-600 text-white px-6 rounded-lg">搜索</button>
                <button type="button" onclick="closeMobileSearch()" class="px-3 text-gray-500">取消</button>
            </form>
        </div>
    </div>

    <script src="/static/js/xpk.js"></script>
    <script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const isOpen = !sidebar.classList.contains('-translate-x-full');
        
        if (isOpen) {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        } else {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function openMobileSearch() {
        document.getElementById('mobileSearchModal').classList.remove('hidden');
        document.getElementById('mobileSearchModal').querySelector('input').focus();
    }

    function closeMobileSearch() {
        document.getElementById('mobileSearchModal').classList.add('hidden');
    }
    </script>
</body>
</html>
