<?php
require_once __DIR__ . '/auth.php';
$__user = current_user();

$__cart_count = 0;
$__pesan_unread = 0;
if ($__user) {
    $__cart_stmt = $pdo->prepare('SELECT COALESCE(SUM(qty), 0) FROM cart_items WHERE user_id = ?');
    $__cart_stmt->execute([$__user['id']]);
    $__cart_count = (int) $__cart_stmt->fetchColumn();

    $__pesan_stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE user_id = ? AND sender_role = 'admin' AND is_read = 0");
    $__pesan_stmt->execute([$__user['id']]);
    $__pesan_unread = (int) $__pesan_stmt->fetchColumn();
}

function __badge_text($n) {
    return $n > 99 ? '99+' : (string) $n;
}
?>
<style>
    .nav-icon-wrapper { position: relative; display: inline-flex; align-items: center; }
    .nav-badge {
        position: absolute;
        top: -7px;
        right: -9px;
        background: #e63946;
        color: #fff;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
        font-weight: 700;
        line-height: 1;
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        box-shadow: 0 0 0 2px var(--color-coffee, #2b1a12);
    }
</style>
    <!-- NAVBAR -->
    <nav class="navbar">
        <a href="index.php" class="navbar-brand-wrapper">
            <div class="navbar-brand">Nusantara<span>Beans</span></div>
            <div class="navbar-tagline">Arabika Premium Indonesia</div>
        </a>
        <div class="navbar-right">
            <div class="navbar-icons">
                <i class="fas fa-search" id="searchBtn" title="Cari Produk"></i>
                <a href="checkout.php" class="nav-icon-wrapper" title="Keranjang">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="nav-badge" id="navCartBadge" style="<?php echo $__cart_count > 0 ? '' : 'display:none;'; ?>"><?php echo __badge_text($__cart_count); ?></span>
                </a>
                <a href="pesan.php" class="nav-icon-wrapper" title="Pesan Admin">
                    <i class="fas fa-comments"></i>
                    <span class="nav-badge" id="navPesanBadge" style="<?php echo $__pesan_unread > 0 ? '' : 'display:none;'; ?>"><?php echo __badge_text($__pesan_unread); ?></span>
                </a>
                <?php if ($__user): ?>
                    <?php if (in_array($__user['role'], ['admin', 'admin_master'])): ?>
                        <a href="admin/index.php"><i class="fas fa-user-shield" title="Panel Admin"></i></a>
                    <?php endif; ?>
                    <a href="user.php"><i class="fas fa-user" title="Akun Saya (<?php echo htmlspecialchars($__user['name']); ?>)"></i></a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt" title="Keluar"></i></a>
                <?php else: ?>
                    <a href="login.php"><i class="fas fa-user" title="Login / Daftar"></i></a>
                <?php endif; ?>
            </div>

            <div class="burger-container">
                <div class="burger-btn" id="burgerBtn">
                    <i class="fas fa-bars"></i>
                </div>
                <div class="social-dropdown" id="socialDropdown">
                    <i class="fas fa-shopping-bag" title="Shopee"></i>
                    <i class="fab fa-tiktok" title="TikTok"></i>
                    <i class="fab fa-instagram" title="Instagram"></i>
                    <i class="fab fa-facebook-f" title="Facebook"></i>
                    <i class="fab fa-whatsapp" title="WhatsApp"></i>
                </div>
            </div>
        </div>
    </nav>

    <!-- SEARCH DROPDOWN BAWAH NAVBAR -->
    <form action="search.php" method="GET" class="search-dropdown-bar" id="searchDropdownBar">
        <div class="search-bar-inner">
            <input type="text" name="q" class="search-bar-input" id="searchBarInput" placeholder="Ketik nama kopi, biji, atau proses..." required>
            <button type="submit" class="search-bar-btn"><i class="fas fa-search"></i></button>
        </div>
    </form>

    <?php if ($__user): ?>
    <script>
        (function () {
            function updatePesanBadge(count) {
                var badge = document.getElementById('navPesanBadge');
                if (!badge) return;
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }
            }
            function pollPesanUnread() {
                fetch('pesan-unread-count.php')
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data && data.status === 'ok') updatePesanBadge(data.count);
                    })
                    .catch(function () {});
            }
            setInterval(pollPesanUnread, 10000);
        })();
    </script>
    <?php endif; ?>
