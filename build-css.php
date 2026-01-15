<?php
/**
 * CSS构建脚本 - 生成优化的Tailwind CSS
 * 使用方法: php build-css.php
 */

echo "开始构建CSS...\n";

// 检查Node.js和npm是否安装
$nodeVersion = shell_exec('node --version 2>&1');
if (empty($nodeVersion) || strpos($nodeVersion, 'not found') !== false) {
    echo "错误: 未找到Node.js，请先安装Node.js\n";
    echo "下载地址: https://nodejs.org/\n";
    exit(1);
}

echo "Node.js版本: " . trim($nodeVersion) . "\n";

// 检查是否已安装依赖
if (!file_exists('node_modules')) {
    echo "安装依赖包...\n";
    $output = shell_exec('npm install 2>&1');
    echo $output . "\n";
}

// 构建CSS
echo "构建Tailwind CSS...\n";
$buildOutput = shell_exec('npm run build-css 2>&1');
echo $buildOutput . "\n";

// 检查构建结果
if (file_exists('static/css/tailwind.min.css')) {
    $fileSize = filesize('static/css/tailwind.min.css');
    $fileSizeKB = round($fileSize / 1024, 2);
    echo "✅ CSS构建成功！\n";
    echo "文件大小: {$fileSizeKB} KB\n";
    echo "文件位置: static/css/tailwind.min.css\n";
    
    // 更新header.php使用本地CSS
    $headerFile = 'template/default/layouts/header.php';
    if (file_exists($headerFile)) {
        $headerContent = file_get_contents($headerFile);
        
        // 检查是否已经更新过
        if (strpos($headerContent, 'tailwind.min.css') === false) {
            // 替换CDN为本地文件
            $headerContent = str_replace(
                '<script src="https://cdn.tailwindcss.com" defer></script>',
                '<link rel="stylesheet" href="/static/css/tailwind.min.css">',
                $headerContent
            );
            
            file_put_contents($headerFile, $headerContent);
            echo "✅ 已更新header.php使用本地CSS文件\n";
        } else {
            echo "ℹ️  header.php已经在使用本地CSS文件\n";
        }
    }
    
    echo "\n使用说明:\n";
    echo "1. 本地开发时可以继续使用CDN版本\n";
    echo "2. 生产环境建议使用构建后的本地文件\n";
    echo "3. 修改样式后运行 'php build-css.php' 重新构建\n";
    echo "4. 或者运行 'npm run watch-css' 监听文件变化自动构建\n";
    
} else {
    echo "❌ CSS构建失败\n";
    exit(1);
}

echo "\n构建完成！\n";
?>