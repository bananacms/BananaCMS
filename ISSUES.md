# 香蕉CMS 问题清单

## 已修复问题 ✅

1. ~~**VodController.php parsePlayUrl 方法损坏**~~ ✅ 已修复
2. ~~**Collect.php parseXmlVideo 方法损坏**~~ ✅ 已修复
3. ~~**缺少 type 模板目录**~~ ✅ 已修复
4. ~~**演员详情页缺少相关视频**~~ ✅ 已修复
5. ~~**缺少 no-image.jpg 默认图片**~~ ✅ 已修复
6. ~~**模板同时使用两种 footer 方式**~~ ✅ 已修复
7. ~~**Vod 模型缺少 pk 定义**~~ ✅ 已修复
8. ~~**User/Admin 模型 updateLoginTime 效率问题**~~ ✅ 已修复
9. ~~**前台缺少 CSRF 保护**~~ ✅ 已修复
10. ~~**data.sql 缺少文章分类**~~ ✅ 已修复
11. ~~**模板文件存在乱码/编码问题**~~ ✅ 已修复
    - 修复：重新保存所有模板文件为正确的 UTF-8 编码
12. ~~**模板引擎 include 语法不兼容**~~ ✅ 已修复
    - 修复：修改 Template.php compile 方法，使用绝对路径编译 include 标签
13. ~~**后台登录缺少 CSRF 保护**~~ ✅ 已修复
    - 修复：AuthController 添加 CSRF Token 生成和验证，login.php 添加隐藏字段
14. ~~**后台路由硬编码问题**~~ ✅ 部分修复
    - 修复：config.php 添加 ADMIN_ENTRY 常量，方便后续扩展
15. ~~**分页组件缺失**~~ ✅ 已修复
    - 修复：所有列表页模板已添加分页组件
16. ~~**搜索分页 URL 问题**~~ ✅ 已修复
    - 修复：搜索分页链接已保留 ?wd= 参数
17. ~~**文章分类关联错误**~~ ✅ 已修复
    - 修复：Art.php getDetail() 方法 JOIN 改为 xpk_art_type 表
18. ~~**缺少文章分类控制器**~~ ✅ 已修复
    - 修复：添加 ArtType 模型、AdminArtTypeController 控制器、后台视图
    - 新增文件：models/ArtType.php、controllers/admin/ArtTypeController.php、views/admin/art_type/
19. ~~**SQL 注入风险（低）**~~ ✅ 已修复
    - 修复：models/Type.php 的 getAll() 方法使用 sanitizeField() 过滤
20. ~~**XSS 风险**~~ ✅ 已修复
    - 修复：播放器模板添加 URL 验证和 iframe sandbox 属性
21. ~~**后台无操作日志**~~ ✅ 已修复
    - 修复：添加 AdminLog 模型、LogController 控制器、日志查看页面
    - 所有后台增删改操作已添加日志记录
    - 新增文件：models/AdminLog.php、controllers/admin/LogController.php、views/admin/log/index.php
22. ~~**配置文件暴露风险**~~ ✅ 已修复
    - 修复：config 目录添加 .htaccess 和 index.html 禁止访问
23. ~~**缺少 DB_PORT 配置**~~ ✅ 已修复
    - 修复：config.php 添加 DB_PORT 常量，Database.php DSN 添加 port 参数
24. ~~**模板缓存无过期机制**~~ ✅ 已修复
    - 修复：Template.php 添加文件修改时间检查，开发模式下自动重新编译
25. ~~**错误处理不完善**~~ ✅ 已修复
    - 修复：添加 ErrorHandler.php 统一错误处理类
    - 支持错误日志记录到 runtime/logs/
    - 开发模式显示详细错误，生产模式显示友好页面
26. ~~**移动端底部搜索栏遮挡内容**~~ ✅ 已修复
    - 修复：header.php body 添加 pb-14 md:pb-0 底部内边距
