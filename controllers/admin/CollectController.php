<?php
/**
 * 后台采集管理控制器
 * Powered by https://xpornkit.com
 */

class AdminCollectController extends AdminBaseController
{
    private XpkCollect $collectModel;
    private XpkVod $vodModel;
    private XpkType $typeModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Collect.php';
        $this->collectModel = new XpkCollect();
        $this->vodModel = new XpkVod();
        $this->typeModel = new XpkType();
    }

    /**
     * 采集站列表
     */
    public function index(): void
    {
        $collects = $this->collectModel->getAll();
        $this->assign('collects', $collects);
        $this->assign('flash', $this->getFlash());
        $this->render('collect/index', '采集管理');
    }

    /**
     * 添加采集站
     */
    public function add(): void
    {
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('collect/form', '添加采集站');
    }

    /**
     * 处理添加
     */
    public function doAdd(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = [
            'collect_name' => trim($this->post('collect_name', '')),
            'collect_api' => trim($this->post('collect_api', '')),
            'collect_type' => trim($this->post('collect_type', 'json')),
            'collect_status' => (int)$this->post('collect_status', 1),
            'collect_filter' => trim($this->post('collect_filter', '')),
            'collect_param' => trim($this->post('collect_param', ''))
        ];

        if (empty($data['collect_name']) || empty($data['collect_api'])) {
            $this->error('名称和API地址不能为空');
        }

        $this->collectModel->insert($data);
        $this->log('添加', '采集', $data['collect_name']);
        $this->flash('success', '添加成功');
        $this->redirect('/admin.php/collect');
    }

    /**
     * 编辑采集站
     */
    public function edit(int $id): void
    {
        $collect = $this->collectModel->find($id);
        if (!$collect) {
            $this->flash('error', '采集站不存在');
            $this->redirect('/admin.php/collect');
        }

        $this->assign('collect', $collect);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('collect/form', '编辑采集站');
    }

    /**
     * 处理编辑
     */
    public function doEdit(int $id): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $data = [
            'collect_name' => trim($this->post('collect_name', '')),
            'collect_api' => trim($this->post('collect_api', '')),
            'collect_type' => trim($this->post('collect_type', 'json')),
            'collect_status' => (int)$this->post('collect_status', 1),
            'collect_filter' => trim($this->post('collect_filter', '')),
            'collect_param' => trim($this->post('collect_param', ''))
        ];

        $this->collectModel->update($id, $data);
        $this->log('编辑', '采集', "ID:{$id} {$data['collect_name']}");
        $this->flash('success', '保存成功');
        $this->redirect('/admin.php/collect');
    }

    /**
     * 删除采集站
     */
    public function delete(): void
    {
        $id = (int)$this->post('id', 0);
        if ($id > 0) {
            $this->collectModel->delete($id);
            $this->log('删除', '采集', "ID:{$id}");
            $this->success('删除成功');
        } else {
            $this->error('参数错误');
        }
    }

    /**
     * 分类绑定页面
     */
    public function bind(int $id): void
    {
        $collect = $this->collectModel->find($id);
        if (!$collect) {
            $this->flash('error', '采集站不存在');
            $this->redirect('/admin.php/collect');
        }

        // 获取远程分类
        $remoteCategories = $this->collectModel->getCategories($collect);
        if ($remoteCategories === null) {
            $this->flash('error', '获取远程分类失败，请检查API地址');
            $this->redirect('/admin.php/collect');
        }

        // 获取本地分类
        $localTypes = $this->typeModel->getTree();

        // 获取已绑定关系
        $binds = [];
        if (!empty($collect['collect_bind'])) {
            $binds = json_decode($collect['collect_bind'], true) ?: [];
        }

        $this->assign('collect', $collect);
        $this->assign('remoteCategories', $remoteCategories);
        $this->assign('localTypes', $localTypes);
        $this->assign('binds', $binds);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('collect/bind', '分类绑定');
    }

    /**
     * 同步远程分类到本地 (AJAX)
     */
    public function syncCategories(): void
    {
        $id = (int)$this->post('id', 0);
        
        $collect = $this->collectModel->find($id);
        if (!$collect) {
            $this->error('采集站不存在');
        }

        // 获取远程分类
        $remoteCategories = $this->collectModel->getCategories($collect);
        if (!$remoteCategories) {
            $this->error('获取远程分类失败');
        }

        // 获取本地已有分类名称
        $localTypes = $this->typeModel->getAll();
        $localNames = array_column($localTypes, 'type_name');

        $added = 0;
        $skipped = 0;
        $bindData = [];

        // 先处理一级分类
        $parentMap = []; // 远程父ID => 本地ID
        foreach ($remoteCategories as $cat) {
            $pid = $cat['pid'] ?? 0;
            if ($pid != 0) continue; // 跳过子分类，后面处理
            
            $name = trim($cat['name']);
            if (in_array($name, $localNames)) {
                // 已存在，找到对应ID用于绑定
                foreach ($localTypes as $lt) {
                    if ($lt['type_name'] === $name) {
                        $bindData[$cat['id']] = $lt['type_id'];
                        $parentMap[$cat['id']] = $lt['type_id'];
                        break;
                    }
                }
                $skipped++;
                continue;
            }

            // 生成slug
            require_once CORE_PATH . 'Slug.php';
            $slug = xpk_slug_unique($name, 'type', 'type_slug');

            // 新增分类
            $newId = $this->typeModel->insert([
                'type_pid' => 0,
                'type_name' => $name,
                'type_slug' => $slug,
                'type_sort' => $cat['id'],
                'type_status' => 1
            ]);

            if ($newId) {
                $bindData[$cat['id']] = $newId;
                $parentMap[$cat['id']] = $newId;
                $localNames[] = $name;
                $added++;
            }
        }

        // 再处理子分类
        foreach ($remoteCategories as $cat) {
            $pid = $cat['pid'] ?? 0;
            if ($pid == 0) continue; // 跳过一级分类
            
            $name = trim($cat['name']);
            $localPid = $parentMap[$pid] ?? 0;
            
            // 检查是否已存在同名子分类
            $exists = false;
            foreach ($localTypes as $lt) {
                if ($lt['type_name'] === $name && $lt['type_pid'] == $localPid) {
                    $bindData[$cat['id']] = $lt['type_id'];
                    $exists = true;
                    $skipped++;
                    break;
                }
            }
            if ($exists) continue;

            // 生成slug
            require_once CORE_PATH . 'Slug.php';
            $slug = xpk_slug_unique($name, 'type', 'type_slug');

            // 新增子分类
            $newId = $this->typeModel->insert([
                'type_pid' => $localPid,
                'type_name' => $name,
                'type_slug' => $slug,
                'type_sort' => $cat['id'],
                'type_status' => 1
            ]);

            if ($newId) {
                $bindData[$cat['id']] = $newId;
                $added++;
            }
        }

        // 自动保存绑定关系
        if (!empty($bindData)) {
            $this->collectModel->update($id, [
                'collect_bind' => json_encode($bindData)
            ]);
        }

        $this->log('同步分类', '采集', "ID:{$id} 新增{$added}个，跳过{$skipped}个");
        $this->success("同步完成！新增 {$added} 个分类，跳过 {$skipped} 个已存在");
    }

    /**
     * 保存分类绑定
     */
    public function saveBind(int $id): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $binds = $this->post('bind', []);
        $bindData = [];
        
        foreach ($binds as $remoteId => $localId) {
            if ($localId > 0) {
                $bindData[$remoteId] = (int)$localId;
            }
        }

        $this->collectModel->update($id, [
            'collect_bind' => json_encode($bindData)
        ]);

        $this->flash('success', '绑定保存成功');
        $this->redirect('/admin.php/collect');
    }

    /**
     * 采集页面
     */
    public function run(int $id): void
    {
        $collect = $this->collectModel->find($id);
        if (!$collect) {
            $this->flash('error', '采集站不存在');
            $this->redirect('/admin.php/collect');
        }

        // 获取远程分类
        $remoteCategories = $this->collectModel->getCategories($collect);

        $this->assign('collect', $collect);
        $this->assign('remoteCategories', $remoteCategories ?: []);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('collect/run', '执行采集');
    }

    /**
     * 执行采集 (AJAX)
     */
    public function doCollect(): void
    {
        // 单次请求最多执行120秒
        set_time_limit(120);
        
        $id = (int)$this->post('id', 0);
        $page = (int)$this->post('page', 1);
        $typeId = (int)$this->post('type_id', 0) ?: null;
        $hours = $this->post('hours', '') ?: null;
        $mode = $this->post('mode', 'add'); // add=只采新 update=只更新 all=全部
        $downloadPic = (int)$this->post('download_pic', 0); // 是否下载图片

        $collect = $this->collectModel->find($id);
        if (!$collect) {
            $this->error('采集站不存在');
        }

        // 获取绑定关系
        $binds = [];
        if (!empty($collect['collect_bind'])) {
            $binds = json_decode($collect['collect_bind'], true) ?: [];
        }

        // 检查是否有绑定
        if (empty($binds)) {
            $this->error('请先绑定分类后再采集');
        }

        // 获取视频列表
        $listResult = $this->collectModel->getVideoList($collect, $page, $typeId, $hours);
        if (!$listResult || empty($listResult['list'])) {
            $this->success('采集完成', ['done' => true, 'page' => $page, 'added' => 0, 'updated' => 0]);
        }

        // 获取视频ID
        $ids = array_column($listResult['list'], 'vod_id');
        
        // 获取详情
        $videos = $this->collectModel->getVideoDetail($collect, $ids);
        if (!$videos) {
            $this->error('获取视频详情失败');
        }

        $added = 0;
        $updated = 0;
        $db = XpkDatabase::getInstance();

        foreach ($videos as $video) {
            // 检查分类绑定
            $localTypeId = $binds[$video['type_id']] ?? 0;
            if (!$localTypeId) continue;

            // 过滤关键词
            if (!empty($collect['collect_filter'])) {
                $filters = explode(',', $collect['collect_filter']);
                $skip = false;
                foreach ($filters as $filter) {
                    if (stripos($video['vod_name'], trim($filter)) !== false) {
                        $skip = true;
                        break;
                    }
                }
                if ($skip) continue;
            }

            // 下载图片（如果开启）
            $picUrl = $video['vod_pic'];
            if ($downloadPic && !empty($picUrl) && filter_var($picUrl, FILTER_VALIDATE_URL)) {
                $localPic = $this->downloadImage($picUrl);
                if ($localPic) {
                    $picUrl = $localPic;
                }
            }

            // 检查是否已存在
            $exists = $db->queryOne(
                "SELECT vod_id FROM " . DB_PREFIX . "vod WHERE vod_name = ? LIMIT 1",
                [$video['vod_name']]
            );

            if ($exists) {
                if ($mode === 'add') continue;
                
                // 更新（包含简介内容）
                $updateSql = "UPDATE " . DB_PREFIX . "vod SET 
                    vod_remarks = ?, vod_content = ?, vod_play_from = ?, vod_play_url = ?, vod_time = ?
                 WHERE vod_id = ?";
                $updateData = [
                    $video['vod_remarks'],
                    $video['vod_content'],
                    $video['vod_play_from'],
                    $video['vod_play_url'],
                    time(),
                    $exists['vod_id']
                ];
                
                // 如果下载了新图片也更新
                if ($downloadPic && $picUrl !== $video['vod_pic']) {
                    $updateSql = "UPDATE " . DB_PREFIX . "vod SET 
                        vod_pic = ?, vod_remarks = ?, vod_content = ?, vod_play_from = ?, vod_play_url = ?, vod_time = ?
                     WHERE vod_id = ?";
                    array_unshift($updateData, $picUrl);
                }
                
                $db->execute($updateSql, $updateData);
                $updated++;
            } else {
                if ($mode === 'update') continue;
                
                // 自动生成 slug
                require_once CORE_PATH . 'Slug.php';
                $vodSlug = xpk_slug_unique($video['vod_name'], 'vod', 'vod_slug');
                
                // 新增
                $db->execute(
                    "INSERT INTO " . DB_PREFIX . "vod 
                        (vod_type_id, vod_name, vod_sub, vod_en, vod_slug, vod_pic, vod_actor, vod_director, 
                         vod_year, vod_area, vod_lang, vod_score, vod_remarks, vod_content, 
                         vod_play_from, vod_play_url, vod_status, vod_time, vod_time_add)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)",
                    [
                        $localTypeId,
                        $video['vod_name'],
                        $video['vod_sub'],
                        $video['vod_en'],
                        $vodSlug,
                        $picUrl,
                        $video['vod_actor'],
                        $video['vod_director'],
                        $video['vod_year'],
                        $video['vod_area'],
                        $video['vod_lang'],
                        $video['vod_score'],
                        $video['vod_remarks'],
                        $video['vod_content'],
                        $video['vod_play_from'],
                        $video['vod_play_url'],
                        time(),
                        time()
                    ]
                );
                $added++;
            }
        }

        $done = $page >= $listResult['pagecount'];
        
        $this->success('采集中...', [
            'done' => $done,
            'page' => $page,
            'pagecount' => $listResult['pagecount'],
            'added' => $added,
            'updated' => $updated
        ]);
    }

    /**
     * 下载远程图片到存储
     */
    private function downloadImage(string $url): ?string
    {
        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ]);
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($httpCode !== 200 || empty($content)) {
                return null;
            }

            // 检查是否是图片
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $mimeType = explode(';', $contentType)[0];
            if (!in_array($mimeType, $allowedTypes)) {
                return null;
            }

            // 确定扩展名
            $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            $ext = $extMap[$mimeType] ?? 'jpg';

            // 生成路径：covers/年/月/日/hash.ext
            $remotePath = 'covers/' . date('Y/m/d/') . md5($url) . '.' . $ext;

            // 使用存储类上传
            require_once CORE_PATH . 'Storage.php';
            $storage = xpk_storage();
            return $storage->uploadContent($content, $remotePath);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * 测试API
     */
    public function test(): void
    {
        $api = trim($this->post('api', ''));
        if (empty($api)) {
            $this->error('API地址不能为空');
        }

        $collect = ['collect_api' => $api];
        $categories = $this->collectModel->getCategories($collect);

        if ($categories === null) {
            $this->error('连接失败，请检查API地址');
        }

        $this->success('连接成功', ['categories' => $categories]);
    }
}
