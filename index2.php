<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<title>IMAGINATOR - ShortFactory</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<script src="https://accounts.google.com/gsi/client" async defer></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Poppins',sans-serif;background:#0a0a0a;color:#fff;min-height:100vh;overflow-x:hidden;}

/* ── HERO: Phone centred at top ── */
.hero{display:flex;flex-direction:column;align-items:center;padding:30px 20px 0;background:linear-gradient(180deg,#1a0a2e 0%,#0a0a0a 60%);}
.hero-title{font-family:'Orbitron',sans-serif;font-size:1.6rem;color:#ff4444;letter-spacing:3px;text-align:center;margin-bottom:4px;text-shadow:0 0 20px rgba(255,68,68,0.4);}
.hero-sub{font-size:0.85rem;color:#888;text-align:center;margin-bottom:24px;}

/* Phone frame */
.phone{position:relative;width:280px;height:560px;background:#000;border-radius:40px;border:8px solid #1a1a1a;box-shadow:0 20px 60px rgba(0,0,0,0.8),0 0 0 2px #333,inset 0 0 0 1px #222;overflow:hidden;flex-shrink:0;}
.phone-notch{position:absolute;top:0;left:50%;transform:translateX(-50%);width:120px;height:24px;background:#000;border-radius:0 0 16px 16px;z-index:10;border:1px solid #222;border-top:0;}
.phone-screen{width:100%;height:100%;position:relative;overflow:hidden;background:#000;}

/* Demo ribbon */
.demo-ribbon{position:absolute;top:28px;right:-32px;background:linear-gradient(135deg,#ff4444,#cc0000);color:#fff;padding:5px 40px;font-family:'Orbitron',sans-serif;font-size:0.55rem;font-weight:900;letter-spacing:2px;transform:rotate(45deg);z-index:30;box-shadow:0 2px 8px rgba(0,0,0,0.5);border:1px solid rgba(255,255,255,0.2);transition:opacity 0.5s;}

/* Intro screen inside phone */
#introScreen{position:absolute;inset:0;z-index:20;background:linear-gradient(160deg,#cc0000 0%,#8b0000 40%,#1a0000 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:18px 14px;text-align:center;transition:opacity 0.5s;}
.intro-logo{font-family:'Orbitron',sans-serif;font-size:0.7rem;color:rgba(255,255,255,0.25);letter-spacing:4px;margin-bottom:10px;}
.intro-claim{font-size:0.65rem;color:#fff;font-weight:600;line-height:1.5;margin-bottom:12px;padding:0 8px;}
.intro-title{font-family:'Orbitron',sans-serif;font-size:0.85rem;color:#fff;letter-spacing:2px;margin-bottom:12px;}
.intro-points{text-align:left;margin-bottom:10px;width:100%;max-width:240px;}
.intro-point{font-size:0.62rem;color:rgba(255,255,255,0.9);margin-bottom:7px;display:flex;align-items:flex-start;gap:6px;line-height:1.3;}
.intro-num{background:#fff;color:#cc0000;min-width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.55rem;font-weight:900;flex-shrink:0;}
.intro-sub{font-size:0.58rem;color:rgba(255,255,255,0.5);line-height:1.4;margin-bottom:10px;}
.intro-ref{background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);border-radius:8px;padding:8px 10px;margin-bottom:12px;width:100%;max-width:240px;}
.intro-ref-tag{font-family:'Orbitron',sans-serif;font-size:0.5rem;color:#FFD700;letter-spacing:2px;margin-bottom:3px;}
.intro-ref-text{font-size:0.58rem;color:rgba(255,255,255,0.8);line-height:1.4;}
.intro-cta{background:#fff;color:#cc0000;padding:8px 22px;border-radius:20px;font-family:'Orbitron',sans-serif;font-size:0.65rem;font-weight:900;letter-spacing:1px;cursor:pointer;transition:all 0.2s;animation:pulse 2s infinite;}
.intro-cta:hover{transform:scale(1.05);box-shadow:0 4px 20px rgba(255,255,255,0.3);}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1);}50%{opacity:0.85;transform:scale(1.03);}}

/* Video overlay inside phone */
.demo-video{width:100%;height:100%;object-fit:cover;}
.video-overlay{position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,0.85));padding:14px;z-index:5;transition:opacity 0.5s;}
.demo-badge{background:#ff4444;color:#fff;padding:3px 10px;border-radius:12px;font-size:0.6rem;font-weight:700;display:inline-block;margin-bottom:6px;animation:pulse 2s infinite;}
.demo-text{font-size:0.68rem;color:#ccc;line-height:1.4;}
.unmute-overlay{position:absolute;top:30px;left:10px;width:36px;height:36px;background:rgba(0,0,0,0.6);border-radius:50%;display:flex;align-items:center;justify-content:center;z-index:26;cursor:pointer;transition:opacity 0.3;border:1px solid rgba(255,255,255,0.2);}
.unmute-btn{font-size:1.1rem;color:#fff;line-height:1;}
.phone-logo{position:absolute;top:23px;right:-2px;width:24px;height:24px;border-radius:50%;object-fit:cover;z-index:9;opacity:0.8;}

/* Platform carousel on phone */
#platCarousel{position:absolute;inset:0;z-index:25;pointer-events:none;}
.plat-slide{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;opacity:0;transition:opacity 1s ease-in-out;padding:20px;}
.plat-slide.active{opacity:1;}
.plat-icon{width:60px;height:60px;margin-bottom:10px;filter:drop-shadow(0 4px 20px rgba(0,0,0,0.6));}
.plat-name{font-family:'Orbitron',sans-serif;font-size:1.1rem;color:#fff;letter-spacing:2px;margin-bottom:4px;text-shadow:0 2px 12px rgba(0,0,0,0.8);}
.plat-hook{font-size:0.75rem;color:rgba(255,255,255,0.8);text-shadow:0 1px 8px rgba(0,0,0,0.9);}

/* Kinetic PRO slide — full screen takeover */
.plat-slide[data-plat="kinetic"]{background:linear-gradient(160deg,#1a0a2e 0%,#0d0520 40%,#000 100%);pointer-events:auto;cursor:pointer;padding:0;text-decoration:none;color:#fff;}
.kinetic-card{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;padding:30px 20px;text-align:center;}
.kinetic-bolt{font-size:2.4rem;margin-bottom:8px;filter:drop-shadow(0 0 20px rgba(255,215,0,0.6));}
.kinetic-brand{font-family:'Orbitron',sans-serif;font-size:1.2rem;color:#FFD700;letter-spacing:4px;margin-bottom:4px;text-shadow:0 0 20px rgba(255,215,0,0.3);}
.kinetic-sub{font-size:0.7rem;color:#888;margin-bottom:20px;letter-spacing:1px;}
.kinetic-price{font-size:2.6rem;font-weight:900;color:#fff;margin-bottom:2px;}
.kinetic-price span{font-size:0.9rem;color:#aaa;font-weight:400;}
.kinetic-period{font-size:0.65rem;color:#666;margin-bottom:18px;}
.kinetic-feats{text-align:left;margin-bottom:20px;width:100%;max-width:200px;}
.kinetic-feat{font-size:0.62rem;color:#bbb;padding:5px 0;border-bottom:1px solid rgba(255,255,255,0.06);display:flex;align-items:center;gap:8px;}
.kinetic-feat-check{color:#FFD700;font-weight:900;font-size:0.7rem;}
.kinetic-cta{background:linear-gradient(135deg,#FFD700,#ff8c00);color:#000;padding:12px 30px;border-radius:25px;font-family:'Orbitron',sans-serif;font-size:0.7rem;font-weight:900;letter-spacing:2px;box-shadow:0 6px 25px rgba(255,215,0,0.35);transition:all 0.3s;}
.kinetic-cta:hover{transform:scale(1.05);box-shadow:0 10px 35px rgba(255,215,0,0.5);}

/* Auth gate overlay */
#authGate{position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.92);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);transition:opacity 0.4s;}
.gate-card{max-width:380px;width:90%;text-align:center;padding:40px 30px;}
.gate-logo{font-family:'Orbitron',sans-serif;font-size:1.8rem;color:#FFD700;letter-spacing:4px;margin-bottom:6px;text-shadow:0 0 30px rgba(255,215,0,0.3);}
.gate-sub{font-size:0.85rem;color:#888;margin-bottom:30px;line-height:1.6;}
.gate-btn{display:inline-flex;align-items:center;gap:12px;padding:16px 36px;background:#fff;border:none;border-radius:30px;font-family:'Poppins',sans-serif;font-size:15px;font-weight:600;color:#333;cursor:pointer;transition:all 0.2s;box-shadow:0 4px 25px rgba(255,255,255,0.15);}
.gate-btn:hover{transform:translateY(-3px);box-shadow:0 8px 35px rgba(255,255,255,0.25);}
.gate-btn svg{width:22px;height:22px;}
.gate-perks{margin-top:28px;text-align:left;max-width:280px;margin-left:auto;margin-right:auto;}
.gate-perk{display:flex;align-items:center;gap:10px;padding:8px 0;font-size:0.75rem;color:#aaa;}
.gate-perk-ico{color:#FFD700;font-size:1rem;flex-shrink:0;width:20px;text-align:center;}
.gate-note{margin-top:24px;font-size:0.65rem;color:#555;line-height:1.5;}
/* Signed-in user bar on hero */
.user-bar{display:flex;align-items:center;gap:10px;margin-top:16px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:30px;padding:6px 14px 6px 6px;}
.user-bar img{width:28px;height:28px;border-radius:50%;border:2px solid #FFD700;}
.user-bar span{font-size:0.75rem;color:#ccc;}
.token-pill{display:inline-flex;align-items:center;gap:4px;background:rgba(255,215,0,0.1);border:1px solid #FFD700;border-radius:12px;padding:2px 10px;font-family:'Orbitron',sans-serif;font-size:0.65rem;color:#FFD700;margin-left:auto;}

/* ── CONTENT SECTIONS ── */
.content{max-width:520px;margin:0 auto;padding:30px 20px 40px;}

/* Section titles */
.sec-title{font-family:'Orbitron',sans-serif;font-size:0.75rem;color:#666;letter-spacing:3px;text-align:center;margin-bottom:16px;}

/* Choice cards */
.choices{display:flex;gap:12px;margin-bottom:30px;}
.choice{flex:1;border-radius:16px;padding:22px 14px;text-align:center;cursor:pointer;transition:all 0.25s;text-decoration:none;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:160px;}
.choice:hover{transform:translateY(-4px);}
.choice-sub{background:linear-gradient(135deg,#1a0a2e,#2d1b69);border:2px solid #ff4444;}
.choice-sub:hover{box-shadow:0 10px 30px rgba(255,68,68,0.35);}
.choice-cin{background:linear-gradient(135deg,#1a0a0a,#2d0a0a);border:2px solid #FFD700;}
.choice-cin:hover{box-shadow:0 10px 30px rgba(255,215,0,0.35);}
.choice-ico{font-size:2rem;margin-bottom:8px;}
.choice-label{font-family:'Orbitron',sans-serif;font-size:0.75rem;letter-spacing:1px;margin-bottom:6px;}
.choice-sub .choice-label{color:#ff4444;}
.choice-cin .choice-label{color:#FFD700;}
.choice-desc{font-size:0.65rem;color:#888;line-height:1.5;}

/* Step cards */
.steps{display:flex;flex-direction:column;gap:12px;margin-bottom:30px;}
.step{display:block;text-decoration:none;border-radius:14px;padding:18px 20px;transition:all 0.25s;position:relative;overflow:hidden;}
.step:hover{transform:translateY(-3px);}
.step-num{font-family:'Orbitron',sans-serif;font-size:0.55rem;color:rgba(255,255,255,0.4);letter-spacing:2px;margin-bottom:4px;}
.step-title{font-family:'Orbitron',sans-serif;font-size:1rem;color:#fff;margin-bottom:4px;}
.step-desc{font-size:0.7rem;color:rgba(255,255,255,0.65);line-height:1.4;}
.step-free{position:absolute;top:12px;right:12px;background:linear-gradient(135deg,#00ff88,#00cc66);color:#000;padding:3px 10px;border-radius:10px;font-size:0.55rem;font-weight:700;letter-spacing:1px;}
.step-pro{position:absolute;top:12px;right:12px;background:#000;color:#FFD700;padding:3px 10px;border-radius:10px;font-size:0.55rem;font-weight:700;letter-spacing:1px;border:1px solid #FFD700;}
.step-1{background:linear-gradient(135deg,#ff4444,#aa0000);}
.step-1:hover{box-shadow:0 8px 25px rgba(255,68,68,0.4);}
.step-2{background:linear-gradient(135deg,#9944ff,#6600cc);}
.step-2:hover{box-shadow:0 8px 25px rgba(153,68,255,0.4);}
.step-3{background:linear-gradient(135deg,#FFD700,#ff8c00);}
.step-3:hover{box-shadow:0 8px 25px rgba(255,215,0,0.4);}
.step-3 .step-title,.step-3 .step-desc{color:#000;}
.step-3 .step-num{color:rgba(0,0,0,0.4);}

/* Features strip */
.feats{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:30px;}
.feat{text-align:center;padding:12px 6px;background:#111;border:1px solid #222;border-radius:10px;}
.feat-ico{font-size:1.2rem;margin-bottom:4px;}
.feat-txt{font-size:0.6rem;color:#888;}

/* Referral banner */
.referral{background:linear-gradient(135deg,#1a0a2e,#0d0d2b);border:1px solid #FFD700;border-radius:14px;padding:18px;text-align:center;margin-bottom:30px;}
.ref-tag{font-family:'Orbitron',sans-serif;font-size:0.6rem;color:#FFD700;letter-spacing:3px;margin-bottom:6px;}
.ref-text{font-size:0.75rem;color:#ccc;line-height:1.5;margin-bottom:10px;}
.ref-btn{display:inline-block;background:#FFD700;color:#000;padding:8px 20px;border-radius:20px;font-family:'Orbitron',sans-serif;font-size:0.65rem;font-weight:900;letter-spacing:1px;cursor:pointer;transition:all 0.2s;text-decoration:none;}
.ref-btn:hover{transform:scale(1.05);box-shadow:0 4px 15px rgba(255,215,0,0.4);}
.ref-input{width:100%;padding:10px 14px;background:#1a1a1a;border:1px solid #333;border-radius:8px;color:#fff;font-size:0.8rem;margin-bottom:10px;}
.ref-input:focus{border-color:#FFD700;outline:none;}
.ref-link-box{display:flex;gap:6px;margin-bottom:12px;}
.ref-link{flex:1;padding:8px 10px;background:#1a1a1a;border:1px solid #FFD700;border-radius:8px;color:#FFD700;font-size:0.65rem;font-family:monospace;}
.ref-copy{padding:8px 14px;background:#FFD700;color:#000;border-radius:8px;font-family:'Orbitron',sans-serif;font-size:0.6rem;font-weight:900;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;}
.ref-copy:hover{background:#ffed4a;}
.ref-stats{display:flex;gap:8px;margin-bottom:12px;}
.ref-stat{flex:1;text-align:center;padding:10px 4px;background:rgba(255,215,0,0.08);border:1px solid rgba(255,215,0,0.2);border-radius:8px;}
.ref-stat-num{font-family:'Orbitron',sans-serif;font-size:1rem;color:#FFD700;font-weight:900;}
.ref-stat-label{font-size:0.55rem;color:#888;margin-top:2px;}
.ref-share-row{display:flex;gap:8px;justify-content:center;}
.ref-share-btn{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:0.7rem;font-weight:900;cursor:pointer;transition:all 0.2s;}
.ref-share-btn:hover{transform:scale(1.15);}

/* Loyalty badge */
.loyalty{display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,#0d0d1a,#1a0a2e);border:1px solid rgba(255,215,0,0.25);border-radius:14px;padding:14px 18px;margin-bottom:14px;cursor:pointer;transition:all 0.25s;}
.loyalty:hover{border-color:#FFD700;box-shadow:0 4px 20px rgba(255,215,0,0.15);transform:translateY(-2px);}
.loyalty-icon{font-size:1.8rem;flex-shrink:0;animation:cashPulse 3s ease-in-out infinite;}
.loyalty-msg{font-size:0.72rem;color:#aaa;line-height:1.5;}
.loyalty-msg strong{color:#FFD700;font-weight:700;}
@keyframes cashPulse{0%,100%{transform:scale(1);}50%{transform:scale(1.15);}}

/* Payment section */
.pay-card{background:#111;border:1px solid #222;border-radius:14px;padding:20px;margin-bottom:30px;position:relative;}
.pay-badge{position:absolute;top:-10px;right:16px;background:#00ff88;color:#000;padding:3px 12px;border-radius:10px;font-size:0.6rem;font-weight:700;letter-spacing:1px;box-shadow:0 2px 8px rgba(0,255,136,0.3);}
.pay-title{font-family:'Orbitron',sans-serif;font-size:0.8rem;color:#fff;letter-spacing:2px;text-align:center;margin-bottom:14px;}
.pay-select{width:100%;padding:10px 14px;background:#1a1a1a;border:1px solid #333;border-radius:8px;color:#fff;font-size:0.85rem;margin-bottom:10px;cursor:pointer;}
.pay-select:focus{border-color:#ff4444;outline:none;}
.pay-select option{background:#1a1a1a;}
.pay-price{text-align:center;font-size:1.6rem;font-weight:900;color:#FFD700;margin:10px 0;}
.pay-email{width:100%;padding:10px 14px;background:#1a1a1a;border:1px solid #333;border-radius:8px;color:#fff;font-size:0.85rem;margin-bottom:12px;}
.pay-email:focus{border-color:#ff4444;outline:none;}
.pay-btn{width:100%;padding:14px;background:linear-gradient(135deg,#FFD700,#ff6b35);border:none;border-radius:10px;color:#000;font-family:'Orbitron',sans-serif;font-size:0.8rem;font-weight:900;letter-spacing:1px;cursor:pointer;transition:all 0.2s;}
.pay-btn:hover:not(:disabled){transform:scale(1.02);box-shadow:0 6px 20px rgba(255,215,0,0.4);}
.pay-btn:disabled{opacity:0.5;cursor:not-allowed;}
.pay-secure{text-align:center;font-size:0.65rem;color:#666;margin-top:10px;line-height:1.4;}

/* Quick features row */
.quick-feats{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:14px;}
.qf{padding:10px 4px;background:#1a1a1a;border:1px solid #222;border-radius:8px;text-align:center;cursor:pointer;transition:all 0.2s;}
.qf:hover{border-color:#ff4444;background:#1f1f1f;}
.qf-ico{font-size:1.1rem;margin-bottom:2px;}
.qf-txt{font-size:0.5rem;color:#888;font-weight:600;}

/* Footer */
.foot{text-align:center;padding:20px;font-size:0.6rem;color:#444;border-top:1px solid #1a1a1a;}
.foot a{color:#666;text-decoration:none;}

/* ── SMALL MOBILE ── */
@media(max-width:380px){
  .hero{padding:16px 12px 0;}
  .hero-title{font-size:1.1rem;}
  .phone{width:220px;height:440px;border-radius:28px;border-width:5px;}
  .phone-notch{width:90px;height:18px;border-radius:0 0 10px 10px;}
  .content{padding:20px 12px 30px;}
  .choices{flex-direction:column;}
  .choice{min-height:auto;padding:14px;}
  .step{padding:12px 14px;}
}

/* ── DESKTOP (900px+) ── */
@media(min-width:900px){
  body{overflow:hidden;height:100vh;}

  /* Split layout: phone left, content right */
  .page-wrap{display:flex;height:100vh;overflow:hidden;}

  .hero{width:420px;min-width:420px;height:100vh;padding:40px 40px 40px;justify-content:center;background:linear-gradient(180deg,#1a0a2e 0%,#0d0d1a 100%);border-right:1px solid #1a1a1a;flex-shrink:0;overflow:hidden;}
  .hero-title{font-size:2.2rem;letter-spacing:5px;margin-bottom:6px;}
  .hero-sub{font-size:1rem;margin-bottom:30px;}
  .phone{width:300px;height:600px;border-radius:44px;border-width:10px;}
  .phone-notch{width:130px;height:26px;}
  .demo-ribbon{top:32px;right:-34px;font-size:0.6rem;padding:6px 44px;}
  .intro-claim{font-size:0.7rem;}
  .intro-title{font-size:0.9rem;}
  .intro-point{font-size:0.66rem;}
  .intro-cta{font-size:0.7rem;padding:10px 26px;}

  .content{max-width:none;width:100%;height:100vh;overflow-y:auto;padding:40px 50px 60px;scrollbar-width:thin;scrollbar-color:#333 #0a0a0a;}
  .content::-webkit-scrollbar{width:6px;}
  .content::-webkit-scrollbar-track{background:#0a0a0a;}
  .content::-webkit-scrollbar-thumb{background:#333;border-radius:3px;}

  .sec-title{font-size:0.9rem;letter-spacing:4px;margin-bottom:20px;}

  /* Choice cards — bigger on desktop */
  .choices{gap:16px;margin-bottom:40px;}
  .choice{padding:30px 20px;min-height:200px;border-radius:20px;}
  .choice-ico{font-size:2.8rem;margin-bottom:12px;}
  .choice-label{font-size:0.9rem;letter-spacing:2px;margin-bottom:8px;}
  .choice-desc{font-size:0.8rem;line-height:1.6;}

  /* Steps — horizontal row on desktop */
  .steps{flex-direction:row;gap:16px;margin-bottom:40px;}
  .step{flex:1;padding:24px;border-radius:16px;}
  .step-num{font-size:0.6rem;}
  .step-title{font-size:1.1rem;margin-bottom:6px;}
  .step-desc{font-size:0.8rem;}

  /* Features — bigger */
  .feats{gap:12px;margin-bottom:40px;}
  .feat{padding:16px 8px;border-radius:12px;}
  .feat-ico{font-size:1.6rem;margin-bottom:6px;}
  .feat-txt{font-size:0.7rem;}

  /* Loyalty & referral — desktop */
  .loyalty{padding:18px 24px;margin-bottom:18px;}
  .loyalty-icon{font-size:2.2rem;}
  .loyalty-msg{font-size:0.85rem;}
  .ref-tag{font-size:0.7rem;margin-bottom:8px;}
  .ref-btn{font-size:0.75rem;padding:10px 24px;}
  .ref-input{font-size:0.9rem;padding:12px 16px;}
  .ref-stat-num{font-size:1.2rem;}

  /* Payment — wider, centred */
  .pay-card{max-width:500px;margin:0 auto 40px;padding:30px;}
  .pay-title{font-size:1rem;margin-bottom:18px;}
  .pay-price{font-size:2rem;}
  .pay-email{font-size:1rem;padding:12px 16px;}
  .pay-btn{font-size:0.9rem;padding:16px;}
  .pay-secure{font-size:0.75rem;}
  .qf{padding:12px 6px;}
  .qf-ico{font-size:1.3rem;}
  .qf-txt{font-size:0.6rem;}

  .foot{max-width:500px;margin:0 auto;}
}
</style>
</head>
<body>

<!-- ════════ AUTH GATE ════════ -->
<div id="authGate" style="display:none;">
  <div class="gate-card">
    <div class="gate-logo">IMAGINATOR</div>
    <div class="gate-sub">You've had your free go! Sign in with Google to keep using all tools, save to Drive, and publish to YouTube.</div>
    <button class="gate-btn" onclick="gateSignIn()">
      <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
      Sign in with Google
    </button>
    <div class="gate-perks">
      <div class="gate-perk"><span class="gate-perk-ico">&#9889;</span>Free cinematic video editor</div>
      <div class="gate-perk"><span class="gate-perk-ico">&#9729;</span>Save projects to your Google Drive</div>
      <div class="gate-perk"><span class="gate-perk-ico">&#9654;</span>Publish Shorts directly to YouTube</div>
      <div class="gate-perk"><span class="gate-perk-ico">&#127911;</span>AI music generation</div>
      <div class="gate-perk"><span class="gate-perk-ico">&#128176;</span>Earn pocketmoney via referrals</div>
    </div>
    <div style="margin-top:20px;background:rgba(255,215,0,0.08);border:1px solid rgba(255,215,0,0.25);border-radius:10px;padding:12px;text-align:center;">
      <div style="font-family:'Orbitron',sans-serif;font-size:1.4rem;color:#FFD700;">&#9733; 5 FREE TOKENS</div>
      <div style="font-size:0.7rem;color:#aaa;margin-top:4px;">1 token = 1 video export. Earn more by referring friends.</div>
    </div>
    <div class="gate-note">We never post without your permission.<br>No spam. No fees. Just sign in and create.</div>
  </div>
</div>

<div class="page-wrap">

<!-- ════════ HERO: Phone Demo ════════ -->
<div class="hero">
  <div class="hero-title">IMAGINATOR</div>
  <div class="hero-sub">Turn stills into cinematic YouTube Shorts</div>
  <div class="user-bar" id="userBar" style="display:none;">
    <img id="userAvatar" src="" alt="">
    <span id="userName"></span>
    <div class="token-pill">&#9733; <span id="tokenCount">0</span> SF</div>
  </div>

  <div class="phone">
    <div class="phone-notch"></div>
    <div class="demo-ribbon">DEMO</div>
    <div class="phone-screen">

      <!-- Red intro -->
      <div id="introScreen">
        <div class="intro-logo">SHORTF&#9650;CTORY</div>
        <div class="intro-claim">No automated YouTube or TikTok short editor is as great, as fast, as easy to use &mdash; or as free.</div>
        <div class="intro-title">WHY IMAGINATOR?</div>
        <div class="intro-points">
          <div class="intro-point"><span class="intro-num">1</span>People watch your videos longer &mdash; and on repeat</div>
          <div class="intro-point"><span class="intro-num">2</span>Your content gets shared more</div>
          <div class="intro-point"><span class="intro-num">3</span>You sell more products</div>
          <div class="intro-point"><span class="intro-num">4</span>We approach advertisers with your best content</div>
        </div>
        <div class="intro-sub">Good at art? Entertainment? Editing?<br>We boost where you're lacking.</div>
        <div class="intro-ref">
          <div class="intro-ref-tag">EARN WITH US</div>
          <div class="intro-ref-text">Refer a friend &amp; earn real cash. Every sign-up from your link puts pocketmoney straight in your account &mdash; no limits.</div>
        </div>
        <div class="intro-cta" onclick="dismissIntro()">SEE THE DEMO</div>
      </div>

      <!-- Demo video (hidden until intro dismissed) -->
      <video class="demo-video" autoplay loop muted playsinline id="demoVideo" style="display:none;">
        <source src="GIANTlove_compressed.mp4" type="video/mp4">
      </video>
      <img src="shortfactory-logo.webp" alt="SF" class="phone-logo" style="display:none;" id="phoneLogo">
      <div class="unmute-overlay" id="unmuteOverlay" style="display:none;">
        <div class="unmute-btn">🔊</div>
      </div>
      <div class="video-overlay" id="videoOverlay" style="display:none;">
        <div class="demo-badge">DEMO</div>
        <div class="demo-text">Upload your own pics, vids &amp; lyrics to create the most impressive YouTube Short ever!</div>
      </div>
      <!-- Platform carousel overlay -->
      <div id="platCarousel" style="display:none;">
        <div class="plat-slide" data-plat="youtube">
          <svg class="plat-icon" viewBox="0 0 24 24"><path fill="#ff0000" d="M23.5 6.2a3 3 0 00-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 00.5 6.2 31.9 31.9 0 000 12a31.9 31.9 0 00.5 5.8 3 3 0 002.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 002.1-2.1A31.9 31.9 0 0024 12a31.9 31.9 0 00-.5-5.8zM9.5 15.6V8.4l6.3 3.6-6.3 3.6z"/></svg>
          <div class="plat-name">YouTube</div>
          <div class="plat-hook">Your Shorts. Cinematic.</div>
        </div>
        <div class="plat-slide" data-plat="tiktok">
          <svg class="plat-icon" viewBox="0 0 24 24"><path fill="#25F4EE" d="M9.37 23.5a7.12 7.12 0 01-5.04-2.1A7.13 7.13 0 019.37 9.25v3.4a3.73 3.73 0 102.63 3.6v-15h3.35a5.8 5.8 0 005.15 5.2v3.35a9.1 9.1 0 01-5.15-1.6v7.8a7.13 7.13 0 01-5.98 7.5z"/><path fill="#FE2C55" d="M10.87 23.5a7.12 7.12 0 01-5.04-2.1A7.13 7.13 0 0110.87 9.25v3.4a3.73 3.73 0 102.63 3.6v-15h3.35a5.8 5.8 0 005.15 5.2v3.35a9.1 9.1 0 01-5.15-1.6v7.8a7.13 7.13 0 01-5.98 7.5z" opacity=".5"/></svg>
          <div class="plat-name">TikTok</div>
          <div class="plat-hook">Go viral. Effortlessly.</div>
        </div>
        <div class="plat-slide" data-plat="instagram">
          <svg class="plat-icon" viewBox="0 0 24 24"><defs><radialGradient id="ig" r="150%" cx="30%" cy="107%"><stop offset="0" stop-color="#fdf497"/><stop offset=".05" stop-color="#fdf497"/><stop offset=".45" stop-color="#fd5949"/><stop offset=".6" stop-color="#d6249f"/><stop offset=".9" stop-color="#285AEB"/></radialGradient></defs><rect width="20" height="20" x="2" y="2" rx="5" fill="url(#ig)"/><circle cx="12" cy="12" r="4.5" fill="none" stroke="#fff" stroke-width="1.5"/><circle cx="17.5" cy="6.5" r="1" fill="#fff"/></svg>
          <div class="plat-name">Instagram</div>
          <div class="plat-hook">Reels that stop the scroll.</div>
        </div>
        <div class="plat-slide" data-plat="twitter">
          <svg class="plat-icon" viewBox="0 0 24 24"><path fill="#fff" d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.4l-5.8-7.57-6.63 7.57H.49l8.6-9.83L0 1.15h7.59l5.24 6.93 6.07-6.93zm-1.29 19.5h2.04L6.48 3.24H4.3l13.31 17.41z"/></svg>
          <div class="plat-name" style="color:#fff;">X / Twitter</div>
          <div class="plat-hook">Stand out in every feed.</div>
        </div>
        <a class="plat-slide" data-plat="kinetic" href="https://www.shortfactory.shop/checkout.html" style="text-decoration:none;color:#fff;">
          <div class="kinetic-card">
            <div class="kinetic-bolt">⚡</div>
            <div class="kinetic-brand">KINETIC PRO</div>
            <div class="kinetic-sub">UNLOCK EVERYTHING</div>
            <div class="kinetic-price">£29<span>/mo</span></div>
            <div class="kinetic-period">Cancel anytime</div>
            <div class="kinetic-feats">
              <div class="kinetic-feat"><span class="kinetic-feat-check">✓</span>25+ animation patterns</div>
              <div class="kinetic-feat"><span class="kinetic-feat-check">✓</span>Export without watermark</div>
              <div class="kinetic-feat"><span class="kinetic-feat-check">✓</span>AI auto-timing</div>
              <div class="kinetic-feat"><span class="kinetic-feat-check">✓</span>Commercial license</div>
              <div class="kinetic-feat"><span class="kinetic-feat-check">✓</span>Priority support</div>
            </div>
            <div class="kinetic-cta">SUBSCRIBE NOW</div>
          </div>
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ════════ CONTENT ════════ -->
<div class="content">

  <!-- What do you want to create? -->
  <div class="sec-title">WHAT DO YOU WANT TO CREATE?</div>
  <div class="choices">
    <a href="https://www.shortfactory.shop/kinetic_editor.html" class="choice choice-sub">
      <div class="choice-ico">Aa</div>
      <div class="choice-label">ADD SUBTITLES</div>
      <div class="choice-desc">AI transcription<br>Word-level timing<br>Dancing kinetic text</div>
    </a>
    <a href="https://www.shortfactory.shop/imaginator/stills_v14.php" class="choice choice-cin">
      <div class="choice-ico">🎬</div>
      <div class="choice-label">CINEMATIC LAYER</div>
      <div class="choice-desc">Kaleidoscope transitions<br>Atmospheric soundscapes<br>AI music generation</div>
    </a>
  </div>

  <!-- 3 Steps -->
  <div class="sec-title">OUR TOOLS</div>
  <div class="steps">
    <a href="https://www.shortfactory.shop/imaginator/stills_v14.php" class="step step-1">
      <span class="step-free">FREE</span>
      <div class="step-num">STEP 1</div>
      <div class="step-title">TURN STILLS INTO VIDEO</div>
      <div class="step-desc">Upload images + AI music &rarr; cinematic WebM/MP4</div>
    </a>
    <a href="https://www.shortfactory.shop/kinetic.html" class="step step-2">
      <span class="step-free">FREE</span>
      <div class="step-num">STEP 2</div>
      <div class="step-title">KINETIC PRO</div>
      <div class="step-desc">AI transcription &bull; Word-level timing &bull; Dancing text</div>
    </a>
    <a href="https://www.shortfactory.shop/kinetic_editor.html" class="step step-3">
      <span class="step-pro">PRO</span>
      <div class="step-num">PRO EDITOR</div>
      <div class="step-title">TIMELINE EDITOR</div>
      <div class="step-desc">Symmetrical progressive frames &bull; Phone viewport &bull; Full control</div>
    </a>
  </div>

  <!-- Features strip -->
  <div class="feats">
    <div class="feat"><div class="feat-ico">⚡</div><div class="feat-txt">30s processing</div></div>
    <div class="feat"><div class="feat-ico">🎨</div><div class="feat-txt">Hollywood FX</div></div>
    <div class="feat"><div class="feat-ico">📱</div><div class="feat-txt">Mobile-ready</div></div>
    <div class="feat"><div class="feat-ico">💾</div><div class="feat-txt">Instant download</div></div>
  </div>

  <!-- Loyalty badge -->
  <div class="loyalty" onclick="toggleRefPanel()">
    <div class="loyalty-icon">💰</div>
    <div class="loyalty-msg">Loyalty is rewarded.<br><strong>Increasing loyalty is increasingly rewarded.</strong></div>
  </div>
  <!-- Hidden referral panel (expands on click) -->
  <div class="referral" id="refPanel" style="display:none;">
    <div class="ref-tag">EARN WITH US</div>
    <div id="refForm">
      <input type="email" id="refEmail" class="ref-input" placeholder="Enter your email to get your referral link...">
      <div class="ref-btn" onclick="generateRefLink()">GET MY LINK</div>
    </div>
    <div id="refResult" style="display:none;">
      <div class="ref-link-box">
        <input type="text" id="refLinkField" class="ref-link" readonly>
        <div class="ref-copy" onclick="copyRefLink()">COPY</div>
      </div>
      <div class="ref-stats">
        <div class="ref-stat"><div class="ref-stat-num" id="refClicks">0</div><div class="ref-stat-label">Clicks</div></div>
        <div class="ref-stat"><div class="ref-stat-num" id="refSignups">0</div><div class="ref-stat-label">Sign-ups</div></div>
        <div class="ref-stat"><div class="ref-stat-num" id="refEarned">£0</div><div class="ref-stat-label">Earned</div></div>
      </div>
      <div class="ref-share-row">
        <div class="ref-share-btn" onclick="shareRef('twitter')" style="background:#1DA1F2;">𝕏</div>
        <div class="ref-share-btn" onclick="shareRef('whatsapp')" style="background:#25D366;">WA</div>
        <div class="ref-share-btn" onclick="shareRef('facebook')" style="background:#1877F2;">fb</div>
        <div class="ref-share-btn" onclick="shareRef('email')" style="background:#666;">@</div>
      </div>
    </div>
  </div>

  <!-- Payment -->
  <div class="pay-card">
    <div class="pay-badge">LIVE NOW</div>
    <div class="pay-title">UPGRADE TO PRO</div>
    <div class="pay-price">£29<span style="font-size:0.8rem;color:#ccc;">/month</span></div>
    <div style="text-align:center;font-size:0.7rem;color:#888;margin-bottom:14px;">Billed monthly, cancel anytime</div>
    <ul style="list-style:none;margin:0 0 16px;padding:0;">
      <li style="padding:8px 0;color:#ccc;font-size:0.75rem;border-bottom:1px solid #1a1a1a;"><span style="color:#FFD700;margin-right:8px;">✓</span>All 25+ animation patterns</li>
      <li style="padding:8px 0;color:#ccc;font-size:0.75rem;border-bottom:1px solid #1a1a1a;"><span style="color:#FFD700;margin-right:8px;">✓</span>Export without watermark</li>
      <li style="padding:8px 0;color:#ccc;font-size:0.75rem;border-bottom:1px solid #1a1a1a;"><span style="color:#FFD700;margin-right:8px;">✓</span>AI auto-timing &amp; batch processing</li>
      <li style="padding:8px 0;color:#ccc;font-size:0.75rem;"><span style="color:#FFD700;margin-right:8px;">✓</span>Commercial license &amp; priority support</li>
    </ul>
    <a href="https://www.shortfactory.shop/checkout.html" class="pay-btn" style="display:block;text-align:center;text-decoration:none;">SUBSCRIBE NOW</a>
    <div class="pay-secure">🔒 Secure checkout powered by Stripe &bull; Cancel anytime</div>
    <div class="quick-feats">
      <div class="qf" onclick="alert('📱 Mobile app coming soon!')"><div class="qf-ico">📱</div><div class="qf-txt">Mobile App</div></div>
      <div class="qf" onclick="alert('☁️ Cloud storage coming soon!')"><div class="qf-ico">☁️</div><div class="qf-txt">Cloud</div></div>
      <div class="qf" onclick="alert('👥 Team features coming soon!')"><div class="qf-ico">👥</div><div class="qf-txt">Teams</div></div>
      <div class="qf" onclick="window.location.href='https://www.youtube.com/@shortfactory'"><div class="qf-ico">🎓</div><div class="qf-txt">Tutorials</div></div>
    </div>
  </div>

  <div class="foot">SHORTF&#9650;CTORY &copy; 2025 &bull; <a href="https://www.shortfactory.shop/">Home</a></div>
</div>
</div><!-- end page-wrap -->

<script>
// ── Intro screen ──
function dismissIntro(){
  var s=document.getElementById('introScreen');
  if(!s||s.style.opacity==='0')return;
  s.style.opacity='0';
  setTimeout(function(){
    s.style.display='none';
    document.getElementById('demoVideo').style.display='block';
    document.getElementById('demoVideo').play();
    document.getElementById('phoneLogo').style.display='block';
    document.getElementById('unmuteOverlay').style.display='flex';
    document.getElementById('videoOverlay').style.display='block';
    // Start platform carousel
    document.getElementById('platCarousel').style.display='block';
    startPlatCarousel();
  },500);
}
setTimeout(dismissIntro,6000);

// ── Platform carousel ──
var platIndex=0;
var platTimer=null;
function startPlatCarousel(){
  var slides=document.querySelectorAll('.plat-slide');
  var ribbon=document.querySelector('.demo-ribbon');
  var overlay=document.getElementById('videoOverlay');
  if(!slides.length)return;
  // Show first slide after a brief delay
  setTimeout(function(){
    slides[0].classList.add('active');
    platTimer=setInterval(function(){
      slides[platIndex].classList.remove('active');
      platIndex=(platIndex+1)%slides.length;
      slides[platIndex].classList.add('active');
      // Kinetic slide = full takeover, hide ribbon + overlay
      var isKinetic=slides[platIndex].getAttribute('data-plat')==='kinetic';
      if(ribbon)ribbon.style.opacity=isKinetic?'0':'1';
      if(overlay)overlay.style.opacity=isKinetic?'0':'1';
    },4000);
  },1500);
}

// ── Unmute ──
var dv=document.getElementById('demoVideo');
var uo=document.getElementById('unmuteOverlay');
if(uo&&dv){
  uo.addEventListener('click',function(){
    dv.muted=false;dv.play();
    uo.style.opacity='0';
    setTimeout(function(){uo.style.display='none';},300);
  });
}

// ── Loyalty / Referral toggle ──
function toggleRefPanel(){
  var p=document.getElementById('refPanel');
  p.style.display=p.style.display==='none'?'block':'none';
}

// ── Referral system ──
function generateRefLink(){
  var email=document.getElementById('refEmail').value.trim();
  if(!email||email.indexOf('@')<1){document.getElementById('refEmail').style.borderColor='#ff4444';return;}
  // Generate a referral code from email hash
  var code=btoa(email).replace(/[^a-zA-Z0-9]/g,'').substring(0,8).toLowerCase();
  var link='https://www.shortfactory.shop/imaginator/index2.php?ref='+code;
  // Store locally
  localStorage.setItem('sf_ref_email',email);
  localStorage.setItem('sf_ref_code',code);
  // Show result
  document.getElementById('refForm').style.display='none';
  document.getElementById('refResult').style.display='block';
  document.getElementById('refLinkField').value=link;
  // Load stats (placeholder — backend will track these)
  var stats=JSON.parse(localStorage.getItem('sf_ref_stats')||'{"clicks":0,"signups":0,"earned":0}');
  document.getElementById('refClicks').textContent=stats.clicks;
  document.getElementById('refSignups').textContent=stats.signups;
  document.getElementById('refEarned').textContent='£'+stats.earned;
}
// Auto-restore if returning user
(function(){
  var c=localStorage.getItem('sf_ref_code');
  if(c){
    var email=localStorage.getItem('sf_ref_email')||'';
    document.getElementById('refEmail').value=email;
    document.getElementById('refPanel').style.display='block';
    generateRefLink();
  }
  // Track incoming referrals
  var params=new URLSearchParams(window.location.search);
  if(params.get('ref')){localStorage.setItem('sf_referred_by',params.get('ref'));}
})();

function copyRefLink(){
  var f=document.getElementById('refLinkField');
  f.select();navigator.clipboard.writeText(f.value).then(function(){
    var btn=document.querySelector('.ref-copy');btn.textContent='COPIED!';
    setTimeout(function(){btn.textContent='COPY';},2000);
  });
}

function shareRef(platform){
  var link=document.getElementById('refLinkField').value;
  var text='Check out ShortFactory — the best free YouTube Shorts editor! '+link;
  var urls={
    twitter:'https://twitter.com/intent/tweet?text='+encodeURIComponent(text),
    whatsapp:'https://wa.me/?text='+encodeURIComponent(text),
    facebook:'https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(link),
    email:'mailto:?subject='+encodeURIComponent('Check out ShortFactory')+'&body='+encodeURIComponent(text)
  };
  if(urls[platform])window.open(urls[platform],'_blank');
}

/* ============================
   GOOGLE AUTH GATE
   ============================ */
var G_CLIENT_ID='246057462897-mui96hjeuk9abvlkgvvqdfdeiknbmojb.apps.googleusercontent.com';
var G_SCOPES='https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/youtube.upload https://www.googleapis.com/auth/youtube.readonly';

function gateSignIn(){
  if(typeof google==='undefined'||!google.accounts){
    alert('Google is still loading — try again in a moment');
    return;
  }
  var client=google.accounts.oauth2.initTokenClient({
    client_id:G_CLIENT_ID,
    scope:G_SCOPES,
    callback:function(response){
      if(response.error)return;
      var token=response.access_token;
      localStorage.setItem('sf_g_token',token);
      localStorage.setItem('sf_g_token_time',Date.now());
      // Fetch profile
      fetch('https://www.googleapis.com/oauth2/v2/userinfo',{
        headers:{'Authorization':'Bearer '+token}
      }).then(function(r){return r.json();}).then(function(d){
        localStorage.setItem('sf_google_name',d.name||'');
        localStorage.setItem('sf_google_email',d.email||'');
        localStorage.setItem('sf_google_avatar',d.picture||'');
        unlockApp(d);
      });
    }
  });
  client.requestAccessToken();
}

function unlockApp(user){
  isSignedIn=true;
  // Hide gate
  var gate=document.getElementById('authGate');
  gate.style.opacity='0';
  gate.style.transition='opacity 0.4s';
  setTimeout(function(){gate.style.display='none';},400);
  // Show user bar
  document.getElementById('userBar').style.display='flex';
  document.getElementById('userAvatar').src=user.picture||'';
  document.getElementById('userName').textContent=(user.given_name||user.name||user.email);
  // Grant signup tokens if first time
  if(!localStorage.getItem('sf_tokens_granted')){
    localStorage.setItem('sf_tokens_granted','true');
    localStorage.setItem('sf_tokens',String(parseInt(localStorage.getItem('sf_tokens')||'0')+5));
  }
  // Daily login bonus
  var today=new Date().toISOString().slice(0,10);
  if(localStorage.getItem('sf_daily_claim')!==today){
    localStorage.setItem('sf_daily_claim',today);
    localStorage.setItem('sf_tokens',String(parseInt(localStorage.getItem('sf_tokens')||'0')+1));
  }
  // Update token display
  document.getElementById('tokenCount').textContent=localStorage.getItem('sf_tokens')||'0';
}

// ── Gate check: 1 free go, then must sign in ──
var isSignedIn=false;

function gateCheck(e){
  if(isSignedIn)return true;
  // Admin bypass
  if(localStorage.getItem('admin_authenticated')==='true')return true;
  var uses=parseInt(localStorage.getItem('sf_free_uses')||'0');
  if(uses<1){
    // First go — let them through, count it
    localStorage.setItem('sf_free_uses',uses+1);
    return true;
  }
  // Used their free go — show gate
  e.preventDefault();
  document.getElementById('authGate').style.display='flex';
  return false;
}

// Attach gate check to all tool links
document.querySelectorAll('.choice, .step').forEach(function(link){
  link.addEventListener('click',function(e){
    if(!gateCheck(e)){e.stopPropagation();}
  });
});

// Check if already signed in (token less than 50 min old)
(function(){
  var token=localStorage.getItem('sf_g_token');
  var time=parseInt(localStorage.getItem('sf_g_token_time')||'0');
  var name=localStorage.getItem('sf_google_name');
  var avatar=localStorage.getItem('sf_google_avatar');
  var email=localStorage.getItem('sf_google_email');
  if(token&&name&&(Date.now()-time)<3000000){
    isSignedIn=true;
    unlockApp({name:name,given_name:name.split(' ')[0],picture:avatar,email:email});
  } else if(name){
    // Show token count even if not fully auth'd
    var tc=document.getElementById('tokenCount');
    if(tc)tc.textContent=localStorage.getItem('sf_tokens')||'0';
  }
  // Admin bypass
  if(localStorage.getItem('admin_authenticated')==='true'){
    isSignedIn=true;
  }
  // If used free go and not signed in, show gate immediately
  var uses=parseInt(localStorage.getItem('sf_free_uses')||'0');
  if(uses>=1&&!isSignedIn){
    document.getElementById('authGate').style.display='flex';
  }
})();

</script>

<script src="/tracking.js"></script>
<script src="/ai_feedback.js"></script>
</body>
</html>
