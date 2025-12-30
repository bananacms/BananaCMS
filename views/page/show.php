<?php
/**
 * 单页面显示
 */
$title = $pageTitle ?? '页面';
$keywords = $pageTitle;
$description = strip_tags(mb_substr($pageContent ?? '', 0, 150));
require VIEW_PATH . 'layouts/header.php';
?>

<div class="bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold mb-6 pb-4 border-b"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
    
    <div class="prose max-w-none">
        <?= $pageContent ?? '<p class="text-gray-500">暂无内容</p>' ?>
    </div>
</div>

<?php require VIEW_PATH . 'layouts/footer.php'; ?>
