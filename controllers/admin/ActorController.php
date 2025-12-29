<?php
/**
 * 后台演员管理控制器
 * Powered by https://xpornkit.com
 */

class AdminActorController extends AdminBaseController
{
    private XpkActor $actorModel;

    public function __construct()
    {
        parent::__construct();
        $this->actorModel = new XpkActor();
    }

    /**
     * 演员列表
     */
    public function index(): void
    {
        $page = (int)($this->get('page', 1));
        $keyword = trim($this->get('keyword', ''));

        $db = XpkDatabase::getInstance();
        $where = [];
        $params = [];

        if ($keyword) {
            $where[] = 'actor_name LIKE ?';
            $params[] = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $keyword) . '%';
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $pageSize = 20;
        $offset = ($page - 1) * $pageSize;

        $list = $db->query(
            "SELECT * FROM " . DB_PREFIX . "actor {$whereStr} ORDER BY actor_id DESC LIMIT {$pageSize} OFFSET {$offset}",
            $params
        );

        $total = $db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "actor {$whereStr}",
            $params
        )['cnt'] ?? 0;

        $this->assign('list', $list);
        $this->assign('total', $total);
        $this->assign('page', $page);
        $this->assign('pageSize', $pageSize);
        $this->assign('totalPages', ceil($total / $pageSize));
        $this->assign('keyword', $keyword);
        $this->assign('csrfToken', $this->csrfToken());
        $this->assign('flash', $this->getFlash());

        $this->render('actor/index', '演员管理');
    }

    /**
     * 获取单个演员（AJAX）
     */
    public function getOne(): void
    {
        $id = (int)$this->input('id', 0);
        $actor = $this->actorModel->find($id);
        
        if (!$actor) {
            $this->error('演员不存在');
        }
        
        $this->success('ok', $actor);
    }

    /**
     * 添加演员（AJAX）
     */
    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->assign('csrfToken', $this->csrfToken());
            $this->render('actor/form', '添加演员');
            return;
        }

        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = $this->getFormData();
        $data['actor_time'] = time();
        
        if (empty($data['actor_name'])) {
            $this->error('演员姓名不能为空');
        }
        
        // 自动生成 slug
        if (empty($data['actor_slug']) && !empty($data['actor_name'])) {
            require_once CORE_PATH . 'Slug.php';
            $data['actor_slug'] = xpk_slug_unique($data['actor_name'], 'actor', 'actor_slug');
        }

        $id = $this->actorModel->insert($data);

        if ($id) {
            $this->log('添加', '演员', "ID:{$id} {$data['actor_name']}");
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 编辑演员（AJAX）
     */
    public function edit(int $id): void
    {
        $actor = $this->actorModel->find($id);
        if (!$actor) {
            $this->error('演员不存在');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->assign('actor', $actor);
            $this->assign('csrfToken', $this->csrfToken());
            $this->render('actor/form', '编辑演员');
            return;
        }

        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = $this->getFormData();
        $data['actor_time'] = time();
        
        // 自动生成 slug（如果为空或名称变更）
        if (empty($data['actor_slug']) || ($data['actor_name'] !== $actor['actor_name'] && empty(trim($this->post('actor_slug', ''))))) {
            require_once CORE_PATH . 'Slug.php';
            $data['actor_slug'] = xpk_slug_unique($data['actor_name'], 'actor', 'actor_slug', $id);
        }

        $this->actorModel->update($id, $data);
        $this->log('编辑', '演员', "ID:{$id} {$data['actor_name']}");

        $this->success('保存成功');
    }

    /**
     * 删除演员
     */
    public function delete(): void
    {
        $id = (int)$this->post('id', 0);

        if ($id <= 0) {
            $this->error('参数错误');
        }

        $this->actorModel->delete($id);
        $this->log('删除', '演员', "ID:{$id}");
        $this->success('删除成功');
    }

    /**
     * 获取表单数据
     */
    private function getFormData(): array
    {
        return [
            'actor_name' => trim($this->post('actor_name', '')),
            'actor_en' => trim($this->post('actor_en', '')),
            'actor_slug' => trim($this->post('actor_slug', '')),
            'actor_pic' => trim($this->post('actor_pic', '')),
            'actor_sex' => trim($this->post('actor_sex', '')),
            'actor_area' => trim($this->post('actor_area', '')),
            'actor_blood' => trim($this->post('actor_blood', '')),
            'actor_birthday' => trim($this->post('actor_birthday', '')),
            'actor_height' => trim($this->post('actor_height', '')),
            'actor_weight' => trim($this->post('actor_weight', '')),
            'actor_content' => trim($this->post('actor_content', '')),
            'actor_status' => (int)$this->post('actor_status', 1),
        ];
    }
}
