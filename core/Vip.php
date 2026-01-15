<?php
/**
 * VIP权限管理
 * Powered by https://xpornkit.com
 */

class XpkVip
{
    private XpkDatabase $db;
    private array $config;
    
    public function __construct()
    {
        $this->db = XpkDatabase::getInstance();
        $this->config = require CONFIG_PATH . 'vip.php';
    }
    
    /**
     * VIP功能是否启用
     */
    public function isEnabled(): bool
    {
        return !empty($this->config['vip_enabled']);
    }
    
    /**
     * 检查用户是否为有效VIP
     */
    public function isVip(array $user): bool
    {
        if (empty($user['user_vip_level']) || $user['user_vip_level'] < 1) {
            return false;
        }
        if (empty($user['user_vip_expire'])) {
            return false;
        }
        return $user['user_vip_expire'] > time();
    }
    
    /**
     * 获取用户VIP信息
     */
    public function getVipInfo(array $user): array
    {
        $isVip = $this->isVip($user);
        
        return [
            'is_vip' => $isVip,
            'vip_level' => (int)($user['user_vip_level'] ?? 0),
            'vip_expire' => (int)($user['user_vip_expire'] ?? 0),
            'vip_expire_date' => $isVip ? date('Y-m-d', $user['user_vip_expire']) : null,
            'points' => (int)($user['user_points'] ?? 0),
        ];
    }
    
    /**
     * 获取用户每日观看限制
     */
    public function getDailyLimit(array $user): int
    {
        if (!$this->isEnabled()) {
            return 9999; // VIP功能关闭，不限制
        }
        
        if ($this->isVip($user)) {
            // VIP用户从套餐获取限制
            $package = $this->db->queryOne(
                "SELECT package_daily_limit FROM " . DB_PREFIX . "vip_package WHERE package_id = ?",
                [$user['user_vip_level'] ?? 1]
            );
            return (int)($package['package_daily_limit'] ?? 9999);
        }
        
        // 免费用户
        return $this->config['free_user']['daily_limit'] ?? 3;
    }

    
    /**
     * 获取今日已观看次数
     */
    public function getTodayViews(int $userId): int
    {
        $today = date('Y-m-d');
        
        $user = $this->db->queryOne(
            "SELECT user_daily_views, user_daily_date FROM " . DB_PREFIX . "user WHERE user_id = ?",
            [$userId]
        );
        
        if (!$user) {
            return 0;
        }
        
        // 日期不同，重置计数
        if ($user['user_daily_date'] !== $today) {
            $this->db->execute(
                "UPDATE " . DB_PREFIX . "user SET user_daily_views = 0, user_daily_date = ? WHERE user_id = ?",
                [$today, $userId]
            );
            return 0;
        }
        
        return (int)$user['user_daily_views'];
    }
    
    /**
     * 检查是否可以观看视频
     */
    public function canWatch(int $userId, int $vodId = 0): array
    {
        // VIP功能未启用，直接允许
        if (!$this->isEnabled()) {
            return ['can' => true, 'type' => 'unlimited'];
        }
        
        $user = $this->db->queryOne(
            "SELECT * FROM " . DB_PREFIX . "user WHERE user_id = ?",
            [$userId]
        );
        
        if (!$user) {
            return ['can' => false, 'reason' => 'not_login', 'message' => '请先登录'];
        }
        
        $dailyLimit = $this->getDailyLimit($user);
        $todayViews = $this->getTodayViews($userId);
        
        // VIP用户
        if ($this->isVip($user)) {
            if ($dailyLimit >= 9999 || $todayViews < $dailyLimit) {
                return [
                    'can' => true, 
                    'type' => 'vip', 
                    'remaining' => max(0, $dailyLimit - $todayViews),
                    'is_vip' => true,
                ];
            }
        }
        
        // 免费用户检查每日限制
        if ($todayViews < $dailyLimit) {
            return [
                'can' => true, 
                'type' => 'free', 
                'remaining' => $dailyLimit - $todayViews,
                'is_vip' => false,
            ];
        }
        
        // 检查积分解锁
        $pointsCost = $this->config['points']['unlock_cost'] ?? 10;
        if ($user['user_points'] >= $pointsCost) {
            return [
                'can' => true, 
                'type' => 'points', 
                'cost' => $pointsCost,
                'balance' => (int)$user['user_points'],
                'is_vip' => false,
            ];
        }
        
        // 无法观看
        return [
            'can' => false, 
            'reason' => 'limit_exceeded',
            'message' => '今日免费次数已用完，请开通VIP或使用积分解锁',
            'need_points' => $pointsCost,
            'have_points' => (int)$user['user_points'],
            'is_vip' => false,
        ];
    }

    
    /**
     * 记录观看(消耗次数或积分)
     */
    public function recordWatch(int $userId, int $vodId, string $type = 'free'): bool
    {
        $today = date('Y-m-d');
        
        if ($type === 'points') {
            $cost = $this->config['points']['unlock_cost'] ?? 10;
            
            // 扣除积分
            $result = $this->db->execute(
                "UPDATE " . DB_PREFIX . "user SET user_points = user_points - ? WHERE user_id = ? AND user_points >= ?",
                [$cost, $userId, $cost]
            );
            
            if ($result) {
                // 获取新余额
                $user = $this->db->queryOne(
                    "SELECT user_points FROM " . DB_PREFIX . "user WHERE user_id = ?",
                    [$userId]
                );
                
                // 记录积分变动
                $this->db->insert(DB_PREFIX . 'point_log', [
                    'user_id' => $userId,
                    'log_type' => 'consume',
                    'log_amount' => -$cost,
                    'log_balance' => $user['user_points'] ?? 0,
                    'log_remark' => '积分解锁视频',
                    'related_id' => $vodId,
                    'log_time' => time(),
                ]);
                
                return true;
            }
            
            return false;
        }
        
        // 增加观看次数
        $this->db->execute(
            "UPDATE " . DB_PREFIX . "user SET user_daily_views = user_daily_views + 1, user_daily_date = ? WHERE user_id = ?",
            [$today, $userId]
        );
        
        return true;
    }
    
