<?php
/**
 * 香蕉CMS 定时任务入口
 * Powered by https://xpornkit.com
 * 
 * 使用方法：
 * php cron.php collect              # 采集所有启用的采集站（只采新数据）
 * php cron.php collect --all        # 采集所有（新增+更新）
 * php cron.php collect --hours=24   # 只采24小时内更新的
 * php cron.php collect --id=1       # 只采集指定采集站
 * php cron.php collect --type=6     # 只采集指定分类
 * php cron.php auto                 # 执行自动采集任务（根据后台配置）
 * php cron.php ai_rewrite           # 执行AI内容改写
 * 
 * 宝塔定时任务设置：
 * 任务类型：Shell脚本
 * 脚本内容：/www/server/php/83/bin/php /www/wwwroot/你的域名/cron.php auto
 */

// 只允许命令行运行
if (php_sapi_name() !== 'cli') {
    die('Only CLI');
}

// 加载配置
require_once __DIR__ . '/config/config.php';

// 验证配置文件（仅在调试模式下显示警告）
if (APP_DEBUG) {
    require_once CORE_PATH . 'ConfigValidator.php';
    $validation = XpkConfigValidator::validate();
    if (!$validation['valid'] && !empty($validation['warnings'])) {
        // 在调试模式下记录警告
        error_log('配置验证警告: ' . implode(', ', $validation['warnings']));
    }
}

// 加载核心
require_once CORE_PATH . 'Database.php';
require_once CORE_PATH . 'Slug.php';
require_once MODEL_PATH . 'Model.php';
require_once MODEL_PATH . 'Collect.php';
require_once MODEL_PATH . 'CollectBind.php';
require_once MODEL_PATH . 'CollectLog.php';
require_once MODEL_PATH . 'Player.php';
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

/**
 * 下载图片到本地
 */
function downloadImage(string $url): ?string {
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }
    
    // 创建上传目录
    $uploadDir = ROOT_PATH . 'upload/vod/' . date('Ymd') . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // 获取文件扩展名
    $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
    if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $ext = 'jpg';
    }
    
    // 生成文件名
    $filename = md5($url . time()) . '.' . $ext;
    $filepath = $uploadDir . $filename;
    
    // 下载图片
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]);
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || empty($content)) {
        return null;
    }
    
    // 保存文件
    if (file_put_contents($filepath, $content) === false) {
        return null;
    }
    
    return '/upload/vod/' . date('Ymd') . '/' . $filename;
}

/**
 * 过滤播放源（自动创建缺失的播放器）
 */
function filterPlaySources(string $playFrom, string $playUrl, array &$enabledCodes): array {
    if (empty($playFrom) || empty($playUrl)) {
        return ['from' => $playFrom, 'url' => $playUrl];
    }
    
    // Auto-create missing players
    $db = XpkDatabase::getInstance();
    $fromArr = explode('$$$', $playFrom);
    $maxSort = $db->queryOne("SELECT MAX(player_sort) as max_sort FROM " . DB_PREFIX . "player")['max_sort'] ?? 100;
    
    foreach ($fromArr as $from) {
        $from = trim($from);
        if (empty($from)) continue;
        
        if (!in_array($from, $enabledCodes)) {
            $maxSort++;
            $playerName = $from;
            if (preg_match('/^(.+?)(m3u8|mp4)$/i', $from, $matches)) {
                $playerName = $matches[1] . '资源';
            }
            
            $db->execute(
                "INSERT IGNORE INTO " . DB_PREFIX . "player (player_name, player_code, player_sort, player_status, player_tip) VALUES (?, ?, ?, 1, ?)",
                [$playerName, $from, $maxSort, '采集自动创建']
            );
            $enabledCodes[] = $from;
        }
    }
    
    $urlArr = explode('$$$', $playUrl);
    
    $filteredFrom = [];
    $filteredUrl = [];
    
    foreach ($fromArr as $index => $from) {
        $from = trim($from);
        if (empty($from)) continue;
        
        if (in_array($from, $enabledCodes)) {
            $filteredFrom[] = $from;
            $filteredUrl[] = $urlArr[$index] ?? '';
        }
    }
    
    return [
        'from' => implode('$$$', $filteredFrom),
        'url' => implode('$$$', $filteredUrl)
    ];
}

/**
 * 执行采集
 */
