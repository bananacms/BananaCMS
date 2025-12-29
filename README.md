# 🍌 BananaCMS

轻量级影视内容管理系统，原生 PHP 开发，无框架依赖，开箱即用。

## ✨ 特性

- 🚀 **轻量高效** - 原生 PHP，无框架依赖，性能优异
- 📦 **开箱即用** - 安装向导，5分钟部署上线
- 🎬 **视频采集** - 对接资源站 API，自动采集入库
- 📱 **短视频/短剧** - 竖屏滑动播放，剧集管理
- 💬 **评论系统** - 楼中楼回复，点赞踩，敏感词过滤
- ⭐ **评分系统** - 1-10分评分，统计分布
- 📢 **广告系统** - 10个广告位，4种广告类型
- 📊 **数据统计** - UV/PV趋势，来源分析，热门排行
- 🔌 **完整API** - RESTful 接口，支持 APP 开发
- ☁️ **云存储** - 支持 Cloudflare R2（可选）
- ⚡ **Redis缓存** - 高速缓存和Session（可选）

## 📋 环境要求

- PHP 8.0+
- MySQL 5.7+
- PDO 扩展

可选：
- Redis 扩展（高速缓存）
- cURL 扩展（采集/云存储）

## 🚀 快速安装

1. 下载代码到网站目录
2. 访问 `http://你的域名/install.php`
3. 按向导填写数据库信息
4. 完成！访问 `/admin.php` 进入后台

## 📁 目录结构

```
├── index.php           # 前台入口
├── admin.php           # 后台入口
├── api.php             # API入口
├── install.php         # 安装向导
├── cron.php            # 定时任务
├── config/             # 配置文件
├── core/               # 核心类库
├── models/             # 数据模型
├── controllers/        # 控制器
├── views/              # 后台视图
├── template/           # 前台模板
├── static/             # 静态资源
├── upload/             # 上传目录
└── runtime/            # 缓存/日志
```


## 🎯 功能清单

### 前台功能
- ✅ 首页（推荐/最新/热门）
- ✅ 视频分类、筛选
- ✅ 视频详情、播放
- ✅ 自动连播下一集
- ✅ 演员列表/详情
- ✅ 文章列表/详情
- ✅ 搜索功能
- ✅ 用户注册/登录
- ✅ 短视频滑动播放
- ✅ 短剧分集播放
- ✅ 评论/回复
- ✅ 视频评分
- ✅ 友情链接

### 后台功能
- ✅ 仪表盘概览
- ✅ 视频管理
- ✅ 分类管理
- ✅ 演员管理
- ✅ 文章管理
- ✅ 用户管理
- ✅ 采集管理
- ✅ 广告管理
- ✅ 评论管理
- ✅ 短视频管理
- ✅ 数据统计
- ✅ 友链管理
- ✅ 操作日志
- ✅ 系统配置

### 技术特性
- ✅ SEO优化（sitemap/robots/自定义URL）
- ✅ 5种URL模式（支持slug伪静态）
- ✅ 文件/Redis 双缓存驱动
- ✅ 本地/Cloudflare R2 双存储驱动
- ✅ CSRF/XSS/SQL注入防护
- ✅ RESTful API（40+接口）
- ✅ Token认证（APP支持）

## 📥 采集功能

支持对接资源站 API（JSON/XML），自动采集视频。

```bash
# 定时采集（Crontab）
0 * * * * php /www/site/cron.php collect --hours=6
```

### 常用资源站

| 名称 | API地址 |
|------|---------|
| 红牛资源 | https://www.hongniuzy2.com/api.php/provide/vod/ |
| 光速资源 | https://api.guangsuapi.com/api.php/provide/vod/ |
| 量子资源 | https://cj.lziapi.com/api.php/provide/vod/ |

