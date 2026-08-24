<?php
require __DIR__ . '/config/db.php';

$threadId = isset($_GET['thread']) ? (int) $_GET['thread'] : 0;

// ============ AKSI: buat thread baru ============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_thread') {
    requireLogin();
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');

    if ($title !== '' && $body !== '') {
        $stmt = $pdo->prepare('INSERT INTO forum_threads (user_id, title, body) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], $title, $body]);
        header('Location: community.php?thread=' . $pdo->lastInsertId());
        exit;
    }
}

// ============ AKSI: balas thread ============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    requireLogin();
    $tid = (int) ($_POST['thread_id'] ?? 0);
    $body = trim($_POST['body'] ?? '');

    if ($tid && $body !== '') {
        $stmt = $pdo->prepare('INSERT INTO forum_replies (thread_id, user_id, body) VALUES (?, ?, ?)');
        $stmt->execute([$tid, $_SESSION['user_id'], $body]);
    }
    header('Location: community.php?thread=' . $tid);
    exit;
}

// ============ AKSI ADMIN: pin / hapus thread / hapus balasan ============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['pin', 'delete_thread', 'delete_reply'], true)) {
    requireAdmin();
    $id = (int) ($_POST['id'] ?? 0);

    if ($_POST['action'] === 'pin' && $id) {
        $pdo->prepare('UPDATE forum_threads SET is_pinned = NOT is_pinned WHERE id = ?')->execute([$id]);
        header('Location: community.php?thread=' . $id);
        exit;
    }
    if ($_POST['action'] === 'delete_thread' && $id) {
        $pdo->prepare('DELETE FROM forum_threads WHERE id = ?')->execute([$id]);
        header('Location: community.php');
        exit;
    }
    if ($_POST['action'] === 'delete_reply' && $id) {
        $tid = (int) ($_POST['thread_id'] ?? 0);
        $pdo->prepare('DELETE FROM forum_replies WHERE id = ?')->execute([$id]);
        header('Location: community.php?thread=' . $tid);
        exit;
    }
}

include __DIR__ . '/includes/header.php';

