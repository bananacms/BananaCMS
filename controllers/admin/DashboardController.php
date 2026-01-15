<?php
/**
 * 后台仪表盘控制器
 * Powered by https://xpornkit.com
 */

class AdminDashboardController extends AdminBaseController
{
    public function index(): void
    {
        $db = XpkDatabase::getInstance();

        // 统计数据
        $stats = [
            'vod_count' => $db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "vod")['cnt'] ?? 0,
            'vod_today' => $db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "vod WHERE vod_time_add >= ?", [strtotime('today')])['cnt'] ?? 0,
            'type_count' => $db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "type")['cnt'] ?? 0,
            'actor_count' => $db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "actor")['cnt'] ?? 0,
            'art_count' => $db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "art")['cnt'] ?? 0,
            'user_count' => $db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "user")['cnt'] ?? 0,
        ];

        // 最新视频
        $latestVods = $db->query(
            "SELECT v.*, t.type_name FROM " . DB_PREFIX . "vod v 
             LEFT JOIN " . DB_PREFIX . "type t ON v.vod_type_id = t.type_id 
             ORDER BY v.vod_time_add DESC LIMIT 10"
        );

        // 热门视频
        $hotVods = $db->query(
            "SELECT vod_id, vod_name, vod_hits FROM " . DB_PREFIX . "vod 
             ORDER BY vod_hits DESC LIMIT 10"
        );

        $this->assign('stats', $stats);
        $this->assign('latestVods', $latestVods);
        $this->assign('hotVods', $hotVods);
        $this->assign('flash', $this->getFlash());

        $this->render('dashboard', '仪表盘');
    }
}
