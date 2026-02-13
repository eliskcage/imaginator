<?php
session_start();
require_once 'config.php';


if (isset($_POST['select_theme'])) {
    $_SESSION['theme'] = $_POST['theme'];
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMAGINATOR - See It Running</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #000 0%, #1a0a2e 50%, #000 100%);
            color: #fff;
            min-height: 100vh;
            padding: 20px;
            overflow-x: hidden;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .phone-section {
            display: flex;
            flex-direction: column;
            flex: 0 0 auto;
            max-width: 480px;
        }

        /* PAYMENT SECTION */
        .payment-section {
            margin-top: 30px;
            padding: 25px 20px;
            background: rgba(20,20,20,0.6);
            border-radius: 20px;
            border: 2px solid #333;
            opacity: 0.5;
            filter: grayscale(100%);
            pointer-events: none;
            position: relative;
            width: 100%;
            box-sizing: border-box;
        }

        .coming-soon-badge {
            position: absolute;
            top: -12px;
            right: 20px;
            background: #ff4444;
            color: #fff;
            padding: 5px 20px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            letter-spacing: 1px;
            box-shadow: 0 4px 10px rgba(255,68,68,0.4);
        }

        .free-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, #00ff88, #00cc66);
            color: #000;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            letter-spacing: 1px;
            box-shadow: 0 4px 12px rgba(0,255,136,0.4);
            z-index: 10;
        }

        .payment-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #fff;
            margin-bottom: 20px;
            text-align: center;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .payment-btn {
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border: 2px solid #444;
            border-radius: 12px;
            text-align: center;
            cursor: not-allowed;
            transition: all 0.3s;
        }

        .payment-icon {
            font-size: 2.5rem;
            margin-bottom: 8px;
        }

        .payment-label {
            font-size: 0.9rem;
            color: #aaa;
            font-weight: bold;
        }

        .google-login {
            width: 100%;
            padding: 18px;
            background: rgba(255,255,255,0.1);
            border: 2px solid #444;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            cursor: not-allowed;
        }

        .google-icon {
            font-size: 1.5rem;
        }

        .google-text {
            font-size: 1.1rem;
            font-weight: bold;
            color: #aaa;
        }

        /* EXTRA FEATURES - SQUARE BUTTONS ROW */
        .extra-features {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            width: 100%;
        }

        .feature-square-btn {
            aspect-ratio: 1;
            padding: 15px;
            background: rgba(255,255,255,0.05);
            border: 2px solid #333;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: not-allowed;
            opacity: 0.4;
            filter: grayscale(100%);
            pointer-events: none;
        }

        .feature-square-icon {
            font-size: 1.5rem;
        }

        .feature-square-label {
            font-size: 0.65rem;
            color: #aaa;
            text-align: center;
            font-weight: bold;
            line-height: 1.2;
        }

        .logo {
            font-size: 3rem;
            font-weight: bold;
            text-shadow: 0 0 30px rgba(255,0,255,0.5);
            margin-bottom: 10px;
        }

        .tagline {
            font-size: 1.2rem;
            color: #ff4444;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #888;
            font-size: 0.9rem;
        }

        .main-container {
            display: flex;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            align-items: flex-start;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* PHONE MOCKUP */
        .phone-preview {
            position: relative;
            width: 450px;
            height: 900px;
            background: #000;
            border-radius: 50px;
            border: 15px solid #222;
            box-shadow: 0 30px 60px rgba(0,0,0,0.8),
                        inset 0 0 0 3px #111;
            overflow: visible;
        }

        /* Diagonal DEMO Ribbon - Desktop & Mobile */
        .phone-preview::after {
            content: 'DEMO';
            position: absolute;
            top: 50px;
            right: -45px;
            background: linear-gradient(135deg, #ff4444, #cc0000);
            color: #fff;
            padding: 12px 60px;
            font-size: 1.1rem;
            font-weight: bold;
            letter-spacing: 3px;
            transform: rotate(45deg);
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
            z-index: 1000;
            border: 2px solid rgba(255,255,255,0.4);
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .phone-notch {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 180px;
            height: 35px;
            background: #000;
            border-radius: 0 0 25px 25px;
            z-index: 10;
        }

        .phone-screen {
            width: 100%;
            height: 100%;
            background: #000;
            position: relative;
            overflow: hidden;
        }

        .demo-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            padding: 20px;
            z-index: 5;
        }

        .demo-badge {
            background: #ff4444;
            color: #fff;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }

        .demo-text {
            font-size: 0.9rem;
            color: #ccc;
        }

        .phone-logo {
            position: absolute;
            top: 10px;
            right: -12px;
            width: 75px;
            height: 75px;
            border-radius: 50%;
            object-fit: cover;
            z-index: 9;
            opacity: 0.9;
        }

        .unmute-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .unmute-overlay:hover {
            background: rgba(0,0,0,0.8);
        }

        .unmute-button {
            font-size: 3rem;
            color: #fff;
            text-shadow: 0 0 20px rgba(255,255,255,0.5);
            animation: pulse 2s infinite;
        }

        /* CONTROLS SECTION */
        .controls-section {
            flex: 0 0 auto;
            width: 550px;
            max-width: 550px;
        }

        .control-card {
            background: rgba(20,20,20,0.8);
            border-radius: 20px;
            padding: 40px;
            border: 2px solid #333;
            margin-bottom: 20px;
        }

        .control-title {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #ff4444;
        }

        .control-desc {
            color: #aaa;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .upload-area {
            border: 3px dashed #444;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s;
            background: rgba(0,0,0,0.3);
        }

        .upload-area:hover {
            border-color: #ff4444;
            background: rgba(255,68,68,0.1);
        }

        .upload-icon {
            font-size: 4rem;
            margin-bottom: 15px;
        }

        .upload-text {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .upload-hint {
            font-size: 0.9rem;
            color: #666;
        }

        .theme-select {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .theme-btn {
            padding: 20px;
            border: 2px solid #333;
            border-radius: 10px;
            background: rgba(0,0,0,0.5);
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .theme-btn:hover {
            transform: translateY(-5px);
        }

        .theme-btn.glamour { border-color: #ff69b4; }
        .theme-btn.glamour:hover { background: rgba(255,105,180,0.2); box-shadow: 0 10px 30px rgba(255,105,180,0.3); }

        .theme-btn.action { border-color: #4169e1; }
        .theme-btn.action:hover { background: rgba(65,105,225,0.2); box-shadow: 0 10px 30px rgba(65,105,225,0.3); }

        .theme-btn.apocalypse { border-color: #228b22; }
        .theme-btn.apocalypse:hover { background: rgba(34,139,34,0.2); box-shadow: 0 10px 30px rgba(34,139,34,0.3); }

        .theme-icon { font-size: 2rem; margin-bottom: 10px; }
        .theme-name { font-size: 1rem; font-weight: bold; }

        .process-btn {
            width: 100%;
            padding: 20px;
            background: linear-gradient(45deg, #ff4444, #ff0066);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 1.3rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
        }

        .process-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 40px rgba(255,68,68,0.5);
        }

        .process-btn:disabled {
            background: #333;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 30px;
        }

        .feature {
            padding: 15px;
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
            border: 1px solid #333;
        }

        .feature-icon {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .feature-text {
            font-size: 0.9rem;
            color: #aaa;
        }

        .compress-btn {
            position: fixed;
            bottom: 20px;
            left: 20px;
            padding: 15px 25px;
            background: linear-gradient(45deg, #9333ea, #db2777);
            border: 2px solid #a855f7;
            border-radius: 10px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            z-index: 1000;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .compress-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(147,51,234,0.5);
        }

        .compress-result {
            position: fixed;
            bottom: 80px;
            left: 20px;
            padding: 15px 20px;
            background: rgba(0,0,0,0.9);
            border-radius: 10px;
            border: 2px solid #0f0;
            color: #0f0;
            font-size: 0.9rem;
            z-index: 999;
            max-width: 300px;
        }

        @media (max-width: 1200px) {
            .main-container {
                flex-direction: column;
            }
            .phone-preview {
                transform: scale(0.8);
            }
            .controls-section {
                max-width: 90%;
            }
        }

        /* MOBILE OPTIMIZED */
        @media (max-width: 768px) {
            body {
                padding: 0;
            }

            .main-container {
                flex-direction: column;
                padding: 15px;
                gap: 20px;
            }

            /* Phone section - normal position below form */
            .phone-section {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 100%;
                margin-top: 30px;
                order: 2; /* Appears after controls */
            }

            .header {
                text-align: center;
                margin-bottom: 20px;
                width: 100%;
            }

            /* Phone mockup - normal size on mobile */
            .phone-mockup {
                width: 300px;
                height: 600px;
                border-radius: 40px;
                border: 10px solid #222;
                position: relative;
                overflow: visible !important; /* For ribbon */
            }

            .phone-screen {
                overflow: hidden; /* Keep video inside */
            }

            .phone-notch {
                width: 120px;
                height: 25px;
                border-radius: 0 0 18px 18px;
            }

            .phone-logo {
                width: 50px;
                height: 50px;
                top: 6px;
                right: -8px;
            }

            .payment-section,
            .extra-features {
                display: none;
            }

            /* Controls appear first */
            .controls-section {
                order: 1;
            }

            /* Header - compact */
            .header {
                margin-bottom: 20px;
            }

            .logo {
                font-size: 2rem;
            }

            .tagline {
                font-size: 1rem;
            }

            .subtitle {
                font-size: 0.8rem;
            }

            /* Controls take full width */
            .controls-section {
                min-width: 100%;
                max-width: 100%;
                width: 100%;
                order: 1;
            }

            .control-card {
                padding: 25px;
                border-radius: 15px;
            }

            /* Buttons - full width, bigger touch targets */
            .control-card > a > div {
                padding: 30px 20px !important;
            }

            .control-card > a > div > div:first-child {
                font-size: 2.5rem !important;
            }

            .control-card > a > div > div:nth-child(2) {
                font-size: 1.2rem !important;
            }

            /* Upload area */
            .upload-area {
                padding: 30px 20px;
            }

            .upload-icon {
                font-size: 3rem;
            }

            /* Theme buttons - bigger */
            .theme-select {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .theme-btn {
                padding: 25px;
            }

            /* Process button - bigger */
            .process-btn {
                padding: 25px;
                font-size: 1.2rem;
            }

            /* Features grid - 2 columns */
            .features {
                grid-template-columns: repeat(2, 1fr);
            }

            /* Free badge - slightly smaller */
            .free-badge {
                padding: 5px 12px;
                font-size: 0.7rem;
            }
        }

        /* SMALL MOBILE */
        @media (max-width: 480px) {
            .logo {
                font-size: 1.5rem;
            }

            .tagline {
                font-size: 0.9rem;
            }

            .control-card {
                padding: 20px;
            }

            .control-card > a > div {
                padding: 25px 15px !important;
            }

            .control-card > a > div > div:first-child {
                font-size: 2rem !important;
            }

            .control-card > a > div > div:nth-child(2) {
                font-size: 1rem !important;
            }

            .theme-btn {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- PHONE SECTION: Header + Phone -->
        <div class="phone-section">
            <div class="header">
                <div class="logo">🎬 IMAGINATOR</div>
                <div class="tagline">IF YOU DON'T SEE IT RUNNING, IT AIN'T REAL</div>
                <div class="subtitle">Watch the demo → Upload yours → Get Hollywood results</div>
            </div>

            <!-- PHONE MOCKUP WITH DEMO -->
            <div class="phone-preview">
            <div class="phone-notch"></div>
            <div class="phone-screen">
                <video class="demo-video" autoplay loop muted playsinline id="demoVideo">
                    <source src="GIANTlove_compressed.mp4" type="video/mp4">
                </video>
                <img src="Sf.gif" alt="ShortFactory" class="phone-logo">
                <div class="unmute-overlay" id="unmuteOverlay">
                    <div class="unmute-button">🔊</div>
                </div>
                <div class="video-overlay">
                    <div class="demo-badge">🎥 LIVE DEMO</div>
                    <div class="demo-text">This is what your video will look like</div>
                </div>
            </div>
        </div>

        <!-- PAYMENT & LOGIN SECTION -->
        <div class="payment-section">
            <div class="coming-soon-badge">COMING SOON</div>
            <div class="payment-title">💳 Choose Payment Method</div>

            <div class="payment-methods">
                <div class="payment-btn">
                    <div class="payment-icon">💵</div>
                    <div class="payment-label">Cash</div>
                </div>
                <div class="payment-btn">
                    <div class="payment-icon">💳</div>
                    <div class="payment-label">Credit Card</div>
                </div>
                <div class="payment-btn">
                    <div class="payment-icon">₿</div>
                    <div class="payment-label">Crypto</div>
                </div>
            </div>

            <div class="google-login">
                <div class="google-icon">🔐</div>
                <div class="google-text">Login with Google</div>
            </div>

            <!-- EXTRA FEATURES - SQUARE ROW -->
            <div class="extra-features" style="margin-top:20px;">
            <div class="feature-square-btn">
                <div class="feature-square-icon">📱</div>
                <div class="feature-square-label">Mobile App</div>
            </div>

            <div class="feature-square-btn">
                <div class="feature-square-icon">☁️</div>
                <div class="feature-square-label">Cloud Storage</div>
            </div>

            <div class="feature-square-btn">
                <div class="feature-square-icon">👥</div>
                <div class="feature-square-label">Team Collab</div>
            </div>

            <div class="feature-square-btn">
                <div class="feature-square-icon">🎓</div>
                <div class="feature-square-label">Tutorials</div>
            </div>
        </div>

        </div><!-- End payment-section -->

        </div><!-- End phone-section -->

        <!-- UPLOAD & CONTROLS -->
        <div class="controls-section">
            <div class="control-card">
                <a href="https://www.shortfactory.shop/imaginator/stills.php" style="text-decoration:none;">
                    <div style="position:relative;background:linear-gradient(135deg,#ff4444,#cc0000);padding:40px;border-radius:20px;text-align:center;cursor:pointer;transition:all 0.3s;box-shadow:0 10px 30px rgba(255,68,68,0.3);margin-bottom:30px;" onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='0 15px 40px rgba(255,68,68,0.5)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 10px 30px rgba(255,68,68,0.3)'">
                        <div class="free-badge">FREE</div>
                        <div style="font-size:3rem;font-weight:bold;color:#fff;margin-bottom:15px;text-shadow:0 0 20px rgba(255,255,255,0.5);">STEP 1</div>
                        <div style="font-size:1.5rem;color:#fff;font-weight:bold;letter-spacing:2px;">TURN STILLS INTO VIDEO</div>
                        <div style="font-size:1rem;color:rgba(255,255,255,0.8);margin-top:10px;">Upload images + MP3 → Get WebM/MP4</div>
                    </div>
                </a>

                <a href="https://www.shortfactory.shop/imaginator/subtitles.php" style="text-decoration:none;">
                    <div style="position:relative;background:linear-gradient(135deg,#9944ff,#6600cc);padding:40px;border-radius:20px;text-align:center;cursor:pointer;transition:all 0.3s;box-shadow:0 10px 30px rgba(153,68,255,0.3);margin-bottom:30px;" onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='0 15px 40px rgba(153,68,255,0.5)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 10px 30px rgba(153,68,255,0.3)'">
                        <div class="free-badge">FREE</div>
                        <div style="font-size:3rem;font-weight:bold;color:#fff;margin-bottom:15px;text-shadow:0 0 20px rgba(255,255,255,0.5);">STEP 2</div>
                        <div style="font-size:1.5rem;color:#fff;font-weight:bold;letter-spacing:2px;">✨ KINETIC SUBTITLES ✨</div>
                        <div style="font-size:1rem;color:rgba(255,255,255,0.8);margin-top:10px;">AI-powered animated subtitles • WORLD'S FIRST</div>
                    </div>
                </a>

                <a href="https://www.shortfactory.shop/imaginator/subtitles_pro_REFERENCE.php" style="text-decoration:none;">
                    <div style="position:relative;background:linear-gradient(135deg,#ffd700,#ff8c00);padding:40px;border-radius:20px;text-align:center;cursor:pointer;transition:all 0.3s;box-shadow:0 10px 30px rgba(255,215,0,0.3);margin-bottom:30px;border:3px solid #ffd700;" onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='0 15px 40px rgba(255,215,0,0.6)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 10px 30px rgba(255,215,0,0.3)'">
                        <div style="position:absolute;top:10px;right:10px;background:#000;color:#ffd700;padding:5px 15px;border-radius:20px;font-size:0.7rem;font-weight:bold;letter-spacing:1px;border:2px solid #ffd700;animation:pulse 2s infinite;">💎 PRO</div>
                        <div style="font-size:3rem;font-weight:bold;color:#000;margin-bottom:15px;text-shadow:0 0 20px rgba(255,215,0,0.5);">PRO EDITOR</div>
                        <div style="font-size:1.5rem;color:#000;font-weight:bold;letter-spacing:2px;">🎬 TIMELINE EDITOR 🎬</div>
                        <div style="font-size:1rem;color:rgba(0,0,0,0.7);margin-top:10px;">Symmetrical progressive frames • Phone viewport • PAYWALL</div>
                        <div style="font-size:0.85rem;color:rgba(0,0,0,0.6);margin-top:8px;font-style:italic;">👀 Sneak peek: Watch us develop it live!</div>
                    </div>
                </a>

                <form method="POST" enctype="multipart/form-data">
                    <div class="upload-area" onclick="document.getElementById('videoInput').click()">
                        <div class="upload-icon">📱</div>
                        <div class="upload-text">Drop Your Video Here</div>
                        <div class="upload-hint">Or click to browse • MP4, MOV, WEBM</div>
                    </div>
                    <input type="file" id="videoInput" name="video" accept="video/*" style="display:none">

                    <div class="theme-select">
                        <label class="theme-btn glamour">
                            <input type="radio" name="theme" value="girl" style="display:none">
                            <div class="theme-icon">💖</div>
                            <div class="theme-name">Glamour</div>
                        </label>
                        <label class="theme-btn action">
                            <input type="radio" name="theme" value="boy" style="display:none" checked>
                            <div class="theme-icon">⚡</div>
                            <div class="theme-name">Action</div>
                        </label>
                        <label class="theme-btn apocalypse">
                            <input type="radio" name="theme" value="zombie" style="display:none">
                            <div class="theme-icon">🧟</div>
                            <div class="theme-name">Apocalypse</div>
                        </label>
                    </div>

                    <button type="submit" name="select_theme" class="process-btn" id="processBtn" disabled>
                        🎬 Transform My Video
                    </button>

                    <div id="uploadProgress" style="display:none;margin-top:20px;">
                        <div style="background:rgba(0,0,0,0.5);border-radius:10px;height:40px;overflow:hidden;border:2px solid #333;">
                            <div id="progressBar" style="height:100%;background:linear-gradient(90deg,#ff4444,#ff0066);width:0%;transition:width 0.3s;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;"></div>
                        </div>
                        <div id="uploadStatus" style="text-align:center;margin-top:10px;color:#aaa;">Uploading...</div>
                    </div>
                </form>

                <div class="features">
                    <div class="feature">
                        <div class="feature-icon">⚡</div>
                        <div class="feature-text">30sec processing</div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">🎨</div>
                        <div class="feature-text">Hollywood FX</div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">📱</div>
                        <div class="feature-text">Mobile-ready</div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">💾</div>
                        <div class="feature-text">Instant download</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Unmute overlay functionality
        const demoVideo = document.getElementById('demoVideo');
        const unmuteOverlay = document.getElementById('unmuteOverlay');

        if (unmuteOverlay && demoVideo) {
            unmuteOverlay.addEventListener('click', () => {
                demoVideo.muted = false;
                demoVideo.play();
                unmuteOverlay.style.opacity = '0';
                setTimeout(() => {
                    unmuteOverlay.style.display = 'none';
                }, 300);
            });
        }

        const videoInput = document.getElementById('videoInput');
        const processBtn = document.getElementById('processBtn');
        const uploadArea = document.querySelector('.upload-area');

        videoInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const fileName = file.name;
                const fileSize = file.size;
                const maxSize = 100 * 1024 * 1024; // 100MB

                // Check file size
                if (fileSize > maxSize) {
                    uploadArea.innerHTML = `
                        <div class="upload-icon">❌</div>
                        <div class="upload-text" style="color:#f66;">File too big!</div>
                        <div class="upload-hint">Max 100MB (3-min videos)<br>Yours: ${(fileSize / 1024 / 1024).toFixed(1)}MB</div>
                    `;
                    processBtn.disabled = true;
                    videoInput.value = '';
                    return;
                }

                // Check duration (requires video element)
                const video = document.createElement('video');
                video.preload = 'metadata';
                video.onloadedmetadata = function() {
                    window.URL.revokeObjectURL(video.src);
                    const duration = video.duration;
                    const width = video.videoWidth;
                    const height = video.videoHeight;
                    const aspectRatio = width / height;

                    // Check if vertical (9:16 = 0.5625, allow some tolerance)
                    const isVertical = aspectRatio < 0.75; // Allows 9:16 and similar vertical formats

                    if (!isVertical) {
                        uploadArea.innerHTML = `
                            <div class="upload-icon">❌</div>
                            <div class="upload-text" style="color:#f66;">Must be vertical!</div>
                            <div class="upload-hint">Need 9:16 portrait (mobile)<br>Yours: ${width}x${height} (landscape)</div>
                        `;
                        processBtn.disabled = true;
                        videoInput.value = '';
                    } else if (duration > 180) { // 3 minutes = 180 seconds
                        uploadArea.innerHTML = `
                            <div class="upload-icon">❌</div>
                            <div class="upload-text" style="color:#f66;">Video too long!</div>
                            <div class="upload-hint">Max 3 minutes<br>Yours: ${Math.floor(duration / 60)}:${Math.floor(duration % 60).toString().padStart(2, '0')}</div>
                        `;
                        processBtn.disabled = true;
                        videoInput.value = '';
                    } else {
                        uploadArea.innerHTML = `
                            <div class="upload-icon">✅</div>
                            <div class="upload-text">${fileName}</div>
                            <div class="upload-hint">${width}x${height} • ${(fileSize / 1024 / 1024).toFixed(1)}MB • ${Math.floor(duration / 60)}:${Math.floor(duration % 60).toString().padStart(2, '0')}</div>
                        `;
                        processBtn.disabled = false;
                    }
                };
                video.src = URL.createObjectURL(file);
            }
        });

        // Theme button selection
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.theme-btn').forEach(b => b.style.opacity = '0.5');
                btn.style.opacity = '1';
            });
        });

        // Handle form submission with progress
        const form = document.querySelector('form');
        const uploadProgress = document.getElementById('uploadProgress');
        const progressBar = document.getElementById('progressBar');
        const uploadStatus = document.getElementById('uploadStatus');

        form.addEventListener('submit', (e) => {
            e.preventDefault();

            // Disable button to prevent double-click
            processBtn.disabled = true;
            processBtn.innerHTML = '⏳ Uploading...';
            processBtn.style.opacity = '0.6';

            // Show progress bar
            uploadProgress.style.display = 'block';

            // Use XMLHttpRequest to track upload progress
            const xhr = new XMLHttpRequest();
            const formData = new FormData(form);

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                    progressBar.textContent = percent + '%';
                    uploadStatus.textContent = `Uploading... ${percent}%`;
                }
            });

            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    uploadStatus.textContent = '✅ Upload complete! Processing...';
                    progressBar.textContent = '100%';
                    // Redirect or show success
                    window.location.href = xhr.responseURL || 'dashboard.php';
                } else {
                    uploadStatus.innerHTML = '<span style="color:#f66;">❌ Upload failed. Try again.</span>';
                    processBtn.disabled = false;
                    processBtn.innerHTML = '🎬 Transform My Video';
                    processBtn.style.opacity = '1';
                }
            });

            xhr.addEventListener('error', () => {
                uploadStatus.innerHTML = '<span style="color:#f66;">❌ Upload error. Check connection.</span>';
                processBtn.disabled = false;
                processBtn.innerHTML = '🎬 Transform My Video';
                processBtn.style.opacity = '1';
            });

            xhr.open('POST', form.action || window.location.href);
            xhr.send(formData);
        });
    </script>

</body>
</html>
