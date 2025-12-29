<?php
/**
 * 单页面控制器
 * Powered by https://xpornkit.com
 */

class PageController extends BaseController
{
    /**
     * 显示单页面
     */
    public function show(string $slug): void
    {
        $pages = [
            'about' => [
                'title' => '关于我们',
                'config_key' => 'page_about'
            ],
            'contact' => [
                'title' => '联系方式',
                'config_key' => 'page_contact'
            ],
            'disclaimer' => [
                'title' => '免责声明',
                'config_key' => 'page_disclaimer'
            ]
        ];
        
        if (!isset($pages[$slug])) {
            $this->error404('页面不存在');
            return;
        }
        
        $page = $pages[$slug];
        $db = XpkDatabase::getInstance();
        
        // 从配置表获取内容
        $config = $db->queryOne(
            "SELECT config_value FROM " . DB_PREFIX . "config WHERE config_name = ?",
            [$page['config_key']]
        );
        
        $content = $config['config_value'] ?? '';
        
        $this->display('page/show', [
            'pageTitle' => $page['title'],
            'pageContent' => $content,
            'slug' => $slug
        ]);
    }
}
