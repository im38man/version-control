# MANCARE - PHP + MySQL (siap upload ke InfinityFree)

Versi ini mengubah dashboard Mancare dari localStorage murni menjadi aplikasi
PHP + MySQL dengan sistem akun sungguhan: Register, Login, Logout, Ganti Akun,
dan Panel Admin untuk kelola user. Tampilan/tema tetap identik dengan index.html asli.

## Struktur File
```
config.php          -> isi data koneksi database di sini
database.sql         -> import ke phpMyAdmin untuk bikin tabel
setup.php             -> jalankan SEKALI untuk bikin akun admin pertama, lalu HAPUS
login.php / register.php / logout.php / account.php  -> halaman auth
index.php             -> dashboard utama (perlu login)
admin/index.php       -> panel kelola user (khusus admin)
api/                   -> endpoint AJAX (accounts, transactions, allocations, income)
includes/              -> file internal (auth, koneksi, helper) - dilindungi .htaccess
assets/img/            -> logo bank & payment network (svg)
```

## Langkah Deploy ke InfinityFree

1. **Buat database MySQL**
   - Login ke panel InfinityFree > "MySQL Databases" > buat database baru.
   - Catat: DB Host, Database Name, Username, Password yang diberikan.

2. **Import skema database**
   - Buka phpMyAdmin dari panel InfinityFree.
   - Pilih database yang baru dibuat > tab "Import" > upload file `database.sql`.

3. **Isi config.php**
   - Buka `config.php`, ganti 4 baris `define(...)` paling atas dengan data dari
     langkah 1 (DB_HOST, DB_NAME, DB_USER, DB_PASS).

4. **Upload semua file**
   - Upload seluruh isi folder ini ke `htdocs/` (root domain) lewat File Manager
     atau FTP (FileZilla) di InfinityFree.

5. **Buat akun admin pertama**
   - Buka `https://domainkamu.com/setup.php` di browser.
   - Isi username, email, password untuk admin pertama.
   - **Setelah berhasil, HAPUS file `setup.php` dari server** (penting untuk keamanan,
     supaya orang lain tidak bisa membuat admin baru lewat halaman itu).

6. **Selesai**
   - User baru bisa daftar sendiri lewat `register.php`.
   - Admin login lewat `login.php`, otomatis diarahkan ke `admin/index.php`.
   - User biasa login lewat `login.php`, diarahkan ke `index.php` (dashboard).

## Update Terbaru
- **Alokasi Budget kini dikunci maksimal 100%** — baik di tampilan (progress bar +
  input di-clamp otomatis) maupun di server (`api/allocations.php` menolak kalau
  total lebih dari 100%). Jadi total semua pos alokasi tidak akan pernah melebihi 100%.
- **Menu baru "Perpindahan Dana"** di sidebar — untuk mindahin saldo antar
  rekening/dompet sendiri (misal dari Tunai ke Rekening Bank). Setiap perpindahan
  otomatis mencatat 2 transaksi (keluar dari asal, masuk ke tujuan) yang saling
  terhubung lewat `transfer_id`, jadi kalau salah satu dihapus, dua-duanya ikut terhapus.

### Kalau database kamu SUDAH pernah di-import sebelumnya
Jalankan file **`migration_transfer.sql`** lewat phpMyAdmin (Import atau SQL tab)
supaya tabel `transactions` dapat kolom baru `transfer_id`. Kalau baru install dari
nol, cukup pakai `database.sql` yang sudah termasuk kolom ini.

## Update Terbaru: Hutang & Piutang
- Menu baru **"Hutang & Piutang"** di sidebar.
- Setiap catatan hutang/piutang **otomatis ambil/nambah dana** dari rekening yang
  dipilih (Piutang = dana keluar karena dipinjamkan ke orang, Hutang = dana masuk
  karena kamu pinjam dari orang).
- **Ikut dihitung ke Net Worth** dan Total Inflow/Outflow (beda dengan Perpindahan
  Dana yang sengaja tidak dihitung, karena hutang/piutang benar-benar melibatkan
  pihak luar, bukan cuma mutasi antar rekening sendiri).
- Status bisa diedit langsung dari dropdown di tabel: **"Dipinjamkan"/"Berhutang"**
  (belum lunas) atau **"Lunas"**.
- Hapus data hutang/piutang otomatis menghapus juga transaksi dana terkait (saldo
  rekening ikut disesuaikan kembali).

Kalau database kamu sudah pernah di-import sebelumnya, jalankan **`migration_debts.sql`**
lewat phpMyAdmin supaya tabel `debts` dan kolom `transactions.debt_id` dibuat.
Kalau baru install dari nol, `database.sql` yang baru sudah termasuk semuanya.


- **Register**: user baru bikin akun sendiri (otomatis dapat 1 rekening "Tunai").
- **Login / Logout**: sesi PHP asli dengan password ter-hash (bcrypt).
- **Ganti Akun** (`account.php`): ubah username, email, atau password (perlu
  konfirmasi password saat ini).
- **Admin Panel** (`admin/index.php`): lihat semua user, blokir/aktifkan,
  jadikan admin/user biasa, reset password, hapus user beserta datanya.
- Semua data rekening, transaksi, dan alokasi budget sekarang tersimpan di
  MySQL per user (bukan lagi localStorage), jadi bisa diakses dari device manapun.

## Catatan Keamanan
- Semua query pakai prepared statement (PDO) - aman dari SQL Injection.
- Password di-hash dengan `password_hash()` (bcrypt) - tidak pernah disimpan plain text.
- Ada proteksi CSRF token di semua form & request AJAX.
- Folder `includes/` diblokir akses langsung lewat `.htaccess`.
- **Wajib hapus `setup.php` setelah admin pertama dibuat.**
- Kalau mau tambah HTTPS, InfinityFree menyediakan opsi "Force HTTPS" di panel mereka.
