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

        $this->assign('overview', $overview);
        $this->assign('trend', $trend);
        $this->assign('userTrend', $userTrend);
        $this->assign('hotVideos', $hotVideos);
        $this->assign('refererStats', $refererStats);
        $this->assign('deviceStats', $deviceStats);
        $this->assign('contentStats', $contentStats);
        $this->assign('onlineCount', $onlineCount);
        $this->assign('diagInfo', $diagInfo);
        $this->assign('days', $days);

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
}
