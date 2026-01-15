<?php
/**
 * Sitemap 生成器（支持分片和URL模式）
 * /sitemap.xml - 索引文件
 * /sitemap.xml?type=vod&page=1 - 视频分片
 * Powered by https://xpornkit.com
 */

require_once __DIR__ . '/config/config.php';
require_once CORE_PATH . 'Database.php';
require_once CORE_PATH . 'Cache.php';
require_once CORE_PATH . 'Router.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = rtrim(SITE_URL, '/');
$db = XpkDatabase::getInstance();
$type = $_GET['type'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5000; // 每个分片最多5000条

// 获取URL模式配置
$config = xpk_cache()->get('site_config') ?: [];
$urlMode = $config['url_mode'] ?? '4';

// 生成索引或分片
if (empty($type)) {
    generateIndex($db, $baseUrl, $perPage);
} else {
    generateSitemap($db, $baseUrl, $type, $page, $perPage, $urlMode);
}

/**
 * 生成 Sitemap 索引
 */
function generateIndex($db, $baseUrl, $perPage) {
    $sitemaps = [];
    
    // 主站点地图（首页、分类等静态页）
    $sitemaps[] = ['loc' => $baseUrl . '/sitemap.xml?type=main', 'lastmod' => date('Y-m-d')];
    
    // 视频分片
    $vodCount = $db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "vod WHERE vod_status = 1")['cnt'];
    $vodPages = ceil($vodCount / $perPage);
    for ($i = 1; $i <= $vodPages; $i++) {
        $sitemaps[] = ['loc' => $baseUrl . '/sitemap.xml?type=vod&page=' . $i, 'lastmod' => date('Y-m-d')];
    }
    
    // 演员分片
    $actorCount = $db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "actor WHERE actor_status = 1")['cnt'];
    if ($actorCount > 0) {
        $actorPages = ceil($actorCount / $perPage);
        for ($i = 1; $i <= $actorPages; $i++) {
            $sitemaps[] = ['loc' => $baseUrl . '/sitemap.xml?type=actor&page=' . $i, 'lastmod' => date('Y-m-d')];
        }
    }
    
    // 文章分片
    $artCount = $db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "art WHERE art_status = 1")['cnt'];
    if ($artCount > 0) {
        $artPages = ceil($artCount / $perPage);
        for ($i = 1; $i <= $artPages; $i++) {
            $sitemaps[] = ['loc' => $baseUrl . '/sitemap.xml?type=art&page=' . $i, 'lastmod' => date('Y-m-d')];
        }
    }
    
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($sitemaps as $sitemap) {
        echo "  <sitemap>\n";
        echo "    <loc>" . htmlspecialchars($sitemap['loc']) . "</loc>\n";
        echo "    <lastmod>" . $sitemap['lastmod'] . "</lastmod>\n";
        echo "  </sitemap>\n";
    }
    echo '</sitemapindex>';
}

/**
 * 生成具体 Sitemap
 */
