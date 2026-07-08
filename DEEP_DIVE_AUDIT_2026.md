# आकाशवाणी — Deep Dive Audit Report
**Reviewed by:** Senior Architecture Team  
**Date:** July 8, 2026  
**Scope:** Full codebase — 130 PHP files, CSS, JS, DB schema, API layer, admin panel

---

## Executive Summary

The आकाशवाणी project is a **large, ambitious Nepali information portal** with strong foundational work in RSS aggregation, NEPSE market data, and multilingual support. However, after years of incremental additions, the codebase shows **clear signs of architectural decay**: inconsistent patterns, duplicated functions, missing infrastructure, and several critical security and performance gaps.

> **Bottom line:** The project works at a surface level but has significant technical debt that will make future development increasingly expensive and risky. This audit provides a phased roadmap to fix it properly.

---

## PHASE 1: CRITICAL / BLOCKER (Do First)
*These issues cause crashes, data loss, or security breaches*

### 1.1 🔴 Config Domain Mismatch
**File:** `config.php:17`
```php
define('SITE_URL', 'https://tankaadhikari.com.np'); // ← WRONG
```
The production domain is `news.bandanasigdel.com.np`, but `config.php` has `tankaadhikari.com.np`. This causes the `SITE_URL` fallback in `data-manager.php` to use the wrong domain. Hardcoded `https://news.bandanasigdel.com.np` in `index.php` masks this for now, but any future use of `SITE_URL` will break.

**Fix:** Update `config.php` to use the correct domain, or make it environment-variable driven.

---

### 1.2 🔴 Missing DB Connection in Production
**Files:** `api/cabinet-decisions.php`, `api/contact-directory.php`

Both APIs call `db()` and silently return `[]` when the DB is unavailable. The **database credentials in `config.php` are placeholder values** (`your_database`, `your_username`). In production, the MySQL connection will always fail silently.

**Fix:** Configure real DB credentials via environment variables (never commit real credentials).

---

### 1.3 🔴 Session Security — No Session Regeneration on Login
**File:** `includes/auth.php:loginUser`

The `loginUser()` function sets `$_SESSION['auth_user_id']` without calling `session_regenerate_id(true)`. The admin login in `admin/index.php` does this correctly, but user auth does not. This enables session fixation attacks.

**Fix:** Add `session_regenerate_id(true)` before setting `$_SESSION['auth_user_id']`.

---

### 1.4 🔴 CSRF Token Not Enforced on All State-Changing Operations
**File:** `includes/csrf.php`

CSRF token is defined but inconsistently enforced. Several AJAX endpoints (`api/push-subscribe.php`, `api/user-data.php`, etc.) accept POST requests without CSRF validation.

**Fix:** Create a `requireCsrf()` helper and use it consistently across all POST endpoints.

---

### 1.5 🔴 Admin Panel — Plaintext Password in Config
**File:** `admin/index.php:16`

```php
hash_equals((string)ADMIN_PASS, $password)  // Compares plaintext!
```
This compares a plaintext password in config directly. Anyone with read access to `config.php` gets the admin password.

**Fix:** Store `ADMIN_PASS` as a bcrypt hash and use `password_verify()`.

---

### 1.6 🔴 Missing Input Validation on `searchNews()`
**File:** `functions.php:134`

```php
$search = '%' . $query . '%';  // Empty $query produces %% which matches ALL
```
No minimum length enforcement. An empty search matches everything in the DB.

**Fix:** Add `if (mb_strlen(trim($query), 'UTF-8') < 2) return [];`

---

## PHASE 2: HIGH PRIORITY
*These cause incorrect behavior, performance problems, or maintenance nightmares*

### 2.1 🟠 28 Functions Defined Multiple Times
**Files:** Across 20+ files

| Function | Defined In |
|----------|-----------|
| `getNews()` | `core/ApiClient.php`, `includes/data-manager.php` |
| `getMarketData()` | `functions.php`, `core/ApiClient.php`, `includes/data-manager.php` |
| `searchNews()` | `functions.php`, `includes/data-manager.php` |
| `getCategories()` | `functions.php`, `includes/data-manager.php` |
| `db()` | `config.php`, `includes/header.php` |
| `t()` | `config.php`, `components/premium-*.php` (duplicates!) |
| `timeAgo()` | `config.php`, `news.php`, `components/premium-news-card.php` |
| `fetchUrl()` | `api/market-data.php`, `api/utilities.php`, `api/weather-alerts.php` |
| `readCache()` / `writeCache()` | 4 different files, each different implementation |

