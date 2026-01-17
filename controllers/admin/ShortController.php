<?php
/**
 * 后台短视频/短剧管理控制器
 * Powered by https://xpornkit.com
 */

class AdminShortController extends AdminBaseController
{
    private XpkShort $shortModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Short.php';
        $this->shortModel = new XpkShort();
    }

    /**
     * 列表
     */
    public function index(): void
    {
        $page = (int)($this->get('page', 1));
        $type = $this->get('type', '');
        $status = $this->get('status', '');

        $statusFilter = $status !== '' ? (int)$status : -1;
        $result = $this->shortModel->getList($type, $statusFilter, $page, 20);
        $stats = $this->shortModel->getStats();

        $this->assign('list', $result['list']);
        $this->assign('total', $result['total']);
        $this->assign('page', $page);
        $this->assign('pageSize', 20);
        $this->assign('totalPages', ceil($result['total'] / 20));
        $this->assign('type', $type);
        $this->assign('status', $status);
        $this->assign('stats', $stats);
        $this->assign('flash', $this->getFlash());

        $this->render('short/index', '短视频管理');
    }

    /**
     * 添加
     */
    public function add(): void
    {
        $type = $this->get('type', 'video');
        $this->assign('type', $type);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('short/form', $type === 'drama' ? '添加短剧' : '添加短视频');
    }

    /**
     * 处理添加
     */
    public function doAdd(): void
    {
        $this->requireCsrf();

        $data = $this->getFormData();

        if (empty($data['short_name'])) {
            $this->error('请填写标题');
        }

        $id = $this->shortModel->insert($data);

        if ($id) {
            $this->log('添加', '短视频', "ID:{$id} {$data['short_name']}");
            
            // 如果是短剧，跳转到剧集管理
            if ($data['short_type'] === 'drama') {
                $this->success('添加成功', ['url' => '/' . $this->adminEntry . '?s=short/episodes/' . $id]);
            } else {
                $this->success('添加成功', ['url' => '/' . $this->adminEntry . '?s=short']);
            }
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 编辑
     */
    public function edit(int $id): void
    {
        $short = $this->shortModel->find($id);
        if (!$short) {
            $this->flash('error', '记录不存在');
            $this->redirect('/' . $this->adminEntry . '?s=short');
        }

        $this->assign('short', $short);
        $this->assign('type', $short['short_type']);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('short/form', '编辑');
    }

    /**
     * 处理编辑
     */
    public function doEdit(int $id): void
    {
        $this->requireCsrf();

        $short = $this->shortModel->find($id);
        if (!$short) {
            $this->error('记录不存在');
        }

        $data = $this->getFormData();

        if (empty($data['short_name'])) {
            $this->error('请填写标题');
        }

        $this->shortModel->update($id, $data);
        $this->log('编辑', '短视频', "ID:{$id} {$data['short_name']}");

        $this->success('保存成功', ['url' => '/' . $this->adminEntry . '?s=short']);
    }

    /**
     * 删除
     */
    public function delete(): void
    {
        // CSRF 验证
        $this->requireCsrf();
        
        $id = (int)$this->post('id', 0);

        if ($id <= 0) {
            $this->error('参数错误');
        }

        $short = $this->shortModel->find($id);
        $this->shortModel->delete($id);
        $this->log('删除', '短视频', "ID:{$id} " . ($short['short_name'] ?? ''));
        $this->success('删除成功');
    }

    /**
     * 切换状态
     */
    public function toggle(): void
    {
        // CSRF 验证
        $this->requireCsrf();
        
        $id = (int)$this->post('id', 0);

        if ($id <= 0) {
            $this->error('参数错误');
        }

        $short = $this->shortModel->find($id);
        if (!$short) {
            $this->error('记录不存在');
        }

        $newStatus = $short['short_status'] ? 0 : 1;
        $this->shortModel->update($id, ['short_status' => $newStatus]);

        $this->success($newStatus ? '已上架' : '已下架');
    }

    // ========== 剧集管理 ==========

    /**
     * 剧集列表
     */
    public function episodes(int $id): void
    {
        $short = $this->shortModel->find($id);
        if (!$short || $short['short_type'] !== 'drama') {
            $this->flash('error', '短剧不存在');
            $this->redirect('/' . $this->adminEntry . '?s=short');
        }

        $episodes = $this->shortModel->getEpisodes($id);

        $this->assign('short', $short);
        $this->assign('episodes', $episodes);
        $this->assign('flash', $this->getFlash());
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('short/episodes', '剧集管理 - ' . $short['short_name']);
    }

    /**
     * 添加剧集
     */
    public function addEpisode(int $shortId): void
    {
        $short = $this->shortModel->find($shortId);
        if (!$short) {
            $this->error('短剧不存在');
        }

        $this->assign('short', $short);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('short/episode_form', '添加剧集');
    }

    /**
     * 处理添加剧集
     */
    public function doAddEpisode(int $shortId): void
    {
        $this->requireCsrf();

        $data = [
            'short_id' => $shortId,
            'episode_name' => trim($this->post('episode_name', '')),
            'episode_url' => trim($this->post('episode_url', '')),
            'episode_pic' => trim($this->post('episode_pic', '')),
            'episode_duration' => (int)$this->post('episode_duration', 0),
            'episode_sort' => (int)$this->post('episode_sort', 0),
            'episode_free' => (int)$this->post('episode_free', 1),
        ];

        if (empty($data['episode_url'])) {
            $this->error('请填写视频地址');
        }

        $id = $this->shortModel->addEpisode($data);
        $this->log('添加', '短剧剧集', "ID:{$id}");

        $this->success('添加成功', ['url' => '/' . $this->adminEntry . '?s=short/episodes/' . $shortId]);
    }

    /**
     * 编辑剧集
     */
    public function editEpisode(int $id): void
    {
        $episode = $this->shortModel->getEpisode($id);
        if (!$episode) {
            $this->error('剧集不存在');
        }

        $short = $this->shortModel->find($episode['short_id']);

        $this->assign('episode', $episode);
        $this->assign('short', $short);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('short/episode_form', '编辑剧集');
    }

    /**
     * 处理编辑剧集
     */
    public function doEditEpisode(int $id): void
    {
        $this->requireCsrf();

        $episode = $this->shortModel->getEpisode($id);
        if (!$episode) {
            $this->error('剧集不存在');
        }

        $data = [
            'episode_name' => trim($this->post('episode_name', '')),
            'episode_url' => trim($this->post('episode_url', '')),
            'episode_pic' => trim($this->post('episode_pic', '')),
            'episode_duration' => (int)$this->post('episode_duration', 0),
            'episode_sort' => (int)$this->post('episode_sort', 0),
            'episode_free' => (int)$this->post('episode_free', 1),
        ];

        $this->shortModel->updateEpisode($id, $data);
        $this->log('编辑', '短剧剧集', "ID:{$id}");

        $this->success('保存成功', ['url' => '/' . $this->adminEntry . '?s=short/episodes/' . $episode['short_id']]);
    }

    /**
     * 删除剧集
     */
    public function deleteEpisode(): void
    {
        // CSRF 验证
        $this->requireCsrf();
        
        $id = (int)$this->post('id', 0);

        if ($id <= 0) {
            $this->error('参数错误');
        }

        $this->shortModel->deleteEpisode($id);
        $this->log('删除', '短剧剧集', "ID:{$id}");
        $this->success('删除成功');
    }

    /**
     * 获取表单数据
     */
    private function getFormData(): array
    {
        return [
            'short_type' => $this->post('short_type', 'video'),
            'short_name' => trim($this->post('short_name', '')),
            'short_pic' => trim($this->post('short_pic', '')),
            'short_url' => trim($this->post('short_url', '')),
            'short_duration' => (int)$this->post('short_duration', 0),
            'short_desc' => trim($this->post('short_desc', '')),
            'short_tags' => trim($this->post('short_tags', '')),
            'category_id' => (int)$this->post('category_id', 0),
            'short_status' => (int)$this->post('short_status', 1),
        ];
    }
}
