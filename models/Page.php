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
            ],
            [
                'page_slug' => 'terms',
                'page_title' => '服务条款',
                'page_content' => '<h2>服务条款</h2><p>欢迎使用本站服务。使用本站即表示您同意以下条款：</p><h3>1. 服务说明</h3><p>本站提供视频内容浏览服务，所有内容均来自互联网。</p><h3>2. 用户行为</h3><p>用户应遵守相关法律法规，不得利用本站从事违法活动。</p><h3>3. 知识产权</h3><p>本站尊重知识产权，如有侵权请联系我们删除。</p><h3>4. 免责声明</h3><p>本站不对内容的准确性、完整性作任何保证。</p><h3>5. 条款修改</h3><p>本站保留随时修改服务条款的权利。</p>',
                'page_sort' => 4,
                'page_status' => 1,
                'page_footer' => 0
            ],
            [
                'page_slug' => 'privacy',
                'page_title' => '隐私政策',
                'page_content' => '<h2>隐私政策</h2><p>我们重视您的隐私保护。</p><h3>1. 信息收集</h3><p>我们可能收集您的注册信息、浏览记录等。</p><h3>2. 信息使用</h3><p>收集的信息仅用于提供和改进服务。</p><h3>3. 信息保护</h3><p>我们采取合理措施保护您的个人信息安全。</p><h3>4. Cookie使用</h3><p>本站使用Cookie来改善用户体验。</p>',
                'page_sort' => 5,
                'page_status' => 1,
                'page_footer' => 0
            ],
            [
                'page_slug' => 'dmca',
                'page_title' => 'DMCA版权声明',
                'page_content' => '<h2>DMCA版权声明</h2><p>本站尊重并保护知识产权，根据《数字千年版权法》(DMCA)的规定，我们将对涉嫌侵权内容采取相应措施。</p><h3>版权投诉</h3><p>如果您认为本站上的内容侵犯了您的版权，请向我们提供以下信息：</p><ul><li>您声称被侵权的版权作品的描述</li><li>涉嫌侵权内容在本站的具体位置（URL链接）</li><li>您的联系方式（地址、电话、邮箱）</li><li>您声明善意相信该内容的使用未经版权所有者授权</li><li>您声明投诉信息准确无误</li><li>您的签名（电子签名或手写签名）</li></ul><h3>处理流程</h3><p>收到有效的版权投诉后，我们将在24-48小时内进行审核处理。</p><h3>联系方式</h3><p>请将版权投诉发送至：admin@example.com</p><p>我们承诺认真对待每一份投诉，并依法保护版权所有者的合法权益。</p>',
                'page_sort' => 6,
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
