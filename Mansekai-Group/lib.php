<?php
require_once __DIR__ . '/db.php';

/** Ambil satu blok data JSON milik user (mis. 'projects', 'team', dst). */
function ud_get(mysqli $conn, int $ownerId, string $key, $default = []) {
    $stmt = $conn->prepare("SELECT data_value FROM user_data WHERE owner_id = ? AND data_key = ? LIMIT 1");
    $stmt->bind_param('is', $ownerId, $key);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $decoded = json_decode($row['data_value'], true);
        return $decoded === null ? $default : $decoded;
    }
    return $default;
}

/** Simpan (upsert) satu blok data JSON milik user. */
function ud_set(mysqli $conn, int $ownerId, string $key, $value): bool {
    $json = json_encode($value, JSON_UNESCAPED_UNICODE);
    $stmt = $conn->prepare("INSERT INTO user_data (owner_id, data_key, data_value)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE data_value = VALUES(data_value)");
    $stmt->bind_param('iss', $ownerId, $key, $json);
    return $stmt->execute();
}

/** Semua data_key yang valid & boleh disimpan/diambil lewat API generik. */
function ud_allowed_keys(): array {
    return ['projects', 'team', 'companies', 'tasks', 'schedules', 'activities', 'cashflow', 'threads'];
}

