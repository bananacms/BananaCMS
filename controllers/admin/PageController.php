<?php
/**
 * 后台单页管理控制器
 * Powered by https://xpornkit.com
 */

class AdminPageController extends AdminBaseController
{
    private XpkPage $pageModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Page.php';
        $this->pageModel = new XpkPage();
    }

    /**
     * 单页列表
     */
    public function index(): void
    {
        $pages = $this->pageModel->getAll();
        
        $this->assign('pages', $pages);
        $this->assign('flash', $this->getFlash());
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('page/index', '单页管理');
    }

    /**
     * 添加页面
     */
    public function add(): void
    {
        $this->assign('page', null);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('page/form', '添加单页');
    }

    /**
     * 处理添加
     */
    public function doAdd(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $slug = trim($this->post('page_slug', ''));
        $title = trim($this->post('page_title', ''));
        $content = $this->post('page_content', '');
        $sort = (int)$this->post('page_sort', 0);
        $status = (int)$this->post('page_status', 1);
        $footer = (int)$this->post('page_footer', 1);

        if (empty($slug) || empty($title)) {
            $this->error('标识和标题不能为空');
        }

        // 验证slug格式
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $slug)) {
            $this->error('标识只能包含字母、数字、下划线和横线');
        }

        // 检查slug是否已存在
        if ($this->pageModel->slugExists($slug)) {
            $this->error('该标识已存在');
        }

        $this->pageModel->insert([
            'page_slug' => $slug,
            'page_title' => $title,
            'page_content' => $content,
            'page_sort' => $sort,
            'page_status' => $status,
            'page_footer' => $footer
        ]);

        $this->log('添加', '单页', $title);
        $this->success('添加成功');
    }

    /**
     * 编辑页面
     */
    public function edit(int $id): void
    {
        $page = $this->pageModel->find($id);
        if (!$page) {
            $this->flash('error', '页面不存在');
            $this->redirect('/' . $this->adminEntry . '?s=page');
            return;
        }

        $this->assign('page', $page);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('page/form', '编辑 - ' . $page['page_title']);
    }

    /**
     * 处理编辑
     */
    public function doEdit(int $id): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $page = $this->pageModel->find($id);
        if (!$page) {
            $this->error('页面不存在');
        }

        $slug = trim($this->post('page_slug', ''));
        $title = trim($this->post('page_title', ''));
        $content = $this->post('page_content', '');
        $sort = (int)$this->post('page_sort', 0);
        $status = (int)$this->post('page_status', 1);
        $footer = (int)$this->post('page_footer', 1);

        if (empty($slug) || empty($title)) {
            $this->error('标识和标题不能为空');
        }

        // 验证slug格式
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $slug)) {
            $this->error('标识只能包含字母、数字、下划线和横线');
        }

        // 检查slug是否已存在（排除当前记录）
        if ($this->pageModel->slugExists($slug, $id)) {
            $this->error('该标识已存在');
        }

        $this->pageModel->update($id, [
            'page_slug' => $slug,
            'page_title' => $title,
            'page_content' => $content,
            'page_sort' => $sort,
            'page_status' => $status,
            'page_footer' => $footer
        ]);

        $this->log('编辑', '单页', $title);
        $this->success('保存成功');
    }

    /**
     * 删除页面
     */
    public function delete(): void
    {
        $id = (int)$this->post('id', 0);
        
        $page = $this->pageModel->find($id);
        if (!$page) {
            $this->error('页面不存在');
        }

        $this->pageModel->delete($id);
        $this->log('删除', '单页', $page['page_title']);
        $this->success('删除成功');
    }

    /**
     * 初始化默认页面
     */
    public function init(): void
    {
        $added = $this->pageModel->initDefaults();
        $this->log('初始化', '单页', "添加{$added}个默认页面");
        $this->success("初始化完成，添加了 {$added} 个默认页面");
    }
}
