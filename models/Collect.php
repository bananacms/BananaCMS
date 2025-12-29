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
            // 支持多种XML格式
            if (isset($xml->class->ty)) {
                // 格式1: <class><ty id="1" pid="0">电影</ty></class>
                foreach ($xml->class->ty as $ty) {
                    $categories[] = [
                        'id' => (int)$ty['id'],
                        'name' => (string)$ty,
                        'pid' => (int)($ty['pid'] ?? 0)
                    ];
                }
            } elseif (isset($xml->class)) {
                // 格式2: <class><type_id>1</type_id><type_name>电影</type_name></class>
                foreach ($xml->class as $class) {
                    if (isset($class->type_id)) {
                        $categories[] = [
                            'id' => (int)$class->type_id,
                            'name' => (string)($class->type_name ?? ''),
                            'pid' => (int)($class->type_pid ?? 0)
                        ];
                    }
                }
            } elseif (isset($xml->type)) {
                // 格式3: <type><id>1</id><name>电影</name></type>
                foreach ($xml->type as $type) {
                    $categories[] = [
                        'id' => (int)($type->id ?? $type['id'] ?? 0),
                        'name' => (string)($type->name ?? $type),
                        'pid' => (int)($type->pid ?? $type['pid'] ?? 0)
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
            $pageCount = (int)($xml->list['pagecount'] ?? $xml['pagecount'] ?? 1);
            
            // 支持多种XML格式的视频列表
            $videoNodes = null;
            if (isset($xml->list->video)) {
                // 格式1: <list><video><id>1</id><name>视频名</name></video></list>
                $videoNodes = $xml->list->video;
            } elseif (isset($xml->video)) {
                // 格式2: <video><id>1</id><name>视频名</name></video>
                $videoNodes = $xml->video;
            } elseif (isset($xml->list->vod)) {
                // 格式3: <list><vod><vod_id>1</vod_id><vod_name>视频名</vod_name></vod>
                $videoNodes = $xml->list->vod;
            }
            
            if ($videoNodes) {
                foreach ($videoNodes as $video) {
                    $videos[] = [
                        'vod_id' => (int)($video->id ?? $video->vod_id ?? 0),
                        'vod_name' => (string)($video->name ?? $video->vod_name ?? ''),
                        'type_id' => (int)($video->tid ?? $video->type_id ?? 0),
                        'type_name' => (string)($video->type ?? $video->type_name ?? ''),
                        'vod_time' => (string)($video->last ?? $video->vod_time ?? $video->time ?? '')
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
            
            // 支持多种XML格式的视频详情
            $videoNodes = null;
            if (isset($xml->list->video)) {
                // 格式1: <list><video>...</video></list>
                $videoNodes = $xml->list->video;
            } elseif (isset($xml->video)) {
                // 格式2: <video>...</video>
                $videoNodes = $xml->video;
            } elseif (isset($xml->list->vod)) {
                // 格式3: <list><vod>...</vod></list>
                $videoNodes = $xml->list->vod;
            }
            
            if ($videoNodes) {
                foreach ($videoNodes as $video) {
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
            'vod_letter' => $this->getFirstLetter($item['vod_name'] ?? ''),
            'vod_tag' => $item['vod_tag'] ?? '',
            'vod_class' => $item['vod_class'] ?? '',
            'vod_isend' => (int)($item['vod_isend'] ?? 0),
            'vod_serial' => $item['vod_serial'] ?? '',
            'vod_total' => (int)($item['vod_total'] ?? 0),
            'vod_weekday' => $item['vod_weekday'] ?? '',
            'vod_state' => $item['vod_state'] ?? '',
            'vod_version' => $item['vod_version'] ?? '',
            'vod_score' => $item['vod_score'] ?? 0,
            'vod_remarks' => $item['vod_remarks'] ?? '',
            'vod_content' => strip_tags($item['vod_content'] ?? ''),
            'vod_play_from' => $item['vod_play_from'] ?? '',
            'vod_play_url' => $item['vod_play_url'] ?? '',
            'vod_down_from' => $item['vod_down_from'] ?? '',
            'vod_down_url' => $item['vod_down_url'] ?? '',
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
        $downFrom = [];
        $downUrl = [];
        
        // 解析播放地址 - 支持多种XML格式
        if (isset($video->dl->dd)) {
            // 格式1: <dl><dd flag="m3u8">播放地址</dd></dl>
            foreach ($video->dl->dd as $dd) {
                $flag = (string)$dd['flag'];
                $url = (string)$dd;
                if (stripos($flag, 'down') !== false || stripos($flag, '下载') !== false) {
                    $downFrom[] = $flag;
                    $downUrl[] = $url;
                } else {
                    $playFrom[] = $flag;
                    $playUrl[] = $url;
                }
            }
        } elseif (isset($video->vod_play_from) && isset($video->vod_play_url)) {
            // 格式2: <vod_play_from>m3u8</vod_play_from><vod_play_url>地址</vod_play_url>
            $playFrom[] = (string)$video->vod_play_from;
            $playUrl[] = (string)$video->vod_play_url;
            
            if (isset($video->vod_down_from) && isset($video->vod_down_url)) {
                $downFrom[] = (string)$video->vod_down_from;
                $downUrl[] = (string)$video->vod_down_url;
            }
        } elseif (isset($video->play)) {
            // 格式3: <play><from>m3u8</from><url>地址</url></play>
            foreach ($video->play as $play) {
                $flag = (string)($play->from ?? $play['from'] ?? '');
                $url = (string)($play->url ?? $play);
                if (!empty($flag) && !empty($url)) {
                    $playFrom[] = $flag;
                    $playUrl[] = $url;
                }
            }
        }
        
        return [
            'vod_id' => (int)($video->id ?? $video->vod_id ?? 0),
            'vod_name' => (string)($video->name ?? $video->vod_name ?? ''),
            'vod_sub' => (string)($video->subname ?? $video->vod_sub ?? ''),
            'vod_en' => (string)($video->en ?? $video->vod_en ?? ''),
            'vod_pic' => (string)($video->pic ?? $video->vod_pic ?? ''),
            'vod_actor' => (string)($video->actor ?? $video->vod_actor ?? ''),
            'vod_director' => (string)($video->director ?? $video->vod_director ?? ''),
            'vod_year' => (string)($video->year ?? $video->vod_year ?? ''),
            'vod_area' => (string)($video->area ?? $video->vod_area ?? ''),
            'vod_lang' => (string)($video->lang ?? $video->vod_lang ?? ''),
            'vod_letter' => $this->getFirstLetter((string)($video->name ?? $video->vod_name ?? '')),
            'vod_tag' => (string)($video->tag ?? $video->vod_tag ?? ''),
            'vod_class' => (string)($video->class ?? $video->vod_class ?? ''),
            'vod_isend' => (int)($video->isend ?? $video->vod_isend ?? 0),
            'vod_serial' => (string)($video->serial ?? $video->vod_serial ?? ''),
            'vod_total' => (int)($video->total ?? $video->vod_total ?? 0),
            'vod_weekday' => (string)($video->weekday ?? $video->vod_weekday ?? ''),
            'vod_state' => (string)($video->state ?? $video->vod_state ?? ''),
            'vod_version' => (string)($video->version ?? $video->vod_version ?? ''),
            'vod_score' => (float)($video->score ?? $video->vod_score ?? 0),
            'vod_remarks' => (string)($video->note ?? $video->remarks ?? $video->vod_remarks ?? ''),
            'vod_content' => strip_tags((string)($video->des ?? $video->content ?? $video->vod_content ?? '')),
            'vod_play_from' => implode('$$$', $playFrom),
            'vod_play_url' => implode('$$$', $playUrl),
            'vod_down_from' => implode('$$$', $downFrom),
            'vod_down_url' => implode('$$$', $downUrl),
            'type_id' => (int)($video->tid ?? $video->type_id ?? 0),
            'type_name' => (string)($video->type ?? $video->type_name ?? '')
        ];
    }

    /**
     * 获取首字母
     */
    private function getFirstLetter(string $name): string
    {
        if (empty($name)) return '';
        
        $firstChar = mb_substr($name, 0, 1, 'UTF-8');
        
        // 如果是数字
        if (is_numeric($firstChar)) {
            return '0-9';
        }
        
        // 如果是英文字母
        if (preg_match('/^[A-Za-z]$/', $firstChar)) {
            return strtoupper($firstChar);
        }
        
        // 中文转拼音首字母
        require_once CORE_PATH . 'Pinyin.php';
        $pinyin = new XpkPinyin();
        $letter = $pinyin->getFirstLetter($firstChar);
        return strtoupper($letter);
    }
}
