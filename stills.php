<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STILLS IMAGINATOR - Turn Images Into Magic</title>
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
            margin-bottom: 40px;
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
            gap: 40px;
            max-width: 1400px;
            margin: 0 auto;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* PHONE MOCKUP */
        .phone-preview {
            position: relative;
            width: 350px;
            height: 700px;
            background: #000;
            border-radius: 50px;
            border: 12px solid #222;
            box-shadow: 0 30px 60px rgba(0,0,0,0.8),
                        inset 0 0 0 2px #111;
            overflow: hidden;
        }

        .phone-notch {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 30px;
            background: #000;
            border-radius: 0 0 20px 20px;
            z-index: 10;
        }

        .phone-screen {
            width: 100%;
            height: 100%;
            background: #000;
            position: relative;
            overflow: hidden;
        }

        .demo-carousel {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .demo-carousel img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .demo-carousel img.active {
            opacity: 1;
            z-index: 2;
        }

        /* HOLLYWOOD TRANSITIONS FROM UUU */
        .transition-slide-left { transform: translateX(-100%); opacity: 0; }
        .transition-slide-left.active { transform: translateX(0); opacity: 1; transition: all 1s; }

        .transition-slide-right { transform: translateX(100%); opacity: 0; }
        .transition-slide-right.active { transform: translateX(0); opacity: 1; transition: all 1s; }

        .transition-zoom-in { transform: scale(0); opacity: 0; }
        .transition-zoom-in.active { transform: scale(1); opacity: 1; transition: all 1s; }

        .transition-zoom-out { transform: scale(2); opacity: 0; }
        .transition-zoom-out.active { transform: scale(1); opacity: 1; transition: all 1s; }

        .transition-rotate { transform: rotate(360deg); opacity: 0; }
        .transition-rotate.active { transform: rotate(0); opacity: 1; transition: all 1s; }

        .transition-flip { transform: rotateY(180deg); opacity: 0; }
        .transition-flip.active { transform: rotateY(0); opacity: 1; transition: all 1s; }

        .transition-blur { filter: blur(10px); opacity: 0; }
        .transition-blur.active { filter: blur(0); opacity: 1; transition: all 1s; }

        .transition-bounce.active { animation: bounceTrans 1s ease-in-out; opacity: 1; }
        @keyframes bounceTrans { 0%{transform:translateY(-100%);} 50%{transform:translateY(20%);} 100%{transform:translateY(0);} }

        .transition-shake.active { animation: shakeTrans 1s ease-in-out; opacity: 1; }
        @keyframes shakeTrans { 0%,100%{transform:translateX(0);} 20%,60%{transform:translateX(-10px);} 40%,80%{transform:translateX(10px);} }

        .transition-pulse.active { animation: pulseTrans 1s ease-in-out; opacity: 1; }
        @keyframes pulseTrans { 0%{transform:scale(1);} 50%{transform:scale(1.1);} 100%{transform:scale(1);} }

        .transition-swing.active { animation: swingTrans 1s ease-in-out; opacity: 1; }
        @keyframes swingTrans { 20%{transform:rotate(15deg);} 40%{transform:rotate(-10deg);} 60%{transform:rotate(5deg);} 80%{transform:rotate(-5deg);} 100%{transform:rotate(0);} }

        .transition-wobble.active { animation: wobbleTrans 1s ease-in-out; opacity: 1; }
        @keyframes wobbleTrans { 0%{transform:translateX(0);} 15%{transform:translateX(-25%) rotate(-5deg);} 30%{transform:translateX(20%) rotate(3deg);} 45%{transform:translateX(-15%) rotate(-3deg);} 60%{transform:translateX(10%) rotate(2deg);} 75%{transform:translateX(-5%) rotate(-1deg);} 100%{transform:translateX(0);} }

        .transition-jello.active { animation: jelloTrans 1s ease-in-out; opacity: 1; }
        @keyframes jelloTrans { 0%,100%{transform:skew(0);} 30%{transform:skew(-25deg,-15deg);} 40%{transform:skew(20deg,10deg);} 50%{transform:skew(-15deg,-5deg);} 65%{transform:skew(10deg,5deg);} 75%{transform:skew(-5deg,-5deg);} }

        .transition-rubber.active { animation: rubberTrans 1s ease-in-out; opacity: 1; }
        @keyframes rubberTrans { 0%{transform:scale(1);} 30%{transform:scaleX(1.25) scaleY(0.75);} 40%{transform:scaleX(0.75) scaleY(1.25);} 50%{transform:scaleX(1.15) scaleY(0.85);} 65%{transform:scaleX(0.95) scaleY(1.05);} 75%{transform:scaleX(1.05) scaleY(0.95);} 100%{transform:scale(1);} }

        .transition-tada.active { animation: tadaTrans 1s ease-in-out; opacity: 1; }
        @keyframes tadaTrans { 0%{transform:scale(1);} 10%,20%{transform:scale(0.9) rotate(-3deg);} 30%,50%,70%,90%{transform:scale(1.1) rotate(3deg);} 40%,60%,80%{transform:scale(1.1) rotate(-3deg);} 100%{transform:scale(1) rotate(0);} }

        .transition-flash.active { animation: flashTrans 1s ease-in-out; }
        @keyframes flashTrans { 0%,50%,100%{opacity:1;} 25%,75%{opacity:0;} }

        .transition-roll-in { transform: translateX(-100%) rotate(-120deg); opacity: 0; }
        .transition-roll-in.active { transform: translateX(0) rotate(0); opacity: 1; transition: all 1s; }

        .transition-bounce-in.active { animation: bounceInTrans 1s ease-in-out; opacity: 1; }
        @keyframes bounceInTrans { 0%{opacity:0;transform:scale(0.3);} 50%{opacity:1;transform:scale(1.05);} 70%{transform:scale(0.9);} 100%{transform:scale(1);} }

        .transition-fade-slide { opacity: 0; transform: translateY(50px); }
        .transition-fade-slide.active { opacity: 1; transform: translateY(0); transition: all 1s; }

        .phone-logo {
            position: absolute;
            top: 8px;
            right: -10px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            z-index: 9;
            opacity: 0.9;
        }

        .watermark {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: rgba(0,0,0,0.7);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            color: #ff4444;
            z-index: 8;
            text-shadow: 0 0 10px rgba(255,68,68,0.5);
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

        /* CONTROLS SECTION */
        .controls-section {
            flex: 1;
            min-width: 400px;
            max-width: 600px;
        }

        .control-card {
            background: rgba(20,20,20,0.8);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,68,68,0.2);
        }

        .control-title {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: #ff4444;
        }

        .control-subtitle {
            color: #888;
            margin-bottom: 30px;
        }

        .upload-area {
            border: 3px dashed rgba(255,68,68,0.5);
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
            background: rgba(255,68,68,0.05);
        }

        .upload-area:hover {
            border-color: #ff4444;
            background: rgba(255,68,68,0.1);
        }

        .upload-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .upload-text {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .upload-hint {
            color: #888;
            font-size: 0.9rem;
        }

        input[type="file"] {
            display: none;
        }

        .process-btn {
            width: 100%;
            padding: 20px;
            background: linear-gradient(135deg, #ff4444, #cc0000);
            color: #fff;
            border: none;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .process-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255,68,68,0.4);
        }

        .process-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .progress-bar {
            width: 100%;
            height: 30px;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            overflow: hidden;
            margin-top: 20px;
            display: none;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff4444, #ff8844);
            width: 0%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .file-list {
            margin-top: 20px;
            padding: 20px;
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
            max-height: 200px;
            overflow-y: auto;
        }

        .file-item {
            padding: 10px;
            background: rgba(255,68,68,0.1);
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            .phone-preview {
                width: 280px;
                height: 560px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">🎬 STILLS IMAGINATOR</div>
        <div class="tagline">Turn Your Images Into Hollywood Magic</div>
        <div class="subtitle">Upload MP3 + Stills → Get Amazing Short Video</div>
    </div>

    <div class="main-container">
        <!-- PHONE MOCKUP DEMO -->
        <div class="phone-preview">
            <div class="phone-notch"></div>
            <div class="phone-screen">
                <div class="demo-carousel" id="demoCarousel">
                    <!-- Demo images will cycle here -->
                    <img src="1.jpg" alt="Demo 1" class="active">
                    <img src="2.jpg" alt="Demo 2">
                    <img src="3.jpg" alt="Demo 3">
                    <img src="4.jpg" alt="Demo 4">
                    <img src="5.jpg" alt="Demo 5">
                </div>
                <img src="Sf.gif" alt="ShortFactory" class="phone-logo">
                <div class="watermark">SHORTFACTORY</div>
                <div class="video-overlay">
                    <div class="demo-badge">🎥 LIVE DEMO</div>
                    <div class="demo-text">Stills → Video with transitions</div>
                </div>
            </div>
        </div>

        <!-- UPLOAD CONTROLS -->
        <div class="controls-section">
            <div class="control-card">
                <h2 class="control-title">Create Your Short</h2>
                <p class="control-subtitle">Upload MP3 music + your still images (up to 40)</p>

                <form id="uploadForm" enctype="multipart/form-data">
                    <!-- MP3 UPLOAD -->
                    <div class="upload-area" onclick="document.getElementById('audioInput').click()">
                        <div class="upload-icon">🎵</div>
                        <div class="upload-text" id="audioText">Drop your MP3 here</div>
                        <div class="upload-hint">Max 10MB | MP3 only</div>
                    </div>
                    <input type="file" id="audioInput" accept="audio/mp3,audio/mpeg" />

                    <!-- IMAGES UPLOAD -->
                    <div class="upload-area" onclick="document.getElementById('imagesInput').click()">
                        <div class="upload-icon">🖼️</div>
                        <div class="upload-text" id="imagesText">Drop your images here</div>
                        <div class="upload-hint">Up to 40 images | JPG, PNG</div>
                    </div>
                    <input type="file" id="imagesInput" accept="image/jpeg,image/jpg,image/png" multiple />

                    <div class="file-list" id="fileList" style="display:none;"></div>

                    <button type="submit" class="process-btn" id="processBtn" disabled>
                        🎬 Create Video
                    </button>

                    <div class="progress-bar" id="progressBar">
                        <div class="progress-fill" id="progressFill">0%</div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Demo carousel animation with HOLLYWOOD TRANSITIONS
        const demoImages = document.querySelectorAll('#demoCarousel img');
        let currentIndex = 0;

        const transitions = [
            'transition-slide-left',
            'transition-slide-right',
            'transition-zoom-in',
            'transition-zoom-out',
            'transition-rotate',
            'transition-flip',
            'transition-blur',
            'transition-bounce',
            'transition-shake',
            'transition-pulse',
            'transition-swing',
            'transition-wobble',
            'transition-jello',
            'transition-rubber',
            'transition-tada',
            'transition-flash',
            'transition-roll-in',
            'transition-bounce-in',
            'transition-fade-slide'
        ];

        function getRandomTransition() {
            return transitions[Math.floor(Math.random() * transitions.length)];
        }

        setInterval(() => {
            // Remove active and transition from current
            const currentImg = demoImages[currentIndex];
            currentImg.classList.remove('active');
            transitions.forEach(t => currentImg.classList.remove(t));

            // Move to next
            currentIndex = (currentIndex + 1) % demoImages.length;
            const nextImg = demoImages[currentIndex];

            // Add random transition and active
            const randomTransition = getRandomTransition();
            nextImg.classList.add(randomTransition);

            setTimeout(() => {
                nextImg.classList.add('active');
            }, 50);
        }, 2500);

        // File upload handling
        const audioInput = document.getElementById('audioInput');
        const imagesInput = document.getElementById('imagesInput');
        const processBtn = document.getElementById('processBtn');
        const fileList = document.getElementById('fileList');
        const audioText = document.getElementById('audioText');
        const imagesText = document.getElementById('imagesText');

        let audioFile = null;
        let imageFiles = [];

        audioInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const maxSize = 10 * 1024 * 1024; // 10MB

                if (file.size > maxSize) {
                    audioText.innerHTML = '❌ File too big! Max 10MB';
                    audioText.style.color = '#f66';
                    audioFile = null;
                } else {
                    audioFile = file;
                    audioText.innerHTML = `✅ ${file.name}`;
                    audioText.style.color = '#4f4';
                }
                checkReadyToProcess();
            }
        });

        imagesInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                imageFiles = Array.from(e.target.files).slice(0, 40); // Max 40 images

                if (imageFiles.length > 0) {
                    imagesText.innerHTML = `✅ ${imageFiles.length} images selected`;
                    imagesText.style.color = '#4f4';

                    // Show file list
                    fileList.style.display = 'block';
                    fileList.innerHTML = imageFiles.map((f, i) =>
                        `<div class="file-item"><span>${i+1}. ${f.name}</span><span>${(f.size/1024).toFixed(1)}KB</span></div>`
                    ).join('');
                }
                checkReadyToProcess();
            }
        });

        function checkReadyToProcess() {
            processBtn.disabled = !(audioFile && imageFiles.length > 0);
        }

        // Form submission
        const form = document.getElementById('uploadForm');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');

        form.addEventListener('submit', (e) => {
            e.preventDefault();

            processBtn.disabled = true;
            processBtn.innerHTML = '⏳ Creating Magic...';
            progressBar.style.display = 'block';

            const formData = new FormData();
            formData.append('audio', audioFile);
            imageFiles.forEach((file, index) => {
                formData.append(`image_${index}`, file);
            });

            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressFill.style.width = percent + '%';
                    progressFill.textContent = percent + '%';
                }
            });

            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        progressFill.textContent = '✅ Done!';
                        progressFill.style.background = 'linear-gradient(90deg, #44ff44, #88ff44)';

                        // Download the video
                        setTimeout(() => {
                            window.location.href = response.download_url;
                        }, 1000);
                    } else {
                        progressFill.textContent = '❌ Failed';
                        progressFill.style.background = '#f44';
                        alert('Error: ' + response.message);
                    }
                } else {
                    progressFill.textContent = '❌ Error';
                    progressFill.style.background = '#f44';
                }

                processBtn.disabled = false;
                processBtn.innerHTML = '🎬 Create Video';
            });

            xhr.open('POST', 'process_stills.php', true);
            xhr.send(formData);
        });
    </script>
</body>
</html>
