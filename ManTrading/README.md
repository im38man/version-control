# ManTrading — PHP + MySQL

Konversi dari HTML/React statis (localStorage) ke PHP + MySQL, lengkap dengan wizard instalasi (`setup-admin.php`) dan dukungan hosting **InfinityFree**.

## Cara Install (pakai wizard, disarankan)

1. Upload semua file project ke hosting (folder `htdocs`/`www` kalau localhost, atau folder root domain kalau di hosting seperti InfinityFree).
2. Buat database MySQL kosong dulu:
   - **InfinityFree**: buka panel → menu **MySQL Databases** → buat database baru, catat *hostname*, *nama database*, *username*, *password* yang diberikan (biasanya format `sqlXXX.infinityfree.com` dan `epiz_xxxxxxx_dbname`).
   - **Localhost (XAMPP/Laragon)**: buat database lewat phpMyAdmin, atau langsung isi nama database apapun — wizard akan buatkan tabelnya otomatis.
3. Pastikan folder `uploads/community/` bisa ditulis (permission 755/775). Kalau upload lewat File Manager hosting biasanya sudah otomatis writable.
4. Buka `https://domainlu.com/setup-admin.php` di browser.
   - **Step 1**: isi host/nama/user/password database → klik "Sambungkan & Buat Tabel". Wizard otomatis import `schema.sql` (bikin semua tabel + data kelas VIP).
   - **Step 2**: buat akun admin pertama (nama, email, password sendiri).
5. Selesai! Bisa langsung ke `login.php`.

Setelah instalasi sukses, `setup-admin.php` mengunci diri sendiri (file `.installed` dibuat) supaya tidak bisa dijalankan ulang sembarangan. Kalau perlu instal ulang, hapus file `.installed` lewat FTP/File Manager lalu buka `setup-admin.php` lagi.

## Migrasi Database (untuk yang sudah terlanjur install sebelum update ini)

Kalau database lu udah jalan duluan (bukan instalasi baru), jalankan query ini sekali lewat **phpMyAdmin → tab SQL** biar dapet kolom `content_url` yang baru (buat link halaman materi tiap kelas VIP):

```sql
ALTER TABLE vip_classes ADD COLUMN content_url VARCHAR(255) NULL AFTER duration;
ALTER TABLE trades ADD COLUMN tp1_hit TINYINT(1) NOT NULL DEFAULT 0 AFTER image_after;
ALTER TABLE trades ADD COLUMN tp2_hit TINYINT(1) NOT NULL DEFAULT 0 AFTER tp1_hit;
ALTER TABLE trades ADD COLUMN tp3_hit TINYINT(1) NOT NULL DEFAULT 0 AFTER tp2_hit;
ALTER TABLE trades ADD COLUMN tp4_hit TINYINT(1) NOT NULL DEFAULT 0 AFTER tp3_hit;
ALTER TABLE trades ADD COLUMN tp5_hit TINYINT(1) NOT NULL DEFAULT 0 AFTER tp4_hit;
ALTER TABLE trades ADD COLUMN tp1_price DECIMAL(18,5) NULL AFTER tp5_hit;
ALTER TABLE trades ADD COLUMN tp2_price DECIMAL(18,5) NULL AFTER tp1_price;
ALTER TABLE trades ADD COLUMN tp3_price DECIMAL(18,5) NULL AFTER tp2_price;
ALTER TABLE trades ADD COLUMN tp4_price DECIMAL(18,5) NULL AFTER tp3_price;
ALTER TABLE trades ADD COLUMN tp5_price DECIMAL(18,5) NULL AFTER tp4_price;
ALTER TABLE trades ADD COLUMN tp1_pips DECIMAL(10,2) NULL AFTER tp5_price;
ALTER TABLE trades ADD COLUMN tp1_usd DECIMAL(14,2) NULL AFTER tp1_pips;
ALTER TABLE trades ADD COLUMN tp2_pips DECIMAL(10,2) NULL AFTER tp1_usd;
ALTER TABLE trades ADD COLUMN tp2_usd DECIMAL(14,2) NULL AFTER tp2_pips;
ALTER TABLE trades ADD COLUMN tp3_pips DECIMAL(10,2) NULL AFTER tp2_usd;
ALTER TABLE trades ADD COLUMN tp3_usd DECIMAL(14,2) NULL AFTER tp3_pips;
ALTER TABLE trades ADD COLUMN tp4_pips DECIMAL(10,2) NULL AFTER tp3_usd;
ALTER TABLE trades ADD COLUMN tp4_usd DECIMAL(14,2) NULL AFTER tp4_pips;
ALTER TABLE trades ADD COLUMN tp5_pips DECIMAL(10,2) NULL AFTER tp4_usd;
ALTER TABLE trades ADD COLUMN tp5_usd DECIMAL(14,2) NULL AFTER tp5_pips;
ALTER TABLE trades ADD COLUMN sl_hit TINYINT(1) NOT NULL DEFAULT 0 AFTER tp5_usd;
ALTER TABLE trades ADD COLUMN sl_pips DECIMAL(10,2) NULL AFTER sl_hit;
ALTER TABLE trades ADD COLUMN sl_usd DECIMAL(14,2) NULL AFTER sl_pips;
```