// ============ TAMPILAN: detail 1 thread ============
if ($threadId) {
    $stmt = $pdo->prepare(
        'SELECT ft.*, u.name AS author_name, u.role AS author_role
         FROM forum_threads ft JOIN users u ON u.id = ft.user_id
         WHERE ft.id = ?'
    );
    $stmt->execute([$threadId]);
    $thread = $stmt->fetch();

    if (!$thread) {
        echo '<section class="p-5"><div class="container"><div class="alert alert-warning rounded-4">Thread tidak ditemukan.</div>
              <a href="community.php" class="btn btn-dark rounded-4">Kembali ke Community</a></div></section>';
        include __DIR__ . '/includes/footer.php';
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT fr.*, u.name AS author_name, u.role AS author_role
         FROM forum_replies fr JOIN users u ON u.id = fr.user_id
         WHERE fr.thread_id = ? ORDER BY fr.created_at ASC'
    );
    $stmt->execute([$threadId]);
    $replies = $stmt->fetchAll();
    ?>

    <section class="p-5">
      <div class="container">
        <a href="community.php" class="text-decoration-none">&larr; Kembali ke Community</a>

        <div class="p-4 rounded-4 bg-yellow mt-3">
          <div class="d-flex justify-content-between flex-wrap gap-2">
            <h3 class="mb-1">
              <?php if ($thread['is_pinned']): ?><span class="badge bg-dark">Pinned</span><?php endif; ?>
              <?= h($thread['title']) ?>
            </h3>
            <?php if (isAdmin()): ?>
              <div class="d-flex gap-2">
                <form method="POST">
                  <input type="hidden" name="action" value="pin">
                  <input type="hidden" name="id" value="<?= (int) $thread['id'] ?>">
                  <button class="btn btn-outline-dark btn-sm rounded-4" type="submit"><?= $thread['is_pinned'] ? 'Unpin' : 'Pin' ?></button>
                </form>
                <form method="POST" onsubmit="return confirm('Hapus thread ini beserta semua balasannya?');">
                  <input type="hidden" name="action" value="delete_thread">
                  <input type="hidden" name="id" value="<?= (int) $thread['id'] ?>">
                  <button class="btn btn-outline-danger btn-sm rounded-4" type="submit">Hapus Thread</button>
                </form>
              </div>
            <?php endif; ?>
          </div>
          <small class="text-dark-emphasis">
            oleh <?= h($thread['author_name']) ?> <?php if ($thread['author_role'] === 'admin'): ?><span class="badge bg-dark">Admin</span><?php endif; ?>
            &middot; <?= h($thread['created_at']) ?>
          </small>
          <p class="mt-3 mb-0"><?= nl2br(h($thread['body'])) ?></p>
        </div>

        <h5 class="mt-5 mb-3"><?= count($replies) ?> Balasan</h5>
        <?php foreach ($replies as $r): ?>
          <div class="p-3 rounded-4 bg-light mb-3">
            <div class="d-flex justify-content-between flex-wrap gap-2">
              <small>
                <strong><?= h($r['author_name']) ?></strong>
                <?php if ($r['author_role'] === 'admin'): ?><span class="badge bg-dark">Admin</span><?php endif; ?>
                &middot; <?= h($r['created_at']) ?>
              </small>
              <?php if (isAdmin()): ?>
                <form method="POST">
                  <input type="hidden" name="action" value="delete_reply">
                  <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                  <input type="hidden" name="thread_id" value="<?= (int) $threadId ?>">
                  <button class="btn btn-sm btn-outline-danger rounded-4" type="submit">Hapus</button>
                </form>
              <?php endif; ?>
            </div>
            <p class="mb-0 mt-2"><?= nl2br(h($r['body'])) ?></p>
          </div>
        <?php endforeach; ?>

        <?php if (isLoggedIn()): ?>
          <form method="POST" class="mt-4">
            <input type="hidden" name="action" value="reply">
            <input type="hidden" name="thread_id" value="<?= (int) $threadId ?>">
            <textarea name="body" class="form-control p-3 rounded-4" rows="3" placeholder="Tulis balasan..." required></textarea>
            <div class="d-grid mt-2">
              <button class="btn btn-dark rounded-4" type="submit">Kirim Balasan</button>
            </div>
          </form>
        <?php else: ?>
          <div class="alert alert-light rounded-4 mt-4">
            <a href="login.php">Login</a> untuk ikut membalas diskusi ini.
          </div>
        <?php endif; ?>
      </div>
    </section>

    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// ============ TAMPILAN: daftar semua thread ============
$threads = $pdo->query(
    'SELECT ft.*, u.name AS author_name, u.role AS author_role,
            (SELECT COUNT(*) FROM forum_replies fr WHERE fr.thread_id = ft.id) AS reply_count
     FROM forum_threads ft JOIN users u ON u.id = ft.user_id
     ORDER BY ft.is_pinned DESC, ft.created_at DESC'
)->fetchAll();
?>

<section class="p-5">
  <div class="container">
    <h2 class="display-4 mb-4">Community</h2>
    <p class="text-dark-emphasis">Forum diskusi terbuka antara pengunjung dan Firman.</p>

    <?php if (isLoggedIn()): ?>
      <div class="p-4 rounded-4 bg-yellow mb-4">
        <h5>Mulai Diskusi Baru</h5>
        <form method="POST">
          <input type="hidden" name="action" value="new_thread">
          <div class="mb-3">
            <input type="text" name="title" class="form-control p-3 rounded-4" placeholder="Judul diskusi" required>
          </div>
          <div class="mb-3">
            <textarea name="body" class="form-control p-3 rounded-4" rows="3" placeholder="Tulis pertanyaan atau topik diskusimu..." required></textarea>
          </div>
          <button class="btn btn-dark rounded-4" type="submit">Posting</button>
        </form>
      </div>
    <?php else: ?>
      <div class="alert alert-light rounded-4 mb-4">
        <a href="login.php">Login</a> atau <a href="register.php">daftar</a> untuk mulai diskusi baru.
      </div>
    <?php endif; ?>

    <?php if (empty($threads)): ?>
      <div class="alert alert-light rounded-4">Belum ada diskusi. Jadilah yang pertama!</div>
    <?php endif; ?>

    <div class="row g-3">
      <?php foreach ($threads as $t): ?>
        <div class="col-12">
          <a href="community.php?thread=<?= (int) $t['id'] ?>" class="text-decoration-none text-dark">
            <div class="p-4 rounded-4 bg-light">
              <h5 class="mb-1">
                <?php if ($t['is_pinned']): ?><span class="badge bg-dark">Pinned</span><?php endif; ?>
                <?= h($t['title']) ?>
              </h5>
              <small class="text-dark-emphasis">
                oleh <?= h($t['author_name']) ?>
                <?php if ($t['author_role'] === 'admin'): ?><span class="badge bg-dark">Admin</span><?php endif; ?>
                &middot; <?= h($t['created_at']) ?> &middot; <?= (int) $t['reply_count'] ?> balasan
              </small>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
