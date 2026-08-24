<?php require_once __DIR__ . "/includes/session.php"; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zenith Tour & Travel</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%231a2f27'/><text x='50' y='70' font-size='60' font-family='Playfair Display, serif' font-weight='bold' fill='%23c5a880' text-anchor='middle'>Z</text></svg>">
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            line-height: 1.6;
            background-color: #fcfbf7;
            overflow-x: hidden;
        }
        a {
            text-decoration: none;
            color: inherit;
        }
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 8%;
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: #1a2f27;
            letter-spacing: 1px;
        }
        .nav-right-container {
            display: flex;
            align-items: center;
            gap: 40px;
        }
        .nav-links {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
        }
        .nav-links a {
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
            color: #555;
        }
        .nav-links a:hover, .nav-links a.active {
            color: #c5a880;
        }
        .nav-socials {
            display: flex;
            gap: 15px;
            align-items: center;
            border-left: 1px solid #e5e0d8;
            padding-left: 20px;
        }
        .nav-socials a, .nav-search-btn {
            color: #1a2f27;
            font-size: 18px;
            transition: color 0.3s, transform 0.3s;
            background: none;
            border: none;
            cursor: pointer;
        }
        .nav-socials a:hover, .nav-search-btn:hover {
            color: #c5a880;
            transform: scale(1.15);
        }
        .btn-nav-book {
            background-color: #1a2f27;
            color: #fff !important;
            padding: 8px 20px;
            border-radius: 4px;
            font-size: 13px;
            transition: background 0.3s !important;
        }
        .btn-nav-book:hover {
            background-color: #c5a880;
        }
        .menu-toggle {
            display: none;
            font-size: 22px;
            color: #1a2f27;
            cursor: pointer;
        }
        @media(max-width: 991px) {
            .nav-right-container {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: #fff;
                padding: 30px;
                box-shadow: 0 10px 15px rgba(0,0,0,0.05);
                gap: 20px;
                border-top: 1px solid #f3eee7;
            }
            .nav-right-container.active {
                display: flex;
            }
            .nav-links {
                flex-direction: column;
                gap: 20px;
                width: 100%;
                text-align: center;
            }
            .nav-socials {
                border-left: none;
                padding-left: 0;
                border-top: 1px solid #f3eee7;
                padding-top: 20px;
                width: 100%;
                justify-content: center;
            }
            .menu-toggle {
                display: block;
            }
        }
        .hero {
            position: relative;
            height: 80vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #fff;
            padding: 0 20px;
            overflow: hidden;
        }
        .hero-bg-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0;
            transition: opacity 1s ease-in-out; 
            z-index: 1;
        }
        .hero-bg-slide.active {
            opacity: 1;
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(26, 47, 39, 0.45), rgba(26, 47, 39, 0.35));
            z-index: 2;
        }
        .hero-content {
            position: relative;
            z-index: 3;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 56px;
            margin-bottom: 15px;
            letter-spacing: 2px;
            font-weight: 700;
        }
        .hero p {
            font-size: 18px;
            font-weight: 300;
            margin-bottom: 35px;
            max-width: 650px;
            color: rgba(255, 255, 255, 0.9);
        }
        .btn-primary {
            background-color: #c5a880;
            color: #fff;
            padding: 14px 35px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 15px;
            letter-spacing: 1px;
            transition: background 0.3s, transform 0.3s;
            box-shadow: 0 4px 15px rgba(197, 168, 128, 0.3);
        }
        .btn-primary:hover {
            background-color: #b0936d;
            transform: translateY(-2px);
        }
        .promo-countdown-section {
            padding: 60px 8%;
            background-color: #1a2f27;
            color: #ffffff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .promo-countdown-container {
            max-width: 1200px;
            margin: 0 auto;
            display: block; 
        }
        @media(min-width: 768px) {
            .promo-countdown-container {
                display: table;
                width: 100%;
            }
            .promo-info {
                display: table-cell;
                width: 55%;
                vertical-align: middle;
                padding-right: 40px;
            }
            .promo-timer-wrapper {
                display: table-cell;
                width: 45%;
                vertical-align: middle;
                text-align: center;
            }
        }
        .promo-info {
            text-align: left;
            margin-bottom: 30px;
        }
        .promo-badge {
            background-color: #c5a880; 
            color: #fff;
            font-size: 11px;
            padding: 6px 14px;
            font-weight: 500;
            border-radius: 4px;
            letter-spacing: 1px;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 15px;
        }
        .promo-info h2 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: #ffffff;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        .promo-info p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 25px;
            font-weight: 300;
        }
        .btn-promo-action {
            background-color: #c5a880;
            color: #fff;
            padding: 12px 28px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 0.5px;
            display: inline-block;
            transition: background 0.3s, transform 0.3s;
            box-shadow: 0 4px 15px rgba(197, 168, 128, 0.2);
        }
        .btn-promo-action:hover {
            background-color: #b0936d;
            transform: translateY(-2px);
        }
        .promo-timer-wrapper {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 35px 25px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            text-align: center;
        }
        .timer-title {
            font-size: 12px;
            letter-spacing: 2px;
            color: #c5a880;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .countdown-timer {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        .timer-box {
            min-width: 70px;
            text-align: center;
        }
        .timer-number {
            font-family: 'Playfair Display', serif;
            font-size: 38px;
            font-weight: 700;
            color: #ffffff;
            display: block;
            line-height: 1;
            margin-bottom: 5px;
        }
        .timer-label {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            font-weight: 300;
            letter-spacing: 0.5px;
        }
        .timer-divider {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: #c5a880;
            font-weight: 400;
            line-height: 1;
            margin-top: -15px;
        }
        @media(max-width: 767px) {
            .promo-countdown-section {
                padding: 50px 5%;
            }
            .promo-info {
                text-align: center;
            }
            .promo-info h2 {
                font-size: 26px;
            }
            .timer-box {
                min-width: 55px;
            }
            .timer-number {
                font-size: 28px;
            }
        }
        .blog-section {
            padding: 100px 8%;
            background-color: #ffffff;
            text-align: center;
        }
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 50px auto 0 auto;
            text-align: left;
        }
        .blog-card {
            background: #fcfbf7;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e0d8;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .blog-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
            border-color: #c5a880;
        }
        .blog-img {
            height: 220px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .blog-date {
            position: absolute;
            bottom: 0;
            left: 0;
            background: #1a2f27;
            color: #fff;
            font-size: 11px;
            padding: 8px 15px;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .blog-content {
            padding: 30px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .blog-category {
            color: #c5a880;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 10px;
            display: block;
        }
        .blog-content h3 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            margin-bottom: 15px;
            color: #1a2f27;
            line-height: 1.4;
        }
        .blog-content p {
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
            font-weight: 300;
            flex-grow: 1;
        }
        .btn-read-more {
            font-size: 13px;
            font-weight: 600;
            color: #1a2f27;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s;
            margin-top: auto;
        }
        .btn-read-more:hover {
            color: #c5a880;
        }
        .btn-read-more i {
            font-size: 12px;
            transition: transform 0.3s;
        }
        .btn-read-more:hover i {
            transform: translateX(4px);
        }
        .blog-action-container {
            margin-top: 50px;
        }
        .destinations {
            padding: 100px 8% 80px 8%;
            text-align: center;
            position: relative;
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 38px;
            color: #1a2f27;
            margin-bottom: 15px;
        }
        .section-subtitle {
            color: #c5a880;
            margin-bottom: 5px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
        }
        .slider-container {
            position: relative;
            margin-top: 50px;
            width: 100%;
            overflow: hidden;
            padding: 10px 0;
            cursor: grab;
            user-select: none;
        }
        .slider-container:active {
            cursor: grabbing;
        }
        .slide img, .slide a, .slide button {
            -webkit-user-drag: none;
            user-select: none;
        }
        .slider-track {
            display: flex;
            transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);
            will-change: transform;
        }
        .slide {
            flex: 0 0 100%;
            padding: 0 15px;
            transition: opacity 0.3s;
        }
        @media(min-width: 768px) {
            .slide {
                flex: 0 0 50%;
            }
        }
        @media(min-width: 1024px) {
            .slide {
                flex: 0 0 33.333%;
            }
        }
        .card {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s;
            height: 100%;
            display: flex;
            flex-direction: column;
            text-align: left;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        }
        .card-img {
            height: 240px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .tag-promo {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(197, 168, 128, 0.95);
            color: #fff;
            font-size: 11px;
            padding: 4px 10px;
            font-weight: 500;
            border-radius: 4px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .card-content {
            padding: 30px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .card-content h3 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            margin-bottom: 10px;
            color: #1a2f27;
        }
        .card-content p {
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
            font-weight: 300;
            flex-grow: 1;
        }
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #f3eee7;
            padding-top: 20px;
            margin-top: auto;
        }
        .price {
            font-weight: 600;
            color: #c5a880;
            font-size: 18px;
        }
        .btn-secondary {
            border: 1px solid #c5a880;
            color: #c5a880;
            padding: 8px 18px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-secondary:hover {
            background-color: #c5a880;
            color: #fff;
            box-shadow: 0 4px 10px rgba(197, 168, 128, 0.15);
        }
        .slider-arrow-btn {
            background: #ffffff;
            border: 1px solid #e5e0d8;
            color: #1a2f27;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }
        .slider-arrow-btn:hover {
            background-color: #1a2f27;
            color: #ffffff;
            border-color: #1a2f27;
            transform: scale(1.05);
        }
        .slider-controls-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 30px;
        }
        .slider-dots {
            display: flex;
            gap: 10px;
            margin: 0 15px;
        }
        .slider-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #e5e0d8;
            cursor: pointer;
            transition: all 0.3s;
        }
        .slider-dot.active {
            background: #c5a880;
            width: 24px;
            border-radius: 4px;
        }

        .testimonials {
            padding: 100px 8%;
            background-color: #fcfbf7;
            text-align: center;
        }
        .testi-slider-container {
            position: relative;
            max-width: 900px;
            margin: 50px auto 0 auto;
            text-align: center;
            overflow: hidden;
        }
        .testimonial-slide {
            display: none;
            animation: fadeTesti 0.6s ease-in-out;
        }
        .testimonial-slide.active {
            display: block;
        }
        @keyframes fadeTesti {
            from { opacity: 0; transform: scale(0.98); }
            to { opacity: 1; transform: scale(1); }
        }
        .testimonial-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e0d8;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 10px;
        }
        .testimonial-card .testimonial-quote {
            font-size: 16px; 
            margin-bottom: 25px;
            max-width: 700px;
            font-family: 'Playfair Display', serif;
            line-height: 1.8;
            color: #1a2f27;
            font-style: italic;
        }
        .testimonial-card .testimonial-proof {
            height: 250px;
            width: 100%;
            max-width: 500px;
            margin-bottom: 25px;
            border-radius: 12px;
            overflow: hidden;
        }
        .testimonial-card .quote-icon {
            font-size: 40px;
            color: #c5a880;
            opacity: 0.5;
            margin-bottom: 20px;
        }
        .testimonial-author {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
            border: 2px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .author-info {
            text-align: left;
        }
        .author-name {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 18px;
            color: #1a2f27;
        }
        .author-trip {
            font-size: 13px;
            color: #c5a880;
            font-weight: 500;
        }
        .testimonial-rating {
            color: #ffb300;
            margin-top: 5px;
            font-size: 14px;
        }
        .proof-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .testimonial-card:hover .proof-img {
            transform: scale(1.05);
        }
        .testi-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 30px;
        }

        .rental-section {
            padding: 100px 8%;
            background-color: #f7f5ef; 
            text-align: center;
        }
        .rental-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 50px auto 0 auto;
            text-align: left;
        }
        .rental-card {
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s;
            border: 1px solid #e5e0d8;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .rental-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
            border-color: #c5a880;
        }
        .rental-img {
            height: 220px;
            background-size: cover;
            background-position: center;
            position: relative;
            background-color: #e5e0d8;
        }
        .rental-tag {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #1a2f27;
            color: #fff;
            font-size: 11px;
            padding: 5px 12px;
            font-weight: 500;
            border-radius: 4px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .rental-content {
            padding: 30px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .rental-content h3 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            color: #1a2f27;
            margin-bottom: 15px;
        }
        .rental-specs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 25px;
        }
        .rental-spec-item {
            font-size: 13px;
            color: #555;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .rental-spec-item i {
            color: #c5a880;
            width: 16px;
            text-align: center;
        }
        .rental-footer {
            border-top: 1px solid #f3eee7;
            padding-top: 20px;
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .rental-price {
            font-weight: 600;
            color: #c5a880;
            font-size: 18px;
        }
        .rental-price span {
            font-size: 12px;
            color: #999;
            font-weight: 400;
            text-transform: lowercase;
        }
        .btn-rental {
            background-color: transparent;
            border: 1px solid #c5a880;
            color: #c5a880;
            padding: 8px 20px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-rental:hover {
            background-color: #c5a880;
            color: #fff;
            box-shadow: 0 4px 10px rgba(197, 168, 128, 0.15);
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(26, 47, 39, 0.96);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            padding: 40px 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .modal.active {
            display: flex;
            opacity: 1;
        }
        .modal-content {
            background-color: #fff;
            max-width: 1000px;
            width: 100%;
            max-height: 85vh;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            position: relative;
            transform: scale(0.95);
            transition: transform 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        .modal.active .modal-content {
            transform: scale(1);
        }
        .modal-body {
            display: flex;
            height: 85vh;
            max-height: 85vh;
        }
        .modal-image-side {
            flex: 1.1;
            background-color: #1a2f27;
            position: relative;
        }
        .modal-image-side img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .modal-info-side {
            flex: 1.2;
            padding: 40px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .modal-close {
            position: absolute;
            top: 20px;
            right: 25px;
            font-size: 32px;
            color: #1a2f27;
            cursor: pointer;
            transition: color 0.3s;
            z-index: 10;
        }
        .modal-close:hover {
            color: #c5a880;
        }
        .modal-tag {
            color: #c5a880;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 5px;
        }
        .modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: #1a2f27;
            line-height: 1.2;
            margin-bottom: 10px;
        }
        .modal-meta {
            display: flex;
            gap: 20px;
            align-items: center;
            border-bottom: 1px solid #f3eee7;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .modal-price {
            font-size: 22px;
            font-weight: 600;
            color: #c5a880;
        }
        .modal-duration {
            font-size: 14px;
            color: #777;
        }
        .modal-tabs {
            display: flex;
            border-bottom: 1px solid #f3eee7;
            margin-bottom: 20px;
            gap: 20px;
        }
        .tab-btn {
            background: none;
            border: none;
            padding: 10px 0;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 500;
            color: #777;
            cursor: pointer;
            position: relative;
            transition: color 0.3s;
            outline: none;
        }
        .tab-btn:hover, .tab-btn.active {
            color: #1a2f27;
        }
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #c5a880;
        }
        .tab-content {
            display: none;
            font-size: 14px;
            color: #555;
            line-height: 1.6;
            flex-grow: 1;
            margin-bottom: 25px;
        }
        .tab-content.active {
            display: block;
        }
        .modal-list {
            list-style: none;
            padding-left: 0;
            margin-top: 10px;
        }
        .modal-list li {
            margin-bottom: 8px;
            position: relative;
            padding-left: 20px;
            color: #555;
        }
        .modal-list li::before {
            content: "•";
            color: #c5a880;
            font-size: 18px;
            position: absolute;
            left: 0;
            top: -2px;
        }
        .itinerary-timeline {
            margin-top: 10px;
        }
        .itinerary-step {
            position: relative;
            padding-left: 25px;
            border-left: 1px solid #e5e0d8;
            padding-bottom: 15px;
        }
        .itinerary-step:last-child {
            border-left: none;
            padding-bottom: 0;
        }
        .itinerary-step::before {
            content: '';
            position: absolute;
            left: -5px;
            top: 5px;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background-color: #c5a880;
        }
        .itinerary-day {
            font-weight: 600;
            color: #1a2f27;
            font-size: 14px;
            margin-bottom: 2px;
        }
        .itinerary-desc {
            font-size: 13px;
            color: #666;
        }
        .btn-modal-book {
            background-color: #1a2f27;
            color: #fff;
            border: none;
            width: 100%;
            padding: 14px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.3s;
            box-shadow: 0 4px 10px rgba(26, 47, 39, 0.15);
            font-family: 'Poppins', sans-serif;
        }
        .btn-modal-book:hover {
            background-color: #c5a880;
        }
        .btn-detail {
            background: transparent;
            border: none;
            color: #555;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            text-decoration: underline;
            cursor: pointer;
            transition: color 0.3s;
            margin-right: 12px;
        }
        .btn-detail:hover {
            color: #c5a880;
        }
        @media(max-width: 768px) {
            .modal {
                padding: 10px;
            }
            .modal-body {
                flex-direction: column;
                height: 90vh;
                max-height: 90vh;
            }
            .modal-image-side {
                height: 180px;
                flex: none;
            }
            .modal-info-side {
                padding: 25px 20px;
            }
            .modal-title {
                font-size: 24px;
            }
        }
        .booking-section {
            background-color: #1a2f27;
            color: #fff;
            padding: 100px 8%;
        }
        .booking-section .section-title {
            color: #fff;
            text-align: center;
        }
        .booking-section .section-subtitle {
            color: #c5a880;
            text-align: center;
        }
        .booking-form {
            max-width: 750px;
            margin: 50px auto 0 auto;
            background: rgba(255, 255, 255, 0.04);
            padding: 45px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }
        @media(max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-size: 13px;
            margin-bottom: 10px;
            color: #c5a880;
            letter-spacing: 1px;
            font-weight: 500;
            text-transform: uppercase;
        }
        .form-group input, .form-group select {
            padding: 14px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 4px;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: all 0.3s;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #c5a880;
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 0 8px rgba(197, 168, 128, 0.2);
        }
        .form-group option {
            background-color: #1a2f27;
            color: #fff;
        }
        .booking-form button {
            width: 100%;
            padding: 16px;
            background-color: #c5a880;
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            box-shadow: 0 4px 15px rgba(197, 168, 128, 0.2);
        }
        .booking-form button:hover {
            background-color: #b0936d;
            transform: translateY(-1px);
        }
        footer {
            background-color: #111e19;
            color: rgba(255,255,255,0.5);
            padding: 60px 8% 30px 8%;
            font-size: 13px;
        }
        .footer-grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 40px;
            margin-bottom: 30px;
        }
        .footer-info-block h3 {
            color: #fff;
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .footer-info-block p, .footer-info-block a {
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.8;
            margin-bottom: 8px;
            display: block;
        }
        .footer-info-block a:hover {
            color: #c5a880;
        }
        .footer-info-block i {
            margin-right: 8px;
            color: #c5a880;
            width: 16px;
        }
        .footer-logo {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 15px;
        }
        .footer-socials {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }
        .footer-socials a {
            color: rgba(255,255,255,0.6);
            font-size: 20px;
            transition: color 0.3s;
            display: inline-block;
        }
        .footer-socials a:hover {
            color: #c5a880;
        }
        .copyright {
            text-align: center;
            font-size: 12px;
        }
        .btn-see-all {
            color: #c5a880;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s;
            margin-bottom: 8px;
        }
        .btn-see-all:hover {
            color: #1a2f27;
        }
        .btn-see-all i {
            font-size: 13px;
            transition: transform 0.3s;
        }
        .btn-see-all:hover i {
            transform: translateX(5px);
        }

        .search-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(26, 47, 39, 0.92);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 3000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .search-modal.active {
            display: flex;
            opacity: 1;
        }
        .search-modal-content {
            width: 90%;
            max-width: 600px;
            text-align: center;
            position: relative;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }
        .search-modal.active .search-modal-content {
            transform: translateY(0);
        }
        .search-input-wrapper {
            position: relative;
            width: 100%;
        }
        .search-modal-input {
            width: 100%;
            padding: 20px 60px 20px 25px;
            font-size: 20px;
            font-family: 'Poppins', sans-serif;
            background: #ffffff;
            border: none;
            border-radius: 50px;
            outline: none;
            color: #1a2f27;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .search-modal-input::placeholder {
            color: #aaa;
        }
        .search-modal-submit {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #1a2f27;
            color: #fff;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: background 0.3s;
        }
        .search-modal-submit:hover {
            background: #c5a880;
        }
        .search-modal-close {
            position: absolute;
            top: -50px;
            right: 0;
            color: #fff;
            font-size: 28px;
            cursor: pointer;
            transition: color 0.3s;
        }
        .search-modal-close:hover {
            color: #c5a880;
        }
        .search-hint {
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            margin-top: 15px;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <nav>
        <div style="display: flex; flex-direction: column;">
            <div class="logo" style="line-height: 1.2;">Zenit Tour & Travel</div>
            <a href="https://www.zenith-adventour.com" target="_blank" style="font-size: 11px; color: #c5a880; letter-spacing: 0.5px; font-weight: 500; margin-top: -2px;">www.zenith-adventour.com</a>
        </div>
        <div class="menu-toggle" onclick="toggleMenu()"><i class="fa-solid fa-bars"></i></div>
        <div class="nav-right-container" id="navContainer">
            <ul class="nav-links">
                <li><a href="#" class="active">Beranda</a></li>
                <li><a href="#destinasi">Destinasi</a></li>
                <li><a href="gallery.php">Galeri</a></li>
                <li><a href="#" class="btn-nav-book" onclick="openSearchModal()"><i class="fa-solid fa-magnifying-glass"></i> Cari Sekarang</a></li>
            </ul>
            <div class="nav-socials">
                <a href="https://instagram.com" target="_blank" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://tiktok.com" target="_blank" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                <a href="https://facebook.com" target="_blank" title="Facebook"><i class="fa-brands fa-facebook"></i></a>
                <a href="https://wa.me/62895333841200?text=Hallo%20admin%20saya%20mau%20bertanya%20tentang%20detail%20perjalanan" target="_blank" title="WhatsApp Admin"><i class="fa-brands fa-whatsapp"></i></a>
            </div>
            <?php include __DIR__ . "/includes/auth_nav.php"; ?>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-bg-slide active" style="background-image: url('img/kawah-putih-01.webp');"></div>
        <div class="hero-bg-slide" style="background-image: url('img/yogyakarta-01.webp');"></div>
        <div class="hero-bg-slide" style="background-image: url('img/bali-01.webp');"></div>
        <div class="hero-bg-slide" style="background-image: url('img/malang-01.webp');"></div>
        <div class="hero-bg-slide" style="background-image: url('img/jakarta-01.webp');"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Jelajahi Nusantara</h1>
            <p>Rasakan perjalanan premium yang dirancang khusus untuk mewujudkan petualangan mewah impian Anda.</p>
            <a href="#destinasi" class="btn-primary">Mulai Petualangan</a>
        </div>
    </header>

    <section class="promo-countdown-section">
        <div class="promo-countdown-container">
            <div class="promo-info">
                <span class="promo-badge"><i class="fa-solid fa-bolt-lightning"></i> Syarat & Ketentuan Berlaku</span>
                <h2>Makin Rame, Makin Murah! Liburan Bareng Geng Jadi Hemat 20%</h2>
                <p>Liburan rame-rame gak pake mahal! Cukup ajak minimal 10 orang buat ikutan tour, langsung otomatis dapet potongan 20% dari harga awal!</p>
                <a href="#booking" class="btn-promo-action" onclick="pilihDestinasi('Kyoto Charm')">📩 Silakan hubungi tim kami untuk penjelasan lebih lengkapnya</a>
            </div>
            <div class="promo-timer-wrapper">
                <p class="timer-title">SISA WAKTU PROMO</p>
                <div class="countdown-timer" id="elegantCountdown">
                    <div class="timer-box">
                        <span class="timer-number" id="days">00</span>
                        <span class="timer-label">Hari</span>
                    </div>
                    <div class="timer-divider">:</div>
                    <div class="timer-box">
                        <span class="timer-number" id="hours">00</span>
                        <span class="timer-label">Jam</span>
                    </div>
                    <div class="timer-divider">:</div>
                    <div class="timer-box">
                        <span class="timer-number" id="minutes">00</span>
                        <span class="timer-label">Menit</span>
                    </div>
                    <div class="timer-divider">:</div>
                    <div class="timer-box">
                        <span class="timer-number" id="seconds">00</span>
                        <span class="timer-label">Detik</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="blog-section" id="blog-spot">
        <div class="section-header-container">
            <div class="section-header-text">
                <p class="section-subtitle">Inspirasi & Tips Perjalanan</p>
                <h2 class="section-title">Blog Spot Zenith</h2>
            </div>
            <a href="semua-blog.php" class="btn-see-all">Lihat Semuanya <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="blog-grid">
            <div class="blog-card">
                <div class="blog-img" style="background-image: url('img/kawah-putih-01.webp');">
                    <span class="blog-date">12 Jul 2026</span>
                </div>
                <div class="blog-content">
                    <span class="blog-category">Tips Travel</span>
                    <h3>Panduan Packing Praktis untuk Liburan Musim Panas</h3>
                    <p>Persiapkan koper Anda dengan cerdas. Simak rahasia packing efisien tanpa harus mengorbankan gaya liburan Anda.</p>
                    <a href="blog-bandung.php" class="btn-read-more">Baca Selengkapnya <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="blog-card">
                <div class="blog-img" style="background-image: url('img/bali-01.webp');">
                    <span class="blog-date">08 Jul 2026</span>
                </div>
                <div class="blog-content">
                    <span class="blog-category">Destinasi</span>
                    <h3>5 Hidden Gem di Bali yang Belum Banyak Diketahui Turis</h3>
                    <p>Tinggalkan keramaian sejenak dan temukan surga tersembunyi di Pulau Dewata yang menawarkan kedamaian eksklusif.</p>
                    <a href="blog-bali.php" class="btn-read-more">Baca Selengkapnya <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="blog-card">
                <div class="blog-img" style="background-image: url('img/yogyakarta-01.webp');">
                    <span class="blog-date">24 Jun 2026</span>
                </div>
                <div class="blog-content">
                    <span class="blog-category">Kuliner</span>
                    <h3>Mencicipi Kuliner Legendaris di Sepanjang Jalan Malioboro</h3>
                    <p>Panduan lengkap menikmati hidangan otentik khas Yogyakarta, dari gudeg hingga kopi jos yang menghangatkan suasana.</p>
                    <a href="blog-yogyakarta.php" class="btn-read-more">Baca Selengkapnya <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="blog-action-container">
            <a href="semua-blog.php" class="btn-secondary">Lihat Semua Artikel</a>
        </div>
    </section>

     <section class="destinations" id="destinasi">
        <div class="section-header-container">
            <div class="section-header-text">
                <p class="section-subtitle">Pilihan Favorit</p>
                <h2 class="section-title">Destinasi Populer</h2>
            </div>
            <a href="semua-destinasi.php" class="btn-see-all">Lihat Semuanya <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        
        <div class="slider-container" id="destSliderContainer">
            <div class="slider-track" id="destSliderTrack">
                <div class="slide">
                    <div class="card">
                        <div class="card-img" style="background-image: url('img/kawah-putih-02.webp');">
                            <span class="tag-promo">Udara Sejuk</span>
                        </div>
                        <div class="card-content">
                            <h3>Kawah Putih Bandung</h3>
                            <p>Nikmati pesona magis kawah vulkanik berkabut eksotis, hamparan kebun teh Ciwidey, dan glamping mewah di alam terbuka.</p>
                            <div class="card-footer">
                                <div style="display: flex; align-items: center;">
                                    <a href="paket-bandung.php" class="btn-secondary">Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slide">
                    <div class="card">
                        <div class="card-img" style="background-image: url('img/yogyakarta-02.webp');">
                            <span class="tag-promo">Kaya Budaya</span>
                        </div>
                        <div class="card-content">
                            <h3>Klasik Yogyakarta</h3>
                            <p>Telusuri keagungan Candi Borobudur, kemegahan Keraton, kehangatan jalan Malioboro, serta tradisi membatik yang adiluhung.</p>
                            <div class="card-footer">
                                <div style="display: flex; align-items: center;">
                                    <a href="paket-yogyakarta.php" class="btn-secondary">Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slide">
                    <div class="card">
                        <div class="card-img" style="background-image: url('img/bali-02.webp');">
                            <span class="tag-promo">Terpopuler</span>
                        </div>
                        <div class="card-content">
                            <h3>Eksotika Bali</h3>
                            <p>Rasakan keindahan murni pantai berpasir putih, ritual tradisi budaya Bali yang sakral, dan ketenangan Ubud.</p>
                            <div class="card-footer">
                                <div style="display: flex; align-items: center;">
                                    <a href="paket-bali.php" class="btn-secondary">Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slide">
                    <div class="card">
                        <div class="card-img" style="background-image: url('img/malang-02.webp');">
                            <span class="tag-promo">Eksplorasi Alam</span>
                        </div>
                        <div class="card-content">
                            <h3>Pesona Malang & Bromo</h3>
                            <p>Saksikan matahari terbit legendaris di Gunung Bromo, nikmati petik apel Kota Batu, dan kesejukan udara pegunungan Jawa Timur.</p>
                            <div class="card-footer">
                                <div style="display: flex; align-items: center;">
                                    <a href="paket-malang.php" class="btn-secondary">Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slide">
                    <div class="card">
                        <div class="card-img" style="background-image: url('img/jakarta-02.webp');">
                            <span class="tag-promo">Metropolitan</span>
                        </div>
                        <div class="card-content">
                            <h3>Metropolitan Jakarta</h3>
                            <p>Rasakan kemewahan staycation hotel bintang 5, wisata sejarah Kota Tua, pusat belanja premium, dan gemerlap kota modern.</p>
                            <div class="card-footer">
                                <div style="display: flex; align-items: center;">
                                    <a href="paket-jakarta.php" class="btn-secondary">Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="slider-controls-container">
                <button class="slider-arrow-btn" onclick="slidePrev()" aria-label="Sebelumnya"><i class="fa-solid fa-chevron-left"></i></button>
                <div class="slider-dots" id="sliderDots"></div>
                <button class="slider-arrow-btn" onclick="slideNext()" aria-label="Selanjutnya"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
            
            <div class="blog-action-container">
                <a href="semua-destinasi.php" class="btn-secondary">Lihat Semua Destinasi</a>
            </div>
        </div>
    </section>

    <section class="rental-section" id="rental-kendaraan">
        <p class="section-subtitle">Layanan Transportasi Rombongan</p>
        <h2 class="section-title">Penyewaan Armada Bus</h2>
        <p style="color: #666; max-width: 650px; margin: 0 auto; font-size: 15px;">Sediakan kenyamanan maksimal untuk perjalanan tour grup dan perusahaan Anda dengan armada bus besar, sedang, dan bus kecil yang prima serta berfasilitas lengkap.</p>
        
        <div class="rental-grid">
            <!-- Bus Besar -->
            <div class="rental-card">
                <div class="rental-img" style="background-image: url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=500&auto=format&fit=crop&q=60');">
                    <span class="rental-tag">Big Bus</span>
                </div>
                <div class="rental-content">
                    <h3>Bus Besar (Big Bus)</h3>
                    <div class="rental-specs">
                        <div class="rental-spec-item"><i class="fa-solid fa-users"></i> 45 - 59 Penumpang</div>
                        <div class="rental-spec-item"><i class="fa-solid fa-suitcase"></i> Bagasi Luas</div>
                        <div class="rental-spec-item"><i class="fa-solid fa-snowflake"></i> AC / Toilet / TV</div>
                        <div class="rental-spec-item"><i class="fa-solid fa-user-tie"></i> Supir & Crew Pro</div>
                    </div>
                    <div class="rental-footer">
                        <div class="rental-price">Rp 3.500.000 <span>/ hari</span></div>
                        <a href="https://wa.me/62895333841200?text=Halo%20Admin%20Zenith,%20saya%20tertarik%20untuk%20menyewa%20Bus%20Besar%20(Big%20Bus)." target="_blank" class="btn-rental">Sewa</a>
                    </div>
                </div>
            </div>

            <!-- Bus Sedang -->
            <div class="rental-card">
                <div class="rental-img" style="background-image: url('https://images.unsplash.com/photo-1570125909232-eb263c188f7e?w=500&auto=format&fit=crop&q=60');">
                    <span class="rental-tag">Medium Bus</span>
                </div>
                <div class="rental-content">
                    <h3>Bus Sedang (Medium Bus)</h3>
                    <div class="rental-specs">
                        <div class="rental-spec-item"><i class="fa-solid fa-users"></i> 31 - 35 Penumpang</div>
                        <div class="rental-spec-item"><i class="fa-solid fa-suitcase"></i> Bagasi Standar</div>
                        <div class="rental-spec-item"><i class="fa-solid fa-snowflake"></i> AC / Karaoke / TV</div>
                        <div class="rental-spec-item"><i class="fa-solid fa-user-tie"></i> Termasuk Supir</div>
                    </div>
                    <div class="rental-footer">
                        <div class="rental-price">Rp 2.400.000 <span>/ hari</span></div>
                        <a href="https://wa.me/62895333841200?text=Halo%20Admin%20Zenith,%20saya%20tertarik%20untuk%20menyewa%20Bus%20Sedang%20(Medium%20Bus)." target="_blank" class="btn-rental">Sewa</a>
                    </div>
                </div>
            </div>

            <!-- Bus Kecil -->
            <div class="rental-card">
                <div class="rental-img" style="background-image: url('https://images.unsplash.com/photo-1631930998583-72cddce04c69?w=500&auto=format&fit=crop&q=60');">
                    <span class="rental-tag">Mini Bus / Elf</span>
                </div>
                <div class="rental-content">
                    <h3>Bus Kecil (Micro Bus)</h3>
                    <div class="rental-specs">
                        <div class="rental-spec-item"><i class="fa-solid fa-users"></i> 14 - 19 Penumpang</div>
                        <div class="rental-spec-item"><i class="fa-solid fa-suitcase"></i> Bagasi Bagus</div>
                        <div class="rental-spec-item"><i class="fa-solid fa-snowflake"></i> Full AC / Audio</div>
                        <div class="rental-spec-item"><i class="fa-solid fa-user-tie"></i> Termasuk Supir</div>
                    </div>
                    <div class="rental-footer">
                        <div class="rental-price">Rp 1.600.000 <span>/ hari</span></div>
                        <a href="https://wa.me/62895333841200?text=Halo%20Admin%20Zenith,%20saya%20tertarik%20untuk%20menyewa%20Bus%20Kecil%20(Micro%20Bus)." target="_blank" class="btn-rental">Sewa</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials" id="testimoni">
        <div class="section-header-container">
            <div class="section-header-text">
                <p class="section-subtitle">Kisah Bahagia Sahabat</p>
                <h2 class="section-title">Testimoni Pelanggan</h2>
            </div>
            <a href="#semua-testimoni" class="btn-see-all">Lihat Semuanya <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        
        <div class="testi-slider-container">
            <div class="testimonial-slide active">
                <div class="testimonial-card">
                    <div class="testimonial-proof">
                        <img src="img/test.jpg" alt="Bukti Perjalanan Yogyakarta" class="proof-img">
                    </div>
                    <i class="fa-solid fa-quote-left quote-icon"></i>
                    <p class="testimonial-quote">"Perjalanan ke Yogyakarta sangat teratur. Menginap di hotel dekat Malioboro yang bernuansa tradisional mewah dan mencicipi hidangan khas sana adalah momen terbaik. Sofia juga sangat ramah dalam mengarahkan rencana kami."</p>
                    
                    <div class="testimonial-author">
                        <div class="author-avatar" style="background-color: #c5a880;">AH</div>
                        <div class="author-info">
                            <h4 class="author-name">Ahmad Hidayat</h4>
                            <span class="author-trip">Paket: Klasik Yogyakarta</span>
                            <div class="testimonial-rating">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="testimonial-slide">
                <div class="testimonial-card">
                    <div class="testimonial-proof">
                        <img src="img/test2.jpg" alt="Bukti Perjalanan Bali" class="proof-img">
                    </div>
                    <i class="fa-solid fa-quote-left quote-icon"></i>
                    <p class="testimonial-quote">"Pengalaman liburan ke Bali yang luar biasa! Pelayanan sangat VIP dari awal penjemputan hingga pulang. Menonton tari Kecak saat sunset di Uluwatu menjadi kenangan tak terlupakan buat keluarga kami."</p>
                    
                    <div class="testimonial-author">
                        <div class="author-avatar" style="background-color: #1a2f27;">BM</div>
                        <div class="author-info">
                            <h4 class="author-name">Budi & Marlina</h4>
                            <span class="author-trip">Paket: Eksotika Bali</span>
                            <div class="testimonial-rating">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="testimonial-slide">
                <div class="testimonial-card">
                    <div class="testimonial-proof">
                        <img src="img/test3.jpg" alt="Bukti Perjalanan Bandung" class="proof-img">
                    </div>
                    <i class="fa-solid fa-quote-left quote-icon"></i>
                    <p class="testimonial-quote">"Glamping di Kawah Putih benar-benar magis. Udaranya sejuk dan fasilitasnya setara bintang 5. Tim Zenith sangat profesional mengatur semuanya sehingga kami tinggal menikmati liburan tanpa pusing sama sekali."</p>
                    
                    <div class="testimonial-author">
                        <div class="author-avatar" style="background-color: #9b7e5a;">SW</div>
                        <div class="author-info">
                            <h4 class="author-name">Siti Wahyuni</h4>
                            <span class="author-trip">Paket: Kawah Putih Bandung</span>
                            <div class="testimonial-rating">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="testi-controls slider-controls-container">
                <button class="slider-arrow-btn" onclick="testiPrev()" aria-label="Sebelumnya"><i class="fa-solid fa-chevron-left"></i></button>
                <div class="slider-dots" id="testiSliderDots">
                    <div class="slider-dot active" onclick="currentTesti(0)"></div>
                    <div class="slider-dot" onclick="currentTesti(1)"></div>
                    <div class="slider-dot" onclick="currentTesti(2)"></div>
                </div>
                <button class="slider-arrow-btn" onclick="testiNext()" aria-label="Selanjutnya"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <div class="modal" id="detailModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeDetailModal()">&times;</span>
            <div class="modal-body">
                <div class="modal-image-side">
                    <img id="modalImg" src="" alt="Detail Destinasi">
                </div>
                <div class="modal-info-side">
                    <div>
                        <span class="modal-tag" id="modalTag">Kategori</span>
                        <h2 class="modal-title" id="modalTitle">Title</h2>
                        <div class="modal-meta">
                            <span class="modal-price" id="modalPrice">Rp 0</span>
                            <span class="modal-duration" id="modalDuration"><i class="fa-regular fa-clock"></i> 3 Hari 2 Malam</span>
                        </div>

                        <div class="modal-tabs">
                            <button class="tab-btn active" onclick="switchTab(event, 'tab-overview')">Ikhtisar</button>
                            <button class="tab-btn" onclick="switchTab(event, 'tab-itinerary')">Rencana Perjalanan</button>
                            <button class="tab-btn" onclick="switchTab(event, 'tab-inclusion')">Fasilitas</button>
                        </div>

                        <div class="tab-content active" id="tab-overview">
                            <p id="modalDesc">Deskripsi lengkap destinasi.</p>
                            <h4 style="margin-top: 20px; color: #1a2f27; font-family: 'Playfair Display', serif; font-size: 16px;">Sorotan Utama Wisata:</h4>
                            <ul id="modalHighlights" class="modal-list"></ul>
                        </div>

                        <div class="tab-content" id="tab-itinerary">
                            <div class="itinerary-timeline" id="modalItinerary"></div>
                        </div>

                        <div class="tab-content" id="tab-inclusion">
                            <div style="margin-bottom: 20px;">
                                <h4 style="color: #1a2f27; font-size: 15px; margin-bottom: 5px;"><i class="fa-solid fa-circle-check" style="color: #2e7d32; margin-right: 8px;"></i> Termasuk (Inclusions):</h4>
                                <ul id="modalInclusions" class="modal-list"></ul>
                            </div>
                            <div>
                                <h4 style="color: #1a2f27; font-size: 15px; margin-bottom: 5px;"><i class="fa-solid fa-circle-xmark" style="color: #c62828; margin-right: 8px;"></i> Tidak Termasuk (Exclusions):</h4>
                                <ul id="modalExclusions" class="modal-list"></ul>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: auto; padding-top: 25px;">
                        <button class="btn-modal-book" id="modalBookBtn" onclick="bookFromModal()">Pesan Paket Wisata Ini</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="search-modal" id="searchModal">
        <div class="search-modal-content">
            <span class="search-modal-close" onclick="closeSearchModal()">&times;</span>
            <div class="search-input-wrapper">
                <input type="text" id="searchInput" class="search-modal-input" placeholder="Cari destinasi, hotel, atau aktivitas..." onkeypress="handleSearchEnter(event)">
                <button class="search-modal-submit" onclick="executeSearch()"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
            <p class="search-hint">Tekan <b>Enter</b> atau klik ikon kaca pembesar untuk mulai mencari.</p>
        </div>
    </div>

    <section class="booking-section" id="booking">
        <p class="section-subtitle">Konsultasi Gratis</p>
        <h2 class="section-title">Ingin Konsultasi Rencana Liburan?</h2>
        <p style="text-align: center; color: rgba(255,255,255,0.7); margin-bottom: 30px;">
            Tinggalkan detail Anda, tim kami akan menghubungi Anda via WhatsApp untuk membantu menyusun liburan impian Anda.
        </p>
        <form class="booking-form" id="formPemesanan" onsubmit="prosesBooking(event)">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" required placeholder="Masukkan nama lengkap Anda">
                </div><br>
                <div class="form-group">
                    <label for="destinasi-select">Pilih Tujuan Destinasi</label>
                    <select id="destinasi-select" required>
                        <option value="" disabled selected>-- Pilih Wisata --</option>
                        <option value="Kawah Putih Bandung">Kawah Putih Bandung</option>
                        <option value="Yogyakarta Klasik">Klasik Yogyakarta</option>
                        <option value="Bali Exotica">Eksotika Bali</option>
                        <option value="Pesona Malang">Pesona Malang</option>
                        <option value="Metropolitan Jakarta">Metropolitan Jakarta</option>
                    </select>
                </div><br>
                <div class="form-group">
                    <label for="jumlah">Jumlah Peserta</label>
                    <input type="number" id="jumlah" min="1" value="1" required>
                </div><br>
                <div class="form-group">
                    <label for="telepon">Nomor WhatsApp</label>
                    <input type="tel" id="telepon" required placeholder="08123456xxx">
                </div><br>
            <button type="submit">Konfirmasi Pemesanan via WhatsApp</button>
        </form>
    </section>

    <?php include __DIR__ . "/includes/footer.php"; ?>

    <script>
        function toggleMenu() {
            const navContainer = document.getElementById('navContainer');
            navContainer.classList.toggle('active');
        }

        function pilihDestinasi(namaDestinasi) {
            const selectDestinasi = document.getElementById('destinasi-select');
            selectDestinasi.value = namaDestinasi;
        }

        const destinasiData = {
            bandung: {
                title: "Kawah Putih Bandung",
                tag: "Bandung, Indonesia",
                price: "Rp 2.200.000",
                duration: "3 Hari 2 Malam",
                image: "img/kawah-putih-03.webp",
                desc: "Jelajahi keindahan alam Ciwidey yang menyejukkan jiwa. Saksikan pesona magis Kawah Putih yang dikelilingi kabut belerang eksotis, nikmati petualangan di Jembatan Pinisi, serta kenyamanan menginap di glamping premium tepi danau.",
                highlights: [
                    "Menjelajahi kawasan kawah vulkanik Kawah Putih Ciwidey",
                    "Menikmati sore romantis di Situ Patenggang",
                    "Staycation mewah ala Glamping bintang 5",
                    "Wisata kuliner dan belanja khas Paris van Java"
                ],
                itinerary: [
                    { day: "Hari 1: Penjemputan & Eksplorasi Kota", desc: "Tiba di Bandung, penjemputan oleh tim VIP. Mengunjungi area bersejarah Jalan Asia Afrika dan Braga, dilanjutkan makan malam kuliner khas Sunda premium." },
                    { day: "Hari 2: Pesona Magis Ciwidey", desc: "Perjalanan menuju Bandung Selatan. Menjelajahi Kawah Putih, berjalan di atas jembatan kayu Ranca Upas, dan bersantai di Kebun Teh Rancabali." },
                    { day: "Hari 3: Belanja Oleh-oleh & Kepulangan", desc: "Sarapan pagi dengan pemandangan alam, berburu oleh-oleh premium Kartika Sari/Amanda, lalu diantar kembali ke stasiun atau bandara." }
                ],
                inclusions: [
                    "Akomodasi Glamping/Hotel Bintang 4 selama 2 malam",
                    "Transportasi private ber-AC selama tour",
                    "Tiket masuk semua objek wisata sesuai program",
                    "Makan sesuai jadwal dengan menu standar premium"
                ],
                exclusions: [
                    "Tiket perjalanan dari kota asal ke Bandung",
                    "Pengeluaran pribadi",
                    "Tips sukarela untuk guide & driver"
                ],
                bookingValue: "Kawah Putih Bandung"
            },
            yogyakarta: {
                title: "Klasik Yogyakarta",
                tag: "Yogyakarta, Indonesia",
                price: "Rp 3.100.000",
                duration: "3 Hari 2 Malam",
                image: "img/yogyakarta-03.webp",
                desc: "Kembali ke pusat kebudayaan Jawa yang penuh kehangatan. Nikmati tur eksklusif menyaksikan kemegahan Candi Borobudur saat fajar, napak tilas sejarah di Keraton Yogyakarta, serta petualangan Lava Tour Merapi yang memicu adrenalin.",
                highlights: [
                    "Berburu matahari terbit eksklusif di Borobudur",
                    "Wisata budaya di Keraton dan Istana Air Taman Sari",
                    "Petualangan Jeep Lava Tour Merapi",
                    "Makan malam romantis dengan tarian tradisional Jawa"
                ],
                itinerary: [
                    { day: "Hari 1: Budaya Keraton & Malioboro", desc: "Tiba di Yogyakarta, check-in hotel butik premium. Mengunjungi Keraton Yogyakarta dan Taman Sari, sore hari menikmati suasana hangat Malioboro." },
                    { day: "Hari 2: Sunrise Borobudur & Adrenalin Merapi", desc: "Dini hari menuju Borobudur untuk melihat sunrise. Dilanjutkan petualangan seru naik Jeep terbuka menyusuri sisa aliran lava Merapi." },
                    { day: "Hari 3: Belanja Batik Premium & Kepulangan", desc: "Mengunjungi pusat kerajinan perak di Kotagede dan workshop batik tulis, dilanjutkan transfer menuju bandara/stasiun." }
                ],
                inclusions: [
                    "Hotel butik bintang 4/5 selama 2 malam",
                    "Sewa Jeep private untuk Lava Tour Merapi",
                    "Semua tiket masuk VIP destinasi wisata",
                    "Layanan antar-jemput private bandara/stasiun"
                ],
                exclusions: [
                    "Tiket pesawat/kereta api ke Yogyakarta",
                    "Pengeluaran pribadi"
                ],
                bookingValue: "Yogyakarta Klasik"
            },
            bali: {
                title: "Eksotika Bali",
                tag: "Bali, Indonesia",
                price: "Rp 4.500.000",
                duration: "4 Hari 3 Malam",
                image: "img/bali-03.webp",
                desc: "Rasakan harmoni alam dan budaya spiritual di pulau Dewata. Paket premium ini dirancang khusus untuk membawa Anda menikmati kedamaian sejati Ubud, pemandangan dramatis pura Uluwatu di atas tebing, serta petualangan eksotis pulau Nusa Penida.",
                highlights: [
                    "Menyaksikan matahari terbenam magis di tebing Pura Uluwatu",
                    "Pagi hari yang syahdu berjalan menyusuri sawah Tegalalang",
                    "Sesi spa aromaterapi Bali tradisional selama 2 jam",
                    "Eksplorasi pantai ikonik Kelingking Beach di Nusa Penida"
                ],
                itinerary: [
                    { day: "Hari 1: Penjemputan & Senja Uluwatu", desc: "Tiba di Bandara Ngurah Rai, disambut hangat oleh pemandu lokal. Check-in hotel di area Seminyak lalu sore harinya mengunjungi Pura Uluwatu untuk menonton Tari Kecak berlatar sunset." },
                    { day: "Hari 2: Jiwa Seni Ubud & Sawah Tegalalang", desc: "Perjalanan menuju Ubud. Berjalan santai di Campuhan Ridge Walk, mengunjungi persawahan hijau Tegalalang, dan makan malam romantis di tepi sungai Ayung." },
                    { day: "Hari 3: Petualangan Nusa Penida Barat", desc: "Menyeberang menggunakan speed boat menuju Nusa Penida. Menjelajahi Kelingking Beach, Broken Beach, dan Angels Billabong yang menakjubkan." },
                    { day: "Hari 4: Relaksasi & Kepulangan", desc: "Menikmati spa khas Bali yang menenangkan jiwa sebelum berbelanja cinderamata eksklusif di galeri seni lokal dan diantar kembali ke bandara." }
                ],
                inclusions: [
                    "Hotel bintang 5 (3 Malam inklusif sarapan pagi)",
                    "Transportasi private ber-AC selama tour berlangsung",
                    "Tiket fast boat pulang-pergi ke Nusa Penida",
                    "Semua tiket masuk destinasi wisata sesuai program"
                ],
                exclusions: [
                    "Tiket pesawat pulang-pergi dari kota asal",
                    "Pengeluaran pribadi"
                ],
                bookingValue: "Bali Exotica"
            },
            malang: {
                title: "Pesona Malang & Bromo",
                tag: "Malang, Indonesia",
                price: "Rp 3.800.000",
                duration: "3 Hari 2 Malam",
                image: "img/malang-03.webp",
                desc: "Saksikan salah satu pemandangan matahari terbit terindah di dunia di Gunung Bromo. Padukan petualangan tersebut dengan kesejukan Kota Wisata Batu yang ramah keluarga serta perkebunan apel organik yang asri.",
                highlights: [
                    "Melihat golden sunrise Bromo dari Penanjakan",
                    "Menjelajahi lautan pasir Bromo menggunakan Jeep 4x4",
                    "Eksplorasi wahana premium di Kota Batu",
                    "Pengalaman memetik buah apel segar langsung di kebun"
                ],
                itinerary: [
                    { day: "Hari 1: Tiba di Malang & Santai Kota Batu", desc: "Penjemputan di bandara/stasiun Malang. Menuju Batu, mengunjungi Museum Angkut, dan check-in resort mewah bernuansa pegunungan." },
                    { day: "Hari 2: Midnight Safari Bromo", desc: "Pukul 00.30 dini hari, Jeep 4x4 menjemput Anda menuju Bromo. Menikmati sunrise, mendaki kawah Bromo, berfoto di Pasir Berbisik dan Bukit Teletubbies." },
                    { day: "Hari 3: Petik Apel & Kepulangan", desc: "Mengunjungi perkebunan apel agrowisata untuk memetik buah langsung dari pohonnya, dilanjutkan transfer kembali ke titik kepulangan." }
                ],
                inclusions: [
                    "Resort bintang 4 di Kota Batu selama 2 malam",
                    "Sewa Jeep eksklusif 4x4 untuk kawasan Bromo",
                    "Tiket masuk TN Bromo Tengger Semeru",
                    "Makan pagi, siang, dan malam selama tour"
                ],
                exclusions: [
                    "Tiket akomodasi dari kota asal",
                    "Pengeluaran pribadi"
                ],
                bookingValue: "Pesona Malang"
            },
            jakarta: {
                title: "Metropolitan Jakarta",
                tag: "Jakarta, Indonesia",
                price: "Rp 2.900.000",
                duration: "3 Hari 2 Malam",
                image: "img/jakarta-03.webp",
                desc: "Nikmati sisi mewah dari ibu kota Indonesia. Rasakan sensasi menginap di hotel pencakar langit bintang 5, makan malam romantis di cruise Jakarta Bay, serta tur sejarah eksklusif di Batavia lama (Kota Tua).",
                highlights: [
                    "Staycation premium di luxury hotel bintang 5 Jakarta",
                    "Sunset dinner cruise romantis di Teluk Jakarta",
                    "Private tour sejarah Batavia Lama dan PIK modern",
                    "Akses belanja eksklusif didampingi personal shopper"
                ],
                itinerary: [
                    { day: "Hari 1: Check-in Kemewahan & Dinner Cruise", desc: "Tiba di Jakarta, penjemputan dengan mobil Alphard premium. Check-in hotel bintang 5. Sore hari menuju dermaga untuk menikmati makan malam romantis di atas kapal yacht/cruise kecil." },
                    { day: "Hari 2: Kontras Jakarta Lama & Baru", desc: "Private tour ke Kota Tua mengunjungi Museum Fatahillah dengan sepeda ontel klasik, dilanjutkan menikmati sore modern estetis di Pantai Indah Kapuk (PIK) 2." },
                    { day: "Hari 3: Relaksasi Spa & Kepulangan", desc: "Menikmati fasilitas spa hotel kelas dunia sebelum bersantai atau berbelanja di Grand Indonesia/Plaza Indonesia, lalu diantar ke bandara." }
                ],
                inclusions: [
                    "Hotel bintang 5 kelas internasional selama 2 malam",
                    "Transportasi private premium (Alphard/Innova Zenix)",
                    "Tiket Exclusive Dinner Cruise",
                    "Semua tiket masuk objek wisata dan pemandu lokal"
                ],
                exclusions: [
                    "Tiket transportasi PP ke Jakarta",
                    "Pengeluaran belanja pribadi"
                ],
                bookingValue: "Metropolitan Jakarta"
            }
        };

        let activeDestinasiKey = '';

        function openDetailModal(key) {
            const data = destinasiData[key];
            if (!data) return;

            activeDestinasiKey = key;

            document.getElementById('modalImg').src = data.image;
            document.getElementById('modalTag').innerText = data.tag;
            document.getElementById('modalTitle').innerText = data.title;
            document.getElementById('modalPrice').innerText = data.price;
            document.getElementById('modalDuration').innerHTML = `<i class="fa-regular fa-clock" style="margin-right: 5px;"></i> ${data.duration}`;
            document.getElementById('modalDesc').innerText = data.desc;

            const highlightsList = document.getElementById('modalHighlights');
            highlightsList.innerHTML = '';
            data.highlights.forEach(item => {
                const li = document.createElement('li');
                li.innerText = item;
                highlightsList.appendChild(li);
            });

            const itineraryContainer = document.getElementById('modalItinerary');
            itineraryContainer.innerHTML = '';
            data.itinerary.forEach(step => {
                const stepDiv = document.createElement('div');
                stepDiv.className = 'itinerary-step';
                stepDiv.innerHTML = `
                    <div class="itinerary-day">${step.day}</div>
                    <div class="itinerary-desc">${step.desc}</div>
                `;
                itineraryContainer.appendChild(stepDiv);
            });

            const inclusionList = document.getElementById('modalInclusions');
            inclusionList.innerHTML = '';
            data.inclusions.forEach(item => {
                const li = document.createElement('li');
                li.innerText = item;
                inclusionList.appendChild(li);
            });

            const exclusionList = document.getElementById('modalExclusions');
            exclusionList.innerHTML = '';
            data.exclusions.forEach(item => {
                const li = document.createElement('li');
                li.innerText = item;
                exclusionList.appendChild(li);
            });

            resetTabs();

            const modal = document.getElementById('detailModal');
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
            
            document.body.style.overflow = 'hidden'; 
        }

        function closeDetailModal() {
            const modal = document.getElementById('detailModal');
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
            document.body.style.overflow = 'auto'; 
        }

        function switchTab(event, tabId) {
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => btn.classList.remove('active'));

            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));

            event.currentTarget.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }

        function resetTabs() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => btn.classList.remove('active'));
            if (tabButtons[0]) tabButtons[0].classList.add('active');

            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            if (tabContents[0]) tabContents[0].classList.add('active');
        }

        function bookFromModal() {
            if (!activeDestinasiKey) return;
            const data = destinasiData[activeDestinasiKey];
            
            pilihDestinasi(data.bookingValue);
            closeDetailModal();

            setTimeout(() => {
                document.getElementById('booking').scrollIntoView({ behavior: 'smooth' });
                document.getElementById('nama').focus();
            }, 350);
        }

        function openSearchModal() {
            const modal = document.getElementById('searchModal');
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('active');
                document.getElementById('searchInput').focus();
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closeSearchModal() {
            const modal = document.getElementById('searchModal');
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
            document.body.style.overflow = 'auto';
        }

        function executeSearch() {
            const keyword = document.getElementById('searchInput').value.trim();
            if (keyword !== "") {
                window.location.href = `search.php?q=${encodeURIComponent(keyword)}`;
            }
        }

        function handleSearchEnter(event) {
            if (event.key === 'Enter') {
                executeSearch();
            }
        }

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('detailModal');
            const searchModal = document.getElementById('searchModal');
            if (event.target === modal) {
                closeDetailModal();
            }
            if (event.target === searchModal) {
                closeSearchModal();
            }
        });

        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDetailModal();
                closeSearchModal();
            }
        });

        function prosesBooking(event) {
            event.preventDefault(); 
            const nama = document.getElementById('nama').value;
            const destinasi = document.getElementById('destinasi-select').value;
            const jumlah = document.getElementById('jumlah').value;
            const telepon = document.getElementById('telepon').value;

            const templatePesan = `Halo Zenith Tour & Travel,

Saya ingin menanyakan tur dengan detail berikut:
*Nama Lengkap:* ${nama}
*Paket Destinasi:* ${destinasi}
*Jumlah Peserta:* ${jumlah} Orang
*Nomor WhatsApp:* ${telepon}

Mohon jelaskan benefit apa saja yang saya dapatkan. Terima kasih!`;

            const pesanValidURL = encodeURIComponent(templatePesan);
            const nomorAdmin = "62895333841200"; 
            window.open(`https://wa.me/${nomorAdmin}?text=${pesanValidURL}`, '_blank');
        }

        const track = document.getElementById('destSliderTrack');
        const sliderContainer = document.getElementById('destSliderContainer');
        const slides = Array.from(track.children);
        let currentIndex = 0;
        let itemsPerSlide = 3; 

        function updateItemsPerSlide() {
            const width = window.innerWidth;
            if (width < 768) {
                itemsPerSlide = 1; 
            } else if (width < 1024) {
                itemsPerSlide = 2; 
            } else {
                itemsPerSlide = 3; 
            }
            createSliderDots();
            moveToSlide(currentIndex);
        }

        function createSliderDots() {
            const dotsContainer = document.getElementById('sliderDots');
            dotsContainer.innerHTML = '';
            const totalDots = slides.length - itemsPerSlide + 1;
            
            if (totalDots <= 1) return;

            for (let i = 0; i < totalDots; i++) {
                const dot = document.createElement('div');
                dot.className = `slider-dot ${i === currentIndex ? 'active' : ''}`;
                dot.addEventListener('click', () => {
                    moveToSlide(i);
                });
                dotsContainer.appendChild(dot);
            }
        }

        function moveToSlide(index) {
            const totalDots = slides.length - itemsPerSlide + 1;
            if (index < 0) index = 0;
            if (index >= totalDots) index = totalDots - 1;
            
            currentIndex = index;
            
            track.style.transition = 'transform 0.6s cubic-bezier(0.25, 1, 0.5, 1)';
            const amountToMove = -(currentIndex * (100 / itemsPerSlide));
            track.style.transform = `translateX(${amountToMove}%)`;
            
            const dots = document.querySelectorAll('#sliderDots .slider-dot');
            dots.forEach((dot, idx) => {
                if (idx === currentIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        function slideNext() {
            const totalDots = slides.length - itemsPerSlide + 1;
            if (currentIndex < totalDots - 1) {
                moveToSlide(currentIndex + 1);
            } else {
                moveToSlide(0); 
            }
        }

        function slidePrev() {
            if (currentIndex > 0) {
                moveToSlide(currentIndex - 1);
            } else {
                const totalDots = slides.length - itemsPerSlide + 1;
                moveToSlide(totalDots - 1); 
            }
        }

        let isDragging = false;
        let startX = 0;
        let currentTranslate = 0;
        let startTranslate = 0;
        let dragDistance = 0;

        function getX(event) {
            return event.type.includes('mouse') ? event.pageX : event.touches[0].pageX;
        }

        function dragStart(event) {
            isDragging = true;
            startX = getX(event);
            dragDistance = 0;
            track.style.transition = 'none';

            const containerWidth = sliderContainer.clientWidth;
            const slideWidth = containerWidth / itemsPerSlide;
            startTranslate = -currentIndex * slideWidth;
            currentTranslate = startTranslate;
        }

        function dragMove(event) {
            if (!isDragging) return;

            const currentX = getX(event);
            const deltaX = currentX - startX;
            dragDistance = Math.abs(deltaX);

            currentTranslate = startTranslate + deltaX;

            const maxTranslate = 0;
            const totalDots = slides.length - itemsPerSlide + 1;
            const minTranslate = -(totalDots - 1) * (sliderContainer.clientWidth / itemsPerSlide);

            if (currentTranslate > maxTranslate) {
                currentTranslate = maxTranslate + (currentTranslate - maxTranslate) * 0.3;
            } else if (currentTranslate < minTranslate) {
                currentTranslate = minTranslate + (currentTranslate - minTranslate) * 0.3;
            }

            track.style.transform = `translateX(${currentTranslate}px)`;
        }

        function dragEnd() {
            if (!isDragging) return;
            isDragging = false;

            const deltaX = currentTranslate - startTranslate;
            const slideWidth = sliderContainer.clientWidth / itemsPerSlide;
            const threshold = slideWidth * 0.2; 

            if (deltaX < -threshold) {
                const totalDots = slides.length - itemsPerSlide + 1;
                if (currentIndex < totalDots - 1) {
                    moveToSlide(currentIndex + 1);
                } else {
                    moveToSlide(currentIndex); 
                }
            } else if (deltaX > threshold) {
                if (currentIndex > 0) {
                    moveToSlide(currentIndex - 1);
                } else {
                    moveToSlide(currentIndex); 
                }
            } else {
                moveToSlide(currentIndex); 
            }
        }

        track.addEventListener('touchstart', dragStart, { passive: true });
        track.addEventListener('touchmove', dragMove, { passive: true });
        track.addEventListener('touchend', dragEnd);
        track.addEventListener('mousedown', (e) => {
            e.preventDefault(); 
            dragStart(e);
        });
        window.addEventListener('mousemove', dragMove);
        window.addEventListener('mouseup', dragEnd);

        track.addEventListener('click', (e) => {
            if (dragDistance > 10) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true); 

        let currentTestiIndex = 0;
        const testiSlidesEl = Array.from(document.querySelectorAll('.testimonial-slide'));
        const testiDots = Array.from(document.querySelectorAll('#testiSliderDots .slider-dot'));

        function showTestimonial(index) {
            if (testiSlidesEl.length === 0) return;
            
            if (index >= testiSlidesEl.length) {
                currentTestiIndex = 0;
            } else if (index < 0) {
                currentTestiIndex = testiSlidesEl.length - 1;
            } else {
                currentTestiIndex = index;
            }

            testiSlidesEl.forEach(slide => slide.classList.remove('active'));
            testiSlidesEl[currentTestiIndex].classList.add('active');

            if (testiDots.length > 0) {
                testiDots.forEach(dot => dot.classList.remove('active'));
                testiDots[currentTestiIndex].classList.add('active');
            }
        }

        function testiNext() {
            showTestimonial(currentTestiIndex + 1);
        }

        function testiPrev() {
            showTestimonial(currentTestiIndex - 1);
        }

        function currentTesti(index) {
            showTestimonial(index);
        }

        let testiInterval = setInterval(testiNext, 6000);

        const testiContainer = document.querySelector('.testi-slider-container');
        if (testiContainer) {
            testiContainer.addEventListener('mouseenter', () => clearInterval(testiInterval));
            testiContainer.addEventListener('mouseleave', () => testiInterval = setInterval(testiNext, 6000));
        }

        const targetDate = new Date("2026-07-07T07:00:00").getTime(); 

        function updateCountdown() {
            const now = new Date().getTime();
            const difference = targetDate - now;

            if (difference < 0) {
                document.getElementById('elegantCountdown').innerHTML = "<p style='color:#c5a880; font-weight:500;'>YAK! PROMO TELAH BERAKHIR</p>";
                return;
            }

            const d = Math.floor(difference / (1000 * 60 * 60 * 24));
            const h = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const m = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
            const s = Math.floor((difference % (1000 * 60)) / 1000);

            document.getElementById('days').innerText = d < 10 ? '0' + d : d;
            document.getElementById('hours').innerText = h < 10 ? '0' + h : h;
            document.getElementById('minutes').innerText = m < 10 ? '0' + m : m;
            document.getElementById('seconds').innerText = s < 10 ? '0' + s : s;
        }

        setInterval(updateCountdown, 1000);
        updateCountdown();

        let currentHeroBgIndex = 0;
        const heroBgSlides = Array.from(document.querySelectorAll('.hero-bg-slide'));

        function rotateHeroBackground() {
            if (heroBgSlides.length <= 1) return;
            heroBgSlides[currentHeroBgIndex].classList.remove('active');
            currentHeroBgIndex = (currentHeroBgIndex + 1) % heroBgSlides.length;
            heroBgSlides[currentHeroBgIndex].classList.add('active');
        }

        setInterval(rotateHeroBackground, 4000);

        window.addEventListener('resize', updateItemsPerSlide);
        window.addEventListener('load', () => {
            updateItemsPerSlide();
        });
    </script>
</body>
</html>
