# आकाशवाणी — Comprehensive Project Audit Report
**Date:** July 8, 2026  
**Project:** Aakashvani (आकाशवाणी) — Nepal Information Portal  
**Type:** PHP-based News/Financial Portal with Admin Dashboard, API Layer, and PWA Support  

---

## Taste Rating

🟡 **Acceptable** — The project is functional, well-organized, and shows good security instincts. However, it has significant architectural drift, some security gaps, and code quality issues that need addressing before this can be considered production-grade.

---

## Executive Summary

| Category | Status | Notes |
|---|---|---|
| **Security** | 🟡 Medium Risk | CSRF, auth, and rate-limiting exist but with gaps |
| **Code Quality** | 🟡 Needs Work | Inconsistent patterns, dead code, dual db() systems |
| **Architecture** | 🟡 Fragmented | Two parallel DB systems (PDO in config.php vs DataManager class) |
| **Performance** | 🟢 Good | Caching layer, lazy loading, CDN fonts |
| **Data Integrity** | 🟡 At Risk | Web scraping without fallback, hardcoded sample data |
| **Documentation** | 🟢 Good | ARCHITECTURE.md, AUDIT.md, README all exist |

---

## 1. Security Analysis

### ✅ What's Done Well

- **CSRF Protection** (`includes/csrf.php`): Uses `hash_equals()` for timing-safe token comparison. Token generated with `random_bytes(32)`. `csrfRequire()` for API endpoints.
- **Prepared Statements**: All database queries in `functions.php` and `auth.php` use PDO prepared statements — no raw SQL interpolation.
- **Password Hashing**: Uses `password_hash()` / `password_verify()` (PASSWORD_DEFAULT) — good.
- **Security Headers**: `sendSecurityHeaders()` in `functions.php` sets X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy.
- **Rate Limiting**: File-based rate limiting in both `functions.php` (global) and `includes/rate-limit.php` (per-API). Properly returns 429 with Retry-After header.
- **Input Sanitization**: `sanitize()` function uses `htmlspecialchars()` with ENT_QUOTES and UTF-8.
- **Error Handling**: Centralized error handler (`includes/error-handler.php`) that suppresses errors in production, logs them, and returns generic error messages to users.
- **Session Management**: Sessions started properly with `PHP_SESSION_NONE` check. Admin auth uses dedicated `requireAdmin()` pattern.
- **CORS**: API endpoints explicitly set allowed origins.

### ⚠️ Security Gaps

#### 🔴 HIGH — SSRF Risk in `sync-functions.php`

```php
// sync-functions.php lines 54-60, 82-88, 109-115, 136-142, etc.
function syncNepse(): bool {
    $url = 'https://nepalstock.com.np';
    $ch = curl_init($url);
    // ...
}
```

All sync functions fetch external URLs with **no validation**. An attacker who compromises the sync mechanism (or if these cron jobs run with elevated trust) could be redirected to internal/cloud metadata endpoints (e.g., `http://169.254.169.254/`), SSRF targets, or internal services.

**Recommendation**: Validate all external URLs against an allowlist. Never accept URLs from user input or untrusted sources. If cron-triggered, use a secret CRON_KEY (already documented in README) but it's not enforced in the sync endpoints.

#### 🔴 HIGH — `CRON_KEY` documented but not enforced

The README references `CRON_KEY` environment variable for cron jobs, but a search shows no enforcement of this key in `api/sync-trigger.php`, `api/auto-sync.php`, or `cron/` files. Any unauthenticated caller can trigger sync operations.

#### 🟡 MEDIUM — Hardcoded credentials in `config.php`

