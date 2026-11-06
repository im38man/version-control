<?php
require_once __DIR__ . '/includes/admin_auth.php';

// Daftar percakapan: satu baris per user, dengan pesan terakhir & jumlah belum dibaca
$threads = mysqli_query($koneksi, "
    SELECT u.id as user_id, u.name, u.email,
        (SELECT message FROM pesan_messages WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as last_message,
        (SELECT created_at FROM pesan_messages WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as last_time,
        (SELECT COUNT(*) FROM pesan_messages WHERE user_id = u.id AND sender='user' AND is_read=0) as unread
    FROM users u
    WHERE u.role = 'user' AND EXISTS (SELECT 1 FROM pesan_messages cm WHERE cm.user_id = u.id)
    ORDER BY last_time DESC
");

$selected_user_id = (int)($_GET['user_id'] ?? 0);
$selected_user = null;
$messages = [];

if ($selected_user_id) {
    $stmt = mysqli_prepare($koneksi, 'SELECT id, name, email FROM users WHERE id = ? AND role = "user"');
    mysqli_stmt_bind_param($stmt, 'i', $selected_user_id);
    mysqli_stmt_execute($stmt);
    $selected_user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($selected_user) {
        $stmt2 = mysqli_prepare($koneksi, 'SELECT id, sender, message, created_at FROM pesan_messages WHERE user_id = ? ORDER BY id ASC');
        mysqli_stmt_bind_param($stmt2, 'i', $selected_user_id);
        mysqli_stmt_execute($stmt2);
        $messages = mysqli_fetch_all(mysqli_stmt_get_result($stmt2), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt2);

        // Tandai pesan user sudah dibaca admin
        $mark = mysqli_prepare($koneksi, 'UPDATE pesan_messages SET is_read = 1 WHERE user_id = ? AND sender = "user"');
        mysqli_stmt_bind_param($mark, 'i', $selected_user_id);
        mysqli_stmt_execute($mark);
        mysqli_stmt_close($mark);
    }
}

$page_title = 'Pesan Pelanggan';
$active_menu = 'pesan';
require __DIR__ . '/includes/admin_header.php';
?>
<h1>Pesan Pelanggan</h1>
<p class="sub">Balas pesan dari pelanggan yang sudah login.</p>

<div style="display:flex;gap:20px;align-items:flex-start;">
    <div class="card" style="width:300px;flex-shrink:0;padding:0;max-height:600px;overflow-y:auto;">
        <?php if (mysqli_num_rows($threads) === 0): ?>
            <p style="padding:20px;color:#999;font-size:13px;">Belum ada percakapan masuk.</p>
        <?php endif; ?>
        <?php while ($t = mysqli_fetch_assoc($threads)): ?>
        <a href="pesan.php?user_id=<?= $t['user_id'] ?>" style="display:block;padding:16px 18px;text-decoration:none;color:#333;border-bottom:1px solid #f0ede6;<?= $selected_user_id === (int)$t['user_id'] ? 'background:#faf6ee;' : '' ?>">
            <div style="display:flex;justify-content:space-between;">
                <strong style="font-size:13px;"><?= h($t['name']) ?></strong>
                <?php if ($t['unread'] > 0): ?><span class="badge" style="background:#c5423b;color:#fff;"><?= $t['unread'] ?></span><?php endif; ?>
            </div>
            <div style="font-size:12px;color:#888;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= h(mb_strimwidth($t['last_message'] ?? '', 0, 40, '...')) ?></div>
        </a>
        <?php endwhile; ?>
    </div>

    <div class="card" style="flex:1;">
        <?php if (!$selected_user): ?>
            <p style="color:#999;text-align:center;padding:60px 0;">Pilih percakapan di sebelah kiri untuk mulai membalas.</p>
        <?php else: ?>
            <h3 style="font-family:'Playfair Display',serif;color:#1a2f27;margin-bottom:15px;">
                <?= h($selected_user['name']) ?> <small style="color:#999;font-weight:400;font-family:'Poppins',sans-serif;">(<?= h($selected_user['email']) ?>)</small>
            </h3>
            <div id="pesanBox" style="max-height:400px;overflow-y:auto;border:1px solid #f0ede6;border-radius:8px;padding:16px;margin-bottom:16px;">
                <?php foreach ($messages as $m): ?>
                    <div style="margin-bottom:12px;text-align:<?= $m['sender']==='admin' ? 'right' : 'left' ?>;">
                        <span style="display:inline-block;max-width:70%;padding:9px 14px;border-radius:12px;font-size:13px;background:<?= $m['sender']==='admin' ? '#1a2f27' : '#f0ede6' ?>;color:<?= $m['sender']==='admin' ? '#fff' : '#333' ?>;">
                            <?= nl2br(h($m['message'])) ?>
                        </span>
                        <div style="font-size:10px;color:#aaa;margin-top:2px;"><?= date('d M H:i', strtotime($m['created_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <form id="replyForm" style="display:flex;gap:10px;">
                <input type="text" id="replyInput" placeholder="Tulis balasan..." required style="flex:1;padding:11px 14px;border:1px solid #e5e0d8;border-radius:8px;font-size:14px;">
                <button type="submit" class="btn btn-view">Kirim</button>
            </form>
            <script>
            document.getElementById('replyForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const input = document.getElementById('replyInput');
                const text = input.value.trim();
                if (!text) return;
                fetch('pesan-reply.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'user_id=<?= (int)$selected_user_id ?>&message=' + encodeURIComponent(text)
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        const box = document.getElementById('pesanBox');
                        const div = document.createElement('div');
                        div.style.marginBottom = '12px';
                        div.style.textAlign = 'right';
                        div.innerHTML = '<span style="display:inline-block;max-width:70%;padding:9px 14px;border-radius:12px;font-size:13px;background:#1a2f27;color:#fff;">' + text.replace(/</g,'&lt;') + '</span><div style="font-size:10px;color:#aaa;margin-top:2px;">' + data.time + '</div>';
                        box.appendChild(div);
                        box.scrollTop = box.scrollHeight;
                        input.value = '';
                    }
                });
            });
            document.getElementById('pesanBox').scrollTop = document.getElementById('pesanBox').scrollHeight;
            </script>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
