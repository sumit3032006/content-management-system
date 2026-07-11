# Professional CMS — PHP 8 + MySQL + Bootstrap 5

A complete, production-style Content Management System built with PHP 8 (PDO),
MySQL, Bootstrap 5, jQuery, AJAX, and TinyMCE. Designed as a portfolio-ready
project and interview reference for full-stack web development roles.
live link:https://contentmanagementsystem.ct.ws

---

## 1. Features

- Secure authentication (bcrypt hashing, CSRF, session hardening, brute-force throttling)
- Role-based access: **Admin** (full control) and **Editor** (content only)
- Dashboard with live stats and recent activity feed
- Page management with SEO fields (slug, meta title/description) and publish states
- Blog management with categories, tags, featured images, drafts, search, pagination
- TinyMCE rich text editor on Pages and Blog Posts
- Image Manager (upload/preview/delete, type & size validation)
- Public contact form + admin inbox (search, mark as read, delete)
- Global search across pages, posts, and messages
- Site-wide settings (name, logo, favicon, contact info, social links, footer)
- Fully responsive frontend (Home, About, Services, Blog, Contact) with dynamic nav
- Security: prepared statements everywhere, CSRF tokens, output escaping, upload
  validation, `.htaccess` hardening

---

## 2. Folder Structure

```
cms/
├── admin/                 Admin panel (all protected by require_login/require_admin)
│   ├── includes/          Shared header, sidebar, footer for admin pages
│   ├── ajax/              AJAX endpoints (delete, toggle status, mark read)
│   ├── dashboard.php, pages.php, blogs.php, users.php, settings.php, ...
├── assets/
│   ├── css/               admin.css (admin panel), site.css (public site)
│   └── js/                admin.js, site.js
├── config/
│   ├── config.php         BASE_URL, upload limits, session setup
│   └── db.php             PDO connection (edit DB credentials here)
├── database/
│   └── schema.sql         Full schema + seed data (import this first)
├── includes/
│   ├── functions.php      Shared helper functions (auth, CSRF, uploads, etc.)
│   ├── site-header.php    Public site header w/ dynamic navigation
│   └── site-footer.php    Public site footer
├── install/
│   └── reset_admin_password.php   One-time helper — delete after use
├── uploads/               Uploaded images (blog/, pages/, logo/, media/)
├── index.php, about.php, services.php, blog.php, blog-single.php,
│   contact.php, page.php  Public-facing pages
└── .htaccess              Security hardening
```

---

## 3. Installation Guide (XAMPP / Local)

1. Copy the entire `cms` folder into `C:\xampp\htdocs\cms` (Windows) or
   `/Applications/XAMPP/htdocs/cms` (Mac).
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open `http://localhost/phpmyadmin`, create a new database named `cms_db`.
4. Import `database/schema.sql` into `cms_db` (Import tab → choose file → Go).
5. Open `config/db.php` and confirm the credentials match your MySQL setup
   (defaults `root` / empty password work for most XAMPP installs).
6. Open `config/config.php` and set `BASE_URL` to `http://localhost/cms`.
7. Visit `http://localhost/cms/admin/login.php` and log in with:
   - **Username:** `admin`
   - **Password:** `Admin@123`
8. If login fails (rare bcrypt cross-environment issue), visit
   `http://localhost/cms/install/reset_admin_password.php` once, then
   **delete the `install` folder**.
9. Visit `https://localhot/csm` to see the public website.

---

## 4. Deployment Guide (Hostinger / InfinityFree)

1. Create a MySQL database and user from your hosting control panel (hPanel /
   InfinityFree control panel), and note the host, database name, username,
   and password.
2. Upload the entire project folder via File Manager or FTP to `public_html`
   (or a subfolder if you want it at a sub-path).
3. Import `database/schema.sql` via phpMyAdmin on your host.
4. Edit `config/db.php` with your live database credentials.
5. Edit `config/config.php` and set `BASE_URL` to your real domain, e.g.
   `https://yourdomain.com`.
6. Visit `https://yourdomain.com/admin/login.php` and log in with the default
   credentials, then **immediately change the password** via
   Profile → Change Password.
7. Delete or restrict the `/install` folder after any one-time use.
8. Because free hosts sometimes disable `mod_rewrite` or specific PHP
   functions, if you see a blank page, temporarily set
   `ini_set('display_errors', 1);` in `config/config.php` to see the exact
   error, then revert it once fixed.

---

## 5. Default Admin Credentials

| Field    | Value        |
|----------|--------------|
| Username | `admin`      |
| Password | `Admin@123`  |

**Change this password immediately after first login** (Profile → Change
Password), especially before deploying to a live/public server.

---

## 6. Roles & Permissions

| Capability                  | Admin | Editor |
|------------------------------|:-----:|:------:|
| Create/edit pages & posts     | ✅   | ✅    |
| Upload/delete images          | ✅   | ✅    |
| View/manage contact messages  | ✅   | ✅    |
| Manage categories & tags      | ✅   | ✅    |
| Manage users                  | ✅   | ❌    |
| Change site settings          | ✅   | ❌    |

---

## 7. Security Notes

- All database queries use **PDO prepared statements** — no raw string
  concatenation, protecting against SQL injection.
- All dynamic output is passed through `e()` (an `htmlspecialchars` wrapper)
  to prevent XSS.
- Every state-changing form (login, CRUD forms, AJAX calls) includes a
  **CSRF token** validated server-side via `csrf_verify()`.
- Passwords are hashed with PHP's `password_hash()` (bcrypt) — never stored
  in plain text.
- Uploaded files are validated by extension **and** `getimagesize()` to
  reject disguised non-image files; the `uploads/` folder blocks script
  execution via `.htaccess`.
- Sessions use `httponly` and strict mode; enable `session.cookie_secure`
  once you're serving over HTTPS.

---

## 8. Feature-to-File Map (quick reference for interviews)

| Feature                    | Key files                                              |
|-----------------------------|---------------------------------------------------------|
| Auth & sessions              | `admin/login.php`, `admin/logout.php`, `includes/functions.php` |
| Dashboard stats              | `admin/dashboard.php`                                    |
| Pages CRUD + SEO             | `admin/page-add.php`, `page-edit.php`, `pages.php`        |
| Blog CRUD + categories/tags  | `admin/blog-add.php`, `blog-edit.php`, `blogs.php`, `categories.php` |
| Image manager                | `admin/images.php`, `admin/ajax/delete_image.php`         |
| Contact form (public+admin)  | `contact.php`, `admin/messages.php`                       |
| User management              | `admin/users.php`, `user-add.php`, `user-edit.php`        |
| Global search                | `admin/search.php`                                        |
| Settings                     | `admin/settings.php`                                      |
| Frontend site                | `index.php`, `about.php`, `services.php`, `blog.php`, `blog-single.php`, `contact.php` |

---

## 9. Tech Stack Summary

HTML5 • CSS3 • Bootstrap 5 • JavaScript (ES6) • jQuery • PHP 8 (PDO) • MySQL
• AJAX • TinyMCE

---

## 10. Notes for Extending This Project

- Swap `mail()`/dev-preview link in `admin/forgot-password.php` for a real
  SMTP sender (e.g. PHPMailer) before relying on password-reset emails.
- Add rate-limiting at the web-server level (e.g. mod_evasive) for extra
  brute-force protection in production.
- Consider adding automated tests (PHPUnit) for the helper functions in
  `includes/functions.php` if extending this into a larger codebase.
