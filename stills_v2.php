<?php
header('Cache-Control: no-cache, no-store, must-revalidate');

/* ---- SONAUTO MUSIC GEN API (AJAX handler) ---- */
if(isset($_POST['musicgen_action'])){
  header('Content-Type: application/json');
  require_once __DIR__.'/secrets.php';
  $apiKey=SONAUTO_API_KEY;
  $action=$_POST['musicgen_action'];

  if($action==='generate'){
    $title=trim($_POST['mg_title']??'ShortFactory Track');
    $styles=trim($_POST['mg_styles']??'dark synthwave');
    $instrumental=!empty($_POST['mg_instrumental']);
    $tags=array_filter(array_map('trim',explode(',',$styles)));
    if(empty($tags))$tags=['dark','synthwave'];
    $prompt="A ".implode(' ',$tags)." song about lost souls and electric nights";
    $payload=['tags'=>$tags,'prompt'=>$prompt,'instrumental'=>$instrumental,'num_songs'=>1,'output_format'=>'mp3'];
    $ch=curl_init('https://api.sonauto.ai/v1/generations');
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($payload),CURLOPT_HTTPHEADER=>["Authorization: Bearer $apiKey","Content-Type: application/json"]]);
    $resp=curl_exec($ch);$http=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
    if($http!==200){echo json_encode(['ok'=>false,'error'=>"API $http"]);exit;}
    $data=json_decode($resp,true);
    echo json_encode(['ok'=>true,'task_id'=>$data['task_id']??null]);exit;
  }

  if($action==='poll'){
    $taskId=$_POST['task_id']??'';
    if(!$taskId){echo json_encode(['ok'=>false,'error'=>'No task ID']);exit;}
    $ctx=stream_context_create(['http'=>['header'=>"Authorization: Bearer $apiKey"]]);
    $resp=@file_get_contents("https://api.sonauto.ai/v1/generations/$taskId",false,$ctx);
    $data=json_decode($resp,true);
    echo json_encode(['ok'=>true,'status'=>$data['status']??'UNKNOWN','song_url'=>$data['song_paths'][0]??null,'error_msg'=>$data['error_message']??null]);exit;
  }
  echo json_encode(['ok'=>false,'error'=>'Bad action']);exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>SHORTF&#9650;CTORY &mdash; Imaginator Step 1</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<script src="https://accounts.google.com/gsi/client" async defer></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{background:#0a0a0a;color:#fff;font-family:'Poppins',sans-serif;min-height:100vh;overflow-x:hidden;}

