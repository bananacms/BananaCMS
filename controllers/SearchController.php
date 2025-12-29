<?php
/**
 * 搜索控制器
 * Powered by https://xpornkit.com
 */

class SearchController extends BaseController
{
    private XpkVod $vodModel;

    public function __construct()
    {
        parent::__construct();
        $this->vodModel = new XpkVod();
    }

    /**
     * 搜索页
     */
    public function index(int $page = 1): void
    {
        $keyword = trim($this->get('wd', ''));
        
        if (empty($keyword)) {
            $this->assign('vodList', []);
            $this->assign('keyword', '');
            $this->assign('total', 0);
            $this->assign('page', 1);
            $this->assign('totalPages', 0);
        } else {
            $result = $this->vodModel->search($keyword, $page, PAGE_SIZE);
            
            $this->assign('vodList', $result['list']);
            $this->assign('keyword', $keyword);
            $this->assign('total', $result['total']);
            $this->assign('page', $result['page']);
            $this->assign('totalPages', $result['totalPages']);
        }
        
        // SEO - 搜索页禁止收录
        $this->assign('title', ($keyword ? $keyword . ' - ' : '') . '搜索 - ' . SITE_NAME);
        $this->assign('noindex', true);
        
        $this->render('search/index');
    }
}
