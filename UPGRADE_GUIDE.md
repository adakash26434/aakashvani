# आकाशवाणी — Upgrade Guide v3
**Date:** 2026-05-26

## ⚡ STEP 1 — Database (Most Important, Do First!)

Open **cPanel → phpMyAdmin → tankaadh_admin** and run:

1. Click **Import** tab
2. Select file: `sql/MASTER_INSTALL.sql`
3. Click **Go**

This creates ALL missing tables:
- `success_stories` (fixes radio/story crashes)
- `radio_stations` (fixes radio.php Fatal Error)
- `visit_places` (fixes visit-nepal.php)
- `app_notices` (fixes notices section)
- `podcasts`, `user_bookmarks`, `push_subscriptions`
- Adds `password` and `role` columns to `users`

---

## 🔐 STEP 2 — Fix Password Security

**IMPORTANT:** Your DB password was exposed in the ZIP file. Do these now:

1. **cPanel → MySQL Databases** → Change password for `tankaadh_admin` user
2. **cPanel → Environment Variables** (or Software → PHP) → Add:
   ```
   DB_PASS = your_new_password_here
   CRON_KEY = any_random_string_like_abc123xyz789
   ```
3. Upload the new `config.php` from this package (password now reads from env var)

---

## 📁 STEP 3 — Upload Fixed Files via cPanel File Manager

Upload these files to replace old ones:

| File | What Changed |
|---|---|
| `config.php` | Password via env var, security headers, session fix |
| `api/clear-cache.php` | Removed duplicate session_start() |
| `package.json` | Fixed — was wrongly set to Next.js, now PHP |
| `sql/MASTER_INSTALL.sql` | NEW — master DB installer |
| `includes/rate-limit.php` | NEW — API rate limiter |

---

## 🗑️ STEP 4 — Delete Dead Files from cPanel

These NextJS/React files are in your PHP project by mistake. Delete them:

```
next.config.mjs
next-env.d.ts
app/layout.tsx
app/page.tsx
app/globals.css
styles/globals.css
lib/utils.ts
tsconfig.json (root only)
components.json
postcss.config.mjs
pnpm-lock.yaml
hooks/use-toast.ts
hooks/use-mobile.ts
components/ui/  (entire folder — 50+ React files)
cleanup.php     (one-time script, done)
```

---

## 🔧 STEP 5 — Add Rate Limiting to API Files

In busy API files, add at the top (after config.php include):
```php
require_once __DIR__ . '/../includes/rate-limit.php';
rateLimit('market-data', 30, 60); // max 30 req/min
```

Recommended for: `api/market-data.php`, `api/news-rss.php`, `api/ai-chat.php`

---

## ✅ STEP 6 — Verify

After uploading, check these pages work:
- `/radio.php` — should no longer crash
- `/success-stories.php` — should load
- `/notices.php` — should load
- Check cPanel error_log — should be clean

---

## 🚀 Next Features to Build

1. **Earthquake alerts** — USGS API integration
2. **Job board** — Nepali companies jobs listing
3. **Price alerts** — WhatsApp/push when gold/petrol changes
4. **User dashboard** — personalized rashifal, bookmarks, portfolio
5. **LOK SEWA live results** — automated sync
