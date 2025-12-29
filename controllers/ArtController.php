<?php
/**
 * 文章控制器
 * Powered by https://xpornkit.com
 */

class ArtController extends BaseController
{
    private XpkArt $artModel;

    public function __construct()
    {
        parent::__construct();
        $this->artModel = new XpkArt();
    }

    /**
     * 文章列表
     */
    public function index(int $page = 1): void
    {
        $result = $this->artModel->paginate($page, PAGE_SIZE, ['art_status' => 1], 'art_time DESC');
        
        $this->assign('artList', $result['list']);
        $this->assign('page', $result['page']);
        $this->assign('totalPages', $result['totalPages']);
        $this->assign('total', $result['total']);
        $this->assign('baseUrl', '/art');
        
        // SEO
        $this->assign('title', '文章资讯 - ' . $this->data['siteName']);
        
        $this->render('art/index');
    }

    /**
     * 文章详情
     */
    public function detail(int $id): void
    {
        $art = $this->artModel->getDetail($id);
        if (!$art) {
            $this->redirect(xpk_url('art'));
            return;
        }

        // 增加点击量
        $this->artModel->incHits($id);
        
        $this->assign('art', $art);
        
        // SEO
        $seoVars = ['name' => $art['art_name'], 'description' => mb_substr(strip_tags($art['art_content'] ?? ''), 0, 150)];
        $this->assign('title', $this->seoTitle('art_detail', $seoVars));
        $this->assign('keywords', $art['art_name']);
        $this->assign('description', $seoVars['description']);
        
        $this->render('art/detail');
    }

    /**
     * 通过 slug 访问文章详情
     */
    public function detailBySlug(string $slug): void
    {
        $art = $this->artModel->findBySlug($slug);
        if (!$art) {
            $this->redirect(xpk_url('art'));
            return;
        }
        $this->detail($art['art_id']);
    }
}
