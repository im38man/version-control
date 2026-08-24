<?php
require 'includes/auth.php';
requireLogin();

$pageTitle  = "Kalkulator - Mansekai";
$activePage = "kalkulator.php";

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
        main { margin-left: 260px; flex-grow: 1; padding: 30px; background: linear-gradient(135deg, #f9f1f5 0%, #e8e2eb 100%); min-height: 100vh; width: calc(100% - 260px); display: flex; flex-direction: column; align-items: center; transition: all 0.3s ease; }
        .header-title { width: 100%; text-align: center; margin-bottom: 20px; }
        .header-title h1 { font-size: 1.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; }
        .card-calc { background-color: var(--card-bg); border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); width: 100%; max-width: 320px; }
        .calc-display { width: 100%; height: 50px; background: #11131d; color: var(--accent-green); font-size: 1.5rem; font-weight: 600; text-align: right; padding: 8px 15px; border-radius: 8px; margin-bottom: 15px; overflow: hidden; }
        .calc-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
        .calc-btn { padding: 14px; background: #f0f0f5; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem; transition: 0.2s; }
        .calc-btn:hover { background: #e0e0eb; }
        .calc-btn.op { background: rgba(0, 242, 195, 0.2); color: #00876b; }
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
            <h1>Kalkulator Belajar</h1>
            <p style="color: var(--text-muted);">Hitung rumus atau angka keperluan studi Anda.</p>
        </div>
        <div class="card-calc">
            <div class="calc-display" id="calcDisplay">0</div>
            <div class="calc-grid">
                <button class="calc-btn" onclick="calcClear()">C</button>
                <button class="calc-btn" onclick="calcAppend('/')">/</button>
                <button class="calc-btn" onclick="calcAppend('*')">*</button>
                <button class="calc-btn" onclick="calcDelete()">⌫</button>
                <button class="calc-btn" onclick="calcAppend('7')">7</button>
                <button class="calc-btn" onclick="calcAppend('8')">8</button>
                <button class="calc-btn" onclick="calcAppend('9')">9</button>
                <button class="calc-btn op" onclick="calcAppend('-')">-</button>
                <button class="calc-btn" onclick="calcAppend('4')">4</button>
                <button class="calc-btn" onclick="calcAppend('5')">5</button>
                <button class="calc-btn" onclick="calcAppend('6')">6</button>
                <button class="calc-btn op" onclick="calcAppend('+')">+</button>
                <button class="calc-btn" onclick="calcAppend('1')">1</button>
                <button class="calc-btn" onclick="calcAppend('2')">2</button>
                <button class="calc-btn" onclick="calcAppend('3')">3</button>
                <button class="calc-btn op" onclick="calcCalculate()">=</button>
                <button class="calc-btn" onclick="calcAppend('0')" style="grid-column: span 2;">0</button>
                <button class="calc-btn" onclick="calcAppend('.')">.</button>
            </div>
        </div>
    
<script>

        let calcInput = "";
        function calcAppend(val) {
            if(calcInput === "0" && val !== ".") calcInput = "";
            calcInput += val;
            document.getElementById('calcDisplay').textContent = calcInput;
        }
        function calcClear() { calcInput = ""; document.getElementById('calcDisplay').textContent = "0"; }
        function calcDelete() {
            calcInput = calcInput.slice(0, -1);
            document.getElementById('calcDisplay').textContent = calcInput === "" ? "0" : calcInput;
        }
        function calcCalculate() {
            try {
                calcInput = eval(calcInput).toString();
                document.getElementById('calcDisplay').textContent = calcInput;
            } catch (e) {
                document.getElementById('calcDisplay').textContent = "Error";
                calcInput = "";
            }
        }
    
</script>
<?php include 'includes/footer.php'; ?>