The `config.php` file has placeholder credentials:
```php
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```
While this is intended as a template, `config.php` itself is committed to the repo (README says it's ignored, but it exists in the repo). The `ADMIN_PASS` is also referenced but not defined in the shown config — likely set via environment. **This is acceptable if properly deployed, but the file itself should never be committed with real credentials.**

#### 🟡 MEDIUM — `Access-Control-Allow-Origin: *` on admin API

```php
// api/admin-data-manager.php line 8
header('Access-Control-Allow-Origin: *');
```

The admin data manager allows CORS from any origin. While the endpoint checks session auth (`requireAdmin()`), it still allows cross-origin requests to hit the admin API, increasing attack surface.

**Recommendation**: Use a strict origin allowlist or remove the CORS header if the admin panel is on the same origin.

#### 🟡 MEDIUM — `die()` and `exit` instead of `throw`

In `api/ai-chat.php` and other API files, error cases use `die()` or `exit` to terminate. This prevents proper cleanup and error logging from the centralized error handler.

```php
// api/ai-chat.php line 27
echo "data: " . json_encode(['error' => 'POST only']) . "\n\n";
flush(); exit;
```

**Recommendation**: Throw exceptions or use a centralized `apiDie()` helper that logs and then terminates.

#### 🟡 MEDIUM — Web Scraping is fragile and opaque

All financial data (gold, NEPSE, forex, petrol) is scraped via regex from external websites:
```php
// market-data.php line 69
if (preg_match('#FINE\s*GOLD[^<]*<br>\s*<span>[^<]*per\s*1\s*tola.*?<b>\s*([\d,]+(?:\.\d+)?)\s*</b>#siu', $html, $m))
```

When scraping fails, the API returns stale cached data or `null`. There's no alerting mechanism. The code comments say "NEVER fabricated" but stale null is nearly as bad for a financial portal.

#### 🟢 LOW — `DEBUG_MODE` is undefined by default

In `error-handler.php`, if `DEBUG_MODE` is not defined, it falls through to `E_ALL & ~E_NOTICE & ~E_DEPRECATED` with `display_errors = 0`. This is correct production behavior, but there's no explicit `define('DEBUG_MODE', false)` in config.php — relying on `defined()` checks is fragile.

---

## 2. Architecture & Code Quality

### 🟡 Dual Database Systems — Major Architectural Issue

The project has **two completely separate database systems**:

**System 1 — PDO Singleton in `config.php`:**
```php
// config.php
function getDB() { /* static $pdo ... */ }
```
Returns raw PDO, used throughout the codebase via `getDB()` → `db()`.

**System 2 — `DataManager` class in `includes/data-manager.php`:**
```php
// data-manager.php
class DataManager {
    private static $instance = null;
    // Singleton pattern
    public function getNews() { ... }
}
```
A separate singleton with its own caching, separate DB access, and partially overlapping functionality.

These two systems don't know about each other. `DataManager::getInstance()->getNews()` creates a new PDO connection via `db()` (line 69), while `getPublishedNews()` in `functions.php` uses `getDB()`. They may share the same DB but have different connection pools, different error handling, and different caching strategies.

**This is confusing and will cause bugs.** Pick one DB abstraction layer.

### 🟡 `db()` function exists but `getDB()` is defined

In `functions.php` and `auth.php`, calls go to `db()` (e.g., `db()->prepare(...)`), but `config.php` only defines `getDB()`. There must be a `db()` wrapper function somewhere (possibly redefined in `includes/data-schema.php` via `DataManager` or in other included files). This is a maintenance hazard — finding where `db()` is actually defined requires grepping across all includes.

### 🟡 Dead/Duplicate Code — `sync-functions.php`

All sync functions (`syncNepse`, `syncGoldSilver`, `syncForex`, `syncPetrol`, `syncIPO`) store only the first 10,000 characters of raw HTML:
```php
file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000), 'synced_at' => date('Y-m-d H:i:s')]));
```

This is essentially dead code — the cached HTML is never parsed back into usable data. The actual data parsing happens in `market-data.php` via `scrapeGoldFromFenegosida()` etc., which hit the external sites directly with fresh fetches. The sync → cache → parse pipeline exists in name only.

### 🟡 Mixed HTTP clients

- `config.php` and `functions.php` have no HTTP fetch helper
- `includes/http.php` has `nh_fetchUrl()` (good, SSL-verified by default)
- `api/market-data.php` wraps it as `fetchUrl()` 
- `includes/sync-functions.php` uses raw `curl_init()` everywhere — inconsistent
- `api/ai-chat.php` uses `file_get_contents()` with stream context
- `core/ApiClient.php` uses `file_get_contents()` (not cURL)
- `core/ApiClient` also has a singleton that doesn't integrate with the rest of the codebase

**Recommendation**: Consolidate on `nh_fetchUrl()` from `includes/http.php` everywhere. Remove the custom curl code from sync functions.

### 🟡 Namespace mismatch

`core/ApiClient.php` uses `namespace Aakashvani\Core;` but most of the codebase is flat PHP with no namespaces. This class is essentially isolated and may not even be used by the main application — a search suggests it's defined but likely not included anywhere in the critical path.

### 🟡 `slugify()` called in admin dashboard without definition visible

In `admin/dashboard.php` lines 53, 65:
```php
$slug = slugify($_POST['title']);
```

`slugify()` is used but not defined in the visible scope. It may exist in an included file, but it's not in `functions.php` or obvious from the includes. This function call could silently fail.

### 🟡 Admin Dashboard — Inline PHP mixing

The admin dashboard (`admin/dashboard.php`) is a massive ~1900 line file with embedded HTML, PHP logic, and inline JavaScript all in one file. While functional, this is a maintenance nightmare. Each modal (add_story, add_visit, add_radio, add_podcast) is embedded directly in the HTML.

### 🟢 Good: Error Logger Separate from Error Handler

`includes/error-handler.php` (centralized error handling) and `includes/error-logger.php` (separate logging) are properly separated, each doing one thing well.

### 🟢 Good: `MARKET_LIB_ONLY` Pattern

The `market-data.php` API uses a `define('MARKET_LIB_ONLY')` guard to allow being included as a library. This is a pragmatic solution for sharing code between API endpoints and internal consumers.

---

## 3. Data Integrity

### 🟡 Sample data treated as real data

In `market-data.php`, when scraping fails, there's fallback to hardcoded sample data:
```php
// market-data.php lines 242-260
function getSampleGainers(): array {
    return [
        ['symbol' => 'NABIL', 'price' => 850.00, 'change' => 25.00, ...],
        // ...
    ];
}
```

These are returned by `getNepseData()` when scraping fails. A user of a financial portal seeing fake stock prices is a serious issue. The code comments say "REAL data only" but the fallback exists and could activate silently.

### 🟡 Web scraping is not reliable

All external data sources (FENEGOSIDA, Hamro Patro, Nepal Stock, Mero Lagani) are scraped via HTML regex. Any of these could change their HTML structure at any time and the scrapers would silently fail. No monitoring or alerting exists for this.

### 🟡 Cache directory inconsistency

- `functions.php` writes to `__DIR__ . '/data/cache/'` 
- `market-data.php` writes to `__DIR__ . '/../data/cache/'` (which resolves to the same)
- `api/admin-data-manager.php` writes to `__DIR__ . '/../cache'` (different!)
- `DataManager` writes to `__DIR__ . '/../cache'` (different again!)

Four different code paths, two different directories. This is a reliability issue.

### 🟡 SQLite vs MySQL dual-driver handling

In `auth.php` and other places, there's conditional SQL:
```php
$idCol = function_exists('isMysql') && isMysql()
    ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY'
    : 'INTEGER PRIMARY KEY AUTOINCREMENT';
```

This suggests SQLite is also supported, but not all queries are written for dual-driver compatibility. This adds complexity for uncertain benefit.

---

## 4. Performance

### 🟢 Good: CSS Architecture

- Modular CSS (`variables.css`, `layout.css`, `components.css`, `responsive.css`, `skeleton.css`)
- Two main entry points: `app.css` and `premium.css`
- CSS custom properties (variables) for theming

### 🟢 Good: PWA Support

- `manifest.json` and `manifest.webmanifest` both present
- `service-worker.js` and `sw.js` for offline caching
- Push notification ready (commented in README)

### 🟢 Good: Font Loading

```php
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
```
Proper preconnect for Google Fonts (Inter + Noto Sans Devanagari).

### 🟢 Good: Lazy Loading

`assets/js/lazyload.js` exists for image lazy loading.

### 🟡 JS — jQuery NOT used

The codebase is vanilla JS (good choice for performance). However, some inline JS patterns could be cleaned up:
```php
// ai-assistant.php line 141-148
function aiRemember(role, content) {
  try {
    var key = 'nsh_ai_recent';
    var rows = JSON.parse(localStorage.getItem(key) || '[]');
```
The AI chat uses localStorage with a hardcoded key `nsh_ai_recent` — the `nsh_` prefix doesn't match the project name (`aakashvani`).

### 🟡 Missing: No image optimization pipeline

Images are loaded directly from external URLs (e.g., `https://picsum.photos/`) in the schema and sample data. There's no image CDN, no WebP conversion, and no responsive images (`srcset`).

---

## 5. File-by-File Critical Notes

| File | Issue |
|---|---|
| **`api/admin-data-manager.php`** | CORS `*` on admin endpoint. `cacheDir` uses `/cache` not `/data/cache`. |
| **`api/ai-chat.php`** | Uses `die`/`exit`. SSE streaming without proper connection cleanup. |
| **`core/ApiClient.php`** | Singleton with `getInstance()` but not used by main app. Dead code. |
| **`admin/dashboard.php`** | Massive inline file. Missing `slugify()` definition. |
| **`admin/settings.php`** | Directly modifies `config.php` via `file_put_contents()` with regex. Risky. |
| **`includes/sync-functions.php`** | All sync functions store raw HTML that's never parsed. Dead code pipeline. |
| **`includes/data-manager.php`** | Separate DB singleton that duplicates `getDB()`. |
| **`sync-functions.php`** | SSRF risk. Raw curl with no URL validation. |
| **`market-data.php`** | Sample data fallbacks could mislead users. Regex scraping fragile. |
| **`config.php`** | Placeholder values committed. `db()` function not defined in this file. |
| **`test.php`** | Debug/test page in production-accessible path. |
| **`tool.php`** | Another debug utility page. Should be protected or removed from production. |
| **`includes/ai-assistant.php`** | Hardcoded localStorage key `nsh_ai_recent` doesn't match project name. |

---

## 6. Issues Found Summary

### 🔴 Critical (Must Fix)

1. **SSRF in sync-functions.php** — No URL validation on external fetches
2. **CRON_KEY not enforced** — Sync endpoints have no authentication
3. **Sample financial data as fallback** — `getSampleGainers()`/`getSampleLosers()` silently used when scraping fails

### 🟡 Medium (Should Fix)

4. **Dual DB abstraction layers** — `getDB()` vs `DataManager` cause confusion
5. **Two cache directories** — `/cache` vs `/data/cache` means cache invalidation is incomplete
6. **`db()` function undefined** — Where is `db()` defined? It's called but `getDB()` is the only function in config.php
7. **`die()`/`exit` in API files** — Bypasses error handler and cleanup
8. **Admin CORS `*`** — Admin API should restrict origins
9. **`slugify()` undefined** — Called in admin dashboard but not found
10. **Web scraping without monitoring** — No alerting when scraping fails
11. **Dead code in sync pipeline** — Cached HTML never parsed back

### 🟢 Minor (Nice to Have)

12. **Debug pages in production** — `test.php` and `tool.php` accessible in production
13. **Inconsistent HTTP clients** — Six different ways to make HTTP requests
14. **Namespace isolation** — `ApiClient` in its own namespace but unused
15. **localStorage key mismatch** — `nsh_ai_recent` vs project name
16. **`config.php` has placeholder values** — Should use `config.example.php` with no default values

---

## 7. Recommendations (Priority Order)

### Immediate (Before Next Deploy)

1. **Enforce CRON_KEY** on all sync endpoints (`api/sync-trigger.php`, `api/auto-sync.php`, `cron/`)
2. **Fix SSRF** — Validate all external URLs against allowlist in sync functions
3. **Remove sample data fallbacks** — When scraping fails on market data, return an explicit "unavailable" response, not fake stock prices
4. **Restrict CORS on admin API** — Change `Access-Control-Allow-Origin: *` to specific origins or remove
5. **Consolidate cache directory** — All code should use `/data/cache`, delete `/cache`

### Short Term (Next Sprint)

6. **Consolidate DB layer** — Pick one of `getDB()` or `DataManager`, deprecate the other
7. **Find `db()` function** — Document its definition location, or consolidate on `getDB()`
8. **Remove dead sync code** — Either implement full scrape → cache → parse pipeline, or remove the cache writes in sync functions
9. **Add scraping monitoring** — Alert when FENEGOSIDA, NepalStock, etc. scrapers fail
10. **Define `slugify()`** — Ensure it's in `functions.php` or another included file

### Medium Term (Technical Debt)

11. **Split admin/dashboard.php** — Extract modals into separate included files
12. **Consolidate HTTP clients** — Make `nh_fetchUrl()` the standard throughout
13. **Remove debug pages from production** — Either protect with auth or move outside webroot
14. **Add `srcset` for responsive images** — Use modern image formats
15. **Clean up ApiClient** — Either use it project-wide or remove it

---

## Risk Assessment

⚠️ **Overall Risk: 🟡 MEDIUM**

The project has a solid security foundation (CSRF, prepared statements, password hashing, rate limiting). However, the SSRF vulnerabilities, unauthenticated cron endpoints, and dual database systems represent real production risks. The financial data scrapers are particularly fragile — a single HTML structure change on FENEGOSIDA could silently corrupt gold price data for all users.

The architectural drift (two DB systems, multiple HTTP clients, scattered caching) makes the codebase harder to maintain and debug, increasing the likelihood of future security issues.

**Worth merging with fixes?** The project is functional and has good bones, but the critical SSRF and cron auth issues should be addressed before production deployment.

---

## Verdict

❌ **Needs rework** — Critical security issues (SSRF, unauthenticated cron) and architectural fragmentation must be resolved first.

**Key Insight:** The project has better-than-average security instincts for a PHP application of this size (CSRF tokens, prepared statements, secure password hashing, rate limiting). The real risks are operational — fragile web scraping for financial data with no monitoring, and the architectural split between two database abstraction layers that will inevitably cause bugs.
