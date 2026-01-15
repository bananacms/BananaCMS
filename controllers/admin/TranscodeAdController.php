<?php
/**
 * 后台转码广告管理控制器
 * Powered by https://xpornkit.com
 */

class AdminTranscodeAdController extends AdminBaseController
{
    private XpkTranscodeAd $adModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'TranscodeAd.php';
        $this->adModel = new XpkTranscodeAd();
    }

    /**
     * 广告列表
     */
    public function index(): void
    {
        $list = $this->adModel->getList();
        $config = $this->adModel->getConfig();
        
        $this->assign('list', $list);
        $this->assign('config', $config);
        $this->assign('flash', $this->getFlash());
        $this->assign('csrfToken', $this->csrfToken());
        
        $this->render('transcode/ad', '转码广告管理');
    }

    /**
     * 添加广告
     */
    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->doAdd();
            return;
        }
        
        $this->assign('ad', null);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('transcode/ad_form', '添加转码广告');
    }

    /**
     * 处理添加
     */
    private function doAdd(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }
        
        $data = [
            'ad_name' => trim($this->post('ad_name', '')),
            'ad_position' => $this->post('ad_position', 'head'),
            'ad_file' => trim($this->post('ad_file', '')),
            'ad_duration' => (int)$this->post('ad_duration', 0),
            'ad_sort' => (int)$this->post('ad_sort', 0),
            'ad_status' => (int)$this->post('ad_status', 0),
        ];
        
        if (empty($data['ad_name'])) {
            $this->error('请输入广告名称');
        }
        if (empty($data['ad_file'])) {
            $this->error('请上传广告视频');
        }
        
        // 验证广告位置
        if (!in_array($data['ad_position'], ['head', 'middle', 'tail'])) {
            $data['ad_position'] = 'head';
        }
        
        // 获取视频时长
        $filePath = ROOT_PATH . ltrim($data['ad_file'], '/');
        if ($data['ad_duration'] <= 0 && file_exists($filePath)) {
            require_once CORE_PATH . 'Transcoder.php';
            $transcoder = new XpkTranscoder();
            $info = $transcoder->getVideoInfo($filePath);
            $data['ad_duration'] = $info['duration'] ?? 0;
        }
        
        $id = $this->adModel->add($data);
        
        if ($id) {
            $this->log('添加', '转码广告', "ID:{$id} {$data['ad_name']}");
            $this->success('添加成功', ['url' => '/' . $this->adminEntry . '?s=transcode/ad']);
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 编辑广告
     */
    public function edit(int $id): void
    {
        $ad = $this->adModel->find($id);
        if (!$ad) {
            $this->flash('error', '广告不存在');
            $this->redirect('/' . $this->adminEntry . '?s=transcode/ad');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->doEdit($id);
            return;
        }
        
        $this->assign('ad', $ad);
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('transcode/ad_form', '编辑转码广告');
    }

    /**
     * 处理编辑
     */
    private function doEdit(int $id): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }
        
        $data = [
            'ad_name' => trim($this->post('ad_name', '')),
            'ad_position' => $this->post('ad_position', 'head'),
            'ad_file' => trim($this->post('ad_file', '')),
            'ad_duration' => (int)$this->post('ad_duration', 0),
            'ad_sort' => (int)$this->post('ad_sort', 0),
            'ad_status' => (int)$this->post('ad_status', 0),
        ];
        
        if (empty($data['ad_name'])) {
            $this->error('请输入广告名称');
        }
        
        // 验证广告位置
        if (!in_array($data['ad_position'], ['head', 'middle', 'tail'])) {
            $data['ad_position'] = 'head';
        }
        
        // 获取视频时长
        $filePath = !empty($data['ad_file']) ? ROOT_PATH . ltrim($data['ad_file'], '/') : '';
        if ($data['ad_duration'] <= 0 && $filePath && file_exists($filePath)) {
            require_once CORE_PATH . 'Transcoder.php';
            $transcoder = new XpkTranscoder();
            $info = $transcoder->getVideoInfo($filePath);
            $data['ad_duration'] = $info['duration'] ?? 0;
        }
        
        if ($this->adModel->edit($id, $data)) {
            $this->log('编辑', '转码广告', "ID:{$id} {$data['ad_name']}");
            $this->success('保存成功', ['url' => '/' . $this->adminEntry . '?s=transcode/ad']);
        } else {
            $this->error('保存失败');
        }
    }

    /**
     * 删除广告
     */
    public function delete(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }
        
        $id = (int)$this->post('id', 0);
        
        if ($id <= 0) {
            $this->error('参数错误');
        }
        
        $ad = $this->adModel->find($id);
        if (!$ad) {
            $this->error('广告不存在');
        }
        
        // 删除文件
        if (!empty($ad['ad_file'])) {
            $filePath = ROOT_PATH . ltrim($ad['ad_file'], '/');
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        
        if ($this->adModel->delete($id)) {
            $this->log('删除', '转码广告', "ID:{$id} {$ad['ad_name']}");
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 切换状态
     */
    public function toggle(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }
        
        $id = (int)$this->post('id', 0);
        
        $ad = $this->adModel->find($id);
        if (!$ad) {
            $this->error('广告不存在');
        }
        
        $newStatus = $ad['ad_status'] ? 0 : 1;
        
        if ($this->adModel->edit($id, ['ad_status' => $newStatus])) {
            $this->success($newStatus ? '已启用' : '已禁用');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 保存配置
     */
    public function saveConfig(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }
        
        $config = [
            'enable' => (bool)$this->post('enable', false),
            'head_enable' => (bool)$this->post('head_enable', false),
            'middle_enable' => (bool)$this->post('middle_enable', false),
            'middle_interval' => max(60, (int)$this->post('middle_interval', 300)),
            'tail_enable' => (bool)$this->post('tail_enable', false),
        ];
        
        if ($this->adModel->saveConfig($config)) {
            $this->log('保存', '转码广告配置', json_encode($config));
            $this->success('配置已保存');
        } else {
            $this->error('保存失败');
        }
    }

    /**
     * 上传广告视频
     */
    public function upload(): void
    {
        if (empty($_FILES['file'])) {
            $this->json(['success' => false, 'error' => '没有上传文件']);
            return;
        }
        
        $file = $_FILES['file'];
        
        // 检查文件类型
        $allowExt = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowExt)) {
            $this->json(['success' => false, 'error' => '不支持的文件格式']);
            return;
        }
        
        // 检查大小（最大100MB）
        if ($file['size'] > 100 * 1024 * 1024) {
            $this->json(['success' => false, 'error' => '文件不能超过100MB']);
            return;
        }
        
        // 保存文件
        $uploadDir = UPLOAD_PATH . 'transcode_ad/' . date('Ym') . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = bin2hex(random_bytes(8)) . '.' . $ext;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // 获取视频时长
            $duration = 0;
            require_once CORE_PATH . 'Transcoder.php';
            $transcoder = new XpkTranscoder();
            $info = $transcoder->getVideoInfo($filepath);
            if (!isset($info['error'])) {
                $duration = $info['duration'];
            }
            
            $url = str_replace(ROOT_PATH, '/', $filepath);
            
            $this->json([
                'success' => true,
                'url' => $url,
                'duration' => $duration,
            ]);
        } else {
            $this->json(['success' => false, 'error' => '上传失败']);
        }
    }
}
