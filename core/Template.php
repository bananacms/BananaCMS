<?php
/**
 * 模板引擎类
 * 支持 {xpk:xxx} 标签
 * Powered by https://xpornkit.com
 */

class XpkTemplate
{
    private array $vars = [];
    private string $tplPath;
    private string $cachePath;

    public function __construct()
    {
        $this->vars = [];
        $this->cachePath = RUNTIME_PATH . 'cache/';
        
        // 从数据库获取当前模板配置
        $template = 'default';
        try {
            $db = XpkDatabase::getInstance();
            $row = $db->queryOne("SELECT config_value FROM " . DB_PREFIX . "config WHERE config_name = 'site_template'");
            if ($row && !empty($row['config_value'])) {
                $template = $row['config_value'];
            }
        } catch (Exception $e) {
            // 数据库异常时使用默认模板
        }
        
        // 验证模板目录是否存在
        $tplDir = TPL_PATH . $template . '/';
        if (!is_dir($tplDir)) {
            $template = 'default';
            $tplDir = TPL_PATH . 'default/';
        }
        
        $this->tplPath = $tplDir;
        
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * 分配变量
     */
    public function assign(string $name, mixed $value): void
    {
        $this->vars[$name] = $value;
    }

    /**
     * 批量分配变量
     */
    public function assignArray(array $data): void
    {
        foreach ($data as $name => $value) {
            $this->vars[$name] = $value;
        }
    }

    /**
     * 渲染模板
     */
    public function render(string $template): void
    {
        $tplFile = $this->tplPath . $template . '.html';
        
        if (!file_exists($tplFile)) {
            throw new Exception("模板文件不存在: {$template}");
        }

        // 检查必须的 footer 标签
        $content = file_get_contents($tplFile);
        if (!$this->parseTag($content)) {
            // 标签检查失败时添加警告
            define('XPK_TPL_WARNING', true);
        }

        // 缓存文件（包含模板目录名，确保不同模板使用不同缓存）
        $tplName = basename(dirname($this->tplPath));
        $cacheFile = $this->cachePath . $tplName . '_' . md5($template) . '.php';
        
        // 开发模式或缓存过期时重新编译
        $needCompile = APP_DEBUG 
            || !file_exists($cacheFile) 
            || filemtime($tplFile) > filemtime($cacheFile);
        
        if ($needCompile) {
            // 编译模板
            $compiled = $this->compile($content);
            file_put_contents($cacheFile, $compiled);
        }

        // 提取变量并执行
        extract($this->vars);
        include $cacheFile;
    }

    /**
     * 编译模板
     */
    private function compile(string $content): string
    {
        // 编译 {xpk:vod} 标签
        $content = $this->compileVodTag($content);
        
        // 编译 {xpk:type} 标签
        $content = $this->compileTypeTag($content);
        
        // 编译 {xpk:actor} 标签
        $content = $this->compileActorTag($content);
        
        // 编译 {xpk:art} 标签
        $content = $this->compileArtTag($content);

        // 编译 {xpk:hot} 标签
        $content = $this->compileHotTag($content);

        // 编译 {xpk:ad} 广告标签
        $content = $this->compileAdTag($content);

        // 编译 {xpk:score} 评分标签
        $content = $this->compileScoreTag($content);

        // 编译 {xpk:footer} 标签
        $content = $this->compileFooterTag($content);

        // 编译变量 {$xxx}
        $content = preg_replace('/\{\$(\w+)\.(\w+)\}/', '<?php echo htmlspecialchars($${1}[\'${2}\'] ?? \'\'); ?>', $content);
        $content = preg_replace('/\{\$(\w+)\}/', '<?php echo htmlspecialchars($${1} ?? \'\'); ?>', $content);

        // 编译原始变量 {:$xxx} 不转义
        $content = preg_replace('/\{:\$(\w+)\.(\w+)\}/', '<?php echo $${1}[\'${2}\'] ?? \'\'; ?>', $content);
        $content = preg_replace('/\{:\$(\w+)\}/', '<?php echo $${1} ?? \'\'; ?>', $content);

        // 编译 if 语句
        $content = preg_replace('/\{if\s+(.+?)\}/', '<?php if(${1}): ?>', $content);
        $content = preg_replace('/\{elseif\s+(.+?)\}/', '<?php elseif(${1}): ?>', $content);
        $content = str_replace('{else}', '<?php else: ?>', $content);
        $content = str_replace('{/if}', '<?php endif; ?>', $content);

        // 编译 foreach 语句
        $content = preg_replace('/\{foreach\s+\$(\w+)\s+as\s+\$(\w+)\}/', '<?php foreach($${1} as $${2}): ?>', $content);
        $content = preg_replace('/\{foreach\s+\$(\w+)\s+as\s+\$(\w+)\s*=>\s*\$(\w+)\}/', '<?php foreach($${1} as $${2} => $${3}): ?>', $content);
        $content = str_replace('{/foreach}', '<?php endforeach; ?>', $content);

        // 编译 include - 使用绝对路径
        $tplPath = $this->tplPath;
        $content = preg_replace_callback('/\{include\s+file="(.+?)"\}/', function($matches) use ($tplPath) {
            $file = $matches[1];
            return '<?php include \'' . addslashes($tplPath) . $file . '.html\'; ?>';
        }, $content);

        return $content;
    }

    /**
     * 编译视频标签 {xpk:vod num="10" order="time" type="1"}
     */
    private function compileVodTag(string $content): string
    {
        $pattern = '/\{xpk:vod\s+(.*?)\}(.*?)\{\/xpk:vod\}/s';
        
        return preg_replace_callback($pattern, function($matches) {
            $attrs = $this->parseAttrs($matches[1]);
            $inner = $matches[2];
            
            $num = $attrs['num'] ?? 10;
            $order = $attrs['order'] ?? 'time';
            $type = $attrs['type'] ?? '';
            
            $code = '<?php ';
            $code .= '$_vod_list = (new XpkVod())->getList(' . $num . ', "' . $order . '", ' . ($type ?: 'null') . ');';
            $code .= 'foreach($_vod_list as $vo): ?>';
            $code .= $inner;
            $code .= '<?php endforeach; ?>';
            
            return $code;
        }, $content);
    }

    /**
     * 编译分类标签 {xpk:type}
     */
    private function compileTypeTag(string $content): string
    {
        $pattern = '/\{xpk:type\s*(.*?)\}(.*?)\{\/xpk:type\}/s';
        
        return preg_replace_callback($pattern, function($matches) {
            $attrs = $this->parseAttrs($matches[1]);
            $inner = $matches[2];
            
            $pid = $attrs['pid'] ?? 0;
            
            $code = '<?php ';
            $code .= '$_type_list = (new XpkType())->getList(' . $pid . ');';
            $code .= 'foreach($_type_list as $vo): ?>';
            $code .= $inner;
            $code .= '<?php endforeach; ?>';
            
            return $code;
        }, $content);
    }

    /**
     * 编译演员标签 {xpk:actor}
     */
    private function compileActorTag(string $content): string
    {
        $pattern = '/\{xpk:actor\s+(.*?)\}(.*?)\{\/xpk:actor\}/s';
        
        return preg_replace_callback($pattern, function($matches) {
            $attrs = $this->parseAttrs($matches[1]);
            $inner = $matches[2];
            
            $num = $attrs['num'] ?? 10;
            $order = $attrs['order'] ?? 'id';
            
            $code = '<?php ';
            $code .= '$_actor_list = (new XpkActor())->getList(' . $num . ', "' . $order . '");';
            $code .= 'foreach($_actor_list as $vo): ?>';
            $code .= $inner;
            $code .= '<?php endforeach; ?>';
            
            return $code;
        }, $content);
    }

    /**
     * 编译文章标签 {xpk:art}
     */
    private function compileArtTag(string $content): string
    {
        $pattern = '/\{xpk:art\s+(.*?)\}(.*?)\{\/xpk:art\}/s';
        
        return preg_replace_callback($pattern, function($matches) {
            $attrs = $this->parseAttrs($matches[1]);
            $inner = $matches[2];
            
            $num = $attrs['num'] ?? 10;
            $order = $attrs['order'] ?? 'time';
            
            $code = '<?php ';
            $code .= '$_art_list = (new XpkArt())->getList(' . $num . ', "' . $order . '");';
            $code .= 'foreach($_art_list as $vo): ?>';
            $code .= $inner;
            $code .= '<?php endforeach; ?>';
            
            return $code;
        }, $content);
    }

    /**
     * 编译热门标签 {xpk:hot}
     */
    private function compileHotTag(string $content): string
    {
        $pattern = '/\{xpk:hot\s+(.*?)\}(.*?)\{\/xpk:hot\}/s';
        
        return preg_replace_callback($pattern, function($matches) {
            $attrs = $this->parseAttrs($matches[1]);
            $inner = $matches[2];
            
            $num = $attrs['num'] ?? 10;
            
            $code = '<?php ';
            $code .= '$_hot_list = (new XpkVod())->getHot(' . $num . ');';
            $code .= 'foreach($_hot_list as $vo): ?>';
            $code .= $inner;
            $code .= '<?php endforeach; ?>';
            
            return $code;
        }, $content);
    }

    /**
     * 编译广告标签 {xpk:ad position="home_top"}
     */
    private function compileAdTag(string $content): string
    {
        // 单个广告 {xpk:ad position="xxx"}
        $content = preg_replace_callback('/\{xpk:ad\s+(.*?)\/?\}/', function($matches) {
            $attrs = $this->parseAttrs($matches[1]);
            $position = $attrs['position'] ?? '';
            $random = isset($attrs['random']) && $attrs['random'] === 'true';
            
            return '<?php echo xpk_ad(\'' . $position . '\', ' . ($random ? 'true' : 'false') . '); ?>';
        }, $content);
        
        // 多个广告循环 {xpk:adlist position="xxx"}...{/xpk:adlist}
        $pattern = '/\{xpk:adlist\s+(.*?)\}(.*?)\{\/xpk:adlist\}/s';
        $content = preg_replace_callback($pattern, function($matches) {
            $attrs = $this->parseAttrs($matches[1]);
            $inner = $matches[2];
            $position = $attrs['position'] ?? '';
            
            $code = '<?php ';
            $code .= 'require_once MODEL_PATH . "Ad.php";';
            $code .= '$_ad_model = new XpkAd();';
            $code .= '$_ad_list = $_ad_model->getByPosition(\'' . $position . '\');';
            $code .= 'foreach($_ad_list as $ad): ';
            $code .= '$_ad_model->incrementShow($ad["ad_id"]); ?>';
            $code .= $inner;
            $code .= '<?php endforeach; ?>';
            
            return $code;
        }, $content);
        
        return $content;
    }

    /**
     * 编译评分标签 {xpk:score type="vod" id="123"}
     */
    private function compileScoreTag(string $content): string
    {
        return preg_replace_callback('/\{xpk:score\s+(.*?)\/?\}/', function($matches) {
            $attrs = $this->parseAttrs($matches[1]);
            $type = $attrs['type'] ?? 'vod';
            $id = $attrs['id'] ?? '0';
            $size = $attrs['size'] ?? 'normal';
            
            // 如果id是变量形式
            if (preg_match('/^\$(\w+)$/', $id, $m)) {
                $idCode = '${' . $m[1] . '}';
            } else {
                $idCode = $id;
            }
            
            return '<div class="xpk-score-container" data-type="' . $type . '" data-id="<?php echo ' . $idCode . '; ?>" data-size="' . $size . '"></div>';
        }, $content);
    }

    /**
     * 编译页脚标签 {xpk:footer}
     */
    private function compileFooterTag(string $content): string
    {
        $footer = '<div class="text-center text-gray-500 text-sm py-4">';
        $footer .= 'Powered by <a href="https://xpornkit.com" class="text-red-600 hover:underline" target="_blank">香蕉CMS</a>';
        $footer .= '</div>';
        
        return str_replace('{xpk:footer}', $footer, $content);
    }

    /**
     * 解析标签属性
     */
    private function parseAttrs(string $str): array
    {
        $attrs = [];
        preg_match_all('/(\w+)=["\']([^"\']*)["\']/', $str, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $attrs[$match[1]] = $match[2];
        }
        return $attrs;
    }

    /**
     * 标签解析校验
     */
    private function parseTag(string $content): bool
    {
        // 检查是否包含 footer 标签或版权信息
        return strpos($content, '{xpk:footer}') !== false 
            || strpos($content, 'xpornkit.com') !== false;
    }

    /**
     * 设置模板目录
     */
    public function setTplPath(string $path): void
    {
        $this->tplPath = $path;
    }
}

/**
 * 快捷函数
 */
function xpk_view(string $template, array $data = []): void
{
    $tpl = new XpkTemplate();
    $tpl->assignArray($data);
    $tpl->render($template);
}

/**
 * 广告渲染函数
 * @param string $position 广告位置
 * @param bool $random 是否随机显示一个
 * @return string HTML代码
 */
function xpk_ad(string $position, bool $random = false): string
{
    static $adModel = null;
    if ($adModel === null) {
        require_once MODEL_PATH . 'Ad.php';
        $adModel = new XpkAd();
    }
    
    return $adModel->render($position, $random);
}

/**
 * 获取所有广告（用于自定义渲染）
 */
function xpk_ads(string $position): array
{
    static $adModel = null;
    if ($adModel === null) {
        require_once MODEL_PATH . 'Ad.php';
        $adModel = new XpkAd();
    }
    
    return $adModel->getByPosition($position);
}

/**
 * 格式化数字（如播放量）
 * @param int|float $number 数字
 * @return string 格式化后的字符串
 */
function xpk_format_number($number): string
{
    $number = (int)$number;
    if ($number >= 100000000) {
        return round($number / 100000000, 1) . '亿';
    } elseif ($number >= 10000) {
        return round($number / 10000, 1) . '万';
    } elseif ($number >= 1000) {
        return number_format($number);
    }
    return (string)$number;
}