/** Data awal/demo untuk akun yang baru mendaftar, biar dashboard tidak kosong melompong. */
function seed_default_data(mysqli $conn, int $ownerId, string $name) {
    $now = date('Y-m-d');

    $team = [
        ['id' => 1, 'name' => 'Mike Loke', 'role' => 'Backend Dev', 'type' => 'team', 'avatar' => avatar_url('Mike Loke')],
        ['id' => 2, 'name' => 'Sarah Hosten', 'role' => 'QA Engineer', 'type' => 'team', 'avatar' => avatar_url('Sarah Hosten')],
        ['id' => 3, 'name' => 'Dena Thompson', 'role' => 'Business Dev', 'type' => 'team', 'avatar' => avatar_url('Dena Thompson')],
        ['id' => 4, 'name' => 'Belong Interactive', 'role' => 'Client Representative', 'type' => 'client', 'avatar' => avatar_url('Belong Interactive')],
        ['id' => 5, 'name' => 'App Emirates', 'role' => 'Project Owner', 'type' => 'client', 'avatar' => avatar_url('App Emirates')],
        ['id' => 6, 'name' => 'PT Solusi Digital', 'role' => 'Calon Klien Potensial', 'type' => 'prospect', 'avatar' => avatar_url('PT Solusi Digital')],
    ];

    $companies = [
        ['id' => 1, 'name' => 'PT Solusi Teknologi Utama', 'type' => 'IT / Tech', 'assets' => 150000, 'liabilities' => 45000, 'color' => '#3b3086'],
        ['id' => 2, 'name' => 'Kopi Senja Utama', 'type' => 'F&B / Coffee', 'assets' => 85000, 'liabilities' => 25000, 'color' => '#2ecc71'],
    ];

    $projects = [
        ['id' => 1, 'title' => 'App Development', 'client' => 'Belong Interactive', 'status' => 'started', 'progress' => 45, 'color' => '#ff9f43', 'icon' => 'fa-rocket', 'deadline' => date('Y-m-d', strtotime('+30 days')), 'team' => [1, 4], 'images' => [], 'comments' => [['user' => 'Mike', 'text' => 'Desain UI perlu direvisi sedikit.']]],
        ['id' => 2, 'title' => 'Website Design', 'client' => 'App Emirates', 'status' => 'approval', 'progress' => 75, 'color' => '#2ecc71', 'icon' => 'fa-laptop-code', 'deadline' => date('Y-m-d', strtotime('+10 days')), 'team' => [2, 5], 'images' => [], 'comments' => []],
        ['id' => 3, 'title' => 'Landing Page', 'client' => 'Dev Batch', 'status' => 'completed', 'progress' => 100, 'color' => '#3498db', 'icon' => 'fa-plane-departure', 'deadline' => date('Y-m-d', strtotime('-2 days')), 'team' => [3], 'images' => [], 'comments' => []],
        ['id' => 4, 'title' => 'Quality Assurance', 'client' => 'Q2 Technologies', 'status' => 'discrepancy', 'progress' => 25, 'color' => '#9b59b6', 'icon' => 'fa-cubes', 'deadline' => date('Y-m-d', strtotime('+5 days')), 'team' => [2], 'images' => [], 'comments' => []],
    ];

    $tasks = [
        ['id' => 1, 'title' => 'Review PR Frontend', 'completed' => false],
        ['id' => 2, 'title' => 'Submit Laporan QA', 'completed' => false],
    ];

    $schedules = [
        ['id' => 1, 'time' => '08:00 - 09:30', 'activity' => 'Morning Standup & Sprint Planning', 'category' => 'Meeting', 'borderColor' => '#ff9f43'],
        ['id' => 2, 'time' => '10:00 - 12:00', 'activity' => 'Coding & Frontend Integration', 'category' => 'Development', 'borderColor' => '#2ecc71'],
        ['id' => 3, 'time' => '13:30 - 15:00', 'activity' => 'Client Review with Belong Interactive', 'category' => 'Client', 'borderColor' => '#3498db'],
    ];

    $threads = [
        ['id' => 1, 'name' => 'Sarah Hosten', 'handle' => '@sarah_qa', 'avatar' => avatar_url('Sarah Hosten'), 'text' => 'Teman-teman, deployment v5.0 berjalan sangat mulus hari ini! Segenap tim QA sudah verifikasi semua fitur utama. 🚀', 'time' => '2j lalu', 'likes' => 14, 'liked' => false, 'reposts' => 2, 'replyList' => [['name' => 'Mike Loke', 'text' => 'Mantap kak!', 'time' => '1j lalu']]],
        ['id' => 2, 'name' => 'Mike Loke', 'handle' => '@mike_backend', 'avatar' => avatar_url('Mike Loke'), 'text' => 'Mantap! Sisa optimasi query database cash flow biar load-nya makin sat-set ke depannya.', 'time' => '4j lalu', 'likes' => 8, 'liked' => false, 'reposts' => 1, 'replyList' => []],
    ];

    ud_set($conn, $ownerId, 'team', $team);
    ud_set($conn, $ownerId, 'companies', $companies);
    ud_set($conn, $ownerId, 'projects', $projects);
    ud_set($conn, $ownerId, 'tasks', $tasks);
    ud_set($conn, $ownerId, 'schedules', $schedules);
    ud_set($conn, $ownerId, 'threads', $threads);
    ud_set($conn, $ownerId, 'activities', [
        ['id' => 1, 'name' => $name, 'role' => 'Lead Developer', 'text' => 'Membuat akun dashboard baru', 'time' => 'Baru saja', 'avatar' => avatar_url($name)],
    ]);
    ud_set($conn, $ownerId, 'cashflow', []);
}

function avatar_url(string $name): string {
    return 'https://ui-avatars.com/api/?name=' . urlencode($name ?: 'User') . '&background=random&color=fff&bold=true';
}

/** Kirim email lewat Brevo (Sendinblue) Transactional Email API. Return true jika terkirim. */
function send_email_brevo(string $toEmail, string $toName, string $subject, string $htmlContent): bool {
    if (BREVO_API_KEY === '') return false;

    $payload = [
        'sender' => ['name' => BREVO_SENDER_NAME, 'email' => BREVO_SENDER_EMAIL],
        'to' => [['email' => $toEmail, 'name' => $toName ?: $toEmail]],
        'subject' => $subject,
        'htmlContent' => $htmlContent,
    ];

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'accept: application/json',
            'api-key: ' . BREVO_API_KEY,
            'content-type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $response !== false && $httpCode >= 200 && $httpCode < 300;
}

function json_input(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function json_out($data, int $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
