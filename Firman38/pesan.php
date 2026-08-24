<?php
require __DIR__ . '/config/db.php';
requireLogin(); // chat cuma buat yang sudah login (user maupun admin)

// ============ KIRIM PESAN ============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $body = trim($_POST['message'] ?? '');

    if (isAdmin()) {
        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        if ($body !== '' && $targetUserId) {
            $stmt = $pdo->prepare('INSERT INTO messages (user_id, sender_role, message) VALUES (?, "admin", ?)');
            $stmt->execute([$targetUserId, $body]);
        }
        header('Location: pesan.php?user=' . $targetUserId);
        exit;
    } else {
        if ($body !== '') {
            $stmt = $pdo->prepare('INSERT INTO messages (user_id, sender_role, message) VALUES (?, "user", ?)');
            $stmt->execute([$_SESSION['user_id'], $body]);
        }
        header('Location: pesan.php');
        exit;
    }
}

// ============ AKSI ADMIN: tandai dibaca / hapus pesan kontak satu arah ============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['contact_read', 'contact_delete'], true)) {
    requireAdmin();
    $id = (int) ($_POST['id'] ?? 0);
    if ($_POST['action'] === 'contact_read' && $id) {
        $pdo->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$id]);
    } elseif ($_POST['action'] === 'contact_delete' && $id) {
        $pdo->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([$id]);
    }
    header('Location: pesan.php');
    exit;
}

include __DIR__ . '/includes/header.php';

