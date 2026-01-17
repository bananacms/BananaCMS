<?php
/**
 * 后台采集管理控制器
 * Powered by https://xpornkit.com
 */

class AdminCollectController extends AdminBaseController
{
    private XpkCollect $collectModel;
    private XpkVod $vodModel;
    private XpkType $typeModel;
    private XpkPlayer $playerModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Collect.php';
        require_once MODEL_PATH . 'CollectBind.php';
        require_once MODEL_PATH . 'Player.php';
        $this->collectModel = new XpkCollect();
        $this->vodModel = new XpkVod();
        $this->typeModel = new XpkType();
        $this->playerModel = new XpkPlayer();
    }

    /**
     * 采集站列表
     */
    public function index(): void
    {
        $collects = $this->collectModel->getAll();
        $this->assign('collects', $collects);
        $this->assign('flash', $this->getFlash());
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('collect/index', '采集管理');
    }

    /**
     * 添加采集站
     */
    public function add(): void
    {
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('collect/form', '添加采集站');
    }

    /**
     * 处理添加
     */
    public function doAdd(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = $this->getCollectFormData();

        if (empty($data['collect_name']) || empty($data['collect_api'])) {
            $this->error('名称和API地址不能为空');
        }

        $this->collectModel->insert($data);
        $this->log('添加', '采集', $data['collect_name']);
        $this->success('添加成功', ['url' => '/' . $this->adminEntry . '?s=collect']);
    }

    /**
     * 获取采集站表单数据
     */
    private function getCollectFormData(): array
    {
        // 处理更新字段复选框
        $updateFields = $this->post('collect_opt_update_fields', []);
        if (is_array($updateFields)) {
            $updateFields = implode(',', $updateFields);
        }

        return [
            'collect_name' => trim($this->post('collect_name', '')),
            'collect_api' => trim($this->post('collect_api', '')),
            'collect_type' => trim($this->post('collect_type', 'json')),
            'collect_status' => (int)$this->post('collect_status', 1),
            'collect_filter' => trim($this->post('collect_filter', '')),
            'collect_param' => trim($this->post('collect_param', '')),
            'collect_opt_hits_start' => (int)$this->post('collect_opt_hits_start', 0),
            'collect_opt_hits_end' => (int)$this->post('collect_opt_hits_end', 0),
            'collect_opt_score_start' => (float)$this->post('collect_opt_score_start', 0),
            'collect_opt_score_end' => (float)$this->post('collect_opt_score_end', 0),
            'collect_opt_dup_rule' => trim($this->post('collect_opt_dup_rule', 'name')),
            'collect_opt_update_fields' => $updateFields ?: 'remarks,content,play',
            'collect_opt_play_merge' => (int)$this->post('collect_opt_play_merge', 0)
        ];
    }

    /**
     * 编辑采集站
     */
    public function edit(int $id): void
    {
        $collect = $this->collectModel->find($id);
        if (!$collect) {
            $this->flash('error', '采集站不存在');
            $this->redirect('/' . $this->adminEntry . '?s=collect');
        }

        $this->assign('collect', $collect);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('collect/form', '编辑采集站');
    }

    /**
     * 处理编辑
     */
    public function doEdit(int $id): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = $this->getCollectFormData();

        $this->collectModel->update($id, $data);
        $this->log('编辑', '采集', "ID:{$id} {$data['collect_name']}");
        $this->success('保存成功', ['url' => '/' . $this->adminEntry . '?s=collect']);
    }

    /**
     * 删除采集站
     */
    public function delete(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $id = (int)$this->post('id', 0);
        if ($id > 0) {
            // 删除前先查询采集站详情，用于日志记录
            $collect = $this->collectModel->find($id);
            if (!$collect) {
                $this->error('采集站不存在');
            }
            
            $this->collectModel->delete($id);
            $this->log('删除', '采集', "ID:{$id} 《{$collect['collect_name']}》 {$collect['collect_api']}");
            $this->success('删除成功');
        } else {
            $this->error('参数错误');
        }
    }

    /**
     * 删除采集站的所有视频
     */
    public function deleteVods(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $collectId = (int)$this->post('collect_id', 0);
        if ($collectId <= 0) {
            $this->error('参数错误');
        }

        $collect = $this->collectModel->find($collectId);
        if (!$collect) {
            $this->error('采集站不存在');
        }

        $db = XpkDatabase::getInstance();
        
        // 统计数量
        $count = $db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "vod WHERE vod_collect_id = ?",
            [$collectId]
        )['cnt'] ?? 0;

        if ($count == 0) {
            $this->error('该采集站暂无视频');
        }

        // 删除视频
        $db->execute(
            "DELETE FROM " . DB_PREFIX . "vod WHERE vod_collect_id = ?",
            [$collectId]
        );

        $this->log('清空视频', '采集', "采集站:{$collect['collect_name']} 删除{$count}条");
        $this->success("已删除 {$count} 条视频");
    }

    /**
     * 分类绑定页面
     */
    public function bind(int $id): void
    {
        $collect = $this->collectModel->find($id);
        if (!$collect) {
            $this->flash('error', '采集站不存在');
            $this->redirect('/' . $this->adminEntry . '?s=collect');
        }

        // 获取远程分类
        $remoteCategories = $this->collectModel->getCategories($collect);
        if ($remoteCategories === null) {
            $this->flash('error', '获取远程分类失败，请检查API地址');
            $this->redirect('/' . $this->adminEntry . '?s=collect');
        }

        // 获取本地分类
        $localTypes = $this->typeModel->getTree();

        // 获取绑定关系（使用新模型）
        $bindModel = new XpkCollectBind();
        $binds = $bindModel->getBinds($id);
        
        // 获取全局绑定
        $globalBinds = $bindModel->getGlobalBinds();
        
        // 获取所有采集站（用于复制绑定）
        $allCollects = $this->collectModel->getAll();

        $this->assign('collect', $collect);
        $this->assign('remoteCategories', $remoteCategories);
        $this->assign('localTypes', $localTypes);
        $this->assign('binds', $binds);
        $this->assign('globalBinds', $globalBinds);
        $this->assign('allCollects', $allCollects);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('collect/bind', '分类绑定');
    }

    /**
     * 同步远程分类到本地 (AJAX)
     * 
     * 直接使用远程分类ID作为本地分类ID，避免绑定问题
     */
    public function syncCategories(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $id = (int)$this->post('id', 0);
        
        $collect = $this->collectModel->find($id);
        if (!$collect) {
            $this->error('采集站不存在');
        }

        // 获取远程分类
        $remoteCategories = $this->collectModel->getCategories($collect);
        if (!$remoteCategories) {
            $this->error('获取远程分类失败');
        }

        // 获取本地已有分类（ID和名称）
        $localTypes = $this->typeModel->getAll();
        $localById = [];
        $localByName = [];
        foreach ($localTypes as $t) {
            $localById[$t['type_id']] = $t['type_name'];
            $localByName[$t['type_name']] = $t['type_id'];
        }

        $added = 0;
        $skipped = 0;
        $conflicts = [];
        $db = XpkDatabase::getInstance();
        $bindModel = new XpkCollectBind();
        $remoteNames = [];
        $bindData = [];

        // 先处理一级分类（pid=0）
        foreach ($remoteCategories as $cat) {
            $pid = $cat['pid'] ?? 0;
            if ($pid != 0) continue;
            
            $remoteId = (int)$cat['id'];
            $name = trim($cat['name']);
            $remoteNames[$remoteId] = $name;
            
            // 检查本地是否有同ID分类
            if (isset($localById[$remoteId])) {
                // ID相同，检查名称是否匹配
                if ($localById[$remoteId] === $name) {
                    // 名称也相同，直接绑定
                    $bindData[$remoteId] = $remoteId;
                    $skipped++;
                } else {
                    // ID相同但名称不同，尝试按名称匹配
                    if (isset($localByName[$name])) {
                        $bindData[$remoteId] = $localByName[$name];
                        $skipped++;
                    } else {
                        // 记录冲突，不自动绑定
                        $conflicts[] = "远程「{$name}」(ID:{$remoteId}) 与本地「{$localById[$remoteId]}」(ID:{$remoteId}) ID冲突";
                        $skipped++;
                    }
                }
                continue;
            }
            
            // 检查本地是否有同名分类
            if (isset($localByName[$name])) {
                $bindData[$remoteId] = $localByName[$name];
                $skipped++;
                continue;
            }

            // 生成slug
            require_once CORE_PATH . 'Slug.php';
            $slug = xpk_slug_unique($name, 'type', 'type_en');

            // 直接使用远程ID插入
            $db->execute(
                "INSERT INTO " . DB_PREFIX . "type (type_id, type_pid, type_name, type_en, type_sort, type_status) VALUES (?, 0, ?, ?, ?, 1)",
                [$remoteId, $name, $slug, $remoteId]
            );
            $localById[$remoteId] = $name;
            $localByName[$name] = $remoteId;
            $bindData[$remoteId] = $remoteId;
            $added++;
        }

        // 再处理子分类（pid!=0）
        foreach ($remoteCategories as $cat) {
            $pid = $cat['pid'] ?? 0;
            if ($pid == 0) continue;
            
            $remoteId = (int)$cat['id'];
            $name = trim($cat['name']);
            $remotePid = (int)$pid;
            $remoteNames[$remoteId] = $name;
            
            // 检查本地是否有同ID分类
            if (isset($localById[$remoteId])) {
                if ($localById[$remoteId] === $name) {
                    $bindData[$remoteId] = $remoteId;
                    $skipped++;
                } else {
                    if (isset($localByName[$name])) {
                        $bindData[$remoteId] = $localByName[$name];
                        $skipped++;
                    } else {
                        $conflicts[] = "远程「{$name}」(ID:{$remoteId}) 与本地「{$localById[$remoteId]}」(ID:{$remoteId}) ID冲突";
                        $skipped++;
                    }
                }
                continue;
            }

            // 检查本地是否有同名分类
            if (isset($localByName[$name])) {
                $bindData[$remoteId] = $localByName[$name];
                $skipped++;
                continue;
            }

            // 检查父分类是否存在
            $localPid = $bindData[$remotePid] ?? 0;
            if (!$localPid) {
                continue;
            }

            // 生成slug
            require_once CORE_PATH . 'Slug.php';
            $slug = xpk_slug_unique($name, 'type', 'type_en');

            // 直接使用远程ID插入
            $db->execute(
                "INSERT INTO " . DB_PREFIX . "type (type_id, type_pid, type_name, type_en, type_sort, type_status) VALUES (?, ?, ?, ?, ?, 1)",
                [$remoteId, $localPid, $name, $slug, $remoteId]
            );
            $localById[$remoteId] = $name;
            $localByName[$name] = $remoteId;
            $bindData[$remoteId] = $remoteId;
            $added++;
        }

        // 保存绑定关系
        $bindModel->saveBinds($id, $bindData, $remoteNames);

        $this->log('同步分类', '采集', "ID:{$id} 新增{$added}个，跳过{$skipped}个，冲突" . count($conflicts) . "个");
        
        $msg = "同步完成！新增 {$added} 个，已匹配 {$skipped} 个";
        if (!empty($conflicts)) {
            $msg .= "，有 " . count($conflicts) . " 个ID冲突需手动绑定";
        }
        $this->success($msg);
    }

    /**
     * 保存分类绑定
     */
    public function saveBind(int $id): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $binds = $this->post('bind', []);
        $remoteNames = $this->post('remote_name', []);
        $saveAsGlobal = (int)$this->post('save_as_global', 0);
        
        $bindData = [];
        $nameData = [];
        
        foreach ($binds as $remoteId => $localId) {
            $bindData[$remoteId] = (int)$localId;
            $nameData[$remoteId] = $remoteNames[$remoteId] ?? '';
        }

        $bindModel = new XpkCollectBind();
        
        // 保存到指定采集站
        $bindModel->saveBinds($id, $bindData, $nameData);
        
        // 如果选择同时保存为全局绑定
        if ($saveAsGlobal) {
            $bindModel->saveGlobalBinds($bindData, $nameData);
        }

        $this->success('绑定保存成功', ['url' => '/' . $this->adminEntry . '?s=collect']);
    }

    /**
     * 从其他采集站复制绑定
     */
    public function copyBind(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $fromId = (int)$this->post('from_id', 0);
        $toId = (int)$this->post('to_id', 0);
        
        if ($fromId <= 0 || $toId <= 0) {
            $this->error('参数错误');
        }
        
        $bindModel = new XpkCollectBind();
        $count = $bindModel->copyBinds($fromId, $toId);
        
        $this->log('复制绑定', '采集', "从ID:{$fromId}复制到ID:{$toId}，共{$count}条");
        $this->success("已复制 {$count} 条绑定关系");
    }

    /**
     * 采集页面
     */
    public function run(int $id): void
    {
        $collect = $this->collectModel->find($id);
        if (!$collect) {
            $this->flash('error', '采集站不存在');
            $this->redirect('/' . $this->adminEntry . '?s=collect');
        }

        // 获取远程分类
        $remoteCategories = $this->collectModel->getCategories($collect);

        $this->assign('collect', $collect);
        $this->assign('remoteCategories', $remoteCategories ?: []);
        $this->assign('csrfToken', $this->csrfToken());
        
        // 传递绑定模型类供视图使用
        $this->render('collect/run', '执行采集');
    }

    /**
     * 执行采集 (AJAX)
     */
    public function doCollect(): void
    {
        // CSRF验证
        if (!$this->verifyCsrf()) {
            $this->json(['code' => 1, 'msg' => '非法请求']);
            return;
        }

        // 设置错误处理，确保所有错误都能被捕获并返回JSON
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        
        // 捕获所有错误
        try {
            // 单次请求最多执行120秒
            set_time_limit(120);
            
            $id = (int)$this->post('id', 0);
            $page = (int)$this->post('page', 1);
            $typeId = (int)$this->post('type_id', 0) ?: null;
            $hours = $this->post('hours', '') ?: null;
            $mode = $this->post('mode', 'add'); // add=只采新 update=只更新 all=全部
            $downloadPic = (int)$this->post('download_pic', 0); // 是否下载图片

            $collect = $this->collectModel->find($id);
            if (!$collect) {
                $this->error('采集站不存在');
            }

            // 获取绑定关系（使用新模型）
            $bindModel = new XpkCollectBind();
            $binds = $bindModel->getBinds($id);

            // 检查是否有绑定
            if (empty($binds)) {
                $this->error('请先绑定分类后再采集');
            }

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

            // 获取视频列表
            $listResult = $this->collectModel->getVideoList($collect, $page, $typeId, $hours);
            if (!$listResult || empty($listResult['list'])) {
                $this->success('采集完成', ['done' => true, 'page' => $page, 'added' => 0, 'updated' => 0, 'skipped' => 0]);
                return;
            }

            // 获取视频ID
            $ids = array_column($listResult['list'], 'vod_id');
            
            // 获取详情
            $videos = $this->collectModel->getVideoDetail($collect, $ids);
            if (!$videos) {
                $this->error('获取视频详情失败');
            }

            $added = 0;
            $updated = 0;
            $skipped = 0;
            $db = XpkDatabase::getInstance();

        foreach ($videos as $video) {
            // 检查分类绑定
            $localTypeId = $binds[$video['type_id']] ?? 0;
            if (!$localTypeId) {
                $skipped++;
                continue;
            }

            // 过滤关键词
            if (!empty($collect['collect_filter'])) {
                $filters = explode(',', $collect['collect_filter']);
                $skip = false;
                foreach ($filters as $filter) {
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

            // 下载图片（如果开启）
            $picUrl = $video['vod_pic'];
            if ($downloadPic && !empty($picUrl) && filter_var($picUrl, FILTER_VALIDATE_URL)) {
                $localPic = $this->downloadImage($picUrl);
                if ($localPic) {
                    $picUrl = $localPic;
                }
            }

            // 验证并过滤播放器
            $filteredPlay = $this->filterPlaySources($video['vod_play_from'], $video['vod_play_url']);
            if (empty($filteredPlay['from'])) {
                // 没有有效的播放源，跳过此视频
                $skipped++;
                continue;
            }
            $video['vod_play_from'] = $filteredPlay['from'];
            $video['vod_play_url'] = $filteredPlay['url'];

            // 根据重复规则检查是否已存在
            $exists = $this->checkDuplicate($video, $localTypeId, $opts['dup_rule'], $db);

            if ($exists) {
                if ($mode === 'add') {
                    $skipped++;
                    continue;
                }
                
                // 检查是否锁定
                if (!empty($exists['vod_lock'])) {
                    $skipped++; // 跳过锁定的视频
                    continue;
                }
                
                // 更新已有视频
                $this->updateExistingVideo($exists, $video, $picUrl, $downloadPic, $opts, $db);
                $updated++;
            } else {
                if ($mode === 'update') {
                    $skipped++;
                    continue;
                }
                
                // 新增视频
                $this->insertNewVideo($video, $localTypeId, $picUrl, $opts, $db, $id);
                $added++;
            }
        }

        $done = $page >= $listResult['pagecount'];
        
        // 累计统计
        $totalAdded = (int)$this->post('total_added', 0) + $added;
        $totalUpdated = (int)$this->post('total_updated', 0) + $updated;
        $totalSkipped = (int)$this->post('total_skipped', 0) + $skipped;
        $logId = (int)$this->post('log_id', 0);
        
        // 第一页时创建日志
        if ($page == 1 && !$logId) {
            require_once MODEL_PATH . 'CollectLog.php';
            $logModel = new XpkCollectLog();
            $logId = $logModel->start($id, $collect['collect_name'], 'manual', $mode);
        }
        
        // 采集完成时更新日志
        if ($done && $logId) {
            require_once MODEL_PATH . 'CollectLog.php';
            $logModel = new XpkCollectLog();
            $logModel->finish($logId, $page, $totalAdded, $totalUpdated, $totalSkipped);
        }
        
        // 保存采集进度（用于断点续采）
        $this->collectModel->update($id, [
            'collect_progress' => json_encode([
                'page' => $done ? 1 : $page + 1,
                'pagecount' => $listResult['pagecount'],
                'done' => $done,
                'time' => time(),
                'type_id' => $typeId,
                'mode' => $mode,
                'hours' => $hours,
                'download_pic' => $downloadPic,
                'total_added' => $totalAdded,
                'total_updated' => $totalUpdated,
                'total_skipped' => $totalSkipped,
                'log_id' => $logId
            ])
        ]);
        
        $this->success('采集中...', [
            'done' => $done,
            'page' => $page,
            'pagecount' => $listResult['pagecount'],
            'added' => $added,
            'updated' => $updated,
            'skipped' => $skipped,
            'log_id' => $logId
        ]);
        } catch (Exception $e) {
            restore_error_handler();
            // 记录错误到日志
            $logDir = RUNTIME_PATH . 'logs/';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logFile = $logDir . date('Y-m-d') . '_collect.log';
            $logMsg = "[" . date('Y-m-d H:i:s') . "] 采集错误: " . $e->getMessage() . "\n";
            $logMsg .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
            $logMsg .= "Trace: " . $e->getTraceAsString() . "\n\n";
            @file_put_contents($logFile, $logMsg, FILE_APPEND);
            
            $this->error('采集出错: ' . $e->getMessage());
        } catch (Error $e) {
            restore_error_handler();
            // 记录错误到日志
            $logDir = RUNTIME_PATH . 'logs/';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logFile = $logDir . date('Y-m-d') . '_collect.log';
            $logMsg = "[" . date('Y-m-d H:i:s') . "] 采集致命错误: " . $e->getMessage() . "\n";
            $logMsg .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
            $logMsg .= "Trace: " . $e->getTraceAsString() . "\n\n";
            @file_put_contents($logFile, $logMsg, FILE_APPEND);
            
            $this->error('采集出错: ' . $e->getMessage());
        }
    }

    /**
     * 根据重复规则检查视频是否已存在
     */
    private function checkDuplicate(array $video, int $localTypeId, string $rule, XpkDatabase $db): ?array
    {
        $sql = "SELECT vod_id, vod_play_from, vod_play_url, vod_down_from, vod_down_url, vod_lock FROM " . DB_PREFIX . "vod WHERE vod_name = ?";
        $params = [$video['vod_name']];

        switch ($rule) {
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

        $sql .= " LIMIT 1";
        return $db->queryOne($sql, $params);
    }

    /**
     * 更新已有视频
     */
    private function updateExistingVideo(array $exists, array $video, string $picUrl, bool $downloadPic, array $opts, XpkDatabase $db): void
    {
        $updateData = [];
        $allowedFields = $opts['update_fields'];

        // 备注/集数
        if (in_array('remarks', $allowedFields)) {
            $updateData['vod_remarks'] = $video['vod_remarks'];
        }

        // 简介内容
        if (in_array('content', $allowedFields)) {
            $updateData['vod_content'] = $video['vod_content'];
        }

        // 播放地址
        if (in_array('play', $allowedFields)) {
            if ($opts['play_merge']) {
                // 合并播放地址
                $merged = $this->mergePlaySources(
                    $exists['vod_play_from'], $exists['vod_play_url'],
                    $video['vod_play_from'], $video['vod_play_url']
                );
                $updateData['vod_play_from'] = $merged['from'];
                $updateData['vod_play_url'] = $merged['url'];
            } else {
                // 覆盖播放地址
                $updateData['vod_play_from'] = $video['vod_play_from'];
                $updateData['vod_play_url'] = $video['vod_play_url'];
            }
        }

        // 下载地址
        if (in_array('down', $allowedFields) && !empty($video['vod_down_from'])) {
            if ($opts['play_merge']) {
                // 合并下载地址
                $merged = $this->mergePlaySources(
                    $exists['vod_down_from'] ?? '', $exists['vod_down_url'] ?? '',
                    $video['vod_down_from'], $video['vod_down_url']
                );
                $updateData['vod_down_from'] = $merged['from'];
                $updateData['vod_down_url'] = $merged['url'];
            } else {
                $updateData['vod_down_from'] = $video['vod_down_from'];
                $updateData['vod_down_url'] = $video['vod_down_url'];
            }
        }

        // 封面图片
        if (in_array('pic', $allowedFields) && $downloadPic && $picUrl !== $video['vod_pic']) {
            $updateData['vod_pic'] = $picUrl;
        }

        // 演员
        if (in_array('actor', $allowedFields) && !empty($video['vod_actor'])) {
            $updateData['vod_actor'] = $video['vod_actor'];
        }

        // 导演
        if (in_array('director', $allowedFields) && !empty($video['vod_director'])) {
            $updateData['vod_director'] = $video['vod_director'];
        }

        // 评分
        if (in_array('score', $allowedFields) && $video['vod_score'] > 0) {
            $updateData['vod_score'] = $video['vod_score'];
        }

        // 扩展字段
        if (in_array('extend', $allowedFields)) {
            if (!empty($video['vod_tag'])) {
                $updateData['vod_tag'] = $video['vod_tag'];
            }
            if (!empty($video['vod_class'])) {
                $updateData['vod_class'] = $video['vod_class'];
            }
            if (isset($video['vod_isend'])) {
                $updateData['vod_isend'] = $video['vod_isend'];
            }
            if (!empty($video['vod_serial'])) {
                $updateData['vod_serial'] = $video['vod_serial'];
            }
            if (!empty($video['vod_total'])) {
                $updateData['vod_total'] = $video['vod_total'];
            }
            if (!empty($video['vod_weekday'])) {
                $updateData['vod_weekday'] = $video['vod_weekday'];
            }
            if (!empty($video['vod_state'])) {
                $updateData['vod_state'] = $video['vod_state'];
            }
            if (!empty($video['vod_version'])) {
                $updateData['vod_version'] = $video['vod_version'];
            }
            if (!empty($video['vod_letter'])) {
                $updateData['vod_letter'] = $video['vod_letter'];
            }
        }

        if (empty($updateData)) return;

        $updateData['vod_time'] = time();

        $setParts = [];
        $params = [];
        foreach ($updateData as $field => $value) {
            $setParts[] = "{$field} = ?";
            $params[] = $value;
        }
        $params[] = $exists['vod_id'];

        $sql = "UPDATE " . DB_PREFIX . "vod SET " . implode(', ', $setParts) . " WHERE vod_id = ?";
        $db->execute($sql, $params);
    }

    /**
     * 新增视频
     */
    private function insertNewVideo(array $video, int $localTypeId, string $picUrl, array $opts, XpkDatabase $db, int $collectId = 0): void
    {
        // 生成随机点击量
        $hits = 0;
        if ($opts['hits_start'] > 0 && $opts['hits_end'] > 0) {
            $hits = rand(min($opts['hits_start'], $opts['hits_end']), max($opts['hits_start'], $opts['hits_end']));
        }

        // 生成随机评分或使用资源站评分
        $score = $video['vod_score'];
        if ($opts['score_start'] > 0 && $opts['score_end'] > 0) {
            $min = min($opts['score_start'], $opts['score_end']);
            $max = max($opts['score_start'], $opts['score_end']);
            $score = round($min + mt_rand() / mt_getrandmax() * ($max - $min), 1);
        }

        // 获取一级分类ID
        $topLevelId = $this->typeModel->getTopLevelId($localTypeId);

        // 自动生成 slug
        require_once CORE_PATH . 'Slug.php';
        $vodSlug = xpk_slug_unique($video['vod_name'], 'vod', 'vod_slug');

        $db->execute(
            "INSERT INTO " . DB_PREFIX . "vod 
                (vod_type_id, vod_type_id_1, vod_name, vod_sub, vod_en, vod_slug, vod_pic, vod_actor, vod_director, 
                 vod_year, vod_area, vod_lang, vod_letter, vod_tag, vod_class, vod_isend, vod_serial, vod_total, 
                 vod_weekday, vod_state, vod_version, vod_score, vod_hits, vod_remarks, vod_content, 
                 vod_play_from, vod_play_url, vod_down_from, vod_down_url, vod_status, vod_collect_id, vod_time, vod_time_add)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?)",
            [
                $localTypeId,
                $topLevelId,
                $video['vod_name'],
                $video['vod_sub'],
                $video['vod_en'],
                $vodSlug,
                $picUrl,
                $video['vod_actor'],
                $video['vod_director'],
                $video['vod_year'],
                $video['vod_area'],
                $video['vod_lang'],
                $video['vod_letter'] ?? '',
                $video['vod_tag'] ?? '',
                $video['vod_class'] ?? '',
                $video['vod_isend'] ?? 0,
                $video['vod_serial'] ?? '',
                $video['vod_total'] ?? 0,
                $video['vod_weekday'] ?? '',
                $video['vod_state'] ?? '',
                $video['vod_version'] ?? '',
                $score,
                $hits,
                $video['vod_remarks'],
                $video['vod_content'],
                $video['vod_play_from'],
                $video['vod_play_url'],
                $video['vod_down_from'] ?? '',
                $video['vod_down_url'] ?? '',
                $collectId,
                time(),
                time()
            ]
        );
    }

    /**
     * 合并播放源
     */
    private function mergePlaySources(string $oldFrom, string $oldUrl, string $newFrom, string $newUrl): array
    {
        $oldFromArr = array_filter(explode('$$$', $oldFrom));
        $oldUrlArr = array_filter(explode('$$$', $oldUrl));
        $newFromArr = array_filter(explode('$$$', $newFrom));
        $newUrlArr = array_filter(explode('$$$', $newUrl));

        // 构建旧数据的映射
        $merged = [];
        foreach ($oldFromArr as $i => $from) {
            $merged[$from] = $oldUrlArr[$i] ?? '';
        }

        // 合并新数据（同名播放源用新的覆盖）
        foreach ($newFromArr as $i => $from) {
            $merged[$from] = $newUrlArr[$i] ?? '';
        }

        return [
            'from' => implode('$$$', array_keys($merged)),
            'url' => implode('$$$', array_values($merged))
        ];
    }

    /**
     * 下载远程图片到存储
     */
    private function downloadImage(string $url): ?string
    {
        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ]);
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($httpCode !== 200 || empty($content)) {
                return null;
            }

            // 检查是否是图片
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $mimeType = explode(';', $contentType)[0];
            if (!in_array($mimeType, $allowedTypes)) {
                return null;
            }

            // 确定扩展名
            $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            $ext = $extMap[$mimeType] ?? 'jpg';

            // 生成路径：covers/年/月/日/hash.ext
            $remotePath = 'covers/' . date('Y/m/d/') . md5($url) . '.' . $ext;

            // 使用存储类上传
            require_once CORE_PATH . 'Storage.php';
            $storage = xpk_storage();
            return $storage->uploadContent($content, $remotePath);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * 测试API
     */
    public function test(): void
    {
        // CSRF 验证
        $this->requireCsrf();
        
        $api = trim($this->post('api', ''));
        if (empty($api)) {
            $this->error('API地址不能为空');
        }

        $collect = ['collect_api' => $api];
        $categories = $this->collectModel->getCategories($collect);

        if ($categories === null) {
            $this->error('连接失败，请检查API地址');
        }

        $this->success('连接成功', ['categories' => $categories]);
    }

    /**
     * 清除采集进度
     */
    public function clearProgress(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $id = (int)$this->post('id', 0);
        
        if ($id <= 0) {
            $this->error('参数错误');
        }

        $this->collectModel->update($id, [
            'collect_progress' => json_encode(['done' => true, 'page' => 1])
        ]);

        $this->success('进度已清除');
    }

    /**
     * 定时采集配置页面
     */
    public function cron(): void
    {
        $db = XpkDatabase::getInstance();
        
        // 获取配置
        $configRow = $db->queryOne("SELECT config_value FROM " . DB_PREFIX . "config WHERE config_name = 'cron_collect'");
        $config = $configRow ? json_decode($configRow['config_value'], true) : [];
        
        // 获取上次执行时间
        $lastRunRow = $db->queryOne("SELECT config_value FROM " . DB_PREFIX . "config WHERE config_name = 'cron_last_run'");
        $lastRun = $lastRunRow ? (int)$lastRunRow['config_value'] : 0;
        
        // 获取所有采集站
        $collects = $this->collectModel->getAll();
        
        $this->assign('config', $config);
        $this->assign('lastRun', $lastRun);
        $this->assign('collects', $collects);
        $this->assign('csrfToken', $this->csrfToken());
        $this->assign('flash', $this->getFlash());
        $this->render('collect/cron', '定时采集');
    }

    /**
     * 保存定时采集配置
     */
    public function saveCron(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $config = [
            'enabled' => (int)$this->post('enabled', 0),
            'interval' => (int)$this->post('interval', 60),
            'mode' => $this->post('mode', 'add'),
            'hours' => $this->post('hours', ''),
            'download_pic' => (int)$this->post('download_pic', 0),
            'collect_ids' => $this->post('collect_ids', [])
        ];

        $db = XpkDatabase::getInstance();
        $exists = $db->queryOne("SELECT config_id FROM " . DB_PREFIX . "config WHERE config_name = 'cron_collect'");
        
        if ($exists) {
            $db->execute(
                "UPDATE " . DB_PREFIX . "config SET config_value = ? WHERE config_name = 'cron_collect'",
                [json_encode($config)]
            );
        } else {
            $db->execute(
                "INSERT INTO " . DB_PREFIX . "config (config_name, config_value) VALUES ('cron_collect', ?)",
                [json_encode($config)]
            );
        }

        $this->log('修改', '配置', '定时采集配置');
        $this->success('保存成功');
    }

    /**
     * 手动执行定时采集
     */
    public function runCron(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        // 在后台执行采集脚本
        $phpBin = PHP_BINARY ?: 'php';
        $script = ROOT_PATH . 'cron.php';
        $logFile = ROOT_PATH . 'runtime/cron.log';
        
        // 确保runtime目录存在
        if (!is_dir(ROOT_PATH . 'runtime')) {
            mkdir(ROOT_PATH . 'runtime', 0755, true);
        }
        
        // Windows和Linux使用不同的后台执行方式
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen("start /B {$phpBin} {$script} auto >> {$logFile} 2>&1", 'r'));
        } else {
            exec("{$phpBin} {$script} auto >> {$logFile} 2>&1 &");
        }

        $this->log('执行', '采集', '手动触发定时采集');
        $this->success('采集任务已在后台启动，请查看日志了解进度');
    }

    /**
     * 采集日志页面
     */
    public function logList(): void
    {
        require_once MODEL_PATH . 'CollectLog.php';
        $logModel = new XpkCollectLog();
        
        $page = (int)($this->get('page', 1));
        $collectId = (int)($this->get('collect_id', 0)) ?: null;
        
        $logs = $logModel->getList($page, 20, $collectId);
        $stats = $logModel->getStats($collectId);
        $collects = $this->collectModel->getAll();
        
        $this->assign('logs', $logs);
        $this->assign('stats', $stats);
        $this->assign('collects', $collects);
        $this->assign('collectId', $collectId);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('collect/log', '采集日志');
    }

    /**
     * 清理采集日志
     */
    public function cleanLog(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        require_once MODEL_PATH . 'CollectLog.php';
        $logModel = new XpkCollectLog();
        $count = $logModel->clean(30);
        
        $this->log('清理', '采集日志', "清理了{$count}条记录");
        $this->success("已清理 {$count} 条日志");
    }

    /**
     * 过滤播放源，只保留系统中已配置的播放器
     * 
     * @param string $playFrom 播放源标识，多个用$$$分隔
     * @param string $playUrl 播放地址，多个用$$$分隔
     * @return array ['from' => string, 'url' => string]
     */
    private function filterPlaySources(string $playFrom, string $playUrl): array
    {
        if (empty($playFrom) || empty($playUrl)) {
            return ['from' => '', 'url' => ''];
        }

        // 获取系统中启用的播放器标识
        $enabledCodes = $this->playerModel->getEnabledCodes();
        
        // 如果没有配置任何播放器，则不过滤（兼容未配置播放器的情况）
        if (empty($enabledCodes)) {
            return ['from' => $playFrom, 'url' => $playUrl];
        }
        
        // Auto-create missing players from collected sources
        $fromArr = explode('$$$', $playFrom);
        $db = XpkDatabase::getInstance();
        $maxSort = $db->queryOne("SELECT MAX(player_sort) as max_sort FROM " . DB_PREFIX . "player")['max_sort'] ?? 100;
        
        foreach ($fromArr as $from) {
            $from = trim($from);
            if (empty($from)) continue;
            
            if (!in_array($from, $enabledCodes)) {
                // Auto-create player with this code
                $maxSort++;
                $playerName = $from;
                // Generate readable name
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

        $fromArr = explode('$$$', $playFrom);
        $urlArr = explode('$$$', $playUrl);
        
        $filteredFrom = [];
        $filteredUrl = [];
        
        foreach ($fromArr as $index => $from) {
            $from = trim($from);
            if (empty($from)) continue;
            
            // 检查播放器是否在启用列表中
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
}
