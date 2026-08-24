<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin('../login.php');
$__user = current_user();

// daftar percakapan: semua user yang pernah mengirim pesan, + jumlah pesan belum dibaca
$conversations = $pdo->query("
    SELECT u.id, u.full_name, u.email,
        (SELECT COUNT(*) FROM messages m WHERE m.user_id = u.id AND m.sender_role = 'user' AND m.is_read = 0) AS unread,
        (SELECT MAX(created_at) FROM messages m WHERE m.user_id = u.id) AS last_at
    FROM users u
    WHERE u.id IN (SELECT DISTINCT user_id FROM messages)
    ORDER BY last_at DESC
")->fetchAll();

$active_user_id = (int) ($_GET['user_id'] ?? 0);
$active_user = null;
$messages = [];
if ($active_user_id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$active_user_id]);
    $active_user = $stmt->fetch();

    if ($active_user) {
        $stmt = $pdo->prepare('SELECT * FROM messages WHERE user_id = ? ORDER BY id ASC');
        $stmt->execute([$active_user_id]);
        $messages = $stmt->fetchAll();

        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE user_id = ? AND sender_role = 'user' AND is_read = 0")
            ->execute([$active_user_id]);
    }
}
$last_id = !empty($messages) ? end($messages)['id'] : 0;
?>
<?php $__admin_page = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesan - Nusantara Beans</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-theme.css?v=<?php echo @filemtime(__DIR__ . '/../assets/css/admin-theme.css') ?: time(); ?>">
    <link rel="stylesheet" href="../assets/css/admin-extra.css?v=<?php echo @filemtime(__DIR__ . '/../assets/css/admin-extra.css') ?: time(); ?>">
    
    <!-- TAMBAHAN: Mengunci Header & Footer, serta styling scroll -->
    <style>
        .chat-main-container {
            height: 75vh; /* Mengunci tinggi box agar tidak memanjang */
            max-height: 800px;
            display: flex;
            flex-direction: column;
        }
        .chat-header, .chat-footer, .chat-attach-preview {
            flex-shrink: 0; /* Mencegah header & footer menyusut */
            position: relative;
            z-index: 10;
        }
        .chat-body {
            flex: 1;
            overflow-y: auto; /* Membuat hanya area pesan yang bisa di-scroll */
        }
        /* Opsional: Membuat scrollbar lebih rapi (khusus browser webkit) */
        .chat-body::-webkit-scrollbar {
            width: 6px;
        }
        .chat-body::-webkit-scrollbar-thumb {
            background-color: #C5A059;
            border-radius: 10px;
        }
    </style>
