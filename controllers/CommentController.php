<?php
/**
 * 前台评论控制�?
 * Powered by https://xpornkit.com
 */

class CommentController extends BaseController
{
    private XpkComment $commentModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Comment.php';
        $this->commentModel = new XpkComment();
    }

    /**
     * 获取评论列表（AJAX�?
     */
    public function list(): void
    {
        $type = $this->get('type', 'vod');
        $targetId = (int)$this->get('id', 0);
        $page = (int)$this->get('page', 1);

        if (!in_array($type, ['vod', 'art']) || $targetId <= 0) {
            $this->apiJson(1, '参数错误');
        }

        $result = $this->commentModel->getListByTarget($type, $targetId, $page, 20);
        
        // 获取用户投票状�?
        $userId = $this->getUserId();
        $commentIds = array_column($result['list'], 'comment_id');
        foreach ($result['list'] as &$item) {
            foreach ($item['replies'] as $reply) {
                $commentIds[] = $reply['comment_id'];
            }
        }
        $userVotes = $userId ? $this->commentModel->getUserVotes($userId, $commentIds) : [];

        $this->apiJson(0, 'success', [
            'list' => $result['list'],
            'total' => $result['total'],
            'page' => $page,
            'user_votes' => $userVotes
        ]);
    }

    /**
     * 获取更多回复（AJAX�?
     */
    public function replies(): void
    {
        $parentId = (int)$this->get('parent_id', 0);
        $offset = (int)$this->get('offset', 0);

        if ($parentId <= 0) {
            $this->apiJson(1, '参数错误');
        }

        $replies = $this->commentModel->getMoreReplies($parentId, $offset, 10);
        $this->apiJson(0, 'success', ['list' => $replies]);
    }

    /**
     * 发表评论
     */
    public function post(): void
    {
        // 检查评论功能是否开�?
        $config = xpk_cache()->get('site_config') ?: [];
        if (($config['comment_enabled'] ?? '1') !== '1') {
            $this->apiJson(1, '评论功能已关�?);
        }

        // 检查登�?
        $userId = $this->getUserId();
        $allowGuest = ($config['comment_guest'] ?? '0') === '1';
        
        if (!$userId && !$allowGuest) {
            $this->apiJson(2, '请先登录');
        }

        $type = $this->post('type', 'vod');
        $targetId = (int)$this->post('target_id', 0);
        $content = trim($this->post('content', ''));
        $parentId = (int)$this->post('parent_id', 0);
        $replyId = (int)$this->post('reply_id', 0);

        // 验证参数
        if (!in_array($type, ['vod', 'art']) || $targetId <= 0) {
            $this->apiJson(1, '参数错误');
        }

        // 验证内容长度
        $minLen = (int)($config['comment_min_length'] ?? 1);
        $maxLen = (int)($config['comment_max_length'] ?? 500);
        $contentLen = mb_strlen($content);

        if ($contentLen < $minLen) {
            $this->apiJson(1, "评论内容至少 {$minLen} 个字");
        }
        if ($contentLen > $maxLen) {
            $this->apiJson(1, "评论内容最�?{$maxLen} 个字");
        }

        // 检查发言频率
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        if (!$this->commentModel->checkFrequency($userId ?: 0, $ip)) {
            $interval = (int)($config['comment_interval'] ?? 60);
            $this->apiJson(1, "发言太频繁，�?{$interval} 秒后再试");
        }

        // 发表评论
        $data = [
            'comment_type' => $type,
            'target_id' => $targetId,
            'user_id' => $userId ?: 0,
            'parent_id' => $parentId,
            'reply_id' => $replyId,
            'comment_content' => $content,
        ];

        $result = $this->commentModel->add($data);

        if ($result['id']) {
            $msg = $result['need_audit'] ? '评论已提交，等待审核' : '评论成功';
            $this->apiJson(0, $msg, [
                'id' => $result['id'],
                'need_audit' => $result['need_audit']
            ]);
        } else {
            $this->apiJson(1, '评论失败');
        }
    }

    /**
     * 点赞/�?
     */
    public function vote(): void
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->apiJson(2, '请先登录');
        }

        $commentId = (int)$this->post('id', 0);
        $action = $this->post('action', 'up');

        if ($commentId <= 0 || !in_array($action, ['up', 'down'])) {
            $this->apiJson(1, '参数错误');
        }

        $result = $this->commentModel->vote($commentId, $userId, $action);
        
        // 获取最新数�?
        $comment = $this->commentModel->find($commentId);
        
        $this->apiJson(0, 'success', [
            'action' => $result['action'],
            'type' => $result['type'],
            'up' => $comment['comment_up'] ?? 0,
            'down' => $comment['comment_down'] ?? 0
        ]);
    }

    /**
     * 删除自己的评�?
     */
    public function delete(): void
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->apiJson(2, '请先登录');
        }

        $commentId = (int)$this->post('id', 0);
        if ($commentId <= 0) {
            $this->apiJson(1, '参数错误');
        }

        $comment = $this->commentModel->find($commentId);
        if (!$comment || $comment['user_id'] != $userId) {
            $this->apiJson(1, '无权删除');
        }

        $this->commentModel->delete($commentId, true);
        $this->apiJson(0, '删除成功');
    }

    /**
     * 获取当前用户ID
     */
    private function getUserId(): int
    {
        return (int)($_SESSION['user_id'] ?? 0);
    }

    /**
     * API响应
     */
    private function apiJson(int $code, string $msg, array $data = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['code' => $code, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