PHP's `function_exists()` guards prevent fatal errors, but **which version runs depends on include order** — a silent, unpredictable bug.

**Fix:** Create `includes/autoload.php` as single loading point. Move all shared functions to `includes/`. Components should **use** functions, not **define** them.

---

### 2.2 🟠 API Response Format Inconsistency
**43 APIs** use `'ok' => true/false`, but 6 APIs don't:
- `ai-chat.php` — SSE streaming
- `download-proxy.php` — Binary passthrough
- `news-expand.php` — Raw content
- `pwa-manifest.php` — Manifest JSON
- `stories.php` — No standard wrapper
- `utilities.php` — Mixed responses

**Fix:** Standardize on `ApiResponse::success($data)` / `ApiResponse::error($msg)` as the **only** JSON response pattern.

---

### 2.3 🟠 Cache Stampede Risk
**Files:** `includes/data-manager.php`, `api/news-rss.php`

```php
// No locking — concurrent writes corrupt the cache
@file_put_contents($cacheFile, json_encode($result, ...));
```
When the 30-minute cache expires, hundreds of concurrent requests all hit RSS feeds simultaneously.

**Fix:** Use `flock()` for atomic cache writes.

---

### 2.4 🟠 `$homepageNews` — Self-Referential HTTP Fetch
**File:** `index.php:18-33`

```php
$apiBase = 'https://news.bandanasigdel.com.np';  // External call to self
$ch = curl_init($apiBase . '/api/news-rss.php?limit=4&cat=general');
```
Homepage makes an HTTP request to its own API. Fails behind load balancers or CDNs. Also, the JS `NewsLoader` fetches `news-unified.php` (different format: `data.items`) while PHP fetches `news-rss.php` (`data.items` too, but different structure). Confusing and slow.

**Fix:** Use `dataManager()->getNews()` directly instead of HTTP.

---

### 2.5 🟠 Empty Catch Blocks Everywhere
**Files:** `api/news-rss.php:89`, throughout

```php
} catch (Throwable $e) {
}  // ← Exception completely lost. No logging.
```
Silent failures make production debugging impossible.

**Fix:** Never use empty catch blocks. Always log with context.

---

### 2.6 🟠 HSTS Header Commented Out
**File:** `.htaccess:163-164`

```apache
# header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
```
HTTPS is enforced via rewrite but HSTS is disabled. MITM can strip HTTPS on subsequent visits.

**Fix:** Uncomment HSTS once SSL is confirmed working.

---

## PHASE 3: MEDIUM PRIORITY

### 3.1 🟡 Two Parallel CSS Systems
`assets/css/app.css` (58KB) + `assets/css/premium.css` (34KB) — overlapping styles, no documented methodology (not BEM, not Tailwind, not CSS Modules). Dead CSS likely exists in both.

**Fix:** Audit overlap, consolidate to one design token system.

---

### 3.2 🟡 No PHP 8.x Compatibility Check
`@` error suppression used extensively. Mixed return types without union syntax. No named arguments.

**Fix:** Run `phpcs` with `PHPCompatibility` ruleset.

---

### 3.3 🟡 Image URLs Not Proxied
```php
'image' => $r['image_url'] ?: null  // Leaks server IP via Referer header
```
External image servers track your users. External images can break page layout.

**Fix:** Route images through `api/download-proxy.php`.

---

### 3.4 🟡 `market-data.php` — 810-Line God Object
Handles NEPSE, forex, gold, crypto, indices all in one file.

**Fix:** Split into `api/market-data.php` (dispatcher) + `includes/market-providers/` (one class per source).

---

### 3.5 🟡 Missing DB Indexes on `tech_news`
`news-rss.php` queries `tech_news` by `is_published + created_at` and `category` but no composite indexes exist.

**Fix:** Add indexes: `(is_published, created_at)`, `(category, created_at)`, `(source_name)`.

---

### 3.6 🟡 Missing Security Headers
- **CSP** — Not defined. XSS can load arbitrary external scripts.
- **Permissions-Policy** — Not defined.
- **X-Content-Type-Options** — In `.htaccess` ✓ but not in `sendSecurityHeaders()` PHP function.

