<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user = current_user();
refresh_mentor_status($conn, $user['id']);
$user = current_user(); // ambil ulang biar mentor_status ke-sync
$activeTab = 'community';
$flash = flash_get();

$sql = "SELECT p.*, u.full_name AS author_name, u.role AS author_role, u.mentor_status AS author_mentor_status,
        (SELECT COUNT(*) FROM community_likes l WHERE l.post_id = p.id) AS like_count,
        (SELECT COUNT(*) FROM community_likes l WHERE l.post_id = p.id AND l.user_id = ?) AS liked_by_me
        FROM community_posts p
        JOIN users u ON u.id = p.user_id
        ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil komentar (+ reply + like count) untuk semua post sekaligus
$commentsByPost = [];   // post_id => array of top-level comments (untuk dipreview & modal)
$childrenMap = [];      // parent_id => array of comment (reply)
$commentsById = [];     // id => comment (buat lookup "membalas @siapa")
$totalCommentCount = []; // post_id => jumlah semua komentar+reply

if ($posts) {
    $ids = array_column($posts, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = 'i' . str_repeat('i', count($ids));
    $stmt = $conn->prepare("SELECT c.*, u.full_name,
            (SELECT COUNT(*) FROM community_comment_likes cl WHERE cl.comment_id = c.id) AS like_count,
            (SELECT COUNT(*) FROM community_comment_likes cl WHERE cl.comment_id = c.id AND cl.user_id = ?) AS liked_by_me
        FROM community_comments c JOIN users u ON u.id = c.user_id
        WHERE c.post_id IN ($placeholders) ORDER BY c.created_at ASC");
    $stmt->bind_param($types, $user['id'], ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['id'] = (int)$row['id'];
        $row['post_id'] = (int)$row['post_id'];
        $row['parent_id'] = $row['parent_id'] !== null ? (int)$row['parent_id'] : null;
        $commentsById[$row['id']] = $row;
        $totalCommentCount[$row['post_id']] = ($totalCommentCount[$row['post_id']] ?? 0) + 1;
        if ($row['parent_id'] === null) {
            $commentsByPost[$row['post_id']][] = $row;
        } else {
            $childrenMap[$row['parent_id']][] = $row;
        }
    }
    $stmt->close();
}

/** Ratain semua balasan (reply dari reply juga ikut) di bawah 1 komentar utama, urut waktu */
function flatten_replies(int $parentId, array $childrenMap): array {
    $result = [];
    foreach ($childrenMap[$parentId] ?? [] as $child) {
        $result[] = $child;
        $result = array_merge($result, flatten_replies($child['id'], $childrenMap));
    }
    return $result;
}

$pageTitle = 'ManTrading - Community';
require_once __DIR__ . '/includes/head.php';
?>
<div class="flex bg-gray-50 lg:h-screen lg:overflow-hidden">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>

  <div class="flex-1 flex flex-col min-w-0 lg:overflow-hidden">
    <header class="bg-white px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200 flex flex-wrap justify-between items-center gap-3 z-10 sticky top-0 shadow-sm">
      <div class="flex items-center gap-3 sm:gap-4 min-w-0">
        <button onclick="toggleSidebar(true)" class="lg:hidden text-gray-500 hover:text-gray-800 shrink-0">
          <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <div class="min-w-0">
          <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-800 tracking-tight truncate">ManTrading Community</h1>
          <p class="text-gray-500 text-[11px] sm:text-xs md:text-sm mt-0.5 truncate">Ruang diskusi eksklusif & update analisa harian.</p>
        </div>
      </div>

      <?php if (can_post_community()): ?>
      <button onclick="toggleModal('postModal', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl font-semibold text-xs md:text-sm transition-all shadow-lg shadow-indigo-600/25 flex items-center gap-2 shrink-0">
        <i class="fa-solid fa-plus"></i> <span class="hidden sm:inline">Buat Postingan</span>
      </button>
      <?php endif; ?>
    </header>

    <main class="flex-1 lg:overflow-y-auto p-3 sm:p-4 md:p-6 lg:p-8 custom-scrollbar bg-gray-50/50 flex justify-center">
      <div class="w-full max-w-2xl flex flex-col gap-4 sm:gap-6 animate-fade-in-up min-w-0">

        <?php if ($flash): ?>
          <div class="text-xs font-semibold rounded-xl p-3 <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($flash['type'] === 'info' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-rose-50 text-rose-700 border border-rose-200') ?>">
            <?= e($flash['msg']) ?>
          </div>
        <?php endif; ?>

        <?php if (!is_admin() && !is_mentor()): ?>
          <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-5 shadow-sm flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-chalkboard-user"></i></div>
              <div class="min-w-0">
                <p class="text-sm font-bold text-gray-800">Mau ikutan share analisa di Community?</p>
                <p class="text-xs text-gray-500">
                  <?php if ($user['mentor_status'] === 'pending'): ?>
                    Pengajuan lu jadi Mentor lagi menunggu persetujuan admin.
                  <?php elseif ($user['mentor_status'] === 'rejected'): ?>
                    Pengajuan sebelumnya ditolak. Lu bisa ajukan ulang kapan aja.
                  <?php else: ?>
                    Ajukan diri jadi Mentor, kalau disetujui admin lu bisa posting di sini.
                  <?php endif; ?>
                </p>
              </div>
            </div>
            <?php if ($user['mentor_status'] === 'pending'): ?>
              <span class="text-[10px] font-bold uppercase px-3 py-1.5 rounded-full border bg-amber-50 text-amber-600 border-amber-200 shrink-0">Menunggu</span>
            <?php else: ?>
              <button onclick="toggleModal('mentorModal', true)" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-semibold text-xs transition-all shadow shadow-amber-500/20 shrink-0">Ajukan Jadi Mentor</button>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if (!$posts): ?>
          <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center text-gray-500">Belum ada postingan di komunitas saat ini.</div>
        <?php else: foreach ($posts as $post):
          $topLevel = $commentsByPost[$post['id']] ?? [];
          $totalCC = $totalCommentCount[$post['id']] ?? 0;
          // Preview: 3 komentar utama dengan like terbanyak (tie-break: yang lebih dulu duluan)
          $preview = $topLevel;
          usort($preview, function ($a, $b) {
              if ($a['like_count'] !== $b['like_count']) return $b['like_count'] <=> $a['like_count'];
              return $a['id'] <=> $b['id'];
          });
          $preview = array_slice($preview, 0, 3);
        ?>
          <div id="post-<?= (int)$post['id'] ?>" class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm min-w-0">
            <div class="p-3 sm:p-4 flex items-center justify-between gap-2 border-b border-gray-100">
              <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-tr from-indigo-600 to-indigo-400 text-white flex items-center justify-center font-bold text-xs sm:text-sm shadow-md shrink-0">
                  <?= e(strtoupper(substr($post['author_name'], 0, 2))) ?>
                </div>
                <div class="min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <h4 class="text-xs sm:text-sm font-bold text-gray-800 truncate"><?= e($post['author_name']) ?></h4>
                    <span class="bg-indigo-50 text-indigo-600 text-[9px] sm:text-[10px] font-extrabold px-2 py-0.5 rounded-full border border-indigo-100 uppercase shrink-0">
                      <?= $post['author_role'] === 'admin' ? 'Head Analyst' : ($post['author_mentor_status'] === 'approved' ? 'Mentor' : 'Trader') ?>
                    </span>
                  </div>
                  <p class="text-[10px] sm:text-[11px] text-gray-400"><?= e(date('d F Y', strtotime($post['created_at']))) ?></p>
                </div>
              </div>
              <?php if (is_admin() || (int)$post['user_id'] === $user['id']): ?>
                <form method="POST" action="post-delete.php" onsubmit="return confirm('Yakin ingin menghapus postingan ini, admin?');" class="shrink-0">
<?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                  <button type="submit" class="text-gray-400 hover:text-rose-500 p-2 transition-colors" title="Hapus Post"><i class="fa-solid fa-trash-can text-sm"></i></button>
                </form>
              <?php endif; ?>
            </div>

            <div class="p-3 sm:p-4 text-sm text-gray-700 leading-relaxed whitespace-pre-line break-words"><?= nl2br(e($post['caption'])) ?></div>

            <div onclick="openImageModal('serve-image.php?f=<?= urlencode($post['image_path']) ?>')" style="touch-action: pan-y;" class="w-full bg-gray-900 flex items-center justify-center border-y border-gray-100 cursor-zoom-in group relative overflow-hidden">
              <img src="serve-image.php?f=<?= urlencode($post['image_path']) ?>" alt="Post content" class="w-full h-auto max-h-[55vh] sm:max-h-[70vh] object-contain group-hover:scale-[1.02] transition-transform duration-300 pointer-events-none">
              <span class="absolute bottom-2 right-2 bg-black/60 text-white text-[10px] font-semibold px-2 py-1 rounded-lg flex items-center gap-1 opacity-90 pointer-events-none">
                <i class="fa-solid fa-magnifying-glass-plus"></i> Perbesar
              </span>
            </div>

            <div class="px-3 sm:px-4 py-2.5 sm:py-3 flex items-center justify-between border-b border-gray-100 text-xs sm:text-sm text-gray-500">
              <form method="POST" action="like.php">
<?= csrf_field() ?>
                <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                <button type="submit" class="flex items-center gap-1.5 sm:gap-2 font-semibold transition-colors <?= $post['liked_by_me'] ? 'text-rose-600' : 'hover:text-rose-600' ?>">
                  <i class="<?= $post['liked_by_me'] ? 'fa-solid' : 'fa-regular' ?> fa-heart text-base sm:text-lg"></i>
                  <span><?= (int)$post['like_count'] ?> Suka</span>
                </button>
              </form>
              <button onclick="toggleModal('commentsModal_<?= (int)$post['id'] ?>', true)" class="flex items-center gap-1.5 text-[11px] sm:text-xs shrink-0 hover:text-indigo-600 transition-colors" title="Lihat semua komentar">
                <i class="fa-regular fa-comment"></i>
                <span><?= $totalCC ?> Komentar</span>
              </button>
            </div>

            <div class="p-3 sm:p-4 bg-gray-50/50 flex flex-col gap-3">
              <div class="space-y-2">
                <?php if (!$preview): ?>
                  <p class="text-xs text-gray-400 italic">Belum ada komentar. Jadilah yang pertama berkomentar!</p>
                <?php else: foreach ($preview as $c): ?>
                  <div class="bg-white p-2.5 rounded-xl border border-gray-200 text-xs shadow-2xs break-words">
                    <div class="flex items-start justify-between gap-2">
                      <div class="min-w-0">
                        <span class="font-bold text-gray-800 mr-2"><?= e($c['full_name']) ?>:</span>
                        <span class="text-gray-600"><?= e($c['comment_text']) ?></span>
                      </div>
                      <?php if ((int)$c['like_count'] > 0): ?>
                        <span class="shrink-0 text-rose-500 font-semibold flex items-center gap-1"><i class="fa-solid fa-heart text-[10px]"></i><?= (int)$c['like_count'] ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; endif; ?>
                <?php if ($totalCC > count($preview)): ?>
                  <button onclick="toggleModal('commentsModal_<?= (int)$post['id'] ?>', true)" class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-700">Lihat semua <?= $totalCC ?> komentar</button>
                <?php endif; ?>
              </div>

              <form method="POST" action="comment-save.php" class="flex gap-2 mt-1">
<?= csrf_field() ?>
                <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                <input type="text" name="comment_text" placeholder="Tulis komentar..." required class="flex-1 min-w-0 bg-white border border-gray-300 text-gray-800 text-xs p-2.5 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 sm:px-4 py-2.5 rounded-xl font-semibold text-xs transition-all shadow-md shadow-indigo-600/20 shrink-0">Kirim</button>
              </form>
            </div>
          </div>

          <!-- MODAL SEMUA KOMENTAR + REPLY (post #<?= (int)$post['id'] ?>) -->
          <div id="commentsModal_<?= (int)$post['id'] ?>" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('commentsModal_<?= (int)$post['id'] ?>', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col animate-fade-in-up">
              <div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50/50 rounded-t-2xl shrink-0">
                <h2 class="text-base font-bold text-gray-800">Semua Komentar (<?= $totalCC ?>)</h2>
                <button onclick="toggleModal('commentsModal_<?= (int)$post['id'] ?>', false)" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-red-500 transition-colors flex items-center justify-center"><i class="fa-solid fa-xmark"></i></button>
              </div>

              <div class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-5 space-y-4">
                <?php if (!$topLevel): ?>
                  <p class="text-xs text-gray-400 italic text-center py-6">Belum ada komentar. Jadilah yang pertama berkomentar!</p>
                <?php else: foreach ($topLevel as $c):
                  $replies = flatten_replies($c['id'], $childrenMap);
                ?>
                  <div class="space-y-2">
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-3">
                      <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 text-xs">
                          <span class="font-bold text-gray-800"><?= e($c['full_name']) ?></span>
                          <p class="text-gray-600 mt-0.5 break-words"><?= e($c['comment_text']) ?></p>
                        </div>
                      </div>
                      <div class="flex items-center gap-4 mt-2">
                        <form method="POST" action="comment-like.php" class="inline">
<?= csrf_field() ?>
                          <input type="hidden" name="comment_id" value="<?= (int)$c['id'] ?>">
                          <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                          <button type="submit" class="flex items-center gap-1 text-[11px] font-semibold transition-colors <?= $c['liked_by_me'] ? 'text-rose-600' : 'text-gray-400 hover:text-rose-600' ?>">
                            <i class="<?= $c['liked_by_me'] ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i> <?= (int)$c['like_count'] ?>
                          </button>
                        </form>
                        <button type="button" onclick="document.getElementById('replyForm_<?= (int)$c['id'] ?>').classList.toggle('hidden')" class="text-[11px] font-semibold text-gray-400 hover:text-indigo-600 transition-colors">Balas</button>
                      </div>
                      <form id="replyForm_<?= (int)$c['id'] ?>" method="POST" action="comment-save.php" class="hidden flex gap-2 mt-2">
<?= csrf_field() ?>
                        <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                        <input type="hidden" name="parent_id" value="<?= (int)$c['id'] ?>">
                        <input type="text" name="comment_text" placeholder="Balas <?= e($c['full_name']) ?>..." required class="flex-1 min-w-0 bg-white border border-gray-300 text-gray-800 text-xs p-2 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg font-semibold text-[11px] transition-all shrink-0">Kirim</button>
                      </form>
                    </div>

                    <?php foreach ($replies as $r): $replyingTo = $commentsById[$r['parent_id']]['full_name'] ?? ''; ?>
                      <div class="ml-4 sm:ml-6 pl-3 border-l-2 border-indigo-100 bg-white border border-gray-200 rounded-xl p-3">
                        <div class="text-xs min-w-0">
                          <span class="font-bold text-gray-800"><?= e($r['full_name']) ?></span>
                          <?php if ($replyingTo !== '' && $r['parent_id'] !== $c['id']): ?>
                            <span class="text-indigo-400 font-medium">membalas @<?= e($replyingTo) ?></span>
                          <?php endif; ?>
                          <p class="text-gray-600 mt-0.5 break-words"><?= e($r['comment_text']) ?></p>
                        </div>
                        <div class="flex items-center gap-4 mt-2">
                          <form method="POST" action="comment-like.php" class="inline">
<?= csrf_field() ?>
                            <input type="hidden" name="comment_id" value="<?= (int)$r['id'] ?>">
                            <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                            <button type="submit" class="flex items-center gap-1 text-[11px] font-semibold transition-colors <?= $r['liked_by_me'] ? 'text-rose-600' : 'text-gray-400 hover:text-rose-600' ?>">
                              <i class="<?= $r['liked_by_me'] ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i> <?= (int)$r['like_count'] ?>
                            </button>
                          </form>
                          <button type="button" onclick="document.getElementById('replyForm_<?= (int)$r['id'] ?>').classList.toggle('hidden')" class="text-[11px] font-semibold text-gray-400 hover:text-indigo-600 transition-colors">Balas</button>
                        </div>
                        <form id="replyForm_<?= (int)$r['id'] ?>" method="POST" action="comment-save.php" class="hidden flex gap-2 mt-2">
<?= csrf_field() ?>
                          <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                          <input type="hidden" name="parent_id" value="<?= (int)$r['id'] ?>">
                          <input type="text" name="comment_text" placeholder="Balas <?= e($r['full_name']) ?>..." required class="flex-1 min-w-0 bg-white border border-gray-300 text-gray-800 text-xs p-2 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                          <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg font-semibold text-[11px] transition-all shrink-0">Kirim</button>
                        </form>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endforeach; endif; ?>
              </div>

              <form method="POST" action="comment-save.php" class="flex gap-2 p-4 border-t border-gray-100 shrink-0">
<?= csrf_field() ?>
                <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                <input type="text" name="comment_text" placeholder="Tulis komentar..." required class="flex-1 min-w-0 bg-gray-50 border border-gray-300 text-gray-800 text-xs p-2.5 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 sm:px-4 py-2.5 rounded-xl font-semibold text-xs transition-all shadow-md shadow-indigo-600/20 shrink-0">Kirim</button>
              </form>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </main>
  </div>
</div>

<!-- MODAL LIGHTBOX PERBESAR FOTO -->
<div id="imageModal" class="fixed inset-0 z-[120] hidden items-center justify-center p-2 sm:p-6" onclick="closeImageModal(event)">
  <div class="absolute inset-0 bg-black/90 backdrop-blur-sm"></div>
  <button onclick="closeImageModal(event)" class="absolute top-3 right-3 sm:top-5 sm:right-5 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center z-10 transition-colors">
    <i class="fa-solid fa-xmark text-lg"></i>
  </button>
  <img id="imageModalImg" src="" alt="Preview" class="relative max-w-full max-h-full object-contain rounded-lg shadow-2xl animate-fade-in-up" onclick="event.stopPropagation()">
</div>

<script>
  function openImageModal(src) {
    document.getElementById('imageModalImg').src = src;
    const m = document.getElementById('imageModal');
    m.classList.remove('hidden'); m.classList.add('flex');
    document.body.style.overflow = 'hidden';
  }
  function closeImageModal(e) {
    const m = document.getElementById('imageModal');
    m.classList.add('hidden'); m.classList.remove('flex');
    document.getElementById('imageModalImg').src = '';
    document.body.style.overflow = '';
  }
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeImageModal(e);
  });

  // Auto-buka modal komentar post terkait kalau URL ada hash #comments-<id>
  // (dipakai buat balik ke modal yang sama setelah kirim komentar/reply/like)
  (function () {
    const hash = window.location.hash; // contoh: #comments-12
    const match = hash.match(/^#comments-(\d+)$/);
    if (match) {
      const modal = document.getElementById('commentsModal_' + match[1]);
      if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }
    }
  })();
</script>

<?php if (can_post_community()): ?>
<div id="postModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 sm:p-6">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('postModal', false)"></div>
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[95vh] overflow-y-auto custom-scrollbar animate-fade-in-up">
    <div class="flex justify-between items-center p-6 border-b border-gray-100 bg-gray-50/50 rounded-t-2xl sticky top-0 z-10">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-md"><i class="fa-solid fa-bullhorn"></i></div>
        <div>
          <h2 class="text-lg font-bold text-gray-800">Buat Postingan Baru</h2>
          <p class="text-xs text-gray-500 mt-0.5">Bagikan update analisa atau setup ke komunitas.</p>
        </div>
      </div>
      <button onclick="toggleModal('postModal', false)" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-red-500 transition-colors flex items-center justify-center"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <form method="POST" action="post-save.php" enctype="multipart/form-data" class="p-6 space-y-4">
<?= csrf_field() ?>
      <div>
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1 block">Upload Foto <span class="text-red-500">*</span></label>
        <div class="relative">
          <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required
            class="w-full bg-gray-50 border border-gray-300 text-gray-900 p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white file:text-xs file:font-semibold">
        </div>
        <p class="text-[10px] text-gray-400 mt-1">Format JPG/PNG/WEBP, maksimal 3MB.</p>
      </div>
      <div>
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1 block">Bio / Caption Analisa <span class="text-red-500">*</span></label>
        <textarea name="caption" rows="4" placeholder="Tulis analisa, setup, atau pesan untuk member..." required class="w-full bg-gray-50 border border-gray-300 text-gray-900 p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none"></textarea>
      </div>
      <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
        <button type="button" onclick="toggleModal('postModal', false)" class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-6 py-2.5 rounded-lg font-semibold text-sm transition-all">Batal</button>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2.5 rounded-lg font-semibold text-sm transition-all shadow-lg shadow-indigo-600/25 flex items-center gap-2"><i class="fa-solid fa-paper-plane"></i> Publikasikan</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if (!is_admin() && !is_mentor() && $user['mentor_status'] !== 'pending'): ?>
<!-- MODAL AJUKAN JADI MENTOR -->
<div id="mentorModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 sm:p-6">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('mentorModal', false)"></div>
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[95vh] overflow-y-auto custom-scrollbar animate-fade-in-up">
    <div class="flex justify-between items-center p-6 border-b border-gray-100 bg-gray-50/50 rounded-t-2xl sticky top-0 z-10">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center text-white shadow-md"><i class="fa-solid fa-chalkboard-user"></i></div>
        <div>
          <h2 class="text-lg font-bold text-gray-800">Ajukan Jadi Mentor</h2>
          <p class="text-xs text-gray-500 mt-0.5">Kalau disetujui admin, lu bisa posting analisa di Community.</p>
        </div>
      </div>
      <button onclick="toggleModal('mentorModal', false)" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-red-500 transition-colors flex items-center justify-center"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <form method="POST" action="mentor-request.php" class="p-6 space-y-4">
<?= csrf_field() ?>
      <div>
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1 block">Kenapa lu pantas jadi Mentor?</label>
        <textarea name="message" rows="4" placeholder="Ceritain pengalaman trading, strategi andalan, atau alasan lain (opsional)..." class="w-full bg-gray-50 border border-gray-300 text-gray-900 p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none resize-none"></textarea>
      </div>
      <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
        <button type="button" onclick="toggleModal('mentorModal', false)" class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-6 py-2.5 rounded-lg font-semibold text-sm transition-all">Batal</button>
        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white px-8 py-2.5 rounded-lg font-semibold text-sm transition-all shadow-lg shadow-amber-500/25 flex items-center gap-2"><i class="fa-solid fa-paper-plane"></i> Kirim Pengajuan</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
</body>
</html>
