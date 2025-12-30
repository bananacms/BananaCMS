<?php
/**
 * Bilibili风格分页组件
 * 使用: <?php include TPL_PATH . 'bilibili/components/pagination.php'; ?>
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
        <a href="<?= $baseUrl ?>1" class="px-3 py-2 bg-white rounded-lg text-gray-600 hover:bg-pink-50 hover:text-pink-500 text-sm shadow-sm">首页</a>
        <a href="<?= $baseUrl ?><?= $current - 1 ?>" class="px-3 py-2 bg-white rounded-lg text-gray-600 hover:bg-pink-50 hover:text-pink-500 text-sm shadow-sm">上一页</a>
    <?php endif; ?>
    
    <?php if ($start > 1): ?>
        <span class="px-2 text-gray-400">...</span>
    <?php endif; ?>
    
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <?php if ($i == $current): ?>
            <span class="px-4 py-2 bg-pink-500 text-white rounded-lg text-sm shadow-sm"><?= $i ?></span>
        <?php else: ?>
            <a href="<?= $baseUrl ?><?= $i ?>" class="px-4 py-2 bg-white rounded-lg text-gray-600 hover:bg-pink-50 hover:text-pink-500 text-sm shadow-sm"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    
    <?php if ($end < $total): ?>
        <span class="px-2 text-gray-400">...</span>
    <?php endif; ?>
    
    <?php if ($current < $total): ?>
        <a href="<?= $baseUrl ?><?= $current + 1 ?>" class="px-3 py-2 bg-white rounded-lg text-gray-600 hover:bg-pink-50 hover:text-pink-500 text-sm shadow-sm">下一页</a>
        <a href="<?= $baseUrl ?><?= $total ?>" class="px-3 py-2 bg-white rounded-lg text-gray-600 hover:bg-pink-50 hover:text-pink-500 text-sm shadow-sm">末页</a>
    <?php endif; ?>
</div>