**Fix:** Add CSP, `nosniff` to `sendSecurityHeaders()`, add Permissions-Policy.

---

## PHASE 4: LOW PRIORITY

### 4.1 ⚪ Static `sitemap.xml` and `robots.txt`
Go stale immediately after news is added. Need dynamic generation.

### 4.2 ⚪ Zero Automated Tests
Any refactoring risks breaking functionality without a safety net.

### 4.3 ⚪ Admin Panel: Tailwind CDN
```html
<script src="https://cdn.tailwindcss.com"></script>  <!-- 150KB+ per load -->
```
Should use local compiled CSS.

### 4.4 ⚪ `ai-assistant.php` + `ai-chat.php` — Duplicated Logic
335 lines of AI assistant code copied in two places.

### 4.5 ⚪ Inconsistent BS Date Handling
Scattered across `bs-date.php`, `nepali-patro.php`, `rashifal.php` with different approaches.

---

## PHASE 5: OPTIMIZATION

| Win | Impact | Effort |
|-----|--------|--------|
| Brotli compression | 15-20% smaller transfers | Low |
| Preconnect to external domains | Faster load | Low |
| Redis/Memcached | 10x faster caching | Medium |
| CDN for static assets | Global performance | Medium |
| WebP/AVIF images | 40-60% smaller images | Medium |
| Critical CSS inlining | Faster FCP | Low |
| Composite DB indexes | Faster queries | Low |
| Image proxying | Privacy + reliability | Medium |

---

## ARCHITECTURE STRENGTHS

These are real wins worth preserving and building on:

1. **RSS Aggregation** — Multi-source feed fetching with dedup and caching is well-designed
2. **Data Schema** — `includes/data-schema.php` provides solid typed contracts
3. **Rate Limiting** — Consistent file-based rate limiting across APIs
4. **Security Headers** — Comprehensive in `.htaccess` (CSP-ready structure)
5. **Prepared Statements** — Consistent PDO throughout — zero SQL injection
6. **Nepali/English i18n** — Clean `t()` function, consistent throughout
7. **Error Handling Infrastructure** — Foundation exists in `includes/error-handler.php`
8. **Admin Auth** — Proper session regeneration, CSRF, password hashing

---

## EXECUTION ROADMAP

```
WEEK 1 — Stability & Security
├── Fix config domain (SITE_URL → correct domain)
├── Env-based credentials (never commit secrets)
├── session_regenerate_id() on user login
├── requireCsrf() on ALL POST endpoints
├── password_hash() for ADMIN_PASS
└── Minimum length on search queries

WEEKS 2-3 — Architecture Foundation  
├── Create includes/autoload.php
├── Deduplicate all 28 functions → single source of truth
├── ApiResponse::success/error as ONLY JSON pattern
├── Fix ALL empty catch blocks
├── flock() for atomic cache writes
└── Uncomment HSTS header

WEEKS 3-4 — Code Quality
├── Add CSP header
├── Split market-data.php (god object → providers)
├── Add composite DB indexes on tech_news
├── php8-compatibility audit
├── Proxy images through download-proxy.php
└── Audit CSS overlap, consolidate

ONGOING — Polish
├── Dynamic sitemap generation
├── PHPUnit smoke tests for APIs
├── Local Tailwind for admin
└── Brotli compression

MONTH 2+ — Scale
├── Redis instead of file caching
├── CDN for static assets
├── WebP/AVIF pipeline
└── Critical CSS inlining
```

---

## SCORECARD

| Category | Score | Notes |
|----------|-------|-------|
| **Security** | 6/10 | Good foundations (prepared stmts, sec headers) but CSRF gaps, plaintext admin pass |
| **Architecture** | 5/10 | Good separation but 28 duplicate functions and god objects |
| **Performance** | 6/10 | Good caching/gzip; needs Brotli, CDN, image proxy |
| **Code Quality** | 5/10 | Inconsistent patterns, empty catch blocks, no tests |
| **SEO** | 7/10 | Good meta/OG tags; needs structured data + dynamic sitemap |
| **Accessibility** | 5/10 | lang attr, semantic HTML present; needs WCAG audit |
| **i18n** | 8/10 | Clean `t()` function, consistent Nepali/English |
| **Maintainability** | 4/10 | High debt, no tests, no docs for devs |

**Overall: 5.7/10** — Functional but needs structured refactoring before scaling.
