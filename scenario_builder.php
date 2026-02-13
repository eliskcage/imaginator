<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><div style="position:fixed;top:10px;right:10px;background:rgba(0,0,0,0.8);padding:10px;border-radius:5px;font-size:0.8rem;z-index:9999;">
    <span id="request-count">Requests: 0/15 per min</span>
</div>

<script>
let requestCount = 0;
let lastMinute = Date.now();

function trackRequest() {
    if (Date.now() - lastMinute > 60000) {
        requestCount = 0;
        lastMinute = Date.now();
    }
    requestCount++;
    document.getElementById('request-count').textContent = `Requests: ${requestCount}/15 per min`;
    
    if (requestCount >= 14) {
        alert('⚠️ Approaching rate limit! Wait a minute before next request.');
    }
}

// Call trackRequest() inside sendMessage() before the fetch
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scenario Builder - SHORTF▲CTORY</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #0a0a0a;
            color: #fff;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: rgba(20,20,20,0.8);
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .logo { font-size: 2rem; font-weight: bold; }
        .back-link {
            color: #ff4444;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .builder-container {
            max-width: 1600px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .chat-panel, .preview-panel {
            background: rgba(30,30,30,0.9);
            border-radius: 15px;
            padding: 30px;
            height: 80vh;
            display: flex;
            flex-direction: column;
        }
        .panel-title {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #ff4444;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
        }
        .message {
            margin: 15px 0;
            padding: 15px;
            border-radius: 10px;
            line-height: 1.6;
        }
        .message.user {
            background: #4169e1;
            margin-left: 20%;
        }
        .message.bot {
            background: #333;
            margin-right: 20%;
        }
        .input-container {
            display: flex;
            gap: 10px;
        }
        #chat-input {
            flex: 1;
            padding: 15px;
            background: #222;
            border: 1px solid #444;
            color: white;
            border-radius: 10px;
            font-size: 1rem;
        }
        #send-btn {
            padding: 15px 30px;
            background: linear-gradient(45deg, #ff4444, #cc0000);
            border: none;
            color: white;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
        }
        #send-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        #code-preview {
            flex: 1;
            background: #000;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            overflow-y: auto;
            white-space: pre-wrap;
            font-size: 0.85rem;
            color: #0f0;
        }
        .deploy-section {
            margin-top: 20px;
            display: grid;
            gap: 10px;
        }
        .deploy-section input, .deploy-section textarea {
            width: 100%;
            padding: 12px;
            background: #222;
            border: 1px solid #444;
            color: white;
            border-radius: 5px;
        }
        .deploy-btn {
            padding: 15px;
            background: linear-gradient(45deg, #00ff00, #00cc00);
            border: none;
            color: #000;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .quick-prompts {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .quick-prompt {
            padding: 8px 15px;
            background: #333;
            border: 1px solid #555;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .quick-prompt:hover { background: #ff4444; }
        @media (max-width: 1024px) {
            .builder-container { grid-template-columns: 1fr; }
            .chat-panel, .preview-panel { height: 60vh; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">SHORTF▲CTORY - AI Builder</div>
        <a href="dashboard.php" class="back-link">← Back</a>
    </div>
    
    <div class="builder-container">
        <div class="chat-panel">
            <h2 class="panel-title">🤖 AI Designer</h2>
            
            <div class="quick-prompts">
                <div class="quick-prompt" onclick="quickPrompt('romantic sunset with soft pink colors and gentle camera movements')">💖 Romantic</div>
                <div class="quick-prompt" onclick="quickPrompt('high action sports with fast cuts and blue tones')">⚡ Action</div>
                <div class="quick-prompt" onclick="quickPrompt('horror night vision with green tint and shaky camera')">🧟 Horror</div>
                <div class="quick-prompt" onclick="quickPrompt('vintage 8mm film with grain and light leaks')">📼 Vintage</div>
            </div>
            
            <div class="chat-messages" id="chat-messages"></div>
            
            <div class="input-container">
                <input type="text" id="chat-input" placeholder="Describe your scenario..." />
                <button id="send-btn">SEND</button>
            </div>
        </div>
        
        <div class="preview-panel">
            <h2 class="panel-title">📝 Generated Code</h2>
            <div id="code-preview">// Your AI-generated scenario code will appear here...
// Chat with the AI to create your custom video transformation!</div>
            
            <div class="deploy-section">
                <input type="text" id="scenario-name" placeholder="Scenario Name (e.g., 'Romantic Sunset Timelapse')" />
                <textarea id="scenario-desc" rows="3" placeholder="Description for users"></textarea>
                <input type="text" id="scenario-tags" placeholder="Tags (comma separated, e.g., romantic,sunset,timelapse)" />
                <input type="number" id="scenario-price" placeholder="Price in £" value="4.99" step="0.01" min="0.99" />
                <button class="deploy-btn" onclick="deployScenario()">🚀 DEPLOY TO MARKETPLACE</button>
            </div>
        </div>
    </div>
    
    <script>
    const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=<?php echo GEMINI_API_KEY; ?>';
    const SYSTEM_PROMPT = 'You are SHORTFACTORY AI. Generate COMPLETE HTML templates with video effects. Always output code in triple backticks: ```html ... ```. Include: video element, camera shake, zoom, blur, color filters. Make it stunning!';
    
    let chatHistory = [{ role: "user", parts: [{ text: SYSTEM_PROMPT }] }];
    let generatedCode = '';
    
    const messagesDiv = document.getElementById('chat-messages');
    const input = document.getElementById('chat-input');
    const sendBtn = document.getElementById('send-btn');
    const codePreview = document.getElementById('code-preview');
    
    addMessage('bot', '👋 Hi! Describe your video effect (e.g., "romantic sunset" or "action replay")');
    
    sendBtn.onclick = sendMessage;
    input.onkeypress = (e) => { if (e.key === 'Enter' && !sendBtn.disabled) sendMessage(); };
    
    function quickPrompt(text) {
        input.value = text;
        sendMessage();
    }
    
    function addMessage(sender, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'message ' + sender;
        msgDiv.textContent = text;
        messagesDiv.appendChild(msgDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
    
    async function sendMessage() {
        const userText = input.value.trim();
        if (!userText) return;
        
        addMessage('user', userText);
        input.value = '';
        sendBtn.disabled = true;
        sendBtn.textContent = 'THINKING...';
        
        chatHistory.push({ role: "user", parts: [{ text: userText }] });
        
        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ contents: chatHistory })
            });
            
            // Check if response is ok
            if (!response.ok) {
                throw new Error(`API Error: ${response.status} ${response.statusText}`);
            }
            
            const data = await response.json();
            
            // Debug log
            console.log('API Response:', data);
            
            // Check for API errors
            if (data.error) {
                throw new Error(data.error.message || 'API returned an error');
            }
            
            // Safe access with optional chaining and error checking
            if (!data.candidates || !Array.isArray(data.candidates) || data.candidates.length === 0) {
                console.error('Full response:', data);
                throw new Error('API returned no candidates. Possible rate limit or invalid request.');
            }
            
            const candidate = data.candidates[0];
            if (!candidate || !candidate.content || !candidate.content.parts || !candidate.content.parts[0]) {
                console.error('Invalid candidate structure:', candidate);
                throw new Error('Invalid response structure from API');
            }
            
            const botText = candidate.content.parts[0].text.trim();
            
            if (!botText) {
                throw new Error('API returned empty response');
            }
            
            addMessage('bot', botText);
            chatHistory.push({ role: "model", parts: [{ text: botText }] });
            
            // Extract code
            const codeMatch = botText.match(/```html\n([\s\S]*?)```/);
            if (codeMatch) {
                generatedCode = codeMatch[1];
                codePreview.textContent = generatedCode;
                addMessage('bot', '✅ Code extracted! Fill in the details below and click Deploy.');
            } else {
                addMessage('bot', '⚠️ No code block found. Ask me to format it in ```html tags.');
            }
            
        } catch (error) {
            console.error('Full error:', error);
            addMessage('bot', '❌ Error: ' + error.message + '\n\nTry:\n• Check your internet connection\n• Verify Gemini API key is valid\n• You may have hit rate limits');
        }
        
        sendBtn.disabled = false;
        sendBtn.textContent = 'SEND';
    }
    
    async function deployScenario() {
        const name = document.getElementById('scenario-name').value.trim();
        const desc = document.getElementById('scenario-desc').value.trim();
        const tags = document.getElementById('scenario-tags').value.trim();
        const price = parseFloat(document.getElementById('scenario-price').value);
        
        if (!name || !desc || !generatedCode) {
            alert('❌ Fill all fields and generate code first!');
            return;
        }
        
        if (price < 0) {
            alert('❌ Price must be 0 or higher');
            return;
        }
        
        if (!confirm(`Deploy "${name}" for £${price.toFixed(2)}?`)) {
            return;
        }
        
        try {
            const response = await fetch('api.php?action=save_scenario', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name, 
                    description: desc,
                    theme_data: {},
                    code_template: generatedCode,
                    tags, 
                    price
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('✅ Scenario deployed to marketplace!');
                location.href = 'dashboard.php';
            } else {
                alert('❌ ' + result.error);
            }
        } catch (error) {
            alert('❌ Failed: ' + error.message);
        }
    }
</script>
</body>
</html>
