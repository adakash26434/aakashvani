# आकाशवाणी - Deep Audit Report

## Last Updated: 2026-07-08

---

## Summary

| Category | Total | Status |
|----------|-------|--------|
| PHP Pages | 18 | ✅ All exist |
| CSS Classes | 100+ | ✅ All defined |
| Images | 1 | ✅ Placeholder created |
| Missing Pages | 0 | ✅ All created |
| Nav Classes | Consistent | ✅ Fixed |

---

## Phase 1: Complete Page Audit

| Page | Header | Nav Class | Footer | Status |
|------|--------|-----------|--------|--------|
| index.php | ✅ Full | .nav-link | ✅ | ✅ READY |
| news.php | ✅ Full | .nav-link | ✅ | ✅ READY |
| news-post.php | ✅ Full | .nav-link | ✅ | ✅ READY |
| about.php | ✅ Full | .nav-link | ✅ | ✅ FIXED |
| contact.php | ✅ Full | .nav-link | ✅ | ✅ FIXED |
| emergency.php | ✅ Full | .nav-link | ✅ | ✅ READY |
| gov-services.php | ✅ Full | .nav-link | ✅ | ✅ FIXED |
| ipo-tracker.php | ✅ Full | .nav-link | ✅ | ✅ READY |
| nepali-patro.php | ✅ Full | .nav-link | ✅ | ✅ READY |
| rashifal.php | ✅ Full | .nav-link | ✅ | ✅ READY |
| tools.php | ✅ Full | .nav-link | ✅ | ✅ READY |
| info-hub.php | ✅ Full | .nav-link | ✅ | ✅ FIXED |
| privacy.php | ✅ Full | .nav-link | ✅ | ✅ FIXED |
| terms.php | ✅ Full | .nav-link | ✅ | ✅ FIXED |
| login.php | ✅ (full page) | N/A | N/A | ✅ READY |
| register.php | ✅ (full page) | N/A | N/A | ✅ CREATED |
| tool.php | ✅ Full | .nav-link | ✅ | ✅ CREATED |
| test.php | ✅ Full | .nav-link | ✅ | ✅ READY |

---

## Phase 2: Data Sources

| Page | Type | Source |
|------|------|--------|
| index.php | Dynamic | Database + Live API |
| news.php | Dynamic | Database + Live API |
| news-post.php | Dynamic | Database |
| about.php | Static | Hardcoded |
| contact.php | Form | User input |
| emergency.php | Static | PHP array |
| gov-services.php | Static | PHP array |
| ipo-tracker.php | Sample | Cache/JSON |
| nepali-patro.php | Calculated | BS Date |
| rashifal.php | Static | PHP array |
| tools.php | Links | Static links |
| info-hub.php | Links | Static links |
| privacy.php | Static | Hardcoded |
| terms.php | Static | Hardcoded |
| register.php | Form | User input |
| tool.php | Dynamic | JavaScript |

---

## Phase 3: CSS Classes Used

### Header Classes
- `.site-header` - Main header wrapper
- `.header-main` - Header inner container
- `.header-grid` - Flexbox layout
- `.header-brand` / `.brand` - Logo link
- `.brand-logo` - Logo icon
- `.brand-text` - Logo text
- `.main-nav` - Navigation bar
- `.nav-list` - Nav items container
- `.nav-link` - Nav item link

### Footer Classes
- `.site-footer` - Footer wrapper
- `.footer-bottom` - Footer bottom
- `.footer-copyright` - Copyright text

### Component Classes
- `.card` - Card container
- `.card-body` - Card content
- `.badge` - Badge
- `.btn` - Button
- `.input` - Form input
- `.page-header` - Page header section
- `.section` - Content section

---

## Phase 4: Fixes Applied

### 2026-06-16 Deep Audit Fixes

1. **Nav Class Consistency**
   - Changed `.nav-item-link` → `.nav-link` on 6 pages
   - All pages now use same nav classes

2. **Header Structure**
   - All pages use `.header-grid` layout
   - All pages have full navigation
   - All pages use `.header-brand` or `.brand`

3. **Missing Files Created**
   - `register.php` - User registration
   - `tool.php` - Tool detail page with calculators
   - `assets/images/placeholder.svg` - Default image

4. **CSS Variables**
   - `.text-primary` uses `--primary` (green)
   - `.header-brand` aliased to `.brand`
   - `.brand-name` aliased

---

## All Pages: Complete Structure

```
Every page has:
├── <header class="site-header">
│   ├── .header-main
│   │   ├── .container
│   │   │   ├── .header-grid
│   │   │   │   ├── .header-brand / .brand
│   │   │   │   └── .main-nav / .header-nav
│   └── <nav class="main-nav"> (on full pages)
├── <section class="page-header"> (most pages)
├── <main> or <section>
├── <footer class="site-footer">
│   └── .container > .footer-bottom
└── <script src="/assets/js/app.js">
```

