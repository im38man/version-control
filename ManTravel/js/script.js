/* ManTravel Elite — script bersama untuk index, login, dan chat.
   Data disimpan di localStorage (demo tanpa backend). */

// ===== Util =====
const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];
const IDR = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const imgUrl = (url, w) => `${url}${url.includes('?') ? '&' : '?'}auto=format&fit=crop&w=${w}&q=80`;

const store = {
    get(key, fallback) {
        try { const v = JSON.parse(localStorage.getItem(key)); return v ?? fallback; } catch { return fallback; }
    },
    set(key, val) { try { localStorage.setItem(key, JSON.stringify(val)); } catch { /* storage penuh/diblokir */ } },
    raw(key) { try { return localStorage.getItem(key); } catch { return null; } },
    setRaw(key, val) { try { localStorage.setItem(key, val); } catch { /* abaikan */ } },
    del(key) { try { localStorage.removeItem(key); } catch { /* abaikan */ } }
};

function toast(msg, type = 'ok') {
    const region = $('#toasts');
    if (!region) return;
    const el = document.createElement('div');
    el.className = 'toast' + (type === 'error' ? ' error' : '');
    el.textContent = msg;
    region.appendChild(el);
    setTimeout(() => el.remove(), 4200);
}

async function hashPass(pass) {
    if (!window.crypto?.subtle) return pass;
    const buf = await crypto.subtle.digest('SHA-256', new TextEncoder().encode('mantravel:' + pass));
    return 'h:' + [...new Uint8Array(buf)].map(b => b.toString(16).padStart(2, '0')).join('');
}

const getDest = id => DESTINATIONS.find(d => d.id === id);
const MAX_GUESTS = 10;

// ===== Sesi & penyimpanan =====
const getUsers = () => store.get('manTravel_users', []);
const getActiveEmail = () => store.raw('manTravel_activeUser');
const getActiveUser = () => {
    const email = getActiveEmail();
    return email ? (getUsers().find(u => u.email === email) || { email }) : null;
};
const getBookings = () => store.get('manTravel_bookings', []);
const saveBookings = list => store.set('manTravel_bookings', list);
const getFavs = () => store.get('manTravel_favs', []);
const saveFavs = list => store.set('manTravel_favs', list);

const nameOf = u => u.name || u.email.split('@')[0];

function renderAuthSlot() {
    const slot = $('#authSlot');
    if (!slot) return;
    const user = getActiveUser();
    if (user) {
        const name = nameOf(user);
        slot.innerHTML = `<span class="user-chip"><span class="avatar" aria-hidden="true">${esc(name[0].toUpperCase())}</span>${esc(name)}<button class="link-btn" id="logoutBtn">Keluar</button></span>`;
        $('#logoutBtn').addEventListener('click', () => {
            store.del('manTravel_activeUser');
            renderAuthSlot();
            toast('Anda sudah keluar.');
        });
    } else {
        slot.innerHTML = '<a href="login.html" class="btn btn-sm">Sign In</a>';
    }
}

function updateTripBadge() {
    const badge = $('#tripBadge');
    if (!badge) return;
    const n = getBookings().filter(b => b.status !== 'Dibatalkan').length;
    badge.textContent = n;
    badge.hidden = n === 0;
}

// ===== Halaman utama: daftar destinasi =====
const PAGE_SIZE = 6;
const listState = { q: '', filter: 'Semua', sort: 'featured', shown: PAGE_SIZE };

function filteredDestinations() {
    const q = listState.q.trim().toLowerCase();
    const favs = getFavs();
    let list = DESTINATIONS.filter(d => {
        if (listState.filter === 'Favorit' && !favs.includes(d.id)) return false;
        if (CATEGORIES.includes(listState.filter) && d.category !== listState.filter) return false;
        if (!q) return true;
        return [d.title, d.city, d.country, d.region, d.category, d.short].join(' ').toLowerCase().includes(q);
    });
    if (listState.sort === 'price-asc') list = [...list].sort((a, b) => a.price - b.price);
    if (listState.sort === 'price-desc') list = [...list].sort((a, b) => b.price - a.price);
    if (listState.sort === 'nights') list = [...list].sort((a, b) => b.nights - a.nights);
    return list;
}

function ticketCard(d) {
    const fav = getFavs().includes(d.id);
    return `
    <article class="ticket">
        <div class="ticket-media">
            <span class="fallback" aria-hidden="true">${esc(d.city)}</span>
            <img src="${imgUrl(d.img, 800)}" alt="${esc(d.title)}" loading="lazy" onerror="this.remove()">
            <span class="tag">${esc(d.category)}</span>
            <button class="fav" data-fav="${d.id}" aria-pressed="${fav}" aria-label="${fav ? 'Hapus' : 'Simpan'} ${esc(d.title)} ${fav ? 'dari' : 'ke'} favorit">
                <i class="fa-${fav ? 'solid' : 'regular'} fa-heart"></i>
            </button>
        </div>
        <div class="ticket-body">
            <p class="ticket-place">${esc(d.city)}, ${esc(d.country)}</p>
            <h3>${esc(d.title)}</h3>
            <p>${esc(d.short)}</p>
        </div>
        <div class="perf" aria-hidden="true"></div>
        <div class="ticket-stub">
            <div>
                <small>${d.nights + 1} hari ${d.nights} malam</small>
                <strong>${IDR(d.price)}</strong>
            </div>
            <a class="btn btn-sm" href="#/trip/${d.id}">Lihat detail</a>
        </div>
    </article>`;
}

