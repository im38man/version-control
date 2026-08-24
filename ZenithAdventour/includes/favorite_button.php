<?php
/**
 * Merender tombol "Favoritkan" untuk sebuah destinasi.
 * Jika belum login, tombol tetap tampil tapi klik akan mengarahkan ke halaman login
 * (sesuai aturan: pengunjung belum login hanya bisa melihat, tidak bisa menyimpan favorit).
 */
function render_favorite_button($slug, $title) {
    global $koneksi;

    $is_fav = false;
    if (is_logged_in()) {
        $stmt = mysqli_prepare($koneksi, 'SELECT id FROM favorites WHERE user_id = ? AND destination_slug = ?');
        mysqli_stmt_bind_param($stmt, 'is', $_SESSION['user_id'], $slug);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $is_fav = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
    }
    ?>
    <button
        type="button"
        class="btn-favorite <?= $is_fav ? 'is-active' : '' ?>"
        data-slug="<?= h($slug) ?>"
        data-title="<?= h($title) ?>"
        data-logged-in="<?= is_logged_in() ? '1' : '0' ?>"
        onclick="toggleFavorite(this)"
        style="display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid #e5e0d8;padding:10px 18px;border-radius:30px;cursor:pointer;font-family:'Poppins',sans-serif;font-size:13px;font-weight:500;color:<?= $is_fav ? '#c5423b' : '#1a2f27' ?>;transition:all 0.3s;">
        <i class="fa-<?= $is_fav ? 'solid' : 'regular' ?> fa-heart"></i>
        <span class="fav-label"><?= $is_fav ? 'Tersimpan di Favorit' : 'Tambah ke Favorit' ?></span>
    </button>
    <script>
    if (typeof toggleFavorite !== 'function') {
        function toggleFavorite(btn) {
            if (btn.dataset.loggedIn !== '1') {
                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname.split('/').pop());
                return;
            }
            const slug = btn.dataset.slug;
            const title = btn.dataset.title;
            btn.disabled = true;
            fetch('favorite.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'slug=' + encodeURIComponent(slug) + '&title=' + encodeURIComponent(title)
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                if (!data.success) {
                    if (data.need_login) window.location.href = 'login.php';
                    return;
                }
                const label = btn.querySelector('.fav-label');
                const icon = btn.querySelector('i');
                if (data.favorited) {
                    btn.classList.add('is-active');
                    btn.style.color = '#c5423b';
                    icon.classList.remove('fa-regular');
                    icon.classList.add('fa-solid');
                    label.textContent = 'Tersimpan di Favorit';
                } else {
                    btn.classList.remove('is-active');
                    btn.style.color = '#1a2f27';
                    icon.classList.remove('fa-solid');
                    icon.classList.add('fa-regular');
                    label.textContent = 'Tambah ke Favorit';
                }
            })
            .catch(() => { btn.disabled = false; });
        }
    }
    </script>
    <?php
}