// ============================================================
// TAMPILAN ADMIN
// ============================================================
if (isAdmin()):
    $openUserId = isset($_GET['user']) ? (int) $_GET['user'] : 0;

    if ($openUserId):
        // tandai pesan dari user ini sebagai sudah dibaca
        $pdo->prepare('UPDATE messages SET is_read = 1 WHERE user_id = ? AND sender_role = "user"')->execute([$openUserId]);

        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$openUserId]);
        $chatUser = $stmt->fetch();

        if (!$chatUser) {
            echo '<section class="p-5"><div class="container"><div class="alert alert-warning rounded-4">User tidak ditemukan.</div>
                  <a href="pesan.php" class="btn btn-dark rounded-4">Kembali</a></div></section>';
            include __DIR__ . '/includes/footer.php';
            exit;
        }

        $stmt = $pdo->prepare('SELECT * FROM messages WHERE user_id = ? ORDER BY id ASC');
        $stmt->execute([$openUserId]);
        $chatMessages = $stmt->fetchAll();
        $lastId = $chatMessages ? end($chatMessages)['id'] : 0;
        ?>

        <section class="p-4">
          <div class="container" style="max-width:700px;">
            <a href="pesan.php" class="text-decoration-none">&larr; Semua percakapan</a>
            <h4 class="mt-2 mb-3"><?= h($chatUser['name']) ?> <small class="text-dark-emphasis fs-6"><?= h($chatUser['email']) ?></small></h4>

            <div id="chat-box" class="p-3 rounded-4 bg-light mb-3" style="height:55vh; overflow-y:auto;" data-last-id="<?= (int) $lastId ?>" data-user-id="<?= (int) $openUserId ?>">
              <?php foreach ($chatMessages as $m): ?>
                <?php $mine = $m['sender_role'] === 'admin'; ?>
                <div class="d-flex <?= $mine ? 'justify-content-end' : 'justify-content-start' ?> mb-2">
                  <div class="p-2 px-3 rounded-4 <?= $mine ? 'bg-dark text-white' : 'bg-white border' ?>" style="max-width:75%;">
                    <div><?= nl2br(h($m['message'])) ?></div>
                    <small class="<?= $mine ? 'text-white-50' : 'text-dark-emphasis' ?>"><?= date('H:i', strtotime($m['created_at'])) ?></small>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <form method="POST" class="d-flex gap-2">
              <input type="hidden" name="action" value="send">
              <input type="hidden" name="user_id" value="<?= (int) $openUserId ?>">
              <input type="text" name="message" class="form-control p-3 rounded-4" placeholder="Tulis balasan..." autocomplete="off" required>
              <button class="btn btn-dark rounded-4 px-4" type="submit">Kirim</button>
            </form>
          </div>
        </section>

        <script>
        (function () {
          const box = document.getElementById('chat-box');
          box.scrollTop = box.scrollHeight;
          let lastId = parseInt(box.dataset.lastId, 10) || 0;
          const userId = box.dataset.userId;

          setInterval(async function () {
            try {
              const res = await fetch('pesan-poll.php?since=' + lastId + '&user=' + userId);
              const data = await res.json();
              if (data.messages && data.messages.length) {
                data.messages.forEach(function (m) {
                  const mine = m.sender_role === 'admin';
                  const row = document.createElement('div');
                  row.className = 'd-flex ' + (mine ? 'justify-content-end' : 'justify-content-start') + ' mb-2';
                  row.innerHTML = '<div class="p-2 px-3 rounded-4 ' + (mine ? 'bg-dark text-white' : 'bg-white border') +
                    '" style="max-width:75%;"><div>' + m.message_html + '</div><small class="' +
                    (mine ? 'text-white-50' : 'text-dark-emphasis') + '">' + m.time + '</small></div>';
                  box.appendChild(row);
                  lastId = m.id;
                });
                box.scrollTop = box.scrollHeight;
              }
            } catch (e) { /* diam saja kalau gagal, coba lagi nanti */ }
          }, 4000);
        })();
        </script>

        <?php
    else:
        // daftar semua percakapan (WA-style list)
        $conversations = $pdo->query(
            "SELECT u.id, u.name, u.email,
                    (SELECT message FROM messages m WHERE m.user_id = u.id ORDER BY m.id DESC LIMIT 1) AS last_message,
                    (SELECT created_at FROM messages m WHERE m.user_id = u.id ORDER BY m.id DESC LIMIT 1) AS last_time,
                    (SELECT COUNT(*) FROM messages m WHERE m.user_id = u.id AND m.sender_role = 'user' AND m.is_read = 0) AS unread
             FROM users u
             WHERE u.role = 'user' AND EXISTS (SELECT 1 FROM messages m2 WHERE m2.user_id = u.id)
             ORDER BY last_time DESC"
        )->fetchAll();
        ?>

        <section class="p-4">
          <div class="container" style="max-width:700px;">
            <h2 class="display-5 mb-4">Percakapan</h2>

            <?php if (empty($conversations)): ?>
              <div class="alert alert-light rounded-4">Belum ada user yang mengirim pesan.</div>
            <?php endif; ?>

            <?php foreach ($conversations as $c): ?>
              <a href="pesan.php?user=<?= (int) $c['id'] ?>" class="text-decoration-none text-dark">
                <div class="p-3 rounded-4 bg-light mb-2 d-flex justify-content-between align-items-center">
                  <div>
                    <strong><?= h($c['name']) ?></strong>
                    <?php if ($c['unread'] > 0): ?><span class="badge bg-danger"><?= (int) $c['unread'] ?></span><?php endif; ?>
                    <div class="text-dark-emphasis small text-truncate" style="max-width:400px;"><?= h($c['last_message']) ?></div>
                  </div>
                  <small class="text-dark-emphasis"><?= $c['last_time'] ? date('d/m H:i', strtotime($c['last_time'])) : '' ?></small>
                </div>
              </a>
            <?php endforeach; ?>

            <hr class="my-5">

            <h4 class="mb-3">
              Pesan Singkat (dari form kontak, satu arah)
              <?php $ucount = (int) $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn(); ?>
              <?php if ($ucount > 0): ?><span class="badge bg-danger"><?= $ucount ?></span><?php endif; ?>
            </h4>
            <?php $contactMessages = $pdo->query('SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC')->fetchAll(); ?>
            <?php if (empty($contactMessages)): ?>
              <div class="alert alert-light rounded-4">Belum ada pesan singkat.</div>
            <?php endif; ?>
            <?php foreach ($contactMessages as $cm): ?>
              <div class="p-3 rounded-4 <?= $cm['is_read'] ? 'bg-light' : 'bg-yellow' ?> mb-2">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                  <div>
                    <strong><?= h($cm['name']) ?></strong> <small class="text-dark-emphasis"><?= h($cm['email']) ?></small>
                    <?php if (!$cm['is_read']): ?><span class="badge bg-danger">Baru</span><?php endif; ?>
                  </div>
                  <div class="d-flex gap-2">
                    <?php if (!$cm['is_read']): ?>
                    <form method="POST">
                      <input type="hidden" name="action" value="contact_read">
                      <input type="hidden" name="id" value="<?= (int) $cm['id'] ?>">
                      <button class="btn btn-outline-dark btn-sm rounded-4" type="submit">Tandai Dibaca</button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" onsubmit="return confirm('Hapus pesan ini?');">
                      <input type="hidden" name="action" value="contact_delete">
                      <input type="hidden" name="id" value="<?= (int) $cm['id'] ?>">
                      <button class="btn btn-outline-danger btn-sm rounded-4" type="submit">Hapus</button>
                    </form>
                  </div>
                </div>
                <p class="mb-0 mt-2"><?= nl2br(h($cm['message'])) ?></p>
                <small class="text-dark-emphasis"><?= h($cm['created_at']) ?></small>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <?php
    endif;
