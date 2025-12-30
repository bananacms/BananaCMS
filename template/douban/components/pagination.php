<?php
/**
 * 豆瓣风格分页组件
 * 使用: <?php include TPL_PATH . 'douban/components/pagination.php'; ?>
 * 需要变量: $pagination (包含 current, total, baseUrl)
 */
if (!isset($pagination) || $pagination['total'] <= 1) return;

$current = $pagination['current'];
$total = $pagination['total'];
$baseUrl = $pagination['baseUrl'];

// 计算显示的页码范围
$range = 2;
$start = max(1, $current - $range);
$end = min($total, $current + $range);
?>
<div class="flex justify-center items-center space-x-2 mt-8">
    <?php if ($current > 1): ?>
        <a href="<?= $baseUrl ?>1" class="px-3 py-1 border border-gray-300 rounded text-gray-600 hover:border-green-700 hover:text-green-700 text-sm">首页</a>
        <a href="<?= $baseUrl ?><?= $current - 1 ?>" class="px-3 py-1 border border-gray-300 rounded text-gray-600 hover:border-green-700 hover:text-green-700 text-sm">&lt; 前页</a>
    <?php endif; ?>
    
    <?php if ($start > 1): ?>
        <span class="px-2 text-gray-400">...</span>
    <?php endif; ?>
    
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <?php if ($i == $current): ?>
            <span class="px-3 py-1 bg-green-700 text-white rounded text-sm"><?= $i ?></span>
        <?php else: ?>
            <a href="<?= $baseUrl ?><?= $i ?>" class="px-3 py-1 border border-gray-300 rounded text-gray-600 hover:border-green-700 hover:text-green-700 text-sm"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    
    <?php if ($end < $total): ?>
        <span class="px-2 text-gray-400">...</span>
    <?php endif; ?>
    
    <?php if ($current < $total): ?>
        <a href="<?= $baseUrl ?><?= $current + 1 ?>" class="px-3 py-1 border border-gray-300 rounded text-gray-600 hover:border-green-700 hover:text-green-700 text-sm">后页 &gt;</a>
        <a href="<?= $baseUrl ?><?= $total ?>" class="px-3 py-1 border border-gray-300 rounded text-gray-600 hover:border-green-700 hover:text-green-700 text-sm">末页</a>
    <?php endif; ?>
</div>
