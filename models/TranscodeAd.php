<?php
/**
 * 转码广告模型
 * Powered by https://xpornkit.com
 */

class XpkTranscodeAd extends XpkModel
{
    protected string $table = DB_PREFIX . 'transcode_ad';
    protected string $pk = 'ad_id';

    // 广告位置
    const POS_HEAD = 'head';      // 片头
    const POS_MIDDLE = 'middle';  // 片中
    const POS_TAIL = 'tail';      // 片尾

    /**
     * 获取所有启用的广告
     */
    public function getEnabled(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE ad_status = 1 ORDER BY ad_sort ASC, ad_id ASC"
        );
    }

    /**
     * 按位置获取广告
     */
    public function getByPosition(string $position): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE ad_position = ? AND ad_status = 1 ORDER BY ad_sort ASC",
            [$position]
        );
    }

    /**
     * 获取广告列表（后台）
     */
    public function getList(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY ad_position ASC, ad_sort ASC, ad_id DESC"
        );
    }

    /**
     * 添加广告
     */
    public function add(array $data): int
    {
        $data['created_at'] = time();
        $data['updated_at'] = time();
        return $this->insert($data);
    }

    /**
     * 更新广告
     */
    public function edit(int $id, array $data): bool
    {
        $data['updated_at'] = time();
        return $this->update($id, $data) !== false;
    }

    /**
     * 获取转码配置
     */
    public function getConfig(): array
    {
        $config = xpk_config('transcode_ad_config', '');
        if (empty($config)) {
            return [
                'enable' => false,
                'head_enable' => false,
                'middle_enable' => false,
                'middle_interval' => 300, // 每5分钟插入一次
                'tail_enable' => false,
            ];
        }
        return json_decode($config, true) ?: [];
    }

    /**
     * 保存转码配置
     */
    public function saveConfig(array $config): bool
    {
        $db = XpkDatabase::getInstance();
        $exists = $db->queryOne(
            "SELECT 1 FROM " . DB_PREFIX . "config WHERE config_name = 'transcode_ad_config'"
        );
        
        $json = json_encode($config, JSON_UNESCAPED_UNICODE);
        
        if ($exists) {
            $result = $db->execute(
                "UPDATE " . DB_PREFIX . "config SET config_value = ? WHERE config_name = 'transcode_ad_config'",
                [$json]
            ) !== false;
        } else {
            $result = $db->execute(
                "INSERT INTO " . DB_PREFIX . "config (config_name, config_value) VALUES ('transcode_ad_config', ?)",
                [$json]
            ) !== false;
        }
        
        // 清除配置缓存
        if ($result) {
            xpk_cache()->delete('site_config');
        }
        
        return $result;
    }
}
