<?php
/**
 * 前台评分控制器
 * Powered by https://xpornkit.com
 */

class ScoreController extends BaseController
{
    private XpkScore $scoreModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Score.php';
        $this->scoreModel = new XpkScore();
    }

    /**
     * 提交评分
     */
    public function rate(): void
    {
        // CSRF 验证
        $this->requireCsrf();
        
        // 速率限制检查
        $this->requireRateLimit('score', 10, 300); // 5分钟内最多10次评分
        
        $type = $this->post('type', 'vod');
        $targetId = (int)$this->post('target_id', 0);
        $score = (int)$this->post('score', 0);

        // 验证参数
        if (!in_array($type, ['vod', 'art']) || $targetId <= 0) {
            $this->apiJson(1, '参数错误');
        }

        if ($score < 1 || $score > 10) {
            $this->apiJson(1, '评分范围为1-10分');
        }

        // 检查评分设置
        $config = xpk_cache()->get('site_config') ?: [];
        $allowGuest = ($config['score_guest'] ?? '1') === '1';

        $userId = $this->getUserId();

        if (!$userId && !$allowGuest) {
            $this->apiJson(2, '请先登录');
        }

        // 执行评分
        if ($userId) {
            $result = $this->scoreModel->rate($type, $targetId, $userId, $score);
        } else {
            $result = $this->scoreModel->rateByIp($type, $targetId, $score);
        }

        if ($result['action'] === 'exists') {
            $this->apiJson(1, $result['message']);
        }

        // 获取最新统计
        $stats = $this->scoreModel->getStats($type, $targetId);

        // 记录评分操作日志
        $this->logUserAction(XpkEventTypes::SCORE_RATE, [
            'type' => $type,
            'target_id' => $targetId,
            'score' => $score,
            'action' => $result['action'],
            'stats' => $stats
        ]);

        $msg = $result['action'] === 'update' ? '评分已更新' : '评分成功';
        $this->apiJson(0, $msg, [
            'action' => $result['action'],
            'score' => $score,
            'stats' => $stats
        ]);
    }

    /**
     * 获取评分统计
     */
    public function stats(): void
    {
        $type = $this->get('type', 'vod');
        $targetId = (int)$this->get('target_id', 0);

        if (!in_array($type, ['vod', 'art']) || $targetId <= 0) {
            $this->apiJson(1, '参数错误');
        }

        $stats = $this->scoreModel->getStats($type, $targetId);
        
        // 检查当前用户是否已评分
        $userId = $this->getUserId();
        $userScore = null;
        $hasRated = false;

        if ($userId) {
            $record = $this->scoreModel->getUserScore($type, $targetId, $userId);
            if ($record) {
                $userScore = $record['score'];
                $hasRated = true;
            }
        } else {
            $hasRated = $this->scoreModel->hasRated($type, $targetId, 0);
        }

        $this->apiJson(0, 'success', [
            'stats' => $stats,
            'user_score' => $userScore,
            'has_rated' => $hasRated
        ]);
    }

    /**
     * 获取当前用户ID
     */
    private function getUserId(): int
    {
        return (int)($_SESSION['user_id'] ?? 0);
    }

}
