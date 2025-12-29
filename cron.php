<?php
/**
 * 香蕉CMS 定时任务入口
 * Powered by https://xpornkit.com
 * 
 * 使用方法：
 * php cron.php collect           # 采集所有启用的采集站（只采新数据）
 * php cron.php collect --all     # 采集所有（新增+更新）
 * php cron.php collect --hours=24  # 只采24小时内更新的
 * php cron.php collect --id=1    # 只采集指定采集站
 * 
 * 定时任务示例（每小时执行）：
 * 0 * * * * php /www/bananacms/cron.php collect --hours=6 >> /www/bananacms/runtime/cron.log 2>&1
 */

// 只允许命令行运行
if (php_sapi_name() !== 'cli') {
    die('Only CLI');
}

// 加载配置
require_once __DIR__ . '/config/config.php';

// 加载核心
require_once CORE_PATH . 'Database.php';
require_once MODEL_PATH . 'Model.php';
require_once MODEL_PATH . 'Collect.php';
require_once MODEL_PATH . 'Vod.php';
require_once MODEL_PATH . 'Type.php';

// 解析参数
$action = $argv[1] ?? '';
$options = [];
for ($i = 2; $i < count($argv); $i++) {
    if (preg_match('/^--(\w+)(?:=(.+))?$/', $argv[$i], $m)) {
        $options[$m[1]] = $m[2] ?? true;
    }
}

// 日志函数
function clog(string $msg): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
}

// 执行采集
function runCollect(array $options): void {
    $collectModel = new XpkCollect();
    $db = XpkDatabase::getInstance();
    
    // 获取采集站
    if (!empty($options['id'])) {
        $collect = $collectModel->find((int)$options['id']);
        $collects = $collect ? [$collect] : [];
    } else {
        $collects = $collectModel->getEnabled();
    }
    
    if (empty($collects)) {
        clog('没有可用的采集站');
        return;
    }
    
    $mode = isset($options['all']) ? 'all' : 'add';
    $hours = $options['hours'] ?? null;
    
    foreach ($collects as $collect) {
        clog("开始采集: {$collect['collect_name']}");
        
        // 获取绑定关系
        $binds = [];
        if (!empty($collect['collect_bind'])) {
            $binds = json_decode($collect['collect_bind'], true) ?: [];
        }
        
        if (empty($binds)) {
            clog("  跳过: 未绑定分类");
            continue;
        }
        
        $page = 1;
        $totalAdded = 0;
        $totalUpdated = 0;
        
        while (true) {
            $listResult = $collectModel->getVideoList($collect, $page, null, $hours);
            
            if (!$listResult || empty($listResult['list'])) {
                break;
            }
            
            $ids = array_column($listResult['list'], 'vod_id');
            $videos = $collectModel->getVideoDetail($collect, $ids);
            
            if (!$videos) {
                clog("  第{$page}页: 获取详情失败");
                $page++;
                continue;
            }
            
            $added = 0;
            $updated = 0;
            
            foreach ($videos as $video) {
                $localTypeId = $binds[$video['type_id']] ?? 0;
                if (!$localTypeId) continue;
                
                // 过滤
                if (!empty($collect['collect_filter'])) {
                    $skip = false;
                    foreach (explode(',', $collect['collect_filter']) as $filter) {
                        if (stripos($video['vod_name'], trim($filter)) !== false) {
                            $skip = true;
                            break;
                        }
                    }
                    if ($skip) continue;
                }
                
                // 检查是否存在
                $exists = $db->queryOne(
                    "SELECT vod_id FROM " . DB_PREFIX . "vod WHERE vod_name = ? LIMIT 1",
                    [$video['vod_name']]
                );
                
                if ($exists) {
                    if ($mode === 'add') continue;
                    
                    $db->execute(
                        "UPDATE " . DB_PREFIX . "vod SET vod_remarks = ?, vod_play_from = ?, vod_play_url = ?, vod_time = ? WHERE vod_id = ?",
                        [$video['vod_remarks'], $video['vod_play_from'], $video['vod_play_url'], time(), $exists['vod_id']]
                    );
                    $updated++;
                } else {
                    if ($mode === 'update') continue;
                    
                    $db->execute(
                        "INSERT INTO " . DB_PREFIX . "vod (vod_type_id, vod_name, vod_sub, vod_en, vod_pic, vod_actor, vod_director, vod_year, vod_area, vod_lang, vod_score, vod_remarks, vod_content, vod_play_from, vod_play_url, vod_status, vod_time, vod_time_add) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)",
                        [$localTypeId, $video['vod_name'], $video['vod_sub'], $video['vod_en'], $video['vod_pic'], $video['vod_actor'], $video['vod_director'], $video['vod_year'], $video['vod_area'], $video['vod_lang'], $video['vod_score'], $video['vod_remarks'], $video['vod_content'], $video['vod_play_from'], $video['vod_play_url'], time(), time()]
                    );
                    $added++;
                }
            }
            
            $totalAdded += $added;
            $totalUpdated += $updated;
            clog("  第{$page}/{$listResult['pagecount']}页: 新增{$added} 更新{$updated}");
            
            if ($page >= $listResult['pagecount']) {
                break;
            }
            $page++;
            usleep(500000); // 0.5秒延迟
        }
        
        clog("  完成: 共新增{$totalAdded} 更新{$totalUpdated}");
    }
    
    clog('采集任务结束');
}

// 主逻辑
switch ($action) {
    case 'collect':
        runCollect($options);
        break;
    default:
        echo "香蕉CMS 定时任务\n";
        echo "用法:\n";
        echo "  php cron.php collect           采集所有启用的采集站\n";
        echo "  php cron.php collect --all     采集全部（新增+更新）\n";
        echo "  php cron.php collect --hours=24  只采24小时内更新的\n";
        echo "  php cron.php collect --id=1    只采集指定采集站\n";
        break;
}
