<?php
require 'includes/auth.php';
requireLogin();

$pageTitle  = "Upload Dokumen - Mansekai";
$activePage = "upload.php";

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
        
        .card { background-color: var(--card-bg); border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-top: 20px; }
        
        .upload-dropzone { border: 2px dashed var(--accent-green-dark); padding: 30px; border-radius: 12px; text-align: center; background: #fafafa; cursor: pointer; transition: 0.2s; margin-bottom: 15px; }
        .upload-dropzone:hover { background: rgba(0,242,195,0.05); }
        .upload-dropzone i { font-size: 2.5rem; color: var(--accent-green-dark); margin-bottom: 10px; }
        .upload-dropzone p { font-size: 0.9rem; color: var(--text-muted); }
        
        .file-input-hidden { display: none; }
        .btn-upload { background-color: var(--accent-green-dark); color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; width: 100%; font-size: 0.9rem; }
        .btn-upload:hover { background-color: #00876b; }

        .file-list { list-style: none; margin-top: 15px; max-height: 300px; overflow-y: auto; }
        .file-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: #fafafa; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid var(--accent-green-dark); font-size: 0.88rem; gap: 10px; }
        .file-info { display: flex; align-items: center; gap: 10px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .file-info i { color: var(--accent-green-dark); font-size: 1.2rem; }
        .file-actions { display: flex; gap: 12px; align-items: center; }
        .btn-action { background: none; border: none; cursor: pointer; font-size: 1rem; color: var(--text-muted); transition: 0.2s; text-decoration: none; }
        .btn-action.fa-eye:hover { color: var(--accent-green-dark); }
        .btn-action.fa-trash:hover { color: #ff4d4d; }

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
            <h1>Upload Dokumen</h1>
            <p style="color: var(--text-muted);">Unggah dan buka dokumen PDF, Word, atau Excel secara langsung.</p>
        </div>

        <div class="card">
            <div class="upload-dropzone" onclick="document.getElementById('fileInput').click()">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <p>Klik di sini atau pilih file untuk diunggah<br><small>Format yang didukung: PDF, DOC, DOCX, XLS, XLSX</small></p>
            </div>
            <input type="file" id="fileInput" class="file-input-hidden" accept=".pdf,.doc,.docx,.xls,.xlsx" onchange="handleFileSelect(event)">
            <button class="btn-upload" onclick="document.getElementById('fileInput').click()"><i class="fa-solid fa-folder-open"></i> Pilih File</button>
        </div>

        <div class="card">
            <h3>Daftar Dokumen Tersimpan</h3>
            <ul class="file-list" id="fileListContainer">
                <!-- Daftar file dimuat secara dinamis -->
            </ul>
        </div>
    
<script>

        // Dokumen sekarang benar-benar diupload & disimpan di server (per user login),
        // bukan lagi base64 di localStorage. File fisik ada di /uploads/documents/{id_user}/
        // dan hanya bisa dibuka lewat api/download.php (dicek kepemilikannya).
        let uploadedFiles = [];

        async function loadFiles() {
            const res = await fetch('api/upload.php');
            const json = await res.json();
            if (json.success) {
                uploadedFiles = json.data;
                renderFiles();
            }
        }

        function renderFiles() {
            const container = document.getElementById('fileListContainer');
            container.innerHTML = '';

            if (uploadedFiles.length === 0) {
                container.innerHTML = '<p style="font-size: 0.85rem; color: var(--text-muted); text-align: center; padding: 15px;">Belum ada dokumen yang diunggah.</p>';
                return;
            }

            uploadedFiles.forEach((file) => {
                let iconClass = "fa-file-lines";
                if (file.ekstensi === 'pdf') iconClass = "fa-file-pdf";
                else if (file.ekstensi === 'xls' || file.ekstensi === 'xlsx') iconClass = "fa-file-excel";
                else if (file.ekstensi === 'doc' || file.ekstensi === 'docx') iconClass = "fa-file-word";

                const ukuranKb = (file.ukuran / 1024).toFixed(0);
                const tanggal = new Date(file.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

                const li = document.createElement('li');
                li.className = 'file-item';
                li.innerHTML = `
                    <div class="file-info">
                        <i class="fa-solid ${iconClass}"></i>
                        <span title="${escapeHtml(file.nama_asli)}"><b>${escapeHtml(file.nama_asli)}</b> <small style="color: var(--text-muted);">(${ukuranKb} KB • ${tanggal})</small></span>
                    </div>
                    <div class="file-actions">
                        <a href="api/download.php?id=${file.id}" target="_blank" class="btn-action fa-solid fa-eye" title="Buka / Preview File"></a>
                        <button class="btn-action fa-solid fa-trash" onclick="deleteFile(${file.id})" title="Hapus"></button>
                    </div>
                `;
                container.appendChild(li);
            });
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        async function handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);

            const res = await fetch('api/upload.php', { method: 'POST', body: formData });
            const json = await res.json();

            if (json.success) {
                uploadedFiles.unshift(json.data);
                renderFiles();
                alert(`File "${file.name}" berhasil diunggah!`);
            } else {
                alert(json.message || 'Gagal mengunggah file.');
            }
            event.target.value = '';
        }

        async function deleteFile(id) {
            if (confirm("Yakin ingin menghapus dokumen ini?")) {
                await fetch('api/upload.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                uploadedFiles = uploadedFiles.filter(f => f.id !== id);
                renderFiles();
            }
        }

        window.onload = loadFiles;
    
</script>
<?php include 'includes/footer.php'; ?>
