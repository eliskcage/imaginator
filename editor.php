<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

$scenario_id = isset($_GET['scenario_id']) ? intval($_GET['scenario_id']) : 0;

// Get scenario
$stmt = $pdo->prepare("
    SELECT s.* FROM scenarios s 
    LEFT JOIN purchases p ON p.scenario_id = s.id AND p.user_id = ? AND p.status = 'completed'
    WHERE s.id = ? AND (s.creator_id = ? OR p.id IS NOT NULL OR s.price = 0)
");
$stmt->execute([$_SESSION['user_id'], $scenario_id, $_SESSION['user_id']]);
$scenario = $stmt->fetch();

if (!$scenario) {
    die("❌ Access denied. You must own this scenario. <a href='dashboard.php' style='color:#ff4444;'>Go back</a>");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor - <?php echo htmlspecialchars($scenario['name']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial;
            background: #0a0a0a;
            color: #fff;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            background: rgba(20,20,20,0.8);
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }
        .upload-panel, .preview-panel {
            background: rgba(30,30,30,0.9);
            border-radius: 15px;
            padding: 30px;
        }
        .upload-zone {
            border: 3px dashed #444;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            margin: 20px 0;
            transition: all 0.3s;
        }
        .upload-zone:hover { border-color: #ff4444; background: rgba(255,68,68,0.05); }
        .upload-zone.loading { border-color: #4169e1; animation: pulse 1.5s infinite; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        #file-input { display: none; }
        .transform-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(45deg, #ff4444, #cc0000);
            border: none;
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .transform-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .transform-btn:hover:not(:disabled) { transform: scale(1.02); }
        .preview-frame {
            width: 100%;
            height: 70vh;
            border: 2px solid #333;
            border-radius: 10px;
            background: #000;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #222;
            border-radius: 15px;
            overflow: hidden;
            margin: 15px 0;
            display: none;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(45deg, #4169e1, #1e90ff);
            width: 0%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .status {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .status.processing { background: rgba(65,105,225,0.2); color: #4169e1; }
        .status.success { background: rgba(0,255,0,0.2); color: #0f0; }
        .status.error { background: rgba(255,68,68,0.2); color: #ff4444; }
        @media (max-width: 1024px) {
            .container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="font-size:1.5rem;font-weight:bold;">SHORTF▲CTORY Editor</div>
        <a href="dashboard.php" style="color:#ff4444;text-decoration:none;font-weight:bold;">← Back</a>
    </div>
    
    <div class="container">
        <div class="upload-panel">
            <h2 style="color:#ff4444;margin-bottom:20px;">📤 Upload Video</h2>
            
            <div style="background:rgba(0,0,0,0.3);padding:20px;border-radius:10px;margin-bottom:20px;">
                <h3 style="margin-bottom:10px;color:#ff4444;"><?php echo htmlspecialchars($scenario['name']); ?></h3>
                <p style="color:#aaa;"><?php echo htmlspecialchars($scenario['description']); ?></p>
            </div>
            
            <div class="upload-zone" id="upload-zone" onclick="document.getElementById('file-input').click()">
                <div style="font-size:4rem;" id="upload-icon">🎬</div>
                <h3 id="upload-text">Click to Upload Video</h3>
                <p style="color:#888;margin-top:10px;">MP4, WebM, MOV • Drag & Drop supported</p>
            </div>
            
            <input type="file" id="file-input" accept="video/*" />
            
            <div id="file-info" style="display:none;padding:15px;background:rgba(0,255,0,0.1);border-radius:8px;margin:15px 0;">
                <p><strong>📄 File:</strong> <span id="file-name"></span></p>
                <p><strong>📦 Size:</strong> <span id="file-size"></span></p>
            </div>
            
            <div class="progress-bar" id="progress-bar">
                <div class="progress-fill" id="progress-fill">0%</div>
            </div>
            
            <button class="transform-btn" id="transform-btn" disabled>🎨 TRANSFORM VIDEO</button>
            
            <div id="status"></div>
        </div>
        
        <div class="preview-panel">
            <h2 style="color:#ff4444;margin-bottom:20px;">👁️ Live Preview</h2>
            <iframe id="preview-frame" class="preview-frame"></iframe>
        </div>
    </div>
    
    <script>
        let selectedFile = null;
        const fileInput = document.getElementById('file-input');
        const transformBtn = document.getElementById('transform-btn');
        const previewFrame = document.getElementById('preview-frame');
        const uploadZone = document.getElementById('upload-zone');
        const uploadIcon = document.getElementById('upload-icon');
        const uploadText = document.getElementById('upload-text');
        const progressBar = document.getElementById('progress-bar');
        const progressFill = document.getElementById('progress-fill');
        const statusDiv = document.getElementById('status');
        
        // Drag and drop
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.style.borderColor = '#00ff00';
        });
        
        uploadZone.addEventListener('dragleave', () => {
            uploadZone.style.borderColor = '#444';
        });
        
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.style.borderColor = '#444';
            const file = e.dataTransfer.files[0];
            if (file) handleFile(file);
        });
        
        fileInput.onchange = (e) => {
            const file = e.target.files[0];
            if (file) handleFile(file);
        };
        
        function handleFile(file) {
            if (!file.type.startsWith('video/')) {
                alert('❌ Please select a video file');
                return;
            }
            
            selectedFile = file;
            document.getElementById('file-info').style.display = 'block';
            document.getElementById('file-name').textContent = file.name;
            document.getElementById('file-size').textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            
            uploadIcon.textContent = '✅';
            uploadText.textContent = 'Video Ready!';
            uploadZone.style.borderColor = '#00ff00';
            
            transformBtn.disabled = false;
        }
        
        transformBtn.onclick = async () => {
            if (!selectedFile) return;
            
            // Start processing
            transformBtn.disabled = true;
            transformBtn.textContent = '🔄 PROCESSING...';
            uploadZone.classList.add('loading');
            progressBar.style.display = 'block';
            
            statusDiv.className = 'status processing';
            statusDiv.innerHTML = '⏳ Loading video...';
            
            // Simulate progress
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += 5;
                if (progress <= 90) {
                    progressFill.style.width = progress + '%';
                    progressFill.textContent = progress + '%';
                }
            }, 100);
            
            try {
                // Create video URL
                const videoURL = URL.createObjectURL(selectedFile);
                
                statusDiv.innerHTML = '📥 Fetching scenario template...';
                progressFill.style.width = '30%';
                progressFill.textContent = '30%';
                
                // Get scenario template
                const response = await fetch(`api.php?action=get_scenario&scenario_id=<?php echo $scenario_id; ?>`);
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Failed to load scenario');
                }
                
                statusDiv.innerHTML = '🎨 Applying effects...';
                progressFill.style.width = '60%';
                progressFill.textContent = '60%';
                
                // Inject video into template
                let template = data.scenario.code_template;
                
                // Replace ALL video sources with uploaded video
                template = template.replace(/src="[^"]*\.(webm|mp4|mov)"/gi, `src="${videoURL}"`);
                template = template.replace(/src='[^']*\.(webm|mp4|mov)'/gi, `src='${videoURL}'`);
                template = template.replace(/https?:\/\/[^"'\s]*\.(webm|mp4|mov)/gi, videoURL);
                
                statusDiv.innerHTML = '🎬 Rendering preview...';
                progressFill.style.width = '80%';
                progressFill.textContent = '80%';
                
                // Small delay for effect
                await new Promise(resolve => setTimeout(resolve, 500));
                
                // Render in iframe
                const iframe = previewFrame.contentWindow;
                iframe.document.open();
                iframe.document.write(template);
                iframe.document.close();
                
                // Complete
                clearInterval(progressInterval);
                progressFill.style.width = '100%';
                progressFill.textContent = '100% ✓';
                
                statusDiv.className = 'status success';
                statusDiv.innerHTML = '✅ <strong>Video Transformed!</strong><br>Preview is now playing with effects applied.';
                
                transformBtn.textContent = '🔄 TRANSFORM ANOTHER';
                transformBtn.disabled = false;
                uploadZone.classList.remove('loading');
                
                // Hide progress after 2 seconds
                setTimeout(() => {
                    progressBar.style.display = 'none';
                }, 2000);
                
            } catch (error) {
                clearInterval(progressInterval);
                console.error('Transform error:', error);
                
                statusDiv.className = 'status error';
                statusDiv.innerHTML = '❌ <strong>Transform Failed:</strong><br>' + error.message;
                
                transformBtn.textContent = '🎨 TRY AGAIN';
                transformBtn.disabled = false;
                uploadZone.classList.remove('loading');
                progressBar.style.display = 'none';
            }
        };
    </script>
</body>
</html>
