<?php
/**
 * Netflix风格分页组件
 * 使用: <?php include TPL_PATH . 'netflix/components/pagination.php'; ?>
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
<div class="flex justify-center items-center space-x-2 mt-10">
    <?php if ($current > 1): ?>
        <a href="<?= $baseUrl ?>1" class="bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded transition text-sm">首页</a>
        <a href="<?= $baseUrl ?><?= $current - 1 ?>" class="bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded transition text-sm">上一页</a>
    <?php endif; ?>
    
    <?php if ($start > 1): ?>
        <span class="text-gray-600 px-2">...</span>
    <?php endif; ?>
    
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <?php if ($i == $current): ?>
            <span class="bg-red-600 px-4 py-2 rounded text-sm"><?= $i ?></span>
        <?php else: ?>
            <a href="<?= $baseUrl ?><?= $i ?>" class="bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded transition text-sm"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    
    <?php if ($end < $total): ?>
        <span class="text-gray-600 px-2">...</span>
    <?php endif; ?>
    
    <?php if ($current < $total): ?>
        <a href="<?= $baseUrl ?><?= $current + 1 ?>" class="bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded transition text-sm">下一页</a>
        <a href="<?= $baseUrl ?><?= $total ?>" class="bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded transition text-sm">末页</a>
    <?php endif; ?>
</div>
