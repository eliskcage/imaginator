const fs = require('fs');
const subtitle = require('subtitle');

// Parse SRT file manually (simple parser)
const srtPath = 'C:/Users/User/Desktop/GIANTlove_compressed.mp4.srt';
const srtContent = fs.readFileSync(srtPath, 'utf8');

function parseSRT(srtText) {
  const lines = srtText.trim().split('\n');
  const subtitles = [];
  let current = {};

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i].trim();

    // Skip index numbers
    if (/^\d+$/.test(line)) {
      if (current.text) {
        subtitles.push(current);
      }
      current = {};
      continue;
    }

    // Parse timecodes
    if (line.includes('-->')) {
      const [start, end] = line.split('-->').map(t => t.trim());
      current.start = timeToMs(start);
      current.end = timeToMs(end);
      continue;
    }

    // Text content
    if (line && current.start !== undefined) {
      current.text = (current.text || '') + line + ' ';
    }
  }

  if (current.text) {
    subtitles.push(current);
  }

  return subtitles;
}

function timeToMs(timeStr) {
  const [time, ms] = timeStr.split(',');
  const [hours, minutes, seconds] = time.split(':').map(Number);
  return (hours * 3600 + minutes * 60 + seconds) * 1000 + (parseInt(ms) || 0);
}

const parsedSubs = parseSRT(srtContent);
console.log(`🎬 Loaded ${parsedSubs.length} subtitles from giantlove.srt`);

// Wild animation effect generators
const effects = [
  'zoomIn', 'zoomOut', 'slideUp', 'slideDown', 'slideLeft', 'slideRight',
  'fadeIn', 'fadeOut', 'spin', 'bounce', 'explode', 'shrink', 'pulse',
  'glitch', 'wave', 'shake'
];

function randomEffect() {
  return effects[Math.floor(Math.random() * effects.length)];
}

function randomRange(min, max) {
  return Math.random() * (max - min) + min;
}

function randomInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

// Generate wild animation data for each subtitle
const animations = parsedSubs.map((sub, index) => {
  const duration = (sub.end - sub.start) / 1000; // seconds

  // Random wild effects
  const entryEffect = randomEffect();
  const exitEffect = randomEffect();
  const midEffect = Math.random() > 0.5 ? randomEffect() : null;

  // Random positioning (mobile phone format)
  const startX = randomInt(10, 90); // percentage
  const startY = randomInt(20, 80);
  const endX = randomInt(10, 90);
  const endY = randomInt(20, 80);

  // Random scaling
  const startScale = randomRange(0.3, 1.5);
  const midScale = randomRange(0.5, 3.0);
  const endScale = randomRange(0.3, 1.5);

  // Random rotation
  const startRotate = randomInt(-45, 45);
  const midRotate = randomInt(-180, 180);
  const endRotate = randomInt(-45, 45);

  // Random opacity
  const startOpacity = randomRange(0.2, 1.0);
  const midOpacity = randomRange(0.5, 1.0);
  const endOpacity = randomRange(0.2, 1.0);

  // Random colors
  const colors = [
    '#FFFFFF', '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A',
    '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2', '#F8B500'
  ];
  const color = colors[randomInt(0, colors.length - 1)];

  // Random font size
  const fontSize = randomInt(24, 72);

  // Random blur effect
  const hasBlur = Math.random() > 0.7;
  const blurAmount = hasBlur ? randomInt(1, 5) : 0;

  return {
    index: index + 1,
    text: sub.text,
    start: sub.start / 1000,
    end: sub.end / 1000,
    duration: duration,

    // Entry animation (0% to 30% of duration)
    entry: {
      effect: entryEffect,
      x: startX,
      y: startY,
      scale: startScale,
      rotate: startRotate,
      opacity: startOpacity
    },

    // Mid animation (30% to 70% of duration)
    mid: midEffect ? {
      effect: midEffect,
      x: randomInt(20, 80),
      y: randomInt(30, 70),
      scale: midScale,
      rotate: midRotate,
      opacity: midOpacity
    } : null,

    // Exit animation (70% to 100% of duration)
    exit: {
      effect: exitEffect,
      x: endX,
      y: endY,
      scale: endScale,
      rotate: endRotate,
      opacity: endOpacity
    },

    // Style
    style: {
      color: color,
      fontSize: fontSize,
      blur: blurAmount,
      fontWeight: Math.random() > 0.5 ? 'bold' : 'normal',
      textShadow: Math.random() > 0.5 ? `0 0 ${randomInt(10, 30)}px ${color}` : 'none'
    }
  };
});

// Save animation data as JSON
const outputPath = 'C:/Users/User/Desktop/imaginator/giantlove_animation.json';
fs.writeFileSync(outputPath, JSON.stringify(animations, null, 2));

console.log(`\n✅ Generated ${animations.length} WILD animations!`);
console.log(`📁 Saved to: ${outputPath}`);
console.log(`\n🎨 Animation Effects Used:`);

const effectCount = {};
animations.forEach(anim => {
  effectCount[anim.entry.effect] = (effectCount[anim.entry.effect] || 0) + 1;
  effectCount[anim.exit.effect] = (effectCount[anim.exit.effect] || 0) + 1;
  if (anim.mid) effectCount[anim.mid.effect] = (effectCount[anim.mid.effect] || 0) + 1;
});

Object.entries(effectCount).sort((a, b) => b[1] - a[1]).forEach(([effect, count]) => {
  console.log(`  ${effect}: ${count} times`);
});

console.log(`\n🚀 Next: Run the HTML player to see the WILD animations!`);
