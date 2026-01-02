# 🍌 BananaCMS

<div align="right">
  <strong>English</strong> | <a href="README.md">中文</a>
</div>

Lightweight video content management system, built with native PHP, framework-free, ready to use.

## ✨ Core Features

- 🚀 **Lightweight & Efficient** - Native PHP 8.0+, no framework dependencies, excellent performance
- 📦 **Ready to Use** - Installation wizard, deploy online in 5 minutes
- 🎬 **Smart Collection** - Connect to resource site APIs, automatic collection, scheduled tasks
- 📱 **Short Videos/Series** - Vertical sliding playback, episode management, random recommendations
- 💬 **Advanced Comments** - Nested replies, like/dislike, sensitive word filtering, comment moderation
- ⭐ **Rating System** - 1-10 point rating, statistical distribution, rating trends
- 📢 **Ad System** - 10 ad positions, 4 ad types, click statistics
- 📊 **Data Statistics** - UV/PV trends, source analysis, popular rankings, real-time monitoring
- 🔌 **Complete API** - 40+ RESTful interfaces, Token authentication, APP development support
- ☁️ **Cloud Storage** - Supports Cloudflare R2, local/cloud dual storage
- ⚡ **Redis Cache** - High-speed cache and Session, File/Redis dual drivers
- 🔒 **Security Protection** - SQL injection/XSS/CSRF protection, admin IP obfuscation
- 🎨 **Template System** - Custom tags, 5 URL modes, SEO optimization
- 🔄 **Video Transcoding** - FFmpeg transcoding, progress tracking, transcoding ads

## 📋 Requirements

- PHP 8.0+
- MySQL 5.7+
- PDO Extension

Optional:
- Redis Extension (high-speed cache)
- cURL Extension (collection/cloud storage)

## 🚀 Quick Installation

1. Download code to website directory
2. Visit `http://yourdomain.com/install.php`
3. Follow wizard to fill in database information
4. Done! Visit `/admin.php` to enter backend

## 📁 Directory Structure

```
├── index.php           # Frontend entry
├── admin.php           # Backend entry
├── api.php             # API entry
├── install.php         # Installation wizard
├── cron.php            # Scheduled tasks
├── config/             # Configuration files
├── core/               # Core libraries
├── models/             # Data models
├── controllers/        # Controllers
├── views/              # Backend views
├── template/           # Frontend templates
├── static/             # Static resources
├── upload/             # Upload directory
└── runtime/            # Cache/logs
```

## 🎯 Feature List

### Frontend Features
- ✅ **Homepage System** - Recommended/latest/popular videos, category aggregation, carousel
- ✅ **Video System** - Category filtering, detail playback, auto-play next, playback history
- ✅ **Short Videos/Series** - Vertical sliding, episode management, random recommendations, category browsing
- ✅ **Actor System** - Actor list, actor details, work showcase, actor search
- ✅ **Article System** - Article list, article details, article categories, article search
- ✅ **Search Function** - Site-wide search, popular searches, search suggestions, search history
- ✅ **User System** - Registration/login, personal center, profile modification, password change
- ✅ **Interactive Features** - Comment replies, video ratings, like/dislike, favorites/history
- ✅ **Friendly Links** - Link display, link application, link categories
- ✅ **Single Pages** - About us, contact, disclaimer, custom pages

### Backend Features
- ✅ **Dashboard** - Data overview, daily statistics, system status, quick operations
- ✅ **Video Management** - Video CRUD, batch operations, play source management, video locking
- ✅ **Category Management** - Tree structure, category CRUD, batch operations, category sorting
- ✅ **Actor Management** - Actor information, work association, actor statistics, batch import
- ✅ **Article Management** - Article publishing, editor, article categories, article statistics
- ✅ **User Management** - User list, user editing, user disable, user statistics
- ✅ **Collection Management** - Collection site configuration, collection tasks, collection logs, scheduled collection
- ✅ **Ad Management** - Ad position configuration, ad placement, click statistics, ad sorting
- ✅ **Comment Management** - Comment moderation, comment deletion, sensitive word configuration, comment statistics
- ✅ **Short Video Management** - Short video/series management, episode management, status control
- ✅ **Data Statistics** - UV/PV trends, source analysis, popular rankings, data export
- ✅ **Transcoding Management** - Transcoding tasks, transcoding progress, transcoding ads, transcoding statistics
- ✅ **Link Management** - Link moderation, link checking, link categories, link statistics
- ✅ **Operation Logs** - Admin operations, log queries, log cleanup, security audit
- ✅ **System Configuration** - Site information, SEO configuration, cache configuration, storage configuration
- ✅ **Player Management** - Player configuration, player enable, player statistics
- ✅ **Page Management** - Page creation, page editing, page deletion, page sorting

