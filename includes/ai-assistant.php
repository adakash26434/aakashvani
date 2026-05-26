<?php /* Sahayak AI — Floating Assistant (light theme, auto-included from footer) */
// Defensive: ensure h() helper exists (functions.php may not be loaded in some contexts)
if (!function_exists('h')) {
    function h(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
?>
<!-- ═══ FLOATING AI ASSISTANT ════════════════════════════════════════════════ -->
<style>
@keyframes aiBlink   { 0%,100%{opacity:1} 50%{opacity:.3} }
@keyframes aiBadgePop{ from{transform:scale(.6) translateY(8px);opacity:0} to{transform:scale(1) translateY(0);opacity:1} }
@keyframes aiMsgSlide{ from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }
.ai-msg-anim { animation: aiMsgSlide .22s ease both; }
#ai-fab        { transition:transform .2s, box-shadow .2s; visibility:visible !important; opacity:1 !important; }
#ai-fab:hover  { transform:scale(1.06); box-shadow:0 8px 28px rgba(21,128,61,.32); }
#ai-input:focus{ border-color:#15803d; box-shadow:0 0 0 3px rgba(21,128,61,.12); }
#ai-voice-btn:hover { color:#15803d; }
.ai-quick-btn:hover { background:#f0fdf4; color:#15803d; border-color:#86efac; }
#ai-send-btn:hover  { background:#166534; }
.ai-msg-content strong { color:#0f172a; font-weight:600; }
.ai-msg-content em     { color:#475569; font-style:italic; }
.ai-msg-content code   { background:#f1f5f9; color:#15803d; padding:1px 5px; border-radius:4px; font-family:ui-monospace,monospace; font-size:12px; }
.ai-msg-content ul     { list-style:disc; padding-left:18px; margin:6px 0; }
.ai-msg-content li     { margin:2px 0; }
@media (max-width:1023px) {
  #ai-fab-wrap #ai-fab { bottom:80px !important; }
  #ai-fab-wrap #ai-badge { bottom:128px !important; }
  #ai-panel { bottom:150px !important; }
}
</style>

<div id="ai-fab-wrap">
  <!-- FAB -->
  <button id="ai-fab" type="button" onclick="aiToggle()" aria-label="AI Assistant खोल्नुस्"
    style="position:fixed;bottom:24px;right:24px;z-index:2147483000;
           width:56px;height:56px;border-radius:50%;
           background:#15803d;border:none;cursor:pointer;
           box-shadow:0 6px 20px rgba(21,128,61,.35);
           display:flex;align-items:center;justify-content:center;color:#fff;">
    <svg id="ai-fab-icon" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.582a.5.5 0 0 1 0 .962L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/></svg>
    <svg id="ai-fab-close" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
  </button>

  <!-- Greeting badge -->
  <div id="ai-badge" style="position:fixed;bottom:80px;right:24px;z-index:2147482999;
       background:#0f172a;color:#fff;font-size:11px;font-weight:500;
       padding:5px 10px;border-radius:8px;
       animation:aiBadgePop .35s ease both;pointer-events:none;
       box-shadow:0 4px 14px rgba(15,23,42,.18);">
    <?= ($lang ?? 'ne') === 'en' ? 'Ask Sahayak AI' : 'नमस्ते — सहायक AI सोध्नुस्' ?>
  </div>
</div>

<!-- Chat panel -->
<div id="ai-panel" style="
  position:fixed;bottom:92px;right:24px;z-index:2147482999;
  width:min(400px, calc(100vw - 32px));
  background:#ffffff;border:1px solid #e7e5e4;border-radius:16px;
  box-shadow:0 20px 50px rgba(15,23,42,.18);
  display:none;flex-direction:column;
  max-height:calc(100vh - 130px); overflow:hidden;">

  <!-- Header -->
  <div style="border-bottom:1px solid #f1f5f9;padding:14px 16px;display:flex;align-items:center;gap:10px;background:#fafaf9;">
    <div style="width:34px;height:34px;border-radius:50%;background:#15803d;
         display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;">
      <i data-lucide="sparkles" style="width:16px;height:16px;"></i>
    </div>
    <div style="flex:1;">
      <div style="font-weight:600;color:#0f172a;font-size:.9375rem;line-height:1.2;"><?= ($lang ?? 'ne') === 'en' ? 'Sahayak AI' : 'सहायक AI' ?></div>
      <div style="font-size:11px;color:#15803d;display:flex;align-items:center;gap:5px;">
        <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;animation:aiBlink 2s infinite;display:inline-block;"></span>
        Nepal AI · Online
      </div>
    </div>
    <div style="display:flex;gap:3px;background:#fff;border:1px solid #e7e5e4;border-radius:8px;padding:2px;">
      <button id="btn-lang-ne" type="button" onclick="aiSetLang('ne')"
        style="padding:4px 9px;border-radius:6px;border:none;cursor:pointer;font-size:11px;font-weight:600;background:#15803d;color:#fff;transition:all .2s;">ने</button>
      <button id="btn-lang-en" type="button" onclick="aiSetLang('en')"
        style="padding:4px 9px;border-radius:6px;border:none;cursor:pointer;font-size:11px;font-weight:600;background:transparent;color:#64748b;transition:all .2s;">EN</button>
    </div>
    <button type="button" onclick="aiToggle()" aria-label="Close"
      style="background:none;border:none;cursor:pointer;color:#94a3b8;padding:4px;display:flex;">
      <i data-lucide="x" style="width:18px;height:18px;"></i>
    </button>
  </div>

  <!-- Messages -->
  <div id="ai-messages" style="flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:10px;min-height:200px;max-height:380px;background:#ffffff;">
    <div class="ai-msg-bot" style="display:flex;gap:8px;align-items:flex-start;">
      <div style="width:26px;height:26px;border-radius:50%;background:#f0fdf4;border:1px solid #bbf7d0;
           display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;color:#15803d;">
        <i data-lucide="sparkles" style="width:13px;height:13px;"></i>
      </div>
      <div style="background:#f8fafc;border:1px solid #e7e5e4;border-radius:0 12px 12px 12px;
           padding:10px 13px;max-width:85%;font-size:.875rem;color:#334155;line-height:1.6;">
        <span id="ai-welcome-ne">नमस्ते 🙏 म <strong style="color:#15803d;">सहायक AI</strong> हुँ।<br>
        सोध्नुस्: <em>सुनको भाउ, पेट्रोल, NEPSE, राशिफल, BS/AD मिति…</em></span>
        <span id="ai-welcome-en" style="display:none;">Hi 🙏 I'm <strong style="color:#15803d;">Sahayak AI</strong>.<br>
        Ask me: <em>gold price, petrol, NEPSE, rashifal, BS/AD date…</em></span>
      </div>
    </div>
  </div>

  <!-- Quick prompts -->
  <div id="ai-quick" style="padding:0 12px 8px;display:flex;flex-wrap:wrap;gap:5px;background:#fff;">
    <?php
    $qNe = ['सुनको भाउ','पेट्रोल मूल्य','NEPSE','आजको राशिफल','BS/AD मिति'];
    $qEn = ['Gold price','Petrol price','NEPSE','Today rashifal','BS/AD date'];
    foreach ($qNe as $i => $q): ?>
    <button class="ai-quick-btn" type="button" data-ne="<?= h($q) ?>" data-en="<?= h($qEn[$i]) ?>"
      onclick="aiQuickSend(this)"
      style="font-size:11px;padding:4px 10px;border-radius:20px;border:1px solid #e7e5e4;
             background:#fff;color:#64748b;cursor:pointer;white-space:nowrap;transition:all .15s;">
      <?= h($q) ?>
    </button>
    <?php endforeach; ?>
  </div>

  <!-- Input -->
  <div style="border-top:1px solid #f1f5f9;padding:10px;display:flex;gap:8px;align-items:flex-end;background:#fff;">
    <div style="flex:1;position:relative;">
      <textarea id="ai-input" rows="1"
        placeholder="यहाँ सोध्नुस्..."
        onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();aiSend();}"
        oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px'"
        style="width:100%;background:#f8fafc;border:1px solid #e7e5e4;color:#0f172a;
               border-radius:10px;padding:9px 36px 9px 12px;
               font-family:inherit;font-size:.875rem;line-height:1.5;
               resize:none;outline:none;max-height:120px;overflow-y:auto;transition:border-color .15s,box-shadow .15s;"></textarea>
      <button id="ai-voice-btn" type="button" onclick="aiVoiceInput()"
        style="position:absolute;right:6px;bottom:6px;background:none;border:none;cursor:pointer;
               width:26px;height:26px;display:flex;align-items:center;justify-content:center;
               color:#94a3b8;transition:color .15s;" title="Voice">
        <i data-lucide="mic" style="width:15px;height:15px;"></i>
      </button>
    </div>
    <button id="ai-send-btn" type="button" onclick="aiSend()"
      style="width:38px;height:38px;border-radius:10px;background:#15803d;border:none;cursor:pointer;
             display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;transition:background .15s;">
      <i data-lucide="send" style="width:16px;height:16px;"></i>
    </button>
  </div>
</div>

<script>
(function() {
var aiOpen = false;
var aiLang = '<?= isset($lang) ? $lang : "ne" ?>';
var aiHistory = [];
var aiStreaming = false;

function aiRemember(role, content) {
  try {
    var key = 'nsh_ai_recent';
    var rows = JSON.parse(localStorage.getItem(key) || '[]');
    rows.push({role: role, content: String(content).slice(0, 240), at: Date.now()});
    localStorage.setItem(key, JSON.stringify(rows.slice(-10)));
  } catch(e) {}
}

setTimeout(function() {
  var b = document.getElementById('ai-badge');
  if (b) { b.style.opacity='0'; b.style.transition='opacity .5s'; setTimeout(function(){b.remove();},500); }
}, 5000);

window.aiToggle = function() {
  aiOpen = !aiOpen;
  var panel = document.getElementById('ai-panel');
  var icon  = document.getElementById('ai-fab-icon');
  var close = document.getElementById('ai-fab-close');
  if (aiOpen) {
    panel.style.display = 'flex';
    icon.style.display = 'none';
    close.style.display = 'inline-block';
    var b = document.getElementById('ai-badge'); if (b) b.remove();
    setTimeout(function(){ var i = document.getElementById('ai-input'); i && i.focus(); }, 50);
  } else {
    panel.style.display = 'none';
    icon.style.display = 'inline-block';
    close.style.display = 'none';
  }
};

window.aiOpenWith = function(q) {
  if (!aiOpen) window.aiToggle();
  setTimeout(function() {
    var input = document.getElementById('ai-input');
    if (!input) return;
    input.value = q || '';
    input.focus();
    if (q) window.aiSend();
  }, 200);
};

window.aiSetLang = function(l) {
  aiLang = l;
  var ne = document.getElementById('btn-lang-ne');
  var en = document.getElementById('btn-lang-en');
  ne.style.background = l==='ne' ? '#15803d' : 'transparent';
  ne.style.color      = l==='ne' ? '#fff'    : '#64748b';
  en.style.background = l==='en' ? '#15803d' : 'transparent';
  en.style.color      = l==='en' ? '#fff'    : '#64748b';
  document.getElementById('ai-welcome-ne').style.display = l==='ne' ? '' : 'none';
  document.getElementById('ai-welcome-en').style.display = l==='en' ? '' : 'none';
  document.getElementById('ai-input').placeholder = l==='ne' ? 'यहाँ सोध्नुस्...' : 'Ask anything...';
  document.querySelectorAll('.ai-quick-btn').forEach(function(b) {
    b.textContent = l==='ne' ? b.dataset.ne : b.dataset.en;
  });
};

window.aiQuickSend = function(btn) {
  var msg = aiLang==='ne' ? btn.dataset.ne : btn.dataset.en;
  document.getElementById('ai-input').value = msg;
  aiSend();
};

window.aiSend = function() {
  if (aiStreaming) return;
  var input = document.getElementById('ai-input');
  var msg = input.value.trim();
  if (!msg) return;
  input.value = ''; input.style.height = 'auto';

  aiAppendMsg('user', escapeHtml(msg));
  aiHistory.push({role:'user', content:msg});
  aiRemember('user', msg);

  aiStreaming = true;
  var thinkingId = aiAppendMsg('bot', '<span style="color:#94a3b8;font-style:italic;">'+(aiLang==='ne'?'सोच्दैछु...':'thinking...')+'</span>');
  var botEl = document.getElementById(thinkingId);
  var content = '';

  fetch('/api/ai-chat.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({message:msg, lang:aiLang, history:aiHistory.slice(-8)})
  }).then(function(r) {
    if (!r.body) throw new Error('No stream');
    var reader = r.body.getReader();
    var decoder = new TextDecoder();
    var buffer = '';
    function read() {
      reader.read().then(function(res) {
        if (res.done) { aiStreaming=false; if (content) { aiHistory.push({role:'assistant',content:content}); aiRemember('assistant', content); } return; }
        buffer += decoder.decode(res.value, {stream:true});
        var lines = buffer.split('\n');
        buffer = lines.pop();
        lines.forEach(function(line) {
          if (!line.startsWith('data: ')) return;
          try {
            var d = JSON.parse(line.slice(6));
            if (d.done) { aiStreaming=false; if (content) { aiHistory.push({role:'assistant',content:content}); aiRemember('assistant', content); } return; }
            if (d.content) { content += d.content; if (botEl) botEl.innerHTML = aiMarkdown(content); }
            if (d.error)   { if (botEl) botEl.innerHTML = '<span style="color:#b91c1c;">'+escapeHtml(d.error)+'</span>'; }
          } catch(e){}
        });
        var msgs = document.getElementById('ai-messages');
        msgs.scrollTop = msgs.scrollHeight;
        read();
      }).catch(function(){ aiStreaming=false; });
    }
    read();
  }).catch(function() {
    if (botEl) botEl.innerHTML = '<span style="color:#b91c1c;">'+(aiLang==='ne'?'जडान त्रुटि — पुन: प्रयास गर्नुस्।':'Connection error. Try again.')+'</span>';
    aiStreaming=false;
  });
};

function aiAppendMsg(role, html) {
  var id = 'ai-msg-' + Date.now() + Math.random().toString(36).slice(2,6);
  var msgs = document.getElementById('ai-messages');
  var div = document.createElement('div');
  div.className = 'ai-msg-anim';
  div.style.cssText = 'display:flex;gap:8px;align-items:flex-start;'+(role==='user'?'justify-content:flex-end;':'');

  var inner = '<div class="ai-msg-content" id="'+id+'" style="'
    + (role==='user'
      ? 'background:#15803d;color:#fff;border-radius:12px 0 12px 12px;'
      : 'background:#f8fafc;border:1px solid #e7e5e4;color:#334155;border-radius:0 12px 12px 12px;')
    + 'padding:9px 13px;max-width:85%;font-size:.875rem;line-height:1.6;word-break:break-word;">'
    + html + '</div>';

  if (role === 'bot') {
    div.innerHTML = '<div style="width:26px;height:26px;border-radius:50%;background:#f0fdf4;border:1px solid #bbf7d0;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;color:#15803d;"><i data-lucide="sparkles" style="width:13px;height:13px;"></i></div>' + inner;
  } else {
    div.innerHTML = inner;
  }

  msgs.appendChild(div);
  msgs.scrollTop = msgs.scrollHeight;
  if (window.lucide) lucide.createIcons();
  return id;
}

function escapeHtml(s){ return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

function aiMarkdown(text) {
  return text
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>')
    .replace(/\*(.+?)\*/g,'<em>$1</em>')
    .replace(/`(.+?)`/g,'<code>$1</code>')
    .replace(/^### (.+)$/gm,'<h4 style="color:#15803d;font-weight:600;margin:8px 0 4px;">$1</h4>')
    .replace(/^## (.+)$/gm, '<h3 style="color:#0f172a;font-weight:700;margin:10px 0 4px;">$1</h3>')
    .replace(/^- (.+)$/gm, '<li>$1</li>')
    .replace(/(<li>.*<\/li>(\n)?)+/g, '<ul>$&</ul>')
    .replace(/\n/g,'<br>');
}

window.aiVoiceInput = function() {
  var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (!SR) { alert(aiLang==='ne'?'यो browser ले voice input support गर्दैन।':'Voice input not supported in this browser.'); return; }
  var r = new SR();
  r.lang = aiLang === 'ne' ? 'ne-NP' : 'en-US';
  r.interimResults = false;
  r.maxAlternatives = 1;
  var btn = document.getElementById('ai-voice-btn');
  btn.style.color = '#b91c1c';
  r.onresult = function(e) {
    document.getElementById('ai-input').value = e.results[0][0].transcript;
    btn.style.color = '#94a3b8';
  };
  r.onerror = r.onend = function() { btn.style.color = '#94a3b8'; };
  r.start();
};

setTimeout(function(){
  var fab = document.getElementById('ai-fab');
  if (fab) { fab.style.display='flex'; fab.style.visibility='visible'; fab.style.opacity='1'; }
  if (window.lucide && lucide.createIcons) lucide.createIcons();
}, 250);
})();
</script>
