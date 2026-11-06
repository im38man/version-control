<?php
/**
 * Footer terpadu untuk seluruh halaman.
 * Desain diambil dari footer halaman paket-*.php.
 * Style dibuat mandiri (self-contained) agar tampilan konsisten
 * di semua halaman meski CSS bawaan tiap halaman berbeda-beda.
 */
?>
<style>
.site-footer { background-color: #111e19; color: rgba(255,255,255,0.5); padding: 60px 5% 30px 5%; font-size: 13px; margin-top: auto; }
.site-footer .footer-grid-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 40px; margin-bottom: 30px; max-width: 1200px; margin-left: auto; margin-right: auto; }
.site-footer .footer-info-block h3 { color: #fff; font-family: 'Playfair Display', serif; font-size: 18px; margin-bottom: 15px; }
.site-footer .footer-info-block p, .site-footer .footer-info-block a { color: rgba(255, 255, 255, 0.6); line-height: 1.8; margin-bottom: 8px; display: block; transition: color 0.3s; font-size: 13px; }
.site-footer .footer-info-block a:hover { color: #c5a880; }
.site-footer .footer-info-block i { margin-right: 8px; color: #c5a880; width: 16px; }
.site-footer .footer-logo { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; color: #fff; margin-bottom: 15px; }
.site-footer .footer-socials { display: flex; gap: 20px; margin-top: 15px; }
.site-footer .footer-socials a { color: rgba(255,255,255,0.6); font-size: 20px; transition: color 0.3s; display: inline-block; margin-bottom: 0; }
.site-footer .footer-socials a:hover { color: #c5a880; }
.site-footer .copyright { text-align: center; max-width: 1200px; margin: 0 auto; font-size: 12px; color: rgba(255,255,255,0.35); }
</style>
<footer class="site-footer">
    <div class="footer-grid-container">
        <div class="footer-info-block">
            <div class="footer-logo">ZENITH-ADVENTOUR</div>
            <p>Menyediakan layanan perjalanan wisata premium kelas utama ke berbagai daerah di Indonesia dengan kenyamanan dan aman terpercaya.</p>
            <div class="footer-socials">
                <a href="https://instagram.com" target="_blank" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://tiktok.com" target="_blank" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                <a href="https://facebook.com" target="_blank" title="Facebook"><i class="fa-brands fa-facebook"></i></a>
                <a href="https://wa.me/62895333841200" target="_blank" title="WhatsApp Admin"><i class="fa-brands fa-whatsapp"></i></a>
            </div>
        </div>
        <div class="footer-info-block">
            <h3>Alamat Kantor</h3>
            <p><i class="fa-solid fa-location-dot"></i> Jl. Cihampelas, Bandung, Jawa barat, Indonesia</p>
            <p><i class="fa-solid fa-phone"></i> +62 89-5333-841-200</p>
            <p><i class="fa-solid fa-envelope"></i> info@zenith-adventour.com</p>
        </div>
        <div class="footer-info-block">
            <h3>Link Website</h3>
            <a href="https://www.zenith-adventour.com" target="_blank"><i class="fa-solid fa-globe"></i> www.zenith-adventour.com</a>
            <a href="index.php#destinasi"><i class="fa-solid fa-chevron-right"></i> Paket Liburan</a>
            <a href="favorit-saya.php"><i class="fa-solid fa-chevron-right"></i> Favorit Saya</a>
            <a href="payment-confirm.php"><i class="fa-solid fa-chevron-right"></i> Konfirmasi Pembayaran</a>
            <a href="pesan.php"><i class="fa-solid fa-chevron-right"></i> Pesan Admin</a>
            <a href="testimoni.php"><i class="fa-solid fa-chevron-right"></i> Testimoni</a>
        </div>
    </div>
    <div class="copyright">
        <p>&copy; 2026 zenith-adventour Tour & Travel. Crafted for elegant journeys.</p>
    </div>
</footer>
