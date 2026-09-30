/* dream.js — data dummy Dreambook untuk halaman Home.
 *
 * Field:
 *   title         : judul mimpi (dipakai untuk pencarian, usahakan unik)
 *   image         : link gambar (URL http/https atau path relatif, mis. "img/rumah.jpg")
 *   createdAt     : tanggal mimpi dibuat (YYYY-MM-DD)
 *   targetDate    : target tercapainya mimpi (YYYY-MM-DD)
 *   bio           : deskripsi singkat
 *   price         : harga dalam Rupiah (opsional, isi null kalau tidak ada)
 *   dreamcometrue : true = sudah tercapai, false = masih diimpikan
 */
window.DREAMS = [
  {
    title: "Rumah Impian di Bandung",
    image: "https://picsum.photos/seed/dream-house/640/420",
    createdAt: "2025-01-10",
    targetDate: "2030-12-31",
    bio: "Rumah dua lantai dengan halaman kecil, ruang kerja yang tenang, dan pemandangan pegunungan dari balkon.",
    price: 1200000000,
    dreamcometrue: false
  },
  {
    title: "MacBook Pro untuk Kerja",
    image: "https://picsum.photos/seed/dream-macbook/640/420",
    createdAt: "2025-03-02",
    targetDate: "2025-12-20",
    bio: "Laptop yang kuat untuk coding, desain, dan editing video tanpa hambatan.",
    price: 28500000,
    dreamcometrue: true
  },
  {
    title: "Umroh Bersama Orang Tua",
    image: "https://picsum.photos/seed/dream-umroh/640/420",
    createdAt: "2025-02-14",
    targetDate: "2026-06-30",
    bio: "Membawa Ayah dan Ibu menunaikan ibadah umroh dengan tenang dan tanpa terburu-buru.",
    price: 65000000,
    dreamcometrue: true
  },
  {
    title: "Liburan ke Kyoto, Jepang",
    image: "https://picsum.photos/seed/dream-kyoto/640/420",
    createdAt: "2025-05-21",
    targetDate: "2027-04-01",
    bio: "Melihat sakura di musim semi, menyusuri kuil-kuil tua, dan mencicipi kuliner lokal.",
    price: 35000000,
    dreamcometrue: false
  },
  {
    title: "Kamera Mirrorless Pertama",
    image: "https://picsum.photos/seed/dream-camera/640/420",
    createdAt: "2025-08-08",
    targetDate: "2026-03-15",
    bio: "Mulai serius belajar fotografi dan mengabadikan perjalanan dengan kualitas yang layak dikenang.",
    price: 15500000,
    dreamcometrue: true
  },
  {
    title: "Finish Half Marathon 21K",
    image: "https://picsum.photos/seed/dream-marathon/640/420",
    createdAt: "2026-01-05",
    targetDate: "2026-11-15",
    bio: "Latihan rutin tiga kali seminggu sampai bisa menyelesaikan 21 kilometer tanpa berhenti.",
    price: null,
    dreamcometrue: false
  },
  {
    title: "Menerbitkan Buku Pertama",
    image: "https://picsum.photos/seed/dream-book/640/420",
    createdAt: "2025-06-18",
    targetDate: "2027-08-17",
    bio: "Menulis kumpulan cerita tentang perjalanan dan pelajaran hidup, lalu menerbitkannya.",
    price: null,
    dreamcometrue: false
  },
  {
    title: "Gitar Akustik Impian",
    image: "https://picsum.photos/seed/dream-guitar/640/420",
    createdAt: "2025-04-12",
    targetDate: "2025-10-10",
    bio: "Gitar akustik dengan suara hangat untuk menemani latihan lagu-lagu favorit setiap malam.",
    price: 8900000,
    dreamcometrue: true
  }
];