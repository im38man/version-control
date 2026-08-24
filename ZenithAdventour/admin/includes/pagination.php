<?php
/**
 * Helper pagination sederhana untuk tabel-tabel di admin.
 * Semua tabel listing di admin dibatasi 10 baris per halaman.
 */
define('ADMIN_PER_PAGE', 10);

/** Ambil nomor halaman aktif dari query string (?page=) */
function get_current_page() {
    $page = (int)($_GET['page'] ?? 1);
    return $page < 1 ? 1 : $page;
}

/** Hitung OFFSET SQL dari nomor halaman aktif */
function get_page_offset() {
    return (get_current_page() - 1) * ADMIN_PER_PAGE;
}

/**
 * Render kontrol pagination (Sebelumnya / nomor halaman / Berikutnya).
 * $total_rows = jumlah total baris data (dari query COUNT terpisah).
 * $extra_query = query string tambahan yang perlu dipertahankan, contoh: "status=pending&"
 */
function render_pagination($total_rows, $extra_query = '') {
    $total_pages = (int)ceil($total_rows / ADMIN_PER_PAGE);
    if ($total_pages <= 1) return;

    $current = get_current_page();
    if ($current > $total_pages) $current = $total_pages;

    echo '<div class="pagination">';
    echo '<span class="pagination-info">Halaman ' . $current . ' dari ' . $total_pages . ' (' . $total_rows . ' data)</span>';
    echo '<div class="pagination-links">';

    if ($current > 1) {
        echo '<a href="?' . $extra_query . 'page=' . ($current - 1) . '" class="page-link"><i class="fa-solid fa-chevron-left"></i></a>';
    }

    $start = max(1, $current - 2);
    $end = min($total_pages, $current + 2);
    if ($start > 1) echo '<a href="?' . $extra_query . 'page=1" class="page-link">1</a>' . ($start > 2 ? '<span class="page-dots">…</span>' : '');
    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $current ? ' active' : '';
        echo '<a href="?' . $extra_query . 'page=' . $i . '" class="page-link' . $active . '">' . $i . '</a>';
    }
    if ($end < $total_pages) echo ($end < $total_pages - 1 ? '<span class="page-dots">…</span>' : '') . '<a href="?' . $extra_query . 'page=' . $total_pages . '" class="page-link">' . $total_pages . '</a>';

    if ($current < $total_pages) {
        echo '<a href="?' . $extra_query . 'page=' . ($current + 1) . '" class="page-link"><i class="fa-solid fa-chevron-right"></i></a>';
    }

    echo '</div></div>';
}
