<?php
/**
 * 单页面控制器
 * Powered by https://xpornkit.com
 */

class PageController extends BaseController
{
    private XpkPage $pageModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Page.php';
        $this->pageModel = new XpkPage();
    }

    /**
     * 显示单页面
     */
    public function show(string $slug): void
    {
        $page = $this->pageModel->findBySlug($slug);
        
        if (!$page) {
            $this->error404('页面不存在');
            return;
        }
        
        $this->display('page/show', [
            'pageTitle' => $page['page_title'],
            'pageContent' => $page['page_content'],
            'slug' => $slug,
            'page' => $page
        ]);
    }
}
