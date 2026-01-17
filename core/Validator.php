<?php
/**
 * 统一输入验证器
 * Powered by https://xpornkit.com
 */

class XpkValidator
{
    /**
     * 验证邮箱格式
     * 
     * @param string $email 邮箱地址
     * @return bool
     */
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 验证 URL 格式
     * 
     * @param string $url URL 地址
     * @return bool
     */
    public static function url(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * 验证整数
     * 
     * @param mixed $value 值
     * @param int|null $min 最小值
     * @param int|null $max 最大值
     * @return bool
     */
    public static function int($value, ?int $min = null, ?int $max = null): bool
    {
        if (!is_numeric($value)) {
            return false;
        }
        
        $int = (int)$value;
        
        if ($min !== null && $int < $min) {
            return false;
        }
        
        if ($max !== null && $int > $max) {
            return false;
        }
        
        return true;
    }

    /**
     * 验证浮点数
     * 
     * @param mixed $value 值
     * @param float|null $min 最小值
     * @param float|null $max 最大值
     * @return bool
     */
    public static function float($value, ?float $min = null, ?float $max = null): bool
    {
        if (!is_numeric($value)) {
            return false;
        }
        
        $float = (float)$value;
        
        if ($min !== null && $float < $min) {
            return false;
        }
        
        if ($max !== null && $float > $max) {
            return false;
        }
        
        return true;
    }

    /**
     * 验证 Slug 格式（仅小写字母、数字、连字符）
     * 
     * @param string $slug Slug 字符串
     * @return bool
     */
    public static function slug(string $slug): bool
    {
        return preg_match('/^[a-z0-9-]+$/', $slug) === 1;
    }

    /**
     * 验证用户名格式（字母、数字、下划线、连字符，3-20字符）
     * 
     * @param string $username 用户名
     * @return bool
     */
    public static function username(string $username): bool
    {
        return preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username) === 1;
    }