/* TOP BAR */
.topbar{background:#111;border-bottom:1px solid #222;padding:10px 16px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:100;}
.topbar .brand{font-family:'Orbitron',sans-serif;font-size:14px;color:#FFD700;letter-spacing:2px;}
.topbar .limits{font-size:11px;color:#888;}
.topbar .limits b{color:#FFD700;}
.topbar .guser{display:flex;align-items:center;gap:8px;cursor:pointer;}
.topbar .guser img{width:28px;height:28px;border-radius:50%;border:2px solid #FFD700;}
.topbar .guser span{font-size:11px;color:#aaa;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.topbar .gsign{padding:6px 14px;background:transparent;border:1px solid #444;border-radius:16px;color:#aaa;font-size:11px;cursor:pointer;transition:all 0.2s;font-family:'Poppins',sans-serif;}
.topbar .gsign:hover{border-color:#FFD700;color:#FFD700;}
.token-badge{display:flex;align-items:center;gap:5px;background:linear-gradient(135deg,#1a1000,#0d0d0d);border:1px solid #FFD700;border-radius:16px;padding:4px 12px 4px 8px;font-family:'Orbitron',sans-serif;font-size:11px;color:#FFD700;letter-spacing:1px;cursor:pointer;transition:all 0.2s;}
.token-badge:hover{background:#1a1a00;box-shadow:0 0 12px rgba(255,215,0,0.2);}
.token-coin{font-size:14px;}
.token-zero{color:#ff4444;border-color:#ff4444;}

/* Token earn panel */
#tokenPanel{display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:200;background:#111;border:2px solid #FFD700;border-radius:16px;padding:24px;max-width:340px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.9);}
#tokenPanel h3{font-family:'Orbitron',sans-serif;font-size:14px;color:#FFD700;letter-spacing:2px;text-align:center;margin-bottom:16px;}
.earn-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #222;font-size:12px;}
.earn-row:last-child{border:none;}
.earn-action{color:#ccc;}
.earn-reward{color:#FFD700;font-family:'Orbitron',sans-serif;font-weight:900;font-size:13px;}
.earn-btn{width:100%;padding:12px;background:linear-gradient(135deg,#FFD700,#ff6b35);border:none;border-radius:8px;color:#000;font-family:'Orbitron',sans-serif;font-size:12px;font-weight:900;letter-spacing:1px;cursor:pointer;margin-top:12px;transition:all 0.2s;}
.earn-btn:hover{transform:scale(1.02);}
#tokenOverlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:199;}

/* LAYOUT */
.wrap{display:flex;gap:16px;padding:16px;max-width:1200px;margin:0 auto;flex-wrap:wrap;}
.col-preview{flex:1;min-width:300px;max-width:500px;}
.col-controls{flex:1;min-width:300px;}

/* PREVIEW (vidman engine) */
#preview{position:relative;width:100%;aspect-ratio:9/16;background:#000;border-radius:12px;overflow:hidden;border:2px solid #222;}
#prevBg{position:absolute;inset:0;background-size:230%;background-position:50% 50%;background-repeat:repeat;transition:filter 0.3s;}
#prevFx{position:absolute;inset:0;pointer-events:none;backdrop-filter:blur(30px);-webkit-backdrop-filter:blur(30px);mask-image:radial-gradient(circle at center,transparent 0%,transparent 30%,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.7) 70%,black 90%);-webkit-mask-image:radial-gradient(circle at center,transparent 0%,transparent 30%,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.7) 70%,black 90%);}
#prevVignette{position:absolute;inset:0;background:radial-gradient(circle at center,transparent 30%,rgba(0,0,0,0.75) 100%);pointer-events:none;opacity:0.7;}
#prevFlash{position:absolute;inset:0;background:#333;opacity:0;pointer-events:none;transition:opacity 0.25s ease-out;z-index:20;}
#prevLightning{position:absolute;inset:0;background:#fff;opacity:0;pointer-events:none;z-index:25;mix-blend-mode:screen;}
#prevBreath{position:absolute;inset:0;background:rgba(0,0,0,0.15);opacity:0;pointer-events:none;}
#prevGrain{position:absolute;inset:0;pointer-events:none;opacity:0.06;z-index:15;}
#shutterT,#shutterB{position:absolute;left:0;width:100%;height:50%;background:#111;pointer-events:none;transition:transform 0.1s ease-out;z-index:60;}
#shutterT{top:0;transform:translateY(-100%);}
#shutterB{bottom:0;transform:translateY(100%);}
#prevText{position:absolute;bottom:60px;left:0;right:0;text-align:center;z-index:30;pointer-events:none;}
#prevText .ktext{font-family:'Orbitron',sans-serif;font-size:22px;color:#fff;text-shadow:0 0 30px rgba(255,215,0,0.9),0 0 60px rgba(255,215,0,0.4),0 2px 4px #000;opacity:0;transition:opacity 0.6s,transform 0.6s;transform:translateY(30px) scale(0.9);}
#prevText .ktext.show{opacity:1;transform:translateY(0) scale(1);}
#prevWatermark{position:absolute;bottom:12px;right:12px;font-family:'Orbitron',sans-serif;font-size:9px;color:rgba(255,215,0,0.4);letter-spacing:2px;z-index:30;pointer-events:none;}
#prevStatus{position:absolute;top:12px;left:12px;font-size:10px;color:#FFD700;font-family:'Orbitron',sans-serif;letter-spacing:1px;z-index:30;pointer-events:none;}
#demoTag{position:absolute;top:12px;right:12px;font-family:'Orbitron',sans-serif;font-size:8px;color:#000;background:#FFD700;padding:3px 8px;border-radius:4px;letter-spacing:2px;z-index:35;pointer-events:none;animation:demoPulse 2s ease-in-out infinite;}
@keyframes demoPulse{0%,100%{opacity:1;}50%{opacity:0.5;}}
#demoHint{position:absolute;inset:0;z-index:50;display:flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(0,0,0,0.7);cursor:pointer;text-align:center;padding:20px;backdrop-filter:blur(4px);}
#demoHint .dh-icon{font-size:40px;color:#FFD700;margin-bottom:12px;animation:dhBounce 1.5s ease-in-out infinite;}
@keyframes dhBounce{0%,100%{transform:translateY(0);}50%{transform:translateY(-8px);}}
#demoHint .dh-text{font-family:'Orbitron',sans-serif;font-size:14px;color:#FFD700;letter-spacing:2px;margin-bottom:8px;}
#demoHint .dh-sub{font-size:13px;color:#ccc;line-height:1.6;}
#demoHint .dh-tap{font-size:10px;color:#666;margin-top:16px;letter-spacing:1px;}
.prev-nudge{text-align:center;font-size:11px;color:#888;margin-top:8px;padding:6px;border:1px dashed #333;border-radius:8px;}

/* LYRICS BOX */
.lyrics-pro{position:absolute;top:12px;right:14px;background:#FFD700;color:#000;font-size:8px;font-weight:900;padding:3px 8px;border-radius:8px;font-family:'Orbitron',sans-serif;letter-spacing:1px;}
.lyrics-count{text-align:right;font-size:11px;color:#555;margin-top:4px;}

/* MUSIC GEN PANEL */
#musicPanel{position:relative;border-color:#FFD700;animation:mgSlide 0.3s ease-out;}
@keyframes mgSlide{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:translateY(0);}}
.mg-close{position:absolute;top:12px;right:14px;font-size:22px;color:#666;cursor:pointer;line-height:1;}
.mg-close:hover{color:#fff;}
.mg-input{width:100%;padding:10px 12px;background:#1a1a1a;border:1px solid #333;border-radius:8px;color:#fff;font-size:13px;margin-bottom:8px;font-family:'Poppins',sans-serif;}
.mg-input:focus{border-color:#FFD700;outline:none;}
.mg-check{display:block;font-size:12px;color:#aaa;margin:6px 0 12px;cursor:pointer;}
.mg-check input{margin-right:6px;}
.mg-btn{width:100%;padding:14px;background:linear-gradient(135deg,#FFD700,#FF6B35);border:none;border-radius:8px;font-family:'Orbitron',sans-serif;font-size:13px;font-weight:900;color:#000;letter-spacing:2px;cursor:pointer;transition:all 0.2s;}
.mg-btn:hover{transform:scale(1.02);box-shadow:0 4px 20px rgba(255,215,0,0.3);}
.mg-btn:disabled{opacity:0.4;cursor:not-allowed;transform:none;}
.mg-status{margin-top:10px;padding:10px;background:#1a1a1a;border-radius:8px;font-size:12px;color:#FFD700;text-align:center;}
.mg-actions{display:flex;gap:8px;margin-top:8px;}
.mg-use{flex:1;padding:10px;background:#FFD700;border:none;border-radius:8px;font-family:'Orbitron',sans-serif;font-size:11px;font-weight:900;color:#000;letter-spacing:1px;cursor:pointer;}
.mg-use:hover{background:#ffed4a;}
.mg-dl{flex:1;padding:10px;background:#222;border:1px solid #FFD700;border-radius:8px;font-family:'Orbitron',sans-serif;font-size:11px;font-weight:900;color:#FFD700;letter-spacing:1px;cursor:pointer;text-decoration:none;text-align:center;}

/* CONTROLS */
.card{background:#151515;border:1px solid #222;border-radius:12px;padding:16px;margin-bottom:12px;}
.card h3{font-family:'Orbitron',sans-serif;font-size:12px;color:#FFD700;letter-spacing:2px;margin-bottom:10px;}

/* AUTO BUTTON */
#autoBtn{width:100%;padding:20px;background:linear-gradient(135deg,#FFD700,#FF6B35);border:none;border-radius:12px;font-family:'Orbitron',sans-serif;font-size:18px;font-weight:900;color:#000;letter-spacing:3px;cursor:pointer;transition:all 0.2s;margin-bottom:4px;}
#autoBtn:hover{transform:scale(1.02);box-shadow:0 4px 30px rgba(255,215,0,0.4);}
#autoBtn:disabled{opacity:0.4;cursor:not-allowed;transform:none;}
.auto-sub{font-size:10px;color:#666;text-align:center;margin-bottom:12px;}

/* MANUAL BUTTONS */
.manual-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.mbtn{padding:12px;background:#1a1a1a;border:1px solid #333;border-radius:8px;color:#fff;font-size:12px;font-weight:600;cursor:pointer;text-align:center;transition:all 0.2s;}
.mbtn:hover{border-color:#FFD700;background:#1f1a10;}
.mbtn .ico{font-size:20px;display:block;margin-bottom:4px;}

/* TIMELINE */
#timeline{display:flex;gap:4px;flex-wrap:wrap;min-height:60px;background:#0a0a0a;border:1px dashed #333;border-radius:8px;padding:8px;align-items:center;}
.titem{width:50px;height:50px;border-radius:6px;overflow:hidden;border:2px solid #333;cursor:pointer;position:relative;flex-shrink:0;}
.titem img{width:100%;height:100%;object-fit:cover;}
.titem.active{border-color:#FFD700;}
.titem .tdel{position:absolute;top:-4px;right:-4px;width:16px;height:16px;background:#f00;color:#fff;border:none;border-radius:50%;font-size:9px;cursor:pointer;display:flex;align-items:center;justify-content:center;}
.titem .ttype{position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.7);font-size:7px;text-align:center;color:#FFD700;padding:1px;}
#timeline .empty{color:#555;font-size:11px;width:100%;text-align:center;}

/* TEXT ITEMS */
.text-entry{background:#1a1a1a;border:1px solid #333;border-radius:6px;padding:8px;margin-bottom:6px;display:flex;gap:8px;align-items:center;}
.text-entry input{flex:1;background:#0a0a0a;border:1px solid #333;border-radius:4px;padding:6px 8px;color:#fff;font-size:12px;outline:none;}
.text-entry input:focus{border-color:#FFD700;}
.text-entry .tdel{width:24px;height:24px;background:#f00;color:#fff;border:none;border-radius:50%;font-size:11px;cursor:pointer;}

/* MUSIC */
#musicInfo{font-size:11px;color:#888;padding:6px 0;}
#musicInfo b{color:#FFD700;}

/* LOG */
#log{background:#0a0a0a;border:1px solid #222;border-radius:8px;padding:8px;max-height:120px;overflow-y:auto;font-size:10px;font-family:monospace;color:#555;}
#log div{padding:2px 0;border-bottom:1px solid #111;}
#log .ok{color:#4f4;}
#log .warn{color:#fa0;}
#log .err{color:#f44;}

/* BUSY BANNER */
#busyBanner{display:none;background:#330;border:1px solid #660;border-radius:8px;padding:12px;text-align:center;font-size:12px;color:#ffa;margin-bottom:12px;}

/* HIDDEN FILE INPUTS */
.hidden{display:none;}
</style>
</head>
<body>

<div class="topbar">
  <div class="brand">IMAGINATOR &mdash; STEP 1</div>
  <div style="display:flex;align-items:center;gap:10px;">
    <div class="token-badge" id="tokenBadge" onclick="showTokenPanel()"><span class="token-coin">&#9733;</span><span id="tokenCount">0</span> SF</div>
    <div id="gSignIn"><button class="gsign" onclick="googleSignIn()">Sign in</button></div>
    <div id="gUserBar" class="guser" style="display:none;" onclick="googleSignIn()">
      <img id="gAvatar" src="" alt="">
      <span id="gName"></span>
    </div>
  </div>
</div>

<div id="busyBanner">BUSY HOURS &mdash; Free users limited to 1 create. <a href="#" style="color:#FFD700;">Go Monthly</a> for unlimited.</div>

<div class="wrap">
  <!-- PREVIEW -->
  <div class="col-preview">
    <div id="preview">
      <div id="prevBg"></div>
      <div id="prevFx"></div>
      <div id="prevVignette"></div>
      <div id="prevFlash"></div>
      <div id="prevLightning"></div>
      <div id="prevBreath"></div>
      <canvas id="prevGrain"></canvas>
      <div id="shutterT"></div>
      <div id="shutterB"></div>
      <div id="prevText"><div class="ktext" id="ktext"></div></div>
      <div id="prevWatermark">SHORTF&#9650;CTORY</div>
      <div id="prevStatus">IDLE</div>
      <div id="demoTag">DEMO REEL</div>
      <div id="demoHint" onclick="this.style.display='none'">
        <div class="dh-icon">&#9654;</div>
        <div class="dh-text">This is a <b>demo</b> of our effects engine</div>
        <div class="dh-sub">Add your own images using the buttons<br>and they'll appear right here</div>
        <div class="dh-tap">tap to dismiss</div>
      </div>
    </div>
    <div class="prev-nudge">&#9650; Your content previews here &mdash; add images or hit AUTO CREATE</div>
  </div>

  <!-- CONTROLS -->
  <div class="col-controls">
    <!-- AUTO -->
    <button id="autoBtn" onclick="autoGenerate()">AUTO CREATE</button>
    <div class="auto-sub">One click. AI does everything. Content owned by ShortF&#9650;ctory.</div>

    <!-- MANUAL OPTIONS -->
    <div class="card">
      <h3>OR CUSTOMISE</h3>
      <div class="manual-grid">
        <div class="mbtn" onclick="document.getElementById('imgInput').click()"><span class="ico">+</span>Add Image</div>
        <div class="mbtn" onclick="toggleMusicPanel()"><span class="ico">M</span>AI Music</div>
      </div>
    </div>

    <!-- LYRICS (greyed out — PRO/future) -->
    <div class="card" style="opacity:0.4;position:relative;">
      <h3>LYRICS</h3>
      <span class="lyrics-pro">COMING SOON</span>
      <textarea id="lyricsBox" maxlength="5000" placeholder="Paste your song lyrics here..." disabled style="width:100%;min-height:100px;background:#1a1a1a;border:1px solid #333;border-radius:8px;color:#666;font-size:13px;padding:10px;resize:vertical;font-family:'Poppins',sans-serif;"></textarea>
      <div class="lyrics-count"><span id="lyricsCount">0</span>/5000</div>
    </div>

    <!-- MUSIC -->
    <div class="card">
      <h3>MUSIC</h3>
      <div id="musicInfo">No music selected. AUTO will pick one.</div>
      <audio id="musicPlayer" style="width:100%;margin-top:6px;display:none;" controls></audio>
    </div>

    <!-- PLAY -->
    <div class="card">
      <button id="playBtn" onclick="playPreview()" style="width:100%;padding:14px;background:#222;border:1px solid #FFD700;border-radius:8px;color:#FFD700;font-family:'Orbitron',sans-serif;font-size:13px;letter-spacing:2px;cursor:pointer;">PLAY PREVIEW</button>
    </div>

    <!-- EXPORT -->
    <div class="card">
      <button id="exportBtn" onclick="exportVideo()" style="width:100%;padding:16px;background:linear-gradient(135deg,#00cc66,#009944);border:none;border-radius:8px;color:#fff;font-family:'Orbitron',sans-serif;font-size:13px;letter-spacing:2px;cursor:pointer;transition:all 0.2s;">EXPORT VIDEO</button>
      <div id="exportProgress" style="display:none;margin-top:10px;">
        <div style="background:#222;border-radius:6px;overflow:hidden;height:8px;margin-bottom:6px;">
          <div id="exportBar" style="height:100%;background:linear-gradient(90deg,#00cc66,#FFD700);width:0%;transition:width 0.3s;border-radius:6px;"></div>
        </div>
        <div id="exportStatus" style="font-size:11px;color:#888;text-align:center;"></div>
      </div>
      <!-- YouTube publish (appears after export) -->
      <div id="ytPublish" style="display:none;margin-top:12px;background:#111;border:1px solid #ff0000;border-radius:10px;padding:14px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
          <svg width="24" height="18" viewBox="0 0 24 18"><path fill="#ff0000" d="M23.5 2.8A3 3 0 0021.4.7C19.5.1 12 .1 12 .1S4.5.1 2.6.7A3 3 0 00.5 2.8 31.9 31.9 0 000 8.6a31.9 31.9 0 00.5 5.8 3 3 0 002.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 002.1-2.1 31.9 31.9 0 00.5-5.8 31.9 31.9 0 00-.5-5.8z"/><path fill="#fff" d="M9.5 12.2V5l6.3 3.6-6.3 3.6z"/></svg>
          <span style="font-family:'Orbitron',sans-serif;font-size:11px;color:#ff0000;letter-spacing:2px;">PUBLISH TO YOUTUBE SHORTS</span>
        </div>
        <div id="ytNotLinked">
          <input type="text" id="ytTitle" placeholder="Short title..." value="" style="width:100%;padding:10px 12px;background:#1a1a1a;border:1px solid #333;border-radius:8px;color:#fff;font-size:13px;margin-bottom:8px;">
          <div style="font-size:10px;color:#666;margin-bottom:10px;">Must be under 3 minutes to be listed as a YouTube Short. Your video qualifies!</div>
          <button id="ytLinkBtn" onclick="ytAuth()" style="width:100%;padding:14px;background:linear-gradient(135deg,#ff0000,#cc0000);border:none;border-radius:8px;color:#fff;font-family:'Orbitron',sans-serif;font-size:12px;letter-spacing:2px;cursor:pointer;transition:all 0.2s;">LINK YOUTUBE ACCOUNT</button>
        </div>
        <div id="ytLinked" style="display:none;">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
            <div style="width:8px;height:8px;border-radius:50%;background:#00cc66;"></div>
            <span id="ytEmail" style="font-size:12px;color:#aaa;"></span>
          </div>
          <input type="text" id="ytTitle2" placeholder="Short title..." value="" style="width:100%;padding:10px 12px;background:#1a1a1a;border:1px solid #333;border-radius:8px;color:#fff;font-size:13px;margin-bottom:8px;">
          <button id="ytUploadBtn" onclick="ytUpload()" style="width:100%;padding:14px;background:linear-gradient(135deg,#ff0000,#cc0000);border:none;border-radius:8px;color:#fff;font-family:'Orbitron',sans-serif;font-size:12px;letter-spacing:2px;cursor:pointer;">PUBLISH SHORT</button>
          <div id="ytUploadProgress" style="display:none;margin-top:8px;">
            <div style="background:#222;border-radius:6px;overflow:hidden;height:6px;margin-bottom:4px;">
              <div id="ytUploadBar" style="height:100%;background:#ff0000;width:0%;transition:width 0.3s;border-radius:6px;"></div>
            </div>
            <div id="ytUploadStatus" style="font-size:11px;color:#888;text-align:center;"></div>
          </div>
          <div id="ytSuccess" style="display:none;margin-top:10px;padding:12px;background:rgba(0,204,102,0.1);border:1px solid #00cc66;border-radius:8px;text-align:center;">
            <div style="font-size:13px;color:#00cc66;font-weight:700;margin-bottom:4px;">Published!</div>
            <a id="ytLink" href="#" target="_blank" style="color:#FFD700;font-size:12px;">View on YouTube</a>
          </div>
        </div>
      </div>
    </div>

    <!-- GOOGLE DRIVE SAVE -->
    <div class="card" id="driveCard" style="display:none;">
      <h3>GOOGLE DRIVE</h3>
      <div style="display:flex;gap:8px;">
        <button id="saveProjectBtn" onclick="saveProjectToDrive()" style="flex:1;padding:12px;background:#222;border:1px solid #4285F4;border-radius:8px;color:#4285F4;font-family:'Orbitron',sans-serif;font-size:11px;letter-spacing:1px;cursor:pointer;transition:all 0.2s;">SAVE PROJECT</button>
        <button id="saveVideoBtn" onclick="saveVideoToDrive()" style="flex:1;padding:12px;background:#222;border:1px solid #00cc66;border-radius:8px;color:#00cc66;font-family:'Orbitron',sans-serif;font-size:11px;letter-spacing:1px;cursor:pointer;transition:all 0.2s;" disabled>SAVE VIDEO</button>
      </div>
      <div id="driveSaveStatus" style="font-size:11px;color:#888;text-align:center;margin-top:6px;"></div>
    </div>

    <!-- LOG -->
    <div class="card">
      <h3>LOG</h3>
      <div id="log"></div>
    </div>

    <!-- MUSIC GEN PANEL (hidden until AI Music clicked) -->
    <div class="card" id="musicPanel" style="display:none;">
      <h3>AI MUSIC GENERATOR</h3>
      <div class="mg-close" onclick="toggleMusicPanel()">&times;</div>
      <input type="text" id="mgTitle" placeholder="Track title" value="ShortFactory Beat" class="mg-input">
      <input type="text" id="mgStyles" placeholder="Styles (comma separated)" value="dark, synthwave, cinematic" class="mg-input">
      <label class="mg-check"><input type="checkbox" id="mgInstrumental" checked> Instrumental (no vocals)</label>
      <button id="mgGenBtn" class="mg-btn" onclick="startMusicGen()">GENERATE TRACK</button>
      <div id="mgStatus" class="mg-status" style="display:none;"></div>
      <div id="mgResult" style="display:none;">
        <audio id="mgAudio" controls style="width:100%;margin-top:8px;"></audio>
        <div class="mg-actions">
          <button class="mg-use" onclick="useMusicTrack()">USE IN VIDEO</button>
          <a id="mgDownload" href="#" download class="mg-dl">Download MP3</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- TOKEN EARN PANEL -->
<div id="tokenOverlay" onclick="hideTokenPanel()"></div>
<div id="tokenPanel">
  <h3>&#9733; SF TOKENS</h3>
  <div style="text-align:center;font-size:28px;color:#FFD700;font-family:'Orbitron',sans-serif;font-weight:900;margin-bottom:4px;" id="tokenBig">0</div>
  <div style="text-align:center;font-size:11px;color:#888;margin-bottom:16px;">1 token = 1 video export</div>
  <div class="earn-row"><span class="earn-action">Sign up (first time)</span><span class="earn-reward">+5</span></div>
  <div class="earn-row"><span class="earn-action">Daily login</span><span class="earn-reward">+1</span></div>
  <div class="earn-row"><span class="earn-action">Refer a friend who signs in</span><span class="earn-reward">+3</span></div>
  <div class="earn-row"><span class="earn-action">Share on social media</span><span class="earn-reward">+1</span></div>
  <div class="earn-row"><span class="earn-action">PRO subscriber</span><span class="earn-reward">UNLIMITED</span></div>
  <button class="earn-btn" onclick="claimDailyToken()">CLAIM DAILY TOKEN</button>
  <button class="earn-btn" onclick="hideTokenPanel();toggleRefPanel&&toggleRefPanel();" style="background:linear-gradient(135deg,#9944ff,#6600cc);color:#fff;margin-top:6px;">REFER A FRIEND (+3)</button>
  <div style="text-align:center;margin-top:10px;"><a href="https://www.shortfactory.shop/checkout.html" style="color:#FFD700;font-size:11px;">Go PRO — Unlimited tokens</a></div>
</div>

<!-- HIDDEN INPUTS -->
<input type="file" id="imgInput" class="hidden" accept="image/*" multiple onchange="handleImages(this.files)">
<!-- musicInput removed — PRO only -->

<script>
/* ============================
   STATE
   ============================ */
var items = []; // {type:'image'|'text'|'lyrics', src:string, text:string}
var musicSrc = null;
var musicIsAuto = false;
var lastMood = '';
var isPlaying = false;
var playIndex = 0;
var playTimer = null;
var textTimer = null;
var dailyKey = 'sf_imaginator_'+new Date().toISOString().slice(0,10);
var usesLeft = 3;
var isBusy = false;

/* ============================
   RATE LIMITING
   ============================ */
function initLimits(){
  var stored = localStorage.getItem(dailyKey);
  if(stored != null) usesLeft = parseInt(stored);
  else usesLeft = 3;

  // Busy hours: 18:00-22:00 local time
  var h = new Date().getHours();
  if(h >= 18 && h < 22){
    isBusy = true;
    document.getElementById('busyBanner').style.display = 'block';
    if(usesLeft > 1) usesLeft = 1; // limit to 1 during busy
  }

  updateLimitUI();
}

function useCredit(){
  if(usesLeft <= 0) return false;
  usesLeft--;
  localStorage.setItem(dailyKey, usesLeft);
  updateLimitUI();
  return true;
}

function updateLimitUI(){
  document.getElementById('usesLeft').textContent = usesLeft;
  document.getElementById('autoBtn').disabled = (usesLeft <= 0);
  if(usesLeft <= 0){
    document.getElementById('autoBtn').textContent = 'DAILY LIMIT REACHED';
  }
}

initLimits();

/* ============================
   LOGGING
   ============================ */
function log(msg, cls){
  var d = document.getElementById('log');
  var line = document.createElement('div');
  line.className = cls || '';
  line.textContent = '['+new Date().toLocaleTimeString()+'] '+msg;
  d.appendChild(line);
  d.scrollTop = d.scrollHeight;
}
log('Imaginator ready. '+usesLeft+' creates remaining today.','ok');

/* ============================
   AUTO GENERATE
   ============================ */
function dismissDemo(){
  var h=document.getElementById('demoHint');if(h)h.style.display='none';
  var t=document.getElementById('demoTag');if(t)t.style.display='none';
  var n=document.querySelector('.prev-nudge');if(n)n.style.display='none';
}

function autoGenerate(){
  dismissDemo();
  if(usesLeft <= 0){log('Daily limit reached.','err');return;}
  if(!useCredit()){log('No credits left.','err');return;}

  var btn = document.getElementById('autoBtn');
  btn.disabled = true;
  btn.textContent = 'GENERATING...';
  document.getElementById('prevStatus').textContent = 'GENERATING...';
  log('AUTO: Starting generation...','ok');

  // Step 1: Ask AI for a theme + text lines
  var sysMsg = 'You are a creative director for ShortFactory video platform. Generate a short video concept. Return ONLY a JSON object with: theme (2-3 word title), mood (1 word), texts (array of 4-6 short punchy text lines for kinetic typography, max 6 words each), imageCount (number 6-12). Example: {"theme":"Night Terror","mood":"horror","texts":["THEY ARE WATCHING","RUN","DONT LOOK BACK","ITS BEHIND YOU","SCREAM"],"imageCount":8}';

  fetch('https://api.x.ai/v1/chat/completions',{
    method:'POST',
    headers:{'Content-Type':'application/json','Authorization':'Bearer xai-dZJEKBMwCzVFMGJ8JuD4cN3iG9sSuwzmWe0H3LcZfJssPuyyHTcIDHyZnRPgRVmuU81lpYblFfhLqkzj'},
    body:JSON.stringify({
      model:'grok-4-latest',
      messages:[{role:'system',content:sysMsg},{role:'user',content:'Generate a random engaging video concept. Be creative and dramatic.'}],
      max_tokens:200,
      temperature:1.0
    })
  })
  .then(function(r){return r.json();})
  .then(function(data){
    var text = data.choices&&data.choices[0]&&data.choices[0].message&&data.choices[0].message.content||'';
    var m = text.match(/\{[\s\S]*\}/);
    if(!m) throw new Error('No JSON');
    var concept = JSON.parse(m[0]);
    log('AI concept: '+concept.theme+' ('+concept.mood+')','ok');
    buildFromConcept(concept);
  })
  .catch(function(err){
    log('AI failed, using fallback: '+err.message,'warn');
    // Fallback concepts
    var fallbacks = [
      {theme:'Urban Chaos',mood:'intense',texts:['THE CITY NEVER SLEEPS','CONCRETE JUNGLE','SIRENS IN THE DARK','KEEP MOVING','NO WAY OUT','SURVIVE'],imageCount:8},
      {theme:'Lost Signal',mood:'eerie',texts:['SIGNAL LOST','CAN ANYONE HEAR','STATIC','THEY WENT SILENT','FIND THEM','TOO LATE'],imageCount:7},
      {theme:'Golden Hour',mood:'dreamy',texts:['CHASE THE LIGHT','GOLDEN MOMENTS','TIME STANDS STILL','BREATHE','BEAUTIFUL CHAOS'],imageCount:6},
      {theme:'Street Kings',mood:'hype',texts:['RUN THIS TOWN','NO RULES','ALL GAS NO BRAKES','LEGENDS ONLY','BUILT DIFFERENT','CROWN ME'],imageCount:10},
      {theme:'Deep Water',mood:'dark',texts:['SINKING','CANT BREATHE','THE DEEP','NO SURFACE','DROWN IN IT','RISE'],imageCount:8}
    ];
    var concept = fallbacks[Math.floor(Math.random()*fallbacks.length)];
    log('Fallback: '+concept.theme,'ok');
    buildFromConcept(concept);
  });
}

function buildFromConcept(concept){
  // Clear current
  items = [];
  lastMood = concept.mood || '';

  // Pick random stills from both sets
  var pool = stillsPool.slice();
  shuffle(pool);
  var count = concept.imageCount || 8;
  var picked = pool.slice(0, Math.min(count, 80));

  log('Picking '+picked.length+' stills from library','ok');

  // Add images as items
  picked.forEach(function(src, idx){
    items.push({
      type:'image',
      src:src,
      text: concept.texts[idx % concept.texts.length] || ''
    });
  });

  // Interleave remaining text lines
  if(concept.texts.length > picked.length){
    for(var i=picked.length; i<concept.texts.length; i++){
      // attach extra text to random existing item
      var ri = Math.floor(Math.random()*items.length);
      items[ri].text = concept.texts[i];
    }
  }

  // Nature soundscape — matched to mood
  musicSrc = 'procedural';
  musicIsAuto = true;
  var scapeKey = pickScape(concept.mood);
  document.getElementById('musicInfo').innerHTML = 'AUTO: <b>'+SCAPES[scapeKey].label+'</b> (matched to '+concept.mood+')';

  log('Soundscape: '+SCAPES[scapeKey].label+' (mood: '+concept.mood+')','ok');
  log('Build complete: '+items.length+' slides, '+concept.texts.length+' text lines','ok');

  renderTimeline();
  renderTextList();

  document.getElementById('autoBtn').disabled = (usesLeft<=0);
  document.getElementById('autoBtn').textContent = usesLeft<=0?'DAILY LIMIT REACHED':'AUTO CREATE';
  document.getElementById('prevStatus').textContent = 'READY — HIT PLAY';

  // Auto-play
  setTimeout(function(){playPreview();},500);
}

function shuffle(a){for(var i=a.length-1;i>0;i--){var j=Math.floor(Math.random()*(i+1));var t=a[i];a[i]=a[j];a[j]=t;}}

/* ============================
   MANUAL: ADD IMAGE
   ============================ */
function handleImages(files){
  dismissDemo();
  for(var i=0;i<files.length;i++){
    (function(file){
      var reader = new FileReader();
      reader.onload = function(e){
        items.push({type:'image',src:e.target.result,text:'',isCustom:true});
        log('Added image: '+file.name,'ok');
        renderTimeline();
      };
      reader.readAsDataURL(file);
    })(files[i]);
  }
}

/* ============================
   MANUAL: ADD TEXT
   ============================ */
function addText(){
  items.push({type:'text',src:'',text:'YOUR TEXT HERE'});
  log('Added text slide','ok');
  renderTimeline();
  renderTextList();
  document.getElementById('textCard').style.display = 'block';
}

// addLyrics + addMusic = PRO only (greyed out)

/* ============================
   MANUAL: ADD MUSIC
   ============================ */
function handleMusic(file){
  if(!file) return;
  var reader = new FileReader();
  reader.onload = function(e){
    musicSrc = e.target.result;
    musicIsAuto = false;
    document.getElementById('musicInfo').innerHTML = 'CUSTOM: <b>'+file.name+'</b>';
    document.getElementById('musicPlayer').src = musicSrc;
    document.getElementById('musicPlayer').style.display = 'block';
    log('Music loaded: '+file.name,'ok');
  };
  reader.readAsDataURL(file);
}

/* ============================
   TIMELINE RENDER
   ============================ */
function renderTimeline(){
  var tl = document.getElementById('timeline');
  if(!items.length){tl.innerHTML='<div class="empty">Empty — click AUTO or add items</div>';return;}

  tl.innerHTML = '';
  items.forEach(function(item, idx){
    var div = document.createElement('div');
    div.className = 'titem';
    if(item.type === 'image' && item.src){
      div.innerHTML = '<img src="'+item.src+'">';
    } else {
      div.style.background = '#1a1a2a';
      div.innerHTML = '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:9px;padding:2px;text-align:center;color:#FFD700;">'+escHtml(item.text.slice(0,20))+'</div>';
    }
    div.innerHTML += '<div class="ttype">'+(item.type==='image'?'IMG':'TXT')+'</div>';
    div.innerHTML += '<button class="tdel" onclick="removeItem('+idx+')">x</button>';
    tl.appendChild(div);
  });
}

function renderTextList(){
  var list = document.getElementById('textList');
  var hasText = items.some(function(i){return i.text;});
  if(!hasText){document.getElementById('textCard').style.display='none';return;}
  document.getElementById('textCard').style.display='block';

  list.innerHTML = '';
  items.forEach(function(item, idx){
    if(!item.text && item.type!=='text') return;
    var div = document.createElement('div');
    div.className = 'text-entry';
    div.innerHTML = '<input value="'+escAttr(item.text)+'" onchange="items['+idx+'].text=this.value" placeholder="Text for slide '+(idx+1)+'...">';
    div.innerHTML += '<button class="tdel" onclick="items['+idx+'].text=\'\';renderTextList();">x</button>';
    list.appendChild(div);
  });
}

function removeItem(idx){
  items.splice(idx,1);
  renderTimeline();
  renderTextList();
  log('Removed item '+idx,'warn');
}

function escHtml(s){return (s||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function escAttr(s){return (s||'').replace(/"/g,'&quot;').replace(/</g,'&lt;');}

/* ============================
   VIDMAN ENGINE — CINEMATIC KALEIDOSCOPE
   ============================ */
var prevBg = document.getElementById('prevBg');
var prevFx = document.getElementById('prevFx');
var prevFlash = document.getElementById('prevFlash');
var prevLightning = document.getElementById('prevLightning');
var prevBreath = document.getElementById('prevBreath');
var grainCanvas = document.getElementById('prevGrain');
var grainCtx = grainCanvas.getContext('2d');
var shutterT = document.getElementById('shutterT');
var shutterB = document.getElementById('shutterB');
var ktext = document.getElementById('ktext');

// Image pools
var STILLS_BASE = '/imaginator/stills/';
var stillsPool = [];
for(var s=1;s<=2;s++) for(var n=1;n<=40;n++) stillsPool.push(STILLS_BASE+'set'+s+'/'+n+'.jpg');

// Engine state
var breathPhase = 0;
var baseZoom = 230;
var zoomTarget = 230;
var zoomSpeed = 0.04; // how fast zoom eases (0.04 = responsive)
var contrastValue = 0;
var transLock = false; // locks during transitions
var camOffX = 0, camOffY = 0; // extra camera offset for transitions
var camTargetX = 0, camTargetY = 0;
var shakeIntensity = 1; // multiplier for panic moments
var driftX = 0, driftY = 0, driftTX = 0, driftTY = 0; // random drift (no pattern)
var breathRate = 1.8; // LFO rate — varies over time
var frameCount = 0;

function rand(a,b){return a+Math.random()*(b-a);}

// Set image on tiled bg (prevFx is now a pure blur overlay, no image needed)
function setStill(src){
  prevBg.style.backgroundImage='url('+src+')';
}

/* ============================
   NATURE SOUNDSCAPE ENGINE
   Wind, Rain, Forest, Ocean, Night, Storm
   All synthesized — zero copyright
   ============================ */
var audioCtx = null;
var masterGain = null;
var musicPlaying = false;
var scapeNodes = []; // all active nodes for cleanup
var scapeTimers = []; // all intervals for cleanup
var activeScape = '';

// Soundscape presets matched to moods
var SCAPES = {
  wind:    {label:'Howling Wind',  moods:['eerie','dark','intense','lonely']},
  rain:    {label:'Rainfall',      moods:['calm','dreamy','melancholy','sad']},
  storm:   {label:'Thunderstorm',  moods:['intense','dark','horror','dramatic']},
  forest:  {label:'Deep Forest',   moods:['calm','dreamy','peaceful','nature']},
  ocean:   {label:'Ocean Waves',   moods:['dreamy','epic','vast','adventure']},
  night:   {label:'Night Ambience',moods:['eerie','horror','suspense','mystery']},
  desolate:{label:'Desolate Void', moods:['dark','horror','dread','dystopian']}
};
var SCAPE_KEYS = Object.keys(SCAPES);

function initAudio(){
  try{
    audioCtx = new (window.AudioContext||window.webkitAudioContext)();
    masterGain = audioCtx.createGain();
    masterGain.gain.value = 0.8;
    masterGain.connect(audioCtx.destination);
    initBreathing();
    initHeartbeat();
    initCoughSneeze();
    log('Audio engine ready','ok');
  }catch(e){log('Audio init failed','err');}
}

// Utility: create looping noise buffer
function makeNoise(seconds,color){
  var len = audioCtx.sampleRate*seconds|0;
  var buf = audioCtx.createBuffer(1,len,audioCtx.sampleRate);
  var d = buf.getChannelData(0);
  var last=0,last2=0;
  for(var i=0;i<len;i++){
    var white=Math.random()*2-1;
    if(color==='brown'){last=(last+(0.02*white))/1.02;d[i]=last*3.5;}
    else if(color==='pink'){last=0.99765*last+white*0.0990460;last2=0.963*last2+white*0.2965164;d[i]=(last+last2+white*0.1848)*0.6;}
    else d[i]=white; // white
  }
  var src=audioCtx.createBufferSource();src.buffer=buf;src.loop=true;
  return src;
}

// Track nodes for cleanup
function trackNode(n){scapeNodes.push(n);return n;}
function trackTimer(t){scapeTimers.push(t);return t;}

/* ---- BREATHING (always on) ---- */
var breathLfo=null;
function initBreathing(){
  var noise=makeNoise(2,'brown');
  var bpf=audioCtx.createBiquadFilter();bpf.type='bandpass';bpf.frequency.value=800;bpf.Q.value=0.8;
  var bGain=audioCtx.createGain();bGain.gain.value=0;
  breathLfo=audioCtx.createOscillator();breathLfo.type='sine';breathLfo.frequency.value=0.3;
  var lfoG=audioCtx.createGain();lfoG.gain.value=0.04;
  breathLfo.connect(lfoG);lfoG.connect(bGain.gain);
  noise.connect(bpf);bpf.connect(bGain);bGain.connect(masterGain);
  noise.start();breathLfo.start();
  setInterval(function(){
    var r=0.2+Math.random()*0.4;
    breathLfo.frequency.linearRampToValueAtTime(r,audioCtx.currentTime+2);
    breathRate=r*6;
    // Sync heartbeat speed to breath rate
    heartbeatRate=r;
  },4000+Math.random()*6000);
}

/* ---- HEARTBEAT (real MP3, synced to breathing) ---- */
var heartbeatRate=0.3;
var hbBuffer=null;
var hbSource=null;
var hbGain=null;
function initHeartbeat(){
  // Load real heartbeat MP3
  fetch('/trump/heartbeat.mp3')
    .then(function(r){return r.arrayBuffer();})
    .then(function(buf){return audioCtx.decodeAudioData(buf);})
    .then(function(decoded){
      hbBuffer=decoded;
      startHeartbeatLoop();
      log('Heartbeat loaded','ok');
    })
    .catch(function(e){log('Heartbeat load failed: '+e,'err');});
}
function startHeartbeatLoop(){
  if(!hbBuffer||!audioCtx)return;
  if(hbSource){try{hbSource.stop();}catch(e){}}
  hbSource=audioCtx.createBufferSource();
  hbSource.buffer=hbBuffer;
  hbSource.loop=true;
  hbGain=audioCtx.createGain();
  hbGain.gain.value=0.35;
  hbSource.connect(hbGain);
  hbGain.connect(masterGain);
  // Playback rate: faster breathing = faster heartbeat (0.7x to 1.4x)
  hbSource.playbackRate.value=0.7+heartbeatRate*1.4;
  hbSource.start(0);
  // Continuously adapt rate to breathing
  (function adaptRate(){
    if(!hbSource||!musicPlaying)return;
    hbSource.playbackRate.setTargetAtTime(0.7+heartbeatRate*1.4,audioCtx.currentTime,0.5);
    setTimeout(adaptRate,500);
  })();
}

/* ---- COUGHS & SNEEZES (rare, with camera wobble) ---- */
var coughOdds=0.01; // 1/100 base, varies
function initCoughSneeze(){
  setInterval(function(){
    if(!audioCtx||!musicPlaying)return;
    // Vary odds over time (0.005 to 0.02)
    if(Math.random()<0.05) coughOdds=0.005+Math.random()*0.015;
    if(Math.random()>coughOdds)return;
    if(Math.random()<0.7) doCough(); else doSneeze();
  },1000);
}

function doCough(){
  if(!audioCtx)return;
  var t=audioCtx.currentTime;
  // Cough: short noise burst, bandpass 300-1200Hz, 2-3 pulses
  var pulses=2+Math.floor(Math.random()*2);
  for(var p=0;p<pulses;p++){
    var dur=0.06+Math.random()*0.04;
    var buf=audioCtx.createBuffer(1,audioCtx.sampleRate*dur|0,audioCtx.sampleRate);
    var d=buf.getChannelData(0);
    for(var i=0;i<d.length;i++){
      var env=Math.sin(i/d.length*Math.PI);
      d[i]=(Math.random()*2-1)*env;
    }
    var src=audioCtx.createBufferSource();src.buffer=buf;
    var g=audioCtx.createGain();g.gain.value=0.08+Math.random()*0.04;
    var bp=audioCtx.createBiquadFilter();bp.type='bandpass';bp.frequency.value=500+Math.random()*700;bp.Q.value=1.5;
    src.connect(bp);bp.connect(g);g.connect(masterGain);
    src.start(t+p*0.15);
  }
  // Camera wobble
  shakeIntensity=3+Math.random()*2;
  setTimeout(function(){shakeIntensity=1;},400+pulses*150);
  log('*cough*','');
}

function doSneeze(){
  if(!audioCtx)return;
  var t=audioCtx.currentTime;
  // Sneeze: inhale (rising noise) + explosive burst
  // Inhale: 0.4s rising filtered noise
  var inhDur=0.4;
  var inhBuf=audioCtx.createBuffer(1,audioCtx.sampleRate*inhDur|0,audioCtx.sampleRate);
  var id=inhBuf.getChannelData(0);
  for(var i=0;i<id.length;i++){
    var env=Math.pow(i/id.length,2); // rising
    id[i]=(Math.random()*2-1)*env*0.5;
  }
  var inhSrc=audioCtx.createBufferSource();inhSrc.buffer=inhBuf;
  var inhG=audioCtx.createGain();inhG.gain.value=0.06;
  var inhBp=audioCtx.createBiquadFilter();inhBp.type='highpass';inhBp.frequency.value=1500;
  inhSrc.connect(inhBp);inhBp.connect(inhG);inhG.connect(masterGain);
  inhSrc.start(t);
  // Burst: short loud wide-band noise
  var burstDur=0.1;
  var burstBuf=audioCtx.createBuffer(1,audioCtx.sampleRate*burstDur|0,audioCtx.sampleRate);
  var bd=burstBuf.getChannelData(0);
  for(var i=0;i<bd.length;i++){
    bd[i]=(Math.random()*2-1)*Math.pow(1-i/bd.length,1.5);
  }
  var burstSrc=audioCtx.createBufferSource();burstSrc.buffer=burstBuf;
  var burstG=audioCtx.createGain();burstG.gain.value=0.15;
  burstSrc.connect(burstG);burstG.connect(masterGain);
  burstSrc.start(t+inhDur);
  // Big camera jolt on the burst
  setTimeout(function(){
    shakeIntensity=6;
    setTimeout(function(){shakeIntensity=1;},500);
  },inhDur*1000);
  log('*sneeze*','');
}

/* ---- THUNDER ---- */
function thunderCrack(){
  if(!audioCtx)return;
  try{
    var dur=0.15+Math.random()*0.1;
    var buf=audioCtx.createBuffer(1,audioCtx.sampleRate*dur|0,audioCtx.sampleRate);
    var d=buf.getChannelData(0);
    for(var i=0;i<d.length;i++) d[i]=(Math.random()*2-1)*Math.pow(1-i/d.length,2);
    var src=audioCtx.createBufferSource();src.buffer=buf;
    var g=audioCtx.createGain();g.gain.value=0.15;
    g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+dur+0.8);
    var lp=audioCtx.createBiquadFilter();lp.type='lowpass';lp.frequency.value=1500;
    src.connect(lp);lp.connect(g);g.connect(masterGain);src.start();
  }catch(e){}
}

/* ============================
   SOUNDSCAPE GENERATORS
   Each returns nothing, builds into masterGain, tracked for cleanup
   ============================ */

// ---- WIND: layered filtered noise with slow LFO ----
function scapeWind(){
  var g=audioCtx.createGain();g.gain.value=0;
  g.gain.linearRampToValueAtTime(0.18,audioCtx.currentTime+3);
  g.connect(masterGain);trackNode(g);

  // Base wind: brown noise through bandpass
  var wind=makeNoise(3,'brown');
  var bp=audioCtx.createBiquadFilter();bp.type='bandpass';bp.frequency.value=400;bp.Q.value=0.3;
  wind.connect(bp);bp.connect(g);wind.start();trackNode(wind);

  // Gusts: second noise layer, LFO-modulated
  var gust=makeNoise(2,'pink');
  var gustG=audioCtx.createGain();gustG.gain.value=0;
  var gustLfo=audioCtx.createOscillator();gustLfo.type='sine';gustLfo.frequency.value=0.08;
  var gustLfoG=audioCtx.createGain();gustLfoG.gain.value=0.12;
  gustLfo.connect(gustLfoG);gustLfoG.connect(gustG.gain);
  var gustBp=audioCtx.createBiquadFilter();gustBp.type='bandpass';gustBp.frequency.value=800;gustBp.Q.value=0.5;
  gust.connect(gustBp);gustBp.connect(gustG);gustG.connect(g);
  gust.start();gustLfo.start();trackNode(gust);trackNode(gustLfo);

  // Howl: slow filter sweep on wind
  trackTimer(setInterval(function(){
    if(!musicPlaying)return;
    bp.frequency.linearRampToValueAtTime(200+Math.random()*600,audioCtx.currentTime+4);
    gustLfo.frequency.linearRampToValueAtTime(0.03+Math.random()*0.12,audioCtx.currentTime+3);
  },5000));
}

// ---- RAIN: rapid short noise bursts ----
function scapeRain(){
  var g=audioCtx.createGain();g.gain.value=0;
  g.gain.linearRampToValueAtTime(0.12,audioCtx.currentTime+2);
  g.connect(masterGain);trackNode(g);

  // Steady rain: high-passed white noise
  var rain=makeNoise(2,'white');
  var hp=audioCtx.createBiquadFilter();hp.type='highpass';hp.frequency.value=3000;
  var lp=audioCtx.createBiquadFilter();lp.type='lowpass';lp.frequency.value=8000;
  rain.connect(hp);hp.connect(lp);lp.connect(g);rain.start();trackNode(rain);

  // Individual drops: short noise pops at random intervals
  trackTimer(setInterval(function(){
    if(!musicPlaying)return;
    var dur=0.01+Math.random()*0.02;
    var buf=audioCtx.createBuffer(1,audioCtx.sampleRate*dur|0,audioCtx.sampleRate);
    var d=buf.getChannelData(0);
    for(var i=0;i<d.length;i++) d[i]=(Math.random()*2-1)*Math.pow(1-i/d.length,4);
    var src=audioCtx.createBufferSource();src.buffer=buf;
    var dg=audioCtx.createGain();dg.gain.value=0.03+Math.random()*0.04;
    var dbp=audioCtx.createBiquadFilter();dbp.type='bandpass';dbp.frequency.value=2000+Math.random()*5000;
    src.connect(dbp);dbp.connect(dg);dg.connect(g);src.start();
  },50+Math.random()*100));

  // Rain intensity varies
  trackTimer(setInterval(function(){
    if(!musicPlaying)return;
    var vol=0.06+Math.random()*0.12;
    g.gain.linearRampToValueAtTime(vol,audioCtx.currentTime+4);
    hp.frequency.linearRampToValueAtTime(2000+Math.random()*3000,audioCtx.currentTime+3);
  },6000));
}

// ---- STORM: wind + rain + periodic thunder ----
function scapeStorm(){
  scapeWind();
  scapeRain();
  // Periodic thunder
  trackTimer(setInterval(function(){
    if(!musicPlaying)return;
    if(Math.random()<0.3){
      thunderCrack();
      strikeLightning();
    }
  },4000+Math.random()*6000));
}

// ---- FOREST: birds + rustling leaves + gentle wind ----
function scapeForest(){
  // Gentle wind base
  var g=audioCtx.createGain();g.gain.value=0;
  g.gain.linearRampToValueAtTime(0.06,audioCtx.currentTime+2);
  g.connect(masterGain);trackNode(g);
  var wind=makeNoise(3,'brown');
  var bp=audioCtx.createBiquadFilter();bp.type='bandpass';bp.frequency.value=300;bp.Q.value=0.2;
  wind.connect(bp);bp.connect(g);wind.start();trackNode(wind);

  // Leaf rustling: high noise bursts
  trackTimer(setInterval(function(){
    if(!musicPlaying)return;
    if(Math.random()<0.4)return;
    var dur=0.1+Math.random()*0.2;
    var buf=audioCtx.createBuffer(1,audioCtx.sampleRate*dur|0,audioCtx.sampleRate);
    var d=buf.getChannelData(0);
    for(var i=0;i<d.length;i++){var e=Math.sin(i/d.length*Math.PI);d[i]=(Math.random()*2-1)*e*0.5;}
    var src=audioCtx.createBufferSource();src.buffer=buf;
    var lg=audioCtx.createGain();lg.gain.value=0.03;
    var lbp=audioCtx.createBiquadFilter();lbp.type='highpass';lbp.frequency.value=4000+Math.random()*3000;
    src.connect(lbp);lbp.connect(lg);lg.connect(masterGain);src.start();
  },800+Math.random()*1500));

  // Birds: sine chirps with fast pitch sweep
  trackTimer(setInterval(function(){
    if(!musicPlaying)return;
    if(Math.random()<0.5)return;
    var baseF=2000+Math.random()*3000;
    var osc=audioCtx.createOscillator();osc.type='sine';osc.frequency.value=baseF;
    var bg2=audioCtx.createGain();bg2.gain.value=0.03+Math.random()*0.02;
    osc.connect(bg2);bg2.connect(masterGain);
    osc.start();
    // Chirp: rapid pitch sweep
    var t=audioCtx.currentTime;
    var chirps=2+Math.floor(Math.random()*4);
    for(var c=0;c<chirps;c++){
      var ct=t+c*0.12;
      osc.frequency.setValueAtTime(baseF+Math.random()*1500,ct);
      osc.frequency.linearRampToValueAtTime(baseF-500+Math.random()*1000,ct+0.08);
    }
    bg2.gain.linearRampToValueAtTime(0.001,t+chirps*0.12+0.1);
    osc.stop(t+chirps*0.12+0.2);
  },2000+Math.random()*4000));
}

// ---- OCEAN: rhythmic low noise swells ----
function scapeOcean(){
  var g=audioCtx.createGain();g.gain.value=0;
  g.gain.linearRampToValueAtTime(0.15,audioCtx.currentTime+3);
  g.connect(masterGain);trackNode(g);

  // Wave base: brown noise
  var wave=makeNoise(3,'brown');
  var lp=audioCtx.createBiquadFilter();lp.type='lowpass';lp.frequency.value=500;
  wave.connect(lp);lp.connect(g);wave.start();trackNode(wave);

  // Wave swell LFO (0.06-0.12 Hz = 5-8s per wave)
  var waveLfo=audioCtx.createOscillator();waveLfo.type='sine';waveLfo.frequency.value=0.08;
  var waveAmt=audioCtx.createGain();waveAmt.gain.value=0.1;
  waveLfo.connect(waveAmt);waveAmt.connect(g.gain);
  waveLfo.start();trackNode(waveLfo);

  // Surf hiss: white noise timed to wave peaks
  var surf=makeNoise(2,'white');
  var surfG=audioCtx.createGain();surfG.gain.value=0;
  var surfHp=audioCtx.createBiquadFilter();surfHp.type='highpass';surfHp.frequency.value=2000;
  var surfLp=audioCtx.createBiquadFilter();surfLp.type='lowpass';surfLp.frequency.value=6000;
  surf.connect(surfHp);surfHp.connect(surfLp);surfLp.connect(surfG);surfG.connect(g);
  surf.start();trackNode(surf);

  // Surf LFO synced slightly offset from wave
  var surfLfo=audioCtx.createOscillator();surfLfo.type='sine';surfLfo.frequency.value=0.08;
  surfLfo.connect(audioCtx.createGain()).gain.value=0.04;
  // Manual surf pulse
  trackTimer(setInterval(function(){
    if(!musicPlaying)return;
    surfG.gain.linearRampToValueAtTime(0.06+Math.random()*0.04,audioCtx.currentTime+2);
    setTimeout(function(){
      surfG.gain.linearRampToValueAtTime(0.01,audioCtx.currentTime+3);
    },2500);
    // Vary wave speed
    waveLfo.frequency.linearRampToValueAtTime(0.05+Math.random()*0.08,audioCtx.currentTime+2);
  },6000+Math.random()*4000));
}

// ---- NIGHT: crickets + distant owl + subtle wind ----
function scapeNight(){
  // Very soft wind
  var g=audioCtx.createGain();g.gain.value=0;
  g.gain.linearRampToValueAtTime(0.04,audioCtx.currentTime+2);
  g.connect(masterGain);trackNode(g);
  var wind=makeNoise(3,'brown');
  var bp=audioCtx.createBiquadFilter();bp.type='bandpass';bp.frequency.value=250;bp.Q.value=0.2;
  wind.connect(bp);bp.connect(g);wind.start();trackNode(wind);

  // Crickets: high-freq pulsing oscillator
  var cOsc=audioCtx.createOscillator();cOsc.type='sine';cOsc.frequency.value=5500;
  var cGain=audioCtx.createGain();cGain.gain.value=0;
  var cLfo=audioCtx.createOscillator();cLfo.type='square';cLfo.frequency.value=7;
  var cLfoG=audioCtx.createGain();cLfoG.gain.value=0.02;
  cLfo.connect(cLfoG);cLfoG.connect(cGain.gain);
  cOsc.connect(cGain);cGain.connect(masterGain);
  cOsc.start();cLfo.start();trackNode(cOsc);trackNode(cLfo);

  // Second cricket layer, slightly different pitch/rate
  var c2=audioCtx.createOscillator();c2.type='sine';c2.frequency.value=5800;
  var c2G=audioCtx.createGain();c2G.gain.value=0;
  var c2L=audioCtx.createOscillator();c2L.type='square';c2L.frequency.value=5.5;
  var c2LG=audioCtx.createGain();c2LG.gain.value=0.015;
  c2L.connect(c2LG);c2LG.connect(c2G.gain);
  c2.connect(c2G);c2G.connect(masterGain);
  c2.start();c2L.start();trackNode(c2);trackNode(c2L);

  // Owl: occasional low sine hoot
  trackTimer(setInterval(function(){
    if(!musicPlaying)return;
    if(Math.random()<0.6)return;
    var osc=audioCtx.createOscillator();osc.type='sine';
    var base=280+Math.random()*60;
    osc.frequency.value=base;
    var og=audioCtx.createGain();og.gain.value=0.04;
    osc.connect(og);og.connect(masterGain);osc.start();
    var t=audioCtx.currentTime;
    // Two-tone hoot: "hoo-hooo"
    osc.frequency.setValueAtTime(base,t);
    osc.frequency.linearRampToValueAtTime(base-40,t+0.3);
    og.gain.setValueAtTime(0.04,t);
    og.gain.linearRampToValueAtTime(0.001,t+0.35);
    // Second note
    setTimeout(function(){
      og.gain.linearRampToValueAtTime(0.05,audioCtx.currentTime+0.1);
      osc.frequency.linearRampToValueAtTime(base-60,audioCtx.currentTime+0.5);
      og.gain.linearRampToValueAtTime(0.001,audioCtx.currentTime+0.6);
      osc.stop(audioCtx.currentTime+0.7);
    },500);
  },6000+Math.random()*8000));
}

// ---- DESOLATE: deep drone + distant metallic pings ----
function scapeDesolate(){
  var g=audioCtx.createGain();g.gain.value=0;
  g.gain.linearRampToValueAtTime(0.15,audioCtx.currentTime+4);
  g.connect(masterGain);trackNode(g);

  // Deep drone: detuned sine pair
  var d1=audioCtx.createOscillator();d1.type='sine';d1.frequency.value=55;
  var d2=audioCtx.createOscillator();d2.type='sine';d2.frequency.value=55.5;
  var d3=audioCtx.createOscillator();d3.type='triangle';d3.frequency.value=82.5;d3.detune.value=-10;
  [d1,d2,d3].forEach(function(d){d.connect(g);d.start();trackNode(d);});

  // Distant metallic pings
  trackTimer(setInterval(function(){
    if(!musicPlaying)return;
    if(Math.random()<0.5)return;
    var osc=audioCtx.createOscillator();osc.type='sine';
    osc.frequency.value=800+Math.random()*2000;
    var pg=audioCtx.createGain();pg.gain.value=0.02+Math.random()*0.02;
    pg.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+2);
    osc.connect(pg);pg.connect(masterGain);
    osc.start();osc.stop(audioCtx.currentTime+2.5);
  },3000+Math.random()*5000));

  // Sub rumble: very low filtered noise
  var rum=makeNoise(3,'brown');
  var rlp=audioCtx.createBiquadFilter();rlp.type='lowpass';rlp.frequency.value=80;
  var rg=audioCtx.createGain();rg.gain.value=0.08;
  rum.connect(rlp);rlp.connect(rg);rg.connect(g);rum.start();trackNode(rum);
}

/* ============================
   SOUNDSCAPE CONTROLLER
   ============================ */
// Match mood to best soundscape
function pickScape(mood){
  if(!mood) return SCAPE_KEYS[Math.floor(Math.random()*SCAPE_KEYS.length)];
  mood = mood.toLowerCase();
  var best=null,bestScore=0;
  SCAPE_KEYS.forEach(function(key){
    var score=0;
    SCAPES[key].moods.forEach(function(m){
      if(mood.indexOf(m)>=0||m.indexOf(mood)>=0) score+=2;
      // Partial match
      if(mood.charAt(0)===m.charAt(0)) score+=0.5;
    });
    if(score>bestScore){bestScore=score;best=key;}
  });
  return best||SCAPE_KEYS[Math.floor(Math.random()*SCAPE_KEYS.length)];
}

function startMusic(mood){
  if(musicPlaying||!audioCtx)return;
  musicPlaying=true;
  var key=pickScape(mood);
  activeScape=key;

  switch(key){
    case 'wind':scapeWind();break;
    case 'rain':scapeRain();break;
    case 'storm':scapeStorm();break;
    case 'forest':scapeForest();break;
    case 'ocean':scapeOcean();break;
    case 'night':scapeNight();break;
    case 'desolate':scapeDesolate();break;
    default:scapeWind();break;
  }
  log('Soundscape: '+SCAPES[key].label,'ok');
}

function stopMusic(){
  musicPlaying=false;
  // Fade out and cleanup all tracked nodes
  scapeNodes.forEach(function(n){
    try{
      if(n.gain) n.gain.linearRampToValueAtTime(0,audioCtx.currentTime+2);
      else n.stop(audioCtx.currentTime+2);
    }catch(e){try{n.stop();}catch(e2){}}
  });
  scapeTimers.forEach(function(t){clearInterval(t);});
  setTimeout(function(){scapeNodes=[];scapeTimers=[];},3000);
}

// Init audio on first click (browser autoplay policy)
var audioReady=false;
document.addEventListener('click',function(){
  if(!audioReady){initAudio();audioReady=true;}
},{once:false});

/* ============================
   FILM GRAIN (canvas noise)
   ============================ */
function updateGrain(){
  var w = grainCanvas.width = grainCanvas.offsetWidth/4|0 || 80;
  var h = grainCanvas.height = grainCanvas.offsetHeight/4|0 || 140;
  var img = grainCtx.createImageData(w,h);
  var d = img.data;
  for(var i=0;i<d.length;i+=4){
    var v = Math.random()*255|0;
    d[i]=d[i+1]=d[i+2]=v;
    d[i+3]=40;
  }
  grainCtx.putImageData(img,0,0);
}

/* ============================
   LIGHTNING
   ============================ */
function strikeLightning(){
  // Visual: bright white flash with flicker
  prevLightning.style.opacity=0.9;
  setTimeout(function(){prevLightning.style.opacity=0.1;},60);
  setTimeout(function(){prevLightning.style.opacity=0.7;},120);
  setTimeout(function(){prevLightning.style.opacity=0.05;},200);
  setTimeout(function(){prevLightning.style.opacity=0.4;},260);
  setTimeout(function(){prevLightning.style.opacity=0;},400);
  // Sound
  thunderCrack();
  // Camera jolt
  shakeIntensity = 4;
  setTimeout(function(){shakeIntensity=1;},800);
}

/* ============================
   MAIN ANIMATE LOOP
   ============================ */
function animate(){
  var t = Date.now()*0.001;
  breathPhase += 0.016;
  frameCount++;

  // BREATHING — visual overlay pulse
  var breathCycle = Math.sin(breathPhase*breathRate)*0.5+0.5;
  prevBreath.style.opacity = breathCycle*0.15;

  // Random drift targets (change every ~2s, eased)
  if(Math.random()<0.008){driftTX=rand(-2,2)*shakeIntensity;driftTY=rand(-1.5,1.5)*shakeIntensity;}
  driftX+=(driftTX-driftX)*0.02;
  driftY+=(driftTY-driftY)*0.02;

  // CAMERA: gentle drift + subtle micro-jitter (60% less than before)
  var sx = driftX + Math.sin(t*0.7)*1.5*shakeIntensity;
  var sy = driftY + Math.cos(t*0.9)*1.2*shakeIntensity;
  var jx = (Math.random()-0.5)*0.5*shakeIntensity;
  var jy = (Math.random()-0.5)*0.5*shakeIntensity;

  // Transition camera offset eases
  camOffX += (camTargetX-camOffX)*0.08;
  camOffY += (camTargetY-camOffY)*0.08;

  var posX = 50+sx+jx+camOffX;
  var posY = 50+sy+jy+camOffY;
  prevBg.style.backgroundPosition = posX+'% '+posY+'%';

  // ZOOM with proper easing
  if(!transLock && Math.random()<0.008){
    // Occasional dramatic zoom shift
    if(Math.random()<0.25){
      zoomTarget = rand(55,100); // ZOOM OUT — kaleidoscope reveal
      zoomSpeed = 0.02; // slow dramatic pullback
    } else {
      zoomTarget = rand(180,400);
      zoomSpeed = 0.03;
    }
  }
  baseZoom += (zoomTarget-baseZoom)*zoomSpeed;
  prevBg.style.backgroundSize = baseZoom+'%';

  // CONTRAST SHIFTS
  if(Math.random()<0.008){
    contrastValue = Math.floor(rand(-25,50));
    prevBg.style.filter = 'contrast('+(1+contrastValue/100)+') brightness('+(1+contrastValue/200)+')';
  }

  // RANDOM LIGHTNING (rare — 1 in ~500 frames)
  if(Math.random()<0.002 && !transLock){
    strikeLightning();
  }

  // FILM GRAIN (update every 3 frames for performance)
  if(frameCount%3===0) updateGrain();

  requestAnimationFrame(animate);
}

/* ============================
   TRANSITIONS — THE DRAMATIC ONES
   ============================ */

// 1. KALEIDOSCOPE ZOOM-OUT — the showstopper
//    Zooms WAY out so tiles are visible, HOLDS it, swaps image, then slowly pulls back in
function transKaleidoscope(cb){
  transLock=true;
  shakeIntensity=2;
  // Zoom out dramatically — let user SEE the tiles
  zoomTarget=rand(40,75);
  zoomSpeed=0.08; // fast pull-out
  // Wait for zoom to arrive, then hold
  setTimeout(function(){
    // We're at kaleidoscope view — hold it, let them see it
    shakeIntensity=0.5; // calm the camera so pattern is clear
  },600);
  setTimeout(function(){
    // NOW swap the image (no flash needed — the tiles ARE the spectacle)
    cb();
    shakeIntensity=1.5;
  },1200);
  setTimeout(function(){
    // Slowly pull back in
    zoomTarget=rand(220,380);
    zoomSpeed=0.015; // slow cinematic drift in
    shakeIntensity=1;
    transLock=false;
  },1600);
}

// 2. SHUTTER SLAM — mechanical camera shutter
function transShutter(cb){
  transLock=true;
  shakeIntensity=3; // camera jolt
  shutterT.style.transform='translateY(0)';
  shutterB.style.transform='translateY(0)';
  setTimeout(function(){
    cb();
    baseZoom=rand(160,340);
    zoomTarget=baseZoom;
    zoomSpeed=0.03;
  },150);
  setTimeout(function(){
    shutterT.style.transform='translateY(-100%)';
    shutterB.style.transform='translateY(100%)';
    shakeIntensity=1;
    transLock=false;
  },350);
}

// 3. WHIP PAN — violent horizontal whip
function transWhipPan(cb){
  transLock=true;
  shakeIntensity=3;
  // Whip left
  camTargetX=-80;
  prevBg.style.filter='blur(8px)';
  setTimeout(function(){
    cb();
    camOffX=80; camTargetX=0; // snap to right, ease back
    baseZoom=rand(180,300);
    zoomTarget=baseZoom;
    zoomSpeed=0.03;
    prevBg.style.filter='';
  },350);
  setTimeout(function(){
    shakeIntensity=1;
    transLock=false;
  },800);
}

// 4. DRIFT DOWN — vertical flutter with zoom-out
function transDrift(cb){
  transLock=true;
  // Pull out + drift down
  zoomTarget=rand(80,130);
  zoomSpeed=0.04;
  camTargetY=60;
  setTimeout(function(){
    cb();
    camOffY=-50; camTargetY=0;
    zoomTarget=rand(230,370);
    zoomSpeed=0.02;
  },700);
  setTimeout(function(){
    transLock=false;
  },1200);
}

// 5. LIGHTNING STRIKE — flash of white, new image emerges from darkness
function transLightningStrike(cb){
  transLock=true;
  strikeLightning();
  shakeIntensity=5;
  setTimeout(function(){
    cb();
    baseZoom=rand(200,350);
    zoomTarget=baseZoom;
    zoomSpeed=0.03;
  },250);
  setTimeout(function(){
    shakeIntensity=1;
    transLock=false;
  },900);
}

// 6. BREATH HOLD — everything goes still, then explosive reveal
function transBreathHold(cb){
  transLock=true;
  // Go eerily calm
  shakeIntensity=0.1;
  prevBreath.style.opacity=0.5; // heavy dark overlay
  zoomSpeed=0.005; // barely moving
  zoomTarget=baseZoom-20; // slight creep in
  setTimeout(function(){
    // EXPLOSIVE reveal
    shakeIntensity=6;
    prevBreath.style.opacity=0;
    cb();
    zoomTarget=rand(60,100); // zoom out fast
    zoomSpeed=0.12;
  },1000);
  setTimeout(function(){
    zoomTarget=rand(240,380);
    zoomSpeed=0.025;
    shakeIntensity=1;
    transLock=false;
  },1500);
}

// Weighted random transition picker (showcases best ones more)
var TRANS_TABLE = [
  {fn:transKaleidoscope, weight:3},
  {fn:transShutter, weight:2},
  {fn:transWhipPan, weight:2},
  {fn:transDrift, weight:2},
  {fn:transLightningStrike, weight:2},
  {fn:transBreathHold, weight:1}
];
var TRANS_TOTAL = 0;
TRANS_TABLE.forEach(function(t){TRANS_TOTAL+=t.weight;});

function doTransition(cb){
  var r = Math.random()*TRANS_TOTAL;
  var sum = 0;
  for(var i=0;i<TRANS_TABLE.length;i++){
    sum += TRANS_TABLE[i].weight;
    if(r<sum){TRANS_TABLE[i].fn(cb);return;}
  }
  TRANS_TABLE[0].fn(cb);
}

/* ============================
   IDLE CYCLE
   ============================ */
var idleSlideTimer = null;
function startIdleCycle(){
  var idx = Math.floor(Math.random()*stillsPool.length);
  setStill(stillsPool[idx]);
  idleSlideTimer = setInterval(function(){
    var nextIdx = Math.floor(Math.random()*stillsPool.length);
    doTransition(function(){
      setStill(stillsPool[nextIdx]);
    });
  },7000);
}
startIdleCycle();
animate();

/* ============================
   PLAY PREVIEW
   ============================ */
function playPreview(){
  if(!items.length){log('Nothing to play. Add items or click AUTO.','warn');return;}
  clearInterval(idleSlideTimer);
  isPlaying = true;
  playIndex = 0;
  document.getElementById('prevStatus').textContent = 'PLAYING';
  log('Playing preview: '+items.length+' slides','ok');

  // Start music: procedural or user-uploaded
  if(musicSrc==='procedural'){
    if(!audioReady){initAudio();audioReady=true;}
    startMusic(lastMood);
  } else if(musicSrc){
    var mp=document.getElementById('musicPlayer');
    mp.currentTime=0;mp.play().catch(function(){});
  }
  showSlide(0);
}

function showSlide(idx){
  if(idx >= items.length){
    isPlaying = false;
    document.getElementById('prevStatus').textContent = 'DONE';
    log('Preview finished','ok');
    stopMusic();
    document.getElementById('musicPlayer').pause();
    startIdleCycle();
    return;
  }

  playIndex = idx;
  var item = items[idx];

  doTransition(function(){
    if(item.type==='image' && item.src){
      setStill(item.src);
    } else {
      prevBg.style.backgroundImage='none';
      prevBg.style.backgroundColor='#0a0015';
      prevFx.style.backgroundImage='none';
    }
  });

  // Kinetic text — delayed so transition settles
  setTimeout(function(){
    if(item.text){
      ktext.textContent = item.text;
      ktext.className = 'ktext';
      setTimeout(function(){ktext.className='ktext show';},150);
    } else {
      ktext.className='ktext';
      ktext.textContent='';
    }
  },600);

  document.getElementById('prevStatus').textContent = (idx+1)+'/'+items.length;
  var titems = document.querySelectorAll('.titem');
  titems.forEach(function(t,i){t.classList.toggle('active',i===idx);});

  // 5-7 seconds per slide — transitions need room to breathe
  var duration = 5000+Math.random()*2000;
  playTimer = setTimeout(function(){showSlide(idx+1);},duration);
}

/* ============================
   STOP
   ============================ */
function stopPreview(){
  isPlaying = false;
  clearTimeout(playTimer);
  clearTimeout(textTimer);
  document.getElementById('prevStatus').textContent = 'STOPPED';
  stopMusic();
  document.getElementById('musicPlayer').pause();
  startIdleCycle();
}

document.getElementById('preview').addEventListener('click',function(){
  if(isPlaying) stopPreview();
});

/* ============================
   MUSIC GENERATOR (Sonauto)
   ============================ */
var mgPollTimer=null;

function toggleMusicPanel(){
  var p=document.getElementById('musicPanel');
  p.style.display=(p.style.display==='none')?'block':'none';
  if(p.style.display==='block') p.scrollIntoView({behavior:'smooth',block:'nearest'});
}

function startMusicGen(){
  var btn=document.getElementById('mgGenBtn');
  var status=document.getElementById('mgStatus');
  var result=document.getElementById('mgResult');
  btn.disabled=true;btn.textContent='GENERATING...';
  status.style.display='block';status.textContent='Sending to AI...';
  result.style.display='none';

  var fd=new FormData();
  fd.append('musicgen_action','generate');
  fd.append('mg_title',document.getElementById('mgTitle').value);
  fd.append('mg_styles',document.getElementById('mgStyles').value);
  fd.append('mg_instrumental',document.getElementById('mgInstrumental').checked?'1':'');

  fetch(window.location.pathname,{method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
      if(!d.ok||!d.task_id){
        status.textContent='Error: '+(d.error||'No task ID');
        btn.disabled=false;btn.textContent='GENERATE TRACK';
        return;
      }
      status.textContent='Generating your track... this takes 30-60s';
      pollMusicGen(d.task_id,0);
    })
    .catch(function(e){
      status.textContent='Network error: '+e;
      btn.disabled=false;btn.textContent='GENERATE TRACK';
    });
}

function pollMusicGen(taskId,attempt){
  if(attempt>=45){
    document.getElementById('mgStatus').textContent='Timeout — took too long. Try again.';
    document.getElementById('mgGenBtn').disabled=false;
    document.getElementById('mgGenBtn').textContent='GENERATE TRACK';
    return;
  }
  var dots='';for(var i=0;i<(attempt%4);i++)dots+='.';
  document.getElementById('mgStatus').textContent='Generating'+dots+' ('+Math.round(attempt*2)+'s)';

  var fd=new FormData();
  fd.append('musicgen_action','poll');
  fd.append('task_id',taskId);

  fetch(window.location.pathname,{method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
      if(!d.ok){
        document.getElementById('mgStatus').textContent='Poll error: '+(d.error||'Unknown');
        document.getElementById('mgGenBtn').disabled=false;
        document.getElementById('mgGenBtn').textContent='GENERATE TRACK';
        return;
      }
      if(d.status==='SUCCESS'&&d.song_url){
        document.getElementById('mgStatus').textContent='Track ready!';
        document.getElementById('mgAudio').src=d.song_url;
        document.getElementById('mgDownload').href=d.song_url;
        document.getElementById('mgResult').style.display='block';
        document.getElementById('mgGenBtn').disabled=false;
        document.getElementById('mgGenBtn').textContent='GENERATE ANOTHER';
        log('AI Music track generated','ok');
        return;
      }
      if(d.status==='FAILURE'){
        document.getElementById('mgStatus').textContent='Failed: '+(d.error_msg||'Unknown error');
        document.getElementById('mgGenBtn').disabled=false;
        document.getElementById('mgGenBtn').textContent='GENERATE TRACK';
        return;
      }
      // Still processing — poll again in 2s
      mgPollTimer=setTimeout(function(){pollMusicGen(taskId,attempt+1);},2000);
    })
    .catch(function(e){
      document.getElementById('mgStatus').textContent='Poll network error';
      document.getElementById('mgGenBtn').disabled=false;
      document.getElementById('mgGenBtn').textContent='GENERATE TRACK';
    });
}

function useMusicTrack(){
  var url=document.getElementById('mgAudio').src;
  if(!url)return;
  musicSrc=url;
  var mp=document.getElementById('musicPlayer');
  mp.src=url;mp.style.display='block';
  document.getElementById('musicInfo').textContent='AI Generated Track';
  toggleMusicPanel();
  log('AI track set as video music','ok');
}

/* ============================
   VIDEO EXPORT ENGINE
   Intro → Slides → Outro
   ============================ */
var exporting=false;
var exportCanvas=null;
var exportCtx=null;
var exportW=1080;
var exportH=1920;

// Pre-load logo
var sfLogo=new Image();
sfLogo.crossOrigin='anonymous';
sfLogo.src='shortfactory-logo.webp';

function exportVideo(){
  if(exporting){log('Export already in progress','warn');return;}
  if(!items.length){log('Nothing to export — add images first','warn');return;}
  if(!spendToken())return; // Check tokens
  exporting=true;
  log('Starting video export...','ok');

  var btn=document.getElementById('exportBtn');
  btn.disabled=true;btn.textContent='EXPORTING...';
  document.getElementById('exportProgress').style.display='block';
  setExportProgress(0,'Preparing...');

  // Create canvas
  if(!exportCanvas){
    exportCanvas=document.createElement('canvas');
    exportCanvas.width=exportW;
    exportCanvas.height=exportH;
    exportCtx=exportCanvas.getContext('2d');
  }

  // Load all slide images first
  var slideImages=[];
  var loaded=0;
  var toLoad=items.filter(function(it){return it.type==='image'&&it.src;});
  if(!toLoad.length){log('No images to export','warn');resetExport();return;}

  toLoad.forEach(function(item,i){
    var img=new Image();
    img.crossOrigin='anonymous';
    img.onload=function(){
      slideImages[i]={img:img,text:item.text||''};
      loaded++;
      if(loaded===toLoad.length) startRecording(slideImages);
    };
    img.onerror=function(){
      slideImages[i]={img:null,text:item.text||''};
      loaded++;
      if(loaded===toLoad.length) startRecording(slideImages);
    };
    img.src=item.src;
  });
}

function startRecording(slideImages){
  var stream=exportCanvas.captureStream(30);

  // Add audio if available
  var audioEl=document.getElementById('musicPlayer');
  var hasAudio=musicSrc&&audioEl&&audioEl.src;
  if(hasAudio){
    try{
      var aCtx=new(window.AudioContext||window.webkitAudioContext)();
      var source=aCtx.createMediaElementSource(audioEl);
      var dest=aCtx.createMediaStreamDestination();
      source.connect(dest);
      source.connect(aCtx.destination);
      dest.stream.getAudioTracks().forEach(function(t){stream.addTrack(t);});
      audioEl.currentTime=0;audioEl.play();
    }catch(e){hasAudio=false;}
  }

  var mimeType='video/webm;codecs=vp9';
  if(!MediaRecorder.isTypeSupported(mimeType)){
    mimeType='video/webm;codecs=vp8';
    if(!MediaRecorder.isTypeSupported(mimeType)) mimeType='video/webm';
  }

  var recorder=new MediaRecorder(stream,{mimeType:mimeType,videoBitsPerSecond:8000000});
  var chunks=[];
  recorder.ondataavailable=function(e){if(e.data.size>0)chunks.push(e.data);};
  recorder.onstop=function(){
    var blob=new Blob(chunks,{type:mimeType});
    lastExportBlob=blob; // Save for YouTube upload
    var url=URL.createObjectURL(blob);
    var a=document.createElement('a');
    a.href=url;
    a.download='shortfactory_'+Date.now()+'.webm';
    a.click();
    // Don't revoke — keep for YouTube upload
    log('Video exported! Downloading...','ok');
    if(hasAudio){audioEl.pause();audioEl.currentTime=0;}
    resetExport();
    // Show YouTube publish option
    document.getElementById('ytPublish').style.display='block';
    document.getElementById('ytTitle').value='ShortFactory Imaginator';
    document.getElementById('ytTitle2').value='ShortFactory Imaginator';
    // Enable Save Video to Drive button
    document.getElementById('saveVideoBtn').disabled=false;
    log('Ready to publish to YouTube Shorts or save to Drive!','ok');
  };
  recorder.start();

  // Run the sequence: intro → slides → outro
  runExportSequence(slideImages,recorder,hasAudio);
}

function runExportSequence(slideImages,recorder,hasAudio){
  var fps=30;
  var frame=0;

  // Timing (in frames)
  var introFrames=fps*4;      // 4 second intro
  var slideFrames=fps*6;      // 6 seconds per slide
  var transFrames=fps*1;      // 1 second transition between slides
  var outroFrames=fps*4;      // 4 second outro
  var totalSlideFrames=slideImages.length*(slideFrames+transFrames);
  var totalFrames=introFrames+totalSlideFrames+outroFrames;

  setExportProgress(5,'Rendering intro...');

  function tick(){
    if(frame>=totalFrames){
      recorder.stop();
      setExportProgress(100,'Done!');
      return;
    }

    var pct=Math.round((frame/totalFrames)*100);

    if(frame<introFrames){
      // ── INTRO PHASE ──
      drawIntro(frame,introFrames);
      if(frame===0)setExportProgress(5,'Rendering intro...');
    }
    else if(frame<introFrames+totalSlideFrames){
      // ── SLIDES PHASE ──
      var sf=frame-introFrames;
      var perSlide=slideFrames+transFrames;
      var slideIdx=Math.floor(sf/perSlide);
      var inSlide=sf%perSlide;

      if(slideIdx>=slideImages.length){slideIdx=slideImages.length-1;inSlide=perSlide-1;}

      if(inSlide<slideFrames){
        // Show slide with Ken Burns
        drawSlide(slideImages[slideIdx],inSlide,slideFrames);
      } else {
        // Transition: crossfade to next
        var tProg=(inSlide-slideFrames)/transFrames;
        var nextIdx=Math.min(slideIdx+1,slideImages.length-1);
        drawSlide(slideImages[slideIdx],slideFrames-1,slideFrames);
        exportCtx.globalAlpha=tProg;
        drawSlide(slideImages[nextIdx],0,slideFrames);
        exportCtx.globalAlpha=1;
      }
      setExportProgress(10+Math.round(pct*0.8),'Rendering slide '+(slideIdx+1)+'/'+slideImages.length);
    }
    else{
      // ── OUTRO PHASE ──
      var of2=frame-(introFrames+totalSlideFrames);
      drawOutro(of2,outroFrames);
      setExportProgress(90+Math.round((of2/outroFrames)*10),'Rendering outro...');
    }

    frame++;
    requestAnimationFrame(tick);
  }
  tick();
}

/* ── DRAW INTRO: White page, logo, marketing text, fade to black ── */
function drawIntro(f,total){
  var ctx=exportCtx;
  var w=exportW,h=exportH;
  var progress=f/total;

  // White background
  ctx.fillStyle='#ffffff';
  ctx.fillRect(0,0,w,h);

  // Fade stages: 0-0.1 fade in, 0.1-0.8 hold, 0.8-1.0 fade to black
  var alpha=1;
  if(progress<0.1) alpha=progress/0.1;
  else if(progress>0.8) alpha=1-((progress-0.8)/0.2);

  ctx.globalAlpha=alpha;

  // Logo centered (draw at ~200px)
  if(sfLogo.complete&&sfLogo.naturalWidth>0){
    var logoSize=200;
    var lx=(w-logoSize)/2;
    var ly=h*0.18;
    ctx.drawImage(sfLogo,lx,ly,logoSize,logoSize);
  }

  // Brand name
  ctx.fillStyle='#111';
  ctx.textAlign='center';
  ctx.font='900 72px Orbitron, sans-serif';
  ctx.fillText('SHORTF\u25B2CTORY',w/2,h*0.42);

  // "Created FREE at"
  ctx.font='600 36px Poppins, sans-serif';
  ctx.fillStyle='#333';
  ctx.fillText('Created FREE at',w/2,h*0.48);

  // URL
  ctx.font='900 44px Orbitron, sans-serif';
  ctx.fillStyle='#cc0000';
  ctx.fillText('shortfactory.shop',w/2,h*0.53);

  // Divider line
  ctx.strokeStyle='#ddd';
  ctx.lineWidth=2;
  ctx.beginPath();
  ctx.moveTo(w*0.2,h*0.57);
  ctx.lineTo(w*0.8,h*0.57);
  ctx.stroke();

  // Marketing lines
  ctx.fillStyle='#444';
  ctx.font='400 32px Poppins, sans-serif';
  ctx.fillText('Have fun making your own entertainment',w/2,h*0.63);

  ctx.font='600 30px Poppins, sans-serif';
  ctx.fillStyle='#222';
  ctx.fillText('or earn instant pocketmoney',w/2,h*0.68);
  ctx.fillText('making adverts for your',w/2,h*0.72);
  ctx.fillText('favourite brands',w/2,h*0.76);

  // Small tagline
  ctx.font='400 24px Poppins, sans-serif';
  ctx.fillStyle='#999';
  ctx.fillText('The best free YouTube Shorts editor',w/2,h*0.84);
  ctx.fillText('No sign-up required',w/2,h*0.88);

  ctx.globalAlpha=1;

  // Fade to black at end
  if(progress>0.8){
    var blackAlpha=(progress-0.8)/0.2;
    ctx.fillStyle='rgba(0,0,0,'+blackAlpha+')';
    ctx.fillRect(0,0,w,h);
  }
}

/* ── DRAW SLIDE: Ken Burns zoom + drift effect ── */
function drawSlide(slideData,f,total){
  var ctx=exportCtx;
  var w=exportW,h=exportH;
  var img=slideData.img;

  // Black background
  ctx.fillStyle='#000';
  ctx.fillRect(0,0,w,h);

  if(!img)return;

  var progress=f/total;

  // Ken Burns: slow zoom in + drift
  var startZoom=1.1;
  var endZoom=1.35;
  var zoom=startZoom+(endZoom-startZoom)*progress;
  var driftX=Math.sin(progress*Math.PI*2)*30;
  var driftY=Math.cos(progress*Math.PI*1.5)*20;

  // Calculate draw dimensions to cover canvas (9:16)
  var iw=img.naturalWidth;
  var ih=img.naturalHeight;
  var scale=Math.max(w/iw,h/ih)*zoom;
  var dw=iw*scale;
  var dh=ih*scale;
  var dx=(w-dw)/2+driftX;
  var dy=(h-dh)/2+driftY;

  ctx.drawImage(img,dx,dy,dw,dh);

  // Vignette overlay
  var grad=ctx.createRadialGradient(w/2,h/2,w*0.3,w/2,h/2,w*0.9);
  grad.addColorStop(0,'rgba(0,0,0,0)');
  grad.addColorStop(1,'rgba(0,0,0,0.6)');
  ctx.fillStyle=grad;
  ctx.fillRect(0,0,w,h);

  // Film grain (subtle noise)
  if(f%3===0){
    var gd=ctx.getImageData(0,0,w,h);
    var px=gd.data;
    for(var i=0;i<px.length;i+=16){
      var n=(Math.random()-0.5)*18;
      px[i]+=n;px[i+1]+=n;px[i+2]+=n;
    }
    ctx.putImageData(gd,0,0);
  }

  // Kinetic text
  if(slideData.text){
    var textAlpha=1;
    if(progress<0.15) textAlpha=progress/0.15;
    else if(progress>0.85) textAlpha=1-((progress-0.85)/0.15);
    ctx.globalAlpha=textAlpha;
    ctx.textAlign='center';
    ctx.font='900 56px Orbitron, sans-serif';
    ctx.fillStyle='#fff';
    ctx.shadowColor='rgba(255,215,0,0.9)';
    ctx.shadowBlur=30;
    ctx.fillText(slideData.text,w/2,h*0.82);
    ctx.shadowBlur=0;
    ctx.globalAlpha=1;
  }

  // Watermark
  ctx.textAlign='right';
  ctx.font='600 20px Orbitron, sans-serif';
  ctx.fillStyle='rgba(255,215,0,0.35)';
  ctx.fillText('SHORTF\u25B2CTORY',w-30,h-30);
  ctx.textAlign='center';
}

/* ── DRAW OUTRO: Kinetic PRO membership card ── */
function drawOutro(f,total){
  var ctx=exportCtx;
  var w=exportW,h=exportH;
  var progress=f/total;

  // Dark purple gradient background
  var bg=ctx.createLinearGradient(0,0,w*0.5,h);
  bg.addColorStop(0,'#1a0a2e');
  bg.addColorStop(0.4,'#0d0520');
  bg.addColorStop(1,'#000000');
  ctx.fillStyle=bg;
  ctx.fillRect(0,0,w,h);

  // Fade in
  var alpha=1;
  if(progress<0.15) alpha=progress/0.15;
  else if(progress>0.85) alpha=1-((progress-0.85)/0.15);
  ctx.globalAlpha=alpha;

  ctx.textAlign='center';

  // Bolt
  ctx.font='120px serif';
  ctx.fillStyle='#FFD700';
  ctx.shadowColor='rgba(255,215,0,0.6)';
  ctx.shadowBlur=40;
  ctx.fillText('\u26A1',w/2,h*0.22);
  ctx.shadowBlur=0;

  // KINETIC PRO
  ctx.font='900 64px Orbitron, sans-serif';
  ctx.fillStyle='#FFD700';
  ctx.fillText('KINETIC PRO',w/2,h*0.32);

  // UNLOCK EVERYTHING
  ctx.font='400 30px Poppins, sans-serif';
  ctx.fillStyle='#888';
  ctx.fillText('UNLOCK EVERYTHING',w/2,h*0.36);

  // Price
  ctx.font='900 100px Poppins, sans-serif';
  ctx.fillStyle='#fff';
  ctx.fillText('\u00A329/mo',w/2,h*0.47);

  ctx.font='400 26px Poppins, sans-serif';
  ctx.fillStyle='#666';
  ctx.fillText('Cancel anytime',w/2,h*0.50);

  // Features list
  var feats=['All 25+ animation patterns','Export without watermark','AI auto-timing','Batch processing','Commercial license','Priority support'];
  ctx.font='400 28px Poppins, sans-serif';
  ctx.textAlign='left';
  var startY=h*0.56;
  feats.forEach(function(feat,i){
    var fy=startY+i*55;
    ctx.fillStyle='#FFD700';
    ctx.font='900 28px Poppins, sans-serif';
    ctx.fillText('\u2713',w*0.22,fy);
    ctx.fillStyle='#ccc';
    ctx.font='400 28px Poppins, sans-serif';
    ctx.fillText(feat,w*0.28,fy);
  });

  // CTA button
  ctx.textAlign='center';
  var btnY=h*0.82;
  var btnW=500;
  var btnH=80;
  var btnGrad=ctx.createLinearGradient(w/2-btnW/2,btnY,w/2+btnW/2,btnY);
  btnGrad.addColorStop(0,'#FFD700');
  btnGrad.addColorStop(1,'#ff8c00');
  roundRect(ctx,(w-btnW)/2,btnY-btnH/2,btnW,btnH,40);
  ctx.fillStyle=btnGrad;
  ctx.fill();
  ctx.font='900 32px Orbitron, sans-serif';
  ctx.fillStyle='#000';
  ctx.fillText('shortfactory.shop/checkout',w/2,btnY+12);

  // URL at bottom
  ctx.font='400 24px Poppins, sans-serif';
  ctx.fillStyle='rgba(255,255,255,0.4)';
  ctx.fillText('shortfactory.shop',w/2,h*0.94);

  ctx.globalAlpha=1;
}

function roundRect(ctx,x,y,w,h,r){
  ctx.beginPath();
  ctx.moveTo(x+r,y);
  ctx.lineTo(x+w-r,y);
  ctx.quadraticCurveTo(x+w,y,x+w,y+r);
  ctx.lineTo(x+w,y+h-r);
  ctx.quadraticCurveTo(x+w,y+h,x+w-r,y+h);
  ctx.lineTo(x+r,y+h);
  ctx.quadraticCurveTo(x,y+h,x,y+h-r);
  ctx.lineTo(x,y+r);
  ctx.quadraticCurveTo(x,y,x+r,y);
  ctx.closePath();
}

function setExportProgress(pct,msg){
  document.getElementById('exportBar').style.width=pct+'%';
  document.getElementById('exportStatus').textContent=msg;
}

var lastExportBlob=null; // Store the exported video blob for YouTube upload

function resetExport(){
  exporting=false;
  var btn=document.getElementById('exportBtn');
  btn.disabled=false;btn.textContent='EXPORT VIDEO';
  setTimeout(function(){document.getElementById('exportProgress').style.display='none';},3000);
}

/* ============================
   GOOGLE AUTH (unified: Drive + YouTube + Pay)
   ============================ */
var G_CLIENT_ID='246057462897-mui96hjeuk9abvlkgvvqdfdeiknbmojb.apps.googleusercontent.com';
var G_SCOPES='https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/youtube.upload https://www.googleapis.com/auth/youtube.readonly';
var gAccessToken=null;
var gUser=null;
var sfDriveFolderId=null;

function googleSignIn(){
  if(typeof google==='undefined'||!google.accounts){
    log('Google loading — try again in a sec','warn');
    return;
  }
  var client=google.accounts.oauth2.initTokenClient({
    client_id:G_CLIENT_ID,
    scope:G_SCOPES,
    callback:handleGoogleAuth
  });
  client.requestAccessToken();
}

function handleGoogleAuth(response){
  if(response.error){
    log('Google auth failed: '+response.error,'err');
    return;
  }
  gAccessToken=response.access_token;
  log('Google signed in! Token expires in '+response.expires_in+'s','ok');

  // Fetch profile
  fetch('https://www.googleapis.com/oauth2/v2/userinfo',{
    headers:{'Authorization':'Bearer '+gAccessToken}
  }).then(function(r){return r.json();}).then(function(d){
    gUser=d;
    localStorage.setItem('sf_google_name',d.name||'');
    localStorage.setItem('sf_google_email',d.email||'');
    localStorage.setItem('sf_google_avatar',d.picture||'');

    // Update topbar
    document.getElementById('gSignIn').style.display='none';
    document.getElementById('gUserBar').style.display='flex';
    document.getElementById('gAvatar').src=d.picture||'';
    document.getElementById('gName').textContent=d.given_name||d.name||d.email;

    // Show Drive card
    document.getElementById('driveCard').style.display='block';

    // Pre-fill YouTube section if visible
    document.getElementById('ytEmail').textContent=d.email||'Linked';
    document.getElementById('ytNotLinked').style.display='none';
    document.getElementById('ytLinked').style.display='block';

    log('Welcome, '+d.name+'!','ok');
    // Grant signup tokens + daily claim
    grantSignupTokens();
    var today=new Date().toISOString().slice(0,10);
    if(localStorage.getItem('sf_daily_claim')!==today){
      localStorage.setItem('sf_daily_claim',today);
      addTokens(1);
      log('Daily login bonus: +1 SF token','ok');
    }
  });

  // Find or create ShortFactory Drive folder
  initDriveFolder();
}

function initDriveFolder(){
  fetch('https://www.googleapis.com/drive/v3/files?q=name%3D%27ShortFactory%27+and+mimeType%3D%27application/vnd.google-apps.folder%27+and+trashed%3Dfalse&fields=files(id)',{
    headers:{'Authorization':'Bearer '+gAccessToken}
  }).then(function(r){return r.json();}).then(function(data){
    if(data.files&&data.files.length>0){
      sfDriveFolderId=data.files[0].id;
      log('Drive folder ready: '+sfDriveFolderId,'ok');
    } else {
      // Create it
      fetch('https://www.googleapis.com/drive/v3/files',{
        method:'POST',
        headers:{'Authorization':'Bearer '+gAccessToken,'Content-Type':'application/json'},
        body:JSON.stringify({name:'ShortFactory',mimeType:'application/vnd.google-apps.folder',description:'ShortFactory Imaginator projects and videos'})
      }).then(function(r){return r.json();}).then(function(folder){
        sfDriveFolderId=folder.id;
        log('Drive folder created: '+sfDriveFolderId,'ok');
      });
    }
  });
}

// Alias for YouTube section backward compat
function ytAuth(){googleSignIn();}

/* ── Save Project to Drive ── */
function saveProjectToDrive(){
  if(!gAccessToken){log('Sign in with Google first','warn');return;}
  if(!sfDriveFolderId){log('Drive folder not ready — try again','warn');return;}
  var status=document.getElementById('driveSaveStatus');
  status.textContent='Saving project...';

  var project={
    app:'ShortFactory Imaginator',
    version:1,
    created:new Date().toISOString(),
    user:localStorage.getItem('sf_google_email')||'',
    slides:items.map(function(it){return{type:it.type,text:it.text||'',hasSrc:!!it.src};}),
    musicSrc:musicSrc||null,
    slideCount:items.length
  };

  var filename='SF_Project_'+new Date().toISOString().slice(0,10)+'_'+Date.now()+'.json';
  var meta={name:filename,mimeType:'application/json',parents:[sfDriveFolderId]};
  var form=new FormData();
  form.append('metadata',new Blob([JSON.stringify(meta)],{type:'application/json'}));
  form.append('file',new Blob([JSON.stringify(project,null,2)],{type:'application/json'}));

  fetch('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,webViewLink',{
    method:'POST',headers:{'Authorization':'Bearer '+gAccessToken},body:form
  }).then(function(r){
    if(!r.ok)throw new Error('HTTP '+r.status);
    return r.json();
  }).then(function(file){
    status.innerHTML='Saved! <a href="'+(file.webViewLink||'#')+'" target="_blank" style="color:#4285F4;">Open in Drive</a>';
    log('Project saved to Drive: '+file.name,'ok');
  }).catch(function(e){
    status.textContent='Save failed: '+e.message;
    log('Drive save error: '+e.message,'err');
  });
}

/* ── Save Exported Video to Drive ── */
function saveVideoToDrive(){
  if(!gAccessToken){log('Sign in with Google first','warn');return;}
  if(!sfDriveFolderId){log('Drive folder not ready','warn');return;}
  if(!lastExportBlob){log('Export a video first','warn');return;}
  var status=document.getElementById('driveSaveStatus');
  status.textContent='Uploading video to Drive...';

  var filename='SF_Video_'+new Date().toISOString().slice(0,10)+'_'+Date.now()+'.webm';
  var meta={name:filename,mimeType:lastExportBlob.type,parents:[sfDriveFolderId]};

  // Resumable upload for large files
  fetch('https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable&fields=id,name,webViewLink',{
    method:'POST',
    headers:{'Authorization':'Bearer '+gAccessToken,'Content-Type':'application/json','X-Upload-Content-Length':lastExportBlob.size,'X-Upload-Content-Type':lastExportBlob.type},
    body:JSON.stringify(meta)
  }).then(function(r){
    if(!r.ok)throw new Error('Init failed: '+r.status);
    return r.headers.get('Location');
  }).then(function(uploadUrl){
    var xhr=new XMLHttpRequest();
    xhr.open('PUT',uploadUrl);
    xhr.setRequestHeader('Content-Type',lastExportBlob.type);
    xhr.upload.onprogress=function(e){
      if(e.lengthComputable){
        status.textContent='Uploading... '+Math.round((e.loaded/e.total)*100)+'%';
      }
    };
    xhr.onload=function(){
      if(xhr.status>=200&&xhr.status<300){
        var file=JSON.parse(xhr.responseText);
        status.innerHTML='Video saved! <a href="'+(file.webViewLink||'#')+'" target="_blank" style="color:#4285F4;">Open in Drive</a>';
        log('Video saved to Drive: '+file.name,'ok');
      } else {
        status.textContent='Upload failed: '+xhr.status;
        log('Drive video upload failed: '+xhr.status,'err');
      }
    };
    xhr.onerror=function(){status.textContent='Network error';};
    xhr.send(lastExportBlob);
  }).catch(function(e){
    status.textContent='Failed: '+e.message;
    log('Drive upload error: '+e.message,'err');
  });
}

function ytUpload(){
  if(!gAccessToken){log('Link YouTube account first','warn');return;}
  if(!lastExportBlob){log('Export a video first','warn');return;}

  // Check duration — must be under 3 minutes (180s). Our exports are well under.
  var totalSlides=items.filter(function(it){return it.type==='image'&&it.src;}).length;
  var estDuration=4+totalSlides*7+4; // intro + slides + outro
  if(estDuration>180){
    log('Video too long for Shorts ('+estDuration+'s). Max 3 minutes. Remove some slides.','warn');
    return;
  }

  var title=document.getElementById('ytTitle2').value.trim()||'Made with ShortFactory Imaginator';
  var description='Created FREE at shortfactory.shop — the best YouTube Shorts editor.\n\n'
    +'Have fun making your own entertainment, or earn instant pocketmoney making adverts for your favourite brands.\n\n'
    +'#Shorts #ShortFactory #Imaginator #YouTubeShorts';

  var btn=document.getElementById('ytUploadBtn');
  btn.disabled=true;btn.textContent='UPLOADING...';
  document.getElementById('ytUploadProgress').style.display='block';
  document.getElementById('ytUploadStatus').textContent='Preparing upload...';
  document.getElementById('ytUploadBar').style.width='10%';

  // YouTube resumable upload
  var metadata={
    snippet:{
      title:title,
      description:description,
      tags:['Shorts','ShortFactory','Imaginator','YouTube Shorts'],
      categoryId:'22'
    },
    status:{
      privacyStatus:'public',
      selfDeclaredMadeForKids:false
    }
  };

  // Step 1: Init resumable upload
  fetch('https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status',{
    method:'POST',
    headers:{
      'Authorization':'Bearer '+gAccessToken,
      'Content-Type':'application/json; charset=UTF-8',
      'X-Upload-Content-Length':lastExportBlob.size,
      'X-Upload-Content-Type':lastExportBlob.type
    },
    body:JSON.stringify(metadata)
  }).then(function(r){
    if(!r.ok) throw new Error('Init failed: '+r.status);
    var uploadUrl=r.headers.get('Location');
    if(!uploadUrl) throw new Error('No upload URL returned');
    document.getElementById('ytUploadStatus').textContent='Uploading video...';
    document.getElementById('ytUploadBar').style.width='30%';

    // Step 2: Upload the video blob
    return new Promise(function(resolve,reject){
      var xhr=new XMLHttpRequest();
      xhr.open('PUT',uploadUrl);
      xhr.setRequestHeader('Content-Type',lastExportBlob.type);
      xhr.upload.onprogress=function(e){
        if(e.lengthComputable){
          var pct=30+Math.round((e.loaded/e.total)*60);
          document.getElementById('ytUploadBar').style.width=pct+'%';
          document.getElementById('ytUploadStatus').textContent='Uploading... '+Math.round((e.loaded/e.total)*100)+'%';
        }
      };
      xhr.onload=function(){
        if(xhr.status>=200&&xhr.status<300){
          resolve(JSON.parse(xhr.responseText));
        } else {
          reject(new Error('Upload failed: '+xhr.status));
        }
      };
      xhr.onerror=function(){reject(new Error('Network error'));};
      xhr.send(lastExportBlob);
    });
  }).then(function(video){
    var videoId=video.id;
    var url='https://www.youtube.com/shorts/'+videoId;
    document.getElementById('ytUploadBar').style.width='100%';
    document.getElementById('ytUploadStatus').textContent='Published!';
    document.getElementById('ytSuccess').style.display='block';
    document.getElementById('ytLink').href=url;
    document.getElementById('ytLink').textContent=url;
    btn.textContent='PUBLISHED!';
    log('YouTube Short published: '+url,'ok');
  }).catch(function(err){
    log('YouTube upload error: '+err.message,'err');
    document.getElementById('ytUploadStatus').textContent='Error: '+err.message;
    // Token might be expired — offer re-auth
    if(err.message.indexOf('401')>-1||err.message.indexOf('403')>-1){
      document.getElementById('ytUploadStatus').textContent='Session expired — re-link your account';
      document.getElementById('ytNotLinked').style.display='block';
      document.getElementById('ytLinked').style.display='none';
      gAccessToken=null;
    }
    btn.disabled=false;btn.textContent='PUBLISH SHORT';
  });
}

// Auto-restore Google profile from localStorage (visual only — token needs re-auth)
(function(){
  var name=localStorage.getItem('sf_google_name');
  var avatar=localStorage.getItem('sf_google_avatar');
  var email=localStorage.getItem('sf_google_email');
  if(name&&avatar){
    document.getElementById('gSignIn').style.display='none';
    document.getElementById('gUserBar').style.display='flex';
    document.getElementById('gAvatar').src=avatar;
    document.getElementById('gName').textContent=name.split(' ')[0]||name;
  }
})();

/* ============================
   SF TOKEN SYSTEM
   ============================ */
function getTokens(){return parseInt(localStorage.getItem('sf_tokens')||'0');}
function setTokens(n){localStorage.setItem('sf_tokens',Math.max(0,n));updateTokenUI();}
function addTokens(n){setTokens(getTokens()+n);}
function spendToken(){
  var t=getTokens();
  // Admin/PRO bypass
  if(localStorage.getItem('admin_authenticated')==='true')return true;
  if(localStorage.getItem('sf_pro')==='true')return true;
  if(t<=0){
    log('No SF tokens left! Earn more or go PRO.','warn');
    showTokenPanel();
    return false;
  }
  setTokens(t-1);
  log('Used 1 SF token. Remaining: '+(t-1),'ok');
  return true;
}

function updateTokenUI(){
  var t=getTokens();
  var el=document.getElementById('tokenCount');
  var badge=document.getElementById('tokenBadge');
  var big=document.getElementById('tokenBig');
  if(el)el.textContent=t;
  if(big)big.textContent=t;
  if(badge){
    if(t<=0)badge.classList.add('token-zero');
    else badge.classList.remove('token-zero');
  }
}

function showTokenPanel(){
  updateTokenUI();
  document.getElementById('tokenPanel').style.display='block';
  document.getElementById('tokenOverlay').style.display='block';
}
function hideTokenPanel(){
  document.getElementById('tokenPanel').style.display='none';
  document.getElementById('tokenOverlay').style.display='none';
}

// Grant 5 tokens on first sign-in
function grantSignupTokens(){
  if(!localStorage.getItem('sf_tokens_granted')){
    localStorage.setItem('sf_tokens_granted','true');
    addTokens(5);
    log('Welcome bonus: +5 SF tokens!','ok');
  }
}

// Daily login bonus
function claimDailyToken(){
  var today=new Date().toISOString().slice(0,10);
  var last=localStorage.getItem('sf_daily_claim');
  if(last===today){
    alert('Already claimed today! Come back tomorrow.');
    return;
  }
  localStorage.setItem('sf_daily_claim',today);
  addTokens(1);
  log('Daily token claimed! +1 SF token','ok');
  alert('+1 SF Token! Balance: '+getTokens());
}

// Referral bonus (called when a referred user signs in)
function grantReferralTokens(){
  addTokens(3);
  log('Referral bonus! +3 SF tokens','ok');
}

// Init tokens on page load
updateTokenUI();

// Auto daily claim check on sign-in
</script>
</body>
</html>
