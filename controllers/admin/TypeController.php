<?php
/**
 * 后台分类管理控制器
 * Powered by https://xpornkit.com
 */

class AdminTypeController extends AdminBaseController
{
    private XpkType $typeModel;

    public function __construct()
    {
        parent::__construct();
        $this->typeModel = new XpkType();
    }

    /**
     * 分类列表
     */
    public function index(): void
    {
        $types = $this->typeModel->getTree();
        $parentTypes = $this->typeModel->getAll(['type_pid' => 0]);

        $this->assign('types', $types);
        $this->assign('parentTypes', $parentTypes);
        $this->assign('csrfToken', $this->csrfToken());
        $this->assign('flash', $this->getFlash());
        $this->render('type/index', '分类管理');
    }

    /**
     * 获取单个分类（AJAX）
     */
    public function get(): void
    {
        $id = (int)$this->get('id', 0);
        $type = $this->typeModel->find($id);
        
        if (!$type) {
            $this->error('分类不存在');
        }
        
        $this->success('ok', $type);
    }

    /**
     * 添加分类（AJAX）
     */
    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // GET请求显示表单页面（兼容旧方式）
            $parentTypes = $this->typeModel->getAll(['type_pid' => 0]);
            $this->assign('parentTypes', $parentTypes);
            $this->assign('csrfToken', $this->csrfToken());
            $this->render('type/form', '添加分类');
            return;
        }

        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = [
            'type_pid' => (int)$this->post('type_pid', 0),
            'type_name' => trim($this->post('type_name', '')),
            'type_en' => trim($this->post('type_en', '')),
            'type_sort' => (int)$this->post('type_sort', 0),
            'type_status' => (int)$this->post('type_status', 1),
            'type_key' => trim($this->post('type_key', '')),
            'type_des' => trim($this->post('type_des', '')),
        ];

        if (empty($data['type_name'])) {
            $this->error('分类名称不能为空');
        }

        $id = $this->typeModel->insert($data);

        if ($id) {
            $this->log('添加', '分类', "ID:{$id} {$data['type_name']}");
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 编辑分类（AJAX）
     */
    public function edit(int $id): void
    {
        $type = $this->typeModel->find($id);
        if (!$type) {
            $this->error('分类不存在');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // GET请求显示表单页面（兼容旧方式）
            $parentTypes = $this->typeModel->getAll(['type_pid' => 0]);
            $this->assign('type', $type);
            $this->assign('parentTypes', $parentTypes);
            $this->assign('csrfToken', $this->csrfToken());
            $this->render('type/form', '编辑分类');
            return;
        }

        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = [
            'type_pid' => (int)$this->post('type_pid', 0),
            'type_name' => trim($this->post('type_name', '')),
            'type_en' => trim($this->post('type_en', '')),
            'type_sort' => (int)$this->post('type_sort', 0),
            'type_status' => (int)$this->post('type_status', 1),
            'type_key' => trim($this->post('type_key', '')),
            'type_des' => trim($this->post('type_des', '')),
        ];

        // 不能将自己设为父级
        if ($data['type_pid'] == $id) {
            $this->error('不能将自己设为父级分类');
        }

        $this->typeModel->update($id, $data);
        $this->log('编辑', '分类', "ID:{$id} {$data['type_name']}");

        $this->success('保存成功');
    }

    /**
     * 删除分类
     */
    public function delete(): void
    {
        $id = (int)$this->post('id', 0);

        if ($id <= 0) {
            $this->error('参数错误');
        }

        // 检查是否有子分类
        $children = $this->typeModel->count(['type_pid' => $id]);
        if ($children > 0) {
            $this->error('请先删除子分类');
        }

        // 检查是否有视频
        $vodCount = XpkDatabase::getInstance()->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "vod WHERE vod_type_id = ?",
            [$id]
        )['cnt'] ?? 0;

        if ($vodCount > 0) {
            $this->error('该分类下有视频，无法删除');
        }

        $this->typeModel->delete($id);
        $this->log('删除', '分类', "ID:{$id}");
        $this->success('删除成功');
    }
}
