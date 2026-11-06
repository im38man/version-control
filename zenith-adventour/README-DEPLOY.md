# Panduan Deploy Zenith Tour & Travel ke InfinityFree

Situs ini sudah diubah dari HTML statis menjadi PHP dinamis dengan fitur:
- Login/Register user
- Favorit destinasi
- Konfirmasi pembayaran (upload bukti transfer)
- Pemberangkatan (admin menandai status Proses/Selesai untuk booking yang sudah dibayar)
- Testimoni (hanya bisa diisi user yang pemberangkatannya sudah ditandai Selesai oleh admin)
- Pesan user ↔ admin (harus login untuk kirim pesan, tamu hanya bisa lihat tampilan)
- Booking manual via WhatsApp (admin input data pelanggan yang memesan lewat chat WA, dengan atau tanpa akun terdaftar)
- Pengaturan akun user: ganti nama/HP & ganti password sendiri, plus pemulihan password via WhatsApp admin
- Dashboard admin dengan Admin Master (khusus hapus user & jadikan admin) dan Admin biasa

## 1. Buat Akun & Database di InfinityFree

1. Daftar/login di https://infinityfree.com dan buat hosting account baru (dapat subdomain gratis, misal `zenith.infinityfreeapp.com`, atau hubungkan domain sendiri).
2. Di **Client Area / vPanel**, buka menu **MySQL Databases**.
3. Klik **Create Database**, catat 4 hal berikut yang akan diberikan:
   - Database host (contoh: `sqlXXX.infinityfree.com`)
   - Database name (contoh: `if0_12345678_zenith`)
   - Database username (contoh: `if0_12345678`)
   - Database password (yang Anda buat sendiri)

## 2. Import Struktur Database

**Instalasi baru (database masih kosong):**
1. Di vPanel, buka **phpMyAdmin** dari menu MySQL Databases.
2. Pilih database yang baru dibuat di sidebar kiri.
3. Klik tab **Import**, pilih file `sql/schema.sql` dari folder proyek ini, lalu klik **Go**.
4. Pastikan tabel `users`, `favorites`, `bookings`, `payments`, `pesan_messages`, `testimonials` berhasil dibuat.

**Sudah pernah deploy sebelumnya (database sudah ada isinya)?**
Anda hanya perlu menjalankan migrasi tambahan berikut lewat **phpMyAdmin → tab SQL** (salin isi file, tempel, klik **Go**). Data lama Anda tidak akan hilang:
1. `sql/migration_pemberangkatan_testimoni.sql` — jika belum pernah dijalankan (menambahkan fitur Pemberangkatan & Testimoni).
2. `sql/migration_master_admin.sql` — menambahkan kolom `is_master` ke tabel `users`, dan otomatis menjadikan akun admin Anda yang **paling pertama dibuat** sebagai **Admin Master**.
3. `sql/migration_booking_wa.sql` — menambahkan dukungan booking manual via WhatsApp (kolom `nama_pelanggan`, `sumber`, `catatan_admin`, dll di tabel `bookings`, dan membuat `user_id` boleh kosong untuk pelanggan yang belum punya akun).

### Tentang Admin Master

- **Admin Master** adalah satu-satunya peran yang boleh **menghapus pengguna** dan **menjadikan pengguna sebagai admin** di menu **Admin → Pengguna**.
- **Admin biasa** tetap bisa mengakses seluruh dashboard (booking, pembayaran, pemberangkatan, testimoni, pesan), tapi di menu Pengguna dia hanya bisa **melihat**, tombol Jadikan Admin/Cabut Admin/Hapus tidak akan muncul untuknya.
- Kalau Anda instalasi baru, akun admin pertama yang dibuat lewat `admin/setup-admin.php` otomatis menjadi Admin Master.
- Kalau Anda instalasi lama, jalankan `sql/migration_master_admin.sql` — akun admin yang paling lama terdaftar otomatis dijadikan Admin Master.

### Tentang Booking via WhatsApp

- Menu **Admin → Booking via WA** bisa dipakai **semua admin** (tidak perlu Admin Master) untuk mencatat pelanggan yang deal-nya terjadi lewat chat WhatsApp, bukan lewat website.
- Admin bisa pilih: pelanggan **sudah punya akun** (tinggal pilih dari daftar) atau **belum punya akun** (isi nama & email manual, tanpa perlu bikin akun baru).
- Booking hasil input manual ini otomatis tampil di menu **Booking**, **Pemberangkatan**, dan dashboard, dengan label sumber **"WA Admin"** agar mudah dibedakan dari booking yang dibuat sendiri oleh pelanggan lewat web.
- Catatan: fitur **Testimoni** hanya bisa diisi oleh pelanggan yang **punya akun & login** — jadi booking manual untuk pelanggan tanpa akun tidak akan bisa mengisi testimoni, kecuali pelanggan tersebut nanti mendaftar akun sendiri.

