# 🍌 BananaCMS

<div align="center">

![GitHub stars](https://img.shields.io/github/stars/bananacms/BananaCMS?style=flat-square&logo=github)
![GitHub forks](https://img.shields.io/github/forks/bananacms/BananaCMS?style=flat-square&logo=github)
![GitHub issues](https://img.shields.io/github/issues/bananacms/BananaCMS?style=flat-square&logo=github)
![GitHub license](https://img.shields.io/github/license/bananacms/BananaCMS?style=flat-square)
![PHP Version](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL Version](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)

</div>

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
3. Follow wizard to fill in database info, admin account, and custom backend entry path
4. Done! Visit your custom backend path to enter admin panel

## 📁 Directory Structure

```
├── index.php           # Frontend entry
├── admin.php           # Backend entry (customizable during installation)
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

> The config file `config/config.php` is auto-generated during installation with database connection and site info. Below are optional advanced configurations.

### Cache Configuration

```php
// Cache driver (file or redis)
define('CACHE_DRIVER', 'file');

// Session driver (file or redis)
define('SESSION_DRIVER', 'file');
```

### Redis Configuration (Optional)

```php
// Redis config (effective when CACHE_DRIVER or SESSION_DRIVER is redis)
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_PASS', '');           // Redis password, leave empty if none
define('REDIS_DB', 0);              // Database for cache
define('REDIS_SESSION_DB', 1);      // Database for session
define('REDIS_PREFIX', 'xpk:');     // Cache key prefix
```

### Cloudflare R2 Configuration (Optional)

```php
// Storage driver (local or r2)
define('STORAGE_DRIVER', 'local');

// Cloudflare R2 config (effective when STORAGE_DRIVER is r2)
define('R2_ACCOUNT_ID', '');        // Cloudflare Account ID
define('R2_ACCESS_KEY_ID', '');     // R2 Access Key ID
define('R2_SECRET_ACCESS_KEY', ''); // R2 Secret Access Key
define('R2_BUCKET', '');            // Bucket name
define('R2_PUBLIC_URL', '');        // Public access domain
```

## 🌐 URL Rewrite Configuration

> 💡 After installation, the system will automatically generate rewrite rules based on your server type and admin entry path. Just copy and use them.

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

# Static resources
location /static/ {
    try_files $uri =404;
}

location /upload/ {
    try_files $uri =404;
}

# All requests handled by index.php
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

    # All requests handled by index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?s=$1 [QSA,L]
</IfModule>

<IfModule mod_negotiation.c>
    Options -MultiViews
</IfModule>

AcceptPathInfo On

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
| API Interfaces | 50+ | Complete RESTful API |
| Template Tags | 10+ | Custom template tag system |
| Database Tables | 25+ | Complete database structure |
| Lines of Code | 48000+ | PHP/HTML/JS/CSS code |

## 📢 Community

- **Telegram**: [@BananaCMS](https://t.me/BananaCMS)
- **GitHub**: [BananaCMS](https://github.com/bananacms/BananaCMS)

## 📄 License

MIT License - Free to use, commercial friendly

---

**BananaCMS** - Lighter than Apple, Sweeter than Orange 🍌

Powered by [XPornKit](https://xpornkit.com)