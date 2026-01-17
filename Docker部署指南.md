# 香蕉CMS Docker 本地开发指南

> ⚠️ **重要提示**：Docker 方式仅用于**本地开发和调试**，生产环境强烈推荐使用**宝塔面板**直接部署！

## 使用场景

### 🏠 本地开发（推荐 Docker）
- Windows/Mac 本地开发调试
- 快速搭建开发环境
- 测试功能和模板
- 学习和研究代码

### 🚀 生产环境（推荐宝塔面板）
- 性能更好，资源占用更少
- 配置更简单，管理更方便
- 支持一键部署和更新
- 完善的监控和日志
- 自动备份和恢复

**生产环境部署请参考：[部署指南.md](部署指南.md)**

---

## Docker 本地开发环境搭建

### ✅ 已修复的问题

本项目已完美兼容 Docker 环境，修复了以下问题：

- ✅ **Windows BOM 问题**：自动处理文件编码问题
- ✅ **Headers Already Sent**：使用输出缓冲机制
- ✅ **跨平台兼容**：Windows/Mac/Linux 统一体验

**直接使用即可，无需额外配置！**

## Docker 部署步骤

### 1. 创建 Dockerfile

在项目根目录创建 `Dockerfile`：

```dockerfile
FROM php:8.1-apache

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# 安装 PHP 扩展
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli zip

# 启用 Apache 模块
RUN a2enmod rewrite headers

# 配置 PHP
RUN { \
    echo 'upload_max_filesize = 100M'; \
    echo 'post_max_size = 100M'; \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 300'; \
    echo 'date.timezone = Asia/Shanghai'; \
    } > /usr/local/etc/php/conf.d/custom.ini

# 设置工作目录
WORKDIR /var/www/html

# 复制应用文件
COPY . /var/www/html/

# 设置权限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/config \
    && chmod -R 777 /var/www/html/runtime \
    && chmod -R 777 /var/www/html/upload

EXPOSE 80

CMD ["apache2-foreground"]
```

### 2. 创建 docker-compose.yml

在项目根目录创建 `docker-compose.yml`：

```yaml
version: '3.8'

services:
  # Web 服务器
  web:
    build: .
    container_name: banana-cms-web
    ports:
      - "8080:80"
    volumes:
      - ./upload:/var/www/html/upload
      - ./runtime:/var/www/html/runtime
      - ./config:/var/www/html/config
    environment:
      - TZ=Asia/Shanghai
    depends_on:
      - db
    networks:
      - banana-network
    restart: unless-stopped

  # MySQL 数据库
  db:
    image: mysql:8.0
    container_name: banana-cms-db
    environment:
      MYSQL_ROOT_PASSWORD: root123456
      MYSQL_DATABASE: banana_cms
      MYSQL_USER: banana
      MYSQL_PASSWORD: banana123
      TZ: Asia/Shanghai
    ports:
      - "3306:3306"
    volumes:
      - mysql-data:/var/lib/mysql
    command: 
      - --character-set-server=utf8mb4
      - --collation-server=utf8mb4_unicode_ci
    networks:
      - banana-network
    restart: unless-stopped

volumes:
  mysql-data:

networks:
  banana-network:
    driver: bridge
```

### 3. 启动容器

```bash
# 构建并启动容器
docker-compose up -d

# 查看容器状态
docker-compose ps

# 查看日志
docker-compose logs -f
```

### 4. 访问安装向导

打开浏览器访问：`http://localhost:8080/install.php`

### 5. 数据库配置

在安装向导中填写以下信息：

- **主机**：`db`（容器名称）
- **端口**：`3306`
- **数据库名**：`banana_cms`
- **用户名**：`banana`
- **密码**：`banana123`


## 为什么本地用 Docker？

### ✅ 优势
- **环境隔离**：不影响本机环境
- **快速搭建**：一键启动完整环境
- **跨平台**：Windows/Mac/Linux 统一
- **易于清理**：删除容器即可

### ❌ 不适合生产
- **性能损耗**：虚拟化有性能开销
- **资源占用**：内存和磁盘占用较大
- **管理复杂**：需要 Docker 知识
- **维护成本**：更新和备份较麻烦

---

## 生产环境推荐方案

### 宝塔面板部署（强烈推荐）

**为什么选择宝塔？**
1. **一键部署**：可视化界面，5分钟上线
2. **性能优异**：原生运行，无虚拟化损耗
3. **管理方便**：网站、数据库、SSL 证书一站式管理
4. **自动备份**：定时备份数据库和文件
5. **监控告警**：实时监控服务器状态
6. **安全防护**：防火墙、防 CC 攻击

**宝塔部署步骤：**

1. 安装宝塔面板（CentOS 示例）：
```bash
yum install -y wget && wget -O install.sh https://download.bt.cn/install/install_6.0.sh && sh install.sh
```

