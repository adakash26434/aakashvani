/*!
 * आकाशवाणी — Nepali (Bikram Sambat) Date Picker
 * Lightweight, dependency-free, ~6kb.
 *
 * Usage:
 *   <input type="text" name="dob_bs" data-nepali-date
 *          data-nepali-target="dob_ad"  (optional: hidden field receives AD date YYYY-MM-DD)
 *          placeholder="मिति छान्नुस्">
 *   <input type="hidden" name="dob_ad" id="dob_ad">
 *
 * Range: BS 2000 – 2090 (covers 1943–2033 AD).
 * Activates automatically on DOMContentLoaded.
 */
(function () {
  'use strict';

  // Days-in-month table for BS years 2000..2090 (months 1..12)
  // Source: standard published BS calendar tables.
  var BS_MONTHS = {
    2000:[30,32,31,32,31,30,30,30,29,30,29,31], 2001:[31,31,32,31,31,31,30,29,30,29,30,30],
    2002:[31,31,32,32,31,30,30,29,30,29,30,30], 2003:[31,32,31,32,31,30,30,30,29,29,30,31],
    2004:[30,32,31,32,31,30,30,30,29,30,29,31], 2005:[31,31,32,31,31,31,30,29,30,29,30,30],
    2006:[31,31,32,32,31,30,30,29,30,29,30,30], 2007:[31,32,31,32,31,30,30,30,29,29,30,31],
    2008:[31,31,31,32,31,31,29,30,30,29,29,31], 2009:[31,31,32,31,31,31,30,29,30,29,30,30],
    2010:[31,31,32,32,31,30,30,29,30,29,30,30], 2011:[31,32,31,32,31,30,30,30,29,29,30,31],
    2012:[31,31,31,32,31,31,29,30,30,29,30,30], 2013:[31,31,32,31,31,31,30,29,30,29,30,30],
    2014:[31,31,32,32,31,30,30,29,30,29,30,30], 2015:[31,32,31,32,31,30,30,30,29,29,30,31],
    2016:[31,31,31,32,31,31,29,30,30,29,30,30], 2017:[31,31,32,31,31,31,30,29,30,29,30,30],
    2018:[31,32,31,32,31,30,30,29,30,29,30,30], 2019:[31,32,31,32,31,30,30,30,29,30,29,31],
    2020:[31,31,31,32,31,31,30,29,30,29,30,30], 2021:[31,31,32,31,31,31,30,29,30,29,30,30],
    2022:[31,32,31,32,31,30,30,30,29,29,30,30], 2023:[31,32,31,32,31,30,30,30,29,30,29,31],
    2024:[31,31,31,32,31,31,30,29,30,29,30,30], 2025:[31,31,32,31,31,31,30,29,30,29,30,30],
    2026:[31,32,31,32,31,30,30,30,29,29,30,31], 2027:[30,32,31,32,31,30,30,30,29,30,29,31],
    2028:[31,31,32,31,31,31,30,29,30,29,30,30], 2029:[31,31,32,31,32,30,30,29,30,29,30,30],
    2030:[31,32,31,32,31,30,30,30,29,29,30,31], 2031:[30,32,31,32,31,30,30,30,29,30,29,31],
    2032:[31,31,32,31,31,31,30,29,30,29,30,30], 2033:[31,31,32,32,31,30,30,29,30,29,30,30],
    2034:[31,32,31,32,31,30,30,30,29,29,30,31], 2035:[30,32,31,32,31,31,29,30,30,29,29,31],
    2036:[31,31,32,31,31,31,30,29,30,29,30,30], 2037:[31,31,32,32,31,30,30,29,30,29,30,30],
    2038:[31,32,31,32,31,30,30,30,29,29,30,31], 2039:[31,31,31,32,31,31,29,30,30,29,30,30],
    2040:[31,31,32,31,31,31,30,29,30,29,30,30], 2041:[31,31,32,32,31,30,30,29,30,29,30,30],
    2042:[31,32,31,32,31,30,30,30,29,29,30,31], 2043:[31,31,31,32,31,31,29,30,30,29,30,30],
    2044:[31,31,32,31,31,31,30,29,30,29,30,30], 2045:[31,31,32,32,31,30,30,29,30,29,30,30],
    2046:[31,32,31,32,31,30,30,30,29,29,30,31], 2047:[31,31,31,32,31,31,30,29,30,29,30,30],
    2048:[31,31,32,31,31,31,30,29,30,29,30,30], 2049:[31,31,32,32,31,30,30,29,30,29,30,30],
    2050:[31,32,31,32,31,30,30,30,29,29,30,31], 2051:[31,31,31,32,31,31,30,29,30,29,30,30],
    2052:[31,31,32,31,31,31,30,29,30,29,30,30], 2053:[31,31,32,32,31,30,30,29,30,29,30,30],
    2054:[31,32,31,32,31,30,30,30,29,29,30,31], 2055:[31,31,31,32,31,31,30,29,30,29,30,30],
    2056:[31,31,32,31,32,30,30,29,30,29,30,30], 2057:[31,32,31,32,31,30,30,30,29,29,30,31],
    2058:[31,31,31,32,31,31,29,30,30,29,30,30], 2059:[31,31,32,31,31,31,30,29,30,29,30,30],
    2060:[31,31,32,32,31,30,30,29,30,29,30,30], 2061:[31,32,31,32,31,30,30,30,29,29,30,31],
    2062:[31,31,31,32,31,31,29,30,29,30,29,31], 2063:[31,31,32,31,31,31,30,29,30,29,30,30],
    2064:[31,31,32,32,31,30,30,29,30,29,30,30], 2065:[31,32,31,32,31,30,30,30,29,29,30,31],
    2066:[31,31,31,32,31,31,29,30,30,29,29,31], 2067:[31,31,32,31,31,31,30,29,30,29,30,30],
    2068:[31,31,32,32,31,30,30,29,30,29,30,30], 2069:[31,32,31,32,31,30,30,30,29,29,30,31],
    2070:[31,31,31,32,31,31,29,30,30,29,30,30], 2071:[31,31,32,31,31,31,30,29,30,29,30,30],
    2072:[31,32,31,32,31,30,30,30,29,29,30,30], 2073:[31,32,31,32,31,30,30,30,29,30,29,31],
    2074:[31,31,31,32,31,31,30,29,30,29,30,30], 2075:[31,31,32,31,31,31,30,29,30,29,30,30],
    2076:[31,32,31,32,31,30,30,30,29,29,30,30], 2077:[31,32,31,32,31,30,30,30,29,30,29,31],
    2078:[31,31,31,32,31,31,30,29,30,29,30,30], 2079:[31,31,32,31,31,31,30,29,30,29,30,30],
    2080:[31,32,31,32,31,30,30,30,29,29,30,30], 2081:[31,31,32,32,31,30,30,30,29,30,30,30],
    2082:[30,32,31,32,31,30,30,30,29,30,30,30], 2083:[31,31,32,31,31,30,30,30,29,30,30,30],
    2084:[31,31,32,31,31,30,30,30,29,30,30,30], 2085:[31,32,31,31,31,31,30,30,29,30,30,30],
    2086:[30,31,32,31,31,31,30,30,29,30,30,30], 2087:[31,31,32,31,31,31,30,30,29,30,30,30],
    2088:[30,31,32,32,30,31,30,30,29,30,30,30], 2089:[30,31,32,31,31,31,30,30,29,30,30,30],
    2090:[30,31,32,31,31,31,30,30,29,30,30,30]
  };

  // Reference anchor: BS 2000/1/1 = AD 1943-04-14
  var BS_EPOCH = { y: 2000, m: 1, d: 1, jd: gregorianToJD(1943, 4, 14) };

  function gregorianToJD(y, m, d) {
    var a = Math.floor((14 - m) / 12);
    var y2 = y + 4800 - a, m2 = m + 12 * a - 3;
    return d + Math.floor((153 * m2 + 2) / 5) + 365 * y2 +
      Math.floor(y2 / 4) - Math.floor(y2 / 100) + Math.floor(y2 / 400) - 32045;
  }

  function jdToGregorian(jd) {
    var a = jd + 32044, b = Math.floor((4 * a + 3) / 146097), c = a - Math.floor(146097 * b / 4);
    var d = Math.floor((4 * c + 3) / 1461), e = c - Math.floor(1461 * d / 4);
    var m = Math.floor((5 * e + 2) / 153);
    var day = e - Math.floor((153 * m + 2) / 5) + 1;
    var mon = m + 3 - 12 * Math.floor(m / 10);
    var yr = 100 * b + d - 4800 + Math.floor(m / 10);
    return { y: yr, m: mon, d: day };
  }

  function bsToAd(by, bm, bd) {
    if (!BS_MONTHS[by]) return null;
    var days = 0;
    for (var y = BS_EPOCH.y; y < by; y++) {
      if (!BS_MONTHS[y]) return null;
      for (var i = 0; i < 12; i++) days += BS_MONTHS[y][i];
    }
    for (var k = 0; k < bm - 1; k++) days += BS_MONTHS[by][k];
    days += (bd - 1);
    return jdToGregorian(BS_EPOCH.jd + days);
  }

  function adToBs(ay, am, ad) {
    var diff = gregorianToJD(ay, am, ad) - BS_EPOCH.jd;
    if (diff < 0) return null;
    var y = BS_EPOCH.y;
    while (BS_MONTHS[y]) {
      var ytotal = BS_MONTHS[y].reduce(function(a,b){return a+b;}, 0);
      if (diff < ytotal) break;
      diff -= ytotal; y++;
    }
    if (!BS_MONTHS[y]) return null;
    var m = 0;
    while (diff >= BS_MONTHS[y][m]) { diff -= BS_MONTHS[y][m]; m++; }
    return { y: y, m: m + 1, d: diff + 1 };
  }

  var NE_MONTHS = ['बैशाख','जेठ','असार','साउन','भदौ','असोज','कार्तिक','मंसिर','पुष','माघ','फागुन','चैत'];
  var NE_DAYS   = ['आइत','सोम','मंगल','बुध','बिहि','शुक्र','शनि'];
  var NE_DIGITS = ['०','१','२','३','४','५','६','७','८','९'];
  function toNeDigits(n) { return String(n).replace(/\d/g, function(d){ return NE_DIGITS[+d]; }); }

  function pad(n){ return n < 10 ? '0' + n : '' + n; }

  function formatBS(b) {
    return toNeDigits(b.y) + '-' + toNeDigits(pad(b.m)) + '-' + toNeDigits(pad(b.d)) +
           ' (' + NE_MONTHS[b.m - 1] + ' ' + toNeDigits(b.d) + ', ' + toNeDigits(b.y) + ')';
  }

  function formatAD(g) {
    return g.y + '-' + pad(g.m) + '-' + pad(g.d);
  }

  function todayBS() {
    var now = new Date();
    return adToBs(now.getFullYear(), now.getMonth() + 1, now.getDate()) || { y: 2081, m: 1, d: 1 };
  }

  function buildPicker(input) {
    var current = todayBS();
    // Try parse existing value
    var existing = (input.value || '').match(/(\d{4})\D+(\d{1,2})\D+(\d{1,2})/);
    if (existing) {
      var ey = +existing[1].replace(/[०-९]/g, function(d){ return NE_DIGITS.indexOf(d); });
      // Easier: re-derive from digits, just trust ASCII first
      var ey2 = +existing[1], em = +existing[2], ed = +existing[3];
      if (BS_MONTHS[ey2]) current = { y: ey2, m: em, d: ed };
    }

    var pop = document.createElement('div');
    pop.className = 'ndp-pop';
    pop.style.cssText = 'position:absolute;z-index:1300;background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 12px 32px rgba(15,23,42,.18);padding:12px;min-width:280px;font-family:Hind Siliguri,sans-serif;';

    function render() {
      var days = BS_MONTHS[current.y] ? BS_MONTHS[current.y][current.m - 1] : 30;
      var firstAd = bsToAd(current.y, current.m, 1);
      var firstDow = firstAd ? (new Date(firstAd.y, firstAd.m - 1, firstAd.d)).getDay() : 0;

      var yearOpts = '';
      Object.keys(BS_MONTHS).forEach(function(y){
        yearOpts += '<option value="'+y+'"'+(+y===current.y?' selected':'')+'>'+toNeDigits(y)+'</option>';
      });
      var monthOpts = NE_MONTHS.map(function(n, i){
        return '<option value="'+(i+1)+'"'+(i+1===current.m?' selected':'')+'>'+n+'</option>';
      }).join('');

      var html = '<div style="display:flex;gap:6px;margin-bottom:8px;">' +
        '<select class="ndp-m" style="flex:1;padding:6px;border:1px solid #e2e8f0;border-radius:6px;font-family:inherit;">'+monthOpts+'</select>' +
        '<select class="ndp-y" style="width:90px;padding:6px;border:1px solid #e2e8f0;border-radius:6px;font-family:inherit;">'+yearOpts+'</select>' +
      '</div>';
      html += '<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;font-size:.7rem;color:#94a3b8;text-align:center;margin-bottom:4px;">';
      NE_DAYS.forEach(function(d){ html += '<div style="padding:4px 0;">'+d+'</div>'; });
      html += '</div>';
      html += '<div class="ndp-grid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;">';
      for (var i = 0; i < firstDow; i++) html += '<div></div>';
      for (var d = 1; d <= days; d++) {
        var sel = (d === current.d);
        html += '<button type="button" class="ndp-d" data-d="'+d+'" style="padding:6px 0;border:none;background:'+(sel?'#0f766e':'transparent')+';color:'+(sel?'#fff':'#0f172a')+';border-radius:6px;cursor:pointer;font-family:inherit;font-size:.85rem;">'+toNeDigits(d)+'</button>';
      }
      html += '</div>';
      html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;padding-top:8px;border-top:1px solid #f1f5f9;">' +
        '<button type="button" class="ndp-today" style="font-size:.75rem;color:#0f766e;background:none;border:none;cursor:pointer;font-weight:600;">आज</button>' +
        '<button type="button" class="ndp-close" style="font-size:.75rem;color:#94a3b8;background:none;border:none;cursor:pointer;">बन्द</button>' +
      '</div>';
      pop.innerHTML = html;
      pop.querySelector('.ndp-y').addEventListener('change', function(e){ current.y = +e.target.value; current.d = Math.min(current.d, BS_MONTHS[current.y][current.m-1]); render(); });
      pop.querySelector('.ndp-m').addEventListener('change', function(e){ current.m = +e.target.value; current.d = Math.min(current.d, BS_MONTHS[current.y][current.m-1]); render(); });
      pop.querySelectorAll('.ndp-d').forEach(function(b){
        b.addEventListener('click', function(){
          current.d = +b.dataset.d;
          var ad = bsToAd(current.y, current.m, current.d);
          input.value = formatBS(current);
          input.setAttribute('data-bs', current.y + '-' + pad(current.m) + '-' + pad(current.d));
          if (ad) {
            input.setAttribute('data-ad', formatAD(ad));
            var tgt = input.getAttribute('data-nepali-target');
            if (tgt) {
              var el = document.getElementById(tgt) || document.querySelector('[name="'+tgt+'"]');
              if (el) el.value = formatAD(ad);
            }
          }
          input.dispatchEvent(new Event('change', { bubbles: true }));
          close();
        });
      });
      pop.querySelector('.ndp-today').addEventListener('click', function(){ current = todayBS(); render(); });
      pop.querySelector('.ndp-close').addEventListener('click', close);
    }

    function close() { if (pop.parentNode) pop.parentNode.removeChild(pop); document.removeEventListener('click', outside, true); }
    function outside(e) { if (!pop.contains(e.target) && e.target !== input) close(); }

    var rect = input.getBoundingClientRect();
    pop.style.top = (window.scrollY + rect.bottom + 4) + 'px';
    pop.style.left = (window.scrollX + rect.left) + 'px';
    document.body.appendChild(pop);
    render();
    setTimeout(function(){ document.addEventListener('click', outside, true); }, 50);
  }

  function activate(input) {
    if (input.dataset.ndpReady) return;
    input.dataset.ndpReady = '1';
    input.setAttribute('autocomplete', 'off');
    input.setAttribute('readonly', 'readonly');
    input.style.cursor = 'pointer';
    input.addEventListener('focus', function(){ buildPicker(input); });
    input.addEventListener('click', function(){ buildPicker(input); });
  }

  function init() {
    document.querySelectorAll('input[data-nepali-date]').forEach(activate);
  }

  // Public API
  window.NepaliDatePicker = {
    bsToAd: bsToAd, adToBs: adToBs, todayBS: todayBS,
    formatBS: formatBS, formatAD: formatAD, toNeDigits: toNeDigits,
    attach: activate, refresh: init
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();