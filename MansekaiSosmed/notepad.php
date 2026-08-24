<?php
require 'includes/auth.php';
requireLogin();

$pageTitle  = "Notepad - Mansekai";
$activePage = "notepad.php";

include 'includes/header.php';
?>
<style>
/* Style tambahan khusus halaman ini (di luar assets/style.css) */

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        :root { --bg-sidebar: #0f111a; --bg-main: #f4ecf0; --accent-green: #00f2c3; --accent-green-dark: #00c49f; --text-dark: #2d3142; --text-muted: #7d8597; --card-bg: #ffffff; --sidebar-hover: #1c2130; }
        body { display: flex; background-color: var(--bg-main); color: var(--text-dark); min-height: 100vh; overflow-x: hidden; }
        aside { width: 260px; background-color: var(--bg-sidebar); color: #fff; display: flex; flex-direction: column; justify-content: space-between; position: fixed; height: 100vh; left: 0; top: 0; padding: 20px 0; z-index: 100; transition: all 0.3s ease; }
        .sidebar-brand { padding: 0 24px 20px 24px; font-size: 1.1rem; font-weight: 600; color: var(--accent-green); border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar-menu { list-style: none; padding: 20px 12px; flex-grow: 1; overflow-y: auto; }
        .sidebar-menu li { margin-bottom: 6px; }
        .sidebar-menu a { display: flex; align-items: center; gap: 14px; padding: 12px 16px; color: var(--text-muted); text-decoration: none; font-size: 0.95rem; border-radius: 8px; transition: 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu li.active a { background-color: var(--sidebar-hover); color: #fff; }
        .sidebar-menu li.active a { border-left: 4px solid var(--accent-green); }
        .sidebar-footer { padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.85rem; color: var(--text-muted); }
        
        main { margin-left: 260px; flex-grow: 1; padding: 30px; background: linear-gradient(135deg, #f9f1f5 0%, #e8e2eb 100%); min-height: 100vh; width: calc(100% - 260px); transition: all 0.3s ease; }
        .header-title h1 { font-size: 1.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; }
        
        .notepad-controls { display: flex; gap: 10px; margin-bottom: 24px; }
        .notepad-controls button { background-color: var(--accent-green-dark); color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9rem; transition: 0.2s; }
        .notepad-controls button:hover { background-color: #00876b; }

        .notepad-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .note-card { background-color: var(--card-bg); border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between; border-left: 4px solid var(--accent-green-dark); }
        .note-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 10px; }
        .note-title-input { font-size: 1rem; font-weight: 600; border: none; border-bottom: 1px dashed #ccc; outline: none; width: 100%; background: transparent; padding-bottom: 4px; color: var(--text-dark); }
        .note-title-input:focus { border-bottom: 1px solid var(--accent-green-dark); }
        
        .note-body { width: 100%; height: 120px; padding: 10px; border: 1px solid #eee; border-radius: 8px; outline: none; font-size: 0.85rem; background: #fafafa; resize: none; margin-bottom: 12px; }
        .note-footer { display: flex; justify-content: flex-end; gap: 8px; }
        .note-footer i { cursor: pointer; color: var(--text-muted); font-size: 0.9rem; transition: color 0.2s; padding: 4px; }
        .note-footer i.fa-trash:hover { color: #ff4d4d; }

        @media (max-width: 1200px) { .notepad-grid { grid-template-columns: 1fr; } }
        @media (max-width: 992px) {
            aside { width: 75px; padding: 10px 0; }
            aside .sidebar-brand, aside .sidebar-footer, aside .sidebar-menu .menu-label { display: none; }
            aside .sidebar-menu a { justify-content: center; padding: 14px; font-size: 1.1rem; }
            aside .sidebar-menu li.active a { border-left: none; border-bottom: 3px solid var(--accent-green); }
            main { margin-left: 75px; width: calc(100% - 75px); padding: 20px 15px; }
        }
        @media (max-width: 600px) {
            body { flex-direction: column; }
            aside { width: 100%; height: 60px; position: fixed; bottom: 0; top: auto; flex-direction: row; padding: 0; border-top: 1px solid rgba(255,255,255,0.1); z-index: 1000; }
            .sidebar-menu { display: flex; flex-direction: row; justify-content: space-around; padding: 0; align-items: center; width: 100%; }
            .sidebar-menu li { margin-bottom: 0; flex-grow: 1; text-align: center; }
            .sidebar-menu a { padding: 15px 0; border-radius: 0; justify-content: center; }
            .sidebar-menu li.active a { border-bottom: 3px solid var(--accent-green); background-color: var(--sidebar-hover); }
            main { margin-left: 0; width: 100%; padding: 15px; margin-bottom: 70px; }
            .header-title h1 { font-size: 1.3rem; }
        }
    
</style>

        <div class="header-title">
            <h1>Notepad Catatan</h1>
            <p style="color: var(--text-muted); margin-bottom: 20px;">Buat, edit judul, isi, dan hapus catatan bebas Anda secara fleksibel.</p>
        </div>
        
        <div class="notepad-controls">
            <button onclick="addNewNote()"><i class="fa-solid fa-plus"></i> Tambah Catatan Baru</button>
        </div>

        <div class="notepad-grid" id="notepadGrid">
            <!-- Card catatan dimuat secara dinamis via JavaScript -->
        </div>
    
<script>

        // Data catatan disimpan di database (per user login), bukan lagi localStorage,
        // supaya tiap akun punya notepad masing-masing.
        let notesData = [];
        let debounceTimers = {};

        async function apiNotepad(method, body) {
            const res = await fetch('api/notepad.php', {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: body ? JSON.stringify(body) : undefined
            });
            return res.json();
        }

        async function loadNotes() {
            const res = await apiNotepad('GET');
            if (res.success) {
                notesData = res.data.map(n => ({ id: n.id, title: n.judul, content: n.isi }));
                renderNotes();
            }
        }

        function renderNotes() {
            const grid = document.getElementById('notepadGrid');
            grid.innerHTML = "";

            if (notesData.length === 0) {
                grid.innerHTML = '<p style="color:var(--text-muted); grid-column: 1 / -1;">Belum ada catatan. Klik "Tambah Catatan Baru" untuk mulai menulis.</p>';
                return;
            }

            notesData.forEach((note) => {
                const card = document.createElement('div');
                card.className = 'note-card';
                card.innerHTML = `
                    <div class="note-header">
                        <input type="text" class="note-title-input" value="${escapeHtml(note.title)}" oninput="updateNoteTitle(${note.id}, this.value)" placeholder="Judul Catatan...">
                    </div>
                    <textarea class="note-body" oninput="updateNoteContent(${note.id}, this.value)" placeholder="Tulis isi catatan di sini...">${escapeHtml(note.content)}</textarea>
                    <div class="note-footer">
                        <i class="fa-solid fa-trash" onclick="deleteNote(${note.id})" title="Hapus Catatan"></i>
                    </div>
                `;
                grid.appendChild(card);
            });
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        async function addNewNote() {
            const res = await apiNotepad('POST');
            if (res.success) {
                notesData.unshift({ id: res.data.id, title: res.data.judul, content: res.data.isi });
                renderNotes();
            }
        }

        function updateNoteTitle(id, newTitle) {
            const note = notesData.find(n => n.id === id);
            if (note) note.title = newTitle;
            debounceSave(id, { judul: newTitle });
        }

        function updateNoteContent(id, newContent) {
            const note = notesData.find(n => n.id === id);
            if (note) note.content = newContent;
            debounceSave(id, { isi: newContent });
        }

        // Simpan otomatis ke server setelah user berhenti mengetik 600ms,
        // supaya tidak kirim request tiap huruf.
        function debounceSave(id, payload) {
            clearTimeout(debounceTimers[id]);
            debounceTimers[id] = setTimeout(() => {
                apiNotepad('PUT', { id, ...payload });
            }, 600);
        }

        async function deleteNote(id) {
            if(confirm("Yakin ingin menghapus catatan ini?")) {
                await apiNotepad('DELETE', { id });
                notesData = notesData.filter(n => n.id !== id);
                renderNotes();
            }
        }

        window.onload = loadNotes;
    
</script>
<?php include 'includes/footer.php'; ?>
