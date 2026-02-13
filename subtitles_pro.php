<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KINETIC EDITOR - ShortFactory</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            background: #0a0a0a;
            color: #fff;
            overflow: hidden;
        }

        .editor-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        /* TOP BAR - SIMPLIFIED */
        .top-bar {
            height: 80px;
            background: linear-gradient(135deg, #1a0a2e 0%, #000 100%);
            border-bottom: 4px solid #ff4444;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 40px;
            gap: 30px;
        }

        .top-bar h1 {
            font-size: 2.5rem;
            color: #ff4444;
            margin-right: auto;
            text-shadow: 0 0 20px rgba(255,68,68,0.6);
        }

        .btn {
            padding: 18px 35px;
            border: none;
            border-radius: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            font-size: 1.1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff4444, #cc0000);
            color: #fff;
            font-size: 1.4rem;
        }

        .btn-primary:hover {
            transform: scale(1.08);
            box-shadow: 0 10px 30px rgba(255,68,68,0.6);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.15);
            color: #fff;
            border: 3px solid rgba(255,255,255,0.4);
            font-size: 1.2rem;
        }

        /* MAIN CONTENT - TIMELINE WITH PHONE OVERLAY */
        .main-content {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* SCROLLABLE TIMELINE - FULL SCREEN */
        .timeline-scroll {
            width: 100%;
            height: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            position: absolute;
            top: 0;
            left: 0;
            background: #0a0a0a;
            cursor: grab;
            display: flex;
            align-items: center;
        }

        .timeline-scroll:active {
            cursor: grabbing;
        }

        .timeline-canvas {
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 50vw;
            position: relative;
        }

        /* FRAMES WITH PROGRESSIVE SIZING */
        .frame-thumb {
            background-size: cover;
            background-position: center;
            border: 1px solid #111;
            border-radius: 3px;
            flex-shrink: 0;
            transition: all 0.2s ease-out;
            margin: 0 1px;
            position: relative;
        }

        .frame-thumb.current {
            border: 0;
            box-shadow: 0 0 40px rgba(255,68,68,0.5);
        }

        /* PHONE OUTLINE - FIXED CENTER OVERLAY */
        .phone-outline {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            height: 920px;
            border-radius: 55px;
            border: 15px solid #1a1a1a;
            box-shadow:
                0 0 0 3px #444,
                0 40px 100px rgba(0,0,0,0.9),
                inset 0 0 80px rgba(0,0,0,0.5);
            pointer-events: none;
            z-index: 100;
        }

        /* PHONE NOTCH */
        .phone-outline::before {
            content: '';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 30px;
            background: #1a1a1a;
            border-radius: 0 0 20px 20px;
        }

        /* SUBTITLE OVERLAY - FIXED BOTTOM OF PHONE */
        .subtitle-overlay-fixed {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 440px;
            height: 860px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 100px;
            pointer-events: none;
            z-index: 150;
        }

        #subtitleOverlay {
            text-align: center;
            font-size: 40px;
            font-weight: bold;
            color: #fff;
            text-shadow:
                3px 3px 8px rgba(0,0,0,1),
                0 0 20px rgba(0,0,0,0.8);
            padding: 15px 25px;
            max-width: 100%;
        }

        /* TIME DISPLAY - TOP CENTER */
        .time-display-fixed {
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 150;
        }

        .time-display {
            font-size: 3.5rem;
            font-family: monospace;
            color: #ff4444;
            text-shadow:
                0 0 30px rgba(255,68,68,0.8),
                0 0 60px rgba(255,68,68,0.4);
            font-weight: bold;
        }

        /* PLAYHEAD INDICATOR - FIXED CENTER */
        .playhead {
            position: fixed;
            left: 50%;
            top: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(180deg,
                rgba(255,68,68,0) 0%,
                rgba(255,68,68,0.8) 20%,
                rgba(255,68,68,0.8) 80%,
                rgba(255,68,68,0) 100%);
            z-index: 90;
            pointer-events: none;
            transform: translateX(-50%);
        }

        .playhead::before {
            content: '▼';
            position: absolute;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            color: #ff4444;
            font-size: 2.5rem;
            text-shadow: 0 0 20px rgba(255,68,68,1);
        }

        /* CONTROLS - BOTTOM CENTER */
        .controls-fixed {
            position: fixed;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 150;
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .play-btn-big {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff4444, #cc0000);
            border: none;
            color: #fff;
            font-size: 2.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            box-shadow: 0 8px 30px rgba(255,68,68,0.5);
        }

        .play-btn-big:hover {
            transform: scale(1.15);
            box-shadow: 0 12px 40px rgba(255,68,68,0.8);
        }

        #statusText {
            font-size: 1.4rem;
            color: #ff4444;
            font-weight: bold;
            text-shadow: 0 0 10px rgba(255,68,68,0.5);
        }

        /* INSTRUCTIONS */
        .instructions {
            position: fixed;
            bottom: 140px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 1.3rem;
            color: rgba(255,255,255,0.6);
            text-align: center;
            z-index: 150;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.8; }
        }

        /* MOBILE OPTIMIZED - OCD PRECISION */
        @media (max-width: 768px) {
            /* Top bar - compact */
            .top-bar {
                height: 60px;
                padding: 0 15px;
                gap: 10px;
            }

            .top-bar h1 {
                font-size: 1.3rem;
            }

            .btn {
                padding: 12px 20px;
                font-size: 0.85rem;
                border-radius: 10px;
            }

            .btn-primary {
                font-size: 1rem;
            }

            .btn-secondary {
                font-size: 0.9rem;
                padding: 10px 16px;
            }

            /* Phone outline - smaller, top aligned */
            .phone-outline {
                width: 280px;
                height: 560px;
                border-radius: 30px;
                border: 8px solid #1a1a1a;
                box-shadow: 0 0 0 2px #444, 0 20px 40px rgba(0,0,0,0.8);
            }

            .phone-outline::before {
                width: 80px;
                height: 18px;
                border-radius: 0 0 12px 12px;
            }

            /* Subtitle overlay - proportional */
            .subtitle-overlay-fixed {
                width: 250px;
                height: 530px;
                padding-bottom: 60px;
            }

            #subtitleOverlay {
                font-size: 22px;
                padding: 10px 15px;
            }

            /* Time display - smaller */
            .time-display-fixed {
                top: 70px;
            }

            .time-display {
                font-size: 2rem;
            }

            /* Playhead - adjusted */
            .playhead {
                top: calc(60px);
                height: calc(100vh - 60px - 140px);
                width: 3px;
            }

            .playhead::before {
                font-size: 1.5rem;
                top: -20px;
            }

            /* Timeline area - compact bottom controls */
            .timeline-area {
                height: 140px;
            }

            .timeline-controls {
                height: 50px;
                padding: 0 15px;
                gap: 12px;
            }

            .play-btn-big {
                width: 50px;
                height: 50px;
                font-size: 1.8rem;
            }

            #statusText {
                font-size: 0.95rem;
            }

            /* Controls at bottom - proper spacing */
            .controls-fixed {
                bottom: 20px;
                gap: 15px;
            }

            /* Instructions - smaller */
            .instructions {
                bottom: 80px;
                font-size: 0.95rem;
                padding: 0 20px;
            }

            /* Timeline canvas - proper scrolling */
            .timeline-canvas {
                padding: 0 50vw;
            }

            /* Frame thumbs - proportional sizing */
            .frame-thumb {
                border: 1px solid #333;
                border-radius: 4px;
                margin: 0 2px;
            }
        }

        /* SMALL MOBILE - ULTRA COMPACT */
        @media (max-width: 480px) {
            .top-bar {
                height: 50px;
                padding: 0 10px;
            }

            .top-bar h1 {
                font-size: 1.1rem;
            }

            .btn {
                padding: 8px 12px;
                font-size: 0.75rem;
            }

            .phone-outline {
                width: 220px;
                height: 440px;
                border-radius: 25px;
                border: 6px solid #1a1a1a;
            }

            .subtitle-overlay-fixed {
                width: 200px;
                height: 420px;
            }

            #subtitleOverlay {
                font-size: 18px;
            }

            .time-display {
                font-size: 1.6rem;
            }

            .play-btn-big {
                width: 45px;
                height: 45px;
                font-size: 1.6rem;
            }

            #statusText {
                font-size: 0.85rem;
            }

            .instructions {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="editor-container">
        <!-- TOP BAR -->
        <div class="top-bar">
            <h1>🎬 KINETIC EDITOR</h1>
            <button class="btn btn-secondary" onclick="document.getElementById('srtUpload').click()">📄 LOAD LYRICS</button>
            <input type="file" id="srtUpload" accept=".srt" style="display:none" onchange="loadSRT(this)">
        </div>

        <div class="main-content">
            <!-- SCROLLABLE TIMELINE (BACKGROUND) -->
            <div class="timeline-scroll" id="timelineScroll">
                <div class="timeline-canvas" id="timelineCanvas">
                    <!-- Frames loaded here -->
                </div>
            </div>

            <!-- PHONE OUTLINE (OVERLAY) -->
            <div class="phone-outline"></div>

            <!-- SUBTITLE OVERLAY -->
            <div class="subtitle-overlay-fixed">
                <div id="subtitleOverlay"></div>
            </div>

            <!-- PLAYHEAD -->
            <div class="playhead"></div>

            <!-- TIME DISPLAY -->
            <div class="time-display-fixed">
                <div class="time-display" id="timeDisplay">00:00.000</div>
            </div>

            <!-- CONTROLS -->
            <div class="controls-fixed">
                <button class="play-btn-big" id="playBtn" onclick="togglePlay()">▶️</button>
                <span id="statusText">Drag timeline to explore</span>
            </div>

            <!-- INSTRUCTIONS -->
            <div class="instructions">
                ← Scroll left & right to move through video →
            </div>
        </div>
    </div>

    <script>
        // CONFIGURATION
        const TOTAL_DURATION = 218; // seconds
        const TOTAL_FRAMES = 218;
        const RENDER_WINDOW = 100; // Only render ±100 frames for performance

        let currentTime = 0;
        let subtitles = [];
        let isPlaying = false;
        let frameElements = {}; // Only rendered frames

        // SYMMETRICAL SIZING - 1% at ±25 frames, 100% at center
        function getFrameSize(frameIndex, currentIndex) {
            const distance = Math.abs(frameIndex - currentIndex);

            // Smooth scale from 100% (distance 0) to 1% (distance 25+)
            // Simple linear falloff
            const scale = Math.max(0.01, 1 - (distance / 25));

            return {
                width: Math.floor(460 * scale),
                height: Math.floor(870 * scale),
                blur: Math.min(4, distance * 0.15),
                opacity: Math.max(0.2, scale)
            };
        }

        // RENDER ONLY VISIBLE FRAMES (VIRTUAL SCROLLING)
        function renderFrames() {
            const currentIndex = Math.floor(currentTime) + 1;
            const start = Math.max(1, currentIndex - RENDER_WINDOW);
            const end = Math.min(TOTAL_FRAMES, currentIndex + RENDER_WINDOW);
            const canvas = document.getElementById('timelineCanvas');

            // Remove frames outside window
            Object.keys(frameElements).forEach(idx => {
                const frameIdx = parseInt(idx);
                if (frameIdx < start || frameIdx > end) {
                    frameElements[idx].remove();
                    delete frameElements[idx];
                }
            });

            // Add frames in window
            for (let i = start; i <= end; i++) {
                if (!frameElements[i]) {
                    const frame = document.createElement('div');
                    frame.className = 'frame-thumb';
                    frame.style.backgroundImage = `url('frames/frame_${String(i).padStart(4, '0')}.jpg')`;
                    frame.dataset.index = i;

                    frame.addEventListener('click', () => {
                        currentTime = i - 1;
                        updateDisplay();
                    });

                    // Insert in order
                    let inserted = false;
                    const keys = Object.keys(frameElements).map(k => parseInt(k)).sort((a, b) => a - b);
                    for (let key of keys) {
                        if (key > i) {
                            canvas.insertBefore(frame, frameElements[key]);
                            inserted = true;
                            break;
                        }
                    }
                    if (!inserted) canvas.appendChild(frame);

                    frameElements[i] = frame;
                }
            }

            updateFrameSizes();
        }

        // UPDATE FRAME SIZES - SMOOTH TRANSITIONS
        function updateFrameSizes() {
            const currentIndex = Math.floor(currentTime) + 1;

            Object.keys(frameElements).forEach(idx => {
                const frameIdx = parseInt(idx);
                const frame = frameElements[idx];
                const size = getFrameSize(frameIdx, currentIndex);

                frame.style.width = size.width + 'px';
                frame.style.height = size.height + 'px';
                frame.style.filter = `blur(${size.blur}px)`;
                frame.style.opacity = size.opacity;

                if (frameIdx === currentIndex) {
                    frame.classList.add('current');
                } else {
                    frame.classList.remove('current');
                }
            });
        }

        // UPDATE DISPLAY
        function updateDisplay() {
            // Update time display
            const mins = Math.floor(currentTime / 60);
            const secs = Math.floor(currentTime % 60);
            const ms = Math.floor((currentTime % 1) * 1000);
            document.getElementById('timeDisplay').textContent =
                `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}.${String(ms).padStart(3, '0')}`;

            // Update subtitle overlay
            updateSubtitleOverlay();

            // Render frames (adds/removes dynamically)
            renderFrames();

            // Center current frame under phone
            centerOnCurrentFrame();
        }

        // UPDATE SUBTITLE OVERLAY
        function updateSubtitleOverlay() {
            const activeSub = subtitles.find(s => currentTime >= s.start && currentTime <= s.end);
            document.getElementById('subtitleOverlay').textContent = activeSub ? activeSub.text : '';
        }

        // CENTER TIMELINE ON CURRENT FRAME
        function centerOnCurrentFrame() {
            const scroll = document.getElementById('timelineScroll');
            const currentIndex = Math.floor(currentTime) + 1;
            const currentFrame = frameElements[currentIndex];

            if (currentFrame) {
                const frameLeft = currentFrame.offsetLeft;
                const frameWidth = currentFrame.offsetWidth;
                const scrollCenter = scroll.clientWidth / 2;
                scroll.scrollLeft = frameLeft + (frameWidth / 2) - scrollCenter;
            }
        }

        // SCROLL LISTENER - FIND FRAME UNDER PHONE
        let scrollTimeout;
        document.getElementById('timelineScroll').addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                const scroll = document.getElementById('timelineScroll');
                const centerX = scroll.scrollLeft + scroll.clientWidth / 2;

                let closestFrameIdx = 1;
                let minDist = Infinity;

                Object.keys(frameElements).forEach(idx => {
                    const frameIdx = parseInt(idx);
                    const frame = frameElements[idx];
                    const frameCenterX = frame.offsetLeft + frame.offsetWidth / 2;
                    const dist = Math.abs(frameCenterX - centerX);
                    if (dist < minDist) {
                        minDist = dist;
                        closestFrameIdx = frameIdx;
                    }
                });

                currentTime = closestFrameIdx - 1;
                updateDisplay();
            }, 20);
        });

        // LOAD SRT
        function loadSRT(input) {
            const file = input.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                subtitles = parseSRT(e.target.result);
                alert(`✅ Loaded ${subtitles.length} lyrics!`);
            };
            reader.readAsText(file);
        }

        // PARSE SRT
        function parseSRT(srtText) {
            const subtitles = [];
            const blocks = srtText.trim().split(/\n\s*\n/);

            blocks.forEach((block, index) => {
                const lines = block.split('\n');
                if (lines.length < 3) return;

                const timeMatch = lines[1].match(/(\d{2}):(\d{2}):(\d{2}),(\d{3})\s*-->\s*(\d{2}):(\d{2}):(\d{2}),(\d{3})/);
                if (!timeMatch) return;

                const startH = parseInt(timeMatch[1]);
                const startM = parseInt(timeMatch[2]);
                const startS = parseInt(timeMatch[3]);
                const startMs = parseInt(timeMatch[4]);
                const start = startH * 3600 + startM * 60 + startS + startMs / 1000;

                const endH = parseInt(timeMatch[5]);
                const endM = parseInt(timeMatch[6]);
                const endS = parseInt(timeMatch[7]);
                const endMs = parseInt(timeMatch[8]);
                const end = endH * 3600 + endM * 60 + endS + endMs / 1000;

                const text = lines.slice(2).join(' ').trim();

                subtitles.push({
                    id: index + 1,
                    text: text,
                    start: start,
                    end: end
                });
            });

            return subtitles;
        }

        // PLAY/PAUSE
        function togglePlay() {
            isPlaying = !isPlaying;
            document.getElementById('playBtn').textContent = isPlaying ? '⏸️' : '▶️';
            document.getElementById('statusText').textContent = isPlaying ? 'Playing...' : 'Drag timeline to explore';

            if (isPlaying) {
                playLoop();
            }
        }

        function playLoop() {
            if (!isPlaying) return;

            currentTime += 0.033; // ~30fps
            if (currentTime >= TOTAL_DURATION) {
                currentTime = 0;
            }

            updateDisplay();
            requestAnimationFrame(playLoop);
        }

        // INIT
        renderFrames();
        updateDisplay();
    </script>
</body>
</html>
