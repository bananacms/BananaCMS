<?php
/**
 * 后台单页管理控制器
 * Powered by https://xpornkit.com
 */

class AdminPageController extends AdminBaseController
{
    private array $pages = [
        'about' => '关于我们',
        'contact' => '联系方式',
        'disclaimer' => '免责声明'
    ];

    /**
     * 单页列表
     */
    public function index(): void
    {
        $db = XpkDatabase::getInstance();
        
        $pageList = [];
        foreach ($this->pages as $key => $title) {
            $config = $db->queryOne(
                "SELECT config_value FROM " . DB_PREFIX . "config WHERE config_name = ?",
                ['page_' . $key]
            );
            $pageList[] = [
                'key' => $key,
                'title' => $title,
                'content' => $config['config_value'] ?? '',
                'has_content' => !empty($config['config_value'])
            ];
        }

        $this->assign('pageList', $pageList);
        $this->assign('flash', $this->getFlash());
        $this->render('page/index', '单页管理');
    }

    /**
     * 编辑单页
     */
    public function edit(string $key): void
    {
        if (!isset($this->pages[$key])) {
            $this->flash('error', '页面不存在');
            $this->redirect('/admin.php/page');
            return;
        }

        $db = XpkDatabase::getInstance();
        $config = $db->queryOne(
            "SELECT config_value FROM " . DB_PREFIX . "config WHERE config_name = ?",
            ['page_' . $key]
        );

        $this->assign('pageKey', $key);
        $this->assign('pageTitle', $this->pages[$key]);
        $this->assign('pageContent', $config['config_value'] ?? '');
        $this->assign('csrfToken', $this->csrfToken());
        $this->render('page/edit', '编辑 - ' . $this->pages[$key]);
    }

    /**
     * 保存单页
     */
    public function save(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $key = $this->post('key', '');
        $content = $this->post('content', '');

        if (!isset($this->pages[$key])) {
            $this->error('页面不存在');
        }

        $db = XpkDatabase::getInstance();
        $configName = 'page_' . $key;

        $exists = $db->queryOne(
            "SELECT config_id FROM " . DB_PREFIX . "config WHERE config_name = ?",
            [$configName]
        );

        if ($exists) {
            $db->execute(
                "UPDATE " . DB_PREFIX . "config SET config_value = ? WHERE config_name = ?",
                [$content, $configName]
            );
        } else {
            $db->execute(
                "INSERT INTO " . DB_PREFIX . "config (config_name, config_value) VALUES (?, ?)",
                [$configName, $content]
            );
        }

        $this->log('修改', '单页', $this->pages[$key]);
        $this->success('保存成功');
    }
}
