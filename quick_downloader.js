const https = require('https');
const http = require('http');
const fs = require('fs');
const path = require('path');

// Free GIF sources (no API needed)
const FREE_GIFS = [
  // AnimatedImages.org - Free animated letter GIFs
  'https://www.animatedimages.org/data/media/43/animated-letter-image-0001.gif',
  'https://www.animatedimages.org/data/media/43/animated-letter-image-0002.gif',
  'https://www.animatedimages.org/data/media/43/animated-letter-image-0003.gif',
  'https://www.animatedimages.org/data/media/43/animated-letter-image-0004.gif',
  'https://www.animatedimages.org/data/media/43/animated-letter-image-0005.gif'
];

const ASSET_DIR = 'C:/Users/User/Desktop/imaginator/letter_assets/free';

function downloadFile(url, filename) {
  return new Promise((resolve, reject) => {
    const filepath = path.join(ASSET_DIR, filename);

    // Skip if exists
    if (fs.existsSync(filepath)) {
      console.log(`⏭️  ${filename} (exists)`);
      resolve();
      return;
    }

    const protocol = url.startsWith('https') ? https : http;
    const file = fs.createWriteStream(filepath);

    console.log(`📥 Downloading: ${filename}...`);

    protocol.get(url, (response) => {
      if (response.statusCode === 200) {
        response.pipe(file);
        file.on('finish', () => {
          file.close();
          console.log(`✅ Success: ${filename}`);
          resolve();
        });
      } else if (response.statusCode === 301 || response.statusCode === 302) {
        // Handle redirect
        console.log(`↪️  Redirect for ${filename}`);
        reject(new Error('Redirect - skipping'));
      } else {
        console.log(`❌ Failed: ${filename} (HTTP ${response.statusCode})`);
        reject(new Error(`HTTP ${response.statusCode}`));
      }
    }).on('error', (err) => {
      fs.unlink(filepath, () => {});
      console.log(`❌ Error: ${filename} - ${err.message}`);
      reject(err);
    });
  });
}

async function downloadSamples() {
  console.log('🚀 QUICK SAMPLE DOWNLOADER');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');
  console.log(`📂 Downloading to: ${ASSET_DIR}\n`);

  // Ensure directory exists
  if (!fs.existsSync(ASSET_DIR)) {
    fs.mkdirSync(ASSET_DIR, { recursive: true });
  }

  let successCount = 0;

  for (let i = 0; i < FREE_GIFS.length; i++) {
    const url = FREE_GIFS[i];
    const filename = `sample_letter_${i + 1}.gif`;

    try {
      await downloadFile(url, filename);
      successCount++;
      await new Promise(resolve => setTimeout(resolve, 500)); // Small delay
    } catch (err) {
      // Continue on error
    }
  }

  console.log('\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
  console.log(`✅ Downloaded: ${successCount}/${FREE_GIFS.length} samples`);
  console.log(`💾 Location: ${ASSET_DIR}`);
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');

  console.log('📝 NEXT STEPS:');
  console.log('1. Get Pixabay API key: https://pixabay.com/api/docs/');
  console.log('2. Add key to pixabay_downloader.js (line 10)');
  console.log('3. Run: node pixabay_downloader.js');
  console.log('4. Sit back and watch 500 videos download! 🔥\n');
}

downloadSamples().catch(err => {
  console.error('Fatal error:', err);
  process.exit(1);
});