function renderChips() {
    const wrap = $('#chips');
    if (!wrap) return;
    const favCount = getFavs().length;
    const labels = ['Semua', ...CATEGORIES, 'Favorit'];
    wrap.innerHTML = labels.map(r => `<button class="chip" data-filter="${r}" aria-pressed="${listState.filter === r}">${r}${r === 'Favorit' && favCount ? ` (${favCount})` : ''}</button>`).join('');
}

// Menggambar kartu sebanyak listState.shown. Dengan append=true hanya kartu baru yang ditambahkan.
function renderGrid(append = false) {
    const grid = $('#destGrid');
    if (!grid) return;
    const list = filteredDestinations();
    const more = $('#loadMoreWrap');
    if (!list.length) {
        const onFav = listState.filter === 'Favorit' && !listState.q;
        grid.innerHTML = `<div class="empty">
            <h3>${onFav ? 'Belum ada favorit' : 'Tidak ada itinerary yang cocok'}</h3>
            <p>${onFav ? 'Ketuk ikon hati pada itinerary untuk menyimpannya di sini.' : 'Coba kata kunci lain atau tampilkan semua kategori.'}</p>
            <button class="btn btn-ghost" id="resetFilter">Tampilkan semua itinerary</button>
        </div>`;
        if (more) more.innerHTML = '';
        return;
    }
    const visible = list.slice(0, listState.shown);
    if (append) {
        const have = $$('.ticket', grid).length;
        grid.insertAdjacentHTML('beforeend', visible.slice(have).map(ticketCard).join(''));
        $$('.ticket', grid)[have]?.querySelector('a.btn')?.focus({ preventScroll: true });
    } else {
        grid.innerHTML = visible.map(ticketCard).join('');
    }
    if (more) {
        const left = list.length - visible.length;
        const pct = Math.round((visible.length / list.length) * 100);
        more.innerHTML = `
            <p class="load-status">Menampilkan ${visible.length} dari ${list.length} itinerary</p>
            <div class="load-bar" aria-hidden="true"><span style="width:${pct}%"></span></div>
            ${left > 0 ? `<button class="btn btn-ghost" id="loadMore">Muat ${Math.min(PAGE_SIZE, left)} lagi</button>` : ''}`;
    }
}

// Ubah filter/pencarian/urutan: kembali ke 6 kartu pertama
function applyList() {
    listState.shown = PAGE_SIZE;
    renderChips(); renderGrid();
}

function initHome() {
    if (!$('#home-view')) return;

    // Hero: pilihan concierge + saran pencarian
    const pick = DESTINATIONS[0];
    $('#heroPick').innerHTML = `
        <small>Pilihan concierge</small>
        <strong>${esc(pick.title)}</strong>
        <div class="row"><span class="price">${IDR(pick.price)}</span><a class="btn btn-brass btn-sm" href="#/trip/${pick.id}">Lihat</a></div>`;
    $('#heroHints').innerHTML = ['Kyoto', 'Santorini', 'Maladewa'].map(h => `<button type="button" data-hint="${h}">${h}</button>`).join('');

    applyList();

    $('#heroSearch').addEventListener('submit', e => {
        e.preventDefault();
        listState.q = $('#heroQuery').value;
        listState.filter = 'Semua';
        applyList();
        location.hash = '#destinations';
        scrollToDestinations();
    });
    $('#heroHints').addEventListener('click', e => {
        const b = e.target.closest('[data-hint]');
        if (!b) return;
        $('#heroQuery').value = b.dataset.hint;
        $('#heroSearch').requestSubmit();
    });
    $('#chips').addEventListener('click', e => {
        const b = e.target.closest('[data-filter]');
        if (!b) return;
        listState.filter = b.dataset.filter;
        applyList();
    });
    $('#sortSel').addEventListener('change', e => { listState.sort = e.target.value; applyList(); });
    $('#loadMoreWrap').addEventListener('click', e => {
        if (!e.target.closest('#loadMore')) return;
        listState.shown += PAGE_SIZE;
        renderGrid(true);
    });
    $('#destGrid').addEventListener('click', e => {
        const fav = e.target.closest('[data-fav]');
        if (fav) { toggleFav(fav.dataset.fav); return; }
        if (e.target.closest('#resetFilter')) {
            listState.q = ''; listState.filter = 'Semua';
            $('#heroQuery').value = '';
            applyList();
        }
    });
}

function toggleFav(id) {
    const favs = getFavs();
    const i = favs.indexOf(id);
    if (i >= 0) favs.splice(i, 1); else favs.push(id);
    saveFavs(favs);
    renderChips();
    if (listState.filter === 'Favorit') renderGrid();
    else {
        // perbarui hanya tombol yang diubah agar posisi scroll tidak bergeser
        const btn = $(`[data-fav="${id}"]`);
        if (btn) {
            const on = i < 0;
            btn.setAttribute('aria-pressed', on);
            btn.querySelector('i').className = `fa-${on ? 'solid' : 'regular'} fa-heart`;
        }
    }
    toast(i >= 0 ? 'Dihapus dari favorit.' : 'Disimpan ke favorit.');
}