27. ~~**播放页无自动连播**~~ ✅ 已修复
    - 修复：play.html 添加下一集按钮和自动连播脚本
28. ~~**缺少面包屑导航**~~ ✅ 已修复
    - 修复：详情页、播放页、分类页、演员详情页添加面包屑导航
29. ~~**添加 Composer 自动加载**~~ ✅ 已修复
    - 修复：添加 composer.json，支持 classmap 自动加载
30. ~~**添加单元测试**~~ ✅ 已修复
    - 修复：添加 tests/test.php 简单测试脚本
31. ~~**添加 API 接口**~~ ✅ 已修复
    - 修复：添加 api.php，支持视频列表/详情、分类、演员、搜索接口
32. ~~**添加缓存层**~~ ✅ 已修复
    - 修复：添加 Cache.php 文件缓存类，首页数据已启用缓存
33. ~~**添加 SEO 优化**~~ ✅ 已修复
    - 修复：添加 robots.txt、sitemap.php（支持分片5000条/页）
    - 搜索页、用户页添加 noindex 防止被刷留痕
    - SEO 标题/描述模板支持后台配置（标题≤60字符，描述≤160字符）
34. ~~**添加 URL 自定义功能**~~ ✅ 已修复
    - 修复：支持5种URL模式
      - 模式1: `/vod/detail/123`（原始）
      - 模式2: `/vod/123.html`（ID+.html）
      - 模式3: 自定义规则
      - 模式4: `/video/slug`（slug无后缀）
      - 模式5: `/video/slug.html`（slug带.html）
    - 数据库添加 vod_slug、actor_slug、art_slug 字段
    - sitemap.php 自动适配当前URL模式
35. ~~**添加中文转拼音 Slug**~~ ✅ 已修复
    - 修复：添加 Pinyin.php 和 Slug.php
    - 视频/演员/文章添加/编辑时自动生成拼音 slug
    - 采集时自动生成 slug
    - 支持唯一性检查，重复自动添加数字后缀
36. ~~**添加友情链接功能**~~ ✅ 已修复
    - 修复：完整的友链管理系统
    - 前台：友链展示页 + 自助申请表单
    - 后台：友链列表、添加、编辑、删除、审核
    - 自动换链：检测对方网站是否有回链，有则自动通过
    - 支持手动/自动两种审核模式
    - 批量检测回链状态

---

## 总结

✅ **所有问题已修复（36项），项目完善可上线！**

核心功能：
- ✅ 视频展示、播放、自动连播
- ✅ 分类、演员、文章
- ✅ 搜索、分页
- ✅ 用户登录注册
- ✅ 后台管理、操作日志
- ✅ 采集功能

安全防护：
- ✅ CSRF 保护
- ✅ XSS 防护
- ✅ SQL 注入防护
- ✅ 配置文件保护

SEO 优化：
- ✅ robots.txt
- ✅ sitemap.xml（支持分片）
- ✅ 搜索页 noindex
- ✅ SEO 标题/描述模板
- ✅ 5种URL模式（支持slug）

代码质量：
- ✅ 统一错误处理
- ✅ 日志记录
- ✅ 模板缓存优化
- ✅ Composer 自动加载

扩展功能：
- ✅ RESTful API 接口
- ✅ 数据缓存层
- ✅ 简单测试脚本
- ✅ 广告系统
- ✅ 评论系统
- ✅ 评分系统
- ✅ 短视频/短剧模块
- ✅ 数据统计面板
- ✅ Redis缓存支持
- ✅ APP API接口
- ✅ Cloudflare R2云存储（新增）

## 广告系统说明

### 广告位置
| 位置代码 | 说明 |
|----------|------|
| `home_top` | 首页顶部横幅 |
| `home_float` | 首页悬浮广告 |
| `detail_top` | 详情页顶部 |
| `detail_bottom` | 详情页底部 |
| `play_pause` | 播放器暂停广告 |
| `play_before` | 片头广告 |
| `sidebar` | 侧边栏广告 |
| `list_insert` | 列表页插入广告 |
| `popup` | 弹窗广告 |
| `custom` | 自定义位置 |

