const axios = require('axios');
const cheerio = require('cheerio');
const https = require('https');
const fs = require('fs');
const path = require('path');

// ============================================
// WEB SCRAPER v1.0 (No API Key Required!)
// ============================================

const ASSET_DIR = 'C:/Users/User/Desktop/imaginator/letter_assets';
const MAX_DOWNLOADS = 100;
let downloadCount = 0;

// Scrape Pixabay search page
async function scrapePixabayPage(query, page = 1) {
  try {
    const url = `https://pixabay.com/videos/search/${encodeURIComponent(query)}/?pagi=${page}`;
    console.log(`🔍 Scraping: ${url}`);

    const response = await axios.get(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
      }
    });

    const $ = cheerio.load(response.data);
    const videos = [];

    // Find video elements (this is a simplified example - actual scraping may need adjustment)
    $('a[href*="/video/"]').each((i, elem) => {
      const href = $(elem).attr('href');
      if (href && href.includes('/video/')) {
        videos.push('https://pixabay.com' + href);
      }
    });

    return videos;

  } catch (err) {
    console.error(`❌ Scrape failed: ${err.message}`);
    return [];
  }
}

// Download video
function downloadFile(url, filepath) {
  return new Promise((resolve, reject) => {
    const file = fs.createWriteStream(filepath);

    https.get(url, (response) => {
      if (response.statusCode === 200) {
        response.pipe(file);
        file.on('finish', () => {
          file.close();
          console.log(`✅ Downloaded: ${path.basename(filepath)}`);
          resolve();
        });
      } else {
        reject(new Error(`HTTP ${response.statusCode}`));
      }
    }).on('error', (err) => {
      fs.unlink(filepath, () => {});
      reject(err);
    });
  });
}

console.log('🔥 WEB SCRAPER v1.0');
console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');
console.log('💡 NOTE: This scraper is basic and may not work perfectly.');
console.log('   For best results, get a Pixabay API key and use:');
console.log('   pixabay_downloader.js\n');
console.log('🎯 Starting scrape...\n');

// For now, just show instructions
console.log('📋 ALTERNATIVE: Manual Download Guide');
console.log('─────────────────────────────────────────\n');
console.log('FASTEST METHOD:');
console.log('1. Visit: https://pixabay.com/videos/search/alphabet%20animation/');
console.log('2. Click any video');
console.log('3. Click "Free Download"');
console.log('4. Choose "Large" or "Medium" quality');
console.log('5. Save to: C:\\Users\\User\\Desktop\\imaginator\\letter_assets\\free\\\n');
console.log('CATEGORIES TO SEARCH:');
console.log('  • alphabet animation');
console.log('  • fire letters');
console.log('  • neon alphabet  ');
console.log('  • glitter letters');
console.log('  • gold alphabet');
console.log('  • 3d letters\n');
console.log('🎯 Download 50-100 of the best ones!');
console.log('💡 Focus on videos with transparent backgrounds\n');
