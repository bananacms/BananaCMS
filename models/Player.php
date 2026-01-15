<?php
/**
 * 播放器模型
 * Powered by https://xpornkit.com
 */

class XpkPlayer extends XpkModel
{
    protected string $table = DB_PREFIX . 'player';
    protected string $pk = 'player_id';

    /**
     * 获取所有播放器
     */
    public function getAll(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY player_sort ASC, player_id ASC"
        );
    }

    /**
     * 获取启用的播放器
     */
    public function getEnabled(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE player_status = 1 ORDER BY player_sort ASC, player_id ASC"
        );
    }

    /**
     * 获取启用的播放器标识列表（用于采集验证）
     */
    public function getEnabledCodes(): array
    {
        $list = $this->getEnabled();
        return array_column($list, 'player_code');
    }

    /**
     * 根据标识获取播放器
     */
    public function findByCode(string $code): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE player_code = ?",
            [$code]
        );
    }

    /**
     * 检查标识是否存在
     */
    public function codeExists(string $code, int $excludeId = 0): bool
    {
        $sql = "SELECT player_id FROM {$this->table} WHERE player_code = ?";
        $params = [$code];
        
        if ($excludeId > 0) {
            $sql .= " AND player_id != ?";
            $params[] = $excludeId;
        }
        
        return $this->db->queryOne($sql, $params) !== null;
    }

    /**
     * 根据标识数组获取播放器映射（code => player）
     */
    public function getByCodesMap(array $codes): array
    {
        if (empty($codes)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($codes), '?'));
        $list = $this->db->query(
            "SELECT * FROM {$this->table} WHERE player_code IN ({$placeholders}) AND player_status = 1",
            $codes
        );
        
        $map = [];
        foreach ($list as $player) {
            $map[$player['player_code']] = $player;
        }
        return $map;
    }
}
