# CDM SS Manager - cPanel Deployment Guide (dev.divinemercy.my)

This guide assumes cPanel shared hosting and that the subdomain document root is already set to:

```
/home/divinemercy/dev.divinemercy.my/public
```

## 1. Directory layout

Upload the full project *outside* the document root, for example:

```
/home/divinemercy/dev.divinemercy.my/
  app/
  config/
  public/         <-- document root
  routes/
  storage/
  vendor/
  .env
  composer.json
```

## 2. Install dependencies

Via SSH:

```bash
cd /home/divinemercy/dev.divinemercy.my
composer install --no-dev --optimize-autoloader --classmap-authoritative
```

If SSH/Composer is unavailable, upload the `vendor/` directory from your local machine.
This app works fine with a manually uploaded `vendor/` folder.

## 3. Configure `.env`

Edit the project root `.env` (not inside `public/`). Minimal production values:

```
APP_ENV=production
APP_DEBUG=false

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_pass

# Optional session hardening
SESSION_SAMESITE=Lax
```

If you enable SMTP in your app, add mail settings like:

```
MAIL_HOST=mail.example.com
MAIL_PORT=465
MAIL_FROM=your@email
MAIL_FROM_NAME="CDM SS Manager"
MAIL_USER=your@email
MAIL_PASS=your_password
MAIL_ENCRYPTION=ssl
```

## 4. Permissions

Ensure the app can write to `storage/`:

```bash
# Directories
find /home/divinemercy/dev.divinemercy.my/storage -type d -exec chmod 755 {} \;

# Files
find /home/divinemercy/dev.divinemercy.my/storage -type f -exec chmod 644 {} \;
```

Restrict `.env`:

```bash
chmod 600 /home/divinemercy/dev.divinemercy.my/.env
```

## 5. HTTPS

Make sure SSL is enabled in cPanel for `dev.divinemercy.my`. If you want to force HTTPS, add to `public/.htaccess`:

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## 6. Security reminders

- Ensure default passwords are empty in `.env`:
  - `DEFAULT_USER_PASSWORD`
  - `ADMIN_DEFAULT_PASSWORD`
  - `IMPORT_DEFAULT_PASSWORD`
- Keep `.env` outside the web root.
- Keep PHP updated in cPanel.
- Back up the database before deployments.

## 7. Database setup

If this is a fresh install, import the schema:

- Use phpMyAdmin (or CLI) to run `sql/schema.sql`
- This creates required tables including `login_attempts` for rate limiting

## 8. Quick verification

- `https://dev.divinemercy.my` loads the app
- Login works
- `.env` is not accessible from the web
- Session cookies are `HttpOnly` and `Secure` (when HTTPS)
- Login rate limiting works (5 failed attempts locks for ~15 minutes)

php -r "echo password_hash('#IAMCDM40150', PASSWORD_DEFAULT);"
$2y$12$FbRYCSs0Al2E3Ya/maKXreq8myuiDLR9whyqiElK8OnHnPFSITyce

INSERT INTO users (full_name, email, password_hash, status, must_change_password)
VALUES ('Osmund Michael', 'admin@divinemercy.my', '$2y$12$FbRYCSs0Al2E3Ya/maKXreq8myuiDLR9whyqiElK8OnHnPFSITyce', 'ACTIVE', 1);

SET @uid = LAST_INSERT_ID();

INSERT INTO user_roles (user_id, role_id, assigned_by)
VALUES (@uid, (SELECT id FROM roles WHERE code = 'SYSADMIN' LIMIT 1), NULL);
