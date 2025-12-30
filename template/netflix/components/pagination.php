<?php
/**
 * Netflix风格分页组件
 * 使用方法: include TEMPLATE_PATH . 'netflix/components/pagination.php';
 * 需要变量: $page, $totalPages, $baseUrl (可选)
 */

if (!isset($page) || !isset($totalPages) || $totalPages <= 1) {
    return;
}

$baseUrl = $baseUrl ?? '?';
$separator = strpos($baseUrl, '?') !== false ? '&' : '?';
?>

<div class="flex justify-center items-center space-x-2 mt-10">
    <?php if ($page > 1): ?>
    <a href="<?php echo $baseUrl . $separator . 'page=' . ($page - 1); ?>" class="bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded transition">
        上一页
    </a>
    <?php endif; ?>
    
    <?php
    // 显示页码
    $start = max(1, $page - 2);
    $end = min($totalPages, $page + 2);
    
    if ($start > 1): ?>
    <a href="<?php echo $baseUrl . $separator . 'page=1'; ?>" class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded transition">1</a>
    <?php if ($start > 2): ?>
    <span class="text-gray-600 px-2">...</span>
    <?php endif; ?>
    <?php endif; ?>
    
    <?php for ($i = $start; $i <= $end; $i++): ?>
    <a href="<?php echo $baseUrl . $separator . 'page=' . $i; ?>" 
       class="<?php echo $i == $page ? 'bg-red-600' : 'bg-gray-800 hover:bg-gray-700'; ?> px-3 py-2 rounded transition">
        <?php echo $i; ?>
    </a>
    <?php endfor; ?>
    
    <?php if ($end < $totalPages): ?>
    <?php if ($end < $totalPages - 1): ?>
    <span class="text-gray-600 px-2">...</span>
    <?php endif; ?>
    <a href="<?php echo $baseUrl . $separator . 'page=' . $totalPages; ?>" class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded transition"><?php echo $totalPages; ?></a>
    <?php endif; ?>
    
    <?php if ($page < $totalPages): ?>
    <a href="<?php echo $baseUrl . $separator . 'page=' . ($page + 1); ?>" class="bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded transition">
        下一页
    </a>
    <?php endif; ?>
</div>
