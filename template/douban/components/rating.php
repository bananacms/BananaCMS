<?php
/**
 * 豆瓣风格评分星级组件
 * 使用: <?php include TPL_PATH . 'douban/components/rating.php'; ?>
 * 需要变量: $score (评分，0-10)
 */
$score = $score ?? 0;
$stars = round($score / 2); // 转换为5星制
$fullStars = floor($stars);
$halfStar = ($stars - $fullStars) >= 0.5;
$emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
?>
<span class="text-yellow-500 text-sm">
    <?php for ($i = 0; $i < $fullStars; $i++): ?>★<?php endfor; ?>
    <?php if ($halfStar): ?>☆<?php endif; ?>
    <?php for ($i = 0; $i < $emptyStars; $i++): ?>☆<?php endfor; ?>
</span>
<?php if ($score > 0): ?>
<span class="text-orange-500 text-sm ml-1 font-bold"><?php echo number_format($score, 1); ?></span>
<?php endif; ?>
