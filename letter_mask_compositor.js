const fs = require('fs');
const path = require('path');

// ============================================
// LETTER MASK COMPOSITOR v1.0
// Video backgrounds through letter shapes!
// ============================================

/*
CONCEPT:
1. Letter masks (PNG with transparency) = Shape of letter
2. Background videos (fire.webm, water.webm, etc.) = Fill
3. Compositor = Plays video THROUGH the letter shape
4. Result = 🔥 FIRE LETTERS 🔥

TECH STACK:
- ffmpeg for video compositing
- Node.js for automation
- Alpha masking for transparency

EXAMPLE:
  Letter "A" mask + fire.webm = Letter A filled with flames
*/

const MASK_DIR = 'C:/Users/User/Desktop/imaginator/letter_masks';
const VIDEO_DIR = 'C:/Users/User/Desktop/imaginator/background_videos';
const OUTPUT_DIR = 'C:/Users/User/Desktop/imaginator/masked_letters';

// Background video styles
const VIDEO_STYLES = {
  fire: 'fire_background.webm',
  water: 'water_background.webm',
  galaxy: 'galaxy_background.webm',
  smoke: 'smoke_background.webm',
  lightning: 'lightning_background.webm',
  neon: 'neon_background.webm',
  glitter: 'glitter_background.webm',
  gold: 'gold_background.webm'
};

// Create directories
function initDirectories() {
  [MASK_DIR, VIDEO_DIR, OUTPUT_DIR].forEach(dir => {
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
    }
  });

  // Create style subdirectories
  Object.keys(VIDEO_STYLES).forEach(style => {
    const styleDir = path.join(OUTPUT_DIR, style);
    if (!fs.existsSync(styleDir)) {
      fs.mkdirSync(styleDir, { recursive: true });
    }
  });
}

// Generate letter masks using HTML Canvas
function generateLetterMasks() {
  console.log('📝 Generating letter masks...\n');

  const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'.split('');
  const maskTemplate = `
<!DOCTYPE html>
<html>
<head>
<style>
body { margin: 0; background: black; }
canvas { display: block; }
</style>
</head>
<body>
<canvas id="canvas" width="1080" height="1920"></canvas>
<script>
const canvas = document.getElementById('canvas');
const ctx = canvas.getContext('2d');

// Draw letter
ctx.fillStyle = 'white';
ctx.font = 'bold 1200px Arial';
ctx.textAlign = 'center';
ctx.textBaseline = 'middle';
ctx.fillText('{{LETTER}}', 540, 960);

// Export as PNG
canvas.toBlob(blob => {
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'letter_{{LETTER}}.png';
  a.click();
});
</script>
</body>
</html>
`;

  console.log('💡 MASK GENERATION INSTRUCTIONS:');
  console.log('─────────────────────────────────────────\n');
  console.log('OPTION 1: Generate with HTML Canvas (Manual)');
  console.log('1. Open browser');
  console.log('2. For each letter, paste this HTML:');
  console.log('   (Replace {{LETTER}} with actual letter)');
  console.log('3. Save PNG to:', MASK_DIR);
  console.log('');
  console.log('OPTION 2: Use ImageMagick (Automated)');
  console.log('   Command for each letter:');
  console.log('   convert -size 1080x1920 -background black \\');
  console.log('   -fill white -font Arial-Bold -pointsize 1200 \\');
  console.log('   -gravity center label:A letter_A.png\n');
  console.log('OPTION 3: Download pre-made letter masks');
  console.log('   Search: "alphabet masks transparent PNG"\n');
}

// FFmpeg command to composite video through letter mask
function generateCompositeCommand(letter, style) {
  const maskPath = path.join(MASK_DIR, `letter_${letter}.png`);
  const videoPath = path.join(VIDEO_DIR, VIDEO_STYLES[style]);
  const outputPath = path.join(OUTPUT_DIR, style, `${letter}_${style}.mp4`);

  return `ffmpeg -i "${videoPath}" -i "${maskPath}" -filter_complex "[0:v][1:v]alphamerge[masked]" -map "[masked]" -c:v libx264 -pix_fmt yuv420p -t 3 "${outputPath}"`;
}

// Batch process all letters with all styles
function generateBatchScript() {
  console.log('📜 Generating batch processing script...\n');

  let batchScript = '#!/bin/bash\n\n';
  batchScript += '# LETTER MASK BATCH COMPOSITOR\n';
  batchScript += '# Generates all letter+style combinations\n\n';

  const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'.split('');

  Object.keys(VIDEO_STYLES).forEach(style => {
    batchScript += `\necho "🎨 Processing ${style} style..."\n`;
    letters.forEach(letter => {
      const cmd = generateCompositeCommand(letter, style);
      batchScript += `${cmd}\n`;
    });
  });

  batchScript += '\necho "✅ All letters processed!"\n';

  const scriptPath = path.join(__dirname, 'batch_composite.sh');
  fs.writeFileSync(scriptPath, batchScript);

  console.log(`✅ Batch script created: ${scriptPath}`);
  console.log('   Run: bash batch_composite.sh\n');
}

// Main setup
function setup() {
  console.log('🎭 LETTER MASK COMPOSITOR v1.0');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');

  initDirectories();
  console.log('✅ Directories created:\n');
  console.log('   📁 Masks:', MASK_DIR);
  console.log('   📁 Videos:', VIDEO_DIR);
  console.log('   📁 Output:', OUTPUT_DIR);
  console.log('');

  generateLetterMasks();
  generateBatchScript();

  console.log('\n📋 SETUP CHECKLIST:');
  console.log('─────────────────────────────────────────\n');
  console.log('1. ✅ Folders created');
  console.log('2. ⏳ Generate letter masks (A-Z, 0-9)');
  console.log('   → Save to:', MASK_DIR);
  console.log('3. ⏳ Download background videos:');
  console.log('   → fire_background.webm');
  console.log('   → water_background.webm');
  console.log('   → galaxy_background.webm');
  console.log('   → etc...');
  console.log('   → Save to:', VIDEO_DIR);
  console.log('4. ⏳ Run: bash batch_composite.sh');
  console.log('5. 🎉 Get 288 masked letters (36 letters × 8 styles)');
  console.log('');
  console.log('💡 Each letter will have 8 variations:');
  Object.keys(VIDEO_STYLES).forEach(style => {
    console.log(`   🔥 ${style}`);
  });
  console.log('');
  console.log('🚀 This will be REVOLUTIONARY!');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');
}

// Export for use
module.exports = {
  generateCompositeCommand,
  VIDEO_STYLES,
  MASK_DIR,
  VIDEO_DIR,
  OUTPUT_DIR
};

// Run setup if called directly
if (require.main === module) {
  setup();
}