function runCollect(array $options): void {
    $collectModel = new XpkCollect();
    $bindModel = new XpkCollectBind();
    $playerModel = new XpkPlayer();
    $db = XpkDatabase::getInstance();
    
    // 获取启用的播放器
    $enabledCodes = $playerModel->getEnabledCodes();
    
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
    
    $mode = isset($options['all']) ? 'all' : (isset($options['update']) ? 'update' : 'add');
    $hours = $options['hours'] ?? null;
    $typeId = isset($options['type']) ? (int)$options['type'] : null;
    $downloadPic = !empty($options['download_pic']);
    
    foreach ($collects as $collect) {
        clog("开始采集: {$collect['collect_name']}");
        
        // 获取绑定关系（使用新模型）
        $binds = $bindModel->getBinds($collect['collect_id']);
        
        if (empty($binds)) {
            clog("  跳过: 未绑定分类");
            continue;
        }
        
        // 创建采集日志
        $logModel = new XpkCollectLog();
        $logId = $logModel->start($collect['collect_id'], $collect['collect_name'], 'cron', $mode);
        
        // 获取采集配置
        $opts = [
            'hits_start' => (int)($collect['collect_opt_hits_start'] ?? 0),
            'hits_end' => (int)($collect['collect_opt_hits_end'] ?? 0),
            'score_start' => (float)($collect['collect_opt_score_start'] ?? 0),
            'score_end' => (float)($collect['collect_opt_score_end'] ?? 0),
            'dup_rule' => $collect['collect_opt_dup_rule'] ?? 'name',
            'update_fields' => explode(',', $collect['collect_opt_update_fields'] ?? 'remarks,content,play'),
            'play_merge' => (int)($collect['collect_opt_play_merge'] ?? 0)
        ];
        
        $page = 1;
        $totalAdded = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;
        
        while (true) {
            $listResult = $collectModel->getVideoList($collect, $page, $typeId, $hours);
            
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
            $skipped = 0;
            
            foreach ($videos as $video) {
                $localTypeId = $binds[$video['type_id']] ?? 0;
                if (!$localTypeId) {
                    $skipped++;
                    continue;
                }
                
                // 过滤关键词
                if (!empty($collect['collect_filter'])) {
                    $skip = false;
                    foreach (explode(',', $collect['collect_filter']) as $filter) {
                        if (stripos($video['vod_name'], trim($filter)) !== false) {
                            $skip = true;
                            break;
                        }
                    }
                    if ($skip) {
                        $skipped++;
                        continue;
                    }
                }
                
                // 过滤播放器
                if (!empty($enabledCodes)) {
                    $filtered = filterPlaySources($video['vod_play_from'], $video['vod_play_url'], $enabledCodes);
                    if (empty($filtered['from'])) {
                        $skipped++;
                        continue;
                    }
                    $video['vod_play_from'] = $filtered['from'];
                    $video['vod_play_url'] = $filtered['url'];
                }
                
                // 检查是否存在（根据重复规则）
                $sql = "SELECT vod_id, vod_lock, vod_play_from, vod_play_url FROM " . DB_PREFIX . "vod WHERE vod_name = ?";
                $params = [$video['vod_name']];
                
                switch ($opts['dup_rule']) {
                    case 'name_type':
                        $sql .= " AND vod_type_id = ?";
                        $params[] = $localTypeId;
                        break;
                    case 'name_year':
                        $sql .= " AND vod_year = ?";
                        $params[] = $video['vod_year'];
                        break;
                    case 'name_type_year':
                        $sql .= " AND vod_type_id = ? AND vod_year = ?";
                        $params[] = $localTypeId;
                        $params[] = $video['vod_year'];
                        break;
                }
                
                $exists = $db->queryOne($sql . " LIMIT 1", $params);
                
                if ($exists) {
                    if ($mode === 'add') {
                        $skipped++;
                        continue;
                    }
                    
                    // 检查锁定
                    if (!empty($exists['vod_lock'])) {
                        $skipped++;
                        continue;
                    }
                    
                    // 更新
                    $updateData = [];
                    $allowedFields = $opts['update_fields'];
                    
                    if (in_array('remarks', $allowedFields)) {
                        $updateData['vod_remarks'] = $video['vod_remarks'];
                    }
                    if (in_array('content', $allowedFields)) {
                        $updateData['vod_content'] = $video['vod_content'];
                    }
                    if (in_array('play', $allowedFields)) {
                        if ($opts['play_merge']) {
                            // 合并播放地址
                            $oldFrom = explode('$$$', $exists['vod_play_from']);
                            $oldUrl = explode('$$$', $exists['vod_play_url']);
                            $newFrom = explode('$$$', $video['vod_play_from']);
                            $newUrl = explode('$$$', $video['vod_play_url']);
                            
                            $merged = [];
                            foreach ($oldFrom as $i => $f) {
                                if ($f) $merged[$f] = $oldUrl[$i] ?? '';
                            }
                            foreach ($newFrom as $i => $f) {
                                if ($f) $merged[$f] = $newUrl[$i] ?? '';
                            }
                            
                            $updateData['vod_play_from'] = implode('$$$', array_keys($merged));
                            $updateData['vod_play_url'] = implode('$$$', array_values($merged));
                        } else {
                            $updateData['vod_play_from'] = $video['vod_play_from'];
                            $updateData['vod_play_url'] = $video['vod_play_url'];
                        }
                    }
                    
                    if (!empty($updateData)) {
                        $updateData['vod_time'] = time();
                        $sets = [];
                        $vals = [];
                        foreach ($updateData as $k => $v) {
                            $sets[] = "{$k} = ?";
                            $vals[] = $v;
                        }
                        $vals[] = $exists['vod_id'];
                        $db->execute("UPDATE " . DB_PREFIX . "vod SET " . implode(', ', $sets) . " WHERE vod_id = ?", $vals);
                        $updated++;
                    }
                } else {
                    if ($mode === 'update') {
                        $skipped++;
                        continue;
                    }
                    
                    // 生成随机数据
                    $hits = 0;
                    if ($opts['hits_start'] > 0 && $opts['hits_end'] > 0) {
                        $hits = rand(min($opts['hits_start'], $opts['hits_end']), max($opts['hits_start'], $opts['hits_end']));
                    }
                    
                    $score = $video['vod_score'];
                    if ($opts['score_start'] > 0 && $opts['score_end'] > 0) {
                        $min = min($opts['score_start'], $opts['score_end']);
                        $max = max($opts['score_start'], $opts['score_end']);
                        $score = round($min + mt_rand() / mt_getrandmax() * ($max - $min), 1);
                    }
                    
                    // 生成slug
                    $slug = xpk_slug_unique($video['vod_name'], 'vod', 'vod_slug');
                    
                    // 获取一级分类ID
                    $typeModel = new XpkType();
                    $topLevelId = $typeModel->getTopLevelId($localTypeId);
                    
                    // 下载图片（如果开启）
                    $picUrl = $video['vod_pic'];
                    if ($downloadPic && !empty($picUrl)) {
                        $localPic = downloadImage($picUrl);
                        if ($localPic) {
                            $picUrl = $localPic;
                        }
                    }
                    
                    $db->execute(
                        "INSERT INTO " . DB_PREFIX . "vod (vod_type_id, vod_type_id_1, vod_name, vod_sub, vod_en, vod_slug, vod_pic, vod_actor, vod_director, vod_year, vod_area, vod_lang, vod_score, vod_hits, vod_remarks, vod_content, vod_play_from, vod_play_url, vod_status, vod_collect_id, vod_time, vod_time_add) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?)",
                        [$localTypeId, $topLevelId, $video['vod_name'], $video['vod_sub'], $video['vod_en'], $slug, $picUrl, $video['vod_actor'], $video['vod_director'], $video['vod_year'], $video['vod_area'], $video['vod_lang'], $score, $hits, $video['vod_remarks'], $video['vod_content'], $video['vod_play_from'], $video['vod_play_url'], $collect['collect_id'], time(), time()]
                    );
                    $added++;
                }
            }
            
            $totalAdded += $added;
            $totalUpdated += $updated;
            $totalSkipped += $skipped;
            clog("  第{$page}/{$listResult['pagecount']}页: 新增{$added} 更新{$updated} 跳过{$skipped}");
            
            if ($page >= $listResult['pagecount']) {
                break;
            }
            $page++;
            usleep(500000); // 0.5秒延迟
        }
        
        clog("  完成: 共新增{$totalAdded} 更新{$totalUpdated} 跳过{$totalSkipped}");
        
        // 完成采集日志
        $logModel->finish($logId, $page - 1, $totalAdded, $totalUpdated, $totalSkipped);
    }
    
    clog('采集任务结束');
}

