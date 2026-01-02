<?php
/**
 * 后台数据统计控制器
 * Powered by https://xpornkit.com
 */

class AdminStatsController extends AdminBaseController
{
    private XpkStats $statsModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Stats.php';
        $this->statsModel = new XpkStats();
    }

    /**
     * 统计面板首页
     */
    public function index(): void
    {
        $days = (int)($this->get('days', 7));
        if (!in_array($days, [7, 14, 30])) {
            $days = 7;
        }

        // 今日概览
        $overview = $this->statsModel->getTodayOverview();
        
        // 访问趋势
        $trend = $this->statsModel->getTrend($days);
        
        // 用户增长
        $userTrend = $this->statsModel->getUserTrend($days);
        
        // 热门视频
        $hotVideos = $this->statsModel->getHotVideos($days, 10);
        
        // 来源统计
        $refererStats = $this->statsModel->getRefererStats($days);
        
        // 设备统计
        $deviceStats = $this->statsModel->getDeviceStats($days);
        
        // 内容统计
        $contentStats = $this->statsModel->getContentStats();
        
        // 实时在线
        $onlineCount = $this->statsModel->getOnlineCount();
        
        // 诊断信息
        $diagInfo = $this->statsModel->getDiagnostics();

        // 日志统计信息
        $logStats = $this->statsModel->getLogStats();

        $this->assign('overview', $overview);
        $this->assign('trend', $trend);
        $this->assign('userTrend', $userTrend);
        $this->assign('hotVideos', $hotVideos);
        $this->assign('refererStats', $refererStats);
        $this->assign('deviceStats', $deviceStats);
        $this->assign('contentStats', $contentStats);
        $this->assign('onlineCount', $onlineCount);
        $this->assign('diagInfo', $diagInfo);
        $this->assign('logStats', $logStats);
        $this->assign('days', $days);
        $this->assign('csrfToken', $this->csrfToken());

        $this->render('stats/index', '数据统计');
    }

    /**
     * 获取趋势数据（AJAX）
     */
    public function trend(): void
    {
        $days = (int)($this->get('days', 7));
        $type = $this->get('type', '');

        $trend = $this->statsModel->getTrend($days, $type);
        $this->success('ok', $trend);
    }

    /**
     * 获取热门内容（AJAX）
     */
    public function hot(): void
    {
        $days = (int)($this->get('days', 7));
        $limit = (int)($this->get('limit', 10));

        $hotVideos = $this->statsModel->getHotVideos($days, $limit);
        $this->success('ok', $hotVideos);
    }

    /**
     * 清理日志
     */
    public function clean(): void
    {
        $keepDays = (int)($this->post('days', 90));
        $deleted = $this->statsModel->cleanOldLogs($keepDays);
        
        $this->log('清理', '统计日志', "保留{$keepDays}天，删除{$deleted}条");
        $this->success("已清理 {$deleted} 条过期日志");
    }

    /**
     * 清理所有日志
     */
    public function cleanAll(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('CSRF验证失败');
        }

        $options = [
            'stats_days' => (int)($this->post('stats_days', 90)),
            'admin_days' => (int)($this->post('admin_days', 30)),
            'search_days' => (int)($this->post('search_days', 90)),
            'collect_days' => (int)($this->post('collect_days', 30)),
            'vote_days' => (int)($this->post('vote_days', 180)),
            'score_days' => (int)($this->post('score_days', 365)),
            'history_days' => (int)($this->post('history_days', 365)),
            'chunk_days' => (int)($this->post('chunk_days', 7)),
        ];

        $results = $this->statsModel->cleanAllLogs($options);
        
        $total = array_sum($results);
        $details = [];
        foreach ($results as $table => $count) {
            if ($count > 0) {
                $details[] = "{$table}: {$count}条";
            }
        }
        
        $this->log('清理', '全部日志', "清理结果: " . implode(', ', $details) . "，共{$total}条");
        $this->success("已清理 {$total} 条记录", $results);
    }
}
