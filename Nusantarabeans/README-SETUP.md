# Panduan Setup — Nusantara Beans (PHP)

## 1. Import Database
Import `sql/schema.sql` ke MySQL (phpMyAdmin atau CLI). Ini akan membuat
database `nusantara_beans` dan tabel `users`.

## 2. Atur Koneksi Database
Edit `config/db.php`, sesuaikan `$DB_HOST`, `$DB_NAME`, `$DB_USER`, `$DB_PASS`
dengan kredensial server/hosting Anda.

## 3. Buat Akun Admin Master (sekali saja)
Buka `setup-admin.php` di browser lalu isi form. Halaman ini otomatis
terkunci setelah admin master pertama berhasil dibuat — disarankan hapus
atau rename file ini setelah dipakai di server produksi.

## 4. Alur Peran (Role)
- **admin_master** — dibuat lewat `setup-admin.php`. Bisa mengelola akun
  admin biasa (`admin/kelola-admin.php`) dan menghapus akun user
  (`admin/kelola-user.php`).
- **admin** — dibuat oleh admin_master lewat panel admin. Bisa melihat
  daftar user di `admin/kelola-user.php` (tanpa hak hapus).
- **user** — daftar sendiri lewat `register.php`, kelola profil di
  `user.php`.

## 5. Struktur Include
- `includes/navbar.php` — navbar, otomatis berubah sesuai status login
  (Login/Daftar, Akun Saya, Panel Admin, Keluar).
- `includes/footer.php` — footer, sama persis di semua halaman.
- `includes/auth.php` — helper session & pengecekan hak akses
  (`require_login()`, `require_admin()`, `require_admin_master()`).

Semua CSS asli per halaman **tidak diubah sama sekali** — hanya markup
navbar & footer yang dipindah ke include agar tidak duplikat di 11 file.

## 6. Halaman Baru yang Ditambahkan
- `login.php`, `register.php`, `logout.php` (sebelumnya belum ada sama
  sekali di versi statis).
- `admin/index.php`, `admin/kelola-admin.php`, `admin/kelola-user.php`.

## 7. Checkout (Keranjang & Pesanan)
Sudah tersambung penuh ke database:
- Tombol **Beli / Tambah ke Keranjang** di halaman produk (`index.php`,
  `all-product.php`, `search.php`) memanggil `add-to-cart.php` lewat
  `assets/js/cart.js`, otomatis mengarahkan ke `login.php` kalau belum
  login.
- `checkout.php` menampilkan isi keranjang asli dari tabel `cart_items`
  (tambah/kurang qty & hapus lewat `cart-action.php`), lalu tombol
  **Lanjut Checkout** membuka form pengiriman + upload bukti transfer.
- `place-order.php` memindahkan isi keranjang jadi `orders` +
  `order_items`, menyimpan bukti transfer ke
  `uploads/bukti-transfer/`, lalu mengosongkan keranjang.
- `pesanan-sukses.php` menampilkan ringkasan pesanan setelah checkout
  berhasil.
- `admin/kelola-pesanan.php` — admin & admin_master bisa lihat semua
  pesanan, buka bukti transfer, dan ubah status (Menunggu Pembayaran →
  Menunggu Konfirmasi → Dikonfirmasi → Dikirim → Selesai/Dibatalkan).

Jalankan `sql/checkout.sql` (setelah `sql/schema.sql`/`schema-infinityfree.sql`)
untuk membuat tabel `cart_items`, `orders`, dan `order_items`.

Pastikan folder `uploads/bukti-transfer/` bisa ditulis oleh PHP
(writable) di server hosting.

## 8. Chat → Pesan (Realtime via Polling)
Halaman `chat.html` sudah diganti nama jadi `pesan.php`, dan sekarang
**benar-benar tersambung ke database** (bukan demo lagi):
- User mengirim pesan lewat `pesan.php`, tersimpan di tabel `messages`
  lewat `messages-send.php`.
- Halaman otomatis polling `messages-fetch.php` tiap 3 detik untuk
  menampilkan balasan admin tanpa perlu refresh (realtime ala WhatsApp
  Web, cocok untuk hosting shared seperti InfinityFree yang tidak
  mendukung WebSocket).
- Admin membalas dari `admin/pesan.php` — ada daftar semua percakapan
  di sisi kiri (lengkap dengan badge jumlah pesan belum dibaca), klik
  salah satu untuk membuka & membalas, juga polling otomatis.
- Tombol kirim di mobile sudah diperbaiki: sebelumnya box chat memakai
  tinggi `vh` statis (dan `min-height` tetap/`!important` di sisi
  admin) yang tidak menyesuaikan saat keyboard/toolbar browser HP
  muncul, sehingga tombol kirim terdorong keluar layar. Sekarang
  tinggi box dihitung ulang lewat JavaScript (`visualViewport`) supaya
  selalu pas dengan area layar yang benar-benar terlihat.
- Setiap pesan (dari user maupun admin) sekarang otomatis menyertakan
  **daerah pengirim**, dideteksi dari GPS browser (izin lokasi) lalu
  diterjemahkan ke nama kota/provinsi lewat reverse-geocoding
  (OpenStreetMap Nominatim, gratis, dipanggil langsung dari browser).
  Jika user/admin menolak izin lokasi, pesan tetap terkirim seperti
  biasa tanpa label daerah.

Jalankan `sql/messages.sql` (setelah `schema` dan `checkout.sql`) untuk
membuat tabel `messages`.

**Untuk instalasi yang SUDAH berjalan sebelumnya** (tabel `messages`
sudah ada), jalankan tambahan migration berikut sekali saja di
phpMyAdmin agar tidak perlu install ulang dari nol:
- `sql/chat-image-migration.sql` — kolom foto lampiran (jika belum pernah dijalankan)
- `sql/location-migration.sql` — kolom daerah/kota pengirim otomatis
- `sql/emoji-migration.sql` — perbaiki emoji yang jadi "?" saat dikirim
  (tabel diubah ke charset utf8mb4; emoji yang SUDAH terlanjur jadi "?"
  sebelumnya tidak bisa dipulihkan, tapi pesan baru sudah normal)

Selain itu, jam pesan sekarang disamakan ke WIB (Asia/Jakarta) lewat
`config/db.php` — sebelumnya server hosting pakai UTC secara default
sehingga jam yang tampil selisih 7 jam dari jam asli Indonesia. Ini
otomatis berlaku begitu file `config/db.php` diupload ulang, tanpa
perlu migration SQL tambahan (jam pesan lama pun akan ikut terkoreksi
otomatis saat ditampilkan).

