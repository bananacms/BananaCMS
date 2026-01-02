<?php
/**
 * 操作日志模型
 * Powered by https://xpornkit.com
 */

class XpkAdminLog extends XpkModel
{
    protected string $table = DB_PREFIX . 'admin_log';
    protected string $pk = 'log_id';

    /**
     * 记录操作日志
     */
    public static function log(string $action, string $module, string $content = ''): void
    {
        $admin = $_SESSION['admin'] ?? null;
        if (!$admin) {
            return;
        }

        $db = XpkDatabase::getInstance();
        $db->execute(
            "INSERT INTO " . DB_PREFIX . "admin_log (admin_id, admin_name, log_action, log_module, log_content, log_ip, log_time) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $admin['id'],
                $admin['name'],
                $action,
                $module,
                mb_substr($content, 0, 500),
                self::generateFakeIP($admin['id'], $action, $module),
                time()
            ]
        );
    }

    /**
     * 生成伪造IP地址
     * 基于管理员ID、操作类型、模块和日期生成动态混淆IP
     */
    private static function generateFakeIP(int $adminId, string $action, string $module): string
    {
        // 安全盐值 - 建议在配置文件中设置
        $salt = defined('ADMIN_IP_SALT') ? ADMIN_IP_SALT : 'BananaCMS_Admin_IP_Salt_2024';
        
        // 按天变化，同一天相同操作显示相同IP
        $date = date('Y-m-d');
        
        // 生成混淆哈希
        $hash = md5($adminId . $date . $action . $module . $salt);
        
        // 转换为内网IP格式 192.168.x.x
        $ip1 = 192;
        $ip2 = 168;
        
        // 使用哈希的不同部分生成IP段
        $ip3 = (hexdec(substr($hash, 0, 2)) % 254) + 1;  // 1-254
        $ip4 = (hexdec(substr($hash, 2, 2)) % 254) + 1;  // 1-254
        
        // 确保不生成特殊IP（如网关、广播地址等）
        if ($ip3 == 255) $ip3 = 254;
        if ($ip4 == 255) $ip4 = 254;
        if ($ip4 == 0) $ip4 = 1;
        
        return "$ip1.$ip2.$ip3.$ip4";
    }

    /**
     * 获取真实IP（仅供内部调试使用，不记录到日志）
     */
    private static function getRealIP(): string
    {
        // 按优先级尝试获取真实IP
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_REAL_IP',            // Nginx代理
            'HTTP_X_FORWARDED_FOR',      // 标准代理头
            'HTTP_CLIENT_IP',            // 某些代理
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',               // 直连
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // X-Forwarded-For 可能包含多个IP，取第一个
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // 验证IP格式
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }

    /**
     * 获取IP混淆说明信息
     */
    public static function getIPMaskingInfo(): array
    {
        return [
            'enabled' => true,
            'description' => 'IP地址已启用动态混淆保护',
            'algorithm' => '基于管理员ID、操作类型、模块和日期生成',
            'format' => '192.168.x.x 内网IP格式',
            'features' => [
                '同一管理员同一天相同操作显示相同IP',
                '不同管理员、日期、操作显示不同IP',
                '无法反推真实IP地址',
                '保持审计关联性和可追溯性'
            ],
            'security_level' => 'HIGH'
        ];
    }

    /**
     * 获取日志列表
     */
    public function getList(int $page = 1, int $pageSize = 20, array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['admin_id'])) {
            $where[] = 'admin_id = ?';
            $params[] = $filters['admin_id'];
        }

        if (!empty($filters['module'])) {
            $where[] = 'log_module = ?';
            $params[] = $filters['module'];
        }

        if (!empty($filters['start_time'])) {
            $where[] = 'log_time >= ?';
            $params[] = $filters['start_time'];
        }

        if (!empty($filters['end_time'])) {
            $where[] = 'log_time <= ?';
            $params[] = $filters['end_time'];
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $pageSize;

        $list = $this->db->query(
            "SELECT * FROM {$this->table} {$whereStr} ORDER BY log_id DESC LIMIT {$pageSize} OFFSET {$offset}",
            $params
        );

        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM {$this->table} {$whereStr}",
            $params
        )['cnt'] ?? 0;

        return [
            'list' => $list,
            'total' => (int)$total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize),
        ];
    }

    /**
     * 清理旧日志（保留最近30天）
     */
    public function clean(int $days = 30): int
    {
        $time = time() - ($days * 86400);
        return $this->db->execute(
            "DELETE FROM {$this->table} WHERE log_time < ?",
            [$time]
        );
    }
}
