# आकाशवाणी - Icon System Standards

## Policy: Lucide Icons Only

**ALL emojis are prohibited** in the codebase. Only Lucide Icons are allowed.

---

## Approved Icon Library

**[Lucide Icons](https://lucide.dev/)** - The only official icon library for this project.

---

## Standard Icon Mappings

### Navigation & UI

| Purpose | Emoji | Lucide |
|---------|-------|--------|
| Home | 🏠 | `home` |
| Menu | ☰ | `menu` |
| Search | 🔍 | `search` |
| Settings | ⚙️ | `settings` |
| User | 👤 | `user` |
| Users | 👥 | `users` |
| Logout | 🚪 | `log-out` |
| Login | 🔑 | `log-in` |
| Close | ✖️ | `x` |
| Back | ← | `arrow-left` |
| Forward | → | `arrow-right` |
| Menu More | ⋯ | `more-horizontal` |
| Refresh | 🔄 | `refresh-cw` |
| Download | ⬇️ | `download` |
| Upload | ⬆️ | `upload` |
| Filter | 🔽 | `filter` |
| Copy | 📋 | `copy` |
| Delete | 🗑️ | `trash-2` |
| Add | ➕ | `plus` |
| Remove | ➖ | `minus` |
| Check | ✅ | `check` |
| Error | ❌ | `x-circle` |
| Warning | ⚠️ | `alert-triangle` |
| Info | ℹ️ | `info` |
| Help | ❓ | `help-circle` |
| Star | ⭐ | `star` |
| Heart | ❤️ | `heart` |
| Share | 📤 | `share-2` |

### Content & Media

| Purpose | Emoji | Lucide |
|---------|-------|--------|
| News | 📰 | `newspaper` |
| Video | 🎬 | `video` |
| Camera | 📷 | `camera` |
| Photo | 🖼️ | `image` |
| Play | ▶️ | `play` |
| Audio | 🔊 | `volume-2` |
| Live | 🔴 | `radio` |
| Broadcast | 📢 | `radio` |
| Music | 🎵 | `music` |

### Finance & Market

| Purpose | Emoji | Lucide |
|---------|-------|--------|
| Money | 💰 | `badge-dollar-sign` |
| Currency | 💵 | `coins` |
| Gold | 🥇 | `gem` |
| Trending Up | 📈 | `trending-up` |
| Trending Down | 📉 | `trending-down` |
| Chart | 📊 | `bar-chart-2` |
| Bank | 🏦 | `landmark` |

### Government & Public

| Purpose | Emoji | Lucide |
|---------|-------|--------|
| Government | 🏛️ | `landmark` |
| Building | 🏢 | `building` |
| Hospital | 🏥 | `building` |
| Emergency | 🚨 | `alert-circle` |

### Weather

| Purpose | Emoji | Lucide |
|---------|-------|--------|
| Sun | ☀️ | `sun` |
| Cloud | ☁️ | `cloud` |
| Rain | 🌧️ | `cloud-rain` |
| Temperature | 🌡️ | `thermometer` |

### Sports

| Purpose | Emoji | Lucide |
|---------|-------|--------|
| Cricket | 🏏 | `trophy` |
| Trophy | 🏆 | `trophy` |

### Work & Career

| Purpose | Emoji | Lucide |
|---------|-------|--------|
| Briefcase | 💼 | `briefcase` |
| Job | 💼 | `briefcase` |
| Laptop | 💻 | `laptop` |

### Status

| Purpose | Emoji | Lucide |
|---------|-------|--------|
| Success | ✅ | `check-circle` |
| Failed | ❌ | `x-circle` |
| Warning | ⚠️ | `alert-triangle` |
| Fire/Hot | 🔥 | `flame` |
| New | 🆕 | `sparkles` |

---

## Implementation Examples

### PHP Arrays

**WRONG:**
```php
$nav = [
    '/cricket.php' => ['ne'=>'🏏 क्रिकेट', 'icon'=>'trophy'],
    '/nokari.php'  => ['ne'=>'💼 नोकरी', 'icon'=>'briefcase'],
];
```

**CORRECT:**
```php
$nav = [
    '/cricket.php' => ['ne'=>'क्रिकेट', 'icon'=>'trophy'],
    '/nokari.php'  => ['ne'=>'नोकरी', 'icon'=>'briefcase'],
];
```

### HTML Templates

**WRONG:**
```html
<span class="badge">🔥 Hot</span>
<button>✅ Save</button>
```

**CORRECT:**
```html
<span class="badge badge-hot"><i data-lucide="flame" class="icon-sm"></i> Hot</span>
<button class="btn btn-success"><i data-lucide="check" class="icon-md"></i> Save</button>
```

---

## Verification

```bash
# Search for any emoji characters
grep -rn --include="*.php" -E "[^\x00-\x7F]"
```
