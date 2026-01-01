<?php
/**
 * 视频控制器
 * Powered by https://xpornkit.com
 */

class VodController extends BaseController
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
     * 分类列表
     */
    public function type(int $typeId, int $page = 1): void
    {
        $type = $this->typeModel->getById($typeId);
        if (!$type) {
            $this->redirect(xpk_url());
            return;
        }

        $result = $this->vodModel->getByType($typeId, $page, PAGE_SIZE);
        
        $this->assign('type', $type);
        $this->assign('vodList', $result['list']);
        $this->assign('page', $result['page']);
        $this->assign('totalPages', $result['totalPages']);
        $this->assign('total', $result['total']);
        $this->assign('baseUrl', '/vod/type/' . $typeId);
        
        // SEO
        $this->assign('title', $type['type_name'] . ' - ' . SITE_NAME);
        
        $this->render('vod/type');
    }

    /**
     * 热门视频列表
     */
    public function hot(int $page = 1): void
    {
        $result = $this->vodModel->getHotPaged($page, PAGE_SIZE);
        
        $this->assign('vodList', $result['list']);
        $this->assign('page', $result['page']);
        $this->assign('totalPages', $result['totalPages']);
        $this->assign('total', $result['total']);
        $this->assign('baseUrl', '/hot');
        
        // SEO
        $this->assign('title', '热门视频 - ' . SITE_NAME);
        
        $this->render('vod/hot');
    }


    /**
     * 按一级分类筛选
     */
    public function topType(int $topTypeId, int $page = 1): void
    {
        $type = $this->typeModel->getById($topTypeId);
        if (!$type || $type['type_pid'] != 0) {
            $this->redirect(xpk_url());
            return;
        }

        $result = $this->vodModel->getByTopLevelType($topTypeId, $page, PAGE_SIZE);
        
        $this->assign('type', $type);
        $this->assign('vodList', $result['list']);
        $this->assign('page', $result['page']);
        $this->assign('totalPages', $result['totalPages']);
        $this->assign('total', $result['total']);
        $this->assign('baseUrl', '/vod/top-type/' . $topTypeId);
        
        // SEO
        $this->assign('title', $type['type_name'] . '大全 - ' . SITE_NAME);
        
        $this->render('vod/type');
    }

    /**
     * 视频详情
     */
    public function detail(int $id): void
    {
        $vod = $this->vodModel->getDetailWithPlayList($id);
        if (!$vod) {
            $this->redirect(xpk_url());
            return;
        }

        // 增加点击量
        $this->vodModel->incHits($id);
        
        // 记录视频访问统计
        try {
            $stats = new XpkStats();
            $stats->log('vod', $id);
        } catch (Exception $e) {}
        
        // 相关视频
        $relatedList = $this->vodModel->getRelated($vod['vod_type_id'], $id, 6);
        
        $this->assign('vod', $vod);
        $this->assign('relatedList', $relatedList);
        
        // 兼容不同模板的播放列表变量名
        $playUrls = [];
        if (!empty($vod['play_list'])) {
            foreach ($vod['play_list'] as $source) {
                $playUrls[] = [
                    'name' => $source['from'],
                    'urls' => $source['episodes']
                ];
            }
        }
        $this->assign('playUrls', $playUrls);
        $this->assign('playList', $playUrls); // Netflix 模板用这个
        
        // SEO
        $seoVars = ['name' => $vod['vod_name'], 'actor' => $vod['vod_actor'] ?? '', 'type' => $vod['type_name'] ?? '', 'year' => $vod['vod_year'] ?? '', 'area' => $vod['vod_area'] ?? ''];
        $this->assign('title', $this->seoTitle('vod_detail', $seoVars));
        $this->assign('keywords', $this->seoKeywords('vod_detail', $seoVars));
        $this->assign('description', $this->seoDescription('vod_detail', array_merge($seoVars, ['description' => mb_substr(strip_tags($vod['vod_content'] ?? ''), 0, 150)])));
        
        $this->render('vod/detail');
    }


    /**
     * 视频播放
     */
    public function play(int $id, int $sid = 1, int $nid = 1): void
    {
        $vod = $this->vodModel->getDetail($id);
        if (!$vod) {
            $this->redirect(xpk_url());
            return;
        }

        // 增加点击量
        $this->vodModel->incHits($id);
        
        // 记录播放统计
        try {
            $stats = new XpkStats();
            $stats->log('play', $id);
        } catch (Exception $e) {}
        
        // 解析播放地址
        $playFroms = explode('$$$', $vod['vod_play_from'] ?? '');
        $rawPlayUrls = $this->parsePlayUrl($vod['vod_play_url'] ?? '');
        
        // 构建兼容模板的 playUrls 格式: [{name, urls: [{name, url}]}]
        $playUrls = [];
        foreach ($playFroms as $index => $from) {
            $from = trim($from);
            if (empty($from)) continue;
            $playUrls[] = [
                'name' => $from,
                'urls' => $rawPlayUrls[$index] ?? []
            ];
        }
        
        // 获取当前播放源和地址 (sid/nid 从 1 开始，数组从 0 开始)
        $currentFrom = $playFroms[$sid - 1] ?? '';
        $currentEpisodes = $rawPlayUrls[$sid - 1] ?? [];
        $currentUrl = $currentEpisodes[$nid - 1]['url'] ?? '';
        $currentEp = $currentEpisodes[$nid - 1] ?? ['name' => '播放', 'url' => ''];
        
        // 获取播放器配置
        $playerUrl = '';
        $playerInfo = null;
        $useBuiltinPlayer = false;
        
        if (!empty($currentFrom) && !empty($currentUrl)) {
            require_once MODEL_PATH . 'Player.php';
            $playerModel = new XpkPlayer();
            $playerInfo = $playerModel->findByCode($currentFrom);
            
            if ($playerInfo && !empty($playerInfo['player_parse'])) {
                $playerUrl = str_replace('{url}', urlencode($currentUrl), $playerInfo['player_parse']);
            } else {
                $useBuiltinPlayer = true;
                $playerUrl = '/static/player.html?url=' . urlencode($currentUrl);
                if (!empty($vod['vod_pic'])) {
                    $playerUrl .= '&pic=' . urlencode($vod['vod_pic']);
                }
                
                // 添加暂停广告参数
                require_once MODEL_PATH . 'Ad.php';
                $adModel = new XpkAd();
                $pauseAd = $adModel->getOne('play_pause');
                if ($pauseAd && $pauseAd['ad_type'] === 'image' && !empty($pauseAd['ad_image'])) {
                    $playerUrl .= '&pause_ad_image=' . urlencode($pauseAd['ad_image']);
                    $playerUrl .= '&pause_ad_link=' . urlencode($pauseAd['ad_link'] ?? '');
                    $playerUrl .= '&pause_ad_id=' . $pauseAd['ad_id'];
                }
            }
        }
        
        // 上一集/下一集 (nid 从 1 开始)
        $prevEpisode = $nid > 1 ? ['nid' => $nid - 1, 'name' => $currentEpisodes[$nid - 2]['name'] ?? ''] : null;
        $nextEpisode = isset($currentEpisodes[$nid]) ? ['nid' => $nid + 1, 'name' => $currentEpisodes[$nid]['name'] ?? ''] : null;
        
        // 当前播放源信息 (Netflix 模板用)
        $currentSource = [
            'name' => $currentFrom,
            'urls' => $currentEpisodes
        ];
        
        // 相关视频
        $relatedList = $this->vodModel->getRelated($vod['vod_type_id'] ?? 0, $id, 6);
        
        $this->assign('vod', $vod);
        $this->assign('playUrls', $playUrls);
        $this->assign('playList', $playUrls); // Netflix 模板用
        $this->assign('playFroms', $playFroms);
        $this->assign('sid', $sid);
        $this->assign('nid', $nid);
        $this->assign('currentSid', $sid);
        $this->assign('currentNid', $nid);
        $this->assign('currentUrl', $playerUrl);
        $this->assign('playerUrl', $playerUrl);
        $this->assign('playUrl', $playerUrl); // Netflix 模板用
        $this->assign('rawUrl', $currentUrl);
        $this->assign('currentFrom', $currentFrom);
        $this->assign('currentEp', $currentEp);
        $this->assign('currentEpisode', $currentEp); // Netflix 模板用
        $this->assign('currentSource', $currentSource); // Netflix 模板用
        $this->assign('prevEpisode', $prevEpisode);
        $this->assign('nextEpisode', $nextEpisode);
        $this->assign('playerInfo', $playerInfo);
        $this->assign('useBuiltinPlayer', $useBuiltinPlayer);
        $this->assign('relatedList', $relatedList);
        
        // SEO
        $this->assign('title', $vod['vod_name'] . ' 播放 - ' . $this->data['siteName']);
        
        $this->render('vod/play');
    }


    /**
     * 通过 slug 访问详情
     */
    public function detailBySlug(string $slug): void
    {
        $vod = $this->vodModel->findBySlug($slug);
        if (!$vod) {
            $this->redirect(xpk_url());
            return;
        }
        $this->detail($vod['vod_id']);
    }

    /**
     * 通过 slug 播放
     */
    public function playBySlug(string $slug, int $sid = 1, int $nid = 1): void
    {
        $vod = $this->vodModel->findBySlug($slug);
        if (!$vod) {
            $this->redirect(xpk_url());
            return;
        }
        $this->play($vod['vod_id'], $sid, $nid);
    }

    /**
     * 解析播放地址
     * 格式: 播放源1$$$播放源2  源内集数用#分隔  集名和地址用$分隔
     */
    private function parsePlayUrl(string $playUrl): array
    {
        if (empty($playUrl)) {
            return [];
        }
        
        $result = [];
        $groups = explode('$$$', $playUrl);
        
        foreach ($groups as $group) {
            if (empty(trim($group))) {
                continue;
            }
            $episodes = [];
            $items = explode('#', $group);
            foreach ($items as $item) {
                $item = trim($item);
                if (empty($item)) {
                    continue;
                }
                if (strpos($item, '$') !== false) {
                    list($name, $url) = explode('$', $item, 2);
                } else {
                    $name = '播放';
                    $url = $item;
                }
                $episodes[] = ['name' => $name, 'url' => $url];
            }
            if (!empty($episodes)) {
                $result[] = $episodes;
            }
        }
        
        return $result;
    }
}
