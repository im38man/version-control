// assets/js/cart.js
// Menangani klik tombol "Beli" / "Tambah ke Keranjang" di kartu produk manapun.
// Tidak perlu diberi atribut data- tambahan: nama, harga, gambar diambil otomatis
// dari markup kartu produk yang sudah ada (product-title, product-price, product-img).
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.product-actions .btn-solid');
    if (!btn) return;

    const card = btn.closest('.product-card');
    if (!card) return;

    e.preventDefault();

    const name = (card.querySelector('.product-title') || {}).innerText || '';
    const priceText = (card.querySelector('.product-price') || {}).innerText || '';
    const price = parseInt(priceText.replace(/[^0-9]/g, ''), 10) || 0;
    const img = (card.querySelector('.product-img') || {}).getAttribute('src') || '';

    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menambahkan...';
    btn.disabled = true;

    const params = new URLSearchParams();
    params.set('product_name', name.trim());
    params.set('product_image', img);
    params.set('price', price);
    params.set('qty', 1);

    fetch('add-to-cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
        .then((r) => r.json())
        .then((data) => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            if (data.status === 'need_login') {
                window.location.href = 'login.php';
            } else if (data.status === 'ok') {
                btn.innerHTML = '<i class="fas fa-check"></i> Ditambahkan';
                setTimeout(() => { btn.innerHTML = originalHtml; }, 1200);

                const badge = document.getElementById('navCartBadge');
                if (badge) {
                    const next = (parseInt(badge.textContent, 10) || 0) + 1;
                    badge.textContent = next > 99 ? '99+' : next;
                    badge.style.display = '';
                }
            } else {
                alert(data.message || 'Gagal menambahkan produk ke keranjang.');
            }
        })
        .catch(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            alert('Terjadi kesalahan jaringan. Coba lagi.');
        });
});

// Klik tombol "Detail" di kartu produk manapun -> arahkan ke halaman detail
// produk (product.php), sambil membawa info produk (nama, harga, gambar,
// deskripsi) lewat parameter URL. Situs ini tidak punya tabel produk di
// database, jadi info produk diambil langsung dari markup kartu yang sudah ada.
document.addEventListener('click', function (e) {
    const detailBtn = e.target.closest('.product-actions .btn-detail');
    if (!detailBtn) return;

    const card = detailBtn.closest('.product-card');
    if (!card) return;

    e.preventDefault();

    const title = detailBtn.getAttribute('data-title') || (card.querySelector('.product-title') || {}).innerText || '';
    const desc = detailBtn.getAttribute('data-desc') || '';
    const img = (card.querySelector('.product-img') || {}).getAttribute('src') || detailBtn.getAttribute('data-img') || '';
    const priceText = (card.querySelector('.product-price') || {}).innerText || '';
    const price = parseInt(priceText.replace(/[^0-9]/g, ''), 10) || 0;
    const badge = (card.querySelector('.product-badge-top') || {}).innerText || '';
    const label = (card.querySelector('.product-label-corner') || {}).innerText || '';

    const params = new URLSearchParams();
    params.set('title', title.trim());
    params.set('price', price);
    if (desc) params.set('desc', desc);
    if (img) params.set('img', img);
    if (badge) params.set('badge', badge.trim());
    if (label) params.set('label', label.trim());

    window.location.href = 'product.php?' + params.toString();
});