    /**
     * 验证密码强度（至少8字符，包含大小写字母、数字）
     * 
     * @param string $password 密码
     * @param int $minLength 最小长度
     * @return bool
     */
    public static function password(string $password, int $minLength = 6): bool
    {
        if (strlen($password) < $minLength) {
            return false;
        }
        
        // 至少包含一个大写字母、一个小写字母、一个数字
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password) === 1;
    }

    /**
     * 验证手机号码（中国）
     * 
     * @param string $phone 手机号码
     * @return bool
     */
    public static function phone(string $phone): bool
    {
        return preg_match('/^1[3-9]\d{9}$/', $phone) === 1;
    }

    /**
     * 验证身份证号（中国）
     * 
     * @param string $idCard 身份证号
     * @return bool
     */
    public static function idCard(string $idCard): bool
    {
        return preg_match('/^[1-9]\d{5}(18|19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])\d{3}[\dXx]$/', $idCard) === 1;
    }

    /**
     * 验证字符串长度
     * 
     * @param string $str 字符串
     * @param int|null $min 最小长度
     * @param int|null $max 最大长度
     * @return bool
     */
    public static function length(string $str, ?int $min = null, ?int $max = null): bool
    {
        $len = mb_strlen($str, 'UTF-8');
        
        if ($min !== null && $len < $min) {
            return false;
        }
        
        if ($max !== null && $len > $max) {
            return false;
        }
        
        return true;
    }

    /**
     * 验证字符串是否为空
     * 
     * @param mixed $value 值
     * @return bool
     */
    public static function required($value): bool
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }
        
        return !empty($value);
    }

    /**
     * 验证值是否在指定范围内
     * 
     * @param mixed $value 值
     * @param array $allowed 允许的值列表
     * @return bool
     */
    public static function in($value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }

    /**
     * 验证值是否不在指定范围内
     * 
     * @param mixed $value 值
     * @param array $notAllowed 不允许的值列表
     * @return bool
     */
    public static function notIn($value, array $notAllowed): bool
    {
        return !in_array($value, $notAllowed, true);
    }

    /**
     * 验证字符串是否只包含字母
     * 
     * @param string $str 字符串
     * @return bool
     */
    public static function alpha(string $str): bool
    {
        return preg_match('/^[a-zA-Z]+$/', $str) === 1;
    }

    /**
     * 验证字符串是否只包含字母和数字
     * 
     * @param string $str 字符串
     * @return bool
     */
    public static function alphaNum(string $str): bool
    {
        return preg_match('/^[a-zA-Z0-9]+$/', $str) === 1;
    }

    /**
     * 验证字符串是否只包含数字
     * 
     * @param string $str 字符串
     * @return bool
     */
    public static function numeric(string $str): bool
    {
        return is_numeric($str);
    }

    /**
     * 验证 IP 地址
     * 
     * @param string $ip IP 地址
     * @return bool
     */
    public static function ip(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * 验证 IPv4 地址
     * 
     * @param string $ip IP 地址
     * @return bool
     */
    public static function ipv4(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * 验证 IPv6 地址
     * 
     * @param string $ip IP 地址
     * @return bool
     */
    public static function ipv6(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * 验证日期格式
     * 
     * @param string $date 日期字符串
     * @param string $format 日期格式（默认 Y-m-d）
     * @return bool
     */
    public static function date(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * 验证时间格式
     * 
     * @param string $time 时间字符串
     * @param string $format 时间格式（默认 H:i:s）
     * @return bool
     */
    public static function time(string $time, string $format = 'H:i:s'): bool
    {
        $t = \DateTime::createFromFormat($format, $time);
        return $t && $t->format($format) === $time;
    }

    /**
     * 验证日期时间格式
     * 
     * @param string $datetime 日期时间字符串
     * @param string $format 日期时间格式（默认 Y-m-d H:i:s）
     * @return bool
     */
    public static function datetime(string $datetime, string $format = 'Y-m-d H:i:s'): bool
    {
        $dt = \DateTime::createFromFormat($format, $datetime);
        return $dt && $dt->format($format) === $datetime;
    }

    /**
     * 验证 JSON 格式
     * 
     * @param string $json JSON 字符串
     * @return bool
     */
    public static function json(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * 验证正则表达式匹配
     * 
     * @param string $str 字符串
     * @param string $pattern 正则表达式
     * @return bool
     */
    public static function regex(string $str, string $pattern): bool
    {
        return preg_match($pattern, $str) === 1;
    }

    /**
     * 批量验证必需字段
     * 
     * @param array $data 数据数组
     * @param array $fields 必需字段列表
     * @return array 错误信息数组
     */
    public static function required_fields(array $data, array $fields): array
    {
        $errors = [];
        
        foreach ($fields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $errors[$field] = "{$field} 不能为空";
            }
        }
        
        return $errors;
    }

    /**
     * 批量验证字段
     * 
     * @param array $data 数据数组
     * @param array $rules 验证规则数组
     *   格式: ['field' => 'rule1|rule2', ...]
     *   支持的规则: required, email, url, int, float, slug, username, phone, length:min,max, in:a,b,c, regex:/pattern/
     * @return array 错误信息数组
     */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $ruleStr) {
            $value = $data[$field] ?? null;
            $ruleList = explode('|', $ruleStr);
            
            foreach ($ruleList as $rule) {
                $rule = trim($rule);
                
                // 解析规则参数
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $params] = explode(':', $rule, 2);
                    $params = explode(',', $params);
                } else {
                    $ruleName = $rule;
                    $params = [];
                }
                
                $ruleName = trim($ruleName);
                
                // 执行验证
                $valid = true;
                
                switch ($ruleName) {
                    case 'required':
                        $valid = self::required($value);
                        if (!$valid) {
                            $errors[$field] = "{$field} 不能为空";
                        }
                        break;
                    
                    case 'email':
                        if ($value !== null && $value !== '') {
                            $valid = self::email($value);
                            if (!$valid) {
                                $errors[$field] = "{$field} 邮箱格式不正确";
                            }
                        }
                        break;
                    
                    case 'url':
                        if ($value !== null && $value !== '') {
                            $valid = self::url($value);
                            if (!$valid) {
                                $errors[$field] = "{$field} URL 格式不正确";
                            }
                        }
                        break;
                    
                    case 'int':
                        if ($value !== null && $value !== '') {
                            $min = $params[0] ?? null;
                            $max = $params[1] ?? null;
                            $valid = self::int($value, $min, $max);
                            if (!$valid) {
                                $errors[$field] = "{$field} 必须是整数";
                            }
                        }
                        break;
                    
                    case 'float':
                        if ($value !== null && $value !== '') {
                            $min = $params[0] ?? null;
                            $max = $params[1] ?? null;
                            $valid = self::float($value, $min, $max);
                            if (!$valid) {
                                $errors[$field] = "{$field} 必须是数字";
                            }
                        }
                        break;
                    
                    case 'slug':
                        if ($value !== null && $value !== '') {
                            $valid = self::slug($value);
                            if (!$valid) {
                                $errors[$field] = "{$field} 格式不正确（仅小写字母、数字、连字符）";
                            }
                        }
                        break;
                    
                    case 'username':
                        if ($value !== null && $value !== '') {
                            $valid = self::username($value);
                            if (!$valid) {
                                $errors[$field] = "{$field} 用户名格式不正确（3-20字符，仅字母、数字、下划线、连字符）";
                            }
                        }
                        break;
                    
                    case 'phone':
                        if ($value !== null && $value !== '') {
                            $valid = self::phone($value);
                            if (!$valid) {
                                $errors[$field] = "{$field} 手机号码格式不正确";
                            }
                        }
                        break;
                    
                    case 'length':
                        if ($value !== null && $value !== '') {
                            $min = isset($params[0]) ? (int)$params[0] : null;
                            $max = isset($params[1]) ? (int)$params[1] : null;
                            $valid = self::length($value, $min, $max);
                            if (!$valid) {
                                if ($min && $max) {
                                    $errors[$field] = "{$field} 长度必须在 {$min} 到 {$max} 之间";
                                } elseif ($min) {
                                    $errors[$field] = "{$field} 长度不能少于 {$min} 个字符";
                                } elseif ($max) {
                                    $errors[$field] = "{$field} 长度不能超过 {$max} 个字符";
                                }
                            }
                        }
                        break;
                    
                    case 'in':
                        if ($value !== null && $value !== '') {
                            $valid = self::in($value, $params);
                            if (!$valid) {
                                $errors[$field] = "{$field} 值不在允许范围内";
                            }
                        }
                        break;
                    
                    case 'regex':
                        if ($value !== null && $value !== '') {
                            $pattern = $params[0] ?? '';
                            $valid = self::regex($value, $pattern);
                            if (!$valid) {
                                $errors[$field] = "{$field} 格式不正确";
                            }
                        }
                        break;
                }
                
                // 如果验证失败且不是可选字段，停止继续验证
                if (!$valid && $ruleName !== 'required') {
                    break;
                }
            }
        }
        
        return $errors;
    }
}
