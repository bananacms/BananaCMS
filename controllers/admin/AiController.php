<?php
/**
 * 后台 AI 配置控制器
 * Powered by https://xpornkit.com
 */

class AdminAiController extends AdminBaseController
{
    /**
     * AI 配置页面
     */
    public function index(): void
    {
        $config = $this->getAiConfig();
        
        // 获取统计数据
        $stats = $this->getRewriteStats();
        
        $this->assign('config', $config);
        $this->assign('stats', $stats);
        $this->assign('csrfToken', $this->csrfToken());
        $this->assign('flash', $this->getFlash());
        $this->render('ai/index', 'AI 配置');
    }

    /**
     * 保存配置
     */
    public function save(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        $configs = [
            'ai_enabled' => $this->post('ai_enabled', '0'),
            'ai_api_url' => trim($this->post('ai_api_url', '')),
            'ai_api_key' => trim($this->post('ai_api_key', '')),
            'ai_model' => trim($this->post('ai_model', 'gpt-4o-mini')),
            'ai_system_prompt' => trim($this->post('ai_system_prompt', '')),
            'ai_user_prompt' => trim($this->post('ai_user_prompt', '')),
            'ai_temperature' => (float)$this->post('ai_temperature', 0.8),
            'ai_max_tokens' => (int)$this->post('ai_max_tokens', 500),
            'ai_timeout' => (int)$this->post('ai_timeout', 30),
            'ai_batch_size' => (int)$this->post('ai_batch_size', 10),
        ];

        // 验证必填项
        if ($configs['ai_enabled'] === '1') {
            if (empty($configs['ai_api_url'])) {
                $this->error('请填写 API 地址');
            }
            if (empty($configs['ai_api_key'])) {
                $this->error('请填写 API Key');
            }
        }

        // 保存配置
        foreach ($configs as $name => $value) {
            $this->db->execute(
                "INSERT INTO " . DB_PREFIX . "config (config_name, config_value) VALUES (?, ?) 
                 ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)",
                [$name, $value]
            );
        }

