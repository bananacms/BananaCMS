<?php
/**
 * 前台短视频/短剧控制器
 * Powered by https://xpornkit.com
 */

class ShortController extends BaseController
{
    private XpkShort $shortModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Short.php';
        $this->shortModel = new XpkShort();
    }

    /**
     * 短视频首页（滑动播放）
     */
    public function index(): void
    {
        $this->assign('pageTitle', '短视频');
        $this->display('short/index');
    }

    /**
     * 短剧列表
     */
    public function drama(int $page = 1): void
    {
        $result = $this->shortModel->getDramas($page, 20);

        $this->assign('list', $result['list']);
        $this->assign('total', $result['total']);
        $this->assign('page', $page);
        $this->assign('totalPages', ceil($result['total'] / 20));
        $this->assign('baseUrl', '/short/drama');
        $this->assign('pageTitle', '短剧');
        $this->display('short/drama');
    }

    /**
     * 短剧详情
     */
    public function detail(int $id): void
    {
        $short = $this->shortModel->getDetail($id);

        if (!$short || $short['short_status'] != 1) {
            $this->error404('短剧不存在');
        }

        $this->shortModel->incHits($id);

        $this->assign('short', $short);
        $this->assign('episodes', $short['episodes'] ?? []);
        $this->assign('pageTitle', $short['short_name']);
        $this->display('short/detail');
    }

    /**
     * 播放短剧剧集
     */
    public function play(int $id, int $ep = 1): void
    {
        $short = $this->shortModel->getDetail($id);

        if (!$short || $short['short_status'] != 1) {
            $this->error404('短剧不存在');
        }

        $episodes = $short['episodes'] ?? [];
        $currentEp = null;
        $epIndex = $ep - 1;

        if (isset($episodes[$epIndex])) {
            $currentEp = $episodes[$epIndex];
            $this->shortModel->incEpisodeHits($currentEp['episode_id']);
        }

        $this->assign('short', $short);
        $this->assign('episodes', $episodes);
        $this->assign('currentEp', $currentEp);
        $this->assign('epIndex', $epIndex);
        $this->assign('pageTitle', $short['short_name'] . ' 第' . $ep . '集');
        $this->display('short/play');
    }

    // ========== API 接口 ==========

    /**
     * 获取短视频列表（AJAX/滑动加载）
     */
    public function apiList(): void
    {
        $page = (int)($this->get('page', 1));
        $category = (int)($this->get('category', 0));

        $list = $this->shortModel->getVideos($page, 10, $category);

        $this->apiJson(0, 'success', [
            'list' => $list,
            'page' => $page,
            'has_more' => count($list) >= 10
        ]);
    }

    /**
     * 获取随机短视频
     */
    public function apiRandom(): void
    {
        $exclude = (int)($this->get('exclude', 0));
        $list = $this->shortModel->getRandom(5, $exclude);

        $this->apiJson(0, 'success', ['list' => $list]);
    }

    /**
     * 获取单个短视频详情
     */
    public function apiDetail(): void
    {
        $id = (int)($this->get('id', 0));

        if ($id <= 0) {
            $this->apiJson(1, '参数错误');
        }

        $short = $this->shortModel->getDetail($id);

        if (!$short || $short['short_status'] != 1) {
            $this->apiJson(1, '视频不存在');
        }

        $this->shortModel->incHits($id);

        $this->apiJson(0, 'success', $short);
    }

    /**
     * 点赞
     */
    public function apiLike(): void
    {
        $id = (int)($this->post('id', 0));

        if ($id <= 0) {
            $this->apiJson(1, '参数错误');
        }

        $likes = $this->shortModel->like($id);

        $this->apiJson(0, 'success', ['likes' => $likes]);
    }

    /**
     * API响应
     */
    private function apiJson(int $code, string $msg, array $data = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['code' => $code, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