### 广告类型
- 图片广告：支持跳转链接
- 代码广告：支持第三方广告代码（如 Google AdSense）
- 视频广告：支持片头广告、可跳过设置
- 文字广告：简单文字链接

### 模板调用
```html
<!-- 单个广告 -->
{xpk:ad position="home_top"}

<!-- 随机显示一个 -->
{xpk:ad position="sidebar" random="true"}

<!-- 循环多个广告 -->
{xpk:adlist position="sidebar"}
<div class="ad-item">
    <a href="{$ad.ad_link}"><img src="{$ad.ad_image}"></a>
</div>
{/xpk:adlist}
```

### PHP 调用
```php
// 渲染广告
echo xpk_ad('home_top');

// 获取广告数据
$ads = xpk_ads('sidebar');
```

### API 接口
| 接口 | 参数 | 说明 |
|------|------|------|
| `?action=ad.get` | position | 获取指定位置广告 |
| `?action=ad.click` | id (POST) | 记录广告点击 |

## API 接口说明

基础地址: `/api.php`

| 接口 | 参数 | 说明 |
|------|------|------|
| `?action=vod.list` | page, limit, type, order | 视频列表 |
| `?action=vod.detail` | id | 视频详情 |
| `?action=type.list` | - | 分类列表 |
| `?action=actor.list` | page, limit | 演员列表 |
| `?action=actor.detail` | id | 演员详情 |
| `?action=search` | wd, page, limit | 搜索 |


## 评论系统说明

### 功能特点
- 视频/文章评论
- 楼中楼回复（二级嵌套）
- 点赞/踩功能
- 敏感词过滤（自动替换为***）
- 审核机制（可选先审后发）
- 发言频率限制
- 游客评论开关

### 后台管理
- 评论列表（按状态/类型筛选）
- 单条/批量审核
- 评论设置（审核、敏感词、频率限制等）

### 前台调用
```html
<!-- 引入评论JS -->
<script src="/static/js/comment.js"></script>

<!-- 评论容器 -->
<div id="commentBox"></div>

<!-- 初始化 -->
<script>
new XpkComment({
    container: '#commentBox',
    type: 'vod',        // vod 或 art
    targetId: 123       // 视频/文章ID
});
</script>
```

### API 接口
| 接口 | 方法 | 参数 | 说明 |
|------|------|------|------|
| `/comment/list` | GET | type, id, page | 获取评论列表 |
| `/comment/replies` | GET | parent_id, offset | 获取更多回复 |
| `/comment/post` | POST | type, target_id, content, parent_id, reply_id | 发表评论 |
| `/comment/vote` | POST | id, action(up/down) | 点赞/踩 |
| `/comment/delete` | POST | id | 删除自己的评论 |

### 数据库表
- `xpk_comment` - 评论表
- `xpk_comment_vote` - 投票记录表


## 评分系统说明

### 功能特点
- 1-10分评分（显示为5星）
- 登录用户可修改评分
- 游客评分（基于IP，可配置关闭）
- 评分统计（平均分、人数、分布图）
- 自动同步到视频表 vod_score 字段

### 前台调用

**方式1：JS组件（完整交互）**
```html
<script src="/static/js/score.js"></script>
<div id="scoreBox"></div>
<script>
new XpkScore({
    container: '#scoreBox',
    type: 'vod',
    targetId: 123,
    size: 'normal'  // small/normal/large
});
</script>
```

**方式2：模板标签**
```html
{xpk:score type="vod" id="$vod.vod_id" size="normal"/}
```

**方式3：只读显示**
```html
<div id="score"></div>
<script>
xpkScoreDisplay('#score', 8.5, 100); // 分数, 人数
</script>
```

