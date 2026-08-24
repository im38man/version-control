    </main>

    <script>
        // Update angka notif pesan belum dibaca di sidebar. Dipasang di
        // footer.php (bukan di chat.php) supaya jalan di SEMUA halaman,
        // jadi orang tetap kelihatan ada pesan baru walau lagi di halaman lain.
        async function muatBadgeChatSidebar() {
            const badge = document.getElementById('badgeChatUnread');
            if (!badge) return;
            try {
                const res = await fetch('api/pesan.php?action=unread_total');
                const json = await res.json();
                if (json.success) {
                    const total = json.data.total + (json.data.permintaan || 0);
                    badge.textContent = total > 99 ? '99+' : total;
                    badge.style.display = total > 0 ? 'inline-flex' : 'none';
                }
            } catch (e) { /* diem aja kalau gagal, jangan ganggu halaman utama */ }
        }
        muatBadgeChatSidebar();
        setInterval(muatBadgeChatSidebar, 8000);
    </script>

    <script>
        // Nambahin ikon Logout ke bottom-nav-bar pas layar HP (mobile),
        // karena .sidebar-footer (tempat link Logout aslinya) disembunyikan
        // di layar kecil. Dipasang di sini (footer.php) supaya berlaku
        // di SEMUA halaman, bukan cuma satu halaman doang.
        window.addEventListener('DOMContentLoaded', () => {
            const sidebarMenu = document.querySelector('.sidebar-menu');
            if (sidebarMenu && !sidebarMenu.querySelector('.mobile-logout-link')) {
                const logoutLi = document.createElement('li');
                logoutLi.innerHTML = `<a href="logout.php" class="mobile-logout-link" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>`;
                sidebarMenu.appendChild(logoutLi);
            }
        });
    </script>
</body>
</html>
