<?php
/**
 * 搜索控制器
 * Powered by https://xpornkit.com
 */

class SearchController extends BaseController
{
    private XpkVod $vodModel;
    private XpkSearchLog $searchLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->vodModel = new XpkVod();
        require_once MODEL_PATH . 'SearchLog.php';
        $this->searchLogModel = new XpkSearchLog();
    }

    /**
     * 搜索页
     */
    public function index(int $page = 1, string $keyword = ''): void
    {
        // 支持两种方式：伪静态 /search/关键词 或 ?wd=关键词
        if (empty($keyword)) {
            $keyword = trim($this->get('wd', ''));
        }
        // 路由已经解码过了，不需要再urldecode
        $keyword = trim($keyword);
        
        if (empty($keyword)) {
            // 获取热门搜索词和最新搜索词
            $hotKeywords = $this->searchLogModel->getHotKeywords(8);
            $recentKeywords = $this->searchLogModel->getRecentKeywords(8);
            
            $this->assign('vodList', []);
            $this->assign('keyword', '');
            $this->assign('total', 0);
            $this->assign('page', 1);
            $this->assign('totalPages', 0);
            $this->assign('baseUrl', '/search');
            $this->assign('hotKeywords', $hotKeywords);
            $this->assign('recentKeywords', $recentKeywords);
        } else {
            // 记录搜索日志
            $this->searchLogModel->log($keyword);
            
            $result = $this->vodModel->search($keyword, $page, PAGE_SIZE);
            
            // 记录搜索统计
            try {
                $stats = new XpkStats();
                $stats->log('search', 0, $keyword);
            } catch (Exception $e) {}
            
            $this->assign('vodList', $result['list']);
            $this->assign('keyword', $keyword);
            $this->assign('total', $result['total']);
            $this->assign('page', $result['page']);
            $this->assign('totalPages', $result['totalPages']);
            // 伪静态分页URL
            $this->assign('baseUrl', '/search/' . urlencode($keyword));
            $this->assign('hotKeywords', []);
            $this->assign('recentKeywords', []);
        }
        
        // SEO - 搜索页禁止收录
        $this->assign('title', ($keyword ? $keyword . ' - ' : '') . '搜索 - ' . SITE_NAME);
        $this->assign('noindex', true);
        
        $this->render('search/index');
    }

    /**
     * 搜索建议API (AJAX)
     */
    public function suggest(): void
    {
        $query = trim($this->get('q', ''));
        
        if (strlen($query) < 2) {
            $this->json(['suggestions' => []]);
            return;
        }
        
        $suggestions = $this->searchLogModel->getSuggestions($query, 8);
        
        $this->json([
            'suggestions' => array_map(function($item) {
                return [
                    'keyword' => $item['keyword'],
                    'count' => $item['search_count']
                ];
            }, $suggestions)
        ]);
    }
}