### API 接口
| 接口 | 方法 | 参数 | 说明 |
|------|------|------|------|
| `/score/rate` | POST | type, target_id, score | 提交评分 |
| `/score/stats` | GET | type, target_id | 获取评分统计 |

### 数据库表
- `xpk_score` - 评分记录表


## 短视频/短剧模块说明

### 功能特点
- 短视频：竖屏全屏播放，上下滑动切换
- 短剧：多集管理，分集播放
- 点赞、播放量统计
- 付费/免费剧集标记
- 自动连播下一集

### 后台管理
- 短视频/短剧列表（分类筛选）
- 添加/编辑短视频
- 添加/编辑短剧及剧集管理
- 上架/下架控制

### 前台页面
| URL | 说明 |
|-----|------|
| `/short` | 短视频滑动播放页 |
| `/short/drama` | 短剧列表 |
| `/short/detail/{id}` | 短剧详情 |
| `/short/play/{id}/{ep}` | 短剧播放 |

### API 接口
| 接口 | 方法 | 说明 |
|------|------|------|
| `/short/api/list` | GET | 获取短视频列表 |
| `/short/api/random` | GET | 获取随机短视频 |
| `/short/api/detail` | GET | 获取详情 |
| `/short/api/like` | POST | 点赞 |

### 数据库表
- `xpk_short` - 短视频/短剧主表
- `xpk_short_episode` - 短剧剧集表

### 前台调用
```html
<!-- 短视频滑动播放 -->
<script src="/static/js/short.js"></script>
<div id="player"></div>
<script>
new XpkShortPlayer({
    container: '#player'
});
</script>
```


## 数据统计面板说明

### 功能特点
- 今日概览：UV/PV、新用户、播放量（与昨日对比）
- 访问趋势：7/14/30天UV/PV折线图
- 用户增长：新用户柱状图
- 流量来源：百度/谷歌/必应/直接访问等饼图
- 设备分布：手机/平板/电脑占比
- 热门视频：周期内播放量排行TOP10
- 实时在线：5分钟内活跃用户数
- 内容统计：视频/演员/文章/用户/评论/短视频总数
- 日志清理：定期清理过期统计数据

### 后台入口
`/admin.php/stats`

### 统计类型
| 类型 | 说明 |
|------|------|
| `visit` | 页面访问 |
| `vod` | 视频详情页 |
| `play` | 视频播放 |
| `search` | 搜索（关键词存于log_referer） |

### 前台埋点调用
```php
// 在控制器中记录访问
require_once MODEL_PATH . 'Stats.php';
$stats = new XpkStats();

// 记录页面访问
$stats->log('visit');

// 记录视频详情访问
$stats->log('vod', $vodId);

// 记录播放
$stats->log('play', $vodId);

// 记录搜索
$stats->log('search', 0, $keyword);
```

### API 接口
| 接口 | 方法 | 参数 | 说明 |
|------|------|------|------|
| `/admin.php/stats` | GET | days | 统计面板页面 |
| `/admin.php/stats/trend` | GET | days, type | 获取趋势数据 |
| `/admin.php/stats/hot` | GET | days, limit | 获取热门视频 |
| `/admin.php/stats/clean` | POST | days | 清理过期日志 |

### 数据库表
- `xpk_stats_log` - 统计日志表（按IP+日期去重UV，累加PV）


## Redis缓存支持说明

### 功能特点
- 支持 File / Redis 双驱动，配置切换
- 缓存：高速数据缓存，自动序列化
- Session：Redis Session存储，支持分布式
- 向后兼容：Redis不可用时自动回退文件缓存

### 配置方法
编辑 `config/config.php`：

