<?php
/**
 * 后台评论管理控制器
 * Powered by https://xpornkit.com
 */

class AdminCommentController extends AdminBaseController
{
    private XpkComment $commentModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Comment.php';
        $this->commentModel = new XpkComment();
    }

    /**
     * 评论列表
     */
    public function index(): void
    {
        $page = (int)($this->get('page', 1));
        $status = $this->get('status', '');
        $type = $this->get('type', '');
        
        $statusFilter = $status !== '' ? (int)$status : -1;
        $result = $this->commentModel->getList($statusFilter, $type, $page, 20);
        $stats = $this->commentModel->getStats();
        
        $this->assign('list', $result['list']);
        $this->assign('total', $result['total']);
        $this->assign('page', $page);
        $this->assign('pageSize', 20);
        $this->assign('totalPages', ceil($result['total'] / 20));
        $this->assign('status', $status);
        $this->assign('type', $type);
        $this->assign('stats', $stats);
        $this->assign('flash', $this->getFlash());

        $this->render('comment/index', '评论管理');
    }

    /**
     * 审核通过
     */
    public function approve(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $id = (int)$this->post('id', 0);
        
        if ($id <= 0) {
            $this->error('参数错误');
        }

        $this->commentModel->audit($id, XpkComment::STATUS_APPROVED);
        $this->log('审核', '评论', "ID:{$id} 通过");
        $this->success('审核通过');
    }

    /**
     * 审核拒绝
     */
    public function reject(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $id = (int)$this->post('id', 0);
        
        if ($id <= 0) {
            $this->error('参数错误');
        }

        $this->commentModel->audit($id, XpkComment::STATUS_REJECTED);
        $this->log('审核', '评论', "ID:{$id} 拒绝");
        $this->success('已拒绝');
    }

    /**
     * 删除评论
     */
    public function delete(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $id = (int)$this->post('id', 0);

        if ($id <= 0) {
            $this->error('参数错误');
        }

        // 删除前先查询评论详情，用于日志记录
        $comment = $this->commentModel->find($id);
        if (!$comment) {
            $this->error('评论不存在');
        }

        $this->commentModel->delete($id, true);
        
        // 记录详细日志
        $content = mb_substr($comment['comment_content'], 0, 50) . (mb_strlen($comment['comment_content']) > 50 ? '...' : '');
        $user = $comment['user_name'] ?: '游客';
        $this->log('删除', '评论', "ID:{$id} 用户:{$user} 内容:\"{$content}\"");
        $this->success('删除成功');
    }

    /**
     * 批量审核
     */
    public function batchAudit(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $ids = $this->post('ids', '');
        $action = $this->post('action', '');

        if (empty($ids) || !in_array($action, ['approve', 'reject', 'delete'])) {
            $this->error('参数错误');
        }

        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        if (empty($idArr)) {
            $this->error('请选择评论');
        }

        if ($action === 'delete') {
            // 批量删除前先查询评论详情，用于日志记录
            $comments = [];
            foreach ($idArr as $id) {
                $comment = $this->commentModel->find($id);
                if ($comment) {
                    $comments[] = $comment;
                    $this->commentModel->delete($id, true);
                }
            }
            
            // 记录详细日志
            $logDetails = [];
            foreach ($comments as $comment) {
                $content = mb_substr($comment['comment_content'], 0, 30) . '...';
                $user = $comment['user_name'] ?: '游客';
                $logDetails[] = "ID:{$comment['comment_id']}({$user})";
            }
            $logContent = implode(', ', $logDetails);
            
            $this->log('批量删除', '评论', "删除了 " . count($comments) . " 条评论: {$logContent}");
            $this->success('删除成功');
        } else {
            $status = $action === 'approve' ? XpkComment::STATUS_APPROVED : XpkComment::STATUS_REJECTED;
            $count = $this->commentModel->batchAudit($idArr, $status);
            $this->log('批量审核', '评论', "IDs:" . implode(',', $idArr) . " {$action}");
            $this->success("已处理 {$count} 条评论");
        }
    }

    /**
     * 评论设置
     */
    public function setting(): void
    {
        $config = xpk_cache()->get('site_config') ?: [];
        
        $this->assign('config', $config);
        $this->assign('csrfToken', $this->csrfToken());
        $this->assign('flash', $this->getFlash());
        $this->render('comment/setting', '评论设置');
    }

    /**
     * 保存设置
     */
    public function saveSetting(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $config = xpk_cache()->get('site_config') ?: [];
        
        $config['comment_enabled'] = $this->post('comment_enabled', '1');
        $config['comment_audit'] = $this->post('comment_audit', '0');
        $config['comment_guest'] = $this->post('comment_guest', '0');
        $config['comment_interval'] = (int)$this->post('comment_interval', 60);
        $config['comment_min_length'] = (int)$this->post('comment_min_length', 1);
        $config['comment_max_length'] = (int)$this->post('comment_max_length', 500);
        $config['comment_sensitive_words'] = trim($this->post('comment_sensitive_words', ''));
        
        xpk_cache()->set('site_config', $config);
        
        $this->log('修改', '评论设置', '');
        $this->flash('success', '设置已保存');
        $this->redirect('/' . $this->adminEntry . '/comment/setting');
    }
}
