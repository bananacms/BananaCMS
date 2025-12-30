<?php
/**
 * 管理员模型
 * Powered by https://xpornkit.com
 */

class XpkAdmin extends XpkModel
{
    protected string $table = DB_PREFIX . 'admin';
    protected string $pk = 'admin_id';

    /**
     * 根据用户名查找
     */
    public function findByUsername(string $username): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE admin_name = ?",
            [$username]
        );
    }

    /**
     * 验证密码
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * 生成密码哈希
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * 更新登录信息
     */
    public function updateLogin(int $id, string $ip): void
    {
        $this->db->execute(
            "UPDATE {$this->table} SET admin_login_time = ?, admin_login_ip = ?, admin_login_num = admin_login_num + 1 WHERE admin_id = ?",
            [time(), $ip, $id]
        );
    }

    /**
     * 检查是否超级管理员
     */
    public function isSuper(int $id): bool
    {
        $admin = $this->find($id);
        return $admin && $admin['admin_id'] == 1;
    }
}
