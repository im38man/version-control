/* Pencarian global: tombol mengambang + panel hasil.
   Mencari di index, about, gallery (archive), notepad, count, calender,
   termasuk data yang tersimpan di perangkat (catatan, hitung mundur, agenda). */
(function () {
  'use strict';
  if (window.__momonaSearch) return;
  window.__momonaSearch = true;

  var PAGES = [
    { file: 'index.html', label: 'Beranda' },
    { file: 'about.html', label: 'About' },
    { file: 'gallery.html', label: 'Archive' },
    { file: 'notepad.html', label: 'Notepad' },
    { file: 'count.html', label: 'Count' },
    { file: 'calender.html', label: 'Calendar' },
    { file: 'diary.html', label: 'Diary' }
  ];
  var BLOCK_SEL = 'h1,h2,h3,h4,p,figcaption,small,li,.date,.gallery-title,.gallery-brand,.gallery-role,.gallery-foot,.gallery-link,.eyebrow,.letter-caption,.side-text strong,button,.brand,label>span';
  var SKIP_TXT = /^(×|‹|›|♥|\+|＋|←.*|→)$/;

  function curFile() {
    var f = location.pathname.split('/').pop() || 'index.html';
    return f;
  }
  var CUR = curFile();

  /* ---------- CSS ---------- */
  var css = '' +
    '#searchFab{position:fixed;right:max(18px,env(safe-area-inset-right));bottom:max(18px,env(safe-area-inset-bottom));z-index:40;display:grid;place-items:center;width:58px;height:58px;margin:0;padding:0;border:1px solid #fff;border-radius:50%;background:#fffefa;color:#6f876d;cursor:pointer;box-shadow:0 10px 26px #435b4740,0 2px 6px #435b4720;transition:opacity .25s ease,transform .2s ease,background .2s ease}' +
    '#searchFab.above{bottom:calc(max(18px,env(safe-area-inset-bottom)) + 58px + 16px)}' +
    '#searchFab:hover{background:#fff;transform:translateY(-2px)}' +
    '#searchFab:focus-visible{outline:2px solid #8fa58b;outline-offset:3px}' +
    '#searchFab svg{width:24px;height:24px}' +
    'body.side-open #searchFab,body.search-open #searchFab{opacity:0;visibility:hidden;pointer-events:none}' +
    '.sx-modal{position:fixed;inset:0;z-index:90;display:grid;justify-items:center;align-items:start;padding:max(18px,6vh) 16px 16px;background:rgba(37,53,43,.55);-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px);overflow-y:auto}' +
    '.sx-modal[hidden]{display:none}' +
    '.sx-card{position:relative;width:min(100%,560px);max-height:calc(100vh - 32px);max-height:calc(100dvh - 32px);display:flex;flex-direction:column;padding:26px 20px 16px;background:#fffefa;border:1px solid #fff;box-shadow:0 28px 80px #25352b66;transform:rotate(-.4deg);animation:sxIn .35s cubic-bezier(.22,.8,.3,1);color:#25352b}' +
    '.sx-card::before{content:"";position:absolute;top:-11px;left:50%;width:70px;height:21px;margin-left:-35px;background:rgba(214,226,206,.88);border:1px solid rgba(255,255,255,.65);box-shadow:0 1px 3px #35453620;transform:rotate(-3deg)}' +
    '@keyframes sxIn{from{opacity:0;transform:translateY(16px) rotate(-2deg)}to{opacity:1;transform:rotate(-.4deg)}}' +
    '.sx-head{display:flex;align-items:center;gap:10px}' +
    '.sx-box{flex:1;display:flex;align-items:center;gap:10px;min-width:0;padding:4px 14px;border:1px solid #cdd9c8;border-radius:999px;background:#ffffffb0}' +
    '.sx-box:focus-within{border-color:#9caf98;background:#fff}' +
    '.sx-box svg{flex:none;width:18px;height:18px;color:#8a9b88}' +
    '.sx-input{flex:1;min-width:0;border:0;outline:none;background:none;padding:9px 0;color:#25352b;font:italic 500 19px/1.3 "Cormorant Garamond",Georgia,serif}' +
    '.sx-input::placeholder{color:#a9b6a7}' +
    '.sx-input::-webkit-search-cancel-button{display:none}' +
    '.sx-close{flex:none;display:grid;place-items:center;width:36px;height:36px;margin:0;padding:0;border:0;border-radius:50%;background:#fffefaf0;box-shadow:0 2px 6px #435b4722;color:#6f876d;font:18px/1 Arial,sans-serif;cursor:pointer}' +
    '.sx-close:hover{background:#fff}' +
    '.sx-info{margin:12px 4px 6px;font:10px Arial,sans-serif;letter-spacing:.16em;text-transform:uppercase;color:#8a9b88}' +
    '.sx-list{list-style:none;margin:0;padding:0 0 4px;overflow-y:auto;overscroll-behavior:contain;display:grid;gap:6px;align-content:start}' +
    '.sx-group{margin:10px 4px 0;font:9px Arial,sans-serif;letter-spacing:.2em;text-transform:uppercase;color:#6f876d}' +
    '.sx-item{display:block;padding:10px 12px;text-decoration:none;color:#455847;background:#f8f6ec;border:1px solid #e9e5d6;border-radius:12px;transition:.2s ease}' +
    '.sx-item:hover,.sx-item:focus-visible{background:#fff;border-color:#c5d5c2;transform:translateY(-1px);outline:none}' +
    '.sx-t{display:block;font:italic 500 17px/1.35 "Cormorant Garamond",Georgia,serif;color:#31473a;overflow-wrap:anywhere}' +
    '.sx-s{display:block;margin-top:2px;font:12px/1.6 Arial,sans-serif;color:#718174;overflow-wrap:anywhere}' +
    '.sx-w{display:inline-block;margin-top:5px;font:9px Arial,sans-serif;letter-spacing:.14em;text-transform:uppercase;color:#8a9b88}' +
    '.sx-item mark{background:#f3e6b8;color:inherit;border-radius:3px;padding:0 1px}' +
    '.sx-empty{margin:18px 6px 10px;text-align:center;font:italic 14px/1.8 Georgia,serif;color:#8a9b88}' +
    '.sx-note{margin:8px 4px 0;font:italic 11px/1.6 Georgia,serif;color:#a08a55}' +
    '@media(max-width:600px){.sx-modal{padding-top:14px}.sx-card{padding:24px 14px 12px}}' +
    '@media(prefers-reduced-motion:reduce){.sx-card{animation:none}#searchFab{transition:none}}';
  var st = document.createElement('style'); st.textContent = css; document.head.appendChild(st);

  /* ---------- DOM ---------- */
  var SVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/></svg>';

  var fab = document.createElement('button');
  fab.type = 'button'; fab.id = 'searchFab'; fab.setAttribute('aria-label', 'Cari di semua halaman');
  fab.setAttribute('aria-haspopup', 'dialog'); fab.innerHTML = SVG;
  if (document.getElementById('loveFab')) fab.classList.add('above');
  document.body.appendChild(fab);

  var modal = document.createElement('div');
  modal.className = 'sx-modal'; modal.hidden = true;
  modal.setAttribute('role', 'dialog'); modal.setAttribute('aria-modal', 'true'); modal.setAttribute('aria-label', 'Pencarian');
  modal.innerHTML =
    '<div class="sx-card">' +
      '<div class="sx-head">' +
        '<label class="sx-box">' + SVG +
          '<input class="sx-input" type="search" placeholder="Cari foto, surat, catatan, agenda…" autocomplete="off" aria-label="Kata kunci pencarian" enterkeyhint="search">' +
        '</label>' +
        '<button type="button" class="sx-close" aria-label="Tutup pencarian">×</button>' +
      '</div>' +
      '<div class="sx-info" aria-live="polite"></div>' +
      '<ul class="sx-list"></ul>' +
      '<p class="sx-note" hidden></p>' +
    '</div>';
  document.body.appendChild(modal);

  var input = modal.querySelector('.sx-input');
  var listEl = modal.querySelector('.sx-list');
  var infoEl = modal.querySelector('.sx-info');
  var noteEl = modal.querySelector('.sx-note');
  var closeBtn = modal.querySelector('.sx-close');

  /* ---------- pengindeks ---------- */
  var staticIdx = null, loading = null, failed = [];

  function clean(s) { return (s || '').replace(/\s+/g, ' ').trim(); }

  function extract(doc, page) {
    var out = [], seen = Object.create(null);
    var root = doc.body; if (!root) return out;
    var nodes = root.querySelectorAll(BLOCK_SEL);
    for (var i = 0; i < nodes.length; i++) {
      var n = nodes[i];
      if (n.closest('script,style,noscript,.sx-modal,#searchFab,.lightbox,.moment-modal,.modal,.toast,.side-overlay')) continue;
      if (n.closest('#sidePanel') && !(n.closest('.side-text'))) continue;
      if (n.querySelector(BLOCK_SEL)) continue;               // ambil elemen paling dalam saja
      var t = clean(n.textContent);
      if (!t || t.length < 2 || SKIP_TXT.test(t)) continue;
      var sec = n.closest('section[id]');
      var h = sec ? sec.querySelector('h1,h2') : null;
      var secId = sec ? sec.id : '';
      var k = page.file + '|' + secId + '|' + t;
      if (seen[k]) continue; seen[k] = 1;
      out.push({ page: page, text: t, where: h ? clean(h.textContent) : '', hash: secId, kind: 'page' });
    }
    var imgs = root.querySelectorAll('img[alt]');
    for (var j = 0; j < imgs.length; j++) {
      var im = imgs[j];
      if (im.closest('.sx-modal,.lightbox,.moment-modal')) continue;
      var a = clean(im.getAttribute('alt'));
      if (!a || a.length < 3) continue;
      var s2 = im.closest('section[id]');
      var k2 = page.file + '|img|' + (s2 ? s2.id : '') + '|' + a;
      if (seen[k2]) continue; seen[k2] = 1;
      out.push({ page: page, text: a, where: 'Foto', hash: s2 ? s2.id : '', kind: 'page' });
    }
    return out;
  }

  function loadStatic() {
    if (staticIdx) return Promise.resolve(staticIdx);
    if (loading) return loading;
    var idx = [];
    var jobs = PAGES.map(function (p) {
      if (p.file === CUR || (CUR === '' && p.file === 'index.html')) {
        idx = idx.concat(extract(document, p));
        return Promise.resolve();
      }
      return fetch(p.file, { credentials: 'same-origin' })
        .then(function (r) { if (!r.ok) throw new Error(r.status); return r.text(); })
        .then(function (html) {
          var doc = new DOMParser().parseFromString(html, 'text/html');
          idx = idx.concat(extract(doc, p));
        })
        .catch(function () { failed.push(p.label); });
    });
    loading = Promise.all(jobs).then(function () { staticIdx = idx; return idx; });
    return loading;
  }

  function parse(key) {
    try { return JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) { return null; }
  }
  function P(label) { for (var i = 0; i < PAGES.length; i++) if (PAGES[i].label === label) return PAGES[i]; return PAGES[0]; }

  /* data buatan pengguna: dibaca ulang tiap pencarian agar selalu terbaru */
  function loadUserData() {
    var out = [], d;
    d = parse('momona.notepad.v2');
    if (d && Array.isArray(d.notes)) d.notes.forEach(function (n) {
      if (!n || typeof n !== 'object') return;
      var title = clean(n.title), text = clean(n.text);
      if (!title && !text) return;
      var id = typeof n.id === 'string' && /^[\w-]+$/.test(n.id) ? n.id : '';
      out.push({ page: P('Notepad'), text: (title || 'Tanpa judul') + (text ? ' — ' + text : ''), title: title || 'Tanpa judul', body: text,
        where: 'Catatan', href: 'notepad.html' + (id ? '#note-' + id : ''), kind: 'user' });
    });
    d = parse('momona.count.v1');
    if (d && Array.isArray(d.items)) d.items.forEach(function (c) {
      if (!c || typeof c.name !== 'string') return;
      var body = clean((c.desc || '') + ' ' + (c.date || ''));
      out.push({ page: P('Count'), text: c.name + ' — ' + body, title: c.name, body: clean(c.desc) || c.date || '',
        where: 'Hitung mundur · ' + (c.date || ''), href: 'count.html', kind: 'user' });
    });
    d = parse('momona.calendar.v1');
    if (d && Array.isArray(d.items)) d.items.forEach(function (a) {
      if (!a || typeof a.title !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(a.date || '')) return;
      out.push({ page: P('Calendar'), text: a.title + ' — ' + clean(a.note) + ' ' + a.date, title: a.title, body: clean(a.note),
        where: 'Agenda · ' + a.date + (a.time ? ' · ' + String(a.time).replace(':', '.') : ''), href: 'calender.html#d-' + a.date, kind: 'user' });
    });
    return out;
  }

  /* ---------- pencocokan ---------- */
  function norm(s) { return s.toLowerCase(); }
  function terms(q) { return norm(q).split(/\s+/).filter(Boolean); }

  function snippet(text, ts, len) {
    var low = norm(text), pos = -1;
    for (var i = 0; i < ts.length; i++) { var p = low.indexOf(ts[i]); if (p !== -1 && (pos === -1 || p < pos)) pos = p; }
    if (pos === -1) pos = 0;
    var start = Math.max(0, pos - 40), end = Math.min(text.length, start + len);
    return (start > 0 ? '… ' : '') + text.slice(start, end) + (end < text.length ? ' …' : '');
  }
  function highlight(parent, text, ts) {
    var low = norm(text), ranges = [];
    ts.forEach(function (t) { var from = 0, p; while ((p = low.indexOf(t, from)) !== -1) { ranges.push([p, p + t.length]); from = p + t.length; } });
    ranges.sort(function (a, b) { return a[0] - b[0]; });
    var merged = [];
    ranges.forEach(function (r) { var l = merged[merged.length - 1]; if (l && r[0] <= l[1]) l[1] = Math.max(l[1], r[1]); else merged.push(r); });
    var pos = 0;
    merged.forEach(function (r) {
      if (r[0] > pos) parent.appendChild(document.createTextNode(text.slice(pos, r[0])));
      var m = document.createElement('mark'); m.textContent = text.slice(r[0], r[1]); parent.appendChild(m);
      pos = r[1];
    });
    if (pos < text.length) parent.appendChild(document.createTextNode(text.slice(pos)));
  }

  function hrefOf(r) { return r.href || (r.page.file + (r.hash ? '#' + r.hash : '')); }

  var timer = null, seq = 0;
  function run() {
    var q = clean(input.value), my = ++seq;
    listEl.textContent = ''; noteEl.hidden = true;
    if (q.length < 1) { infoEl.textContent = 'Ketik untuk mencari di semua halaman'; return; }
    infoEl.textContent = 'Mencari…';
    loadStatic().then(function (stat) {
      if (my !== seq) return;
      var ts = terms(q);
      var all = stat.concat(loadUserData());
      var hits = all.filter(function (r) {
        var low = norm(r.text);
        return ts.every(function (t) { return low.indexOf(t) !== -1; });
      });
      render(hits, ts, q);
    });
  }

  function render(hits, ts, q) {
    listEl.textContent = '';
    var total = hits.length, MAX = 60;
    if (!total) {
      infoEl.textContent = '';
      var li = document.createElement('li'); li.className = 'sx-empty';
      li.textContent = 'Tidak ada hasil untuk “' + q + '”. Coba kata kunci lain.';
      listEl.appendChild(li);
    } else {
      infoEl.textContent = total + ' hasil' + (total > MAX ? ' · menampilkan ' + MAX + ' pertama' : '');
      var shown = hits.slice(0, MAX), byPage = {};
      PAGES.forEach(function (p) { byPage[p.file] = []; });
      shown.forEach(function (r) { byPage[r.page.file].push(r); });
      PAGES.forEach(function (p) {
        var rs = byPage[p.file]; if (!rs.length) return;
        var g = document.createElement('li'); g.className = 'sx-group'; g.textContent = p.label + ' · ' + rs.length; listEl.appendChild(g);
        rs.forEach(function (r) {
          var li2 = document.createElement('li');
          var a = document.createElement('a'); a.className = 'sx-item'; a.href = hrefOf(r);
          var t = document.createElement('span'); t.className = 'sx-t';
          if (r.kind === 'user') {
            highlight(t, r.title, ts);
            a.appendChild(t);
            if (r.body) { var sb = document.createElement('span'); sb.className = 'sx-s'; highlight(sb, snippet(r.body, ts, 110), ts); a.appendChild(sb); }
          } else {
            highlight(t, snippet(r.text, ts, 120), ts);
            a.appendChild(t);
          }
          if (r.where) { var w = document.createElement('span'); w.className = 'sx-w'; w.textContent = r.where; a.appendChild(w); }
          a.addEventListener('click', function (e) { onPick(e, a); });
          li2.appendChild(a); listEl.appendChild(li2);
        });
      });
    }
    if (failed.length) {
      noteEl.hidden = false;
      noteEl.textContent = 'Halaman ' + failed.join(', ') + ' belum bisa dibaca (buka lewat hosting/server agar semua halaman ikut dicari). Data tersimpan tetap ikut dicari.';
    }
  }

  function onPick(e, a) {
    var url = new URL(a.getAttribute('href'), location.href);
    var same = url.pathname === location.pathname;
    close(true);
    if (same) {
      e.preventDefault();
      if (url.hash) {
        if (location.hash === url.hash) window.dispatchEvent(new Event('hashchange'));
        else location.hash = url.hash;
        var t = document.getElementById(url.hash.slice(1));
        if (t && t.scrollIntoView) t.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }

  /* ---------- buka / tutup ---------- */
  var lastFocus = null, isOpen = false;
  function open() {
    if (isOpen) return;
    isOpen = true; lastFocus = document.activeElement;
    failed = []; staticIdx = null; loading = null;      // baca ulang halaman agar selalu terbaru
    modal.hidden = false; document.body.classList.add('search-open');
    document.documentElement.style.overflow = 'hidden';
    run(); input.focus({ preventScroll: true });
    loadStatic();                                       // pemanasan indeks di latar belakang
  }
  function close(noFocus) {
    if (!isOpen) return;
    isOpen = false; modal.hidden = true; document.body.classList.remove('search-open');
    document.documentElement.style.overflow = '';
    if (!noFocus && lastFocus && document.contains(lastFocus)) lastFocus.focus({ preventScroll: true });
  }
  fab.addEventListener('click', open);
  closeBtn.addEventListener('click', function () { close(); });
  modal.addEventListener('mousedown', function (e) { if (e.target === modal) close(); });
  input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(run, 120); });
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { var f = listEl.querySelector('a.sx-item'); if (f) f.click(); }
    else if (e.key === 'ArrowDown') { var a = listEl.querySelector('a.sx-item'); if (a) { e.preventDefault(); a.focus(); } }
  });
  listEl.addEventListener('keydown', function (e) {
    if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
    var items = [].slice.call(listEl.querySelectorAll('a.sx-item')), i = items.indexOf(document.activeElement);
    if (i === -1) return; e.preventDefault();
    if (e.key === 'ArrowDown') (items[i + 1] || items[i]).focus();
    else if (i === 0) input.focus(); else items[i - 1].focus();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && isOpen) close();
  });
})();
