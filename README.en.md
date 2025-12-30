# 🍌 BananaCMS

<div align="right">
  <strong>English</strong> | <a href="README.md">中文</a>
</div>

Lightweight video content management system, built with native PHP, framework-free, ready to use.

## ✨ Features

- 🚀 **Lightweight & Efficient** - Native PHP, no framework dependencies, excellent performance
- 📦 **Ready to Use** - Installation wizard, deploy online in 5 minutes
- 🎬 **Video Collection** - Connect to resource site APIs, automatic collection and storage
- 📱 **Short Videos/Series** - Vertical sliding playback, episode management
- 💬 **Comment System** - Nested replies, like/dislike, sensitive word filtering
- ⭐ **Rating System** - 1-10 point rating, statistical distribution
- 📢 **Ad System** - 10 ad positions, 4 ad types
- 📊 **Data Statistics** - UV/PV trends, source analysis, popular rankings
- 🔌 **Complete API** - RESTful interface, supports APP development
- ☁️ **Cloud Storage** - Supports Cloudflare R2 (optional)
- ⚡ **Redis Cache** - High-speed cache and Session (optional)

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
- ✅ Homepage (recommended/latest/popular)
- ✅ Video categories, filtering
- ✅ Video details, playback
- ✅ Auto-play next episode
- ✅ Actor list/details
- ✅ Article list/details
- ✅ Search functionality
- ✅ User registration/login
- ✅ Short video sliding playback
- ✅ Short series episode playback
- ✅ Comments/replies
- ✅ Video rating
- ✅ Friendly links

### Backend Features
- ✅ Dashboard overview
- ✅ Video management
- ✅ Category management
- ✅ Actor management
- ✅ Article management
- ✅ User management
- ✅ Collection management
- ✅ Ad management
- ✅ Comment management
- ✅ Short video management
- ✅ Data statistics
- ✅ Link management
- ✅ Operation logs
- ✅ System configuration

### Technical Features
- ✅ SEO optimization (sitemap/robots/custom URLs)
- ✅ 5 URL modes (supports slug rewrite)
- ✅ File/Redis dual cache drivers
- ✅ Local/Cloudflare R2 dual storage drivers
- ✅ CSRF/XSS/SQL injection protection
- ✅ RESTful API (40+ interfaces)
- ✅ Token authentication (APP support)

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

**Nginx:**
```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=$1 last;
    }
}
location ~ ^/(config|core|models|controllers|views|runtime)/ {
    deny all;
}
```

**Apache (.htaccess):**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [QSA,L]
```

> Complete configuration in `伪静态/` directory

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

## 📢 Community

- Telegram Channel: [@BananaCMS](https://t.me/BananaCMS)

## 📄 License

MIT License

---

**BananaCMS** - Lighter than Apple 🍌

Powered by [XPornKit](https://xpornkit.com)