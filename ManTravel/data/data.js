/* ManTravel Elite - data dummy
   Semua kartu dan detail itinerary ada di sini. Untuk menambah destinasi, salin satu blok
   di DESTINATIONS lalu ubah isinya. Field yang dipakai:
   id (unik, tanpa spasi), category (salah satu dari CATEGORIES), title, city, country, region,
   nights, price (angka rupiah per orang), img (URL foto), short (teks kartu), desc (teks detail),
   includes (daftar yang termasuk), days (jadwal: t = judul, d = isi).
   Catatan: URL foto dummy bisa saja berubah atau tidak cocok dengan lokasinya. Ganti field `img`
   dengan foto sendiri (mis. 'img/paris.jpg'); jika gagal dimuat, kartu menampilkan nama kota. */

const CATEGORIES = ['Pantai', 'Gunung', 'Kota', 'Budaya'];

const DESTINATIONS = [
    {
        id: 'paris', category: 'Kota', title: 'Paris Signature Tour', city: 'Paris', country: 'Prancis', region: 'Eropa',
        nights: 4, price: 35000000,
        img: 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34',
        short: 'Private chauffeur dan makan malam di restoran bintang Michelin.',
        desc: 'Nikmati kemewahan kota Paris dengan akomodasi hotel bintang 5 dan pemandu privat.',
        includes: ['Penjemputan VVIP di bandara', 'Hotel bintang 5 (4 malam)', 'Chauffeur pribadi selama tur', 'Makan malam restoran bintang Michelin', 'Kurator pribadi di Louvre', 'Pendamping berbahasa Indonesia'],
        days: [
            { t: 'Kedatangan', d: 'Penjemputan VVIP di Bandara CDG dan check-in di Hotel Le Bristol.' },
            { t: 'Menara Eiffel dan makan malam Michelin', d: 'Tur privat Menara Eiffel, ditutup makan malam di restoran bintang Michelin.' },
            { t: 'Louvre bersama kurator', d: 'Eksplorasi Museum Louvre dengan kurator pribadi, tanpa antre.' },
            { t: 'Belanja eksklusif', d: 'Belanja di Champs-Élysées dengan personal shopper.' },
            { t: 'Kepulangan', d: 'Transfer kembali ke bandara.' }
        ]
    },
    {
        id: 'dubai', category: 'Kota', title: 'Dubai Royal Escapade', city: 'Dubai', country: 'Uni Emirat Arab', region: 'Timur Tengah',
        nights: 3, price: 28500000,
        img: 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c',
        short: 'Desert safari mewah dan akses VIP ke Burj Khalifa.',
        desc: 'Rasakan kemegahan kota gurun modern dengan fasilitas kelas VVIP terbaik dunia.',
        includes: ['Limousine bandara-hotel', 'Armani Hotel Burj Khalifa (3 malam)', 'Akses VIP puncak Burj Khalifa', 'Desert safari mobil privat', 'BBQ eksklusif di gurun', 'Pendamping berbahasa Indonesia'],
        days: [
            { t: 'Kedatangan', d: 'Tiba di Dubai, dijemput limousine menuju Armani Hotel Burj Khalifa.' },
            { t: 'Puncak Burj Khalifa dan Dubai Mall', d: 'Akses VIP ke puncak Burj Khalifa, dilanjutkan tur belanja Dubai Mall.' },
            { t: 'Desert safari', d: 'Luxury desert safari dengan mobil privat dan makan malam BBQ eksklusif.' },
            { t: 'Waktu bebas dan kepulangan', d: 'Waktu santai di hotel sebelum transfer ke bandara.' }
        ]
    },
    {
        id: 'kyoto', category: 'Budaya', title: 'Kyoto Quiet Seasons', city: 'Kyoto', country: 'Jepang', region: 'Asia',
        nights: 5, price: 31000000,
        img: 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e',
        short: 'Ryokan privat, upacara teh, dan kuil sebelum pengunjung datang.',
        desc: 'Kyoto di ritme yang tenang: kuil pagi hari, ryokan tradisional, dan jamuan kaiseki.',
        includes: ['Penjemputan bandara Kansai', 'Ryokan privat dengan onsen (5 malam)', 'Upacara teh bersama master', 'Akses kuil di luar jam kunjungan', 'Makan malam kaiseki', 'Pemandu berbahasa Indonesia atau Inggris'],
        days: [
            { t: 'Kedatangan', d: 'Dijemput di Bandara Kansai, perjalanan privat ke ryokan di Gion.' },
            { t: 'Kuil di pagi hari', d: 'Fushimi Inari saat matahari terbit, sebelum jam kunjungan umum.' },
            { t: 'Upacara teh dan kaiseki', d: 'Belajar upacara teh dengan master, malamnya jamuan kaiseki.' },
            { t: 'Arashiyama', d: 'Hutan bambu dan perjalanan perahu privat di sungai Hozu.' },
            { t: 'Hari bebas', d: 'Waktu bebas, atau kelas kuliner dan workshop kerajinan atas permintaan.' },
            { t: 'Kepulangan', d: 'Transfer privat kembali ke bandara.' }
        ]
    },
    {
        id: 'santorini', category: 'Pantai', title: 'Santorini Aegean Retreat', city: 'Santorini', country: 'Yunani', region: 'Eropa',
        nights: 4, price: 33500000,
        img: 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff',
        short: 'Villa tebing dengan kolam pribadi dan pelayaran saat senja.',
        desc: 'Empat malam di villa tebing menghadap kaldera, dengan pelayaran privat dan wine tasting.',
        includes: ['Penjemputan bandara dan pelabuhan', 'Villa tebing kolam pribadi (4 malam)', 'Pelayaran privat saat senja', 'Wine tasting di kebun anggur', 'Sarapan di villa setiap hari', 'Concierge lokal'],
        days: [
            { t: 'Kedatangan', d: 'Dijemput di bandara, check-in di villa kaldera di Oia.' },
            { t: 'Menyusuri Oia', d: 'Jalan santai bersama pemandu lokal, makan malam di restoran tebing.' },
            { t: 'Pelayaran senja', d: 'Berlayar mengelilingi kaldera, berenang di sumber air panas, makan malam di kapal.' },
            { t: 'Wine tasting', d: 'Kunjungan ke kebun anggur vulkanik dengan sesi cicip bersama pembuatnya.' },
            { t: 'Kepulangan', d: 'Sarapan terakhir di villa, lalu transfer ke bandara.' }
        ]
    },
    {
        id: 'maldives', category: 'Pantai', title: 'Maldives Overwater Escape', city: 'Atol Malé', country: 'Maladewa', region: 'Pulau',
        nights: 4, price: 42000000,
        img: 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8',
        short: 'Villa di atas laut, transfer seaplane, dan makan malam di pantai.',
        desc: 'Villa di atas laguna, transfer dengan seaplane, dan hari-hari yang diatur sepenuhnya mengikuti ritme Anda.',
        includes: ['Transfer seaplane pulang-pergi', 'Overwater villa (4 malam)', 'Makan malam pribadi di pantai', 'Sesi snorkeling bersama ahli kelautan', 'Spa pasangan 90 menit', 'Butler pribadi'],
        days: [
            { t: 'Kedatangan', d: 'Dijemput di Malé, terbang dengan seaplane ke resor dan disambut butler pribadi.' },
            { t: 'Laguna', d: 'Snorkeling di karang bersama ahli kelautan, sore bebas di villa.' },
            { t: 'Spa dan makan malam pantai', d: 'Spa 90 menit, malamnya makan malam pribadi di tepi pantai.' },
            { t: 'Hari bebas', d: 'Kayak, paddle board, atau tidak melakukan apa pun.' },
            { t: 'Kepulangan', d: 'Transfer seaplane kembali ke Malé.' }
        ]
    },
    {
        id: 'swiss', category: 'Gunung', title: 'Swiss Alpine Grand Tour', city: 'Zürich dan Alpen', country: 'Swiss', region: 'Eropa',
        nights: 5, price: 38000000,
        img: 'https://images.unsplash.com/photo-1530122037265-a5f1f91d3b99',
        short: 'Kereta panorama kelas satu dan chalet pribadi di pegunungan.',
        desc: 'Lima malam menyusuri Alpen dengan kereta panorama kelas satu dan chalet pribadi.',
        includes: ['Penjemputan bandara Zürich', 'Hotel dan chalet pegunungan (5 malam)', 'Kereta panorama kelas satu', 'Pemandu ski atau hiking pribadi', 'Makan malam fondue di chalet', 'Pendamping berbahasa Indonesia'],
        days: [
            { t: 'Kedatangan', d: 'Dijemput di Zürich, makan malam di tepi danau.' },
            { t: 'Kereta panorama', d: 'Perjalanan Glacier Express kelas satu menuju Zermatt.' },
            { t: 'Matterhorn', d: 'Naik kereta gunung ke Gornergrat, makan siang dengan pemandangan Matterhorn.' },
            { t: 'Hiking atau ski', d: 'Hari aktif bersama pemandu pribadi, sesuai musim dan kemampuan.' },
            { t: 'Fondue di chalet', d: 'Makan malam fondue di chalet pribadi.' },
            { t: 'Kepulangan', d: 'Transfer kembali ke Zürich.' }
        ]
    },

    {
        id: 'bali', category: 'Budaya', title: 'Bali Ubud Heritage', city: 'Ubud dan Uluwatu', country: 'Indonesia', region: 'Asia',
        nights: 4, price: 18500000,
        img: 'https://images.unsplash.com/photo-1537996194471-e657df975ab4',
        short: 'Villa di tengah sawah, upacara adat, dan senja di tebing Uluwatu.',
        desc: 'Bali dari sisi yang tenang: villa pribadi di Ubud, belajar budaya langsung dari pengrajin, dan senja di tebing selatan.',
        includes: ['Penjemputan Bandara Ngurah Rai', 'Villa pribadi dengan kolam (4 malam)', 'Kelas tari dan gamelan', 'Kunjungan desa pengrajin', 'Makan malam di tebing Uluwatu', 'Sopir dan pemandu pribadi'],
        days: [
            { t: 'Kedatangan', d: 'Dijemput di bandara, perjalanan ke villa di Ubud.' },
            { t: 'Sawah dan pura', d: 'Jalan pagi di Tegallalang, lalu mengikuti upacara melukat di pura air suci.' },
            { t: 'Desa pengrajin', d: 'Mengunjungi pengrajin perak, kayu, dan tenun, dilanjutkan kelas tari dan gamelan.' },
            { t: 'Uluwatu', d: 'Pindah ke selatan, pertunjukan kecak saat senja, makan malam di tebing.' },
            { t: 'Kepulangan', d: 'Sarapan santai dan transfer ke bandara.' }
        ]
    },
    {
        id: 'rajaampat', category: 'Pantai', title: 'Raja Ampat Dive Retreat', city: 'Raja Ampat', country: 'Indonesia', region: 'Asia',
        nights: 5, price: 39000000,
        img: 'https://images.unsplash.com/photo-1516690561799-46d8f74f9abf',
        short: 'Resor tepi laut, selam di terumbu karang, dan pelayaran antar pulau.',
        desc: 'Lima malam di salah satu perairan paling kaya hayati di dunia, dengan pemandu selam bersertifikat dan kapal pribadi.',
        includes: ['Penerbangan Sorong dan transfer kapal', 'Resor tepi laut (5 malam)', 'Kapal pribadi antar pulau', 'Pemandu selam dan snorkeling', 'Semua makan dari dapur resor', 'Peralatan selam'],
        days: [
            { t: 'Kedatangan', d: 'Dijemput di Sorong, perjalanan kapal ke resor.' },
            { t: 'Terumbu karang', d: 'Dua sesi selam atau snorkeling di titik terumbu terdekat.' },
            { t: 'Pianemo', d: 'Mendaki ke titik pandang gugusan karst, lalu berenang di laguna.' },
            { t: 'Hari bebas', d: 'Pilih selam lanjutan, kayak, atau bersantai di pantai pribadi.' },
            { t: 'Kampung pesisir', d: 'Mengunjungi kampung nelayan dan makan siang bersama warga.' },
            { t: 'Kepulangan', d: 'Kapal ke Sorong dan terbang pulang.' }
        ]
    },
    {
        id: 'bhutan', category: 'Gunung', title: 'Bhutan Himalayan Sanctuary', city: 'Paro dan Thimphu', country: 'Bhutan', region: 'Asia',
        nights: 5, price: 36500000,
        img: 'https://images.unsplash.com/photo-1553856622-d1b352e9a211',
        short: 'Biara di tebing, hiking Himalaya, dan penginapan butik di lembah.',
        desc: 'Perjalanan pelan di kerajaan pegunungan: biara, lembah, dan hiking dengan pemandu lokal.',
        includes: ['Penjemputan Bandara Paro', 'Hotel butik lembah (5 malam)', 'Pemandu dan sopir pribadi', 'Visa dan biaya pembangunan berkelanjutan', 'Hiking ke biara tebing', 'Makan malam masakan Bhutan'],
        days: [
            { t: 'Kedatangan', d: 'Mendarat di Paro, perjalanan santai ke penginapan untuk beradaptasi dengan ketinggian.' },
            { t: 'Biara di tebing', d: 'Mendaki ke biara yang bertengger di tebing, dengan istirahat di kedai teh.' },
            { t: 'Thimphu', d: 'Menuju ibu kota, mengunjungi dzong, pasar, dan studio pengrajin.' },
            { t: 'Lembah Punakha', d: 'Menyeberangi Dochula Pass dan berjalan di lembah sawah.' },
            { t: 'Memanah dan makan malam', d: 'Mencoba memanah, olahraga nasional Bhutan, lalu makan malam bersama keluarga setempat.' },
            { t: 'Kepulangan', d: 'Transfer ke bandara Paro.' }
        ]
    },
    {
        id: 'queenstown', category: 'Gunung', title: 'Queenstown Alpine Escape', city: 'Queenstown', country: 'Selandia Baru', region: 'Oseania',
        nights: 5, price: 41000000,
        img: 'https://images.unsplash.com/photo-1507699622108-4be3abd695ad',
        short: 'Lodge tepi danau, helikopter ke pegunungan, dan kebun anggur Central Otago.',
        desc: 'Lodge di tepi danau Wakatipu, penerbangan helikopter menembus Alpen Selatan, dan hari santai di kebun anggur.',
        includes: ['Penjemputan Bandara Queenstown', 'Lodge tepi danau (5 malam)', 'Penerbangan helikopter pemandangan', 'Wine tasting Central Otago', 'Pelayaran Milford Sound', 'Pemandu berbahasa Inggris'],
        days: [
            { t: 'Kedatangan', d: 'Dijemput di bandara, check-in di lodge dan makan malam di tepi danau.' },
            { t: 'Helikopter', d: 'Terbang di atas Alpen Selatan dengan pendaratan di salju.' },
            { t: 'Milford Sound', d: 'Perjalanan sehari ke fjord, pelayaran siang dengan makan siang di kapal.' },
            { t: 'Central Otago', d: 'Wine tasting di kebun anggur dataran tinggi, makan siang bersama pembuat anggur.' },
            { t: 'Hari bebas', d: 'Hiking ringan, spa, atau tidak melakukan apa pun.' },
            { t: 'Kepulangan', d: 'Sarapan terakhir dan transfer ke bandara.' }
        ]
    },
    {
        id: 'newyork', category: 'Kota', title: 'New York After Hours', city: 'New York', country: 'Amerika Serikat', region: 'Amerika',
        nights: 4, price: 37000000,
        img: 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9',
        short: 'Hotel di Manhattan, kursi teater Broadway, dan tur galeri seni privat.',
        desc: 'Empat malam di Manhattan dengan jadwal malam yang padat: teater, jazz, dan restoran yang sulit dipesan.',
        includes: ['Penjemputan bandara JFK', 'Hotel bintang 5 Manhattan (4 malam)', 'Tiket Broadway kursi terbaik', 'Tur galeri seni privat', 'Reservasi restoran pilihan', 'Mobil dengan sopir'],
        days: [
            { t: 'Kedatangan', d: 'Dijemput di JFK, makan malam santai di dekat hotel.' },
            { t: 'Seni dan Central Park', d: 'Tur galeri dan museum bersama kurator, sore di Central Park.' },
            { t: 'Broadway', d: 'Makan malam sebelum pertunjukan, lalu menonton dari kursi terbaik.' },
            { t: 'Brooklyn dan jazz', d: 'Jalan di Brooklyn Bridge, makan malam, dan malam jazz di klub kecil.' },
            { t: 'Kepulangan', d: 'Sarapan dan transfer ke bandara.' }
        ]
    },
    {
        id: 'rome', category: 'Budaya', title: 'Rome Eternal Journey', city: 'Roma', country: 'Italia', region: 'Eropa',
        nights: 4, price: 33000000,
        img: 'https://images.unsplash.com/photo-1552832230-c0197dd311b5',
        short: 'Kolosseum tanpa antre, Vatikan pagi hari, dan kelas pasta bersama koki.',
        desc: 'Roma lewat ahli sejarahnya: situs kuno di luar jam ramai, museum bersama kurator, dan makan di trattoria lokal.',
        includes: ['Penjemputan Bandara Fiumicino', 'Hotel pusat kota (4 malam)', 'Akses Kolosseum dan Forum tanpa antre', 'Vatikan sebelum jam buka umum', 'Kelas pasta bersama koki', 'Pemandu sejarah pribadi'],
        days: [
            { t: 'Kedatangan', d: 'Dijemput di bandara, makan malam di trattoria Trastevere.' },
            { t: 'Roma kuno', d: 'Kolosseum dan Forum Romawi bersama pemandu sejarah.' },
            { t: 'Vatikan', d: 'Museum Vatikan dan Kapel Sistina sebelum jam buka umum.' },
            { t: 'Kelas pasta', d: 'Belajar membuat pasta segar di dapur koki, makan siang hasil karya sendiri.' },
            { t: 'Kepulangan', d: 'Sarapan dan transfer ke bandara.' }
        ]
    },
    {
        id: 'tokyo', category: 'Kota', title: 'Tokyo After Dark', city: 'Tokyo', country: 'Jepang', region: 'Asia',
        nights: 4, price: 34500000,
        img: 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf',
        short: 'Hotel di Shinjuku, sushi omakase, dan malam di izakaya tersembunyi.',
        desc: 'Tokyo dari sisi malamnya: omakase, bar kecil, dan lampu kota dari ketinggian.',
        includes: ['Penjemputan Bandara Haneda', 'Hotel bintang 5 (4 malam)', 'Makan malam sushi omakase', 'Tur izakaya dan bar bersama pemandu', 'Tiket dek observasi', 'Pemandu berbahasa Indonesia atau Inggris'],
        days: [
            { t: 'Kedatangan', d: 'Dijemput di Haneda, makan malam ringan di sekitar hotel.' },
            { t: 'Harajuku dan Shibuya', d: 'Jalan siang bersama pemandu, naik ke dek observasi saat senja.' },
            { t: 'Omakase', d: 'Mengunjungi pasar pagi, malamnya makan malam omakase.' },
            { t: 'Izakaya', d: 'Menjelajah gang-gang izakaya kecil di Shinjuku bersama pemandu.' },
            { t: 'Kepulangan', d: 'Transfer ke bandara.' }
        ]
    },
    {
        id: 'amalfi', category: 'Pantai', title: 'Amalfi Coast Riviera', city: 'Pantai Amalfi', country: 'Italia', region: 'Eropa',
        nights: 4, price: 36000000,
        img: 'https://images.unsplash.com/photo-1533104816931-20fa691ff6ca',
        short: 'Hotel tebing di Positano, perahu privat, dan makan siang di teluk.',
        desc: 'Menyusuri garis pantai Amalfi dengan perahu pribadi, hotel di tebing, dan makan siang seafood di desa nelayan.',
        includes: ['Transfer pribadi dari Napoli', 'Hotel tebing di Positano (4 malam)', 'Perahu pribadi seharian', 'Makan siang seafood di teluk', 'Kelas limoncello dan kebun lemon', 'Concierge lokal'],
        days: [
            { t: 'Kedatangan', d: 'Dijemput di Napoli, menyusuri jalan pesisir menuju Positano.' },
            { t: 'Perahu pribadi', d: 'Berlayar ke Capri dan gua biru, makan siang di teluk.' },
            { t: 'Ravello', d: 'Mengunjungi kebun di Ravello, lalu kelas limoncello di kebun lemon.' },
            { t: 'Hari bebas', d: 'Bersantai di pantai atau menjelajah butik Positano.' },
            { t: 'Kepulangan', d: 'Sarapan di teras dan transfer ke Napoli.' }
        ]
    },
    {
        id: 'marrakech', category: 'Budaya', title: 'Marrakech Medina Retreat', city: 'Marrakech', country: 'Maroko', region: 'Afrika',
        nights: 4, price: 26500000,
        img: 'https://images.unsplash.com/photo-1597212618440-806262de4f6b',
        short: 'Riad pribadi, pasar rempah, dan malam di gurun Agafay.',
        desc: 'Menginap di riad di jantung medina, menyusuri pasar bersama pemandu lokal, dan malam di kemah gurun.',
        includes: ['Penjemputan Bandara Menara', 'Riad pribadi dengan sarapan (4 malam)', 'Tur medina dan pasar rempah', 'Kelas tajine bersama koki', 'Malam di kemah gurun Agafay', 'Pemandu lokal'],
        days: [
            { t: 'Kedatangan', d: 'Dijemput di bandara, disambut teh mint di riad.' },
            { t: 'Medina', d: 'Menyusuri pasar rempah, kulit, dan keramik bersama pemandu.' },
            { t: 'Kelas tajine', d: 'Belanja bahan di pasar dan memasak tajine bersama koki.' },
            { t: 'Gurun Agafay', d: 'Naik ke gurun, menikmati senja dan makan malam di kemah.' },
            { t: 'Kepulangan', d: 'Kembali ke kota dan transfer ke bandara.' }
        ]
    }
];

const ADDONS = [
    { id: 'photo', label: 'Fotografer pribadi', note: 'Sesi 3 jam, 50 foto terpilih', price: 3000000, per: 'grup' },
    { id: 'spa', label: 'Paket spa dan wellness', note: 'Satu sesi per tamu', price: 2500000, per: 'orang' },
    { id: 'insurance', label: 'Asuransi perjalanan premium', note: 'Termasuk medis dan pembatalan', price: 750000, per: 'orang' }
];