/**
 * 执行自动采集（根据后台配置）
 */
function runAuto(): void {
    $db = XpkDatabase::getInstance();
    
    // 获取自动采集配置
    $config = $db->queryOne("SELECT config_value FROM " . DB_PREFIX . "config WHERE config_name = 'cron_collect'");
    
    if (!$config || empty($config['config_value'])) {
        clog('未配置自动采集任务');
        return;
    }
    
    $settings = json_decode($config['config_value'], true);
    
    if (empty($settings['enabled'])) {
        clog('自动采集已禁用');
        return;
    }
    
    // 检查执行间隔
    $lastRun = $db->queryOne("SELECT config_value FROM " . DB_PREFIX . "config WHERE config_name = 'cron_last_run'");
    $lastTime = $lastRun ? (int)$lastRun['config_value'] : 0;
    $interval = (int)($settings['interval'] ?? 60) * 60; // 转换为秒
    
    if (time() - $lastTime < $interval) {
        clog('未到执行时间，跳过');
        return;
    }
    
    // 更新最后执行时间
    if ($lastRun) {
        $db->execute("UPDATE " . DB_PREFIX . "config SET config_value = ? WHERE config_name = 'cron_last_run'", [time()]);
    } else {
        $db->execute("INSERT INTO " . DB_PREFIX . "config (config_name, config_value) VALUES ('cron_last_run', ?)", [time()]);
    }
    
    // 构建采集参数
    $options = [];
    
    if (!empty($settings['mode'])) {
        if ($settings['mode'] === 'all') $options['all'] = true;
        if ($settings['mode'] === 'update') $options['update'] = true;
    }
    
    if (!empty($settings['hours'])) {
        $options['hours'] = $settings['hours'];
    }
    
    if (!empty($settings['download_pic'])) {
        $options['download_pic'] = true;
    }
    
    if (!empty($settings['collect_ids'])) {
        // 逐个采集指定的采集站
        foreach ($settings['collect_ids'] as $id) {
            $options['id'] = $id;
            runCollect($options);
        }
    } else {
        // 采集所有启用的采集站
        runCollect($options);
    }
}

