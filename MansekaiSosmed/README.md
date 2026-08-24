# Mansekai Study — PHP + MySQL

Versi PHP dari aplikasi Mansekai Study kamu, dengan sistem login, database MySQL,
dan panel admin buat mengelola materi.

## Fitur utama
- Login & Register (session-based, password di-hash pakai `password_hash`)
- Role: **admin** dan **user**
- **admin.php** → admin bisa Tambah / Edit / Hapus materi
- **Sistem pengajuan akses**: user HARUS ajukan permintaan dulu ke admin buat tiap
  materi (tombol "Ajukan Akses" di `materi.php`). Materi baru bisa dibaca setelah
  admin klik "Setujui" di panel admin. Kalau ditolak, user bisa ajukan ulang.
- **baca-materi.php** ngecek otomatis: kalau belum di-approve, user gak bisa baca isinya.
- Admin selalu bisa baca semua materi tanpa perlu ajukan (karena dia yang bikin).
- Halaman lain (Notepad, Kalkulator, Arus Kas, Upload, Pengingat, Profil) tetap ada,
  sekarang dilindungi login (tidak bisa diakses tanpa login), **dan datanya
  tersimpan di database per akun** (lihat bagian "Data per-user" di bawah).
- Dashboard (`index.php`) pakai desain asli kamu (welcome card, todo-list, modul,
  jadwal, target belajar, timer) — cuma dibungkus session/login check, dan semua
  isinya (todo, modul, jadwal, target, winstreak) sekarang tersimpan di database
  per akun juga.

## Data per-user (Notepad, Arus Kas, Pengingat, Profil, Dashboard, Upload)

Awalnya fitur-fitur ini cuma nyimpen data di `localStorage` browser (jadi bisa
ketuker antar user yang pakai browser/device yang sama, dan hilang kalau ganti
device). Sekarang semuanya nyimpen ke database MySQL, dikunci per `user_id` dari
session login, lewat endpoint AJAX di folder `api/`:

- `api/notepad.php`   → catatan bebas (tabel `notes`)
- `api/aruskas.php`   → transaksi arus kas (tabel `cashflow`)
- `api/pengingat.php` → pengingat/alarm (tabel `reminders`)
- `api/profil.php`    → bio & link sosial media (tabel `profil`)
- `api/avatar.php`    → upload foto profil (file fisik + kolom `profil.avatar`)
- `api/dashboard.php` → todo, modul, jadwal, target, winstreak (`dash_*`)
- `api/upload.php`    → upload dokumen (tabel `dokumen` + file fisik)
- `api/download.php`  → buka/preview dokumen (cek kepemilikan dulu)

**Kalkulator** tidak diubah — dia stateless (tidak ada history yang perlu disimpan).

### Upload dokumen & foto profil — di mana disimpan?
- Dokumen (`upload.php`) disimpan sebagai file fisik di
  `uploads/documents/{user_id}/`, nama file di-random (bukan pakai nama asli)
  supaya tidak ketebak/bentrok, sementara nama aslinya tetap dicatat di tabel
  `dokumen` biar tetap ditampilkan ke user. Folder ini diblokir akses langsung
  lewat `.htaccess` — file cuma bisa dibuka lewat `api/download.php`, yang
  ngecek dulu file itu punya user yang login atau bukan.
- Foto profil (`profil.php`) disimpan di `uploads/avatars/{user_id}.{ekstensi}`
  dan path-nya disimpan di kolom `profil.avatar`. Folder ini boleh diakses
  langsung (buat `<img src="...">`), tapi tidak bisa dieksekusi sebagai script.
- Kedua folder itu harus **writable** oleh PHP (chmod 755, atau 775/777 kalau
  masih gagal upload karena izin folder di hosting kamu).

## Cara setup (XAMPP / hosting PHP)

1. **Copy folder ini** ke `htdocs` (XAMPP) atau folder public_html hosting kamu.
2. **Buat database**: buka phpMyAdmin → Import → jalankan file SQL **urut sesuai
   nomor ini**:
   1. `sql/mansekai.sql` → bikin database `mansekai_study` + tabel `users` & `materi`.
   2. `sql/update_per_user_data.sql` → tabel Notepad, Arus Kas, Pengingat, Profil, Dashboard.
   3. `sql/update_uploads.sql` → tabel `dokumen` buat fitur Upload.
   (`sql/update_pengajuan.sql` cuma dipakai kalau kamu upgrade dari versi lama yang
   belum ada sistem pengajuan materi — kalau baru import `mansekai.sql`, skip file ini.)
3. **Atur koneksi**: edit `config/koneksi.php` kalau username/password MySQL kamu
   beda dari default XAMPP (`root` tanpa password).
4. **Buat akun admin pertama**: buka `setup_admin.php` di browser, isi nama/username/password.
   Setelah berhasil, **hapus file `setup_admin.php`** dari server (biar orang lain gak bisa
   bikin admin baru sembarangan).
5. Buka `login.php`, login pakai akun admin yang baru dibuat.
6. Masuk ke menu **Kelola Materi** di sidebar → tambahkan materi. Materi muncul
   di halaman `materi.php`, tapi user masih harus **ajukan akses** dulu.
7. User baru bisa daftar sendiri lewat `register.php` (otomatis jadi role `user`, bukan admin).
8. Waktu user klik **"Ajukan Akses"** di suatu materi, pengajuan itu muncul di
   `admin.php` bagian **"Pengajuan Akses Materi"** → admin klik **Setujui** atau **Tolak**.
   Setelah disetujui, baru user bisa klik "Baca Materi".
9. Pastikan folder `uploads/documents/` dan `uploads/avatars/` bisa ditulis oleh
   PHP (writable), supaya fitur Upload Dokumen dan Foto Profil bisa jalan.

## Struktur folder
```
config/koneksi.php     -> koneksi database
includes/auth.php       -> cek login & cek role admin
includes/header.php     -> sidebar + head (dipakai semua halaman)
includes/footer.php     -> penutup halaman
assets/style.css        -> style bersama (sidebar, card, form, tabel admin, dll)
api/                    -> endpoint AJAX (JSON) untuk data per-user, lihat bagian di atas
uploads/documents/      -> file dokumen per user (folder {user_id}/), akses diblokir langsung
uploads/avatars/        -> foto profil per user ({user_id}.ekstensi), boleh diakses langsung
sql/mansekai.sql         -> struktur database + contoh data materi
sql/update_per_user_data.sql -> tabel Notepad/Arus Kas/Pengingat/Profil/Dashboard
sql/update_uploads.sql  -> tabel Upload Dokumen
admin.php               -> CRUD materi (khusus admin)
materi.php / baca-materi.php -> tampilan materi untuk user
login.php / register.php / logout.php / setup_admin.php
index.php               -> dashboard
notepad.php, kalkulator.php, aruskas.php, upload.php, pengingat.php, profil.php
```

## Catatan keamanan
- Semua query pakai prepared statement (`mysqli_prepare`), aman dari SQL Injection.
- Password disimpan pakai `password_hash()` (bcrypt), bukan plain text.
- Semua endpoint di `api/` ngecek session login dulu, dan semua query di-filter
  pakai `user_id` dari session — jadi user A tidak bisa baca/hapus data user B
  walaupun tahu ID-nya.
- File upload divalidasi ekstensi & ukurannya di server (bukan cuma di HTML),
  dan foto profil dicek beneran gambar (`getimagesize`) bukan cuma nama filenya.
- Jangan lupa hapus `setup_admin.php` setelah dipakai sekali.
