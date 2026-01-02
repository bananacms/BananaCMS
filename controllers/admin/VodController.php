<?php
/**
 * 后台视频管理控制器
 * Powered by https://xpornkit.com
 */

class AdminVodController extends AdminBaseController
{
    private XpkVod $vodModel;
    private XpkType $typeModel;

    public function __construct()
    {
        parent::__construct();
        $this->vodModel = new XpkVod();
        $this->typeModel = new XpkType();
    }

    /**
     * 视频列表
     */
    public function index(): void
    {
        $page = (int)($this->get('page', 1));
        $typeId = (int)($this->get('type', 0));
        $status = $this->get('status', '');
        $keyword = trim($this->get('keyword', ''));
        $collectId = $this->get('collect_id', '');

        $db = XpkDatabase::getInstance();
        $where = [];
        $params = [];

        if ($typeId > 0) {
            $where[] = 'v.vod_type_id = ?';
            $params[] = $typeId;
        }

        if ($status !== '') {
            $where[] = 'v.vod_status = ?';
            $params[] = (int)$status;
        }

        if ($collectId !== '') {
            $where[] = 'v.vod_collect_id = ?';
            $params[] = (int)$collectId;
        }

        if ($keyword) {
            $where[] = '(v.vod_name LIKE ? OR v.vod_actor LIKE ?)';
            $kw = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $keyword) . '%';
            $params[] = $kw;
            $params[] = $kw;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $pageSize = 20;
        $offset = ($page - 1) * $pageSize;

        $list = $db->query(
            "SELECT v.*, t.type_name FROM " . DB_PREFIX . "vod v 
             LEFT JOIN " . DB_PREFIX . "type t ON v.vod_type_id = t.type_id 
             {$whereStr} ORDER BY v.vod_id DESC LIMIT {$pageSize} OFFSET {$offset}",
            $params
        );

        $total = $db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "vod v {$whereStr}",
            $params
        )['cnt'] ?? 0;

        $types = $this->typeModel->getAll();
        
        // 获取采集站列表
        require_once MODEL_PATH . 'Collect.php';
        $collectModel = new XpkCollect();
        $collects = $collectModel->getAll();

        $this->assign('list', $list);
        $this->assign('total', $total);
        $this->assign('page', $page);
        $this->assign('pageSize', $pageSize);
        $this->assign('totalPages', ceil($total / $pageSize));
        $this->assign('types', $types);
        $this->assign('collects', $collects);
        $this->assign('typeId', $typeId);
        $this->assign('status', $status);
        $this->assign('collectId', $collectId);
        $this->assign('keyword', $keyword);
        $this->assign('flash', $this->getFlash());