</head>
<body>

    <script>
        // FIX: tombol kirim kepotong keyboard/toolbar di mobile — ukur tinggi viewport yang
        // benar-benar terlihat (bukan vh statis) dan simpan sebagai custom property CSS.
        function updateChatViewportHeight() {
            const vv = window.visualViewport;
            const h = vv ? vv.height : window.innerHeight;
            document.documentElement.style.setProperty('--chat-vvh', h + 'px');
        }
        updateChatViewportHeight();
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', updateChatViewportHeight);
            window.visualViewport.addEventListener('scroll', updateChatViewportHeight);
        } else {
            window.addEventListener('resize', updateChatViewportHeight);
        }
    </script>

    <div class="admin-topbar">
        <div class="navbar-brand">Nusantara<span>Beans</span> <span class="topbar-suffix">Panel Admin</span></div>
        <button type="button" class="admin-burger" id="adminBurger" aria-label="Buka menu"><i class="fas fa-bars"></i></button>
        <div class="admin-menu" id="adminMenu">
            <a href="index.php" class="<?php echo $__admin_page === 'index.php' ? 'active-link' : ''; ?>">Dashboard</a>
            <a href="pesan.php" class="<?php echo $__admin_page === 'pesan.php' ? 'active-link' : ''; ?>">Pesan</a>
            <a href="kelola-pesanan.php" class="<?php echo $__admin_page === 'kelola-pesanan.php' ? 'active-link' : ''; ?>">Kelola Pesanan</a>
            <a href="kelola-user.php" class="<?php echo $__admin_page === 'kelola-user.php' ? 'active-link' : ''; ?>">Kelola User</a>
            <?php if (is_admin_master()): ?>
                <a href="kelola-admin.php" class="<?php echo $__admin_page === 'kelola-admin.php' ? 'active-link' : ''; ?>">Kelola Admin</a>
            <?php endif; ?>
            <a href="../index.php"><i class="fas fa-arrow-left" style="margin-right:5px;"></i>Kembali ke Situs</a>
            <a href="../logout.php" class="link-logout"><i class="fas fa-sign-out-alt" style="margin-right:5px;"></i>Keluar</a>
        </div>
    </div>
    
    <h2 class="page-header-title" style="text-align:center; margin-top:20px;">Kelola <span>Pesan</span></h2>
    
    <div class="pesan-layout <?php echo $active_user ? 'has-active' : ''; ?>">
        <div class="convo-list">
            <?php if (empty($conversations)): ?>
                <p style="padding:14px; color:#888;">Belum ada percakapan masuk.</p>
            <?php endif; ?>
            <?php foreach ($conversations as $c): ?>
            <a href="pesan.php?user_id=<?php echo (int)$c['id']; ?>" class="convo-item <?php echo $active_user_id === (int)$c['id'] ? 'active' : ''; ?>">
                <span class="convo-name"><?php echo htmlspecialchars($c['full_name']); ?></span>
                <?php if ($c['unread'] > 0): ?><span class="convo-unread"><?php echo (int)$c['unread']; ?></span><?php endif; ?>
                <br><small style="color:#888;"><?php echo htmlspecialchars($c['email']); ?></small>
            </a>
            <?php endforeach; ?>
        </div>

        <div style="flex:1;">
            <?php if (!$active_user): ?>
                <div class="chat-main-container">
                    <div class="empty-convo">Pilih salah satu percakapan di sebelah kiri untuk mulai membalas.</div>
                </div>
            <?php else: ?>
            <div class="chat-main-container">
                <div class="chat-header">
                    <a href="pesan.php" class="chat-back-btn" title="Kembali ke daftar"><i class="fas fa-arrow-left"></i></a>
                    <div class="chat-admin-profile">
                        <div class="admin-avatar-wrapper">
                            <?php echo strtoupper(substr($active_user['full_name'], 0, 2)); ?>
                            <div class="status-dot"></div>
                        </div>
                        <div class="admin-info">
                            <h3><?php echo htmlspecialchars($active_user['full_name']); ?></h3>
                            <span><?php echo htmlspecialchars($active_user['email']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="chat-body" id="chatBody">
                    <?php foreach ($messages as $m): ?>
                    <div class="message <?php echo $m['sender_role'] === 'admin' ? 'user' : 'admin'; ?>">
                        <?php if (!empty($m['image_path'])): ?>
                            <a href="../pesan-image.php?path=<?php echo urlencode($m['image_path']); ?>" target="_blank" rel="noopener">
                                <img src="../pesan-image.php?path=<?php echo urlencode($m['image_path']); ?>" class="message-image" alt="Foto">
                            </a>
                        <?php endif; ?>
                        <?php if (trim((string) $m['message']) !== ''): ?>
                            <?php echo nl2br(htmlspecialchars($m['message'], ENT_QUOTES, 'UTF-8')); ?>
                        <?php endif; ?>
                        <?php if (!empty($m['sender_location'])): ?>
                            <span class="message-location"><i class="fas fa-location-dot"></i><?php echo htmlspecialchars($m['sender_location']); ?></span>
                        <?php endif; ?>
                        <span class="message-time"><?php echo date('H:i', strtotime($m['created_at'])); ?></span>
                    </div>
                    <?php endforeach; ?>
                    <div class="typing-indicator" id="typingIndicator"><span></span><span></span><span></span></div>
                </div>

                <div class="chat-attach-preview" id="chatAttachPreview" style="display:none;">
                    <img id="chatAttachPreviewImg" src="" alt="Preview">
                    <span id="chatAttachPreviewName"></span>
                    <button type="button" id="chatAttachCancel" title="Batal"><i class="fas fa-times"></i></button>
                </div>

                <form class="chat-footer" id="chatForm">
                    <input type="file" id="chatImageInput" accept="image/*" style="display:none;">
                    <button type="button" class="chat-input-tool" id="chatAttachBtn" title="Lampirkan Foto"><i class="fas fa-paperclip"></i></button>
                    <input type="text" class="chat-input" id="chatInput" placeholder="Ketik balasan Anda di sini..." autocomplete="off">
                    <button type="submit" class="chat-send-btn" title="Kirim Pesan"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($active_user): ?>
    <script>
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const chatBody = document.getElementById('chatBody');
        const typingIndicator = document.getElementById('typingIndicator');
        const targetUserId = <?php echo (int) $active_user_id; ?>;
        let lastId = <?php echo (int) $last_id; ?>;

        const chatAttachBtn = document.getElementById('chatAttachBtn');
        const chatImageInput = document.getElementById('chatImageInput');
        const chatAttachPreview = document.getElementById('chatAttachPreview');
        const chatAttachPreviewImg = document.getElementById('chatAttachPreviewImg');
        const chatAttachPreviewName = document.getElementById('chatAttachPreviewName');
        const chatAttachCancel = document.getElementById('chatAttachCancel');
        const MAX_IMAGE_SIZE = 1024 * 1024; // 1MB
        let selectedImageFile = null;

        function getCurrentTime() {
            const now = new Date();
            let h = now.getHours(); let m = now.getMinutes();
            h = h < 10 ? '0' + h : h; m = m < 10 ? '0' + m : m;
            return `${h}:${m}`;
        }
        
        // TAMBAHAN: Modifikasi fungsi scroll dengan efek mulus (smooth behavior)
        function scrollToBottom() { 
            chatBody.scrollTo({
                top: chatBody.scrollHeight,
                behavior: 'smooth'
            }); 
        }
        
        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return text.replace(/[&<>"']/g, (m) => map[m]);
        }
        // ================= DETEKSI DAERAH PENGIRIM (OTOMATIS, TANPA POPUP IZIN) =================
        let senderLocationText = sessionStorage.getItem('nb_chat_location') || '';
        function setSenderLocation(text) {
            if (!text) return;
            senderLocationText = text;
            sessionStorage.setItem('nb_chat_location', text);
        }
        function detectLocationByIP() {
            fetch('https://ipwho.is/')
                .then((r) => r.json())
                .then((data) => {
                    if (data && data.success !== false) {
                        const lokasi = [data.city, data.region].filter(Boolean).join(', ');
                        setSenderLocation(lokasi);
                    }
                })
                .catch(() => {});
        }
        function reverseGeocode(lat, lon) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}&zoom=10&addressdetails=1`)
                .then((r) => r.json())
                .then((data) => {
                    const addr = data.address || {};
                    const kota = addr.city || addr.town || addr.municipality || addr.county || addr.village || '';
                    const provinsi = addr.state || '';
                    const lokasi = [kota, provinsi].filter(Boolean).join(', ');
                    setSenderLocation(lokasi);
                })
                .catch(() => {});
        }
        if (!senderLocationText) {
            detectLocationByIP();
            if (navigator.geolocation && navigator.permissions) {
                navigator.permissions.query({ name: 'geolocation' }).then((status) => {
                    if (status.state === 'granted') {
                        navigator.geolocation.getCurrentPosition(
                            (pos) => reverseGeocode(pos.coords.latitude, pos.coords.longitude),
                            () => {},
                            { enableHighAccuracy: false, timeout: 8000, maximumAge: 600000 }
                        );
                    }
                }).catch(() => {});
            }
        }

        function appendMessage(role, text, time, imageUrl, location) {
            const div = document.createElement('div');
            div.className = 'message ' + role;
            let html = '';
            if (imageUrl) {
                html += '<a href="' + imageUrl + '" target="_blank" rel="noopener"><img src="' + imageUrl + '" class="message-image" alt="Foto"></a>';
            }
            if (text) {
                html += escapeHtml(text).replace(/\n/g, '<br>');
            }
            if (location) {
                html += '<span class="message-location"><i class="fas fa-location-dot"></i>' + escapeHtml(location) + '</span>';
            }
            html += '<span class="message-time">' + time + '</span>';
            div.innerHTML = html;
            chatBody.insertBefore(div, typingIndicator);
            scrollToBottom();
        }

        chatAttachBtn.addEventListener('click', () => chatImageInput.click());
        chatImageInput.addEventListener('change', () => {
            const file = chatImageInput.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                alert('File yang dilampirkan harus berupa foto.');
                chatImageInput.value = '';
                return;
            }
            if (file.size > MAX_IMAGE_SIZE) {
                alert('Ukuran foto maksimal 1MB.');
                chatImageInput.value = '';
                return;
            }
            selectedImageFile = file;
            chatAttachPreviewName.textContent = file.name;
            const reader = new FileReader();
            reader.onload = (e) => { chatAttachPreviewImg.src = e.target.result; };
            reader.readAsDataURL(file);
            chatAttachPreview.style.display = 'flex';
        });
        chatAttachCancel.addEventListener('click', () => {
            selectedImageFile = null;
            chatImageInput.value = '';
            chatAttachPreview.style.display = 'none';
        });

        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const messageText = chatInput.value.trim();
            if (!messageText && !selectedImageFile) return;

            const formData = new FormData();
            formData.append('message', messageText);
            formData.append('user_id', targetUserId);
            if (senderLocationText) formData.append('location', senderLocationText);
            if (selectedImageFile) formData.append('image', selectedImageFile);

            chatInput.value = '';
            chatImageInput.value = '';
            chatAttachPreview.style.display = 'none';
            selectedImageFile = null;

            fetch('../messages-send.php', {
                method: 'POST',
                body: formData
            }).then((r) => r.json()).then((data) => {
                if (data.status === 'ok') {
                    const imageUrl = data.image_path ? '../pesan-image.php?path=' + encodeURIComponent(data.image_path) : null;
                    appendMessage('user', messageText, getCurrentTime(), imageUrl, data.sender_location);
                    lastId = Math.max(lastId, data.id);
                } else {
                    alert(data.message || 'Gagal mengirim pesan.');
                }
            }).catch(() => alert('Terjadi kesalahan jaringan.'));
        });

        function pollMessages() {
            fetch('../messages-fetch.php?after_id=' + lastId + '&user_id=' + targetUserId)
                .then((r) => r.json())
                .then((data) => {
                    if (data.status !== 'ok' || !data.messages) return;
                    data.messages.forEach((m) => {
                        if (m.sender_role === 'user') {
                            const imageUrl = m.image_path ? '../pesan-image.php?path=' + encodeURIComponent(m.image_path) : null;
                            appendMessage('admin', m.message, m.created_at.substring(11, 16), imageUrl, m.sender_location);
                        }
                        lastId = Math.max(lastId, parseInt(m.id));
                    });
                }).catch(() => {});
        }
        setInterval(pollMessages, 3000);
        
        // Memastikan obrolan menggulung ke bawah secara langsung saat halaman pertama kali dimuat
        chatBody.scrollTop = chatBody.scrollHeight;
    </script>
    <?php endif; ?>
    <script>
        const adminBurger = document.getElementById('adminBurger');
        const adminMenu = document.getElementById('adminMenu');
        if (adminBurger && adminMenu) {
            adminBurger.addEventListener('click', function (e) {
                adminMenu.classList.toggle('active');
                e.stopPropagation();
            });
            document.addEventListener('click', function (e) {
                if (!adminMenu.contains(e.target) && !adminBurger.contains(e.target)) {
                    adminMenu.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>