    /**
     * 获取所有VIP套餐
     */
    public function getPackages(): array
    {
        return $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "vip_package WHERE package_status = 1 ORDER BY package_sort ASC"
        );
    }
    
    /**
     * 获取单个套餐
     */
    public function getPackage(int $packageId): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM " . DB_PREFIX . "vip_package WHERE package_id = ? AND package_status = 1",
            [$packageId]
        );
    }
    
    /**
     * 根据编码获取套餐
     */
    public function getPackageByCode(string $code): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM " . DB_PREFIX . "vip_package WHERE package_code = ? AND package_status = 1",
            [$code]
        );
    }

    
    /**
     * 添加积分
     */
    public function addPoints(int $userId, int $amount, string $type, string $remark, ?int $relatedId = null): bool
    {
        if ($amount <= 0) {
            return false;
        }
        
        $this->db->beginTransaction();
        
        try {
            // 增加积分
            $this->db->execute(
                "UPDATE " . DB_PREFIX . "user SET user_points = user_points + ? WHERE user_id = ?",
                [$amount, $userId]
            );
            
            // 获取新余额
            $user = $this->db->queryOne(
                "SELECT user_points FROM " . DB_PREFIX . "user WHERE user_id = ?",
                [$userId]
            );
            
            // 记录
            $this->db->insert(DB_PREFIX . 'point_log', [
                'user_id' => $userId,
                'log_type' => $type,
                'log_amount' => $amount,
                'log_balance' => $user['user_points'] ?? 0,
                'log_remark' => $remark,
                'related_id' => $relatedId,
                'log_time' => time(),
            ]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * 扣除积分
     */
    public function deductPoints(int $userId, int $amount, string $type, string $remark, ?int $relatedId = null): bool
    {
        if ($amount <= 0) {
            return false;
        }
        
        $this->db->beginTransaction();
        
        try {
            // 检查余额
            $user = $this->db->queryOne(
                "SELECT user_points FROM " . DB_PREFIX . "user WHERE user_id = ?",
                [$userId]
            );
            
            if (!$user || $user['user_points'] < $amount) {
                $this->db->rollBack();
                return false;
            }
            
            // 扣除积分
            $this->db->execute(
                "UPDATE " . DB_PREFIX . "user SET user_points = user_points - ? WHERE user_id = ? AND user_points >= ?",
                [$amount, $userId, $amount]
            );
            
            $newBalance = $user['user_points'] - $amount;
            
            // 记录
            $this->db->insert(DB_PREFIX . 'point_log', [
                'user_id' => $userId,
                'log_type' => $type,
                'log_amount' => -$amount,
                'log_balance' => $newBalance,
                'log_remark' => $remark,
                'related_id' => $relatedId,
                'log_time' => time(),
            ]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * 获取积分记录
     */
    public function getPointLogs(int $userId, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        
        $list = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "point_log WHERE user_id = ? ORDER BY log_id DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
        
        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . "point_log WHERE user_id = ?",
            [$userId]
        )['cnt'] ?? 0;
        
        return [
            'list' => $list,
            'total' => (int)$total,
            'page' => $page,
            'pages' => ceil($total / $limit),
        ];
    }
}
