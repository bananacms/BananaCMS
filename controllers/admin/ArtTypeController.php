<?php
/**
 * 后台文章分类管理控制器
 * Powered by https://xpornkit.com
 */

class AdminArtTypeController extends AdminBaseController
{
    private XpkArtType $artTypeModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'ArtType.php';
        $this->artTypeModel = new XpkArtType();
    }

    /**
     * 分类列表
     */
    public function index(): void
    {
        $list = $this->artTypeModel->getAll();
        
        $this->assign('list', $list);
        $this->assign('csrfToken', $this->csrfToken());
        $this->assign('flash', $this->getFlash());
        $this->render('art_type/index', '文章分类管理');
    }

    /**
     * 获取单个分类（AJAX）
     */
    public function get(): void
    {
        $id = (int)$this->get('id', 0);
        $type = $this->artTypeModel->find($id);
        
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
            $this->assign('csrfToken', $this->csrfToken());
            $this->render('art_type/form', '添加文章分类');
            return;
        }

        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = [
            'type_name' => trim($this->post('type_name', '')),
            'type_pid' => (int)$this->post('type_pid', 0),
            'type_sort' => (int)$this->post('type_sort', 0),
            'type_status' => (int)$this->post('type_status', 1),
        ];

        if (empty($data['type_name'])) {
            $this->error('分类名称不能为空');
        }

        $id = $this->artTypeModel->insert($data);

        if ($id) {
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
        $type = $this->artTypeModel->find($id);
        if (!$type) {
            $this->error('分类不存在');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->assign('type', $type);
            $this->assign('csrfToken', $this->csrfToken());
            $this->render('art_type/form', '编辑文章分类');
            return;
        }

        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = [
            'type_name' => trim($this->post('type_name', '')),
            'type_pid' => (int)$this->post('type_pid', 0),
            'type_sort' => (int)$this->post('type_sort', 0),
            'type_status' => (int)$this->post('type_status', 1),
        ];

        if (empty($data['type_name'])) {
            $this->error('分类名称不能为空');
        }

        $this->artTypeModel->update($id, $data);

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

        // 检查是否有文章使用此分类
        $db = XpkDatabase::getInstance();
        $count = $db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "art WHERE art_type_id = ?",
            [$id]
        )['cnt'] ?? 0;

        if ($count > 0) {
            $this->error("该分类下有 {$count} 篇文章，无法删除");
        }

        $this->artTypeModel->delete($id);
        $this->success('删除成功');
    }
}
