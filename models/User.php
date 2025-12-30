<?php
/**
 * 用户模型
 * Powered by https://xpornkit.com
 */

class XpkUser extends XpkModel
{
    protected string $table = DB_PREFIX . 'user';
    protected string $pk = 'user_id';

    /**
     * 根据用户名查找
     */
    public function findByUsername(string $username): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE user_name = ?",
            [$username]
        );
    }

    /**
     * 根据邮箱查找
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE user_email = ?",
            [$email]
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
     * 注册用户
     */
    public function register(array $data): int
    {
        $data['user_pwd'] = $this->hashPassword($data['user_pwd']);
        $data['user_reg_time'] = time();
        $data['user_login_time'] = time();
        $data['user_status'] = 1;
        
        return $this->insert($data);
    }

    /**
     * 更新登录时间
     */
    public function updateLoginTime(int $id): void
    {
        $this->db->execute(
            "UPDATE {$this->table} SET user_login_time = ?, user_login_num = user_login_num + 1 WHERE user_id = ?",
            [time(), $id]
        );
    }

    /**
     * 统计今日某IP注册数量
     */
    public function countTodayRegisterByIp(string $ip): int
    {
        if (empty($ip)) return 0;
        
        $todayStart = strtotime('today');
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} WHERE user_reg_ip = ? AND user_reg_time >= ?",
            [$ip, $todayStart]
        );
        return (int)($result['cnt'] ?? 0);
    }
}
