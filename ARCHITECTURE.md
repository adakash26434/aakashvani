# आकाशवाणी - Project Architecture

## Overview

This document describes the clean, professional architecture of आकाशवाणी News Portal.

## File Structure

```
project/
├── assets/
│   ├── css/
│   │   ├── global.css          # Main entry point (imports all modules)
│   │   ├── variables.css       # Design tokens
│   │   ├── layout.css         # Grid, flex, containers
│   │   ├── components.css     # UI components
│   │   ├── responsive.css      # Mobile-first responsive
│   │   └── skeleton.css        # Loading states
│   └── js/
│       ├── lazyload.js         # Performance utilities
│       ├── app.js              # Main application JS
│       └── *.js                # Feature-specific scripts
│
├── api/                        # API endpoints
│   ├── market-data.php         # Market/NEPSE data
│   ├── rashifal.php            # Daily rashifal
│   ├── weather-alerts.php      # Weather alerts
│   └── *.php                   # Feature APIs
│
├── admin/                      # Admin panel
│   ├── dashboard.php
│   ├── content.php
│   └── *.php
│
├── auth/                       # Authentication handlers
│   ├── login.php
│   ├── register.php
│   ├── google.php
│   └── facebook.php
│
├── includes/                   # Shared components
│   ├── header.php              # Main header
│   ├── footer.php              # Main footer
│   ├── header-new.php          # New header (refactored)
│   ├── footer-new.php          # New footer (refactored)
│   ├── design-system.php       # Design tokens
│   └── *.php                   # Helpers
│
├── sql/                        # Database
│   └── MASTER_INSTALL.sql      # Main schema
│
├── *.php                       # Public pages
│
├── index.php                   # Homepage
├── news.php                    # News listing
├── news-detail.php             # Article view
├── search.php                  # Search
└── *.php                       # Feature pages
```

## Core Pages

| Page | Purpose | Priority |
|------|---------|----------|
| index.php | Homepage | Essential |
| news.php | News listing | Essential |
| news-detail.php | Article view | Essential |
| category.php | Category view | Essential |
| search.php | Search results | Essential |

## Info Hub Pages

| Page | Purpose | Priority |
|------|---------|----------|
| info-hub.php | Main hub | High |
| nepali-patro.php | Calendar | High |
| rashifal.php | Daily horoscope | High |
| weather.php | Weather info | High |
| gold-price.php | Gold rates | High |
| emergency.php | Emergency contacts | High |

## Finance Pages

| Page | Purpose | Priority |
|------|---------|----------|
| market.php | Market overview | High |
| ipo-tracker.php | IPO/NEPSE tracker | High |
| nokari.php | Job listings | Medium |
| loksewa.php | Government jobs | Medium |

## Tools Pages

| Page | Purpose | Priority |
|------|---------|----------|
| tools.php | Main tools hub | Medium |
| gov-services.php | Government services | High |
| tax-calculator.php | Tax calculator | Medium |
| currency-converter.php | Currency converter | Medium |

## User Pages

| Page | Purpose | Priority |
|------|---------|----------|
| dashboard.php | User dashboard | Medium |
| profile.php | User profile | Medium |
| bookmarks.php | Saved items | Medium |
| alerts.php | Notifications | Low |

## API Structure

APIs follow RESTful naming conventions:
- `api/market-data.php` - Market data
- `api/rashifal.php` - Rashifal data
- `api/weather-alerts.php` - Weather alerts
- `api/news-rss.php` - RSS feeds

## Design System

The project uses a unified design system defined in `assets/css/`:

### Colors
- Primary: Emerald (#10B981)
- Secondary: Teal (#14B8A6)
- Text: Slate (#0F172A)

### Typography
- Font: Inter + Noto Sans Devanagari
- Consistent scale across all pages

### Components
All UI components are defined globally:
- Buttons
- Cards
- Forms
- Navigation
- Badges

## Performance Targets

- First Contentful Paint: < 1.5s
- Largest Contentful Paint: < 2s
- CLS: ~0
- PageSpeed: 95+

## Security

- Prepared statements for all queries
- XSS protection via output escaping
- CSRF tokens on forms
- Input validation

## Cleanup Status

### Removed Files
- transportation.php (duplicate of transport.php)
- visit-place.php (duplicate)
- article-test.php (test file)
- podcasts.sql (duplicate schema)
- Old documentation files

### Recommended Cleanup (Manual Review)
These pages may be candidates for removal but need manual review:
- auction-notices.php
- government-tenders.php
- quiz-games.php
- offers.php
- dictionary.php

### API Consolidation (Future)
These small APIs could potentially be consolidated:
- bank-interest-rates.php
- tax-rates.php
- cabinet-decisions.php
- contact-directory.php

## Development Guidelines

1. **No Duplicate Code** - Reuse existing functions
2. **Use Design System** - Don't create custom CSS
3. **Mobile First** - Test on mobile first
4. **Performance** - Lazy load images, optimize queries
5. **Security** - Always escape output, use prepared statements