### Technical Features
- ✅ **SEO Optimization** - Sitemap generation, robots configuration, custom URLs, meta tags
- ✅ **5 URL Modes** - Supports slug rewrite, custom rules, SEO friendly
- ✅ **Dual Cache Drivers** - File cache/Redis cache, cache preheating, cache cleanup
- ✅ **Dual Storage Drivers** - Local storage/Cloudflare R2, file management, CDN acceleration
- ✅ **Security Protection** - CSRF/XSS/SQL injection protection, IP obfuscation, operation audit
- ✅ **RESTful API** - 40+ interfaces, Token authentication, APP support, API documentation
- ✅ **Template System** - Custom tags, template compilation, variable rendering, template cache
- ✅ **Multi-language Support** - Chinese/English interface, multi-language templates, internationalization configuration

## 📥 Collection Feature

Supports connecting to resource site APIs (JSON/XML), automatic video collection.

```bash
# Scheduled collection (Crontab)
0 * * * * php /www/site/cron.php collect --hours=6
```

### Resource Site Partnership

> 📢 Resource site partnership contact: Telegram [@ddys_io](https://t.me/ddys_io)

## 🔧 Configuration

Edit `config/config.php`:

```php
// Cache driver (file or redis)
define('CACHE_DRIVER', 'file');

// Storage driver (local or r2)
define('STORAGE_DRIVER', 'local');

// Redis configuration (optional)
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);

// Cloudflare R2 configuration (optional)
define('R2_ACCOUNT_ID', '');
define('R2_ACCESS_KEY_ID', '');
define('R2_SECRET_ACCESS_KEY', '');
define('R2_BUCKET', '');
define('R2_PUBLIC_URL', '');
```

## 🌐 URL Rewrite Configuration

### Nginx Configuration (BT Panel)

Add to BT Panel Site Settings → URL Rewrite:

```nginx
# Sitemap
location = /sitemap.xml {
    rewrite ^ /sitemap.php last;
}

# Block sensitive directories
location ~ ^/(config|core|models|controllers|views|runtime)/ {
    deny all;
}

# Frontend rewrite
location / {
    try_files $uri $uri/ /index.php?s=$uri&$args;
}
```

### Apache Configuration

Create `.htaccess` file:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Sitemap
    RewriteRule ^sitemap\.xml$ sitemap.php [QSA,L]

    # Frontend routing
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?s=$1 [QSA,L]
</IfModule>

# Block sensitive directories
<FilesMatch "^(config|core|models|controllers|views|runtime)">
    Order deny,allow
    Deny from all
</FilesMatch>
```

### URL Mode Description

The system supports 5 URL modes, configurable in backend:

1. **Mode 1**: `/vod/detail/123` (ID mode)
2. **Mode 2**: `/vod/123.html` (ID+HTML)
3. **Mode 3**: `/video/123` (Custom prefix)
4. **Mode 4**: `/video/movie-name` (Slug without suffix)
5. **Mode 5**: `/video/movie-name.html` (Slug+HTML)

> Complete configuration files in `伪静态/` directory

## 📱 API Documentation

Base URL: `/api.php`

Response format: `{ "code": 0, "msg": "success", "data": {...} }`

### Authentication

For interfaces requiring login (marked with 🔐), include Token in request header:
```
X-Token: {token}
```

### Interface List

**System**
| Interface | Description |
|-----------|-------------|
| `?action=config` | Get site configuration |
| `?action=init` | APP initialization |
| `?action=home` | Homepage data |

**User**
| Interface | Parameters | Description |
|-----------|------------|-------------|
| `?action=user.register` | username, password, email | Register |
| `?action=user.login` | username, password | Login |
| `?action=user.info` | - | User info 🔐 |
| `?action=user.update` | nickname, avatar | Update profile 🔐 |
| `?action=user.password` | old_password, new_password | Change password 🔐 |

**Video**
| Interface | Parameters | Description |
|-----------|------------|-------------|
| `?action=vod.list` | page, limit, type, order | Video list |
| `?action=vod.detail` | id | Video details |
| `?action=vod.play` | id, sid, nid | Play URL |
| `?action=vod.related` | id, limit | Related recommendations |

**Category/Actor/Article**
| Interface | Description |
|-----------|-------------|
| `?action=type.list` | Category tree |
| `?action=actor.list` | Actor list |
| `?action=actor.detail&id=` | Actor details |
| `?action=art.list` | Article list |
| `?action=art.detail&id=` | Article details |

**Search**
| Interface | Parameters | Description |
|-----------|------------|-------------|
| `?action=search` | wd, page, type | Search |
| `?action=search.hot` | limit | Hot searches |
| `?action=search.suggest` | wd | Search suggestions |

**Favorites 🔐**
| Interface | Parameters | Description |
|-----------|------------|-------------|
| `?action=favorite.list` | page | Favorites list |
| `?action=favorite.add` | vod_id | Add favorite |
| `?action=favorite.remove` | vod_id | Remove favorite |
| `?action=favorite.check` | vod_id | Check favorite |

**History 🔐**
| Interface | Parameters | Description |
|-----------|------------|-------------|
| `?action=history.list` | page | History list |
| `?action=history.add` | vod_id, sid, nid, progress | Add history |
| `?action=history.remove` | vod_id | Delete history |
| `?action=history.clear` | - | Clear history |

**Comments**
| Interface | Parameters | Description |
|-----------|------------|-------------|
| `?action=comment.list` | type, id, page | Comment list |
| `?action=comment.post` | type, target_id, content | Post comment 🔐 |
| `?action=comment.vote` | id, action | Like/dislike |

**Rating**
| Interface | Parameters | Description |
|-----------|------------|-------------|
| `?action=score.submit` | type, target_id, score | Submit rating |
| `?action=score.stats` | type, id | Rating statistics |

**Short Videos**
| Interface | Parameters | Description |
|-----------|------------|-------------|
| `?action=short.list` | page, type | List |
| `?action=short.detail` | id | Details |
| `?action=short.like` | id | Like |

**Ads**
| Interface | Parameters | Description |
|-----------|------------|-------------|
| `?action=ad.get` | position | Get ad |
| `?action=ad.click` | id | Record click |

### Error Codes

| code | Description |
|------|-------------|
| 0 | Success |
| 1 | General error |
| 401 | Not logged in/Token expired |

## 🛠 Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP 8.0+ (Native) |
| Database | MySQL 5.7+ |
| Frontend | Tailwind CSS |
| Cache | File / Redis |
| Storage | Local / Cloudflare R2 |

## 📊 Project Statistics

| Type | Count | Description |
|------|-------|-------------|
| Core Libraries | 11 | Database, Router, Cache, Template, etc. |
| Data Models | 22 | Complete models for Vod, User, Comment, Score, etc. |
| Frontend Controllers | 12 | Homepage, video, user and other functional controllers |
| Backend Controllers | 22 | Complete backend management functions |
| API Interfaces | 40+ | Complete RESTful API |
| Template Tags | 10+ | Custom template tag system |
| Database Tables | 25+ | Complete database structure |
| Lines of Code | 50000+ | High-quality PHP code |

## 📢 Community

- **Telegram Channel**: [@BananaCMS](https://t.me/BananaCMS)
- **GitHub**: [BananaCMS](https://github.com/bananacms/bananacms)
- **Official Website**: [https://bananacms.com](https://bananacms.com)

## 📄 License

MIT License - Free to use, commercial friendly

---

**BananaCMS** - Lighter than Apple, Sweeter than Orange 🍌

Powered by [XPornKit](https://xpornkit.com)