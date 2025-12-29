<?php
/**
 * 后台广告管理控制器
 * Powered by https://xpornkit.com
 */

class AdminAdController extends AdminBaseController
{
    private XpkAd $adModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Ad.php';
        $this->adModel = new XpkAd();
    }

    /**
     * 广告列表
     */
    public function index(): void
    {
        $page = (int)($this->get('page', 1));
        $position = $this->get('position', '');
        
        $result = $this->adModel->getList($position, $page, 20);
        $stats = $this->adModel->getStats();
        
        $this->assign('list', $result['list']);
        $this->assign('total', $result['total']);
        $this->assign('page', $page);
        $this->assign('pageSize', 20);
        $this->assign('totalPages', ceil($result['total'] / 20));
        $this->assign('position', $position);
        $this->assign('positions', XpkAd::getPositions());
        $this->assign('types', XpkAd::getTypes());
        $this->assign('stats', $stats);
        $this->assign('csrfToken', $this->csrfToken());
        $this->assign('flash', $this->getFlash());

        $this->render('ad/index', '广告管理');
    }

    /**
     * 获取单个广告（AJAX）
     */
    public function getOne(): void
    {
        $id = (int)$this->input('id', 0);
        $ad = $this->adModel->find($id);
        
        if (!$ad) {
            $this->error('广告不存在');
        }
        
        $this->success('ok', $ad);
    }

    /**
     * 添加广告（AJAX）
     */
    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->assign('positions', XpkAd::getPositions());
            $this->assign('types', XpkAd::getTypes());
            $this->assign('csrfToken', $this->csrfToken());
            $this->render('ad/form', '添加广告');
            return;
        }

        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = $this->getFormData();

        if (empty($data['ad_title'])) {
            $this->error('请填写广告名称');
        }

        $id = $this->adModel->insert($data);

        if ($id) {
            $this->log('添加', '广告', "ID:{$id} {$data['ad_title']}");
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 编辑广告（AJAX）
     */
    public function edit(int $id): void
    {
        $ad = $this->adModel->find($id);
        if (!$ad) {
            $this->error('广告不存在');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->assign('ad', $ad);
            $this->assign('positions', XpkAd::getPositions());
            $this->assign('types', XpkAd::getTypes());
            $this->assign('csrfToken', $this->csrfToken());
            $this->render('ad/form', '编辑广告');
            return;
        }

        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = $this->getFormData();

        if (empty($data['ad_title'])) {
            $this->error('请填写广告名称');
        }

        $this->adModel->update($id, $data);
        $this->log('编辑', '广告', "ID:{$id} {$data['ad_title']}");

        $this->success('保存成功');
    }

    /**
     * 删除广告
     */
    public function delete(): void
    {
        $id = (int)$this->post('id', 0);

        if ($id <= 0) {
            $this->error('参数错误');
        }

        $ad = $this->adModel->find($id);
        $this->adModel->delete($id);
        $this->log('删除', '广告', "ID:{$id} " . ($ad['ad_title'] ?? ''));
        $this->success('删除成功');
    }

    /**
     * 切换状态
     */
    public function toggle(): void
    {
        $id = (int)$this->post('id', 0);

        if ($id <= 0) {
            $this->error('参数错误');
        }

        $ad = $this->adModel->find($id);
        if (!$ad) {
            $this->error('广告不存在');
        }

        $newStatus = $ad['ad_status'] ? 0 : 1;
        $this->adModel->update($id, ['ad_status' => $newStatus]);
        
        $statusText = $newStatus ? '启用' : '禁用';
        $this->log('修改状态', '广告', "ID:{$id} {$statusText}");
        $this->success($statusText . '成功');
    }

    /**
     * 记录点击（AJAX）
     */
    public function click(): void
    {
        $id = (int)$this->post('id', 0);
        if ($id > 0) {
            $this->adModel->incrementClick($id);
        }
        $this->success('ok');
    }

    /**
     * 获取表单数据
     */
    private function getFormData(): array
    {
        $startTime = $this->post('ad_start_time', '');
        $endTime = $this->post('ad_end_time', '');
        
        return [
            'ad_title' => trim($this->post('ad_title', '')),
            'ad_position' => trim($this->post('ad_position', '')),
            'ad_type' => trim($this->post('ad_type', 'image')),
            'ad_image' => trim($this->post('ad_image', '')),
            'ad_link' => trim($this->post('ad_link', '')),
            'ad_code' => $this->post('ad_code', ''),
            'ad_video' => trim($this->post('ad_video', '')),
            'ad_duration' => (int)$this->post('ad_duration', 0),
            'ad_skip_time' => (int)$this->post('ad_skip_time', 5),
            'ad_sort' => (int)$this->post('ad_sort', 0),
            'ad_status' => (int)$this->post('ad_status', 1),
            'ad_start_time' => $startTime ? strtotime($startTime) : 0,
            'ad_end_time' => $endTime ? strtotime($endTime) : 0,
            'ad_remark' => trim($this->post('ad_remark', '')),
        ];
    }
}