// ===== Detail & pemesanan =====
const bookingState = { guests: 2, addons: new Set() };

function calcTotal(dest) {
    const base = dest.price * bookingState.guests;
    const lines = ADDONS.filter(a => bookingState.addons.has(a.id)).map(a => ({
        label: a.label, amount: a.per === 'orang' ? a.price * bookingState.guests : a.price
    }));
    const total = base + lines.reduce((s, l) => s + l.amount, 0);
    return { base, lines, total };
}

function renderSummary(dest) {
    const { base, lines, total } = calcTotal(dest);
    $('#summary').innerHTML = `
        <div class="summary-row"><span>${esc(dest.title)} × ${bookingState.guests}</span><span>${IDR(base)}</span></div>
        ${lines.map(l => `<div class="summary-row"><span>${esc(l.label)}</span><span>${IDR(l.amount)}</span></div>`).join('')}
        <div class="summary-total"><span>Total</span><strong>${IDR(total)}</strong></div>`;
}

function showDetail(id) {
    const dest = getDest(id);
    if (!dest) { location.hash = '#/'; return; }
    bookingState.guests = 2;
    bookingState.addons = new Set();
    const user = getActiveUser();
    const today = new Date(); today.setDate(today.getDate() + 3);
    const minDate = today.toISOString().slice(0, 10);

    $('#detailRoot').innerHTML = `
        <a class="back-btn" href="#destinations"><i class="fa-solid fa-arrow-left"></i> Kembali ke destinasi</a>
        <header class="detail-head">
            <p class="muted">${esc(dest.city)}, ${esc(dest.country)}</p>
            <h1>${esc(dest.title)}</h1>
            <p class="muted">${esc(dest.desc)}</p>
            <div class="detail-meta">
                <span><i class="fa-regular fa-calendar"></i>${dest.nights + 1} hari ${dest.nights} malam</span>
                <span><i class="fa-solid fa-location-dot"></i>${esc(dest.category)}, ${esc(dest.region)}</span>
                <span><i class="fa-solid fa-user-group"></i>Maksimal ${MAX_GUESTS} tamu</span>
            </div>
        </header>
        <div class="spa-banner-wrap"><img id="detail-img" class="spa-banner" src="${imgUrl(dest.img, 1600)}" alt="${esc(dest.title)}" onerror="this.removeAttribute('src');this.alt=''"></div>

        <div class="spa-content-grid">
            <div>
                <section class="detail-section">
                    <h2>Sudah termasuk</h2>
                    <ul class="includes">${dest.includes.map(i => `<li><i class="fa-solid fa-check"></i>${esc(i)}</li>`).join('')}</ul>
                </section>
                <section class="detail-section">
                    <h2>Jadwal perjalanan</h2>
                    <div class="timeline">
                        ${dest.days.map((d, i) => `<div class="timeline-item"><small>Hari ${i + 1}</small><h4>${esc(d.t)}</h4><p>${esc(d.d)}</p></div>`).join('')}
                    </div>
                </section>
            </div>

            <aside>
                <div class="booking-box-spa">
                    <h3>Pesan perjalanan</h3>
                    <p class="booking-price"><strong>${IDR(dest.price)}</strong> per orang</p>
                    <form id="spaBookingForm" novalidate>
                        <div class="form-group">
                            <label for="spaCustName">Nama lengkap (sesuai paspor)</label>
                            <input type="text" id="spaCustName" required autocomplete="name" value="${user ? esc(nameOf(user)) : ''}">
                        </div>
                        <div class="form-group">
                            <label for="spaBookDate">Tanggal keberangkatan</label>
                            <input type="date" id="spaBookDate" required min="${minDate}">
                        </div>
                        <div class="form-group">
                            <span class="group-label" id="guestLbl">Jumlah tamu</span>
                            <div class="stepper" role="group" aria-labelledby="guestLbl">
                                <button type="button" data-step="-1" aria-label="Kurangi tamu">&minus;</button>
                                <output id="guestOut" aria-live="polite">2</output>
                                <button type="button" data-step="1" aria-label="Tambah tamu">+</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <span class="group-label">Tambahan</span>
                            <div class="addons">
                                ${ADDONS.map(a => `
                                <label class="addon">
                                    <input type="checkbox" value="${a.id}">
                                    <span>${esc(a.label)}<small>${esc(a.note)}</small></span>
                                    <em>${IDR(a.price)}<small>per ${a.per}</small></em>
                                </label>`).join('')}
                            </div>
                        </div>
                        <div class="summary" id="summary"></div>
                        <p class="field-error" id="bookError" role="alert" hidden></p>
                        <button type="submit" class="btn btn-brass btn-block">Pesan sekarang</button>
                        <p class="fineprint">Belum ada pembayaran. Pesanan tersimpan di perangkat ini.</p>
                    </form>
                </div>
            </aside>
        </div>`;

    renderSummary(dest);
    const form = $('#spaBookingForm');
    form.addEventListener('click', e => {
        const step = e.target.closest('[data-step]');
        if (!step) return;
        bookingState.guests = Math.min(MAX_GUESTS, Math.max(1, bookingState.guests + Number(step.dataset.step)));
        $('#guestOut').textContent = bookingState.guests;
        renderSummary(dest);
    });
    form.addEventListener('change', e => {
        if (e.target.type !== 'checkbox') return;
        e.target.checked ? bookingState.addons.add(e.target.value) : bookingState.addons.delete(e.target.value);
        renderSummary(dest);
    });
    form.addEventListener('submit', e => { e.preventDefault(); submitBooking(dest); });
}

