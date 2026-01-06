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
     * 搜索防护检查
     * 包含：空查询拦截、Referer校验
     * @param string $keyword 搜索关键词
     */
    private function searchProtection(string $keyword): void
    {
        // 1. 空查询拦截 - 阻止 /search?wd= 空参数攻击
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['wd']) && trim($_GET['wd']) === '') {
            http_response_code(400);
            exit;
        }

        // 2. Referer 校验 - 仅对有关键词的搜索请求进行校验
        // 无关键词时允许直接访问搜索页（显示热门搜索等）
        if (!empty($keyword)) {
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            if (empty($referer)) {
                http_response_code(403);
                exit;
            }
            
            // 获取站点域名（从配置或常量）
            $siteUrl = $this->data['siteUrl'] ?? SITE_URL;
            $siteHost = parse_url($siteUrl, PHP_URL_HOST);
            $refererHost = parse_url($referer, PHP_URL_HOST);
            
            if ($refererHost !== $siteHost) {
                http_response_code(403);
                exit;
            }
        }
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
        
        // 搜索防护检查（空查询拦截、Referer校验）
        $this->searchProtection($keyword);
        
        // 关键词长度限制 - 防止超短/超长关键词打数据库 LIKE 查询
        if (!empty($keyword)) {
            $len = mb_strlen($keyword, 'UTF-8');
            if ($len < 1 || $len > 100) {
                http_response_code(400);
                exit;
            }
        }
        
        // 支持排序参数（用于"查看更多"功能）
        $order = $this->get('order', '');
        
        // 对搜索请求进行速率限制
        if (!empty($keyword)) {
            $this->requireRateLimit('search', 30, 60); // 1分钟内最多30次搜索
        }
        
        if (empty($keyword) && empty($order)) {
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
        } elseif (empty($keyword) && $order === 'time') {
            // 按时间排序显示最新视频（"查看更多"功能）
            $result = $this->vodModel->getListPaged($page, PAGE_SIZE, 'time');
            
            $this->assign('vodList', $result['list']);
            $this->assign('keyword', '');
            $this->assign('total', $result['total']);
            $this->assign('page', $result['page']);
            $this->assign('totalPages', $result['totalPages']);
            $this->assign('baseUrl', '/search?order=time');
            $this->assign('hotKeywords', []);
            $this->assign('recentKeywords', []);
            $this->assign('pageTitle', '最新更新');
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
        $pageTitle = $this->data['pageTitle'] ?? ($keyword ? $keyword . ' - ' : '') . '搜索';
        $this->assign('title', $pageTitle . ' - ' . SITE_NAME);
        $this->assign('noindex', true);
        
        $this->render('search/index');
    }

    /**
     * 搜索建议API (AJAX)
     */
    public function suggest(): void
    {
        // Referer 校验 - 阻止外站直接调用
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (empty($referer)) {
            http_response_code(403);
            exit;
        }
        
        $siteUrl = $this->data['siteUrl'] ?? SITE_URL;
        $siteHost = parse_url($siteUrl, PHP_URL_HOST);
        $refererHost = parse_url($referer, PHP_URL_HOST);
        
        if ($refererHost !== $siteHost) {
            http_response_code(403);
            exit;
        }
        
        // 搜索建议也需要速率限制
        $this->requireRateLimit('suggest', 60, 60); // 1分钟内最多60次建议请求
        
        $query = trim($this->get('q', ''));
        
        // 关键词长度限制
        $len = mb_strlen($query, 'UTF-8');
        if ($len < 2 || $len > 100) {
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