function generateSitemap($db, $baseUrl, $type, $page, $perPage, $urlMode) {
    $offset = ($page - 1) * $perPage;
    $urls = [];
    
    switch ($type) {
        case 'main':
            // 首页
            $urls[] = ['loc' => $baseUrl . '/', 'lastmod' => date('Y-m-d'), 'changefreq' => 'daily', 'priority' => '1.0'];
            
            // 分类页
            $types = $db->query("SELECT type_id, type_en FROM " . DB_PREFIX . "type WHERE type_status = 1");
            foreach ($types as $t) {
                $slug = !empty($t['type_en']) ? $t['type_en'] : $t['type_id'];
                $urls[] = ['loc' => $baseUrl . xpk_page_url('type', ['id' => $t['type_id'], 'slug' => $slug]), 'changefreq' => 'daily', 'priority' => '0.8'];
            }
            
            // 演员列表
            $urls[] = ['loc' => $baseUrl . '/actor', 'changefreq' => 'weekly', 'priority' => '0.7'];
            
            // 文章列表
            $urls[] = ['loc' => $baseUrl . '/art', 'changefreq' => 'weekly', 'priority' => '0.6'];
            break;
            
        case 'vod':
            $vods = $db->query(
                "SELECT vod_id, vod_slug, vod_time FROM " . DB_PREFIX . "vod WHERE vod_status = 1 ORDER BY vod_id DESC LIMIT ? OFFSET ?",
                [$perPage, $offset]
            );
            foreach ($vods as $vod) {
                $slug = !empty($vod['vod_slug']) ? $vod['vod_slug'] : $vod['vod_id'];
                $urls[] = [
                    'loc' => $baseUrl . xpk_page_url('vod_detail', ['id' => $vod['vod_id'], 'slug' => $slug]),
                    'lastmod' => date('Y-m-d', $vod['vod_time']),
                    'changefreq' => 'weekly',
                    'priority' => '0.9'
                ];
            }
            break;
            
        case 'actor':
            $actors = $db->query(
                "SELECT actor_id, actor_slug, actor_time FROM " . DB_PREFIX . "actor WHERE actor_status = 1 ORDER BY actor_id DESC LIMIT ? OFFSET ?",
                [$perPage, $offset]
            );
            foreach ($actors as $actor) {
                $slug = !empty($actor['actor_slug']) ? $actor['actor_slug'] : $actor['actor_id'];
                $urls[] = [
                    'loc' => $baseUrl . xpk_page_url('actor_detail', ['id' => $actor['actor_id'], 'slug' => $slug]),
                    'lastmod' => date('Y-m-d', $actor['actor_time'] ?? time()),
                    'changefreq' => 'weekly',
                    'priority' => '0.6'
                ];
            }
            break;
            
        case 'art':
            $arts = $db->query(
                "SELECT art_id, art_slug, art_time FROM " . DB_PREFIX . "art WHERE art_status = 1 ORDER BY art_id DESC LIMIT ? OFFSET ?",
                [$perPage, $offset]
            );
            foreach ($arts as $art) {
                $slug = !empty($art['art_slug']) ? $art['art_slug'] : $art['art_id'];
                $urls[] = [
                    'loc' => $baseUrl . xpk_page_url('art_detail', ['id' => $art['art_id'], 'slug' => $slug]),
                    'lastmod' => date('Y-m-d', $art['art_time']),
                    'changefreq' => 'monthly',
                    'priority' => '0.5'
                ];
            }
            break;
    }
    
    // Submit to IndexNow (batch) - limit to 100 URLs to avoid timeout
    if (!empty($urls) && $page == 1) { // Only submit first page to avoid duplicate
        $urlsToSubmit = array_slice($urls, 0, 100); // Limit to 100 to prevent timeout
        submitToIndexNow($urlsToSubmit);
    }
    
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $url) {
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
        if (!empty($url['lastmod'])) {
            echo "    <lastmod>" . $url['lastmod'] . "</lastmod>\n";
        }
        if (!empty($url['changefreq'])) {
            echo "    <changefreq>" . $url['changefreq'] . "</changefreq>\n";
        }
        if (!empty($url['priority'])) {
            echo "    <priority>" . $url['priority'] . "</priority>\n";
        }
        echo "  </url>\n";
    }
    echo '</urlset>';
}

/**
 * Submit URLs to IndexNow
 */
function submitToIndexNow($urls) {
    if (empty($urls)) {
        return;
    }
    
    try {
        require_once CORE_PATH . 'IndexNow.php';
        $indexNow = new XpkIndexNow();
        
        // Extract URLs from sitemap data
        $urlList = array_map(function($item) {
            return $item['loc'];
        }, $urls);
        
        // Submit in batches of 100 (IndexNow limit)
        $batches = array_chunk($urlList, 100);
        foreach ($batches as $batch) {
            $indexNow->submitUrls($batch);
            usleep(100000); // 100ms delay between batches
        }
    } catch (Exception $e) {
        error_log('IndexNow submission error: ' . $e->getMessage());
    }
}