        $this->log('修改', 'AI配置', '更新AI改写配置');
        $this->success('保存成功');
    }

    /**
     * 测试 API 连接
     */
    public function test(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        require_once CORE_PATH . 'AiRewrite.php';

        $config = [
            'api_url' => trim($this->post('ai_api_url', '')),
            'api_key' => trim($this->post('ai_api_key', '')),
            'model' => trim($this->post('ai_model', 'gpt-4o-mini')),
            'system_prompt' => trim($this->post('ai_system_prompt', '')),
            'user_prompt' => trim($this->post('ai_user_prompt', '')),
            'temperature' => (float)$this->post('ai_temperature', 0.8),
            'max_tokens' => (int)$this->post('ai_max_tokens', 500),
            'timeout' => (int)$this->post('ai_timeout', 30),
        ];

        if (empty($config['api_url']) || empty($config['api_key'])) {
            $this->error('请先填写 API 地址和 Key');
        }

        $ai = new XpkAiRewrite($config);
        $result = $ai->testConnection();

        if ($result['success']) {
            $this->success($result['message'], [
                'duration' => $result['duration'],
                'original' => $result['original'],
                'rewritten' => $result['rewritten']
            ]);
        } else {
            $this->error($result['message']);
        }
    }

    /**
     * 手动执行改写任务
     */
    public function run(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        require_once CORE_PATH . 'AiRewrite.php';

        if (!XpkAiRewrite::isEnabled()) {
            $this->error('AI 改写功能未启用或配置不完整');
        }

        $ai = XpkAiRewrite::fromDatabase();
        if (!$ai) {
            $this->error('加载 AI 配置失败');
        }

        // 检查 vod_ai_rewrite 字段是否存在
        try {
            $this->db->queryOne("SELECT vod_ai_rewrite FROM " . DB_PREFIX . "vod LIMIT 1");
        } catch (Exception $e) {
            $this->error('数据库缺少 vod_ai_rewrite 字段，请先执行数据库升级');
        }

        // 获取批量大小
        $batchSize = (int)$this->db->queryOne(
            "SELECT config_value FROM " . DB_PREFIX . "config WHERE config_name = 'ai_batch_size'"
        )['config_value'] ?? 10;

        // 获取待处理的视频
        $videos = $this->db->query(
            "SELECT vod_id, vod_name, vod_content FROM " . DB_PREFIX . "vod 
             WHERE vod_ai_rewrite = 0 AND vod_content != '' AND LENGTH(vod_content) > 20
             ORDER BY vod_id DESC LIMIT ?",
            [$batchSize]
        );

        if (empty($videos)) {
            $this->success('没有待处理的视频', ['processed' => 0]);
            return;
        }

        $processed = 0;
        $failed = 0;

        foreach ($videos as $video) {
            $rewritten = $ai->rewrite($video['vod_content']);
            
            if ($rewritten) {
                // 更新内容和标记
                $this->db->execute(
                    "UPDATE " . DB_PREFIX . "vod SET vod_content = ?, vod_ai_rewrite = 1 WHERE vod_id = ?",
                    [$rewritten, $video['vod_id']]
                );
                $processed++;
            } else {
                // 标记为处理失败（设为2），避免重复处理
                $this->db->execute(
                    "UPDATE " . DB_PREFIX . "vod SET vod_ai_rewrite = 2 WHERE vod_id = ?",
                    [$video['vod_id']]
                );
                $failed++;
            }

            // 避免请求过快
            usleep(500000); // 0.5秒
        }

        $this->log('执行', 'AI改写', "成功{$processed}条，失败{$failed}条");
        $this->success("处理完成：成功 {$processed} 条，失败 {$failed} 条", [
            'processed' => $processed,
            'failed' => $failed
        ]);
    }

    /**
     * 重置改写状态
     */
    public function reset(): void
    {
        if (!$this->verifyCsrf()) {
            $this->error('非法请求');
        }

        // 检查 vod_ai_rewrite 字段是否存在
        try {
            $this->db->queryOne("SELECT vod_ai_rewrite FROM " . DB_PREFIX . "vod LIMIT 1");
        } catch (Exception $e) {
            $this->error('数据库缺少 vod_ai_rewrite 字段，请先执行数据库升级');
        }

        $type = $this->post('type', 'failed'); // failed=仅失败的, all=全部

        if ($type === 'all') {
            $this->db->execute("UPDATE " . DB_PREFIX . "vod SET vod_ai_rewrite = 0");
            $this->log('重置', 'AI改写', '重置全部状态');
            $this->success('已重置全部视频的改写状态');
        } else {
            $this->db->execute("UPDATE " . DB_PREFIX . "vod SET vod_ai_rewrite = 0 WHERE vod_ai_rewrite = 2");
            $this->log('重置', 'AI改写', '重置失败状态');
            $this->success('已重置失败视频的改写状态');
        }
    }

    /**
     * 获取 AI 配置
     */
    private function getAiConfig(): array
    {
        $rows = $this->db->query("SELECT config_name, config_value FROM " . DB_PREFIX . "config WHERE config_name LIKE 'ai_%'");
        $config = [];
        foreach ($rows as $row) {
            $config[$row['config_name']] = $row['config_value'];
        }

        require_once CORE_PATH . 'AiRewrite.php';

        // 设置默认值
        $defaults = [
            'ai_enabled' => '0',
            'ai_api_url' => 'https://api.openai.com/v1',
            'ai_api_key' => '',
            'ai_model' => 'gpt-4o-mini',
            'ai_system_prompt' => XpkAiRewrite::getDefaultSystemPrompt(),
            'ai_user_prompt' => XpkAiRewrite::getDefaultUserPrompt(),
            'ai_temperature' => '0.8',
            'ai_max_tokens' => '500',
            'ai_timeout' => '30',
            'ai_batch_size' => '10',
        ];

        return array_merge($defaults, $config);
    }

    /**
     * 获取改写统计
     */
    private function getRewriteStats(): array
    {
        $total = $this->db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "vod")['cnt'] ?? 0;
        
        // 检查 vod_ai_rewrite 字段是否存在
        try {
            $rewritten = $this->db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "vod WHERE vod_ai_rewrite = 1")['cnt'] ?? 0;
            $failed = $this->db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "vod WHERE vod_ai_rewrite = 2")['cnt'] ?? 0;
            $pending = $this->db->queryOne("SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "vod WHERE vod_ai_rewrite = 0 AND vod_content != '' AND LENGTH(vod_content) > 20")['cnt'] ?? 0;
        } catch (Exception $e) {
            // 字段不存在，返回默认值
            $rewritten = 0;
            $failed = 0;
            $pending = 0;
        }

        return [
            'total' => $total,
            'rewritten' => $rewritten,
            'failed' => $failed,
            'pending' => $pending
        ];
    }
}
