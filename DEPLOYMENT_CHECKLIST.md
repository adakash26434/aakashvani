# आकाशवाणी Deployment Checklist

Complete checklist for deploying the project to production.

## ✅ Pre-Deployment Checklist

### 1. Code & Repository
- [ ] All changes committed to Git
- [ ] Git push to origin main completed
- [ ] No uncommitted changes in working directory
- [ ] .gitignore properly configured
- [ ] No sensitive files in repository

### 2. Database Setup
- [ ] Create MySQL database
- [ ] Run `fix-database-issues.sql`
- [ ] Verify tables created:
  - `tech_news`
  - `radio_stations`
  - `success_stories`
  - `loksewa_notices`
  - `rashifal_daily`
  - `subscriptions`
  - `news_sync_log`
- [ ] Verify radio stations have data (13 stations)
- [ ] Test Nepali text displays correctly (no ??????)

### 3. Configuration Files
- [ ] `config.php` created with correct values:
  - [ ] DB_HOST, DB_NAME, DB_USER, DB_PASS
  - [ ] DB_CHARSET = 'utf8mb4'
  - [ ] SITE_URL
  - [ ] OPENAI_API_KEY (optional)
- [ ] `config.php` not in git tracking
- [ ] File permissions correct (644 for config)

### 4. Directory Permissions
```bash
chmod 755 /home/USER/public_html/
chmod 755 /home/USER/public_html/data/
chmod 755 /home/USER/public_html/data/cache/
chmod 755 /home/USER/public_html/data/logs/
chmod 755 /home/USER/public_html/cache/
chmod 755 /home/USER/public_html/assets/news-cache/
chmod 755 /home/USER/public_html/cron/
```

### 5. Cron Jobs Setup (cPanel)
```bash
# News sync every 30 minutes
0,30 * * * * /usr/bin/php /home/USER/public_html/cron/ai-sync.php

# Morning brief generation (6 AM daily)
0 6 * * * /usr/bin/php /home/USER/public_html/cron/generate-morning-brief.php

# Rashifal generation (5 AM daily)
0 5 * * * /usr/bin/php /home/USER/public_html/cron/generate-rashifal.php

# Old data cleanup (weekly)
0 0 * * 0 /usr/bin/php /home/USER/public_html/cron/cleanup-old-data.php
```

### 6. Server Requirements
- [ ] PHP 8.1+ installed
- [ ] PHP Extensions enabled:
  - [ ] PDO (pdo_mysql, pdo_sqlite)
  - [ ] curl
  - [ ] mbstring
  - [ ] json
  - [ ] xml
  - [ ] gd (for image processing)
- [ ] SSL certificate installed (HTTPS)
- [ ] mod_rewrite enabled

## 🔧 Post-Deployment Configuration

### 7. Admin Setup
- [ ] Access `/admin/` and login
- [ ] Change default admin password
- [ ] Run `/admin/clear-cache.php` to clear any old cache
- [ ] Test `/admin/article-test.php` for content fetching

### 8. Feature Testing
- [ ] Home page loads correctly
- [ ] News shows full articles (not 1-2 sentences)
- [ ] Nepali Patro displays correct date
- [ ] Radio stations play correctly
- [ ] Market data loads (Gold, Fuel, Forex, NEPSE)
- [ ] IPO tracker works
- [ ] Alerts (BIPAD, Earthquake) display
- [ ] Weather shows Kathmandu forecast
- [ ] Rashifal generates daily
- [ ] Search functionality works

### 9. Mobile & PWA
- [ ] Site loads on mobile browser
- [ ] Install as PWA (Add to Home Screen)
- [ ] Offline mode works
- [ ] Push notifications (if enabled)
- [ ] Touch targets are 44px+
- [ ] No horizontal scrolling

### 10. Security
- [ ] HTTPS forced (redirect HTTP to HTTPS)
- [ ] Security headers active (check in DevTools)
- [ ] No console errors
- [ ] Rate limiting working
- [ ] Admin panel protected
- [ ] No exposed .env or config files

### 11. Performance
- [ ] Page load < 3 seconds
- [ ] Images lazy-loaded
- [ ] CSS/JS minified (if applicable)
- [ ] Cache working (check data/cache/ directory)
- [ ] Database queries optimized

### 12. SEO & Meta
- [ ] Meta tags present on all pages
- [ ] OG tags for social sharing
- [ ] Sitemap.xml generated
- [ ] Robots.txt configured
- [ ] Google Analytics (optional)

## 📊 Final Verification

### API Endpoints Test
```bash
# Test these URLs return JSON
curl https://yoursite.com/api/news-rss.php
curl https://yoursite.com/api/market-data.php
curl https://yoursite.com/api/alerts.php
curl https://yoursite.com/api/weather.php
```

### Critical Functions
- [ ] `sendSecurityHeaders()` in functions.php
- [ ] `checkRateLimit()` working
- [ ] `fetchUrl()` with timeout
- [ ] Database connection (utf8mb4)
- [ ] Service worker registration
- [ ] Lucide icons loading

### Error Handling
- [ ] 404 page styled correctly
- [ ] 500 errors logged
- [ ] Database errors handled gracefully
- [ ] API failures have fallbacks

## 🚀 Production Ready

When all above are checked:
1. Create backup of database
2. Monitor error logs for 24 hours
3. Check cron job execution in logs
4. Monitor server resources

## 📝 Support & Troubleshooting

### Common Issues:
- **?????? in Nepali text**: Run `fix-database-issues.sql`
- **Empty radio/podcast**: Check SQL data inserted
- **API timeouts**: Check `default_socket_timeout` in php.ini
- **Cache not clearing**: Check directory permissions
- **Images not loading**: Check `assets/news-cache/` permissions

### Log Files:
- `/data/logs/ai-sync.log` - News sync errors
- `/data/logs/error.log` - PHP errors
- `/data/cache/` - API cache files

---

**Last Updated:** May 26, 2026  
**Project:** आकाशवाणी v11  
**Status:** Production Ready ✅
