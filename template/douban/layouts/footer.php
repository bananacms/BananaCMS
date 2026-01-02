    </main>

    <!-- 页脚 -->
    <footer class="bg-white border-t mt-10">
        <div class="max-w-5xl mx-auto px-4 py-8">
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
            <div class="mb-6 pb-6 border-b">
                <div class="text-gray-500 text-sm mb-3">友情链接</div>
                <div class="flex flex-wrap gap-4">
                    <?php foreach (array_slice($footerLinks, 0, $footerLinkMax) as $flink): ?>
                    <a href="<?= htmlspecialchars($flink['link_url']) ?>" target="_blank" rel="noopener nofollow" class="text-gray-500 hover:text-green-700 text-sm"><?= htmlspecialchars($flink['link_name']) ?></a>
                    <?php endforeach; ?>
                    <?php if ($footerLinkCount > $footerLinkMax): ?>
                    <a href="/link" class="text-green-700 text-sm">更多 &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                <div>
                    <h4 class="text-gray-700 text-sm font-medium mb-3">导航</h4>
                    <div class="space-y-2">
                        <a href="/" class="block text-gray-500 hover:text-green-700 text-sm">首页</a>
                        <a href="/hot" class="block text-gray-500 hover:text-green-700 text-sm">热门</a>
                    </div>
                </div>
                <div>
                    <h4 class="text-gray-700 text-sm font-medium mb-3">分类</h4>
                    <div class="space-y-2">
                        <?php if (!empty($navTypes)): ?>
                            <?php foreach (array_slice($navTypes, 0, 4) as $type): ?>
                                <a href="<?php echo xpk_page_url('type', ['id' => $type['type_id'], 'slug' => $type['type_en']]); ?>" class="block text-gray-500 hover:text-green-700 text-sm"><?php echo htmlspecialchars($type['type_name']); ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <h4 class="text-gray-700 text-sm font-medium mb-3">帮助</h4>
                    <div class="space-y-2">
                        <?php
                        if (!class_exists('XpkPage')) {
                            require_once MODEL_PATH . 'Page.php';
                        }
                        $footerPages = (new XpkPage())->getEnabled();
                        foreach ($footerPages as $fpage):
                            if (!empty($fpage['page_footer'])):
                        ?>
                        <a href="/page/<?= htmlspecialchars($fpage['page_slug']) ?>" class="block text-gray-500 hover:text-green-700 text-sm"><?= htmlspecialchars($fpage['page_title']) ?></a>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                        <a href="/link" class="block text-gray-500 hover:text-green-700 text-sm">友情链接</a>
                    </div>
                </div>
                <div>
                    <h4 class="text-gray-700 text-sm font-medium mb-3">账户</h4>
                    <div class="space-y-2">
                        <?php if (isset($user)): ?>
                            <a href="/user/center" class="block text-gray-500 hover:text-green-700 text-sm">个人中心</a>
                        <?php else: ?>
                            <a href="/user/login" class="block text-gray-500 hover:text-green-700 text-sm">登录</a>
                            <a href="/user/register" class="block text-gray-500 hover:text-green-700 text-sm">注册</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="text-gray-400 text-sm text-center">
                <?php if (!empty($siteConfig['site_icp'])): ?>
                <p class="mb-2"><a href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow" class="hover:text-green-700"><?= htmlspecialchars($siteConfig['site_icp']) ?></a></p>
                <?php endif; ?>
                Powered by <a href="https://xpornkit.com" class="text-green-700 hover:text-green-800" target="_blank">香蕉CMS</a>
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
            <a href="/" class="flex flex-col items-center py-1 px-3 text-green-700">
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
            <button onclick="toggleMobileSearch()" class="flex flex-col items-center py-1 px-3 text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <span class="text-xs mt-1">搜索</span>
            </a>
            <a href="/user/center" class="flex flex-col items-center py-1 px-3 text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="text-xs mt-1">我的</span>
            </a>
        </div>
    </nav>

    <script src="/static/js/xpk.js" defer></script>
    <script defer>
        // 移动端菜单
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        }

        // 移动端搜索
        function toggleMobileSearch() {
            const search = document.getElementById('mobileSearch');
            search.classList.toggle('hidden');
        }
    </script>
</body>
</html>
