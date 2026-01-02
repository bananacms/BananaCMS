<?php
/**
 * 操作日志控制器
 * Powered by https://xpornkit.com
 */

class AdminLogController extends AdminBaseController
{
    private XpkAdminLog $logModel;

    public function __construct()
    {
        parent::__construct();
        $this->logModel = new XpkAdminLog();
    }

    /**
     * 日志列表
     */
    public function index(): void
    {
        $page = max(1, (int)$this->get('page', 1));
        $filters = [
            'admin_id' => $this->get('admin_id'),
            'module' => $this->get('module'),
        ];

        $result = $this->logModel->getList($page, 20, $filters);
        
        $this->assign('list', $result['list']);
        $this->assign('page', $result['page']);
        $this->assign('totalPages', $result['totalPages']);
        $this->assign('total', $result['total']);
        $this->assign('filters', $filters);
        $this->assign('csrfToken', $this->csrfToken());
        $this->assign('ipMaskingInfo', XpkAdminLog::getIPMaskingInfo());
        
        $this->render('log/index', '操作日志');
    }

    /**
     * 清理日志
     */
    public function clean(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('CSRF验证失败');
        }

        $days = (int)$this->post('days', 30);
        $count = $this->logModel->clean($days);
        
        $this->log('清理', '日志', "清理{$days}天前日志，共{$count}条");
        $this->success("已清理 {$count} 条日志");
    }
}
