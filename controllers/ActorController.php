<?php
/**
 * 演员控制器
 * Powered by https://xpornkit.com
 */

class ActorController extends BaseController
{
    private XpkActor $actorModel;

    public function __construct()
    {
        parent::__construct();
        $this->actorModel = new XpkActor();
    }

    /**
     * 演员列表
     */
    public function index(int $page = 1): void
    {
        $result = $this->actorModel->paginate($page, PAGE_SIZE, ['actor_status' => 1], 'actor_hits DESC');
        
        $this->assign('actorList', $result['list']);
        $this->assign('page', $result['page']);
        $this->assign('totalPages', $result['totalPages']);
        $this->assign('total', $result['total']);
        
        // SEO
        $this->assign('title', '演员列表 - ' . $this->data['siteName']);
        
        $this->render('actor/index');
    }

    /**
     * 演员详情
     */
    public function detail(int $id): void
    {
        $actor = $this->actorModel->getDetail($id);
        if (!$actor) {
            $this->redirect(xpk_url('actor'));
            return;
        }

        // 增加点击量
        $this->actorModel->incHits($id);
        
        // 获取该演员的相关视频
        $vodModel = new XpkVod();
        $vodList = $vodModel->getByActor($actor['actor_name'], 12);
        
        $this->assign('actor', $actor);
        $this->assign('vodList', $vodList);
        
        // SEO
        $seoVars = ['name' => $actor['actor_name'], 'description' => mb_substr(strip_tags($actor['actor_content'] ?? ''), 0, 150)];
        $this->assign('title', $this->seoTitle('actor_detail', $seoVars));
        $this->assign('keywords', $actor['actor_name']);
        $this->assign('description', $seoVars['description']);
        
        $this->render('actor/detail');
    }

    /**
     * 通过 slug 访问演员详情
     */
    public function detailBySlug(string $slug): void
    {
        $actor = $this->actorModel->findBySlug($slug);
        if (!$actor) {
            $this->redirect(xpk_url('actor'));
            return;
        }
        $this->detail($actor['actor_id']);
    }
}
