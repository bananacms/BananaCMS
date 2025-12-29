<?php
/**
 * 后台系统配置控制器
 * Powered by https://xpornkit.com
 */

class AdminConfigController extends AdminBaseController
{
    /**
     * 配置页面
     */
    public function index(): void
    {
        $db = XpkDatabase::getInstance();
        $configs = $db->query("SELECT * FROM " . DB_PREFIX . "config");
        
        $configMap = [];
        foreach ($configs as $config) {
            $configMap[$config['config_name']] = $config['config_value'];
        }

        $this->assign('config', $configMap);
        $this->assign('csrfToken', $this->csrfToken());
        $this->assign('flash', $this->getFlash());
        $this->render('config/index', '系统配置');
    }

    /**
     * 保存配置
     */
    public function save(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $db = XpkDatabase::getInstance();
        $configs = [
            'site_name' => trim($this->post('site_name', '')),
            'site_url' => trim($this->post('site_url', '')),
            'site_keywords' => trim($this->post('site_keywords', '')),
            'site_description' => trim($this->post('site_description', '')),
            'site_icp' => trim($this->post('site_icp', '')),
            'site_logo' => trim($this->post('site_logo', '')),
            'site_template' => trim($this->post('site_template', 'default')),
            'site_status' => trim($this->post('site_status', '1')),
            'site_close_tip' => trim($this->post('site_close_tip', '')),
            // 用户设置
            'user_register' => trim($this->post('user_register', '1')),
            'user_register_limit' => trim($this->post('user_register_limit', '5')),
            // URL规则
            'url_mode' => trim($this->post('url_mode', '1')),
            'url_vod_detail' => trim($this->post('url_vod_detail', '')),
            'url_vod_play' => trim($this->post('url_vod_play', '')),
            'url_type' => trim($this->post('url_type', '')),
            'url_actor_detail' => trim($this->post('url_actor_detail', '')),
            'url_art_detail' => trim($this->post('url_art_detail', '')),
            // SEO模板
            'seo_title_vod_detail' => trim($this->post('seo_title_vod_detail', '')),
            'seo_keywords_vod_detail' => trim($this->post('seo_keywords_vod_detail', '')),
            'seo_description_vod_detail' => trim($this->post('seo_description_vod_detail', '')),
            'seo_title_type' => trim($this->post('seo_title_type', '')),
            'seo_keywords_type' => trim($this->post('seo_keywords_type', '')),
            'seo_title_actor_detail' => trim($this->post('seo_title_actor_detail', '')),
            'seo_title_art_detail' => trim($this->post('seo_title_art_detail', '')),
        ];

        foreach ($configs as $name => $value) {
            $exists = $db->queryOne(
                "SELECT config_id FROM " . DB_PREFIX . "config WHERE config_name = ?",
                [$name]
            );

            if ($exists) {
                $db->execute(
                    "UPDATE " . DB_PREFIX . "config SET config_value = ? WHERE config_name = ?",
                    [$value, $name]
                );
            } else {
                $db->execute(
                    "INSERT INTO " . DB_PREFIX . "config (config_name, config_value) VALUES (?, ?)",
                    [$name, $value]
                );
            }
        }

        // 清除配置缓存
        xpk_cache()->delete('site_config');

        $this->log('修改', '配置', '更新站点配置');
        $this->flash('success', '保存成功');
        $this->redirect('/admin.php/config');
    }

    /**
     * 上传文件（Logo等）
     */
    public function upload(): void
    {
        if (empty($_FILES['file'])) {
            $this->error('请选择文件');
        }

        $file = $_FILES['file'];
        $type = $_POST['type'] ?? 'image';

        // 检查错误
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->error('上传失败，错误码：' . $file['error']);
        }

        // 检查大小（2MB）
        if ($file['size'] > 2 * 1024 * 1024) {
            $this->error('文件大小不能超过2MB');
        }

        // 检查类型
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            $this->error('只支持 JPG/PNG/GIF/WEBP 格式');
        }

        // 生成文件名
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($type === 'logo') {
            $remotePath = 'config/logo.' . $ext;
        } else {
            $remotePath = date('Y/m/d/') . md5(uniqid(mt_rand(), true)) . '.' . $ext;
        }

        // 使用存储类上传
        require_once CORE_PATH . 'Storage.php';
        $storage = xpk_storage();
        
        try {
            $url = $storage->upload($file['tmp_name'], $remotePath);
            
            // Logo加时间戳防缓存
            if ($type === 'logo') {
                $url .= '?t=' . time();
            }
            
            $this->log('上传', '文件', $remotePath);
            $this->success('上传成功', ['url' => $url]);
        } catch (Exception $e) {
            $this->error('上传失败：' . $e->getMessage());
        }
    }
}
