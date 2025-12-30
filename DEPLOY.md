# 香蕉CMS 上线部署指南

## 环境要求

- PHP >= 8.0
- MySQL >= 5.7
- Apache/Nginx
- 开启 PDO_MySQL 扩展

## 部署步骤

### 1. 上传文件

将所有文件上传到服务器 Web 目录

### 2. 配置数据库

编辑 `config/config.php`：

```php
// 【重要】上线前必须关闭调试模式
define('APP_DEBUG', false);

// 【重要】修改安全密钥
define('APP_SECRET', '你的随机密钥');

// 修改站点信息
define('SITE_NAME', '你的站点名称');
define('SITE_URL', 'https://你的域名');

// 修改数据库配置
define('DB_HOST', '数据库地址');
define('DB_PORT', 3306);
define('DB_NAME', '数据库名');
define('DB_USER', '数据库用户');
define('DB_PASS', '数据库密码');
```

### 3. 导入数据库

```bash
mysql -u用户名 -p 数据库名 < data.sql
```

### 4. 设置目录权限

```bash
chmod -R 755 runtime/
chmod -R 755 upload/
chmod -R 755 config/
```

### 5. 配置伪静态

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Sitemap
    RewriteRule ^sitemap\.xml$ sitemap.php [QSA,L]

    # 前台路由
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [QSA,L]

    # 后台路由
    RewriteCond %{REQUEST_URI} ^/admin\.php
    RewriteRule ^admin\.php/(.*)$ admin.php?/$1 [QSA,L]
</IfModule>

# 禁止访问敏感目录
<FilesMatch "^(config|core|models|controllers|views|runtime|tests)">
    Order deny,allow
    Deny from all
</FilesMatch>
```

#### Nginx
```nginx
# Sitemap
location = /sitemap.xml {
    rewrite ^ /sitemap.php last;
}

# 禁止访问敏感目录
location ~ ^/(config|core|models|controllers|views|runtime|tests)/ {
    deny all;
}

# 前台伪静态
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=$1 last;
        break;
    }
}
```

> 💡 完整配置文件见 `伪静态/` 目录

### 6. 创建安装锁

```bash
touch config/install.lock
```

### 7. 访问测试

- 前台: `https://你的域名/`
- 后台: `https://你的域名/admin.php`
- API: `https://你的域名/api.php?action=vod.list`
- Sitemap: `https://你的域名/sitemap.xml`

## SEO 说明

- `robots.txt` - 已配置，屏蔽后台、搜索、用户页面
- `sitemap.xml` - 支持分片，每片最多5000条URL
  - `/sitemap.xml` - 索引文件
  - `/sitemap.xml?type=main` - 首页、分类等
  - `/sitemap.xml?type=vod&page=1` - 视频分片
  - `/sitemap.xml?type=actor&page=1` - 演员分片
  - `/sitemap.xml?type=art&page=1` - 文章分片
- 搜索页、用户页已添加 `noindex` 标签，防止被刷留痕

## 上线检查清单

| 项目 | 状态 |
|------|------|
| APP_DEBUG 设为 false | ☐ |
| APP_SECRET 已修改 | ☐ |
| 数据库配置正确 | ☐ |
| runtime 目录可写 | ☐ |
| upload 目录可写 | ☐ |
| config/install.lock 已创建 | ☐ |
| 伪静态已配置 | ☐ |
| HTTPS 已启用 | ☐ |

## 管理员账号

管理员账号在安装向导中设置，没有默认账号。

- 后台地址: `/admin.php`
- 账号密码: 安装时自行设置

## 目录结构

```
bananacms/
├── config/          # 配置文件（禁止外部访问）
├── controllers/     # 控制器
├── core/            # 核心类
├── models/          # 模型
├── runtime/         # 运行时目录（缓存、日志）
├── static/          # 静态资源
├── template/        # 前台模板
├── upload/          # 上传文件
├── views/           # 后台视图
├── admin.php        # 后台入口
├── api.php          # API入口
├── index.php        # 前台入口
└── data.sql         # 数据库文件
```

## 常见问题

### Q: 页面显示空白
A: 检查 PHP 版本是否 >= 8.0，查看 runtime/logs/ 下的错误日志

### Q: 数据库连接失败
A: 检查 config/config.php 中的数据库配置

### Q: 上传图片失败
A: 检查 upload 目录权限，确保 PHP 有写入权限

### Q: 采集失败
A: 检查服务器是否允许外部请求，curl 扩展是否开启

## 相关链接

- 导航站: [XPornKit成人导航](https://xpornkit.com/zh)
- 问题反馈: 提交 GitHub Issue
