<?php
/**
 * 后台搜索管理控制器
 * Powered by https://xpornkit.com
 */

class AdminSearchController extends AdminBaseController
{
    private XpkSearchLog $searchLogModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'SearchLog.php';
        $this->searchLogModel = new XpkSearchLog();
    }

    /**
     * 搜索统计页面
     */
    public function index(): void
    {
        $stats = $this->searchLogModel->getStats(30);
        $hotKeywords = $this->searchLogModel->getHotKeywords(20, 30);
        $recentKeywords = $this->searchLogModel->getRecentKeywords(20);
        
        $this->assign('stats', $stats);
        $this->assign('hotKeywords', $hotKeywords);
        $this->assign('recentKeywords', $recentKeywords);
        $this->assign('flash', $this->getFlash());
        $this->render('search/index', '搜索统计');
    }

    /**
     * 搜索日志列表
     */
    public function log(): void
    {
        $page = (int)($this->get('page', 1));
        $keyword = trim($this->get('keyword', ''));
        
        $logs = $this->searchLogModel->getLogList($page, 50, $keyword);
        
        $this->assign('logs', $logs);
        $this->assign('keyword', $keyword);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('search/log', '搜索日志');
    }

    /**
     * 清理搜索日志
     */
    public function cleanLog(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $keepDays = (int)$this->post('keep_days', 90);
        $count = $this->searchLogModel->clean($keepDays);
        
        $this->log('清理', '搜索日志', "清理了{$count}条记录");
        $this->success("已清理 {$count} 条搜索日志");
    }
}