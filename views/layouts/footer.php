        </main>
    </div>

    <!-- 页脚 -->
    <footer class="border-t mt-8 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <?php
            // 获取友情链接（页脚最多显示10个）
            if (!class_exists('XpkLink')) {
                require_once MODEL_PATH . 'Link.php';
            }
            $footerLinks = (new XpkLink())->getActive();
            $footerLinkCount = count($footerLinks);
            $footerLinkMax = 10;
            if (!empty($footerLinks)):
            ?>
            <div class="mb-4 pb-4 border-b border-gray-200">
                <div class="text-gray-600 text-sm mb-2">友情链接：</div>
                <div class="flex flex-wrap gap-3">
                    <?php foreach (array_slice($footerLinks, 0, $footerLinkMax) as $flink): ?>
                    <a href="<?= htmlspecialchars($flink['link_url']) ?>" target="_blank" rel="noopener nofollow" class="text-gray-500 hover:text-red-600 text-sm"><?= htmlspecialchars($flink['link_name']) ?></a>
                    <?php endforeach; ?>
                    <?php if ($footerLinkCount > $footerLinkMax): ?>
                    <a href="/link" class="text-red-600 hover:text-red-700 text-sm">更多(<?= $footerLinkCount - $footerLinkMax ?>+) &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-500 text-sm">
                    Powered by <a href="https://xpornkit.com" class="text-red-600 hover:underline" target="_blank">香蕉CMS</a>
                </div>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="/link" class="text-gray-500 hover:text-gray-700 text-sm">友情链接</a>
                    <a href="/about" class="text-gray-500 hover:text-gray-700 text-sm">关于我们</a>
                    <a href="/contact" class="text-gray-500 hover:text-gray-700 text-sm">联系方式</a>
                    <a href="/disclaimer" class="text-gray-500 hover:text-gray-700 text-sm">免责声明</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- 悬浮广告 -->
    <?= xpk_ad('home_float') ?>
    
    <!-- 弹窗广告 -->
    <?= xpk_ad('popup') ?>

    <!-- 移动端搜索 -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t p-2">
        <form action="/search" method="get" class="flex">
            <input type="text" name="wd" placeholder="搜索..." class="flex-1 border border-gray-300 px-4 py-2 rounded-l-full focus:outline-none">
            <button type="submit" class="bg-red-600 text-white px-4 rounded-r-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </form>
    </div>

    <script src="/static/js/xpk.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('fixed');
            sidebar.classList.toggle('inset-0');
            sidebar.classList.toggle('z-40');
            sidebar.classList.toggle('w-60');
            sidebar.classList.toggle('w-full');
        }
    </script>
</body>
</html>