2. 登录宝塔面板，安装环境：
   - PHP 8.0+
   - MySQL 5.7+
   - Nginx 或 Apache

3. 创建网站：
   - 添加站点，绑定域名
   - 上传代码到网站目录
   - 设置伪静态规则（系统自动生成）

4. 创建数据库：
   - 创建数据库和用户
   - 记录数据库信息

5. 访问安装向导：
   - 访问 `http://你的域名/install.php`
   - 填写数据库信息完成安装

6. 配置 SSL 证书（可选）：
   - 宝塔面板一键申请 Let's Encrypt 证书
   - 自动配置 HTTPS

**详细教程：** [部署指南.md](部署指南.md)

---

## Docker 常用命令

```bash
# 启动容器
docker-compose up -d

# 停止容器
docker-compose stop

# 重启容器
docker-compose restart

# 查看日志
docker-compose logs -f web

# 进入容器
docker exec -it banana-cms-web bash

# 删除容器（保留数据）
docker-compose down

# 删除容器和数据
docker-compose down -v
```

---

## 常见问题

### Q1: Docker 环境已经修复了什么问题？

**A:** 本项目已修复 Windows Docker 环境下的 "headers already sent" 错误。通过输出缓冲机制，即使文件包含 BOM 标记也能正常运行，无需手动处理文件编码。

### Q2: 需要手动删除 BOM 吗？

**A:** 不需要。代码已经自动处理了这个问题，直接使用即可。

### Q3: 如何查看容器日志？

**A:** 使用命令 `docker-compose logs -f web` 查看 Web 容器日志，或 `docker-compose logs -f db` 查看数据库日志。

### Q4: 如何备份数据？

**A:** 
```bash
# 备份数据库
docker exec banana-cms-db mysqldump -u banana -pbanana123 banana_cms > backup.sql

# 备份上传文件
tar -czf upload_backup.tar.gz ./upload
```

### Q5: 如何修改端口？

**A:** 编辑 `docker-compose.yml` 文件中的 `ports` 配置：
```yaml
ports:
  - "8080:80"  # 改为你想要的端口，如 "80:80"
```

### Q6: 安装完成后如何访问？

**A:** 
- 前台：`http://localhost:8080/`
- 后台：`http://localhost:8080/admin`（或您自定义的入口）

### Q7: 生产环境可以用 Docker 吗？

**A:** 不推荐。Docker 适合本地开发，生产环境建议用宝塔面板：
- 性能更好（无虚拟化损耗）
- 管理更简单（可视化界面）
- 维护更方便（一键备份恢复）
- 成本更低（资源占用少）

### Q8: 如何从 Docker 迁移到宝塔？

**A:** 
1. 导出 Docker 数据库：`docker exec banana-cms-db mysqldump -u banana -pbanana123 banana_cms > backup.sql`
2. 复制 upload 目录
3. 在宝塔服务器导入数据库和文件
4. 修改 `config/config.php` 数据库配置

---

## 本地开发建议

## 本地开发建议

### 1. 使用代码编辑器

推荐使用 VS Code 或 PhpStorm，避免使用记事本编辑 PHP 文件。

### 2. 文件同步

使用 volumes 挂载，修改代码后自动同步到容器：

```yaml
volumes:
  - .:/var/www/html  # 挂载整个项目目录
```

### 3. 调试配置

在 `config/config.php` 中启用调试模式：

```php
define('APP_DEBUG', true);
```

---

## 性能优化（可选）

### 1. 启用 Redis 缓存

添加 Redis 服务到 `docker-compose.yml`：

```yaml
services:
  redis:
    image: redis:7-alpine
    container_name: banana-cms-redis
    ports:
      - "6379:6379"
    networks:
      - banana-network
    restart: unless-stopped
```

在 `config/config.php` 中配置：

```php
define('CACHE_DRIVER', 'redis');
define('SESSION_DRIVER', 'redis');
define('REDIS_HOST', 'redis');
define('REDIS_PORT', 6379);
```

### 2. 配置 OPcache

在 Dockerfile 中添加 OPcache 配置：

```dockerfile
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=60'; \
    } >> /usr/local/etc/php/conf.d/custom.ini
```

## 技术支持

**本地开发问题：**
- GitHub Issues: 提交问题和建议
- Telegram: [@BananaCMS](https://t.me/BananaCMS)

**生产环境部署：**
- 请参考 [部署指南.md](部署指南.md)
- 推荐使用宝塔面板部署

---

**重要提醒**：
- ✅ Docker 适合本地开发调试
- ❌ Docker 不适合生产环境
- 🚀 生产环境请使用宝塔面板

本项目已修复 Docker 环境下的 "headers already sent" 问题，可直接使用。