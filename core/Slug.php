<?php
/**
 * Slug 生成器
 * Powered by https://xpornkit.com
 */

require_once __DIR__ . '/Pinyin.php';

class XpkSlug
{
    /**
     * 生成 slug
     * @param string $text 中文或英文文本
     * @param string $separator 分隔符，默认 -
     * @return string
     */
    public static function generate(string $text, string $separator = '-'): string
    {
        // 如果已经是纯英文/数字，直接处理
        if (preg_match('/^[a-zA-Z0-9\s\-_]+$/', $text)) {
            return self::sanitize($text, $separator);
        }
        
        // 中文转拼音
        $pinyin = XpkPinyin::getPinyin($text);
        $slug = self::sanitize($pinyin, $separator);
        
        // 如果转换后为空（日语、韩语等），生成随机slug
        if (empty($slug)) {
            $slug = 'v' . substr(md5($text), 0, 8);
        }
        
        return $slug;
    }
    
    /**
     * 生成短 slug（首字母缩写）
     * @param string $text 中文文本
     * @return string
     */
    public static function generateShort(string $text): string
    {
        if (preg_match('/^[a-zA-Z0-9\s\-_]+$/', $text)) {
            return self::sanitize($text, '');
        }
        
        $short = strtolower(XpkPinyin::getShortPinyin($text));
        
        // 如果转换后为空（日语、韩语等），生成随机短slug
        if (empty($short) || !preg_match('/^[a-z0-9]+$/', $short)) {
            $short = substr(md5($text), 0, 6);
        }
        
        return $short;
    }
    
    /**
     * 清理并格式化 slug
     */
    private static function sanitize(string $text, string $separator): string
    {
        // 转小写
        $text = strtolower($text);
        
        // 替换空格和下划线为分隔符
        $text = preg_replace('/[\s_]+/', $separator, $text);
        
        // 只保留字母、数字、分隔符
        $text = preg_replace('/[^a-z0-9' . preg_quote($separator, '/') . ']/', '', $text);
        
        // 去除连续分隔符
        $text = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $text);
        
        // 去除首尾分隔符
        return trim($text, $separator);
    }
    
    /**
     * 生成唯一 slug（检查数据库）
     * @param string $text 文本
     * @param string $table 表名（不含前缀）
     * @param string $field slug 字段名
     * @param int $excludeId 排除的ID（编辑时用）
     * @return string
     */
    public static function unique(string $text, string $table, string $field, int $excludeId = 0): string
    {
        $db = XpkDatabase::getInstance();
        $baseSlug = self::generate($text);
        $slug = $baseSlug;
        $counter = 1;
        
        while (true) {
            $sql = "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . $table . " WHERE {$field} = ?";
            $params = [$slug];
            
            if ($excludeId > 0) {
                // 获取主键名（简单处理，假设是 表名_id）
                $pk = rtrim($table, 's') . '_id';
                if ($table === 'vod') $pk = 'vod_id';
                if ($table === 'actor') $pk = 'actor_id';
                if ($table === 'art') $pk = 'art_id';
                if ($table === 'type') $pk = 'type_id';
                
                $sql .= " AND {$pk} != ?";
                $params[] = $excludeId;
            }
            
            $result = $db->queryOne($sql, $params);
            
            if ($result['cnt'] == 0) {
                break;
            }
            
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}

/**
 * 快捷函数
 */
function xpk_slug(string $text, string $separator = '-'): string
{
    return XpkSlug::generate($text, $separator);
}

function xpk_slug_unique(string $text, string $table, string $field, int $excludeId = 0): string
{
    return XpkSlug::unique($text, $table, $field, $excludeId);
}

/**
 * 生成唯一slug（带自定义后缀）
 * 用于分类同步时，使用远程ID作为后缀确保唯一性
 */
function xpk_slug_unique_with_suffix(string $baseSlug, string $table, string $field, int $suffix): string
{
    $db = XpkDatabase::getInstance();
    
    // 先检查基础slug是否存在
    $result = $db->queryOne(
        "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . $table . " WHERE {$field} = ?",
        [$baseSlug]
    );
    
    // 不存在则直接使用
    if ($result['cnt'] == 0) {
        return $baseSlug;
    }
    
    // 存在则追加后缀
    $slug = $baseSlug . '-' . $suffix;
    
    // 再次检查带后缀的slug
    $result = $db->queryOne(
        "SELECT COUNT(*) as cnt FROM " . DB_PREFIX . $table . " WHERE {$field} = ?",
        [$slug]
    );
    
    // 如果还是重复，继续追加随机数
    if ($result['cnt'] > 0) {
        $slug = $baseSlug . '-' . $suffix . '-' . mt_rand(100, 999);
    }
    
    return $slug;
}