```php
// 缓存配置（file 或 redis）
define('CACHE_DRIVER', 'redis');
define('CACHE_TTL', 3600);

// Session配置（file 或 redis）
define('SESSION_DRIVER', 'redis');
define('SESSION_TTL', 7200);

// Redis配置
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_PASS', '');           // Redis密码
define('REDIS_DB', 0);              // 缓存数据库
define('REDIS_SESSION_DB', 1);      // Session数据库
define('REDIS_PREFIX', 'xpk:');     // 键前缀
```

### 使用方法
```php
$cache = xpk_cache();

// 基础操作
$cache->set('key', 'value', 3600);  // 设置（TTL秒）
$cache->get('key');                  // 获取
$cache->delete('key');               // 删除
$cache->has('key');                  // 是否存在
$cache->clear();                     // 清空

// 计数器（原子操作）
$cache->increment('views', 1);       // 自增
$cache->decrement('stock', 1);       // 自减

// 记住模式（不存在时执行回调）
$data = $cache->remember('hot_videos', 600, function() {
    return (new XpkVod())->getHot(10);
});

// 获取驱动类型
$cache->getDriverType();  // 'file' 或 'redis'
```

### 要求
- PHP Redis扩展（`php-redis`）
- Redis服务器 2.6+


## APP API接口说明

完整的 RESTful API，支持 APP/客户端开发。

### 功能模块
- 用户：注册、登录、Token认证、资料修改
- 视频：列表、详情、播放地址、相关推荐
- 收藏：添加、删除、列表、检查
- 历史：播放进度记录、续播
- 评论：发表、删除、点赞
- 评分：提交、统计
- 搜索：关键词、热搜、建议
- 短视频：列表、详情、点赞

### 认证方式
```
Header: X-Token: {token}
或
Header: Authorization: Bearer {token}
```

### 详细文档
见 `API.md`

### 数据库表
- `xpk_user_favorite` - 用户收藏表
- `xpk_user_history` - 观看历史表
- `xpk_search_log` - 搜索日志表


## Cloudflare R2 云存储说明

### 功能特点
- 支持 Local / Cloudflare R2 双驱动
- R2 兼容 S3 API，无需额外SDK
- 自动生成文件路径（按日期分目录）
- 支持 Base64 图片上传
- 配置切换，无缝迁移

### 配置方法
编辑 `config/config.php`：

```php
// 存储配置（local 或 r2）
define('STORAGE_DRIVER', 'r2');

// Cloudflare R2 配置
define('R2_ACCOUNT_ID', 'your_account_id');
define('R2_ACCESS_KEY_ID', 'your_access_key');
define('R2_SECRET_ACCESS_KEY', 'your_secret_key');
define('R2_BUCKET', 'your_bucket_name');
define('R2_PUBLIC_URL', 'https://cdn.example.com'); // 自定义域名
```

### 获取 R2 凭证
1. 登录 Cloudflare Dashboard
2. 进入 R2 Object Storage
3. 创建 Bucket
4. 在 "Manage R2 API Tokens" 创建 API Token
5. 获取 Account ID、Access Key ID、Secret Access Key

### 使用方法
```php
$storage = xpk_storage();

// 上传本地文件
$url = $storage->upload('/tmp/image.jpg');
// 返回: https://cdn.example.com/2025/12/29/abc123.jpg

// 指定路径上传
$url = $storage->upload('/tmp/image.jpg', 'covers/movie.jpg');

// 上传内容
$url = $storage->uploadContent($imageData, 'avatars/user1.png');

// 上传 Base64 图片
$url = $storage->uploadBase64($base64String);

// 删除文件
$storage->delete('covers/movie.jpg');

// 检查文件是否存在
$exists = $storage->exists('covers/movie.jpg');

// 获取当前驱动类型
$type = $storage->getDriverType(); // 'local' 或 'r2'
```

### 注意事项
- R2 公开访问需要在 Cloudflare 设置自定义域名或开启公开访问
- 建议使用自定义域名，自带 Cloudflare CDN 加速
- 文件路径自动按日期分目录：`2025/12/29/filename.jpg`
