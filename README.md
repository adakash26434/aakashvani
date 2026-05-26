# Aakashvani

This repository contains the Aakashvani project.

## Setup

1. Copy `config.example.php` to `config.php`.
2. Set environment variables for sensitive values if possible:
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
   - `ADMIN_PASS`
   - `OPENAI_API_KEY`
   - `CRON_KEY`
   - `SITE_URL`
   - `SITE_EMAIL`

3. Make sure `data/cache`, `uploads`, and `assets` are writable by the web server.

## Security and Cleanup

- `config.php` is now ignored by git and should stay local to each deployment.
- Cached data files under `data/cache/` are also ignored.
- `uploads/` is ignored for uploaded assets, but `.htaccess` files remain tracked for directory protection.

## Important Notes

- Do not commit plain secrets or passwords to git.
- Use `config.example.php` as the template for creating `config.php`.
- The admin password may be provided via `ADMIN_PASS` environment variable.

## Recent fixes applied

- Fixed a parse error in `admin/admin-podcasts.php`.
- Removed deprecated `${var}` interpolation in `includes/data-manager.php`.
- Added `.gitignore` to exclude local config and generated cache.
- Removed tracked `config.php` and cached JSON files from git index.

## Push/deploy

To deploy the current branch:

```bash
git pull origin main
php -l **/*.php
```

If you want to reset a deployment completely, re-clone the repository and then copy your local `config.php` from a secure source.
