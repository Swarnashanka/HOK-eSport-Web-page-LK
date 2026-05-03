# HOK Esports LK — Official Website

**The Official Sri Lankan Honor of Kings eSports Hub**

> "Forging Sri Lanka's Kings of Honor"

---

## 🛠 Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 8.0+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Design Theme**: Dark Fantasy + Royal (Gold, Dark Red, Black, Ancient Stone Gray)
- **Fonts**: Cinzel Decorative (headings), Poppins (body)

---

## 📁 Project Structure

```
hok-esports-lk/
├── index.php               # Homepage
├── tournaments.php          # Tournament listing & registration
├── teams.php               # Team profiles
├── players.php             # Player profiles & search
├── news.php                # News & updates
├── gallery.php             # Photo & video gallery
├── contact.php             # Contact form
├── login.php               # Player login
├── register.php            # Player registration
├── logout.php              # Logout handler
├── leaderboard.php         # Rankings
├── merchandise.php         # Shop
│
├── admin/                  # Admin Panel (RESTRICTED)
│   ├── login.php           # Admin login
│   ├── logout.php          # Admin logout
│   ├── index.php           # Dashboard
│   ├── tournaments.php     # Manage tournaments
│   ├── teams.php           # Manage teams
│   ├── players.php         # Manage players
│   ├── matches.php         # Manage match results
│   ├── news.php            # Manage news articles
│   ├── gallery.php         # Manage gallery
│   ├── merchandise.php     # Manage shop products
│   ├── sponsors.php        # Manage sponsors
│   ├── messages.php        # Contact messages inbox
│   ├── registrations.php   # Tournament registration approvals
│   ├── admins.php          # Admin user management
│   ├── settings.php        # Site settings & social links
│   └── includes/
│       ├── admin-header.php
│       └── admin-footer.php
│
├── includes/               # Shared PHP includes
│   ├── config.php          # Database & site configuration
│   ├── db.php              # Database connection & helpers
│   ├── auth.php            # Player & admin authentication
│   ├── header.php          # Site header & navbar
│   └── footer.php          # Site footer
│
├── assets/
│   ├── css/style.css       # Main stylesheet
│   └── js/main.js          # Main JavaScript
│
├── uploads/                # User-uploaded files (auto-created)
│   ├── teams/
│   ├── players/
│   ├── news/
│   ├── gallery/
│   ├── tournaments/
│   ├── merchandise/
│   └── sponsors/
│
└── sql/
    └── database.sql        # Complete database schema + sample data
```

---

## 🚀 Installation Guide

### Step 1: Requirements

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- mod_rewrite enabled (Apache)

### Step 2: Set Up the Database

1. Create a MySQL database named `hok_esports_lk`
2. Import the database:
   ```bash
   mysql -u root -p hok_esports_lk < sql/database.sql
   ```

### Step 3: Configure the Site

Edit **`includes/config.php`**:

```php
define('DB_HOST', 'localhost');      // Your database host
define('DB_USER', 'root');           // Your database username
define('DB_PASS', '');               // Your database password
define('DB_NAME', 'hok_esports_lk'); // Your database name

define('SITE_URL', 'http://localhost/hok-esports-lk'); // Your site URL
```

### Step 4: Set File Permissions

```bash
chmod 755 uploads/
chmod 755 uploads/teams/
chmod 755 uploads/players/
chmod 755 uploads/news/
chmod 755 uploads/gallery/
chmod 755 uploads/tournaments/
chmod 755 uploads/merchandise/
chmod 755 uploads/sponsors/
```

### Step 5: Access the Site

- **Website**: `http://localhost/hok-esports-lk/`
- **Admin Panel**: `http://localhost/hok-esports-lk/admin/`

---

## 🔐 Default Admin Credentials

| Field | Value |
|-------|-------|
| **Username** | `admin` |
| **Password** | `Admin@123` |

> ⚠️ **IMPORTANT**: Change the admin password immediately after first login!  
> Go to: Admin Panel → Settings → Change Admin Password

---

## 🔑 Admin Panel Features

| Section | Features |
|---------|----------|
| **Dashboard** | Stats overview, recent registrations, messages, news |
| **Tournaments** | Create, edit, delete tournaments with all details |
| **Teams** | Manage team profiles, logos, stats, achievements |
| **Players** | Edit player profiles, stats, ranks, team assignments |
| **Matches** | Record match results, scores, stages |
| **News** | Full article editor with categories, featured images |
| **Gallery** | Upload photos, embed YouTube/Facebook videos |
| **Merchandise** | Add/edit products with images, prices, stock |
| **Sponsors** | Manage sponsor listings with tiers |
| **Messages** | Inbox for contact form submissions |
| **Registrations** | Approve/reject tournament registrations |
| **Admins** | Create new admin users with roles |
| **Settings** | Site name, tagline, social links, email, maintenance mode |

---

## 🌐 Deploying to Production / cPanel

1. Upload all files via FTP to `public_html/hok-esports-lk/`
2. Create a MySQL database in cPanel → MySQL Databases
3. Import `sql/database.sql` via phpMyAdmin
4. Update `includes/config.php` with your live credentials:
   ```php
   define('SITE_URL', 'https://yourdomain.com');
   define('DB_USER', 'your_cpanel_db_user');
   define('DB_PASS', 'your_db_password');
   ```
5. Set folder permissions for `uploads/` to `755`
6. Done! Visit your domain.

---

## 📦 Uploading to GitHub

```bash
# Initialize repository
git init
git add .
git commit -m "Initial commit: HOK Esports LK website"

# Add your GitHub repo as remote
git remote add origin https://github.com/yourusername/hok-esports-lk.git

# Push
git push -u origin main
```

**Note**: Add a `.gitignore` file to exclude sensitive files:
```
includes/config.php
uploads/
*.env
```

---

## 🎨 Design Theme

| Element | Value |
|---------|-------|
| Primary Gold | `#C8A951` |
| Dark Red | `#8B0000` |
| Background | `#0A0A0F` |
| Stone Gray | `#2A2A3A` |
| Heading Font | Cinzel Decorative |
| Body Font | Poppins |

---

## 📱 Features

- ✅ Fully responsive (mobile-first)
- ✅ Player registration & login system
- ✅ Tournament registration with approval workflow
- ✅ Complete admin panel — modify everything
- ✅ Rankings/leaderboard system
- ✅ News & updates with categories
- ✅ Photo gallery + YouTube/Facebook video embeds
- ✅ Contact form with message inbox
- ✅ Merchandise/shop page
- ✅ Sponsor management
- ✅ Match results tracking
- ✅ Dark fantasy animated UI with particles
- ✅ SEO-ready meta tags

---

## 📞 Support

HOK Esports LK Team  
📧 info@hokesportslk.com  
🌐 hokesportslk.com

---

*Honor of Kings is a trademark of TiMi Studio Group / Tencent. This is a community fan site.*
