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

        $this->assign('list', $list);
        $this->assign('total', $total);
        $this->assign('page', $page);
        $this->assign('pageSize', $pageSize);
        $this->assign('totalPages', ceil($total / $pageSize));
        $this->assign('types', $types);
        $this->assign('typeId', $typeId);
        $this->assign('status', $status);
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
            $this->redirect('/admin.php/vod');
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
            $this->redirect('/admin.php/vod');
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
        $this->redirect('/admin.php/vod');
    }

    /**
     * 删除视频
     */
    public function delete(): void
    {
        $ids = $_POST['ids'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            $this->error('请选择要删除的视频');
        }

        $db = XpkDatabase::getInstance();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $db->execute(
            "DELETE FROM " . DB_PREFIX . "vod WHERE vod_id IN ({$placeholders})",
            array_map('intval', $ids)
        );

        $this->log('删除', '视频', "IDs:" . implode(',', $ids));
        $this->success('删除成功');
    }

    /**
     * 修改状态
     */
    public function status(): void
    {
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
