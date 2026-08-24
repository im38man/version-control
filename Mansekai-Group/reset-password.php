<?php
require_once __DIR__ . '/config.php';
$csrf = csrf_token();
$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password - Dashboard V5.0</title>
<link rel="icon" type="image/png" href="logo.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
    body { background:#f5f6fa; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
    .box { background:#fff; width:100%; max-width:420px; padding:35px; border-radius:20px; box-shadow:0 15px 35px rgba(0,0,0,0.08); }
    .box h2 { margin-bottom:8px; font-size:20px; color:#2d3436; }
    .box p { font-size:12px; color:#8e94a8; margin-bottom:20px; }
    .form-group { margin-bottom:15px; }
    .form-group label { display:block; font-size:12px; margin-bottom:6px; color:#2d3436; font-weight:500; }
    .form-group input { width:100%; padding:12px 15px; border:1px solid #e1e4e8; border-radius:12px; outline:none; font-size:13px; background:#fafafa; }
    .btn { background:#3b3086; color:#fff; border:none; width:100%; padding:14px; border-radius:12px; font-size:13px; font-weight:600; cursor:pointer; margin-top:10px; }
    .alert { padding:10px 14px; border-radius:10px; font-size:12px; margin-bottom:15px; display:none; }
    .alert.error { background:rgba(255,71,87,0.1); color:#ff4757; border:1px solid rgba(255,71,87,0.3); }
    .alert.success { background:rgba(46,204,113,0.1); color:#2ecc71; border:1px solid rgba(46,204,113,0.3); }
    a { color:#3b3086; font-size:12px; }
</style>
</head>
<body>
    <div class="box">
        <h2>Buat Password Baru</h2>
        <p>Masukkan password baru untuk akun dashboard kamu.</p>
        <div id="alertBox" class="alert"></div>
        <?php if (!$token): ?>
            <p style="color:#ff4757; font-size:13px;">Link reset tidak valid. Silakan minta reset password ulang dari halaman login.</p>
        <?php else: ?>
        <form id="resetForm">
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" id="newPassword" placeholder="Minimal 6 karakter" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Password Baru</label>
                <input type="password" id="confirmPassword" placeholder="Ulangi password baru" required>
            </div>
            <button type="submit" class="btn">Simpan Password Baru</button>
        </form>
        <?php endif; ?>
        <p style="margin-top:20px; text-align:center;"><a href="login.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Login</a></p>
    </div>

    <script>
        const TOKEN = <?php echo json_encode($token); ?>;
        const CSRF_TOKEN = <?php echo json_encode($csrf); ?>;

        function showAlert(msg, type) {
            const el = document.getElementById('alertBox');
            el.innerText = msg;
            el.className = 'alert ' + type;
            el.style.display = 'block';
        }

        const formEl = document.getElementById('resetForm');
        if (formEl) {
            formEl.addEventListener('submit', async (e) => {
                e.preventDefault();
                const p1 = document.getElementById('newPassword').value;
                const p2 = document.getElementById('confirmPassword').value;
                if (p1 !== p2) { showAlert('Password dan konfirmasi tidak cocok!', 'error'); return; }

                const res = await fetch('api/auth.php?action=do_reset', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: TOKEN, password: p1, csrf: CSRF_TOKEN })
                });
                const data = await res.json();
                if (!data.ok) { showAlert(data.error || 'Gagal reset password.', 'error'); return; }

                showAlert('Password berhasil diubah! Mengalihkan ke halaman login...', 'success');
                setTimeout(() => { window.location.href = 'login.php'; }, 1500);
            });
        }
    </script>
</body>
</html>