function submitBooking(dest) {
    const name = $('#spaCustName').value.trim();
    const date = $('#spaBookDate').value;
    const err = $('#bookError');
    const fail = msg => { err.textContent = msg; err.hidden = false; };
    if (!name) return fail('Isi nama lengkap sesuai paspor.');
    if (!date) return fail('Pilih tanggal keberangkatan.');
    if (date < $('#spaBookDate').min) return fail('Tanggal keberangkatan minimal 3 hari dari hari ini.');
    err.hidden = true;

    const { total } = calcTotal(dest);
    const existing = getBookings();
    let id;
    do { id = 'TRV-' + String(Math.floor(1000 + Math.random() * 9000)); } while (existing.some(b => b.id === id));
    const booking = {
        id, destId: dest.id, paket: dest.title, harga: IDR(dest.price), nama: name, tanggal: date,
        tamu: String(bookingState.guests), addons: [...bookingState.addons], total,
        status: 'Terkonfirmasi', createdAt: new Date().toISOString(), email: getActiveEmail() || ''
    };
    existing.push(booking);
    saveBookings(existing);
    updateTripBadge();
    openTicket(booking);
}

// ===== E-tiket =====
let lastFocus = null;
const fmtDate = iso => {
    const d = new Date(iso + 'T00:00:00');
    return isNaN(d) ? iso : d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

function barcodeHtml(code) {
    let bars = '';
    for (let i = 0; i < 34; i++) {
        const c = code.charCodeAt(i % code.length) + i * 7;
        bars += `<span style="width:${1 + (c % 3)}px"></span>`;
    }
    return `<div class="barcode" aria-hidden="true">${bars}</div>`;
}

function openTicket(b) {
    const modal = $('#ticketModal');
    if (!modal) return;
    const dest = getDest(b.destId) || DESTINATIONS.find(d => d.title === b.paket);
    const total = b.total ? IDR(b.total) : b.harga;
    const extras = (b.addons || []).map(id => ADDONS.find(a => a.id === id)?.label).filter(Boolean);
    const cancelled = b.status === 'Dibatalkan';

    $('#ticketWrap').innerHTML = `
        <div class="ticket-sheet">
            <div class="ticket-main">
                <div class="ticket-top"><span class="brand">ManTravel<span>Elite</span></span><span class="status ${cancelled ? 'cancelled' : ''}">${esc(statusLabel(b.status))}</span></div>
                <h2 id="ticketTitle">${esc(b.paket)}</h2>
                <div class="ticket-fields">
                    <div><small>Penumpang</small><strong>${esc(b.nama)}</strong></div>
                    <div><small>Keberangkatan</small><strong>${esc(fmtDate(b.tanggal))}</strong></div>
                    <div><small>Jumlah tamu</small><strong>${esc(b.tamu)} orang</strong></div>
                    <div><small>Durasi</small><strong>${dest ? `${dest.nights + 1} hari ${dest.nights} malam` : '-'}</strong></div>
                    <div><small>Tambahan</small><strong>${extras.length ? esc(extras.join(', ')) : 'Tidak ada'}</strong></div>
                    <div><small>Total</small><strong>${esc(total)}</strong></div>
                </div>
            </div>
            <div class="ticket-stub-side">
                <div><small>Kode pesanan</small><span class="code">${esc(b.id)}</span></div>
                ${barcodeHtml(b.id)}
            </div>
        </div>
        <div class="ticket-actions">
            <button class="btn btn-ghost" id="ticketPrint" style="background:var(--paper)"><i class="fa-solid fa-print"></i> Cetak</button>
            <a class="btn btn-brass" href="#trips" id="ticketTrips">Lihat perjalanan saya</a>
            <button class="btn" id="ticketClose">Tutup</button>
        </div>`;

    lastFocus = document.activeElement;
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    $('#ticketClose').focus();
    $('#ticketClose').addEventListener('click', closeTicket);
    $('#ticketTrips').addEventListener('click', closeTicket);
    $('#ticketPrint').addEventListener('click', () => window.print());
}

function closeTicket() {
    const modal = $('#ticketModal');
    if (!modal || modal.hidden) return;
    modal.hidden = true;
    document.body.style.overflow = '';
    if (lastFocus && lastFocus.focus) lastFocus.focus();
}

const statusLabel = s => (s === 'Confirmed' ? 'Terkonfirmasi' : s);

// ===== Perjalanan saya =====
function showTrips() {
    const list = getBookings().slice().reverse();
    const root = $('#tripsRoot');
    root.innerHTML = `
        <div class="trips-head">
            <div>
                <p class="muted">Semua pesanan di perangkat ini</p>
                <h1>Perjalanan saya</h1>
            </div>
            <a href="#destinations" class="btn btn-ghost">Cari perjalanan baru</a>
        </div>
        <div class="trip-list">
            ${list.length ? list.map(tripRow).join('') : `
            <div class="empty">
                <h3>Belum ada perjalanan</h3>
                <p>Pilih itinerary, atur tamu dan tanggal, lalu pesan. Tiket Anda muncul di sini.</p>
                <a href="#destinations" class="btn">Lihat destinasi</a>
            </div>`}
        </div>`;
}

function tripRow(b) {
    const dest = getDest(b.destId) || DESTINATIONS.find(d => d.title === b.paket);
    const cancelled = b.status === 'Dibatalkan';
    const total = b.total ? IDR(b.total) : b.harga;
    return `
    <article class="trip ${cancelled ? 'is-cancelled' : ''}">
        ${dest ? `<img src="${imgUrl(dest.img, 300)}" alt="" loading="lazy" onerror="this.outerHTML='<div class=&quot;thumb&quot;></div>'">` : '<div class="thumb"></div>'}
        <div>
            <h3>${esc(b.paket)}</h3>
            <p>${esc(fmtDate(b.tanggal))}, ${esc(b.tamu)} tamu, ${esc(total)}</p>
            <p>Kode ${esc(b.id)}</p>
        </div>
        <div class="trip-actions">
            <span class="status ${cancelled ? 'cancelled' : ''}">${esc(statusLabel(b.status))}</span>
            <div class="trip-btns">
                <button class="btn btn-ghost btn-sm" data-ticket="${esc(b.id)}">Lihat tiket</button>
                ${cancelled ? '' : `<button class="btn btn-ghost btn-sm" data-cancel="${esc(b.id)}">Batalkan</button>`}
            </div>
        </div>
    </article>`;
}

function initTripActions() {
    const root = $('#tripsRoot');
    if (!root) return;
    root.addEventListener('click', e => {
        const t = e.target.closest('[data-ticket]');
        if (t) { const b = getBookings().find(x => x.id === t.dataset.ticket); if (b) openTicket(b); return; }
        const c = e.target.closest('[data-cancel]');
        if (!c) return;
        if (c.dataset.armed !== '1') {
            // konfirmasi dua langkah tanpa dialog bawaan browser
            c.dataset.armed = '1';
            c.textContent = 'Yakin batalkan?';
            setTimeout(() => { if (c.isConnected) { c.dataset.armed = ''; c.textContent = 'Batalkan'; } }, 3500);
            return;
        }
        const list = getBookings();
        const b = list.find(x => x.id === c.dataset.cancel);
        if (b) { b.status = 'Dibatalkan'; saveBookings(list); }
        updateTripBadge(); showTrips();
        toast('Pesanan dibatalkan.');
    });
}

// ===== Router (hash) =====
function showView(id) {
    $$('.spa-view').forEach(v => v.classList.toggle('active-view', v.id === id));
}
function setActiveNav(key) {
    $$('[data-nav]').forEach(a => a.classList.toggle('active', a.dataset.nav === key));
}
function scrollToDestinations() {
    const el = $('#destinations');
    if (el) el.scrollIntoView({ behavior: 'smooth' });
}

function route() {
    if (!$('#home-view')) return;
    const h = location.hash.replace(/^#\/?/, '');
    $('#navLinks')?.classList.remove('active');
    $('#mobile-menu')?.setAttribute('aria-expanded', 'false');
    closeTicket();

    if (h.startsWith('trip/')) {
        showView('detail-view'); setActiveNav('destinations');
        showDetail(h.slice(5));
        window.scrollTo(0, 0);
    } else if (h === 'trips') {
        showView('trips-view'); setActiveNav('trips');
        showTrips();
        window.scrollTo(0, 0);
    } else {
        const wasHome = $('#home-view').classList.contains('active-view');
        showView('home-view');
        if (h === 'destinations') {
            setActiveNav('destinations');
            setTimeout(scrollToDestinations, wasHome ? 0 : 60);
        } else {
            setActiveNav('home');
            if (!wasHome) window.scrollTo(0, 0);
        }
    }
}

// Kompatibilitas dengan versi lama (onclick="switchView(...)")
function switchView(viewId, targetHash = null) {
    location.hash = targetHash ? '#' + targetHash : '#/';
}
function loadItineraryDetail(key) { location.hash = '#/trip/' + key; }

// ===== Auth =====
function switchAuth(sectionId) {
    $$('.auth-section').forEach(s => s.classList.toggle('active-auth', s.id === sectionId));
    const first = $(`#${sectionId} input`);
    if (first) first.focus();
}

function showFieldError(id, msg) {
    const el = $('#' + id);
    if (!el) return;
    el.textContent = msg || '';
    el.hidden = !msg;
}

function initAuth() {
    if (!$('.auth-wrapper')) return;

    $$('[data-auth]').forEach(b => b.addEventListener('click', () => switchAuth(b.dataset.auth)));
    $$('.toggle-pass').forEach(btn => btn.addEventListener('click', () => {
        const input = btn.parentElement.querySelector('input');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        btn.querySelector('i').className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
    }));

    $('#registerForm').addEventListener('submit', async e => {
        e.preventDefault();
        const name = $('#regName').value.trim();
        const email = $('#regEmail').value.trim().toLowerCase();
        const pass = $('#regPass').value;
        if (!name) return showFieldError('registerError', 'Isi nama lengkap.');
        if (!/^\S+@\S+\.\S+$/.test(email)) return showFieldError('registerError', 'Format email belum benar.');
        if (pass.length < 6) return showFieldError('registerError', 'Password minimal 6 karakter.');
        const users = getUsers();
        if (users.some(u => u.email === email)) return showFieldError('registerError', 'Email ini sudah terdaftar. Gunakan Sign In.');
        users.push({ name, email, pass: await hashPass(pass) });
        store.set('manTravel_users', users);
        showFieldError('registerError', '');
        $('#registerForm').reset();
        $('#logEmail').value = email;
        switchAuth('login-section');
        toast('Akun dibuat. Silakan Sign In.');
    });

    $('#loginForm').addEventListener('submit', async e => {
        e.preventDefault();
        const email = $('#logEmail').value.trim().toLowerCase();
        const pass = $('#logPass').value;
        const hashed = await hashPass(pass);
        // akun lama menyimpan password apa adanya; tetap diterima lalu dimigrasi
        const users = getUsers();
        const user = users.find(u => u.email.toLowerCase() === email && (u.pass === hashed || u.pass === pass));
        if (!user) return showFieldError('loginError', 'Email atau password salah.');
        if (user.pass === pass && hashed !== pass) { user.pass = hashed; store.set('manTravel_users', users); }
        store.setRaw('manTravel_activeUser', user.email);
        showFieldError('loginError', '');
        toast('Berhasil masuk.');
        setTimeout(() => { window.location.href = 'index.html'; }, 600);
    });

    $('#forgotForm').addEventListener('submit', e => {
        e.preventDefault();
        toast('Jika email terdaftar, instruksi reset akan dikirim.');
        switchAuth('login-section');
    });
}

// ===== Live chat =====
const CHAT_KEY = 'manTravel_chat';
const CHAT_GREETING = 'Halo, selamat datang di Concierge ManTravel Elite. Tanyakan harga, cara memesan, atau detail itinerary. Balasan di sini otomatis; untuk permintaan khusus, tim kami menindaklanjuti lewat kontak Anda.';

function chatReply(text) {
    const t = text.toLowerCase();
    const link = d => `<a href="index.html#/trip/${d.id}">${esc(d.title)}</a>`;
    const mentioned = DESTINATIONS.find(d => t.includes(d.id) || t.includes(d.city.toLowerCase()) || t.includes(d.country.toLowerCase()));
    if (mentioned) {
        return { html: `${link(mentioned)}: ${esc(mentioned.short)}\n${mentioned.nights + 1} hari ${mentioned.nights} malam, mulai ${IDR(mentioned.price)} per orang.` };
    }
    const cat = CATEGORIES.find(c => t.includes(c.toLowerCase()));
    if (cat) {
        const items = DESTINATIONS.filter(d => d.category === cat);
        return { html: `Itinerary kategori ${cat}:\n` + items.map(d => `${link(d)}: ${IDR(d.price)}`).join('\n') };
    }
    if (/harga|biaya|tarif|price|berapa/.test(t)) {
        const prices = DESTINATIONS.map(d => d.price);
        return { html: `Harga mulai ${IDR(Math.min(...prices))} sampai ${IDR(Math.max(...prices))} per orang, tergantung tujuan. Sebut kota atau kategori (pantai, gunung, kota, budaya) untuk melihat rincian.` };
    }
    if (/pesan|booking|book|reservasi|cara/.test(t)) {
        return { html: 'Cara memesan:\n1. Pilih itinerary di halaman Destinasi.\n2. Isi nama, tanggal, jumlah tamu, dan tambahan.\n3. Tekan Pesan sekarang. E-tiket langsung muncul dan tersimpan di Perjalanan Saya.' };
    }
    if (/visa|paspor|passport|dokumen/.test(t)) {
        return { html: 'Siapkan paspor yang masih berlaku minimal 6 bulan dari tanggal pulang. Kebutuhan visa berbeda tiap negara; sampaikan tujuan Anda dan concierge kami memandu dokumen yang diperlukan.' };
    }
    if (/batal|cancel|refund|ubah|jadwal/.test(t)) {
        return { html: 'Anda bisa membatalkan pesanan sendiri di halaman Perjalanan Saya. Untuk mengubah tanggal, kirim kode pesanan (contoh TRV-1234) dan tanggal baru di chat ini.' };
    }
    if (/bayar|pembayaran|transfer|kartu/.test(t)) {
        return { html: 'Saat ini pesanan di situs ini belum memproses pembayaran. Tim concierge akan menghubungi Anda untuk instruksi pembayaran setelah pesanan dikonfirmasi.' };
    }
    if (/halo|hai|hi|hello|selamat/.test(t)) {
        return { html: 'Halo! Ada rencana perjalanan yang ingin dibicarakan? Sebut kota tujuan atau tanyakan harga paket.' };
    }
    return { html: 'Terima kasih pesannya. Concierge kami akan menindaklanjuti. Sambil menunggu, Anda bisa bertanya tentang harga, kategori (pantai, gunung, kota, budaya), cara memesan, visa, atau nama kota tujuan.' };
}

function initChat() {
    const chatForm = $('#chatForm');
    const chatBox = $('#chatBox');
    if (!chatForm || !chatBox) return;
    const input = $('#chatMsg');
    const time = () => new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

    let history = store.get(CHAT_KEY, null);
    if (!history || !history.length) history = [{ from: 'bot', html: esc(CHAT_GREETING), t: time() }];

    const bubble = m => `<div class="msg ${m.from}">${m.html}<time>${m.t}</time></div>`;
    const draw = () => { chatBox.innerHTML = history.map(bubble).join(''); chatBox.scrollTop = chatBox.scrollHeight; };
    const persist = () => store.set(CHAT_KEY, history.slice(-60));

    function send(text) {
        text = text.trim();
        if (!text) return;
        history.push({ from: 'user', html: esc(text), t: time() });
        persist(); draw();
        const typing = document.createElement('div');
        typing.className = 'msg bot';
        typing.innerHTML = '<span class="typing" aria-label="Concierge sedang mengetik"><span></span><span></span><span></span></span>';
        chatBox.appendChild(typing);
        chatBox.scrollTop = chatBox.scrollHeight;
        setTimeout(() => {
            history.push({ from: 'bot', html: chatReply(text).html, t: time() });
            persist(); draw();
        }, 900);
    }

    draw();
    chatForm.addEventListener('submit', e => { e.preventDefault(); const v = input.value; input.value = ''; send(v); });
    $('#topics').addEventListener('click', e => { const b = e.target.closest('[data-q]'); if (b) send(b.dataset.q); });
    $('#clearChat').addEventListener('click', () => {
        history = [{ from: 'bot', html: esc(CHAT_GREETING), t: time() }];
        persist(); draw();
    });
}

// ===== Galeri =====
const GALLERY_KEY = 'manTravel_gallery';
const GALLERY_RATIOS = ['4/5', '4/3', '1/1', '4/3', '4/5', '1/1'];
const GALLERY_SEED = DESTINATIONS.map((d, i) => ({
    id: 's-' + d.id, dest: d.id, caption: `${d.city}, ${d.country}`, ratio: GALLERY_RATIOS[i % GALLERY_RATIOS.length],
    src: imgUrl(d.img, 1400), thumb: imgUrl(d.img, 700)
}));

const getMyPhotos = () => store.get(GALLERY_KEY, []);

function saveMyPhotos(list) {
    try { localStorage.setItem(GALLERY_KEY, JSON.stringify(list)); return true; } catch { return false; }
}

function resizeImage(file, max = 1400) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onerror = () => reject(new Error('read'));
        reader.onload = () => {
            const img = new Image();
            img.onerror = () => reject(new Error('decode'));
            img.onload = () => {
                const scale = Math.min(1, max / Math.max(img.width, img.height));
                const w = Math.round(img.width * scale), h = Math.round(img.height * scale);
                const c = document.createElement('canvas');
                c.width = w; c.height = h;
                c.getContext('2d').drawImage(img, 0, 0, w, h);
                resolve({ src: c.toDataURL('image/jpeg', 0.8), w, h });
            };
            img.src = reader.result;
        };
        reader.readAsDataURL(file);
    });
}

