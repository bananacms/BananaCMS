<?php
/**
 * 前台通用分页组件（伪静态URL）
 * 使用方法: include TPL_PATH . 'components/pagination.php';
 * 需要变量: $page, $totalPages, $baseUrl (不含page部分的URL，如 /type/1 或 /search/关键词)
 */
if (!isset($totalPages) || $totalPages <= 1) return;
?>
<div class="flex flex-wrap justify-center items-center gap-2 mt-8">
    <?php if ($page > 1): ?>
    <a href="<?= $baseUrl ?>/page/<?= $page - 1 ?>" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 text-sm">上一页</a>
    <?php endif; ?>
    
    <?php if ($page > 3): ?>
    <a href="<?= $baseUrl ?>/page/1" class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200 text-sm">1</a>
    <?php if ($page > 4): ?>
    <span class="px-2 text-gray-400">...</span>
    <?php endif; ?>
    <?php endif; ?>
    
    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
    <?php if ($i == $page): ?>
    <span class="px-3 py-2 bg-red-600 text-white rounded text-sm"><?= $i ?></span>
    <?php else: ?>
    <a href="<?= $baseUrl ?>/page/<?= $i ?>" class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200 text-sm"><?= $i ?></a>
    <?php endif; ?>
    <?php endfor; ?>
    
    <?php if ($page < $totalPages - 2): ?>
    <?php if ($page < $totalPages - 3): ?>
    <span class="px-2 text-gray-400">...</span>
    <?php endif; ?>
    <a href="<?= $baseUrl ?>/page/<?= $totalPages ?>" class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200 text-sm"><?= $totalPages ?></a>
    <?php endif; ?>
    
    <?php if ($page < $totalPages): ?>
    <a href="<?= $baseUrl ?>/page/<?= $page + 1 ?>" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">下一页</a>
    <?php endif; ?>
    
    <?php if ($totalPages > 5): ?>
    <span class="text-gray-500 text-sm ml-2">共 <?= $totalPages ?> 页</span>
    <div class="flex items-center gap-1 ml-2">
        <input type="number" id="pageJumpInput" min="1" max="<?= $totalPages ?>" value="<?= $page ?>" 
               class="w-16 px-2 py-1.5 border rounded text-sm text-center"
               onkeydown="if(event.key==='Enter')goToPage()">
        <button onclick="goToPage()" class="px-3 py-1.5 bg-gray-100 border rounded hover:bg-gray-200 text-sm">跳转</button>
    </div>
    <script>
    function goToPage() {
        var input = document.getElementById('pageJumpInput');
        var p = parseInt(input.value);
        if (isNaN(p) || p < 1) p = 1;
        if (p > <?= $totalPages ?>) p = <?= $totalPages ?>;
        window.location.href = '<?= $baseUrl ?>/page/' + p;
    }
    </script>
    <?php endif; ?>
</div>
