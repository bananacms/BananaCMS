<?php
/**
 * 前台友链控制器（自助申请）
 * Powered by https://xpornkit.com
 */

class LinkController extends BaseController
{
    private XpkLink $linkModel;

    public function __construct()
    {
        parent::__construct();
        require_once MODEL_PATH . 'Link.php';
        $this->linkModel = new XpkLink();
    }

    /**
     * 友链页面（展示+申请表单）
     */
    public function index(): void
    {
        $links = $this->linkModel->getActive();
        
        $this->assign('links', $links);
        $this->assign('csrfToken', $this->csrfToken());
        $this->display('link/index');
    }

    /**
     * 提交友链申请
     */
    public function apply(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $name = trim($this->post('link_name', ''));
        $url = trim($this->post('link_url', ''));
        $logo = trim($this->post('link_logo', ''));
        $contact = trim($this->post('link_contact', ''));

        // 验证
        if (empty($name) || empty($url)) {
            $this->error('网站名称和地址不能为空');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error('网站地址格式不正确');
        }

        // 检查是否已存在
        $db = XpkDatabase::getInstance();
        $exists = $db->queryOne(
            "SELECT link_id FROM " . DB_PREFIX . "link WHERE link_url = ?",
            [$url]
        );

        if ($exists) {
            $this->error('该网站已申请过友链');
        }

        // 获取配置
        $config = xpk_cache()->get('site_config') ?: [];
        $autoApprove = ($config['link_auto_approve'] ?? '0') === '1';

        $status = 0; // 默认待审核

        // 自动换链模式：检测对方是否已添加回链
        if ($autoApprove) {
            $hasBacklink = $this->linkModel->checkBacklink($url);
            if ($hasBacklink) {
                $status = 1; // 自动通过
            }
        }

        // 插入数据
        $id = $this->linkModel->insert([
            'link_name' => $name,
            'link_url' => $url,
            'link_logo' => $logo,
            'link_contact' => $contact,
            'link_status' => $status,
            'link_type' => 0, // 申请
            'link_check_time' => $autoApprove ? time() : 0,
            'link_check_status' => $autoApprove ? ($status == 1 ? 1 : 2) : 0,
        ]);

        if ($status == 1) {
            $this->success('申请成功！检测到您已添加本站链接，友链已自动通过');
        } else if ($autoApprove) {
            $this->success('申请已提交！未检测到回链，请先在您的网站添加本站链接后重新申请');
        } else {
            $this->success('申请已提交，请等待管理员审核');
        }
    }
}
