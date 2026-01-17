<?php
/**
 * 配置文件验证类
 * Powered by https://xpornkit.com
 */

class XpkConfigValidator
{
    /**
     * 必需的配置常量
     */
    private static array $requiredConstants = [
        'DB_HOST',
        'DB_PORT',
        'DB_NAME',
        'DB_USER',
        'DB_PASS',
        'DB_PREFIX',
        'DB_CHARSET',
        'APP_SECRET',
        'SITE_NAME',
        'SITE_URL',
    ];

    /**
     * 验证配置文件
     * 
     * @return array 验证结果
     */
    public static function validate(): array
    {
        $errors = [];
        $warnings = [];

        // 检查配置文件是否存在
        $configFile = CONFIG_PATH . 'config.php';
        if (!file_exists($configFile)) {
            $errors[] = '配置文件不存在: ' . $configFile;
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // 检查配置文件权限
        $perms = fileperms($configFile);
        if (($perms & 0222) !== 0) {
            $warnings[] = '配置文件权限过高（可写），建议设置为 0644';
        }

        // 检查必需常量
        foreach (self::$requiredConstants as $const) {
            if (!defined($const)) {
                $errors[] = "必需常量未定义: {$const}";
            }
        }

        // 检查配置值有效性
        if (defined('DB_HOST') && empty(DB_HOST)) {
            $errors[] = 'DB_HOST 不能为空';
        }

        if (defined('DB_NAME') && empty(DB_NAME)) {
            $errors[] = 'DB_NAME 不能为空';
        }

        if (defined('DB_USER') && empty(DB_USER)) {
            $errors[] = 'DB_USER 不能为空';
        }

        if (defined('APP_SECRET') && strlen(APP_SECRET) < 16) {
            $warnings[] = 'APP_SECRET 长度过短，建议至少 16 个字符';
        }

        if (defined('SITE_URL') && !filter_var(SITE_URL, FILTER_VALIDATE_URL)) {
            $errors[] = 'SITE_URL 格式不正确';
        }

        // 检查其他配置文件
        $otherConfigs = [
            'constants.php' => CONFIG_PATH . 'constants.php',
            'payment.php' => CONFIG_PATH . 'payment.php',
            'vip.php' => CONFIG_PATH . 'vip.php',
        ];

        foreach ($otherConfigs as $name => $path) {
            if (!file_exists($path)) {
                $warnings[] = "配置文件不存在: {$name}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * 修复配置文件权限
     * 
     * @return bool 是否成功
     */
    public static function fixPermissions(): bool
    {
        $files = [
            CONFIG_PATH . 'config.php',
            CONFIG_PATH . 'constants.php',
            CONFIG_PATH . 'payment.php',
            CONFIG_PATH . 'vip.php',
        ];

        $success = true;
        foreach ($files as $file) {
            if (file_exists($file)) {
                // 设置为 0644（所有者可读写，其他用户只读）
                if (!@chmod($file, 0644)) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * 获取配置文件信息
     * 
     * @return array 配置文件信息
     */
    public static function getInfo(): array
    {
        $info = [];

        $files = [
            'config.php' => CONFIG_PATH . 'config.php',
            'constants.php' => CONFIG_PATH . 'constants.php',
            'payment.php' => CONFIG_PATH . 'payment.php',
            'vip.php' => CONFIG_PATH . 'vip.php',
        ];

        foreach ($files as $name => $path) {
            if (file_exists($path)) {
                $perms = fileperms($path);
                $info[$name] = [
                    'exists' => true,
                    'readable' => is_readable($path),
                    'writable' => is_writable($path),
                    'permissions' => substr(sprintf('%o', $perms), -4),
                    'size' => filesize($path),
                    'modified' => date('Y-m-d H:i:s', filemtime($path)),
                ];
            } else {
                $info[$name] = [
                    'exists' => false,
                ];
            }
        }

        return $info;
    }

    /**
     * 验证数据库连接
     * 
     * @return array 验证结果
     */
    public static function validateDatabase(): array
    {
        $result = [
            'connected' => false,
            'error' => null,
            'version' => null,
        ];

        try {
            $db = XpkDatabase::getInstance();
            $pdo = $db->getPdo();
            
            // 测试连接
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            $result['connected'] = true;
            $result['version'] = $version;
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 生成配置检查报告
     * 
     * @return string HTML 报告
     */
    public static function generateReport(): string
    {
        $validation = self::validate();
        $dbValidation = self::validateDatabase();
        $fileInfo = self::getInfo();

        $html = '<div style="font-family: monospace; padding: 20px; background: #f5f5f5;">';
        
        // 配置验证结果
        $html .= '<h3>配置文件验证</h3>';
        if ($validation['valid']) {
            $html .= '<p style="color: green;">✓ 配置文件验证通过</p>';
        } else {
            $html .= '<p style="color: red;">✗ 配置文件验证失败</p>';
            foreach ($validation['errors'] as $error) {
                $html .= '<p style="color: red;">- ' . htmlspecialchars($error) . '</p>';
            }
        }

        // 警告信息
        if (!empty($validation['warnings'])) {
            $html .= '<h4>警告</h4>';
            foreach ($validation['warnings'] as $warning) {
                $html .= '<p style="color: orange;">⚠ ' . htmlspecialchars($warning) . '</p>';
            }
        }

        // 数据库连接
        $html .= '<h3>数据库连接</h3>';
        if ($dbValidation['connected']) {
            $html .= '<p style="color: green;">✓ 数据库连接成功</p>';
            $html .= '<p>版本: ' . htmlspecialchars($dbValidation['version']) . '</p>';
        } else {
            $html .= '<p style="color: red;">✗ 数据库连接失败</p>';
            $html .= '<p>' . htmlspecialchars($dbValidation['error']) . '</p>';
        }

        // 文件信息
        $html .= '<h3>配置文件信息</h3>';
        $html .= '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
        $html .= '<tr><th>文件</th><th>存在</th><th>可读</th><th>可写</th><th>权限</th><th>大小</th><th>修改时间</th></tr>';
        
        foreach ($fileInfo as $name => $info) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($name) . '</td>';
            $html .= '<td>' . ($info['exists'] ? '✓' : '✗') . '</td>';
            
            if ($info['exists']) {
                $html .= '<td>' . ($info['readable'] ? '✓' : '✗') . '</td>';
                $html .= '<td style="' . ($info['writable'] ? 'color: red;' : 'color: green;') . '">' . ($info['writable'] ? '✓' : '✗') . '</td>';
                $html .= '<td>' . htmlspecialchars($info['permissions']) . '</td>';
                $html .= '<td>' . number_format($info['size']) . ' B</td>';
                $html .= '<td>' . htmlspecialchars($info['modified']) . '</td>';
            } else {
                $html .= '<td colspan="6">-</td>';
            }
            
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }
}

