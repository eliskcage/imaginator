const https = require('https');
const fs = require('fs');
const path = require('path');

// Asset sources with free licenses
const SOURCES = {
  vecteezy: 'https://www.vecteezy.com/free-videos/alphabet-animation',
  videezy: 'https://www.videezy.com/free-video/alphabet',
  pixabay: 'https://pixabay.com/videos/search/alphabet/'
};

const ASSET_DIR = 'C:/Users/User/Desktop/imaginator/letter_assets';

console.log('🚀 ASSET HARVESTER v1.0');
console.log('📦 Downloading animated letter packs...\n');

// Function to download file
function downloadFile(url, dest, callback) {
  const file = fs.createWriteStream(dest);
  https.get(url, (response) => {
    response.pipe(file);
    file.on('finish', () => {
      file.close(callback);
    });
  }).on('error', (err) => {
    fs.unlink(dest, () => {});
    if (callback) callback(err.message);
  });
}

// Manual download instructions (since these sites require interaction)
console.log('📋 DOWNLOAD INSTRUCTIONS:\n');
console.log('These sites require manual download (click buttons):');
console.log('');
console.log('1. VECTEEZY (3,014 alphabet animations)');
console.log('   Visit: https://www.vecteezy.com/free-videos/alphabet-animation');
console.log('   → Click "Free Download" on each letter');
console.log('   → Save to: C:/Users/User/Desktop/imaginator/letter_assets/free/\n');

console.log('2. VIDEEZY (83 alphabet videos)');
console.log('   Visit: https://www.videezy.com/free-video/alphabet');
console.log('   → Download MP4 versions');
console.log('   → Save to: C:/Users/User/Desktop/imaginator/letter_assets/free/\n');

console.log('3. PIXABAY (94 alphabet videos - CC0!)');
console.log('   Visit: https://pixabay.com/videos/search/alphabet/');
console.log('   → Best quality, no attribution needed');
console.log('   → Save to: C:/Users/User/Desktop/imaginator/letter_assets/free/\n');

console.log('💡 TIP: Look for these keywords:');
console.log('   - "transparent background"');
console.log('   - "alpha channel"');
console.log('   - "loop"');
console.log('   - "animated letter"');
console.log('   - "kinetic typography"');

console.log('\n🎯 PRIORITY DOWNLOADS:');
console.log('   1. Fire/flame letters');
console.log('   2. Glitter/sparkle effects');
console.log('   3. Neon glow');
console.log('   4. Gold/metallic');
console.log('   5. 3D letters with depth');

console.log('\n📊 TARGET: 100GB of premium assets!');
console.log('💰 FREE + LEGAL = Win!');

// Create organized folders
const folders = ['free', 'fire', 'glitter', 'neon', 'gold', '3d', 'liquid', 'glass', 'premium', 'clean'];
folders.forEach(folder => {
  const folderPath = path.join(ASSET_DIR, folder);
  if (!fs.existsSync(folderPath)) {
    fs.mkdirSync(folderPath, { recursive: true });
  }
});

console.log('\n✅ Folder structure ready!');
console.log('🎬 Ready to receive assets!');