> 📢 资源站合作内置联系 Telegram: [@ddys_io](https://t.me/ddys_io)

## 🔧 配置说明

编辑 `config/config.php`：

```php
// 缓存驱动（file 或 redis）
define('CACHE_DRIVER', 'file');

// 存储驱动（local 或 r2）
define('STORAGE_DRIVER', 'local');

// Redis配置（可选）
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);

// Cloudflare R2配置（可选）
define('R2_ACCOUNT_ID', '');
define('R2_ACCESS_KEY_ID', '');
define('R2_SECRET_ACCESS_KEY', '');
define('R2_BUCKET', '');
define('R2_PUBLIC_URL', '');
```

## 🌐 伪静态配置

**Nginx:**
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

**Apache (.htaccess):**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [QSA,L]
```

## 📱 API 文档

基础地址: `/api.php`

返回格式: `{ "code": 0, "msg": "success", "data": {...} }`

### 认证方式

需要登录的接口（标记🔐），请在请求头携带 Token：
```
X-Token: {token}
```

### 接口列表

**系统**
| 接口 | 说明 |
|------|------|
| `?action=config` | 获取站点配置 |
| `?action=init` | APP初始化 |
| `?action=home` | 首页数据 |

**用户**
| 接口 | 参数 | 说明 |
|------|------|------|
| `?action=user.register` | username, password, email | 注册 |
| `?action=user.login` | username, password | 登录 |
| `?action=user.info` | - | 用户信息 🔐 |
| `?action=user.update` | nickname, avatar | 更新资料 🔐 |
| `?action=user.password` | old_password, new_password | 修改密码 🔐 |

**视频**
| 接口 | 参数 | 说明 |
|------|------|------|
| `?action=vod.list` | page, limit, type, order | 视频列表 |
| `?action=vod.detail` | id | 视频详情 |
| `?action=vod.play` | id, sid, nid | 播放地址 |
| `?action=vod.related` | id, limit | 相关推荐 |

**分类/演员/文章**
| 接口 | 说明 |
|------|------|
| `?action=type.list` | 分类树 |
| `?action=actor.list` | 演员列表 |
| `?action=actor.detail&id=` | 演员详情 |
| `?action=art.list` | 文章列表 |
| `?action=art.detail&id=` | 文章详情 |

**搜索**
| 接口 | 参数 | 说明 |
|------|------|------|
| `?action=search` | wd, page, type | 搜索 |
| `?action=search.hot` | limit | 热门搜索 |
| `?action=search.suggest` | wd | 搜索建议 |

**收藏 🔐**
| 接口 | 参数 | 说明 |
|------|------|------|
| `?action=favorite.list` | page | 收藏列表 |
| `?action=favorite.add` | vod_id | 添加收藏 |
| `?action=favorite.remove` | vod_id | 取消收藏 |
| `?action=favorite.check` | vod_id | 检查收藏 |

**历史 🔐**
| 接口 | 参数 | 说明 |
|------|------|------|
| `?action=history.list` | page | 历史列表 |
| `?action=history.add` | vod_id, sid, nid, progress | 添加历史 |
| `?action=history.remove` | vod_id | 删除历史 |
| `?action=history.clear` | - | 清空历史 |

**评论**
| 接口 | 参数 | 说明 |
|------|------|------|
| `?action=comment.list` | type, id, page | 评论列表 |
| `?action=comment.post` | type, target_id, content | 发表 🔐 |
| `?action=comment.vote` | id, action | 点赞/踩 |

**评分**
| 接口 | 参数 | 说明 |
|------|------|------|
| `?action=score.submit` | type, target_id, score | 提交评分 |
| `?action=score.stats` | type, id | 评分统计 |

**短视频**
| 接口 | 参数 | 说明 |
|------|------|------|
| `?action=short.list` | page, type | 列表 |
| `?action=short.detail` | id | 详情 |
| `?action=short.like` | id | 点赞 |

**广告**
| 接口 | 参数 | 说明 |
|------|------|------|
| `?action=ad.get` | position | 获取广告 |
| `?action=ad.click` | id | 记录点击 |

### 错误码

| code | 说明 |
|------|------|
| 0 | 成功 |
| 1 | 通用错误 |
| 401 | 未登录/Token过期 |

## 🛠 技术栈

| 项目 | 技术 |
|------|------|
| 后端 | PHP 8.0+ (原生) |
| 数据库 | MySQL 5.7+ |
| 前端 | Tailwind CSS |
| 缓存 | File / Redis |
| 存储 | Local / Cloudflare R2 |

## 📄 开源协议

MIT License

---

**BananaCMS** - 比苹果更轻量 🍌

Powered by [XPornKit](https://xpornkit.com)
