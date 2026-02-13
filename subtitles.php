<?php
session_start();
require_once 'config.php';

// Check if video was uploaded from previous step
$videoFile = isset($_SESSION['video_file']) ? $_SESSION['video_file'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KINETIC SUBTITLES - ShortFactory</title>
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
            height: 100vh;
        }

        /* LEFT PANEL - PHONE PREVIEW */
        .preview-panel {
            width: 400px;
            background: linear-gradient(180deg, #1a0a2e 0%, #000 100%);
            border-right: 1px solid #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .preview-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #ff4444;
            text-shadow: 0 0 20px rgba(255,68,68,0.5);
        }

        .preview-controls {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .control-btn {
            background: rgba(255,68,68,0.2);
            border: 2px solid #ff4444;
            color: #fff;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s;
        }

        .control-btn:hover {
            background: rgba(255,68,68,0.4);
            transform: scale(1.05);
        }

        .phone-preview {
            position: relative;
            width: 300px;
            height: 600px;
            background: #000;
            border-radius: 40px;
            border: 10px solid #222;
            box-shadow: 0 20px 60px rgba(0,0,0,0.8);
            overflow: hidden;
        }

        .phone-screen {
            width: 100%;
            height: 100%;
            background: #000;
            position: relative;
            overflow: hidden;
        }

        #previewVideo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #subtitleOverlay {
            position: absolute;
            bottom: 100px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
            pointer-events: none;
            z-index: 10;
        }

        .watermark-preview {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: rgba(0,0,0,0.7);
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.7rem;
            color: #ff4444;
        }

        /* RIGHT PANEL - EDITOR */
        .editor-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #111;
        }

        .editor-header {
            padding: 20px;
            background: rgba(255,68,68,0.1);
            border-bottom: 2px solid #ff4444;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .editor-header h1 {
            font-size: 2rem;
            color: #ff4444;
        }

        .header-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff4444, #cc0000);
            color: #fff;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255,68,68,0.4);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* SUBTITLE LIST */
        .subtitle-list {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .subtitle-item {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,68,68,0.2);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .subtitle-item:hover {
            background: rgba(255,68,68,0.1);
            border-color: #ff4444;
        }

        .subtitle-item.active {
            background: rgba(255,68,68,0.2);
            border-color: #ff4444;
            box-shadow: 0 0 20px rgba(255,68,68,0.3);
        }

        .subtitle-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .subtitle-time {
            color: #ff4444;
            font-weight: bold;
            font-family: monospace;
        }

        .subtitle-text {
            width: 100%;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 5px;
            padding: 10px;
            color: #fff;
            font-size: 1rem;
            resize: vertical;
            min-height: 60px;
        }

        .subtitle-controls {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .animation-select {
            flex: 1;
            background: rgba(0,0,0,0.5);
            border: 1px solid rgba(255,68,68,0.3);
            border-radius: 5px;
            padding: 8px;
            color: #fff;
            cursor: pointer;
        }

        /* TIMELINE */
        .timeline-container {
            height: 150px;
            background: #1a1a1a;
            border-top: 2px solid #333;
            padding: 10px;
            overflow-x: auto;
        }

        .timeline {
            position: relative;
            height: 100%;
            background: linear-gradient(90deg, #000 0%, #1a1a1a 50%, #000 100%);
            border-radius: 10px;
            min-width: 2000px;
        }

        .timeline-marker {
            position: absolute;
            top: 0;
            width: 2px;
            height: 100%;
            background: #ff4444;
            box-shadow: 0 0 10px rgba(255,68,68,0.5);
            z-index: 10;
            transition: left 0.1s linear;
        }

        .timeline-subtitle {
            position: absolute;
            top: 20px;
            height: 40px;
            background: rgba(255,68,68,0.3);
            border: 2px solid #ff4444;
            border-radius: 5px;
            cursor: grab;
            display: flex;
            align-items: center;
            padding: 0 10px;
            font-size: 0.8rem;
            white-space: nowrap;
            overflow: hidden;
        }

        .timeline-subtitle:active {
            cursor: grabbing;
        }

        .timeline-subtitle:hover {
            background: rgba(255,68,68,0.5);
            z-index: 5;
        }

        /* UPLOAD SECTION */
        .upload-section {
            padding: 40px;
            text-align: center;
        }

        .upload-box {
            border: 3px dashed rgba(255,68,68,0.5);
            border-radius: 20px;
            padding: 60px;
            background: rgba(255,68,68,0.05);
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-box:hover {
            border-color: #ff4444;
            background: rgba(255,68,68,0.1);
        }

        .upload-icon {
            font-size: 5rem;
            margin-bottom: 20px;
        }

        .upload-text {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .upload-hint {
            color: #888;
            font-size: 1rem;
        }

        input[type="file"] {
            display: none;
        }

        /* KINETIC ANIMATION KEYFRAMES */
        @keyframes flyIn {
            0% { transform: translateY(100px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        @keyframes typewriter {
            from { width: 0; }
            to { width: 100%; }
        }

        @keyframes explode {
            0% { transform: scale(0) rotate(0deg); opacity: 0; }
            50% { transform: scale(1.2) rotate(180deg); opacity: 1; }
            100% { transform: scale(1) rotate(360deg); opacity: 1; }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        @keyframes glow {
            0%, 100% { text-shadow: 0 0 10px #ff4444; }
            50% { text-shadow: 0 0 30px #ff4444, 0 0 40px #ff4444; }
        }

        .anim-flyIn { animation: flyIn 0.5s ease-out; }
        .anim-bounce { animation: bounce 0.5s ease-in-out; }
        .anim-explode { animation: explode 0.8s ease-out; }
        .anim-shake { animation: shake 0.5s ease-in-out; }
        .anim-glow { animation: glow 1s ease-in-out infinite; }

        .loading {
            padding: 40px;
            text-align: center;
            font-size: 1.5rem;
            color: #ff4444;
        }

        .loading::after {
            content: '...';
            animation: dots 1.5s steps(4, end) infinite;
        }

        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }
    </style>
</head>
<body>
    <div class="editor-container">
        <!-- LEFT PANEL - PREVIEW -->
        <div class="preview-panel">
            <div class="preview-title">🎬 LIVE PREVIEW</div>
            <div class="preview-controls">
                <button class="control-btn" id="playPauseBtn" onclick="togglePlayPause()">⏸️</button>
                <button class="control-btn" onclick="restartPreview()">🔄</button>
            </div>
            <div class="phone-preview">
                <div class="phone-screen">
                    <video id="previewVideo" loop muted></video>
                    <div id="subtitleOverlay"></div>
                    <div class="watermark-preview">SHORTFACTORY</div>
                </div>
            </div>
            <div style="margin-top: 20px; text-align: center; color: #888; font-size: 0.9rem;">
                Click subtitles to preview animations
            </div>
        </div>

        <!-- RIGHT PANEL - EDITOR -->
        <div class="editor-panel">
            <div class="editor-header">
                <h1>✨ KINETIC SUBTITLES</h1>
                <div class="header-buttons">
                    <button class="btn btn-secondary" onclick="generateSubtitles()">🎤 Auto-Generate</button>
                    <button class="btn btn-secondary" onclick="document.getElementById('srtUpload').click()">📄 Upload SRT</button>
                    <input type="file" id="srtUpload" accept=".srt" style="display:none" onchange="loadSRT(this)">
                    <button class="btn btn-primary" onclick="generateDramatic()" style="background:linear-gradient(135deg,#9944ff,#6600cc);">✨ AI DRAMATIC MODE</button>
                    <button class="btn btn-secondary" onclick="addSubtitle()">➕ Add Subtitle</button>
                    <button class="btn btn-primary" onclick="renderVideo()">🎥 Render Video</button>
                </div>
            </div>

            <div id="editorContent">
                <!-- UPLOAD SECTION (shown initially) -->
                <div class="upload-section" id="uploadSection">
                    <div class="upload-box" onclick="document.getElementById('videoUpload').click()">
                        <div class="upload-icon">🎬</div>
                        <div class="upload-text">Drop your video here</div>
                        <div class="upload-hint">MP4, WebM, or MOV • Max 100MB</div>
                    </div>
                    <input type="file" id="videoUpload" accept="video/*" onchange="loadVideo(this)">

                    <div style="margin-top:30px;padding:20px;background:rgba(255,68,68,0.1);border-radius:15px;border:1px solid rgba(255,68,68,0.3);">
                        <h3 style="color:#ff4444;margin-bottom:10px;">📝 How it works:</h3>
                        <ol style="color:#ccc;line-height:1.8;padding-left:20px;">
                            <li>Upload your video</li>
                            <li>We auto-generate subtitles with AI (words + timings)</li>
                            <li>Click <strong style="color:#9944ff;">✨ AI DRAMATIC MODE</strong> to optimize placement</li>
                            <li>Edit text/animations if needed</li>
                            <li>Render final video!</li>
                        </ol>
                    </div>
                </div>

                <!-- SUBTITLE EDITOR (shown after video loaded) -->
                <div id="subtitleEditor" style="display:none; flex:1; display:flex; flex-direction:column;">
                    <div class="subtitle-list" id="subtitleList">
                        <!-- Subtitles will be added here -->
                    </div>

                    <div class="timeline-container">
                        <div class="timeline" id="timeline">
                            <div class="timeline-marker" id="timelineMarker"></div>
                            <!-- Timeline subtitle blocks will be added here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let videoFile = null;
        let subtitles = [];
        let currentTime = 0;
        let duration = 0;
        const previewVideo = document.getElementById('previewVideo');
        const subtitleOverlay = document.getElementById('subtitleOverlay');
        const timeline = document.getElementById('timeline');
        const timelineMarker = document.getElementById('timelineMarker');

        function loadVideo(input) {
            const file = input.files[0];
            if (!file) return;

            // Validate file size
            const maxSize = 100 * 1024 * 1024; // 100MB
            if (file.size > maxSize) {
                alert(`❌ File too large: ${(file.size / 1024 / 1024).toFixed(1)}MB\nMaximum: 100MB`);
                return;
            }

            videoFile = file;
            const url = URL.createObjectURL(file);
            previewVideo.src = url;

            previewVideo.onloadedmetadata = () => {
                duration = previewVideo.duration;
                document.getElementById('uploadSection').style.display = 'none';
                document.getElementById('subtitleEditor').style.display = 'flex';
                previewVideo.play();

                // AUTO-GENERATE SUBTITLES after video loads
                console.log('📝 Auto-generating subtitles from video...');
                setTimeout(() => {
                    generateSubtitles();
                }, 500);
            };
        }

        // Update timeline marker
        previewVideo.addEventListener('timeupdate', () => {
            currentTime = previewVideo.currentTime;
            const percent = (currentTime / duration) * 100;
            timelineMarker.style.left = percent + '%';

            // Update subtitle overlay
            updateSubtitleDisplay();
        });

        function updateSubtitleDisplay() {
            const activeSub = subtitles.find(s =>
                currentTime >= s.start && currentTime <= s.end
            );

            if (activeSub) {
                subtitleOverlay.textContent = activeSub.text;
                subtitleOverlay.className = 'anim-' + activeSub.animation;
            } else {
                subtitleOverlay.textContent = '';
            }
        }

        function addSubtitle() {
            const sub = {
                id: Date.now(),
                text: 'New subtitle text',
                start: currentTime,
                end: currentTime + 2,
                animation: 'flyIn'
            };

            subtitles.push(sub);
            renderSubtitleList();
            renderTimeline();
        }

        function renderSubtitleList() {
            const list = document.getElementById('subtitleList');
            list.innerHTML = subtitles.map(sub => `
                <div class="subtitle-item" data-id="${sub.id}">
                    <div class="subtitle-header">
                        <span class="subtitle-time">${formatTime(sub.start)} → ${formatTime(sub.end)}</span>
                        <button onclick="deleteSubtitle(${sub.id})" style="background:#ff4444;border:none;color:#fff;padding:5px 10px;border-radius:5px;cursor:pointer;">🗑️</button>
                    </div>
                    <textarea class="subtitle-text" oninput="updateSubtitleText(${sub.id}, this.value)">${sub.text}</textarea>
                    <div class="subtitle-controls">
                        <select class="animation-select" onchange="updateAnimation(${sub.id}, this.value)">
                            <option value="flyIn" ${sub.animation === 'flyIn' ? 'selected' : ''}>🚀 Fly In</option>
                            <option value="bounce" ${sub.animation === 'bounce' ? 'selected' : ''}>⚾ Bounce</option>
                            <option value="explode" ${sub.animation === 'explode' ? 'selected' : ''}>💥 Explode</option>
                            <option value="shake" ${sub.animation === 'shake' ? 'selected' : ''}>📳 Shake</option>
                            <option value="glow" ${sub.animation === 'glow' ? 'selected' : ''}>✨ Glow</option>
                        </select>
                    </div>
                </div>
            `).join('');
        }

        function renderTimeline() {
            const existingBlocks = timeline.querySelectorAll('.timeline-subtitle');
            existingBlocks.forEach(b => b.remove());

            subtitles.forEach(sub => {
                const block = document.createElement('div');
                block.className = 'timeline-subtitle';
                block.style.left = ((sub.start / duration) * 100) + '%';
                block.style.width = (((sub.end - sub.start) / duration) * 100) + '%';
                block.textContent = sub.text.substring(0, 20);
                block.dataset.id = sub.id;
                timeline.appendChild(block);
            });
        }

        function updateSubtitleText(id, text) {
            const sub = subtitles.find(s => s.id === id);
            if (sub) sub.text = text;
        }

        function updateAnimation(id, animation) {
            const sub = subtitles.find(s => s.id === id);
            if (sub) sub.animation = animation;
        }

        function deleteSubtitle(id) {
            subtitles = subtitles.filter(s => s.id !== id);
            renderSubtitleList();
            renderTimeline();
        }

        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }

        function togglePlayPause() {
            const btn = document.getElementById('playPauseBtn');
            if (previewVideo.paused) {
                previewVideo.play();
                btn.textContent = '⏸️';
            } else {
                previewVideo.pause();
                btn.textContent = '▶️';
            }
        }

        function restartPreview() {
            previewVideo.currentTime = 0;
            previewVideo.play();
            document.getElementById('playPauseBtn').textContent = '⏸️';
        }

        // LOAD SRT FILE
        function loadSRT(input) {
            const file = input.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const srtContent = e.target.result;
                console.log('📄 Parsing SRT file...');

                try {
                    subtitles = parseSRT(srtContent);
                    console.log('✅ Loaded', subtitles.length, 'subtitles from SRT');

                    renderSubtitleList();
                    renderTimeline();

                    alert(`✅ Loaded ${subtitles.length} subtitles from SRT!\n\nNow click "✨ AI DRAMATIC MODE" to optimize them!`);
                } catch (error) {
                    console.error('❌ SRT parse error:', error);
                    alert('❌ Failed to parse SRT file: ' + error.message);
                }
            };
            reader.readAsText(file);
        }

        // PARSE SRT FORMAT
        function parseSRT(srtText) {
            const subtitles = [];
            const blocks = srtText.trim().split(/\n\s*\n/); // Split by double newline

            blocks.forEach((block, index) => {
                const lines = block.split('\n');
                if (lines.length < 3) return;

                // Line 0: Index (ignore)
                // Line 1: Timestamp
                // Line 2+: Text

                const timeMatch = lines[1].match(/(\d{2}):(\d{2}):(\d{2}),(\d{3})\s*-->\s*(\d{2}):(\d{2}):(\d{2}),(\d{3})/);
                if (!timeMatch) return;

                // Parse start time
                const startH = parseInt(timeMatch[1]);
                const startM = parseInt(timeMatch[2]);
                const startS = parseInt(timeMatch[3]);
                const startMs = parseInt(timeMatch[4]);
                const start = startH * 3600 + startM * 60 + startS + startMs / 1000;

                // Parse end time
                const endH = parseInt(timeMatch[5]);
                const endM = parseInt(timeMatch[6]);
                const endS = parseInt(timeMatch[7]);
                const endMs = parseInt(timeMatch[8]);
                const end = endH * 3600 + endM * 60 + endS + endMs / 1000;

                // Get text (all remaining lines)
                const text = lines.slice(2).join(' ').trim();

                subtitles.push({
                    id: index + 1,
                    text: text,
                    start: start,
                    end: end,
                    animation: 'flyIn' // Default animation
                });
            });

            return subtitles;
        }

        async function generateSubtitles() {
            if (!videoFile) return alert('Please load a video first');

            const loading = document.createElement('div');
            loading.className = 'loading';
            loading.textContent = '🎤 Generating subtitles with AI';
            document.getElementById('subtitleList').innerHTML = '';
            document.getElementById('subtitleList').appendChild(loading);

            const formData = new FormData();
            formData.append('video', videoFile);

            try {
                const response = await fetch('generate_subtitles.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    subtitles = data.subtitles;
                    renderSubtitleList();
                    renderTimeline();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Failed to generate subtitles: ' + error.message);
            }
        }

        async function generateDramatic() {
            if (!videoFile) {
                alert('⚠️ Please load a video first');
                return;
            }

            // Check if we have generated subtitles first
            if (subtitles.length === 0) {
                alert('⚠️ Please wait for subtitle generation to complete first!\n\nThe system auto-generates subtitles when you upload. Wait a moment, then try AI Dramatic Mode.');
                return;
            }

            const loading = document.createElement('div');
            loading.className = 'loading';
            loading.innerHTML = '✨ AI OPTIMIZING YOUR SUBTITLES FOR DRAMATIC EFFECT<br><small>Analyzing ' + subtitles.length + ' words for maximum impact...</small>';
            document.getElementById('subtitleList').innerHTML = '';
            document.getElementById('subtitleList').appendChild(loading);

            try {
                console.log('🎬 Starting AI dramatic optimization on', subtitles.length, 'existing subtitles...');

                // Step 1: Check if AI server is running (via PHP proxy)
                const healthCheck = await fetch('ai_proxy.php?health=1', {
                    method: 'GET',
                    timeout: 5000
                }).catch(e => {
                    throw new Error('AI server not reachable. Please contact support.');
                });

                if (!healthCheck.ok) {
                    throw new Error('AI server not responding');
                }

                console.log('✅ AI server is online');

                // Step 2: Send video + existing subtitles to AI via PHP proxy
                const aiFormData = new FormData();
                aiFormData.append('audio', videoFile);
                aiFormData.append('subtitles', JSON.stringify(subtitles)); // Send existing subtitles

                console.log('📤 Uploading to AI server via proxy with', subtitles.length, 'subtitles...');

                const aiResponse = await fetch('ai_proxy.php', {
                    method: 'POST',
                    body: aiFormData
                });

                console.log('📥 Response received:', aiResponse.status);

                if (!aiResponse.ok) {
                    const errorText = await aiResponse.text();
                    throw new Error(`Server error (${aiResponse.status}): ${errorText}`);
                }

                const data = await aiResponse.json();
                console.log('✅ AI response:', data);

                if (data.success && data.words && data.words.length > 0) {
                    // Convert AI word placements to subtitle format
                    subtitles = data.words.map(word => ({
                        id: word.id,
                        text: word.text,
                        start: word.start,
                        end: word.end,
                        animation: word.animation,
                        style: word.style // Store AI-generated style
                    }));

                    renderSubtitleList();
                    renderTimeline();

                    alert(`✨ AI Generated ${subtitles.length} dramatic words!\n\n🎵 BPM: ${data.analysis.bpm}\n📊 Peaks detected: ${data.analysis.peaks}\n⏱️ Duration: ${Math.round(data.analysis.duration)}s`);
                } else {
                    throw new Error(data.message || 'No words generated by AI');
                }

            } catch (error) {
                console.error('❌ AI Dramatic Mode Error:', error);

                // Show detailed error to user
                let errorMsg = '❌ AI Dramatic Mode Failed\n\n';
                errorMsg += 'Error: ' + error.message + '\n\n';

                if (error.message.includes('not reachable')) {
                    errorMsg += '💡 The AI server may be offline.\n';
                    errorMsg += 'Falling back to standard subtitle generation...';

                    alert(errorMsg);

                    // Fallback to standard generation
                    generateSubtitles();
                } else {
                    errorMsg += '💡 Try using "Auto-Generate" instead,\n';
                    errorMsg += 'or check the browser console for details.';
                    alert(errorMsg);

                    // Clear loading
                    document.getElementById('subtitleList').innerHTML = '';
                }
            }
        }

        async function extractAudioFromVideo(videoFile) {
            // For now, just send the video file
            // In production, extract audio client-side or server-side
            return videoFile;
        }

        async function renderVideo() {
            if (subtitles.length === 0) {
                return alert('Please add subtitles first!');
            }

            if (confirm('Render video with kinetic subtitles? This may take a few minutes.')) {
                const formData = new FormData();
                formData.append('video', videoFile);
                formData.append('subtitles', JSON.stringify(subtitles));

                try {
                    const response = await fetch('render_subtitles.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('✅ Video rendered! Downloading now...');
                        window.location.href = data.download_url;
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    alert('Failed to render video: ' + error.message);
                }
            }
        }
    </script>
</body>
</html>
