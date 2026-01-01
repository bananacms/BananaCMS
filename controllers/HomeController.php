<?php
/**
 * 首页控制器
 * Powered by https://xpornkit.com
 */

class HomeController extends BaseController
{
    private XpkVod $vodModel;
    private XpkType $typeModel;

    public function __construct()
    {
        parent::__construct();
        $this->vodModel = new XpkVod();
        $this->typeModel = new XpkType();
    }

    /**
     * 首页
     */
    public function index(int $page = 1): void
    {
        $cache = xpk_cache();
        
        // 轮播/推荐视频（缓存5分钟）
        $slideList = $cache->remember('home_slide', 300, fn() => $this->vodModel->getList(5, 'time'));
        $this->assign('slideList', $slideList);
        $this->assign('slides', $slideList); // 兼容别名
        
        // 最新视频（缓存5分钟）
        $newList = $cache->remember('home_new', 300, fn() => $this->vodModel->getList(12, 'time'));
        $this->assign('newList', $newList);
        $this->assign('recommendList', $newList); // 兼容别名
        
        // 热门视频（缓存10分钟）
        $hotList = $cache->remember('home_hot', 600, fn() => $this->vodModel->getHot(12));
        $this->assign('hotList', $hotList);
        
        // 分类及其视频（缓存10分钟）
        $typeModel = $this->typeModel;
        $vodModel = $this->vodModel;
        $typeVods = $cache->remember('home_type_vods', 600, function() use ($typeModel, $vodModel) {
            $types = $typeModel->getList(0);
            $result = [];
            foreach ($types as $type) {
                $result[$type['type_id']] = [
                    'type' => $type,
                    'vods' => $vodModel->getList(12, 'time', $type['type_id'])
                ];
            }
            return $result;
        });
        $this->assign('typeVods', $typeVods);
        
        // 构建 typeVideos 兼容格式（按分类ID直接映射视频列表）
        $typeVideos = [];
        foreach ($typeVods as $typeId => $item) {
            $typeVideos[$typeId] = $item['vods'];
        }
        $this->assign('typeVideos', $typeVideos);
        
        // SEO（使用数据库配置）
        $this->assign('title', $this->data['siteName']);
        $this->assign('keywords', $this->data['siteKeywords']);
        $this->assign('description', $this->data['siteDescription']);
        
        $this->render('index/index');
    }
}