/**
 * AI 内容改写任务
 */
function runAiRewrite(array $options): void {
    require_once CORE_PATH . 'AiRewrite.php';
    
    if (!XpkAiRewrite::isEnabled()) {
        clog('AI 改写功能未启用或配置不完整');
        return;
    }
    
    $ai = XpkAiRewrite::fromDatabase();
    if (!$ai) {
        clog('加载 AI 配置失败');
        return;
    }
    
    $db = XpkDatabase::getInstance();
    
    // 获取批量大小
    $batchSize = (int)($db->queryOne(
        "SELECT config_value FROM " . DB_PREFIX . "config WHERE config_name = 'ai_batch_size'"
    )['config_value'] ?? 10);
    
    // 支持命令行参数覆盖
    if (!empty($options['batch'])) {
        $batchSize = (int)$options['batch'];
    }
    
    clog("开始 AI 改写任务，每批处理 {$batchSize} 条");
    
    // 获取待处理的视频
    $videos = $db->query(
        "SELECT vod_id, vod_name, vod_content FROM " . DB_PREFIX . "vod 
         WHERE vod_ai_rewrite = 0 AND vod_content != '' AND LENGTH(vod_content) > 20
         ORDER BY vod_id DESC LIMIT ?",
        [$batchSize]
    );
    
    if (empty($videos)) {
        clog('没有待处理的视频');
        return;
    }
    
    clog("找到 " . count($videos) . " 条待处理视频");
    
    $processed = 0;
    $failed = 0;
    
    foreach ($videos as $video) {
        clog("处理: [{$video['vod_id']}] {$video['vod_name']}");
        
        $rewritten = $ai->rewrite($video['vod_content']);
        
        if ($rewritten) {
            $db->execute(
                "UPDATE " . DB_PREFIX . "vod SET vod_content = ?, vod_ai_rewrite = 1 WHERE vod_id = ?",
                [$rewritten, $video['vod_id']]
            );
            $processed++;
            clog("  ✓ 改写成功");
        } else {
            $db->execute(
                "UPDATE " . DB_PREFIX . "vod SET vod_ai_rewrite = 2 WHERE vod_id = ?",
                [$video['vod_id']]
            );
            $failed++;
            clog("  ✗ 改写失败");
        }
        
        // 避免请求过快
        usleep(500000);
    }
    
    clog("完成：成功 {$processed} 条，失败 {$failed} 条");
}

// 主逻辑
switch ($action) {
    case 'collect':
        runCollect($options);
        break;
    case 'auto':
        runAuto();
        break;
    case 'ai_rewrite':
        runAiRewrite($options);
        break;
    default:
        echo "香蕉CMS 定时任务\n";
        echo "用法:\n";
        echo "  php cron.php collect              采集所有启用的采集站\n";
        echo "  php cron.php collect --all        采集全部（新增+更新）\n";
        echo "  php cron.php collect --update     只更新已有视频\n";
        echo "  php cron.php collect --hours=24   只采24小时内更新的\n";
        echo "  php cron.php collect --id=1       只采集指定采集站\n";
        echo "  php cron.php collect --type=6     只采集指定分类\n";
        echo "  php cron.php auto                 执行自动采集（根据后台配置）\n";
        echo "  php cron.php ai_rewrite           执行 AI 内容改写\n";
        echo "  php cron.php ai_rewrite --batch=20  指定每批处理数量\n";
        break;
}