### Tentang Ganti Password & Pemulihan Akun

- Setiap user sekarang **wajib** mengisi nomor WhatsApp saat mendaftar (sebelumnya opsional). Ini dipakai untuk pemulihan akun.
- Klik **nama Anda** di navbar (kanan atas saat login) untuk membuka halaman **Pengaturan Akun** — di sana user bisa ubah nama/nomor HP dan ganti password sendiri (harus masukkan password lama dulu).
- Kalau lupa password, user klik **"Lupa password?"** di halaman login → masukkan email → sistem siapkan pesan WhatsApp otomatis ke nomor admin. Karena InfinityFree (hosting gratis) tidak mendukung pengiriman email otomatis, pemulihan password dilakukan **manual lewat WhatsApp**: admin verifikasi identitas pelanggan di chat, lalu buka **Admin → Pengguna → tombol "Reset Password"** di baris user tersebut. Sistem akan membuatkan password acak baru dan menyediakan tombol untuk langsung mengirimkannya ke WhatsApp pelanggan.
- Fitur **Reset Password** di menu Pengguna bisa dipakai oleh **semua admin** (bukan cuma Admin Master), karena ini kebutuhan layanan pelanggan sehari-hari. Yang tetap khusus Admin Master hanya menghapus user & menjadikan user sebagai admin.

## 3. Atur Kredensial Database

1. Buka file `config/db.php` di editor.
2. Ganti 4 baris berikut sesuai data dari Langkah 1:
   ```php
   define('DB_HOST', 'sqlXXX.infinityfree.com');
   define('DB_USER', 'if0_XXXXXXXX');
   define('DB_PASS', 'PASSWORD_DATABASE_ANDA');
   define('DB_NAME', 'if0_XXXXXXXX_zenith');
   ```

## 4. Upload File ke Server

1. Di vPanel, buka **File Manager** (atau gunakan FTP dengan FileZilla, kredensial FTP ada di vPanel → Account → FTP Details).
2. Masuk ke folder `htdocs`.
3. Upload **seluruh isi** folder proyek ini (semua file & folder: `*.php`, `img/`, `config/`, `includes/`, `admin/`, `uploads/`, `.htaccess`, dll) langsung ke dalam `htdocs` (bukan di dalam subfolder).
4. Pastikan folder `uploads/payments/` ikut terupload dan bisa ditulis (permission 755 biasanya sudah cukup di InfinityFree).

## 5. Buat Akun Admin Pertama

1. Buka `https://domain-anda.com/admin/setup-admin.php` di browser.
2. Isi nama, email, dan password untuk akun admin pertama Anda.
3. Setelah berhasil, **segera hapus file `admin/setup-admin.php`** dari server lewat File Manager agar tidak disalahgunakan orang lain.
4. Login admin melalui `https://domain-anda.com/admin/login.php`.

## 6. Uji Coba Situs

- Buka halaman utama (`index.php`) → pastikan tampil normal dan navbar menampilkan tombol **Masuk/Daftar**.
- Daftar akun user baru → coba favoritkan destinasi di halaman paket wisata.
- Login → buka menu **Konfirmasi Pembayaran**, isi form, upload bukti transfer (gambar apa saja untuk uji coba).
- Login sebagai admin → buka **Dashboard Admin** → cek menu **Konfirmasi Pembayaran** untuk menyetujui/menolak.
- Coba **Pesan**: sebagai tamu (belum login) halaman pesan hanya bisa dilihat (input terkunci); setelah login, kirim pesan lalu buka **Dashboard Admin → Pesan Pelanggan** untuk membalas.
- Login sebagai admin → buka **Dashboard Admin → Pemberangkatan**, ubah status booking yang sudah dikonfirmasi pembayarannya menjadi **Proses** lalu **Selesai**.
- Login sebagai user pemilik booking tersebut → buka menu **Testimoni**, form penulisan testimoni akan otomatis muncul untuk booking yang sudah ditandai Selesai. Booking yang belum selesai tidak akan bisa mengisi testimoni.

## Catatan Penting

- **Ganti password admin default** jika Anda memakainya untuk produksi.
- File upload bukti pembayaran disimpan di `uploads/payments/` — folder ini sudah diberi `.htaccess` agar file yang diunggah tidak bisa dieksekusi sebagai skrip PHP (keamanan).
- InfinityFree gratis punya batas resource (CPU/koneksi eksekusi ~30 detik, tanpa cron job kompleks) — situs ini didesain ringan (polling pesan sederhana via AJAX, tanpa WebSocket) sehingga kompatibel.
- Jika muncul error koneksi database, cek kembali 4 kredensial di `config/db.php` — kesalahan tersering adalah menuliskan host sebagai `localhost` padahal InfinityFree memakai host khusus seperti `sqlXXX.infinityfree.com`.
