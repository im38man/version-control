<?php
/**
 * ManTrading - Contoh Halaman Materi VIP Class
 * Ini TEMPLATE. Copy file ini jadi materi2.php, materi3.php, dst untuk kelas lain,
 * lalu isi konten di bagian yang ditandai "GANTI DI SINI".
 * Path file ini yang diisi ke field "Link Materi" di halaman admin/vip-manage.php
 * (contoh: materi/materi1.php).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

// Proteksi tambahan: pastikan user beneran sudah disetujui akses VIP-nya,
// biar gak bisa ditembus lewat tebak-tebak URL walaupun sudah login.
$vipStatus = refresh_vip_status($conn, current_user()['id']);
if ($vipStatus !== 'approved') {
    flash_set('Lu belum punya akses VIP Class. Ajukan dulu ya bro.', 'error');
    header('Location: ../vip-class.php');
    exit;
}

$basePath = '../';
$pageTitle = 'ManTrading - Materi: Gold Scalping Elite'; // GANTI DI SINI: judul tab browser
require_once __DIR__ . '/../includes/head.php';
?>
<div class="flex bg-gray-50 lg:h-screen lg:overflow-hidden">
  <?php $activeTab = 'vip'; require __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="flex-1 flex flex-col min-w-0 lg:overflow-hidden">
    <header class="bg-white px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200 flex flex-wrap justify-between items-center gap-3 z-10 sticky top-0 shadow-sm">
      <div class="flex items-center gap-3 sm:gap-4 min-w-0">
        <button onclick="toggleSidebar(true)" class="lg:hidden text-gray-500 hover:text-gray-800 shrink-0"><i class="fa-solid fa-bars text-xl"></i></button>
        <div class="min-w-0">
          <a href="../vip-class.php" class="text-[11px] font-bold text-indigo-500 hover:text-indigo-700 flex items-center gap-1 mb-1"><i class="fa-solid fa-arrow-left"></i> Kembali ke VIP Class</a>
          <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-800 tracking-tight truncate">Gold Scalping Elite</h1>
          <!-- GANTI DI SINI: judul kelas -->
        </div>
      </div>
    </header>

    <main class="flex-1 lg:overflow-y-auto p-3 sm:p-4 md:p-6 lg:p-8 custom-scrollbar bg-gray-50/50">
      <div class="max-w-3xl mx-auto flex flex-col gap-5 sm:gap-6 animate-fade-in-up">

        <!-- ============ GANTI SEMUA KONTEN DI BAWAH INI SESUAI MATERI KELAS ============ -->

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="aspect-video bg-gray-900 flex items-center justify-center text-white/40">
            <!-- Ganti dengan <iframe> video (YouTube/Vimeo) atau <img> materi -->
            <i class="fa-solid fa-play-circle text-5xl"></i>
          </div>
          <div class="p-5 sm:p-6">
            <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-3">Modul 1: Pola Market Structure & Liquidity Sweep di XAUUSD</h2>
            <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed space-y-3">
              <p>Tulis konten materi di sini — bisa berupa penjelasan teks, embed video, gambar chart, atau link download PDF/e-book.</p>
              <p>Elemen HTML biasa (paragraf, list, gambar, dll) bisa langsung dipakai di area ini karena halamannya PHP biasa, bukan template kaku.</p>
            </div>
          </div>
        </div>

        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 flex items-start gap-3">
          <i class="fa-solid fa-circle-info text-indigo-500 mt-0.5"></i>
          <p class="text-xs sm:text-sm text-indigo-700 leading-relaxed">
            Ini halaman contoh/template. Duplikat file ini (mis. jadi <code class="bg-white/60 px-1 rounded">materi2.php</code>) untuk bikin materi kelas VIP lainnya, lalu isi field <b>Link Materi</b> di halaman <b>Edit Materi VIP</b> (admin) dengan path file barunya.
          </p>
        </div>

        <!-- ============ BATAS AKHIR KONTEN YANG DIGANTI ============ -->

      </div>
    </main>
  </div>
</div>
</body>
</html>
