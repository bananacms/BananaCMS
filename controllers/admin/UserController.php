<?php
/**
 * 后台用户管理控制器
 * Powered by https://xpornkit.com
 */

class AdminUserController extends AdminBaseController
{
    private XpkUser $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new XpkUser();
    }

    /**
     * 用户列表
     */
    public function index(): void
    {
        $page = (int)($this->get('page', 1));
        $keyword = trim($this->get('keyword', ''));
        $status = $this->get('status', '');

        $db = XpkDatabase::getInstance();
        $where = [];
        $params = [];

        if ($keyword) {
            $where[] = '(user_name LIKE ? OR user_email LIKE ?)';
            $kw = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $keyword) . '%';
            $params[] = $kw;
            $params[] = $kw;
        }

        if ($status !== '') {
            $where[] = 'user_status = ?';
            $params[] = (int)$status;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $pageSize = 20;
        $offset = ($page - 1) * $pageSize;

        $list = $db->query(
            "SELECT * FROM " . DB_PREFIX . "user {$whereStr} ORDER BY user_id DESC LIMIT {$pageSize} OFFSET {$offset}",
            $params
        );

        $total = $db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "user {$whereStr}",
            $params
        )['cnt'] ?? 0;

        $this->assign('list', $list);
        $this->assign('total', $total);
        $this->assign('page', $page);
        $this->assign('pageSize', $pageSize);
        $this->assign('totalPages', ceil($total / $pageSize));
        $this->assign('keyword', $keyword);
        $this->assign('status', $status);
        $this->assign('flash', $this->getFlash());

        $this->render('user/index', '用户管理');
    }

    /**
     * 编辑用户
     */
    public function edit(int $id): void
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->flash('error', '用户不存在');
            $this->redirect('/admin.php/user');
        }

        $this->assign('user', $user);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('user/form', '编辑用户');
    }

    /**
     * 处理编辑 (AJAX)
     */
    public function doEdit(int $id): void
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->error('用户不存在');
        }

        $data = [
            'user_nick_name' => trim($this->post('user_nick_name', '')),
            'user_email' => trim($this->post('user_email', '')),
            'user_status' => (int)$this->post('user_status', 1),
        ];

        // 如果填写了新密码
        $newPwd = $this->post('user_pwd', '');
        if (!empty($newPwd)) {
            $data['user_pwd'] = password_hash($newPwd, PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $data);
        $this->log('编辑', '用户', "ID:{$id} {$user['user_name']}");

        $this->success('保存成功');
    }

    /**
     * 删除用户
     */
    public function delete(): void
    {
        $id = (int)$this->post('id', 0);

        if ($id <= 0) {
            $this->error('参数错误');
        }

        $this->userModel->delete($id);
        $this->log('删除', '用户', "ID:{$id}");
        $this->success('删除成功');
    }
}
