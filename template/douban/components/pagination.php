<?php
/**
 * 豆瓣风格分页组件
 * 使用: <?php include TPL_PATH . 'douban/components/pagination.php'; ?>
 * 需要变量: $page, $totalPages, $baseUrl
 */
if (!isset($totalPages) || $totalPages <= 1) return;

// 计算显示的页码范围
$range = 2;
$start = max(1, $page - $range);
$end = min($totalPages, $page + $range);
?>
<div class="flex justify-center items-center space-x-2 mt-8">
    <?php if ($page > 1): ?>
        <a href="<?= $baseUrl ?>/page/1" class="px-3 py-1 border border-gray-300 rounded text-gray-600 hover:border-green-700 hover:text-green-700 text-sm">首页</a>
        <a href="<?= $baseUrl ?>/page/<?= $page - 1 ?>" class="px-3 py-1 border border-gray-300 rounded text-gray-600 hover:border-green-700 hover:text-green-700 text-sm">&lt; 前页</a>
    <?php endif; ?>
    
    <?php if ($start > 1): ?>
        <span class="px-2 text-gray-400">...</span>
    <?php endif; ?>
    
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <?php if ($i == $page): ?>
            <span class="px-3 py-1 bg-green-700 text-white rounded text-sm"><?= $i ?></span>
        <?php else: ?>
            <a href="<?= $baseUrl ?>/page/<?= $i ?>" class="px-3 py-1 border border-gray-300 rounded text-gray-600 hover:border-green-700 hover:text-green-700 text-sm"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    
    <?php if ($end < $totalPages): ?>
        <span class="px-2 text-gray-400">...</span>
    <?php endif; ?>
    
    <?php if ($page < $totalPages): ?>
        <a href="<?= $baseUrl ?>/page/<?= $page + 1 ?>" class="px-3 py-1 border border-gray-300 rounded text-gray-600 hover:border-green-700 hover:text-green-700 text-sm">后页 &gt;</a>
        <a href="<?= $baseUrl ?>/page/<?= $totalPages ?>" class="px-3 py-1 border border-gray-300 rounded text-gray-600 hover:border-green-700 hover:text-green-700 text-sm">末页</a>
    <?php endif; ?>
    
    <?php if ($totalPages > 5): ?>
    <span class="text-gray-500 text-sm ml-2">共 <?= $totalPages ?> 页</span>
    <div class="flex items-center gap-1 ml-2">
        <input type="number" id="pageJumpInput" min="1" max="<?= $totalPages ?>" value="<?= $page ?>" 
               class="w-16 px-2 py-1.5 border rounded text-sm text-center"
               onkeydown="if(event.key==='Enter')goToPage()">
        <button onclick="goToPage()" class="px-3 py-1.5 border rounded hover:bg-green-50 text-sm">跳转</button>
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