// ============================================================
// TAMPILAN USER
// ============================================================
else:
    $userId = $_SESSION['user_id'];
    $pdo->prepare('UPDATE messages SET is_read = 1 WHERE user_id = ? AND sender_role = "admin"')->execute([$userId]);

    $stmt = $pdo->prepare('SELECT * FROM messages WHERE user_id = ? ORDER BY id ASC');
    $stmt->execute([$userId]);
    $chatMessages = $stmt->fetchAll();
    $lastId = $chatMessages ? end($chatMessages)['id'] : 0;
    ?>

    <section class="p-4">
      <div class="container" style="max-width:700px;">
        <h2 class="display-5 mb-4">Chat dengan Firman</h2>

        <div id="chat-box" class="p-3 rounded-4 bg-light mb-3" style="height:55vh; overflow-y:auto;" data-last-id="<?= (int) $lastId ?>">
          <?php if (empty($chatMessages)): ?>
            <p class="text-dark-emphasis text-center mt-5">Belum ada percakapan. Kirim pesan pertamamu di bawah ini 👇</p>
          <?php endif; ?>
          <?php foreach ($chatMessages as $m): ?>
            <?php $mine = $m['sender_role'] === 'user'; ?>
            <div class="d-flex <?= $mine ? 'justify-content-end' : 'justify-content-start' ?> mb-2">
              <div class="p-2 px-3 rounded-4 <?= $mine ? 'bg-dark text-white' : 'bg-white border' ?>" style="max-width:75%;">
                <div><?= nl2br(h($m['message'])) ?></div>
                <small class="<?= $mine ? 'text-white-50' : 'text-dark-emphasis' ?>"><?= date('H:i', strtotime($m['created_at'])) ?></small>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <form method="POST" class="d-flex gap-2">
          <input type="hidden" name="action" value="send">
          <input type="text" name="message" class="form-control p-3 rounded-4" placeholder="Tulis pesan..." autocomplete="off" required>
          <button class="btn btn-dark rounded-4 px-4" type="submit">Kirim</button>
        </form>
      </div>
    </section>

    <script>
    (function () {
      const box = document.getElementById('chat-box');
      box.scrollTop = box.scrollHeight;
      let lastId = parseInt(box.dataset.lastId, 10) || 0;

      setInterval(async function () {
        try {
          const res = await fetch('pesan-poll.php?since=' + lastId);
          const data = await res.json();
          if (data.messages && data.messages.length) {
            data.messages.forEach(function (m) {
              const mine = m.sender_role === 'user';
              const row = document.createElement('div');
              row.className = 'd-flex ' + (mine ? 'justify-content-end' : 'justify-content-start') + ' mb-2';
              row.innerHTML = '<div class="p-2 px-3 rounded-4 ' + (mine ? 'bg-dark text-white' : 'bg-white border') +
                '" style="max-width:75%;"><div>' + m.message_html + '</div><small class="' +
                (mine ? 'text-white-50' : 'text-dark-emphasis') + '">' + m.time + '</small></div>';
              box.appendChild(row);
              lastId = m.id;
            });
            box.scrollTop = box.scrollHeight;
          }
        } catch (e) { /* diam saja kalau gagal, coba lagi nanti */ }
      }, 4000);
    })();
    </script>

    <?php
endif;

include __DIR__ . '/includes/footer.php';