        $this->render('vod/index', '视频管理');
    }

    /**
     * 添加视频
     */
    public function add(): void
    {
        $types = $this->typeModel->getAll();
        $this->assign('types', $types);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('vod/form', '添加视频');
    }

    /**
     * 处理添加
     */
    public function doAdd(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = $this->getFormData();
        $data['vod_time'] = time();
        $data['vod_time_add'] = time();
        
        // 自动生成 slug
        if (empty($data['vod_slug']) && !empty($data['vod_name'])) {
            require_once CORE_PATH . 'Slug.php';
            $data['vod_slug'] = xpk_slug_unique($data['vod_name'], 'vod', 'vod_slug');
        }

        $id = $this->vodModel->insert($data);

        if ($id) {
            $this->log('添加', '视频', "ID:{$id} {$data['vod_name']}");
            $this->flash('success', '添加成功');
            $this->redirect('/' . $this->adminEntry . '/vod');
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 编辑视频
     */
    public function edit(int $id): void
    {
        $vod = $this->vodModel->find($id);
        if (!$vod) {
            $this->flash('error', '视频不存在');
            $this->redirect('/' . $this->adminEntry . '/vod');
        }

        $types = $this->typeModel->getAll();

        $this->assign('vod', $vod);
        $this->assign('types', $types);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('vod/form', '编辑视频');
    }

    /**
     * 处理编辑
     */
    public function doEdit(int $id): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $vod = $this->vodModel->find($id);
        if (!$vod) {
            $this->error('视频不存在');
        }

        $data = $this->getFormData();
        $data['vod_time'] = time();
        
        // 自动生成 slug（如果为空或名称变更）
        if (empty($data['vod_slug']) || ($data['vod_name'] !== $vod['vod_name'] && empty(trim($this->post('vod_slug', ''))))) {
            require_once CORE_PATH . 'Slug.php';
            $data['vod_slug'] = xpk_slug_unique($data['vod_name'], 'vod', 'vod_slug', $id);
        }

        $this->vodModel->update($id, $data);
        $this->log('编辑', '视频', "ID:{$id} {$data['vod_name']}");

        $this->flash('success', '保存成功');
        $this->redirect('/' . $this->adminEntry . '/vod');
    }

    /**
     * 删除视频
     */
    public function delete(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        // 兼容单个删除(id)和批量删除(ids[])
        $ids = $_POST['ids'] ?? [];
        if (empty($ids) && isset($_POST['id'])) {
            $ids = [(int)$_POST['id']];
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        // 过滤空值并转为整数
        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);
        if (empty($ids)) {
            $this->error('请选择要删除的视频');
        }

        $db = XpkDatabase::getInstance();
        
        // 删除前先查询记录详细信息，用于日志记录
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $vodList = $db->query(
            "SELECT vod_id, vod_name, vod_type_id FROM " . DB_PREFIX . "vod WHERE vod_id IN ({$placeholders})",
            $ids
        );
        
        if (empty($vodList)) {
            $this->error('要删除的视频不存在');
        }
        
        // 执行删除
        $affected = $db->execute(
            "DELETE FROM " . DB_PREFIX . "vod WHERE vod_id IN ({$placeholders})",
            $ids
        );

        // 记录详细日志
        $logDetails = [];
        foreach ($vodList as $vod) {
            $logDetails[] = "ID:{$vod['vod_id']} 《{$vod['vod_name']}》";
        }
        $logContent = implode(', ', $logDetails);
        
        $this->log('删除', '视频', "删除了 {$affected} 个视频: {$logContent}");
        $this->success("删除成功，共删除 {$affected} 个视频");
    }

    /**
     * 修改状态
     */
    public function status(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $id = (int)$this->post('id', 0);
        $status = (int)$this->post('status', 0);

        if ($id > 0) {
            $this->vodModel->update($id, ['vod_status' => $status]);
            $this->success('操作成功');
        } else {
            $this->error('参数错误');
        }
    }

    /**
     * 切换锁定状态
     */
    public function lock(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $id = (int)$this->post('id', 0);
        
        if ($id <= 0) {
            $this->error('参数错误');
        }

        $vod = $this->vodModel->find($id);
        if (!$vod) {
            $this->error('视频不存在');
        }

        $newLock = $vod['vod_lock'] ? 0 : 1;
        $this->vodModel->update($id, ['vod_lock' => $newLock]);
        
        $this->log($newLock ? '锁定' : '解锁', '视频', "ID:{$id} {$vod['vod_name']}");
        $this->success($newLock ? '已锁定，采集时将跳过此视频' : '已解锁');
    }

    /**
     * 批量锁定/解锁
     */
    public function batchLock(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $ids = $_POST['ids'] ?? [];
        $lock = (int)$this->post('lock', 1);
        
        if (empty($ids) || !is_array($ids)) {
            $this->error('请选择视频');
        }

        $db = XpkDatabase::getInstance();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $db->execute(
            "UPDATE " . DB_PREFIX . "vod SET vod_lock = ? WHERE vod_id IN ({$placeholders})",
            array_merge([$lock], array_map('intval', $ids))
        );

        $this->log($lock ? '批量锁定' : '批量解锁', '视频', "IDs:" . implode(',', $ids));
        $this->success($lock ? '已锁定' : '已解锁');
    }

    /**
     * 播放地址替换页面
     */
    public function replace(): void
    {
        // 获取所有播放源
        $db = XpkDatabase::getInstance();
        $sources = $db->query(
            "SELECT DISTINCT vod_play_from FROM " . DB_PREFIX . "vod WHERE vod_play_from != '' ORDER BY vod_play_from"
        );
        
        // 解析播放源列表
        $playFromList = [];
        foreach ($sources as $row) {
            $froms = explode('$$$', $row['vod_play_from']);
            foreach ($froms as $from) {
                $from = trim($from);
                if (!empty($from) && !in_array($from, $playFromList)) {
                    $playFromList[] = $from;
                }
            }
        }
        sort($playFromList);

        $this->assign('playFromList', $playFromList);
        $this->assign('csrfToken', $this->csrfToken());
        $this->assign('flash', $this->getFlash());
        $this->render('vod/replace', '播放地址替换');
    }

    /**
     * 执行播放地址替换
     */
    public function doReplace(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $type = trim($this->post('type', 'domain')); // domain=域名替换, source=播放源替换, custom=自定义替换
        $oldStr = trim($this->post('old_str', ''));
        $newStr = trim($this->post('new_str', ''));
        $playFrom = trim($this->post('play_from', '')); // 指定播放源，为空则全部
        $field = trim($this->post('field', 'play')); // play=播放地址, down=下载地址, both=两者

        if (empty($oldStr)) {
            $this->error('请输入要替换的内容');
        }

        $db = XpkDatabase::getInstance();
        $affected = 0;

        // 构建查询条件
        $where = '';
        $params = [];
        
        if (!empty($playFrom)) {
            $where = " WHERE vod_play_from LIKE ?";
            $params[] = '%' . $playFrom . '%';
        }

        // 根据替换类型执行
        if ($field === 'play' || $field === 'both') {
            $sql = "UPDATE " . DB_PREFIX . "vod SET vod_play_url = REPLACE(vod_play_url, ?, ?)" . $where;
            $affected += $db->execute($sql, array_merge([$oldStr, $newStr], $params));
        }

        if ($field === 'down' || $field === 'both') {
            $sql = "UPDATE " . DB_PREFIX . "vod SET vod_down_url = REPLACE(vod_down_url, ?, ?)" . $where;
            $affected += $db->execute($sql, array_merge([$oldStr, $newStr], $params));
        }

        $this->log('批量替换', '播放地址', "替换 '{$oldStr}' 为 '{$newStr}'，影响 {$affected} 条");
        $this->success("替换完成，共影响 {$affected} 条记录");
    }

    /**
     * 播放源管理页面
     */
    public function sources(): void
    {
        $db = XpkDatabase::getInstance();
        
        // 统计各播放源的视频数量
        $sources = $db->query(
            "SELECT vod_play_from, COUNT(*) as count FROM " . DB_PREFIX . "vod WHERE vod_play_from != '' GROUP BY vod_play_from"
        );
        
        // 解析并统计
        $sourceStats = [];
        foreach ($sources as $row) {
            $froms = explode('$$$', $row['vod_play_from']);
            foreach ($froms as $from) {
                $from = trim($from);
                if (empty($from)) continue;
                
                if (!isset($sourceStats[$from])) {
                    $sourceStats[$from] = 0;
                }
                $sourceStats[$from] += $row['count'];
            }
        }
        arsort($sourceStats);

        $this->assign('sourceStats', $sourceStats);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('vod/sources', '播放源管理');
    }

    /**
     * 删除指定播放源
     */
    public function deleteSource(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $sourceName = trim($this->post('source', ''));
        if (empty($sourceName)) {
            $this->error('请指定播放源');
        }

        $db = XpkDatabase::getInstance();
        
        // 获取所有包含该播放源的视频
        $vods = $db->query(
            "SELECT vod_id, vod_play_from, vod_play_url, vod_down_from, vod_down_url FROM " . DB_PREFIX . "vod WHERE vod_play_from LIKE ?",
            ['%' . $sourceName . '%']
        );

        $affected = 0;
        foreach ($vods as $vod) {
            $playFromArr = array_filter(explode('$$$', $vod['vod_play_from']));
            $playUrlArr = array_filter(explode('$$$', $vod['vod_play_url']));
            
            // 找到要删除的播放源索引
            $newFromArr = [];
            $newUrlArr = [];
            foreach ($playFromArr as $i => $from) {
                if (trim($from) !== $sourceName) {
                    $newFromArr[] = $from;
                    $newUrlArr[] = $playUrlArr[$i] ?? '';
                }
            }

            // 更新数据库
            $db->execute(
                "UPDATE " . DB_PREFIX . "vod SET vod_play_from = ?, vod_play_url = ? WHERE vod_id = ?",
                [implode('$$$', $newFromArr), implode('$$$', $newUrlArr), $vod['vod_id']]
            );
            $affected++;
        }

        $this->log('删除播放源', '视频', "删除播放源 '{$sourceName}'，影响 {$affected} 条");
        $this->success("删除完成，共处理 {$affected} 条视频");
    }

    /**
     * 重命名播放源
     */
    public function renameSource(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $oldName = trim($this->post('old_name', ''));
        $newName = trim($this->post('new_name', ''));
        
        if (empty($oldName) || empty($newName)) {
            $this->error('请输入播放源名称');
        }

        if ($oldName === $newName) {
            $this->error('新旧名称相同');
        }

        $db = XpkDatabase::getInstance();
        
        // 直接替换播放源名称
        $affected = $db->execute(
            "UPDATE " . DB_PREFIX . "vod SET vod_play_from = REPLACE(vod_play_from, ?, ?) WHERE vod_play_from LIKE ?",
            [$oldName, $newName, '%' . $oldName . '%']
        );

        // 同时替换下载源名称
        $db->execute(
            "UPDATE " . DB_PREFIX . "vod SET vod_down_from = REPLACE(vod_down_from, ?, ?) WHERE vod_down_from LIKE ?",
            [$oldName, $newName, '%' . $oldName . '%']
        );

        $this->log('重命名播放源', '视频', "'{$oldName}' -> '{$newName}'，影响 {$affected} 条");
        $this->success("重命名完成，共影响 {$affected} 条记录");
    }

    /**
     * 获取表单数据
     */
    private function getFormData(): array
    {
        $typeId = (int)$this->post('vod_type_id', 0);
        $topLevelId = $typeId > 0 ? $this->typeModel->getTopLevelId($typeId) : 0;
        
        // 自动生成首字母
        $vodName = trim($this->post('vod_name', ''));
        $firstLetter = '';
        if (!empty($vodName)) {
            require_once CORE_PATH . 'Pinyin.php';
            $pinyin = new XpkPinyin();
            $firstLetter = $pinyin->getFirstLetter(mb_substr($vodName, 0, 1, 'UTF-8'));
        }
        
        return [
            'vod_type_id' => $typeId,
            'vod_type_id_1' => $topLevelId,
            'vod_name' => $vodName,
            'vod_sub' => trim($this->post('vod_sub', '')),
            'vod_en' => trim($this->post('vod_en', '')),
            'vod_slug' => trim($this->post('vod_slug', '')),
            'vod_pic' => trim($this->post('vod_pic', '')),
            'vod_actor' => trim($this->post('vod_actor', '')),
            'vod_director' => trim($this->post('vod_director', '')),
            'vod_year' => trim($this->post('vod_year', '')),
            'vod_area' => trim($this->post('vod_area', '')),
            'vod_lang' => trim($this->post('vod_lang', '')),
            'vod_letter' => strtoupper($firstLetter),
            'vod_tag' => trim($this->post('vod_tag', '')),
            'vod_class' => trim($this->post('vod_class', '')),
            'vod_isend' => (int)$this->post('vod_isend', 0),
            'vod_serial' => trim($this->post('vod_serial', '')),
            'vod_total' => (int)$this->post('vod_total', 0),
            'vod_weekday' => trim($this->post('vod_weekday', '')),
            'vod_state' => trim($this->post('vod_state', '')),
            'vod_version' => trim($this->post('vod_version', '')),
            'vod_score' => (float)$this->post('vod_score', 0),
            'vod_remarks' => trim($this->post('vod_remarks', '')),
            'vod_content' => trim($this->post('vod_content', '')),
            'vod_play_from' => trim($this->post('vod_play_from', '')),
            'vod_play_url' => trim($this->post('vod_play_url', '')),
            'vod_down_from' => trim($this->post('vod_down_from', '')),
            'vod_down_url' => trim($this->post('vod_down_url', '')),
            'vod_status' => (int)$this->post('vod_status', 1),
            'vod_lock' => (int)$this->post('vod_lock', 0),
        ];
    }
}
