<?php
// =========================================
// Konfigurasi & helper pengiriman email
// Dipakai untuk fitur "Lupa Password".
//
// PENTING buat hosting gratis (InfinityFree dkk):
// PHP mail() dan koneksi SMTP keluar DIBLOKIR total di paket gratis
// InfinityFree, jadi TIDAK BISA dipakai sama sekali. Solusinya, di
// sini kita kirim email lewat Brevo (dulu bernama Sendinblue) pakai
// REST API via HTTPS/cURL, karena koneksi HTTPS keluar masih diizinkan.
//
// Cara setup (gratis, 300 email/hari, tanpa expired):
// 1. Daftar akun di https://www.brevo.com (gratis)
// 2. Masuk ke menu SMTP & API -> API Keys -> Generate a new API key
// 3. Copy API key-nya, tempel ke BREVO_API_KEY di bawah ini
// 4. (opsional tapi disarankan) verifikasi domain/alamat pengirim di
//    Brevo -> Senders supaya email tidak masuk folder spam
// =========================================

define('BREVO_API_KEY', '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');

// Nama & alamat pengirim yang tampil di inbox penerima.
// Kalau belum verifikasi domain sendiri di Brevo, alamat default
// yang otomatis disediakan Brevo (biasanya email akun Brevo kamu)
// lebih aman dipakai supaya tidak ditolak/masuk spam.
define('MAIL_FROM_EMAIL', 'firmanhidayat200138@gmail.com');
define('MAIL_FROM_NAME', 'Firman ( Mansekai Study )');

/**
 * Kirim email lewat Brevo REST API (HTTPS/cURL), bukan mail()/SMTP.
 * Dipakai karena InfinityFree (hosting gratis) blokir mail() & SMTP,
 * tapi koneksi HTTPS keluar via cURL masih diizinkan.
 *
 * @param string $to      Alamat email tujuan
 * @param string $subject Subjek email
 * @param string $htmlBody Isi email dalam format HTML
 * @return bool true kalau berhasil dikirim
 */
function kirimEmail($to, $subject, $htmlBody) {
    if (BREVO_API_KEY === 'GANTI_DENGAN_API_KEY_BREVO_KAMU' || BREVO_API_KEY === '') {
        error_log("kirimEmail() gagal: BREVO_API_KEY belum diisi di config/mail.php");
        return false;
    }

    $payload = [
        'sender'      => ['name' => MAIL_FROM_NAME, 'email' => MAIL_FROM_EMAIL],
        'to'          => [['email' => $to]],
        'subject'     => $subject,
        'htmlContent' => $htmlBody,
    ];

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => [
            'accept: application/json',
            'api-key: ' . BREVO_API_KEY,
            'content-type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("kirimEmail() gagal (cURL): " . $curlError);
        return false;
    }

    // Brevo balikin 201 kalau email berhasil diterima buat dikirim
    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    }

    error_log("kirimEmail() gagal (Brevo HTTP $httpCode): " . $response);
    return false;
}

/**
 * Kirim email berisi link reset password.
 *
 * @param string $to        Alamat email tujuan
 * @param string $nama      Nama pemilik akun
 * @param string $resetLink Link lengkap untuk reset password
 * @return bool
 */
function kirimEmailResetPassword($to, $nama, $resetLink) {
    $subject = "Reset Password - Mansekai Study";
    $namaAman = htmlspecialchars($nama);
    $linkAman = htmlspecialchars($resetLink);

    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto;'>
        <h2 style='color: #00876b;'>Mansekai Study</h2>
        <p>Halo <b>{$namaAman}</b>,</p>
        <p>Kami menerima permintaan untuk reset password akun kamu. Klik tombol di bawah ini untuk membuat password baru:</p>
        <p style='text-align:center; margin: 30px 0;'>
            <a href='{$linkAman}' style='background:#00876b;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;'>Reset Password</a>
        </p>
        <p>Atau salin link berikut ke browser kamu:<br>
        <a href='{$linkAman}'>{$linkAman}</a></p>
        <p>Link ini hanya berlaku selama <b>1 jam</b>. Kalau kamu tidak merasa meminta reset password, abaikan saja email ini.</p>
        <hr style='border:none;border-top:1px solid #eee;margin:25px 0;'>
        <p style='font-size:12px;color:#999;'>Email ini dikirim otomatis, mohon tidak membalas.</p>
    </div>";

    return kirimEmail($to, $subject, $body);
}