Kalau muncul error "Duplicate column name", berarti kolomnya udah ada — aman, abaikan aja.

## Cara Install Manual (tanpa wizard)

1. Import `schema.sql` ke database yang **sudah dibuat duluan** (InfinityFree tidak izinkan `CREATE DATABASE` lewat script).
2. Edit `config.db.php`, isi 4 constant `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
3. Buat file kosong bernama `.installed` di folder root project (menandakan sudah "terinstal").
4. Untuk bikin akun admin pertama, daftar biasa lewat `login.php?view=register`, lalu ubah kolom `role` user tersebut jadi `'admin'` langsung lewat phpMyAdmin.

## Catatan Khusus InfinityFree

- **Database wajib dibuat dulu di panel hosting** — user MySQL di InfinityFree tidak punya izin `CREATE DATABASE`, makanya `schema.sql` sudah tidak mengandung perintah itu.
- **PHP version**: masuk ke panel InfinityFree → set PHP version ke 8.0/8.1 ke atas (default kadang masih PHP lama), supaya semua fitur (password_hash, mysqli, dsb) jalan normal.
- **Foto Community disajikan lewat `serve-image.php`** (proxy PHP), bukan link langsung ke folder `uploads/`. Ini untuk menghindari kasus foto tidak muncul karena beberapa hosting gratis memblokir akses langsung/hotlink ke file di folder upload.
- **Upload max size**: InfinityFree membatasi ukuran upload di beberapa paket gratis (kadang di bawah default PHP). Batas di aplikasi ini sudah diset 3MB per foto — kalau masih gagal upload, cek juga batas `upload_max_filesize` di panel hosting.
- **Folder `uploads/`** sudah diberi `.htaccess` supaya file yang diupload tidak bisa dieksekusi sebagai script — aman untuk hosting shared seperti InfinityFree.
- Tidak ada fitur yang pakai `exec()`, `shell_exec()`, cron job, atau `mail()` — jadi kompatibel dengan batasan hosting gratis.

## Apa yang diperbaiki/diubah dari versi awal (React/localStorage)

**1. Backend PHP + MySQL penuh** — semua data (trades, posts, likes, komentar, kelas VIP, request akses) tersimpan di database. Struktur tabel ada di `schema.sql`.

**2. Login & logika user/admin yang nyata** — sebelumnya tombol login cuma `alert()` lalu redirect, dan toggle "Admin/User" di halaman community adalah tombol UI biasa. Sekarang login pakai session PHP + password hashed (`password_hash`/`password_verify`), dan role admin/user dicek di server (`require_admin()`).

**3. VIP Class — wajib ajukan dulu ke admin** — user baru berstatus `none` → tampil gerbang "Ajukan Akses VIP Class" → status `pending` → admin approve/reject lewat menu sidebar **Kelola Akses VIP**. Begitu disetujui, semua materi kelas VIP terbuka.

**4. Community — upload foto asli, bukan link** — form posting pakai `<input type="file">`, tervalidasi (JPG/PNG/WEBP, maks 3MB), disimpan ke `uploads/community/`, ditampilkan lewat proxy `serve-image.php`.

**5. Wizard instalasi `setup-admin.php`** — tidak perlu edit file config manual atau import SQL lewat CLI; tinggal isi form dan klik.

## Struktur Folder
```
ManTrading/
├── setup-admin.php                # wizard instalasi (jalan pertama kali)
├── config.php                # logic koneksi + guard redirect ke setup
├── config.db.php             # kredensial DB (diisi otomatis oleh setup-admin.php)
├── schema.sql                 # struktur tabel + seed data kelas VIP
├── serve-image.php            # proxy penyaji foto community
├── login.php / logout.php     # otentikasi
├── index.php                  # dashboard jurnal trading
├── trade-save.php / trade-delete.php
├── community.php              # feed komunitas
├── post-save.php / post-delete.php / like.php / comment-save.php
├── vip-class.php              # halaman kelas VIP (gated)
├── vip-request.php            # user ajukan akses
├── admin/vip-requests.php     # admin approve/reject
├── admin/vip-manage.php       # admin kelola kelas & modul VIP
├── materi/materi1.php         # contoh/template halaman konten materi VIP
├── includes/
│   ├── auth.php                 # helper login & proteksi halaman
│   ├── head.php                 # head HTML bersama
│   └── sidebar.php              # sidebar navigasi bersama
└── uploads/community/          # foto hasil upload (+ .htaccess proteksi)
```

## Catatan Keamanan
- Semua query pakai prepared statement (`mysqli`), aman dari SQL injection.
- Password di-hash dengan bcrypt (`password_hash`), tidak disimpan plaintext.
- Setiap halaman yang butuh login memanggil `require_login()`; halaman admin memanggil `require_admin()`.
- `setup-admin.php` otomatis mengunci diri setelah instalasi selesai (file `.installed`).
