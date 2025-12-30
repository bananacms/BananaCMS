<?php
/**
 * 采集分类绑定模型
 * Powered by https://xpornkit.com
 */

class XpkCollectBind extends XpkModel
{
    protected string $table = DB_PREFIX . 'collect_bind';
    protected string $pk = 'bind_id';

    /**
     * 获取采集站的绑定关系
     * 优先使用采集站专属绑定，没有则使用全局绑定
     */
    public function getBinds(int $collectId): array
    {
        // 获取该采集站的专属绑定
        $specific = $this->db->query(
            "SELECT remote_type_id, local_type_id FROM {$this->table} WHERE collect_id = ?",
            [$collectId]
        );
        
        // 获取全局绑定
        $global = $this->db->query(
            "SELECT remote_type_id, local_type_id FROM {$this->table} WHERE collect_id = 0"
        );
        
        // 合并：专属绑定优先
        $binds = [];
        foreach ($global as $row) {
            $binds[$row['remote_type_id']] = (int)$row['local_type_id'];
        }
        foreach ($specific as $row) {
            $binds[$row['remote_type_id']] = (int)$row['local_type_id'];
        }
        
        return $binds;
    }

    /**
     * 获取采集站的绑定详情（包含名称）
     */
    public function getBindsDetail(int $collectId): array
    {
        return $this->db->query(
            "SELECT b.*, t.type_name as local_type_name 
             FROM {$this->table} b 
             LEFT JOIN " . DB_PREFIX . "type t ON b.local_type_id = t.type_id 
             WHERE b.collect_id = ? OR b.collect_id = 0 
             ORDER BY b.collect_id DESC, b.remote_type_id ASC",
            [$collectId]
        );
    }

    /**
     * 获取全局绑定
     */
    public function getGlobalBinds(): array
    {
        $rows = $this->db->query(
            "SELECT remote_type_id, local_type_id FROM {$this->table} WHERE collect_id = 0"
        );
        
        $binds = [];
        foreach ($rows as $row) {
            $binds[$row['remote_type_id']] = (int)$row['local_type_id'];
        }
        return $binds;
    }

    /**
     * 保存绑定关系
     */
    public function saveBinds(int $collectId, array $binds, array $remoteNames = []): void
    {
        // 删除该采集站的旧绑定
        $this->db->execute(
            "DELETE FROM {$this->table} WHERE collect_id = ?",
            [$collectId]
        );
        
        // 插入新绑定
        foreach ($binds as $remoteId => $localId) {
            if ($localId > 0) {
                $this->db->execute(
                    "INSERT INTO {$this->table} (collect_id, remote_type_id, remote_type_name, local_type_id) VALUES (?, ?, ?, ?)",
                    [$collectId, $remoteId, $remoteNames[$remoteId] ?? '', $localId]
                );
            }
        }
    }

    /**
     * 保存全局绑定
     */
    public function saveGlobalBinds(array $binds, array $remoteNames = []): void
    {
        $this->saveBinds(0, $binds, $remoteNames);
    }

    /**
     * 从其他采集站复制绑定
     */
    public function copyBinds(int $fromCollectId, int $toCollectId): int
    {
        // 获取源绑定
        $sourceBinds = $this->db->query(
            "SELECT remote_type_id, remote_type_name, local_type_id FROM {$this->table} WHERE collect_id = ?",
            [$fromCollectId]
        );
        
        if (empty($sourceBinds)) {
            return 0;
        }
        
        // 删除目标的旧绑定
        $this->db->execute(
            "DELETE FROM {$this->table} WHERE collect_id = ?",
            [$toCollectId]
        );
        
        // 复制绑定
        $count = 0;
        foreach ($sourceBinds as $bind) {
            $this->db->execute(
                "INSERT INTO {$this->table} (collect_id, remote_type_id, remote_type_name, local_type_id) VALUES (?, ?, ?, ?)",
                [$toCollectId, $bind['remote_type_id'], $bind['remote_type_name'], $bind['local_type_id']]
            );
            $count++;
        }
        
        return $count;
    }

    /**
     * 获取所有采集站的绑定统计
     */
    public function getBindStats(): array
    {
        return $this->db->query(
            "SELECT collect_id, COUNT(*) as bind_count 
             FROM {$this->table} 
             WHERE local_type_id > 0 
             GROUP BY collect_id"
        );
    }

    /**
     * 检查远程分类是否已有全局绑定
     */
    public function hasGlobalBind(int $remoteTypeId): bool
    {
        $row = $this->db->queryOne(
            "SELECT bind_id FROM {$this->table} WHERE collect_id = 0 AND remote_type_id = ?",
            [$remoteTypeId]
        );
        return !empty($row);
    }
}
