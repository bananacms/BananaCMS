<?php
/**
 * 通用分页组件
 * 使用方法: include 'components/pagination.php'; 
 * 需要变量: $page, $totalPages, $baseUrl (不含page参数的URL)
 */
if (!isset($totalPages) || $totalPages <= 1) return;

$separator = strpos($baseUrl, '?') !== false ? '&' : '?';
$buildUrl = fn($p) => $baseUrl . $separator . 'page=' . $p;
?>
<div class="mt-4 flex items-center justify-center gap-2 flex-wrap">
    <?php if ($page > 1): ?>
    <a href="<?= $buildUrl($page - 1) ?>" class="px-3 py-1.5 bg-white border rounded hover:bg-gray-50 text-sm">上一页</a>
    <?php endif; ?>
    
    <?php if ($page > 3): ?>
    <a href="<?= $buildUrl(1) ?>" class="px-3 py-1.5 bg-white border rounded hover:bg-gray-50 text-sm">1</a>
    <?php if ($page > 4): ?>
    <span class="px-2 text-gray-400">...</span>
    <?php endif; ?>
    <?php endif; ?>
    
    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
    <a href="<?= $buildUrl($i) ?>" 
       class="px-3 py-1.5 border rounded text-sm <?= $i === $page ? 'bg-primary text-white border-primary' : 'bg-white hover:bg-gray-50' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
    
    <?php if ($page < $totalPages - 2): ?>
    <?php if ($page < $totalPages - 3): ?>
    <span class="px-2 text-gray-400">...</span>
    <?php endif; ?>
    <a href="<?= $buildUrl($totalPages) ?>" class="px-3 py-1.5 bg-white border rounded hover:bg-gray-50 text-sm"><?= $totalPages ?></a>
    <?php endif; ?>
    
    <?php if ($page < $totalPages): ?>
    <a href="<?= $buildUrl($page + 1) ?>" class="px-3 py-1.5 bg-white border rounded hover:bg-gray-50 text-sm">下一页</a>
    <?php endif; ?>
    
    <?php if ($totalPages > 5): ?>
    <span class="text-gray-400 text-sm ml-2">共 <?= $totalPages ?> 页</span>
    <div class="flex items-center gap-1 ml-2">
        <input type="number" id="pageJumpInput" min="1" max="<?= $totalPages ?>" value="<?= $page ?>" 
               class="w-16 px-2 py-1 border rounded text-sm text-center" 
               onkeydown="if(event.key==='Enter')goToPage('<?= $baseUrl ?>', <?= $totalPages ?>)">
        <button onclick="goToPage('<?= $baseUrl ?>', <?= $totalPages ?>)" 
                class="px-2 py-1 bg-gray-100 border rounded hover:bg-gray-200 text-sm">跳转</button>
    </div>
    <?php endif; ?>
</div>
