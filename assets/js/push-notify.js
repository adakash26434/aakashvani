/**
 * push-notify.js — Web Push opt-in helper + floating prompt
 * Auto-registers /sw.js, shows a small bell prompt after 8s if push not enabled.
 * VAPID public key is fetched from /api/push-subscribe.php?key (returns {key:'...'}).
 * Stores subscription via POST /api/push-subscribe.php
 */
(function(){
  if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

  // Register SW (idempotent)
  navigator.serviceWorker.register('/sw.js').catch(function(e){ console.warn('SW reg failed', e); });

  function urlB64ToUint8(base64) {
    var padding = '='.repeat((4 - base64.length % 4) % 4);
    var b64 = (base64 + padding).replace(/-/g,'+').replace(/_/g,'/');
    var raw = atob(b64); var out = new Uint8Array(raw.length);
    for (var i=0;i<raw.length;i++) out[i] = raw.charCodeAt(i);
    return out;
  }

  function dismissed(){ try { return localStorage.getItem('nsh_push_dismiss') === '1'; } catch(_) { return false; } }
  function setDismissed(){ try { localStorage.setItem('nsh_push_dismiss','1'); } catch(_){} }

  async function subscribeUser() {
    try {
      var reg = await navigator.serviceWorker.ready;
      var existing = await reg.pushManager.getSubscription();
      if (existing) return existing;

      var keyRes = await fetch('/api/push-subscribe.php?key=1').then(r=>r.json()).catch(()=>null);
      var vapid = keyRes && keyRes.key ? keyRes.key : null;
      var opts = { userVisibleOnly: true };
      if (vapid) opts.applicationServerKey = urlB64ToUint8(vapid);

      var sub = await reg.pushManager.subscribe(opts);
      var lang = (document.documentElement.lang || 'ne').startsWith('en') ? 'en' : 'ne';
      await fetch('/api/push-subscribe.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ subscription: sub, lang: lang })
      });
      return sub;
    } catch (e) {
      console.warn('Push subscribe failed', e);
      return null;
    }
  }

  function showToast(msg, ok){
    var t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:'+(ok?'#0d9488':'#dc2626')+';color:#fff;padding:10px 16px;border-radius:10px;font-size:13px;font-weight:600;z-index:99999;box-shadow:0 4px 20px rgba(0,0,0,.2)';
    document.body.appendChild(t);
    setTimeout(function(){ t.style.opacity='0'; t.style.transition='opacity .3s'; setTimeout(function(){t.remove();}, 300); }, 2200);
  }

  function showPrompt(){
    if (Notification.permission !== 'default') return;
    if (dismissed()) return;
    if (document.getElementById('nsh-push-prompt')) return;

    var bar = document.createElement('div');
    bar.id = 'nsh-push-prompt';
    bar.style.cssText = 'position:fixed;bottom:74px;left:12px;right:12px;max-width:420px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:12px 14px;box-shadow:0 8px 28px -10px rgba(15,23,42,.25);z-index:9998;display:flex;align-items:center;gap:10px;font-family:inherit;animation:nshPushSlide .35s ease';
    bar.innerHTML =
      '<div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:18px">🔔</div>'+
      '<div style="flex:1;min-width:0">'+
        '<div style="font-size:13px;font-weight:700;color:#0b1220">Live समाचार र Alert</div>'+
        '<div style="font-size:11.5px;color:#64748b">भूकम्प, मौसम, IPO र ताजा खबर — सीधै फोनमा</div>'+
      '</div>'+
      '<button id="nsh-push-yes" style="background:#0d9488;color:#fff;border:0;padding:7px 12px;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer">सक्रिय गर्नुस्</button>'+
      '<button id="nsh-push-no" style="background:transparent;border:0;color:#94a3b8;font-size:18px;cursor:pointer;padding:4px 8px" aria-label="dismiss">×</button>';
    var s = document.createElement('style');
    s.textContent = '@keyframes nshPushSlide{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}';
    document.head.appendChild(s);
    document.body.appendChild(bar);

    document.getElementById('nsh-push-no').onclick = function(){ setDismissed(); bar.remove(); };
    document.getElementById('nsh-push-yes').onclick = async function(){
      bar.remove();
      var perm = await Notification.requestPermission();
      if (perm !== 'granted') { showToast('अनुमति दिइएन', false); return; }
      var s = await subscribeUser();
      showToast(s ? '✓ Notifications सक्रिय' : 'सब्सक्राइब असफल', !!s);
    };
  }

  window.nshPushSubscribe = async function(){
    var perm = Notification.permission;
    if (perm === 'denied') { showToast('Browser बाट अनुमति बन्द गरिएको छ', false); return; }
    if (perm === 'default') {
      perm = await Notification.requestPermission();
      if (perm !== 'granted') { showToast('अनुमति दिइएन', false); return; }
    }
    var s = await subscribeUser();
    showToast(s ? '✓ Notifications सक्रिय' : 'सब्सक्राइब असफल', !!s);
  };

  // Show prompt 8s after page settles
  if (document.readyState === 'complete') setTimeout(showPrompt, 8000);
  else window.addEventListener('load', function(){ setTimeout(showPrompt, 8000); });
})();
