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
    public function index(int $page = 1, string $keyword = ''): void
    {
        // 支持两种方式：伪静态 /search/关键词 或 ?wd=关键词
        if (empty($keyword)) {
            $keyword = trim($this->get('wd', ''));
        } else {
            $keyword = urldecode($keyword);
        }
        
        if (empty($keyword)) {
            $this->assign('vodList', []);
            $this->assign('keyword', '');
            $this->assign('total', 0);
            $this->assign('page', 1);
            $this->assign('totalPages', 0);
            $this->assign('baseUrl', '/search');
        } else {
            $result = $this->vodModel->search($keyword, $page, PAGE_SIZE);
            
            $this->assign('vodList', $result['list']);
            $this->assign('keyword', $keyword);
            $this->assign('total', $result['total']);
            $this->assign('page', $result['page']);
            $this->assign('totalPages', $result['totalPages']);
            // 伪静态分页URL
            $this->assign('baseUrl', '/search/' . urlencode($keyword));
        }
        
        // SEO - 搜索页禁止收录
        $this->assign('title', ($keyword ? $keyword . ' - ' : '') . '搜索 - ' . SITE_NAME);
        $this->assign('noindex', true);
        
        $this->render('search/index');
    }
}
