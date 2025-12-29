<?php
/**
 * 采集站模型
 * Powered by https://xpornkit.com
 */

class XpkCollect extends XpkModel
{
    protected string $table = DB_PREFIX . 'collect';
    protected string $pk = 'collect_id';

    /**
     * 获取所有采集站
     */
    public function getAll(): array
    {
        return $this->db->query("SELECT * FROM {$this->table} ORDER BY collect_id ASC");
    }

    /**
     * 获取启用的采集站
     */
    public function getEnabled(): array
    {
        return $this->db->query("SELECT * FROM {$this->table} WHERE collect_status = 1 ORDER BY collect_id ASC");
    }

    /**
     * 请求API
     */
    public function request(string $url, int $timeout = 30): ?array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'BananaCMS/1.0',
            CURLOPT_HTTPHEADER => ['Accept: application/json, application/xml, text/xml']
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || empty($response)) {
            return null;
        }
        
        // 尝试JSON解析
        $json = json_decode($response, true);
        if ($json !== null) {
            return ['type' => 'json', 'data' => $json];
        }
        
        // 尝试XML解析
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response);
        if ($xml !== false) {
            return ['type' => 'xml', 'data' => $xml];
        }
        
        return null;
    }

    /**
     * 获取分类列表
     */
    public function getCategories(array $collect): ?array
    {
        $url = rtrim($collect['collect_api'], '/') . '?ac=list';
        $result = $this->request($url);
        
        if (!$result) return null;
        
        $categories = [];
        
        if ($result['type'] === 'json') {
            $data = $result['data'];
            if (isset($data['class'])) {
                foreach ($data['class'] as $cat) {
                    $categories[] = [
                        'id' => $cat['type_id'] ?? $cat['id'] ?? 0,
                        'name' => $cat['type_name'] ?? $cat['name'] ?? '',
                        'pid' => $cat['type_pid'] ?? $cat['pid'] ?? 0
                    ];
                }
            }
        } else {
            $xml = $result['data'];
            if (isset($xml->class->ty)) {
                foreach ($xml->class->ty as $ty) {
                    $categories[] = [
                        'id' => (int)$ty['id'],
                        'name' => (string)$ty,
                        'pid' => 0
                    ];
                }
            }
        }
        
        return $categories;
    }

    /**
     * 获取视频列表
     */
    public function getVideoList(array $collect, int $page = 1, ?int $typeId = null, ?string $hours = null): ?array
    {
        $url = rtrim($collect['collect_api'], '/') . '?ac=list&pg=' . $page;
        if ($typeId) $url .= '&t=' . $typeId;
        if ($hours) $url .= '&h=' . $hours;
        
        $result = $this->request($url);
        if (!$result) return null;
        
        $videos = [];
        $pageCount = 1;
        
        if ($result['type'] === 'json') {
            $data = $result['data'];
            $pageCount = $data['pagecount'] ?? $data['page_count'] ?? 1;
            $list = $data['list'] ?? [];
            foreach ($list as $item) {
                $videos[] = [
                    'vod_id' => $item['vod_id'] ?? 0,
                    'vod_name' => $item['vod_name'] ?? '',
                    'type_id' => $item['type_id'] ?? 0,
                    'type_name' => $item['type_name'] ?? '',
                    'vod_time' => $item['vod_time'] ?? ''
                ];
            }
        } else {
            $xml = $result['data'];
            $pageCount = (int)($xml->list['pagecount'] ?? 1);
            if (isset($xml->list->video)) {
                foreach ($xml->list->video as $video) {
                    $videos[] = [
                        'vod_id' => (int)$video->id,
                        'vod_name' => (string)$video->name,
                        'type_id' => (int)($video->tid ?? 0),
                        'type_name' => (string)($video->type ?? ''),
                        'vod_time' => (string)($video->last ?? '')
                    ];
                }
            }
        }
        
        return ['list' => $videos, 'pagecount' => $pageCount, 'page' => $page];
    }

    /**
     * 获取视频详情
     */
    public function getVideoDetail(array $collect, array $ids): ?array
    {
        $url = rtrim($collect['collect_api'], '/') . '?ac=detail&ids=' . implode(',', $ids);
        $result = $this->request($url, 60);
        if (!$result) return null;
        
        $videos = [];
        
        if ($result['type'] === 'json') {
            $list = $result['data']['list'] ?? [];
            foreach ($list as $item) {
                $videos[] = $this->parseJsonVideo($item);
            }
        } else {
            $xml = $result['data'];
            if (isset($xml->list->video)) {
                foreach ($xml->list->video as $video) {
                    $videos[] = $this->parseXmlVideo($video);
                }
            }
        }
        
        return $videos;
    }

    /**
     * 解析JSON视频数据
     */
    private function parseJsonVideo(array $item): array
    {
        return [
            'vod_id' => $item['vod_id'] ?? 0,
            'vod_name' => $item['vod_name'] ?? '',
            'vod_sub' => $item['vod_sub'] ?? '',
            'vod_en' => $item['vod_en'] ?? '',
            'vod_pic' => $item['vod_pic'] ?? '',
            'vod_actor' => $item['vod_actor'] ?? '',
            'vod_director' => $item['vod_director'] ?? '',
            'vod_year' => $item['vod_year'] ?? '',
            'vod_area' => $item['vod_area'] ?? '',
            'vod_lang' => $item['vod_lang'] ?? '',
            'vod_score' => $item['vod_score'] ?? 0,
            'vod_remarks' => $item['vod_remarks'] ?? '',
            'vod_content' => strip_tags($item['vod_content'] ?? ''),
            'vod_play_from' => $item['vod_play_from'] ?? '',
            'vod_play_url' => $item['vod_play_url'] ?? '',
            'type_id' => $item['type_id'] ?? 0,
            'type_name' => $item['type_name'] ?? ''
        ];
    }

    /**
     * 解析XML视频数据
     */
    private function parseXmlVideo($video): array
    {
        $playFrom = [];
        $playUrl = [];
        
        if (isset($video->dl->dd)) {
            foreach ($video->dl->dd as $dd) {
                $playFrom[] = (string)$dd['flag'];
                $playUrl[] = (string)$dd;
            }
        }
        
        return [
            'vod_id' => (int)$video->id,
            'vod_name' => (string)$video->name,
            'vod_sub' => (string)($video->subname ?? ''),
            'vod_en' => '',
            'vod_pic' => (string)$video->pic,
            'vod_actor' => (string)($video->actor ?? ''),
            'vod_director' => (string)($video->director ?? ''),
            'vod_year' => (string)($video->year ?? ''),
            'vod_area' => (string)($video->area ?? ''),
            'vod_lang' => (string)($video->lang ?? ''),
            'vod_score' => (float)($video->score ?? 0),
            'vod_remarks' => (string)($video->note ?? ''),
            'vod_content' => strip_tags((string)($video->des ?? '')),
            'vod_play_from' => implode('$$$', $playFrom),
            'vod_play_url' => implode('$$$', $playUrl),
            'type_id' => (int)($video->tid ?? 0),
            'type_name' => (string)($video->type ?? '')
        ];
    }
}