---

## Recommendations

### High Priority - DONE
- [x] Fix nav class inconsistency
- [x] Add navigation to all pages
- [x] Create missing pages

### Medium Priority - DONE
- [x] Add live weather API (weather.php)
- [x] Add live cricket scores (cricket.php)
- [x] Add government tenders page (tenders.php)

### Low Priority - DONE
- [x] Add dark mode toggle (CSS + JS)
- [x] Add PWA support (manifest.json, sw.js)
- [x] Add sitemap.xml (updated with all pages)
- [ ] Add Google Analytics

---

## New Pages Added

| Page | Description | API |
|------|-------------|-----|
| weather.php | Live weather + earthquake alerts | /api/weather-alerts.php |
| cricket.php | Live cricket scores, tabs | /api/cricket.php |
| tenders.php | Government tenders with filters | Static sample data |
| register.php | User registration page | Form only |
| tool.php | Tool detail (Tax/BMI calculators) | JavaScript |

---

## New Features

### Dark Mode
- CSS variables for dark theme
- Theme toggle button in header
- LocalStorage persistence
- System preference detection

### PWA Support
- manifest.json with app metadata
- Service worker (sw.js) for offline support
- Cache static assets
- Push notification ready

### SEO Updates
- Updated sitemap.xml with all new pages
- Full URLs with timestamps
- Correct priorities

### Navigation Updates
- Added Weather, Cricket, Tenders links to nav
- All 21 pages now accessible

---

## Security Fixes Applied (2026-07-08)

### CRITICAL — All Fixed ✅

1. **SSRF Protection in `sync-functions.php`**
   - Added `SYNC_ALLOWED_HOSTS` allowlist for all external URLs
   - Added `syncValidateUrl()` to block non-allowlisted hosts
   - All sync functions now use `syncFetch()` wrapper with validation
   - Forces HTTPS for all external fetches

2. **SSRF Protection in `api/gov-check.php`**
   - Added `GOV_ALLOWED_HOSTS` allowlist for government domains
   - `govFetch()` now validates URL against allowlist before fetching

3. **CRON_KEY Enforcement**
   - `api/sync-trigger.php` now requires CRON_KEY OR admin session
   - `api/sync-status.php` now requires CRON_KEY OR admin session
   - `api/news-expand.php` now requires CRON_KEY OR admin session
   - `api/content-overrides.php` now requires CRON_KEY OR admin session

4. **Fake Financial Data Removed**
   - `market-data.php`: `getSampleGainers()`/`getSampleLosers()` no longer returned as fallback
   - When scraping fails, returns honest `available: false` with null values
   - `scrapeNepseDetailedFromMerolagani()` returns empty arrays instead of fake data

### HIGH — All Fixed ✅

5. **Cache Directory Consolidation**
   - All API files now use `/data/cache/` (not `/cache/`)
   - `api/admin-data-manager.php` → `/data/cache/admin/`
   - `api/alerts.php` → `/data/cache/`
   - `includes/sync-functions.php` → `/data/cache/sync/`

6. **CORS Restrictions**
   - `api/admin-data-manager.php`: Restricted to specific origins
   - `api/content-overrides.php`: Restricted to specific origins
   - `api/alerts.php`: Restricted to specific origins
   - `api/panchang.php`: Restricted to specific origins
   - Note: Read-only public APIs (market-data, tax-rates, etc.) retain `*` CORS — acceptable for public data

7. **Proper Error Handling**
   - Replaced `exit`/`die` with `return` in API files for proper error handler flow
   - Affected files: `api/sync-trigger.php`, `api/sync-status.php`, `api/content-overrides.php`, `api/ai-chat.php`, `api/news-expand.php`, `api/gov-check.php`

### MEDIUM — All Fixed ✅

8. **Missing `db()` Function Added**
   - Added `db()` alias function to `config.php` — was called throughout codebase but undefined

9. **Missing `slugify()` Function Added**
   - Added `slugify()` function to `functions.php` — was called in admin dashboard but undefined

10. **Debug Page Protection**
    - Added `.htaccess` rules blocking `test.php` and `tool.php` from non-localhost access
    - Requires `?debug=KEY` parameter from remote hosts

11. **HTTP Client Consolidation**
    - `sync-functions.php` now uses `nh_fetchUrl()` from `includes/http.php`
    - Removed raw `curl_init()` scattered throughout sync functions

### Notes

- **Read-only public APIs** (market-data, tax-rates, forex, etc.) retain `Access-Control-Allow-Origin: *` — appropriate for public data
- **Core architecture** (dual DB systems, DataManager class) — deferred to future refactoring
- **Scraping monitoring** — alerting when scraping fails should be added in future