function initGallery() {
    const grid = $('#masonry');
    if (!grid) return;
    const state = { filter: 'all', list: [] };
    const destName = id => getDest(id)?.title || 'Lainnya';

    const allPhotos = () => [
        ...getMyPhotos().map(p => ({ ...p, thumb: p.src, mine: true })),
        ...GALLERY_SEED
    ];

    function renderChips() {
        const mine = getMyPhotos().length;
        const chips = [['all', 'Semua'], ...CATEGORIES.map(c => [c, c]), ['mine', `Foto saya${mine ? ` (${mine})` : ''}`]];
        $('#galleryChips').innerHTML = chips.map(([k, l]) => `<button class="chip" data-f="${k}" aria-pressed="${state.filter === k}">${esc(l)}</button>`).join('');
    }

    function render() {
        const all = allPhotos();
        state.list = all.filter(p => state.filter === 'all' || (state.filter === 'mine' ? p.mine : getDest(p.dest)?.category === state.filter));
        $('#galleryCount').textContent = `${state.list.length} foto`;
        if (!state.list.length) {
            grid.innerHTML = `<div class="empty">
                <h3>${state.filter === 'mine' ? 'Belum ada foto Anda' : 'Belum ada foto'}</h3>
                <p>Tambahkan foto dari perjalanan Anda. Foto tersimpan di perangkat ini.</p>
                <button class="btn" id="emptyAdd">Tambah foto</button>
            </div>`;
            return;
        }
        grid.innerHTML = state.list.map((p, i) => `
            <figure class="shot">
                <button class="shot-btn" data-i="${i}" aria-label="Buka foto: ${esc(p.caption)}">
                    <div class="shot-media" style="aspect-ratio:${p.ratio || '4/3'}">
                        <span class="fallback" aria-hidden="true">${esc(p.caption)}</span>
                        <img src="${esc(p.thumb)}" alt="${esc(p.caption)}" loading="lazy" onerror="this.remove()">
                    </div>
                </button>
                <figcaption><strong>${esc(p.caption)}</strong><small>${esc(destName(p.dest))}${p.mine ? '<span class="mine">Foto saya</span>' : ''}</small></figcaption>
            </figure>`).join('');
    }

    // --- Lightbox ---
    const lb = $('#lightbox');
    let idx = 0, opener = null, armedDelete = false;
    function showLb(i) {
        idx = (i + state.list.length) % state.list.length;
        const p = state.list[idx];
        armedDelete = false;
        const img = $('#lbImg');
        img.src = p.src; img.alt = p.caption;
        $('#lbCaption').innerHTML = `<strong>${esc(p.caption)}</strong><small>${esc(destName(p.dest))}, ${idx + 1} dari ${state.list.length}</small>${p.mine ? '<button class="link-btn" id="lbDelete">Hapus foto</button>' : ''}`;
        const multi = state.list.length > 1;
        $('#lbPrev').hidden = $('#lbNext').hidden = !multi;
    }
    function openLb(i) { opener = document.activeElement; lb.hidden = false; document.body.style.overflow = 'hidden'; showLb(i); $('#lbClose').focus(); }
    function closeLb() { if (lb.hidden) return; lb.hidden = true; document.body.style.overflow = ''; $('#lbImg').src = ''; opener?.focus?.(); }

    grid.addEventListener('click', e => {
        const b = e.target.closest('.shot-btn');
        if (b) return openLb(Number(b.dataset.i));
        if (e.target.closest('#emptyAdd')) openUpload();
    });
    $('#lbClose').addEventListener('click', closeLb);
    $('#lbPrev').addEventListener('click', () => showLb(idx - 1));
    $('#lbNext').addEventListener('click', () => showLb(idx + 1));
    lb.addEventListener('click', e => { if (e.target === lb) closeLb(); });
    $('#lbCaption').addEventListener('click', e => {
        const d = e.target.closest('#lbDelete');
        if (!d) return;
        if (!armedDelete) { armedDelete = true; d.textContent = 'Yakin hapus?'; return; }
        saveMyPhotos(getMyPhotos().filter(p => p.id !== state.list[idx].id));
        toast('Foto dihapus.');
        renderChips(); render();
        if (!state.list.length) closeLb(); else showLb(Math.min(idx, state.list.length - 1));
    });

    // --- Upload ---
    const modal = $('#uploadModal');
    function openUpload() {
        opener = document.activeElement;
        $('#upDest').innerHTML = DESTINATIONS.map(d => `<option value="${d.id}">${esc(d.title)}</option>`).join('') + '<option value="other">Lainnya</option>';
        $('#uploadForm').reset();
        showFieldError('uploadError', '');
        modal.hidden = false; document.body.style.overflow = 'hidden';
        $('#upFile').focus();
    }
    function closeUpload() { if (modal.hidden) return; modal.hidden = true; document.body.style.overflow = ''; opener?.focus?.(); }
    $('#addPhotoBtn').addEventListener('click', openUpload);
    $('#upCancel').addEventListener('click', closeUpload);
    modal.addEventListener('click', e => { if (e.target === modal) closeUpload(); });

    $('#uploadForm').addEventListener('submit', async e => {
        e.preventDefault();
        const file = $('#upFile').files[0];
        if (!file) return showFieldError('uploadError', 'Pilih satu foto.');
        if (!file.type.startsWith('image/')) return showFieldError('uploadError', 'File harus berupa gambar.');
        let out;
        try { out = await resizeImage(file); } catch { return showFieldError('uploadError', 'Foto ini tidak bisa dibaca. Coba format JPG atau PNG.'); }
        const dest = $('#upDest').value;
        const photo = {
            id: 'u-' + Date.now(), dest, src: out.src, ratio: `${out.w}/${out.h}`,
            caption: $('#upCaption').value.trim() || (getDest(dest)?.title || 'Foto perjalanan')
        };
        if (!saveMyPhotos([photo, ...getMyPhotos()])) {
            return showFieldError('uploadError', 'Penyimpanan perangkat penuh. Hapus beberapa foto lama lalu coba lagi.');
        }
        closeUpload();
        state.filter = 'mine';
        renderChips(); render();
        toast('Foto ditambahkan.');
    });

    $('#galleryChips').addEventListener('click', e => {
        const b = e.target.closest('[data-f]');
        if (!b) return;
        state.filter = b.dataset.f;
        renderChips(); render();
    });

    document.addEventListener('keydown', e => {
        if (!lb.hidden) {
            if (e.key === 'Escape') closeLb();
            if (e.key === 'ArrowLeft') showLb(idx - 1);
            if (e.key === 'ArrowRight') showLb(idx + 1);
        } else if (!modal.hidden && e.key === 'Escape') closeUpload();
    });

    renderChips(); render();
}

// ===== Boot =====
document.addEventListener('DOMContentLoaded', () => {
    // Menu mobile
    const menuBtn = $('#mobile-menu');
    const navLinks = $('#navLinks');
    if (menuBtn && navLinks) {
        menuBtn.addEventListener('click', () => {
            const open = navLinks.classList.toggle('active');
            menuBtn.setAttribute('aria-expanded', open);
            menuBtn.querySelector('i').className = open ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
        });
    }

    renderAuthSlot();
    updateTripBadge();
    initHome();
    initTripActions();
    initAuth();
    initChat();
    initGallery();

    document.addEventListener('keydown', e => {
        const modal = $('#ticketModal');
        if (!modal || modal.hidden) return;
        if (e.key === 'Escape') closeTicket();
        if (e.key === 'Tab') { // jaga fokus tetap di dalam dialog
            const f = $$('button, a[href]', modal);
            if (!f.length) return;
            const first = f[0], last = f[f.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });
    $('#ticketModal')?.addEventListener('click', e => { if (e.target.id === 'ticketModal') closeTicket(); });

    window.addEventListener('hashchange', route);
    route();
});
