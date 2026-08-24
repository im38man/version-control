<?php require_once __DIR__ . '/includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nusantara Beans - Kopi Premium Indonesia</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 512 512%22><path fill=%22%23C5A059%22 d=%22M96 96c0-35.3 28.7-64 64-64H384c35.3 0 64 28.7 64 64v32H96V96zM48 224h416c26.5 0 48 21.5 48 48v32c0 53-43 96-96 96H160c-53 0-96-43-96-96V272c0-26.5 21.5-48 48-48zm352 160h32c35.3 0 64-28.7 64-64V288H432v96zM32 448H480c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32z%22/></svg>">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ================= VARIABEL TEMA ELEGAN ================= */
        :root {
            --color-coffee: #2C1E16; 
            --color-gold: #C5A059;   
            --color-gold-hover: #D4B572;
            --color-cream: #F8F5F0;  
            --color-text: #333333;
            --color-white: #FFFFFF;
            --font-heading: 'Playfair Display', serif;
            --font-body: 'Poppins', sans-serif;
        }

        /* ================= RESET & DASAR ================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html {
            scroll-behavior: smooth;
        }
        body {
            font-family: var(--font-body);
            background-color: var(--color-cream);
            color: var(--color-text);
            overflow-x: hidden;
        }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }

        /* ================= NAVBAR ================= */
        .navbar {
            background-color: var(--color-coffee);
            color: var(--color-gold);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 5%;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        
        /* Modifikasi Text Logo & Tagline */
        .navbar-brand-wrapper {
            display: flex;
            flex-direction: column;
        }
        .navbar-brand {
            font-family: var(--font-heading);
            font-size: clamp(18px, 3.5vw, 22px);
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--color-gold);
            line-height: 1.1;
        }
        .navbar-tagline {
            font-size: clamp(8px, 1.5vw, 10px);
            font-weight: 400;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.75);
            margin-top: 2px;
        }
        
        .navbar-right {
            display: flex;
            align-items: center;
            gap: clamp(10px, 3vw, 20px);
        }
        
        .burger-container {
            position: relative;
        }
        .burger-btn {
            font-size: clamp(18px, 3vw, 22px);
            cursor: pointer;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            border-right: 1px solid rgba(197, 160, 89, 0.3);
            padding-right: 15px;
        }
        .burger-btn:hover { color: var(--color-white); }
        
        .social-dropdown {
            position: absolute;
            top: 40px;
            right: 0;
            background: var(--color-coffee);
            border: 1px solid var(--color-gold);
            border-radius: 8px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 101;
        }
        .social-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .social-dropdown i {
            font-size: 18px;
            cursor: pointer;
            transition: color 0.3s;
        }
        .social-dropdown i:hover { color: var(--color-white); }

        .navbar-icons {
            display: flex;
            gap: clamp(12px, 2.5vw, 20px);
            align-items: center;
        }
        .navbar-icons i {
            font-size: clamp(16px, 2.5vw, 18px);
            cursor: pointer;
            transition: color 0.3s;
        }
        .navbar-icons i:hover { color: var(--color-white); }

        /* ================= SEARCH DROPDOWN BAWAH NAVBAR (TANPA EFEK GELAP) ================= */
        .search-dropdown-bar {
            position: fixed;
            top: 55px; /* Tepat di bawah navbar */
            left: 0;
            width: 100%;
            background: var(--color-coffee);
            border-bottom: 2px solid var(--color-gold);
            padding: 15px 5%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
            z-index: 99;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-15px);
            transition: all 0.3s ease-in-out;
        }
        .search-dropdown-bar.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .search-bar-inner {
            display: flex;
            width: 100%;
            max-width: 600px;
            background: var(--color-white);
            border-radius: 30px;
            overflow: hidden;
            border: 2px solid var(--color-gold);
        }
        .search-bar-input {
            flex: 1;
            padding: 10px 20px;
            border: none;
            outline: none;
            font-family: var(--font-body);
            font-size: 14px;
            color: var(--color-text);
        }
        .search-bar-btn {
            background: var(--color-gold);
            color: var(--color-white);
            border: none;
            padding: 0 20px;
            cursor: pointer;
            font-size: 15px;
            transition: background 0.3s;
        }
        .search-bar-btn:hover {
            background: var(--color-coffee);
            color: var(--color-gold);
        }

        /* ================= HERO SECTION ================= */
        .hero {
            position: relative;
            height: 100vh;
            width: 100%;
            overflow: hidden;
            background-color: var(--color-coffee);
            margin-top: 55px;
        }
        .hero-slide {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .hero-slide.active { opacity: 1; z-index: 10; }
        .hero-slide::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1;
        }
        .hero-slide img {
            position: absolute;
            width: 100%; height: 100%;
            object-fit: cover;
            z-index: 0;
        }
        .hero-content {
            position: relative;
            z-index: 2;
            color: var(--color-white);
            max-width: 800px;
            padding: 0 20px;
        }
        .hero-content h1 {
            font-family: var(--font-heading);
            font-size: clamp(28px, 6vw, 48px);
            margin-bottom: 15px;
            color: var(--color-gold);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .hero-content p {
            font-size: clamp(13px, 2vw, 18px);
            font-weight: 300;
            letter-spacing: 0.5px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .hero-btn {
            display: inline-block;
            padding: 10px 24px;
            font-size: clamp(13px, 1.5vw, 16px);
            font-weight: 500;
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }

        /* ================= SECTION HEADER & UTILITIES ================= */
        .section-container {
            padding: 60px 4%;
            max-width: 1300px;
            margin: 0 auto;
        }
        .section-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 25px;
            padding: 0 10px;
        }
        .section-title {
            font-family: var(--font-heading);
            font-size: clamp(24px, 4vw, 36px);
            color: var(--color-coffee);
            margin-bottom: 0;
            text-align: left;
        }
        .section-title span { color: var(--color-gold); }

        .view-all-top {
            font-size: clamp(13px, 1.5vw, 15px);
            font-weight: 600;
            color: var(--color-gold);
            transition: color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }
        .view-all-top:hover { color: var(--color-coffee); }

        .view-all-bottom-container {
            text-align: center;
            margin-top: 30px;
        }
        .btn-view-all {
            display: inline-block;
            padding: 10px 28px;
            background: transparent;
            border: 2px solid var(--color-gold);
            color: var(--color-coffee);
            font-weight: 600;
            font-size: 14px;
            border-radius: 30px;
            transition: all 0.3s ease;
        }
        .btn-view-all:hover {
            background: var(--color-gold);
            color: var(--color-white);
            transform: translateY(-2px);
        }

        /* ================= SLIDER & INDICATOR CUSTOM ================= */
        .slider-wrapper {
            position: relative;
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        .slider-container-inner {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .slider-nav-btn {
            background: var(--color-gold);
            color: var(--color-white);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            position: absolute;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        .slider-nav-btn:hover { background: var(--color-coffee); }
        .slider-nav-btn.prev { left: -20px; }
        .slider-nav-btn.next { right: -20px; }

        .universal-slider {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding: 15px 5px;
            scroll-behavior: smooth;
            -ms-overflow-style: none;
            scrollbar-width: none;
            cursor: grab;
            width: 100%;
            scroll-snap-type: x mandatory;
        }
        .universal-slider:active { cursor: grabbing; }
        .universal-slider::-webkit-scrollbar { display: none; }

        .slider-indicators {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .slider-dots {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(197, 160, 89, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dot.active {
            width: 24px;
            border-radius: 4px;
            background: var(--color-gold);
        }

        .slider-progress-bar {
            width: 120px;
            height: 4px;
            background: rgba(197, 160, 89, 0.2);
            border-radius: 2px;
            overflow: hidden;
        }

        .slider-progress-fill {
            height: 100%;
            width: 0%;
            background: var(--color-gold);
            transition: width 0.1s ease-out;
        }

        /* ================= BLOG CARD ================= */
        .blog-card {
            min-width: 280px;
            max-width: 320px;
            flex: 0 0 auto;
            background: var(--color-cream);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: left;
            transition: transform 0.3s ease;
            border: 1px solid rgba(197, 160, 89, 0.2);
            user-select: none;
            display: flex;
            flex-direction: column;
            scroll-snap-align: start;
        }
        .blog-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }

        .blog-img-wrapper {
            position: relative;
            width: 100%;
            height: 180px;
            overflow: hidden;
        }
        .blog-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .blog-card:hover .blog-img { transform: scale(1.05); }
        .blog-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--color-gold);
            color: var(--color-white);
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 500;
            border-radius: 4px;
            z-index: 2;
        }
        .blog-info {
            padding: 18px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .blog-date {
            font-size: 11px;
            color: #888;
            margin-bottom: 6px;
        }
        .blog-title {
            font-family: var(--font-heading);
            font-size: 18px;
            color: var(--color-coffee);
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .blog-desc {
            font-size: 12px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
            flex-grow: 1;
        }

        /* ================= PRODUCT CARD ================= */
        .product-card {
            min-width: 280px;
            max-width: 300px;
            flex: 0 0 auto;
            background: var(--color-white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: left;
            transition: transform 0.3s ease;
            border: 1px solid rgba(197, 160, 89, 0.2);
            user-select: none;
            display: flex;
            flex-direction: column;
            scroll-snap-align: start;
        }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        
        .product-img-wrapper {
            position: relative;
            width: 100%;
            height: 200px;
            background-color: var(--color-white);
            cursor: zoom-in;
            overflow: hidden;
        }
        .product-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 15px;
            transition: transform 0.3s ease;
        }
        .product-img-wrapper:hover .product-img { transform: scale(1.05); }

        .product-badge-top {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #d9534f;
            color: var(--color-white);
            font-size: 10px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 3;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .product-label-corner {
            position: absolute;
            top: 22px;
            right: -35px;
            background: var(--color-coffee);
            color: var(--color-gold);
            font-size: 9px;
            font-weight: 600;
            width: 130px;
            text-align: center;
            padding: 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transform: rotate(45deg);
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            border-top: 1px solid rgba(197, 160, 89, 0.4);
            border-bottom: 1px solid rgba(197, 160, 89, 0.4);
            pointer-events: none;
        }

        .product-info { padding: 18px; display: flex; flex-direction: column; flex-grow: 1; }
        .product-title {
            font-family: var(--font-heading);
            font-size: 19px;
            color: var(--color-coffee);
            margin-bottom: 5px;
        }
        
        .product-price {
            font-size: 15px;
            font-weight: 600;
            color: var(--color-gold);
            margin-bottom: 10px;
        }

        .product-bio {
            font-size: 13px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
            flex-grow: 1;
        }
        
        .product-actions { 
            display: flex; 
            gap: 8px; 
            margin-top: auto; 
            flex-wrap: nowrap;
        }
        
        .btn {
            flex: 1;
            padding: 8px 6px;
            border: none;
            border-radius: 4px;
            font-family: var(--font-body);
            font-weight: 500;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--color-gold);
            color: var(--color-coffee);
        }
        .btn-outline:hover { background: var(--color-gold); color: var(--color-white); }
        .btn-solid {
            background: var(--color-gold);
            color: var(--color-white);
        }
        .btn-solid:hover { background: var(--color-coffee); }

        /* ================= TESTIMONI SLIDER ================= */
        .testimonial-section {
            background-color: var(--color-coffee);
            color: var(--color-white);
            padding: 60px 5%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .testimonial-section .section-title { color: var(--color-gold); margin-bottom: 30px; text-align: center; }
        .testi-container { position: relative; height: 140px; }
        .testi-slide {
            position: absolute;
            top: 0; left: 0; width: 100%;
            opacity: 0;
            transition: opacity 1s ease;
        }
        .testi-slide.active { opacity: 1; }
        .testi-text {
            font-family: var(--font-heading);
            font-size: clamp(16px, 3vw, 20px);
            font-style: italic;
            margin-bottom: 12px;
            color: #F8F5F0;
        }
        .testi-author { font-size: 13px; color: var(--color-gold); font-weight: 600; }

        /* ================= FOOTER ================= */
        .site-footer {
            background: #1A120D;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            padding: 50px 5% 20px;
        }
        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 35px;
        }
        .footer-col h4 {
            font-family: var(--font-heading);
            color: var(--color-gold);
            font-size: 18px;
            margin-bottom: 15px;
        }
        .footer-brand {
            font-family: var(--font-heading);
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--color-white);
            margin-bottom: 12px;
        }
        .footer-brand span { color: var(--color-gold); }
        .footer-desc { margin-bottom: 15px; line-height: 1.6; }
        
        .footer-socials {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .footer-socials a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(197, 160, 89, 0.3);
            border-radius: 50%;
            color: var(--color-white);
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .footer-socials a:hover {
            background: var(--color-gold);
            color: var(--color-coffee);
            transform: translateY(-3px);
        }

        .footer-contact li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
            line-height: 1.5;
        }
        .footer-contact i { color: var(--color-gold); font-size: 15px; margin-top: 3px; }

        .subscribe-form { display: flex; align-items: center; }
        .subscribe-form input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid rgba(197, 160, 89, 0.4);
            background: rgba(255, 255, 255, 0.05);
            color: var(--color-white);
            font-family: var(--font-body);
            border-radius: 4px 0 0 4px;
            outline: none;
            font-size: 13px;
        }
        .subscribe-form button {
            padding: 10px 16px;
            border-radius: 0 4px 4px 0;
            border: 1px solid var(--color-gold);
            height: 100%;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .footer-bottom span { color: var(--color-gold); }

        /* ================= MODAL STANDARD (DETAIL & ZOOM) ================= */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(26, 18, 13, 0.85);
            backdrop-filter: blur(8px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            padding: 20px;
        }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        
        .close-modal {
            position: absolute; 
            top: 20px; right: 25px; 
            font-size: 26px;
            color: var(--color-gold); 
            cursor: pointer;
            z-index: 1001;
            background: rgba(44, 30, 22, 0.6);
            width: 40px; height: 40px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.3s;
            border: 1px solid rgba(197, 160, 89, 0.3);
        }
        .close-modal:hover { background: var(--color-gold); color: var(--color-white); transform: rotate(90deg); }

        .product-modal-content {
            background: var(--color-cream); 
            width: 100%; 
            max-width: 750px;
            border-radius: 12px; 
            overflow: hidden; 
            position: relative;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            transform: translateY(30px) scale(0.95);
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid var(--color-gold);
        }
        .modal-overlay.active .product-modal-content {
            transform: translateY(0) scale(1);
        }

        .product-modal-img-container {
            background-color: var(--color-white);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            border-right: 1px solid rgba(197, 160, 89, 0.2);
        }
        .product-modal-img { 
            width: 100%; 
            max-height: 320px; 
            object-fit: contain; 
        }
        .product-modal-body { 
            padding: 30px; 
            display: flex; 
            flex-direction: column;
            justify-content: center;
        }
        .product-modal-title {
            font-family: var(--font-heading); 
            color: var(--color-coffee);
            font-size: clamp(22px, 3vw, 28px); 
            margin-bottom: 12px;
        }
        .product-modal-desc { 
            font-size: 13.5px; 
            color: #555; 
            line-height: 1.7; 
            margin-bottom: 25px; 
        }

        .image-modal {
            background: rgba(0, 0, 0, 0.95);
            padding: 20px;
        }
        .fullscreen-img {
            max-width: 92%;
            max-height: 88vh;
            object-fit: contain;
            border-radius: 6px;
            box-shadow: 0 0 35px rgba(197, 160, 89, 0.2);
            animation: zoomInModal 0.3s ease;
        }

        @keyframes zoomInModal {
            from { transform: scale(0.85); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @media (max-width: 768px) {
            .slider-nav-btn { display: none; }
            .product-modal-content {
                grid-template-columns: 1fr;
                max-width: 90%;
                max-height: 85vh;
                overflow-y: auto;
            }
            .product-modal-img-container {
                height: 200px;
                border-right: none;
                border-bottom: 1px solid rgba(197, 160, 89, 0.2);
            }
            .product-modal-body { padding: 20px; }
            .section-header-flex { flex-direction: column; align-items: flex-start; gap: 8px; }
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>
    <!-- HERO SECTION -->
    <header class="hero">
        <div class="hero-slide active">
            <img src="https://images.unsplash.com/photo-1511920170033-f8396924c348?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Kopi Gayo">
            <div class="hero-content">
                <h1>Biji Kopi Gayo Premium</h1>
                <p>Kualitas ekspor terbaik langsung dari pegunungan Aceh Tengah. Cita rasa kuat dengan sentuhan rempah Nusantara.</p>
                <a href="#produk" class="btn btn-solid hero-btn">Lihat Produk Kami</a>
            </div>
        </div>
        <div class="hero-slide">
            <img src="https://images.unsplash.com/photo-1611162458324-aae1eb4129a4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Roasting">
            <div class="hero-content">
                <h1>Dipanggang Sempurna</h1>
                <p>Proses roasting eksklusif oleh Artisan Roaster kami untuk mengeluarkan aroma elegan yang memikat jiwa.</p>
                <a href="#produk" class="btn btn-solid hero-btn">Lihat Produk Kami</a>
            </div>
        </div>
        <div class="hero-slide">
            <img src="https://images.unsplash.com/photo-1497935586351-b67a49e012bf?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Luwak Kopi">
            <div class="hero-content">
                <h1>Kopi Luwak Liar Sumatera</h1>
                <p>Eksotisme rasa yang tiada duanya. Halus, rendah asam, dan menyimpan kemewahan dalam setiap tegukannya.</p>
                <a href="#produk" class="btn btn-solid hero-btn">Lihat Produk Kami</a>
            </div>
        </div>
    </header>

    <!-- ================= BLOG SLIDER SECTION ================= -->
    <section class="section-container blog-section">
        <div class="section-header-flex">
            <h2 class="section-title">Wawasan <span>Cerita Kopi</span></h2>
            <a href="blogspot.php" class="view-all-top">Lihat Semuanya -></a>
        </div>
        
        <div class="slider-wrapper">
            <div class="slider-container-inner">
                <button class="slider-nav-btn prev" id="btnPrevBlog"><i class="fas fa-chevron-left"></i></button>

                <div class="universal-slider" id="blogSlider">
                    <!-- Blog Card 1 -->
                    <div class="blog-card">
                        <div class="blog-img-wrapper">
                            <span class="blog-badge">Edukasi</span>
                            <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Tips Seduh Kopi" class="blog-img">
                        </div>
                        <div class="blog-info">
                            <span class="blog-date"><i class="far fa-calendar-alt"></i> 12 Juni 2026</span>
                            <h3 class="blog-title">Mengenal Metode Seduh Manual Brew V60</h3>
                            <p class="blog-desc">Pelajari bagaimana teknik tuang dan suhu air dapat memengaruhi kejernihan rasa serta keasaman kopi Anda di rumah.</p>
                            <a href="blog-wawasan-cerita-kopi-mengenal-metode-seduh-manual-brew-v60.php" class="btn btn-outline"><i class="fas fa-external-link-alt"></i> Baca Selengkapnya</a>
                        </div>
                    </div>
                    <!-- Blog Card 2 -->
                    <div class="blog-card">
                        <div class="blog-img-wrapper">
                            <span class="blog-badge">Petani</span>
                            <img src="https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Petani Gayo" class="blog-img">
                        </div>
                        <div class="blog-info">
                            <span class="blog-date"><i class="far fa-calendar-alt"></i> 08 Juni 2026</span>
                            <h3 class="blog-title">Perjalanan Biji Kopi dari Gayo ke Cangkir Anda</h3>
                            <p class="blog-desc">Mengintip dedikasi petani dataran tinggi Aceh Tengah dalam merawat pohon kopi hingga proses pascapanen terbaik.</p>
                            <a href="blog-perjalanan-biji-kopi-dari-gayo-ke-cangkir-anda.php" class="btn btn-outline"><i class="fas fa-external-link-alt"></i> Baca Selengkapnya</a>
                        </div>
                    </div>
                    <!-- Blog Card 3 -->
                    <div class="blog-card">
                        <div class="blog-img-wrapper">
                            <span class="blog-badge">Tips</span>
                            <img src="https://images.unsplash.com/photo-1507133750043-4a8f6beae2f7?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Simpan Kopi" class="blog-img">
                        </div>
                        <div class="blog-info">
                            <span class="blog-date"><i class="far fa-calendar-alt"></i> 01 Juni 2026</span>
                            <h3 class="blog-title">Cara Menyimpan Biji Kopi agar Aroma Tetap Awet</h3>
                            <p class="blog-desc">Jangan salah langkah! Ketahui wadah kedap udara dan suhu ideal yang tepat agar kesegaran biji kopi bertahan lama.</p>
                            <a href="blog-cara-menyimpan-biji-kopi-agar-aroma-tetap-awet.php" class="btn btn-outline"><i class="fas fa-external-link-alt"></i> Baca Selengkapnya</a>
                        </div>
                    </div>
                    <!-- Blog Card 4 -->
                    <div class="blog-card">
                        <div class="blog-img-wrapper">
                            <span class="blog-badge">Roasting</span>
                            <img src="https://images.unsplash.com/photo-1511920170033-f8396924c348?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Roasting Profile" class="blog-img">
                        </div>
                        <div class="blog-info">
                            <span class="blog-date"><i class="far fa-calendar-alt"></i> 25 Mei 2026</span>
                            <h3 class="blog-title">Perbedaan Light, Medium, dan Dark Roast</h3>
                            <p class="blog-desc">Kenali karakteristik profil tingkat pemanggangan dan temukan mana tingkat kematangan yang paling cocok dengan selera lidah Anda.</p>
                            <a href="blog-perbedaan-light-medium-dan-dark-roast.php" class="btn btn-outline"><i class="fas fa-external-link-alt"></i> Baca Selengkapnya</a>
                        </div>
                    </div>
                </div>

                <button class="slider-nav-btn next" id="btnNextBlog"><i class="fas fa-chevron-right"></i></button>
            </div>

            <div class="slider-indicators">
                <div class="slider-dots" id="dotsBlog"></div>
                <div class="slider-progress-bar">
                    <div class="slider-progress-fill" id="progressBlog"></div>
                </div>
            </div>
        </div>

        <div class="view-all-bottom-container">
            <a href="blogspot.php" class="btn-view-all">Lihat Semuanya</a>
        </div>
    </section>

    <!-- SECTION 1: ROASTED BEANS SLIDER -->
    <section class="section-container products-section" id="produk">
        <div class="section-header-flex">
            <h2 class="section-title">Koleksi <span>Eksklusif</span> Kami</h2>
            <a href="all-product.php" class="view-all-top">Lihat Semuanya -></a>
        </div>
        
        <div class="slider-wrapper">
            <div class="slider-container-inner">
                <button class="slider-nav-btn prev" id="btnPrev"><i class="fas fa-chevron-left"></i></button>

                <div class="universal-slider" id="productSlider">
                    <!-- Card 1 -->
                    <div class="product-card">
                        <div class="product-img-wrapper img-zoom-trigger" data-img="assets/img/arabika-1536x1536.webp" data-title="Arabica Premium">
                            <span class="product-badge-top">Best Seller</span>
                            <span class="product-label-corner">Single Origin</span>
                            <img src="assets/img/arabika-1536x1536.webp" alt="Arabica Premium" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">Arabica Premium</h3>
                            <div class="product-price">Rp 120.000 /kg</div>
                            <p class="product-bio">Kopi Arabica Gayo low acidity & full body. Memiliki sentuhan khas rempah, cokelat pekat, dan hint karamel.</p>
                            <div class="product-actions">
                                <button class="btn btn-outline btn-detail" data-title="Arabica Premium" data-desc="Kopi Arabica Gayo ditanam di ketinggian 1.200 - 1.500 mdpl di dataran tinggi Gayo, Aceh. Memiliki keasaman rendah dengan body tebal. Terdapat sentuhan rempah, cokelat pekat, dan hint karamel." data-img="https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"><i class="fas fa-external-link-alt"></i> Detail</button>
                                <button class="btn btn-solid"><i class="fas fa-cart-plus"></i> Checkout</button>
                            </div>
                        </div>
                    </div>
                    <!-- Card 2 -->
                    <div class="product-card">
                        <div class="product-img-wrapper img-zoom-trigger" data-img="assets/img/fullwash-1122×1402.webp" data-title="Arabica Full Wash">
                            <span class="product-label-corner">Full Wash</span>
                            <img src="assets/img/fullwash-1122×1402.webp" alt="Arabica Full Wash" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">Arabica Full Wash</h3>
                            <div class="product-price">Rp 135.000 /kg</div>
                            <p class="product-bio">Proses Full Wash menghasilkan cita rasa kopi yang sangat bersih dengan tingkat keasaman cerah dan segar.</p>
                            <div class="product-actions">
                                <button class="btn btn-outline btn-detail" data-title="Arabica Full Wash" data-desc="Proses Full Wash menghasilkan cita rasa kopi yang sangat bersih dengan tingkat keasaman (acidity) yang lebih cerah dan segar." data-img="https://images.unsplash.com/photo-1559525839-b184a4d698c7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"><i class="fas fa-external-link-alt"></i> Detail</button>
                                <button class="btn btn-solid"><i class="fas fa-cart-plus"></i> Checkout</button>
                            </div>
                        </div>
                    </div>
                    <!-- Card 3 -->
                    <div class="product-card">
                        <div class="product-img-wrapper img-zoom-trigger" data-img="assets/img/natural-1122x1402.webp" data-title="Arabica Natural">
                            <span class="product-badge-top">Favorit</span>
                            <span class="product-label-corner">Natural</span>
                            <img src="assets/img/natural-1122x1402.webp" alt="Arabica Natural" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">Arabica Natural</h3>
                            <div class="product-price">Rp 140.000 /kg</div>
                            <p class="product-bio">Penjemuran utuh bersama ceri memunculkan notes buah-buahan tropis serta rasa manis alami yang sangat kuat.</p>
                            <div class="product-actions">
                                <button class="btn btn-outline btn-detail" data-title="Arabica Natural" data-desc="Diproses secara natural dengan penjemuran utuh bersama ceri kopi, menghasilkan notes buah-buahan tropis dan manis alami yang kuat." data-img="https://images.unsplash.com/photo-1611162458324-aae1eb4129a4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"><i class="fas fa-external-link-alt"></i> Detail</button>
                                <button class="btn btn-solid"><i class="fas fa-cart-plus"></i> Checkout</button>
                            </div>
                        </div>
                    </div>
                    <!-- Card 4 -->
                    <div class="product-card">
                        <div class="product-img-wrapper img-zoom-trigger" data-img="assets/img/yellowhoney-1149x1369.webp" data-title="Arabica Yellow Honey">
                            <span class="product-label-corner">Honey Process</span>
                            <img src="assets/img/yellowhoney-1149x1369.webp" alt="Arabica Yellow Honey" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">Yellow Honey</h3>
                            <div class="product-price">Rp 145.000 /kg</div>
                            <p class="product-bio">Keseimbangan sempurna antara keasaman dan kemanisan, dilengkapi aroma floral dengan sentuhan madu ringan.</p>
                            <div class="product-actions">
                                <button class="btn btn-outline btn-detail" data-title="Arabica Yellow Honey" data-desc="Proses Yellow Honey menyeimbangkan keasaman dan kemanisan. Memberikan aroma floral dengan sentuhan madu ringan." data-img="https://images.unsplash.com/photo-1511920170033-f8396924c348?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"><i class="fas fa-external-link-alt"></i> Detail</button>
                                <button class="btn btn-solid"><i class="fas fa-cart-plus"></i> Checkout</button>
                            </div>
                        </div>
                    </div>
                    <!-- Card 5 -->
                    <div class="product-card">
                        <div class="product-img-wrapper img-zoom-trigger" data-img="assets/img/redhoney-1149x1369.webp" data-title="Arabica Red Honey">
                            <span class="product-label-corner">Red Honey</span>
                            <img src="assets/img/redhoney-1149x1369.webp" alt="Arabica Red Honey" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">Red Honey</h3>
                            <div class="product-price">Rp 150.000 /kg</div>
                            <p class="product-bio">Tingkat mucilage yang lebih tebal menghasilkan rasa yang jauh lebih manis dari Yellow Honey dengan karakter sirup.</p>
                            <div class="product-actions">
                                <button class="btn btn-outline btn-detail" data-title="Arabica Red Honey" data-desc="Tingkat mucilage tebal menghasilkan rasa manis menyerupai sirup." data-img="https://images.unsplash.com/photo-1511920170033-f8396924c348?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"><i class="fas fa-external-link-alt"></i> Detail</button>
                                <button class="btn btn-solid"><i class="fas fa-cart-plus"></i> Checkout</button>
                            </div>
                        </div>
                    </div>
                    <!-- Card 6 -->
                    <div class="product-card">
                        <div class="product-img-wrapper img-zoom-trigger" data-img="assets/img/blackhoney-1149×1369.webp" data-title="Arabica Black Honey">
                            <span class="product-badge-top">Limited</span>
                            <span class="product-label-corner">Black Honey</span>
                            <img src="assets/img/blackhoney-1149×1369.webp" alt="Arabica Black Honey" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">Black Honey</h3>
                            <div class="product-price">Rp 160.000 /kg</div>
                            <p class="product-bio">Menghasilkan profil kopi kental dengan tingkat kemanisan tertinggi serta kekayaan rasa cokelat yang sangat pekat.</p>
                            <div class="product-actions">
                                <button class="btn btn-outline btn-detail" data-title="Arabica Black Honey" data-desc="Profil kopi kental dengan tingkat kemanisan tertinggi serta kekayaan rasa cokelat pekat." data-img="https://images.unsplash.com/photo-1511920170033-f8396924c348?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"><i class="fas fa-external-link-alt"></i> Detail</button>
                                <button class="btn btn-solid"><i class="fas fa-cart-plus"></i> Checkout</button>
                            </div>
                        </div>
                    </div>
                    <!-- Card 7 -->
                    <div class="product-card">
                        <div class="product-img-wrapper img-zoom-trigger" data-img="assets/img/wine-1197x1315.webp" data-title="Arabica Wine">
                            <span class="product-badge-top">Exclusive</span>
                            <span class="product-label-corner">Wine Process</span>
                            <img src="assets/img/wine-1197x1315.webp" alt="Arabica Wine" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">Arabica Wine</h3>
                            <div class="product-price">Rp 185.000 /kg</div>
                            <p class="product-bio">Difermentasi secara khusus (non-alkohol) untuk memunculkan aroma dan cita rasa anggur yang sangat eksotis.</p>
                            <div class="product-actions">
                                <button class="btn btn-outline btn-detail" data-title="Arabica Wine" data-desc="Difermentasi secara khusus (tanpa alkohol) untuk memunculkan aroma dan rasa anggur yang sangat eksotis dan tajam." data-img="https://images.unsplash.com/photo-1497935586351-b67a49e012bf?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"><i class="fas fa-external-link-alt"></i> Detail</button>
                                <button class="btn btn-solid"><i class="fas fa-cart-plus"></i> Checkout</button>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="slider-nav-btn next" id="btnNext"><i class="fas fa-chevron-right"></i></button>
            </div>

            <div class="slider-indicators">
                <div class="slider-dots" id="dotsProduct"></div>
                <div class="slider-progress-bar">
                    <div class="slider-progress-fill" id="progressProduct"></div>
                </div>
            </div>
        </div>

        <div class="view-all-bottom-container">
            <a href="all-product.php" class="btn-view-all">Lihat Semuanya</a>
        </div>
    </section>

    <!-- SECTION 2: GREEN BEANS SLIDER -->
    <section class="section-container products-section" id="green-beans" style="background-color: var(--color-white); border-radius: 12px; margin-bottom: 40px;">
        <div class="section-header-flex">
            <h2 class="section-title">Koleksi <span>Green Beans</span> Kami</h2>
            <a href="all-product.php" class="view-all-top">Lihat Semuanya -></a>
        </div>
        
        <div class="slider-wrapper">
            <div class="slider-container-inner">
                <button class="slider-nav-btn prev" id="btnPrevGB"><i class="fas fa-chevron-left"></i></button>

                <div class="universal-slider" id="productSliderGB">
                    <!-- Card GB 1 -->
                    <div class="product-card">
                        <div class="product-img-wrapper img-zoom-trigger" data-img="https://images.unsplash.com/photo-1559525839-b184a4d698c7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" data-title="GB Gayo Full Wash">
                            <span class="product-badge-top">Best Seller</span>
                            <span class="product-label-corner">Full Wash</span>
                            <img src="https://images.unsplash.com/photo-1559525839-b184a4d698c7?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Green Beans Gayo" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">GB Gayo Full Wash</h3>
                            <div class="product-price">Rp 95.000 /kg</div>
                            <p class="product-bio">Biji mentah Gayo Full Wash dengan kadar air ideal, siap masuk mesin roasting untuk menemani kreasi profil Anda.</p>
                            <div class="product-actions">
                                <button class="btn btn-outline btn-detail" data-title="Green Beans Gayo Full Wash" data-desc="Biji kopi mentah (green beans) asal Gayo dengan proses Full Wash. Kadar air ideal, siap untuk di-roasting dengan profil favorit Anda." data-img="https://images.unsplash.com/photo-1559525839-b184a4d698c7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"><i class="fas fa-external-link-alt"></i> Detail</button>
                                <button class="btn btn-solid"><i class="fas fa-cart-plus"></i> Checkout</button>
                            </div>
                        </div>
                    </div>
                    <!-- Card GB 2 -->
                    <div class="product-card">
                        <div class="product-img-wrapper img-zoom-trigger" data-img="https://images.unsplash.com/photo-1511920170033-f8396924c348?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" data-title="GB Gayo Natural">
                            <span class="product-label-corner">Natural</span>
                            <img src="https://images.unsplash.com/photo-1511920170033-f8396924c348?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Green Beans Natural" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">GB Gayo Natural</h3>
                            <div class="product-price">Rp 105.000 /kg</div>
                            <p class="product-bio">Pilihan tepat untuk para roaster yang ingin memunculkan notes fruity dan acidity kompleks dalam racikan kopinya.</p>
                            <div class="product-actions">
                                <button class="btn btn-outline btn-detail" data-title="Green Beans Gayo Natural" data-desc="Green beans diproses secara natural. Cocok untuk roaster yang ingin memunculkan notes fruity dan acidity yang kompleks." data-img="https://images.unsplash.com/photo-1511920170033-f8396924c348?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"><i class="fas fa-external-link-alt"></i> Detail</button>
                                <button class="btn btn-solid"><i class="fas fa-cart-plus"></i> Checkout</button>
                            </div>
                        </div>
                    </div>
                    <!-- Card GB 3 -->
                    <div class="product-card">
                        <div class="product-img-wrapper img-zoom-trigger" data-img="https://images.unsplash.com/photo-1611162458324-aae1eb4129a4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" data-title="GB Gayo Honey">
                            <span class="product-label-corner">Honey Process</span>
                            <img src="https://images.unsplash.com/photo-1611162458324-aae1eb4129a4?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Green Beans Honey" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">GB Gayo Honey</h3>
                            <div class="product-price">Rp 110.000 /kg</div>
                            <p class="product-bio">Hasil sortasi terbaik tanpa cacat (defect). Menjanjikan rasa sweet dan balance yang pas saat diseduh nanti.</p>
                            <div class="product-actions">
                                <button class="btn btn-outline btn-detail" data-title="Green Beans Gayo Honey" data-desc="Green beans dengan proses Honey. Kualitas sortasi terbaik tanpa defect, memberikan rasa sweet and balance saat diseduh." data-img="https://images.unsplash.com/photo-1611162458324-aae1eb4129a4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"><i class="fas fa-external-link-alt"></i> Detail</button>
                                <button class="btn btn-solid"><i class="fas fa-cart-plus"></i> Checkout</button>
                            </div>
                        </div>
                    </div>
                    <!-- Card GB 4 -->
                    <div class="product-card">
                        <div class="product-img-wrapper img-zoom-trigger" data-img="https://images.unsplash.com/photo-1497935586351-b67a49e012bf?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" data-title="GB Luwak Liar">
                            <span class="product-badge-top">Rare</span>
                            <span class="product-label-corner">Wild Luwak</span>
                            <img src="https://images.unsplash.com/photo-1497935586351-b67a49e012bf?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Green Beans Luwak" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">GB Luwak Liar</h3>
                            <div class="product-price">Rp 450.000 /kg</div>
                            <p class="product-bio">Green beans kopi luwak liar bersertifikat. Sudah dibersihkan secara higienis, tinggal roasting untuk kemewahan hakiki.</p>
                            <div class="product-actions">
                                <button class="btn btn-outline btn-detail" data-title="Green Beans Luwak Liar" data-desc="Green beans kopi luwak liar bersertifikat. Sudah dibersihkan secara higienis, tinggal masuk ke mesin roasting Anda." data-img="https://images.unsplash.com/photo-1497935586351-b67a49e012bf?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"><i class="fas fa-external-link-alt"></i> Detail</button>
                                <button class="btn btn-solid"><i class="fas fa-cart-plus"></i> Checkout</button>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="slider-nav-btn next" id="btnNextGB"><i class="fas fa-chevron-right"></i></button>
            </div>

            <div class="slider-indicators">
                <div class="slider-dots" id="dotsGB"></div>
                <div class="slider-progress-bar">
                    <div class="slider-progress-fill" id="progressGB"></div>
                </div>
            </div>
        </div>

        <div class="view-all-bottom-container">
            <a href="all-product.php" class="btn-view-all">Lihat Semuanya</a>
        </div>
    </section>

    <!-- TESTIMONIAL SLIDER -->
    <section class="testimonial-section">
        <h2 class="section-title">Apa Kata Penikmat Kopi</h2>
        <div class="testi-container">
            <div class="testi-slide active">
                <p class="testi-text">"Aroma Toraja Sapan-nya luar biasa! Begitu bungkus dibuka, wanginya langsung memenuhi ruangan."</p>
                <p class="testi-author">- Bapak Adipati, Jakarta</p>
            </div>
            <div class="testi-slide">
                <p class="testi-text">"Saya sudah mencoba banyak kopi luwak, tapi Luwak Liar Sumatera dari sini rasanya paling smooth."</p>
                <p class="testi-author">- Ibu Rania, Surabaya</p>
            </div>
            <div class="testi-slide">
                <p class="testi-text">"Packagingnya sangat elegan, cocok sekali dijadikan hampers atau hadiah untuk kolega bisnis."</p>
                <p class="testi-author">- Dimas Aryo, Bali</p>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
<?php include 'includes/footer.php'; ?>
    <!-- ================= MODAL ZOOM GAMBAR ================= -->
    <div class="modal-overlay image-modal" id="modalImageZoom">
        <span class="close-modal" onclick="closeModals()">&times;</span>
        <img src="" alt="Zoom Fullscreen" class="fullscreen-img" id="fullscreenImg">
    </div>

    <!-- ================= JAVASCRIPT ================= -->
    <script>
        const burgerBtn = document.getElementById('burgerBtn');
        const socialDropdown = document.getElementById('socialDropdown');
        
        burgerBtn.addEventListener('click', (e) => {
            socialDropdown.classList.toggle('active');
            e.stopPropagation();
        });

        const heroSlides = document.querySelectorAll('.hero-slide');
        let currentHero = 0;
        setInterval(() => {
            heroSlides[currentHero].classList.remove('active');
            currentHero = (currentHero + 1) % heroSlides.length;
            heroSlides[currentHero].classList.add('active');
        }, 3000);

        const testiSlides = document.querySelectorAll('.testi-slide');
        let currentTesti = 0;
        setInterval(() => {
            testiSlides[currentTesti].classList.remove('active');
            currentTesti = (currentTesti + 1) % testiSlides.length;
            testiSlides[currentTesti].classList.add('active');
        }, 3000);

        // Search Dropdown Toggle Logic & Icon Switcher (Search <-> X)
        const searchBtn = document.getElementById('searchBtn');
        const searchDropdownBar = document.getElementById('searchDropdownBar');
        const searchBarInput = document.getElementById('searchBarInput');

        searchBtn.addEventListener('click', (e) => {
            searchDropdownBar.classList.toggle('active');
            
            // Ubah ikon search jadi 'x' atau kembali ke 'fa-search'
            if (searchDropdownBar.classList.contains('active')) {
                searchBtn.classList.remove('fa-search');
                searchBtn.classList.add('fa-xmark');
                searchBarInput.focus();
            } else {
                searchBtn.classList.remove('fa-xmark');
                searchBtn.classList.add('fa-search');
            }
            e.stopPropagation();
        });

        // Modals Logic
        const modalImageZoom = document.getElementById('modalImageZoom');
        const fullscreenImg = document.getElementById('fullscreenImg');
        // Catatan: modal detail produk (#modalProduct) sudah tidak dipakai lagi —
        // tombol "Detail" sekarang navigasi langsung ke product.php (lihat assets/js/cart.js).

        document.querySelectorAll('.img-zoom-trigger').forEach(wrapper => {
            wrapper.addEventListener('click', () => {
                const imgSrc = wrapper.getAttribute('data-img');
                fullscreenImg.src = imgSrc;
                modalImageZoom.classList.add('active');
            });
        });

        function closeModals() {
            modalImageZoom.classList.remove('active');
        }

        window.addEventListener('click', (e) => {
            if (e.target === modalImageZoom) modalImageZoom.classList.remove('active');
            
            if (!burgerBtn.contains(e.target) && !socialDropdown.contains(e.target)) {
                socialDropdown.classList.remove('active');
            }

            if (!searchDropdownBar.contains(e.target) && e.target !== searchBtn) {
                searchDropdownBar.classList.remove('active');
                searchBtn.classList.remove('fa-xmark');
                searchBtn.classList.add('fa-search');
            }
        });

        function initSlider(sliderId, btnPrevId, btnNextId, dotsId, progressId) {
            const slider = document.getElementById(sliderId);
            const dotsContainer = document.getElementById(dotsId);
            const progressFill = document.getElementById(progressId);
            const cards = slider.children;
            const totalCards = cards.length;

            for (let i = 0; i < totalCards; i++) {
                const dot = document.createElement('div');
                dot.classList.add('dot');
                if (i === 0) dot.classList.add('active');
                dot.addEventListener('click', () => {
                    cards[i].scrollIntoView({ behavior: 'smooth', inline: 'start', block: 'nearest' });
                });
                dotsContainer.appendChild(dot);
            }

            const dots = dotsContainer.children;

            slider.addEventListener('scroll', () => {
                const scrollWidth = slider.scrollWidth - slider.clientWidth;
                const scrollLeft = slider.scrollLeft;
                
                if (scrollWidth > 0) {
                    const progressPercentage = (scrollLeft / scrollWidth) * 100;
                    progressFill.style.width = `${progressPercentage}%`;
                }

                let index = Math.round(scrollLeft / (cards[0].offsetWidth + 20));
                if (index < 0) index = 0;
                if (index >= totalCards) index = totalCards - 1;

                for (let i = 0; i < dots.length; i++) {
                    dots[i].classList.toggle('active', i === index);
                }
            });

            let isDown = false;
            let startX, scrollLeft;

            slider.addEventListener('mousedown', (e) => {
                isDown = true;
                slider.style.scrollBehavior = 'auto';
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
            });
            slider.addEventListener('mouseleave', () => {
                isDown = false;
                slider.style.scrollBehavior = 'smooth';
            });
            slider.addEventListener('mouseup', () => {
                isDown = false;
                slider.style.scrollBehavior = 'smooth';
            });
            slider.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX) * 1.5;
                slider.scrollLeft = scrollLeft - walk;
            });

            document.getElementById(btnPrevId).addEventListener('click', () => {
                slider.style.scrollBehavior = 'smooth';
                slider.scrollBy({ left: -320, top: 0, behavior: 'smooth' });
            });
            document.getElementById(btnNextId).addEventListener('click', () => {
                slider.style.scrollBehavior = 'smooth';
                slider.scrollBy({ left: 320, top: 0, behavior: 'smooth' });
            });
        }

        initSlider('blogSlider', 'btnPrevBlog', 'btnNextBlog', 'dotsBlog', 'progressBlog');
        initSlider('productSlider', 'btnPrev', 'btnNext', 'dotsProduct', 'progressProduct');
        initSlider('productSliderGB', 'btnPrevGB', 'btnNextGB', 'dotsGB', 'progressGB');
    </script>
    <script src="assets/js/cart.js?v=<?php echo @filemtime(__DIR__ . '/assets/js/cart.js') ?: time(); ?>"></script>
</body>
</html>