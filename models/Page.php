<?php
/**
 * 单页面模型
 * Powered by https://xpornkit.com
 */

class XpkPage extends XpkModel
{
    protected string $table = DB_PREFIX . 'page';
    protected string $pk = 'page_id';

    /**
     * 获取所有页面
     */
    public function getAll(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY page_sort ASC, page_id ASC"
        );
    }

    /**
     * 获取启用的页面（用于前台底部显示）
     */
    public function getEnabled(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE page_status = 1 ORDER BY page_sort ASC, page_id ASC"
        );
    }

    /**
     * 根据slug获取页面
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE page_slug = ? AND page_status = 1",
            [$slug]
        );
    }

    /**
     * 检查slug是否存在
     */
    public function slugExists(string $slug, int $excludeId = 0): bool
    {
        $sql = "SELECT page_id FROM {$this->table} WHERE page_slug = ?";
        $params = [$slug];
        
        if ($excludeId > 0) {
            $sql .= " AND page_id != ?";
            $params[] = $excludeId;
        }
        
        return $this->db->queryOne($sql, $params) !== null;
    }

    /**
     * 初始化默认页面
     */
    public function initDefaults(): int
    {
        $defaults = [
            [
                'page_slug' => 'about',
                'page_title' => '关于我们',
                'page_content' => '<h2>关于我们</h2><p>欢迎访问本站！我们致力于为用户提供优质的视频内容服务。</p><p>如有任何问题或建议，欢迎联系我们。</p>',
                'page_sort' => 1,
                'page_status' => 1,
                'page_footer' => 1
            ],
            [
                'page_slug' => 'contact',
                'page_title' => '联系方式',
                'page_content' => '<h2>联系方式</h2><p>如需联系我们，请通过以下方式：</p><ul><li>邮箱：admin@example.com</li></ul><p>我们会尽快回复您的消息。</p>',
                'page_sort' => 2,
                'page_status' => 1,
                'page_footer' => 1
            ],
            [
                'page_slug' => 'disclaimer',
                'page_title' => '免责声明',
                'page_content' => '<h2>免责声明</h2><p>本站所有内容均来自互联网，仅供学习交流使用。</p><p>本站不存储任何视频文件，所有视频均由第三方提供。</p><p>如有侵权，请联系我们删除。</p>',
                'page_sort' => 3,
                'page_status' => 1,
                'page_footer' => 1
            ]
        ];

        $added = 0;
        foreach ($defaults as $page) {
            if (!$this->slugExists($page['page_slug'])) {
                $this->insert($page);
                $added++;
            }
        }
        return $added;
    }
}
