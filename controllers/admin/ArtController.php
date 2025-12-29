<?php
/**
 * 后台文章管理控制器
 * Powered by https://xpornkit.com
 */

class AdminArtController extends AdminBaseController
{
    private XpkArt $artModel;
    private XpkArtType $artTypeModel;

    public function __construct()
    {
        parent::__construct();
        $this->artModel = new XpkArt();
        $this->artTypeModel = new XpkArtType();
    }

    /**
     * 文章列表
     */
    public function index(): void
    {
        $page = (int)($this->get('page', 1));
        $keyword = trim($this->get('keyword', ''));
        $typeId = (int)$this->get('type_id', 0);

        $db = XpkDatabase::getInstance();
        $where = [];
        $params = [];

        if ($keyword) {
            $where[] = 'a.art_name LIKE ?';
            $params[] = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $keyword) . '%';
        }

        if ($typeId > 0) {
            $where[] = 'a.art_type_id = ?';
            $params[] = $typeId;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $pageSize = 20;
        $offset = ($page - 1) * $pageSize;

        $list = $db->query(
            "SELECT a.*, t.type_name FROM " . DB_PREFIX . "art a 
             LEFT JOIN " . DB_PREFIX . "art_type t ON a.art_type_id = t.type_id 
             {$whereStr} ORDER BY a.art_id DESC LIMIT {$pageSize} OFFSET {$offset}",
            $params
        );

        $total = $db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "art a {$whereStr}",
            $params
        )['cnt'] ?? 0;

        $this->assign('list', $list);
        $this->assign('total', $total);
        $this->assign('page', $page);
        $this->assign('pageSize', $pageSize);
        $this->assign('totalPages', ceil($total / $pageSize));
        $this->assign('keyword', $keyword);
        $this->assign('typeId', $typeId);
        $this->assign('types', $this->artTypeModel->getAll());
        $this->assign('flash', $this->getFlash());

        $this->render('art/index', '文章管理');
    }

    /**
     * 添加文章
     */
    public function add(): void
    {
        $this->assign('types', $this->artTypeModel->getAll());
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('art/form', '添加文章');
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
        $data['art_time'] = time();
        
        // 自动生成 slug
        if (empty($data['art_slug']) && !empty($data['art_name'])) {
            require_once CORE_PATH . 'Slug.php';
            $data['art_slug'] = xpk_slug_unique($data['art_name'], 'art', 'art_slug');
        }

        $id = $this->artModel->insert($data);

        if ($id) {
            $this->log('添加', '文章', "ID:{$id} {$data['art_name']}");
            $this->flash('success', '添加成功');
            $this->redirect('/admin.php/art');
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 编辑文章
     */
    public function edit(int $id): void
    {
        $art = $this->artModel->find($id);
        if (!$art) {
            $this->flash('error', '文章不存在');
            $this->redirect('/admin.php/art');
        }

        $this->assign('art', $art);
        $this->assign('types', $this->artTypeModel->getAll());
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('art/form', '编辑文章');
    }

    /**
     * 处理编辑
     */
    public function doEdit(int $id): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $art = $this->artModel->find($id);
        if (!$art) {
            $this->error('文章不存在');
        }

        $data = $this->getFormData();
        $data['art_time'] = time();
        
        // 自动生成 slug（如果为空或名称变更）
        if (empty($data['art_slug']) || ($data['art_name'] !== $art['art_name'] && empty(trim($this->post('art_slug', ''))))) {
            require_once CORE_PATH . 'Slug.php';
            $data['art_slug'] = xpk_slug_unique($data['art_name'], 'art', 'art_slug', $id);
        }

        $this->artModel->update($id, $data);
        $this->log('编辑', '文章', "ID:{$id} {$data['art_name']}");

        $this->flash('success', '保存成功');
        $this->redirect('/admin.php/art');
    }

    /**
     * 删除文章
     */
    public function delete(): void
    {
        $id = (int)$this->post('id', 0);

        if ($id <= 0) {
            $this->error('参数错误');
        }

        $this->artModel->delete($id);
        $this->log('删除', '文章', "ID:{$id}");
        $this->success('删除成功');
    }

    /**
     * 获取表单数据
     */
    private function getFormData(): array
    {
        return [
            'art_type_id' => (int)$this->post('art_type_id', 0),
            'art_name' => trim($this->post('art_name', '')),
            'art_slug' => trim($this->post('art_slug', '')),
            'art_pic' => trim($this->post('art_pic', '')),
            'art_author' => trim($this->post('art_author', '')),
            'art_from' => trim($this->post('art_from', '')),
            'art_content' => trim($this->post('art_content', '')),
            'art_status' => (int)$this->post('art_status', 1),
        ];
    }
}
