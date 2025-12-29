<?php
/**
 * 后台友链管理控制器
 * Powered by https://xpornkit.com
 */

class AdminLinkController extends AdminBaseController
{
    private XpkLink $linkModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Link.php';
        $this->linkModel = new XpkLink();
    }

    /**
     * 友链列表
     */
    public function index(): void
    {
        $page = (int)($this->get('page', 1));
        $status = $this->get('status', '');
        
        $statusFilter = $status !== '' ? (int)$status : -1;
        $result = $this->linkModel->getList($statusFilter, $page, 20);
        
        $stats = $this->linkModel->getStats();
        
        // 获取友链设置
        $config = xpk_cache()->get('site_config') ?: [];
        
        $this->assign('list', $result['list']);
        $this->assign('total', $result['total']);
        $this->assign('page', $page);
        $this->assign('pageSize', 20);
        $this->assign('totalPages', ceil($result['total'] / 20));
        $this->assign('status', $status);
        $this->assign('stats', $stats);
        $this->assign('linkAutoApprove', $config['link_auto_approve'] ?? '0');
        $this->assign('flash', $this->getFlash());

        $this->render('link/index', '友链管理');
    }

    /**
     * 添加友链
     */
    public function add(): void
    {
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('link/form', '添加友链');
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
        $data['link_status'] = 1; // 后台添加直接通过
        $data['link_type'] = 1;   // 手动添加

        $id = $this->linkModel->insert($data);

        if ($id) {
            $this->log('添加', '友链', "ID:{$id} {$data['link_name']}");
            $this->flash('success', '添加成功');
            $this->redirect('/admin.php/link');
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 编辑友链
     */
    public function edit(int $id): void
    {
        $link = $this->linkModel->find($id);
        if (!$link) {
            $this->flash('error', '友链不存在');
            $this->redirect('/admin.php/link');
        }

        $this->assign('link', $link);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('link/form', '编辑友链');
    }

    /**
     * 处理编辑
     */
    public function doEdit(int $id): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $link = $this->linkModel->find($id);
        if (!$link) {
            $this->error('友链不存在');
        }

        $data = $this->getFormData();
        $this->linkModel->update($id, $data);
        $this->log('编辑', '友链', "ID:{$id} {$data['link_name']}");

        $this->flash('success', '保存成功');
        $this->redirect('/admin.php/link');
    }

    /**
     * 删除友链
     */
    public function delete(): void
    {
        $id = (int)$this->post('id', 0);

        if ($id <= 0) {
            $this->error('参数错误');
        }

        $this->linkModel->delete($id);
        $this->log('删除', '友链', "ID:{$id}");
        $this->success('删除成功');
    }

    /**
     * 审核友链
     */
    public function audit(): void
    {
        $id = (int)$this->post('id', 0);
        $status = (int)$this->post('status', 0);

        if ($id <= 0) {
            $this->error('参数错误');
        }

        $this->linkModel->update($id, ['link_status' => $status]);
        $statusText = $status == 1 ? '通过' : '拒绝';
        $this->log('审核', '友链', "ID:{$id} {$statusText}");
        $this->success('操作成功');
    }

    /**
     * 检测回链
     */
    public function check(): void
    {
        $id = (int)$this->post('id', 0);

        if ($id > 0) {
            // 单个检测
            $link = $this->linkModel->find($id);
            if (!$link) {
                $this->error('友链不存在');
            }

            $hasBacklink = $this->linkModel->checkBacklink($link['link_url']);
            $this->linkModel->update($id, [
                'link_check_time' => time(),
                'link_check_status' => $hasBacklink ? 1 : 2
            ]);

            $this->success($hasBacklink ? '检测通过，对方已添加回链' : '检测失败，未发现回链');
        } else {
            // 批量检测
            $results = $this->linkModel->batchCheck();
            $this->success("检测完成：{$results['success']}个有回链，{$results['fail']}个无回链");
        }
    }

    /**
     * 保存设置
     */
    public function saveSetting(): void
    {
        $autoApprove = $this->post('link_auto_approve', '0');
        
        $config = xpk_cache()->get('site_config') ?: [];
        $config['link_auto_approve'] = $autoApprove;
        xpk_cache()->set('site_config', $config);
        
        $this->log('修改', '友链设置', '自动换链:' . ($autoApprove ? '开启' : '关闭'));
        $this->success('设置已保存');
    }

    /**
     * 获取表单数据
     */
    private function getFormData(): array
    {
        return [
            'link_name' => trim($this->post('link_name', '')),
            'link_url' => trim($this->post('link_url', '')),
            'link_logo' => trim($this->post('link_logo', '')),
            'link_contact' => trim($this->post('link_contact', '')),
            'link_sort' => (int)$this->post('link_sort', 0),
            'link_status' => (int)$this->post('link_status', 0),
        ];
    }
}
