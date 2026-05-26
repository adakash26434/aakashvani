/* ─────────────────────────────────────────────────────────────────────────────
 * आकाशवाणी — app.js
 * Wires together:
 *   • Supabase auth (Google + Email/Password) + chat-history sync
 *   • ⌘K command palette with smart commands (USD, gold, EMI, festivals, navigation)
 *   • Web push subscribe (Morning Brief)
 * Loaded once via header.php on every page.
 * ─────────────────────────────────────────────────────────────────────────── */
(function () {
  const cfg = window.NSH_CONFIG || {};
  const lang = cfg.lang || 'ne';
  const t = (ne, en) => lang === 'en' ? en : ne;

  // ─── Supabase init (graceful — features just disable if not configured) ──
  let sb = null;
  if (cfg.supabaseUrl && cfg.supabaseKey && window.supabase) {
    sb = window.supabase.createClient(cfg.supabaseUrl, cfg.supabaseKey, {
      auth: { persistSession: true, autoRefreshToken: true, detectSessionInUrl: true },
    });
  }

  // ═════════════════════════ AUTH WIDGET ═════════════════════════
  const signinBtn = document.getElementById('auth-signin');
  const avatarBtn = document.getElementById('auth-avatar');

  function renderAuth(user) {
    if (!signinBtn || !avatarBtn) return;
    const signupBtn = document.getElementById('auth-signup');
    if (user) {
      signinBtn.style.display = 'none';
      if (signupBtn) signupBtn.style.display = 'none';
      avatarBtn.style.display = 'flex';
      const initial = (user.user_metadata?.full_name || user.email || '?')
        .trim().charAt(0).toUpperCase();
      avatarBtn.textContent = initial;
      avatarBtn.title = user.email || '';
    } else {
      // Keep PHP-rendered state. If a PHP session marked the avatar visible, don't override.
      if (avatarBtn.style.display !== 'flex') {
        signinBtn.style.display = 'inline-flex';
        if (signupBtn) signupBtn.style.display = 'inline-flex';
      }
    }
  }

  if (sb) {
    sb.auth.getSession().then(({ data }) => renderAuth(data.session?.user));
    sb.auth.onAuthStateChange((_e, session) => renderAuth(session?.user));
  }

  // If Supabase configured AND user not yet PHP-logged-in, open modal; else fall back to /login.php (default href).
  if (sb) {
    signinBtn?.addEventListener('click', (e) => {
      if (avatarBtn && avatarBtn.style.display === 'flex') return;
      e.preventDefault(); openAuthModal();
    });
  }
  if (sb) {
    avatarBtn?.addEventListener('click', (e) => {
      // Only intercept if Supabase session is the source of truth
      e.preventDefault(); openAccountMenu();
    });
  }

  // Inline auth modal (built lazily)
  function openAuthModal() {
    if (!sb) { alert(t('Login configure भएको छैन। SETUP-NEW-FEATURES.md हेर्नुस्।','Auth not configured — see SETUP-NEW-FEATURES.md')); return; }
    if (document.getElementById('auth-modal')) return;
    const wrap = document.createElement('div');
    wrap.id = 'auth-modal';
    wrap.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,.4);backdrop-filter:blur(4px);z-index:1100;display:flex;align-items:center;justify-content:center;padding:16px;';
    wrap.innerHTML = `
      <div style="width:100%;max-width:380px;background:#fff;border-radius:14px;padding:24px;box-shadow:0 24px 60px rgba(15,23,42,.18);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
          <h3 style="font-size:1.125rem;font-weight:700;color:#0f172a;">${t('लग-इन','Sign in')}</h3>
          <button id="am-close" style="background:none;border:none;cursor:pointer;color:#94a3b8;padding:4px;">✕</button>
        </div>
        <p style="font-size:.8125rem;color:#64748b;margin-bottom:18px;">${t('Saathi sanga chat history sync गर्नुस्, Morning Brief enable गर्नुस्।','Sync your chat history and enable Morning Brief.')}</p>
        <button id="am-google" style="width:100%;display:flex;align-items:center;justify-content:center;gap:10px;padding:11px;border-radius:10px;border:1px solid #e7e5e4;background:#fff;cursor:pointer;font-weight:500;color:#0f172a;margin-bottom:14px;">
          <svg width="18" height="18" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.79 2.72v2.26h2.9c1.7-1.56 2.69-3.86 2.69-6.62z"/><path fill="#34A853" d="M9 18c2.43 0 4.46-.8 5.95-2.18l-2.9-2.26c-.8.54-1.83.86-3.05.86-2.34 0-4.33-1.58-5.04-3.7H.96v2.32A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.96 10.72A5.42 5.42 0 0 1 3.68 9c0-.6.1-1.18.28-1.72V4.96H.96A9 9 0 0 0 0 9c0 1.45.35 2.82.96 4.04l3-2.32z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.5.45 3.44 1.34l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .96 4.96l3 2.32C4.67 5.16 6.66 3.58 9 3.58z"/></svg>
          ${t('Google मार्फत','Continue with Google')}
        </button>
        <div style="display:flex;align-items:center;gap:10px;color:#94a3b8;font-size:.75rem;margin-bottom:14px;"><div style="flex:1;height:1px;background:#e7e5e4;"></div>${t('वा','or')}<div style="flex:1;height:1px;background:#e7e5e4;"></div></div>
        <form id="am-email-form" style="display:flex;flex-direction:column;gap:10px;">
          <input type="email" name="email" placeholder="${t('इमेल','Email')}" required class="inp"/>
          <input type="password" name="password" placeholder="${t('पासवर्ड (कम्तीमा ६ अक्षर)','Password (min 6 chars)')}" minlength="6" required class="inp"/>
          <div id="am-error" style="display:none;color:#b91c1c;font-size:.8125rem;"></div>
          <div style="display:flex;gap:8px;">
            <button type="submit" data-mode="signin" class="btn-primary" style="flex:1;padding:10px;border:none;cursor:pointer;font-weight:500;">${t('लग-इन','Sign in')}</button>
            <button type="submit" data-mode="signup" class="btn-outline" style="flex:1;padding:10px;cursor:pointer;font-weight:500;">${t('दर्ता','Sign up')}</button>
          </div>
        </form>
      </div>`;
    document.body.appendChild(wrap);
    wrap.addEventListener('click', e => { if (e.target === wrap) wrap.remove(); });
    wrap.querySelector('#am-close').onclick = () => wrap.remove();
    wrap.querySelector('#am-google').onclick = async () => {
      await sb.auth.signInWithOAuth({ provider: 'google', options: { redirectTo: window.location.origin + window.location.pathname } });
    };
    wrap.querySelector('#am-email-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const mode = e.submitter?.dataset.mode || 'signin';
      const fd = new FormData(e.target);
      const email = fd.get('email'), password = fd.get('password');
      const errEl = wrap.querySelector('#am-error');
      errEl.style.display = 'none';
      const fn = mode === 'signup' ? sb.auth.signUp({ email, password }) : sb.auth.signInWithPassword({ email, password });
      const { error } = await fn;
      if (error) { errEl.textContent = error.message; errEl.style.display = 'block'; }
      else { wrap.remove(); }
    });
  }

  function openAccountMenu() {
    const existing = document.getElementById('acct-menu');
    if (existing) { existing.remove(); return; }
    const m = document.createElement('div');
    m.id = 'acct-menu';
    m.style.cssText = 'position:fixed;top:54px;right:12px;background:#fff;border:1px solid #e7e5e4;border-radius:10px;box-shadow:0 12px 32px rgba(15,23,42,.1);min-width:200px;z-index:1100;padding:6px;';
    m.innerHTML = `
      <div style="padding:10px 12px;border-bottom:1px solid #f1f5f9;margin-bottom:4px;">
        <div style="font-size:.75rem;color:#94a3b8;">${t('लग इन','Signed in')}</div>
        <div id="acct-email" style="font-size:.8125rem;color:#0f172a;font-weight:500;"></div>
      </div>
      <a href="/morning-brief.php" style="display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:7px;color:#334155;font-size:.875rem;"><i data-lucide="sunrise" class="ic-sm"></i>${t('बिहानी ब्रिफ','Morning Brief')}</a>
      <button id="acct-signout" style="width:100%;display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:7px;color:#b91c1c;font-size:.875rem;background:none;border:none;cursor:pointer;text-align:left;"><i data-lucide="log-out" class="ic-sm"></i>${t('लग-आउट','Sign out')}</button>`;
    document.body.appendChild(m);
    sb.auth.getUser().then(({ data }) => { m.querySelector('#acct-email').textContent = data.user?.email || ''; });
    window.lucide && lucide.createIcons({ icons: lucide.icons, attrs: {} });
    m.querySelector('#acct-signout').onclick = async () => { await sb.auth.signOut(); m.remove(); };
    setTimeout(() => { document.addEventListener('click', function h(e){ if(!m.contains(e.target)){ m.remove(); document.removeEventListener('click', h);} }); }, 100);
  }

  // ═════════════════════════ CHAT HISTORY HELPERS ═════════════════════════
  // Exposed for ai-guide.php / ai-assistant include to call.
  async function saveChat(sessionId, role, content) {
    if (!sb) return;
    const { data: { user } } = await sb.auth.getUser();
    if (!user) return; // not signed in → skip silently
    await sb.from('chat_messages').insert({
      user_id: user.id, session_id: sessionId, role, content, created_at: new Date().toISOString(),
    });
  }
  async function loadChatHistory(sessionId, limit = 50) {
    if (!sb) return [];
    const { data: { user } } = await sb.auth.getUser();
    if (!user) return [];
    const { data } = await sb.from('chat_messages')
      .select('role, content, created_at')
      .eq('user_id', user.id).eq('session_id', sessionId)
      .order('created_at', { ascending: true }).limit(limit);
    return data || [];
  }
  async function listChatSessions() {
    if (!sb) return [];
    const { data: { user } } = await sb.auth.getUser();
    if (!user) return [];
    const { data } = await sb.rpc('list_chat_sessions').catch(() => ({ data: null }));
    if (data) return data;
    // Fallback: derive from messages
    const { data: rows } = await sb.from('chat_messages')
      .select('session_id, content, created_at')
      .eq('user_id', user.id).order('created_at', { ascending: false }).limit(200);
    const seen = {};
    (rows || []).forEach(r => { if (!seen[r.session_id]) seen[r.session_id] = { id: r.session_id, preview: r.content.slice(0, 60), updated_at: r.created_at }; });
    return Object.values(seen);
  }

  // ═════════════════════════ ⌘K COMMAND PALETTE ═════════════════════════
  const cmdkBackdrop = document.getElementById('cmdk-backdrop');
  const cmdkInput    = document.getElementById('cmdk-input');
  const cmdkList     = document.getElementById('cmdk-list');
  const cmdkTrigger  = document.getElementById('cmdk-trigger');

  const NAV_ITEMS = [
    { icon:'home',           label:t('गृहपृष्ठ','Home'),             href:'/' },
    { icon:'newspaper',      label:t('AI समाचार','AI News'),         href:'/news.php' },
    { icon:'sunrise',        label:t('बिहानी ब्रिफ','Morning Brief'), href:'/morning-brief.php' },
    { icon:'calendar-days',  label:t('नेपाली पात्रो','Nepali Patro'),  href:'/nepali-patro.php' },
    { icon:'sparkles',       label:t('राशिफल','Rashifal'),           href:'/rashifal.php' },
    { icon:'message-circle', label:t('सहायक AI','Sahayak AI'),       href:'#', action:'ask-ai' },
    { icon:'wrench',         label:t('टुलहरू','Tools'),               href:'/tools.php' },
    { icon:'bell',           label:t('सूचनाहरू','Notices'),           href:'/notices.php' },
    { icon:'siren',          label:t('अलर्टहरू','Alerts'),            href:'/alerts.php' },
  ];

  const TOOL_ITEMS = [
    { icon:'languages',  label:t('Preeti → Unicode','Preeti → Unicode'),  href:'/tools.php#preeti-unicode' },
    { icon:'calendar',   label:t('BS ↔ AD मिति','BS ↔ AD Date'),         href:'/tools.php#date-converter' },
    { icon:'banknote',   label:t('विनिमय दर','Currency'),                href:'/tools.php#currency' },
    { icon:'calculator', label:t('EMI Calculator','EMI Calculator'),     href:'/tools.php#emi-calc' },
  ];

  // Smart parsers — return null if no match, otherwise a result card
  function smartParse(q) {
    const qs = q.trim().toLowerCase();
    if (!qs) return null;

    // EMI: "emi 25 lakh", "emi 5000000 10 5" (principal rate years)
    const emiMatch = qs.match(/^emi[\s,]+([\d.,]+)\s*(lakh|crore|k)?(?:\s+([\d.]+))?(?:\s+([\d.]+))?$/);
    if (emiMatch) {
      let P = parseFloat(emiMatch[1].replace(/,/g,''));
      const unit = emiMatch[2];
      if (unit === 'lakh')  P *= 100000;
      if (unit === 'crore') P *= 10000000;
      if (unit === 'k')     P *= 1000;
      const annualRate = parseFloat(emiMatch[3] || 10);    // default 10%
      const years      = parseFloat(emiMatch[4] || 15);    // default 15 yr
      const r = annualRate/12/100, n = years*12;
      const emi = (P * r * Math.pow(1+r, n)) / (Math.pow(1+r, n) - 1);
      const total = emi * n;
      return `<div class="cmdk-result"><b>EMI:</b> रू ${Math.round(emi).toLocaleString('en-IN')}/month<br><span style="color:#64748b;font-size:.8125rem;">${years}yr @ ${annualRate}% • Total: रू ${Math.round(total).toLocaleString('en-IN')} • Principal: रू ${P.toLocaleString('en-IN')}</span></div>`;
    }

    // USD/Gold/NEPSE quick rates — pull from existing market-data API
    if (/^(usd|dollar|डलर)/i.test(qs)) {
      fetchAndShow('/api/market-data.php?type=forex', d => {
        const usd = (d.rates || []).find(r => r.code === 'USD');
        return `<div class="cmdk-result"><b>USD → NPR:</b> रू ${usd?.sell || '—'}<br><span style="color:#64748b;font-size:.8125rem;">${t('NRB official rate','NRB official rate')}</span></div>`;
      });
      return `<div class="cmdk-result">${t('लोड हुँदै…','Loading…')}</div>`;
    }
    if (/^(gold|सुन)/i.test(qs)) {
      fetchAndShow('/api/market-data.php?type=gold',
        d => `<div class="cmdk-result"><b>Gold:</b> रू ${Number(d?.hallmarkPerTola || 0).toLocaleString('en-IN')}/तोला<br><span style="color:#64748b;font-size:.8125rem;">${t('Live market estimate','Live market estimate')}</span></div>`);
      return `<div class="cmdk-result">${t('लोड हुँदै…','Loading…')}</div>`;
    }

    // Festival quick lookup
    const FEST = {
      tihar:    {ne:'तिहार',   approx:'Oct–Nov'},
      dashain:  {ne:'दशैं',    approx:'Sep–Oct'},
      holi:     {ne:'होली',    approx:'Mar'},
      teej:     {ne:'तीज',     approx:'Aug–Sep'},
      chhath:   {ne:'छठ',     approx:'Oct–Nov'},
      lhosar:   {ne:'ल्होसार',  approx:'Feb'},
    };
    for (const k in FEST) {
      if (qs.startsWith(k)) {
        return `<div class="cmdk-result"><b>${FEST[k].ne}</b> — ${FEST[k].approx}<br><a href="/nepali-patro.php" style="color:#15803d;font-size:.8125rem;">${t('पात्रो मा सटिक मिति हेर्नुस् →','Open Patro for exact date →')}</a></div>`;
      }
    }
    return null;
  }

  async function fetchAndShow(url, render) {
    try {
      const r = await fetch(url);
      const d = await r.json();
      const resEl = cmdkList.querySelector('.cmdk-result');
      if (resEl) resEl.outerHTML = render(d);
    } catch (e) { /* swallow */ }
  }

  function renderList(q) {
    const qs = q.trim().toLowerCase();
    let html = '';

    const smart = smartParse(qs);
    if (smart) html += `<div class="cmdk-section">${t('परिणाम','Result')}</div>` + smart;

    // Ask AI option
    if (qs.length > 2) {
      html += `<div class="cmdk-section">${t('AI सोध्नुस्','Ask AI')}</div>
        <div class="cmdk-item" data-action="ask-ai" data-q="${escapeHtml(qs)}">
          <i data-lucide="bot" class="cmdk-icon"></i>
          <span>${t('AI लाई सोध्नुस्','Ask AI')}: <b>${escapeHtml(qs)}</b></span>
          <span class="cmdk-hint">↵</span>
        </div>`;
    }

    const matchNav = NAV_ITEMS.filter(i => !qs || i.label.toLowerCase().includes(qs) || (qs && stripDeva(i.label).includes(qs)));
    if (matchNav.length) {
      html += `<div class="cmdk-section">${t('पृष्ठ','Pages')}</div>`;
      matchNav.forEach(i => {
        const attr = i.action ? ` data-action="${i.action}"` : '';
        html += `<a class="cmdk-item" href="${i.href}"${attr}><i data-lucide="${i.icon}" class="cmdk-icon"></i><span>${i.label}</span></a>`;
      });
    }
    const matchTool = TOOL_ITEMS.filter(i => !qs || i.label.toLowerCase().includes(qs));
    if (matchTool.length) {
      html += `<div class="cmdk-section">${t('टुलहरू','Tools')}</div>`;
      matchTool.forEach(i => {
        html += `<a class="cmdk-item" href="${i.href}"><i data-lucide="${i.icon}" class="cmdk-icon"></i><span>${i.label}</span></a>`;
      });
    }

    if (!html) html = `<div style="padding:32px;text-align:center;color:#94a3b8;font-size:.875rem;">${t('कुनै परिणाम छैन','No results')}</div>`;
    cmdkList.innerHTML = html;
    window.lucide && lucide.createIcons();
    setActive(0);
    cmdkList.querySelectorAll('.cmdk-item[data-action="ask-ai"]').forEach(el => {
      el.addEventListener('click', () => {
        const q = el.dataset.q || '';
        closeCmdk && closeCmdk();
        // Open the floating Sahayak AI assistant and prefill the question
        if (typeof window.aiOpenWith === 'function') {
          window.aiOpenWith(q);
        } else if (typeof window.aiToggle === 'function') {
          window.aiToggle();
          setTimeout(() => {
            const input = document.getElementById('ai-input');
            if (input && q) { input.value = q; input.focus(); }
          }, 200);
        }
      });
    });
  }

  function stripDeva(s){ return s; }
  function escapeHtml(s){ return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

  let activeIdx = 0;
  function activeItems() { return cmdkList.querySelectorAll('.cmdk-item'); }
  function setActive(i) {
    const items = activeItems();
    if (!items.length) return;
    activeIdx = (i + items.length) % items.length;
    items.forEach((el, idx) => el.classList.toggle('is-active', idx === activeIdx));
    items[activeIdx]?.scrollIntoView({ block: 'nearest' });
  }

  function openCmdk() {
    cmdkBackdrop.classList.add('open');
    cmdkInput.value = '';
    renderList('');
    setTimeout(() => cmdkInput.focus(), 30);
  }
  function closeCmdk() { cmdkBackdrop.classList.remove('open'); }

  cmdkTrigger?.addEventListener('click', openCmdk);
  cmdkBackdrop?.addEventListener('click', e => { if (e.target === cmdkBackdrop) closeCmdk(); });
  cmdkInput?.addEventListener('input', e => renderList(e.target.value));
  cmdkInput?.addEventListener('keydown', e => {
    if (e.key === 'ArrowDown') { e.preventDefault(); setActive(activeIdx + 1); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(activeIdx - 1); }
    else if (e.key === 'Enter') {
      const items = activeItems();
      if (items[activeIdx]) { e.preventDefault(); items[activeIdx].click(); }
    } else if (e.key === 'Escape') { closeCmdk(); }
  });
  document.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); openCmdk(); }
    if (e.key === '/' && document.activeElement === document.body) { e.preventDefault(); openCmdk(); }
  });

  // ═════════════════════════ MORNING BRIEF PUSH SUBSCRIBE ═════════════════════════
  async function subscribeMorningBrief() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
      alert(t('यो device ले push notification support गर्दैन।','This device does not support push.'));
      return false;
    }
    if (!cfg.vapidPublic) {
      alert(t('Push configure भएको छैन। SETUP-NEW-FEATURES.md हेर्नुस्।','Push not configured — see SETUP-NEW-FEATURES.md'));
      return false;
    }
    const perm = await Notification.requestPermission();
    if (perm !== 'granted') return false;
    const reg = await navigator.serviceWorker.register('/service-worker.js');
    const sub = await reg.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(cfg.vapidPublic),
    });
    const user = sb ? (await sb.auth.getUser()).data.user : null;
    await fetch('/api/push-subscribe.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ subscription: sub, user_id: user?.id || null, lang }),
    });
    return true;
  }

  function urlBase64ToUint8Array(b64) {
    const padding = '='.repeat((4 - b64.length % 4) % 4);
    const base64 = (b64 + padding).replace(/-/g,'+').replace(/_/g,'/');
    const raw = atob(base64);
    const arr = new Uint8Array(raw.length);
    for (let i = 0; i < raw.length; ++i) arr[i] = raw.charCodeAt(i);
    return arr;
  }



  // ═════════════════════════ PWA + LOCAL NOTIFICATION CENTER ═════════════════════════
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/service-worker.js').catch(() => {});
    });
  }

  const notifyStoreKey = 'nsh_rate_alerts';
  function getRateAlerts() {
    try { return JSON.parse(localStorage.getItem(notifyStoreKey) || '[]'); } catch(e) { return []; }
  }
  function saveRateAlerts(rows) { localStorage.setItem(notifyStoreKey, JSON.stringify(rows.slice(-20))); }
  function addRateAlert(kind, threshold) {
    const rows = getRateAlerts();
    rows.push({ kind, threshold: Number(threshold), done: false, createdAt: Date.now() });
    saveRateAlerts(rows);
    if ('Notification' in window && Notification.permission === 'default') Notification.requestPermission().catch(() => {});
    alert(t('अलर्ट सेभ भयो। मूल्य पुगेपछि यही browser मा notification आउँछ।','Alert saved. You will be notified in this browser when the rate is reached.'));
  }
  function openRateAlertModal(kind = 'usd') {
    const old = document.getElementById('rate-alert-modal'); if (old) old.remove();
    const wrap = document.createElement('div');
    wrap.id = 'rate-alert-modal';
    wrap.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,.35);backdrop-filter:blur(4px);z-index:2147482998;display:flex;align-items:center;justify-content:center;padding:16px;';
    wrap.innerHTML = `<form id="rate-alert-form" style="width:100%;max-width:360px;background:#fff;border:1px solid #e7e5e4;border-radius:14px;padding:20px;box-shadow:0 24px 60px rgba(15,23,42,.18);">
      <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:12px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;">${t('Rate Alert सेट','Set rate alert')}</h3>
        <button type="button" id="rate-alert-close" style="border:0;background:transparent;color:#64748b;cursor:pointer;font-size:20px;">×</button>
      </div>
      <label style="font-size:12px;color:#64748b;font-weight:600;display:block;margin:10px 0 5px;">${t('कुन मूल्य?','Which rate?')}</label>
      <select name="kind" style="width:100%;border:1px solid #e7e5e4;border-radius:10px;padding:10px;color:#0f172a;background:#fff;font-family:inherit;">
        <option value="usd" ${kind === 'usd' ? 'selected' : ''}>USD/NPR</option>
        <option value="gold" ${kind === 'gold' ? 'selected' : ''}>Gold per tola</option>
      </select>
      <label style="font-size:12px;color:#64748b;font-weight:600;display:block;margin:12px 0 5px;">${t('रू पुगेपछि notify','Notify when reaches NPR')}</label>
      <input name="threshold" inputmode="decimal" required placeholder="134" style="width:100%;border:1px solid #e7e5e4;border-radius:10px;padding:10px;color:#0f172a;background:#f8fafc;font-family:inherit;" />
      <button type="submit" style="margin-top:14px;width:100%;border:0;border-radius:10px;background:#15803d;color:#fff;padding:11px;font-weight:700;cursor:pointer;">${t('Alert सेभ','Save alert')}</button>
    </form>`;
    document.body.appendChild(wrap);
    wrap.addEventListener('click', e => { if (e.target === wrap) wrap.remove(); });
    wrap.querySelector('#rate-alert-close').onclick = () => wrap.remove();
    wrap.querySelector('#rate-alert-form').addEventListener('submit', e => {
      e.preventDefault();
      addRateAlert(e.target.kind.value, e.target.threshold.value);
      wrap.remove();
    });
  }
  async function checkRateAlerts() {
    const rows = getRateAlerts().filter(r => !r.done && Number(r.threshold));
    if (!rows.length || !('Notification' in window)) return;
    try {
      const res = await fetch('/api/market-data.php?type=all');
      const d = await res.json();
      const usd = (d.forex?.rates || []).find(r => r.code === 'USD')?.sell;
      const gold = d.gold?.hallmarkPerTola;
      let changed = false;
      rows.forEach(r => {
        const val = r.kind === 'gold' ? gold : usd;
        if (Number(val) >= Number(r.threshold)) {
          r.done = true; changed = true;
          if (Notification.permission === 'granted') {
            new Notification(r.kind === 'gold' ? 'Gold alert' : 'USD alert', { body: `${r.kind.toUpperCase()} रू ${val} पुगेको छ`, icon: '/assets/favicon.svg' });
          }
        }
      });
      if (changed) saveRateAlerts(rows.concat(getRateAlerts().filter(r => r.done)));
    } catch(e) {}
  }
  setTimeout(checkRateAlerts, 4000);
  setInterval(checkRateAlerts, 30 * 60 * 1000);

  window.NSHAlerts = { addRateAlert, openRateAlertModal, checkRateAlerts, list: getRateAlerts };

  // ═════════════════════════ SPA NAVIGATION ═════════════════════════
  // Intercepts internal link clicks and loads pages via AJAX for smooth transitions
  (function initSPANavigation() {
    // Don't enable SPA for embed mode or admin pages
    if (location.search.includes('embed=1')) return;
    if (location.pathname.startsWith('/admin/')) return;
    if (!history.pushState) return; // Browser support check

    let isNavigating = false;
    const mainContent = document.getElementById('app-shell') || document.querySelector('main') || document.querySelector('.app-shell') || document.body;
    const progressBar = document.createElement('div');
    progressBar.className = 'nsh-page-progress';
    progressBar.innerHTML = '<div class="nsh-progress-bar"></div>';
    document.body.appendChild(progressBar);

    // Add global styles for progress bar and transitions
    const style = document.createElement('style');
    style.textContent = `
      .nsh-page-progress { position:fixed; top:0; left:0; right:0; z-index:9999; height:3px; background:transparent; pointer-events:none; }
      .nsh-progress-bar { height:100%; width:0; background:linear-gradient(90deg, #0d9488, #14b8a6); transition:width 0.3s ease; }
      .nsh-page-progress.loading .nsh-progress-bar { width:70%; transition:width 0.5s ease; }
      .nsh-page-progress.loaded .nsh-progress-bar { width:100%; transition:width 0.2s ease; }
      .nsh-page-transition { opacity:0; transform:translateY(8px); }
      .nsh-page-transition.in { opacity:1; transform:translateY(0); transition:opacity 0.25s ease, transform 0.25s ease; }
    `;
    document.head.appendChild(style);

    function isInternalLink(href) {
      try {
        const url = new URL(href, location.origin);
        return url.origin === location.origin && !url.hash && !url.search.includes('embed=1');
      } catch { return false; }
    }

    async function loadPage(url, push = true) {
      if (isNavigating) return;
      isNavigating = true;
      progressBar.classList.add('loading');

      try {
        const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!resp.ok) throw new Error('Failed to load');
        const html = await resp.text();

        // Parse the response
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.getElementById('app-shell') || doc.querySelector('main') || doc.querySelector('.app-shell') || doc.body;
        const newTitle = doc.querySelector('title')?.textContent || document.title;

        // Update content with transition
        mainContent.classList.add('nsh-page-transition');
        await new Promise(r => setTimeout(r, 50));

        // Extract and update content
        if (newContent) {
          // Update main content - preserve the container but replace inner
          if (mainContent.id === 'app-shell' && newContent.id === 'app-shell') {
            mainContent.innerHTML = newContent.innerHTML;
          } else if (mainContent.tagName === 'MAIN' && newContent.tagName === 'MAIN') {
            mainContent.innerHTML = newContent.innerHTML;
          } else {
            // Fallback: replace the entire container
            mainContent.outerHTML = newContent.outerHTML;
          }
        }

        // Update title
        document.title = newTitle;

        // Push to history
        if (push) history.pushState({ url }, newTitle, url);

        // Re-initialize page-specific scripts
        progressBar.classList.remove('loading');
        progressBar.classList.add('loaded');
        setTimeout(() => progressBar.classList.remove('loaded'), 300);

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Re-init Lucide icons
        if (window.lucide && lucide.createIcons) lucide.createIcons();

        // Re-run page scripts (news load, etc)
        if (typeof window.loadNews === 'function') window.loadNews();
        if (typeof window.loadContent === 'function') window.loadContent('ne');

        // Dispatch custom event for other scripts
        window.dispatchEvent(new CustomEvent('nsh:pagechange', { detail: { url } }));

      } catch (err) {
        // Fallback to normal navigation on error
        progressBar.classList.remove('loading');
        location.href = url;
      } finally {
        isNavigating = false;
      }
    }

    // Intercept link clicks
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href]');
      if (!link) return;

      const href = link.getAttribute('href');
      if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
      if (link.target === '_blank' || link.hasAttribute('download')) return;
      if (!isInternalLink(href)) return;

      // Skip if modifier keys pressed
      if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

      e.preventDefault();
      loadPage(href);
    }, { passive: false });

    // Handle browser back/forward
    window.addEventListener('popstate', (e) => {
      if (e.state && e.state.url) {
        loadPage(e.state.url, false);
      }
    });

    // Set initial state
    if (!history.state) {
      history.replaceState({ url: location.href }, document.title, location.href);
    }

    // Make loadPage available globally for programmatic navigation
    window.NSHNavigate = loadPage;
  })();

  // ═════════════════════════ INSTALL APP PROMPT ═════════════════════════
  let deferredInstallPrompt = null;
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredInstallPrompt = e;
    document.querySelectorAll('[data-nsh-install]').forEach(el => { el.style.display = 'inline-flex'; });
  });
  async function installApp() {
    if (!deferredInstallPrompt) {
      alert(t('Browser menu बाट “Add to Home screen” छान्नुस्।','Choose “Add to Home screen” from your browser menu.'));
      return false;
    }
    deferredInstallPrompt.prompt();
    await deferredInstallPrompt.userChoice.catch(() => null);
    deferredInstallPrompt = null;
    return true;
  }
  window.NSHInstall = installApp;

  // ═════════════════════════ PUBLIC API ═════════════════════════
  window.NSH = {
    sb,
    auth: { open: openAuthModal, signOut: () => sb?.auth.signOut() },
    cmdk: { open: openCmdk, close: closeCmdk },
    chat: { save: saveChat, history: loadChatHistory, list: listChatSessions },
    push: { subscribe: subscribeMorningBrief },
    alerts: window.NSHAlerts,
    install: installApp,
    t,
  };
})();
