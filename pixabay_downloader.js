const https = require('https');
const fs = require('fs');
const path = require('path');

// ============================================
// PIXABAY AUTO-DOWNLOADER v1.0
// ============================================

const PIXABAY_API_KEY = '54546082-bf81ebbae63d438ca1a1425ea'; // Get from: https://pixabay.com/api/docs/
const ASSET_DIR = 'C:/Users/User/Desktop/imaginator/letter_assets';
const MAX_DOWNLOADS = 500; // Download limit
const RATE_LIMIT = 100; // 100 requests per 60 seconds

// Search queries for different letter styles
const SEARCH_QUERIES = [
  'animated alphabet fire',
  'animated letters neon',
  'alphabet glitter sparkle',
  'animated alphabet gold',
  'kinetic typography letters',
  'animated letter 3d',
  'alphabet liquid metal',
  'neon letter glow',
  'fire text animation',
  'glitter alphabet loop'
];

let downloadCount = 0;
let requestCount = 0;
let startTime = Date.now();

// Rate limiting function
function waitForRateLimit() {
  requestCount++;
  if (requestCount >= RATE_LIMIT) {
    const elapsed = Date.now() - startTime;
    if (elapsed < 60000) {
      const waitTime = 60000 - elapsed;
      console.log(`⏳ Rate limit reached. Waiting ${Math.ceil(waitTime/1000)}s...`);
      return new Promise(resolve => setTimeout(resolve, waitTime));
    }
    requestCount = 0;
    startTime = Date.now();
  }
  return Promise.resolve();
}

// Search Pixabay API
function searchPixabay(query, page = 1) {
  return new Promise((resolve, reject) => {
    const url = `https://pixabay.com/api/videos/?key=${PIXABAY_API_KEY}&q=${encodeURIComponent(query)}&page=${page}&per_page=50&video_type=animation`;

    https.get(url, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        try {
          resolve(JSON.parse(data));
        } catch (e) {
          reject(e);
        }
      });
    }).on('error', reject);
  });
}

// Download video file
function downloadVideo(url, filename, category) {
  return new Promise((resolve, reject) => {
    const categoryPath = path.join(ASSET_DIR, category);
    if (!fs.existsSync(categoryPath)) {
      fs.mkdirSync(categoryPath, { recursive: true });
    }

    const filepath = path.join(categoryPath, filename);

    // Skip if already downloaded
    if (fs.existsSync(filepath)) {
      console.log(`⏭️  Skip: ${filename} (already exists)`);
      resolve();
      return;
    }

    const file = fs.createWriteStream(filepath);

    https.get(url, (response) => {
      const totalSize = parseInt(response.headers['content-length'], 10);
      let downloaded = 0;

      response.on('data', (chunk) => {
        downloaded += chunk.length;
        const percent = ((downloaded / totalSize) * 100).toFixed(1);
        process.stdout.write(`\r📥 ${filename}: ${percent}%`);
      });

      response.pipe(file);

      file.on('finish', () => {
        file.close();
        console.log(`\n✅ Downloaded: ${filename}`);
        downloadCount++;
        resolve();
      });
    }).on('error', (err) => {
      fs.unlink(filepath, () => {});
      reject(err);
    });
  });
}

// Categorize video by tags
function categorizeVideo(tags) {
  const tagStr = tags.toLowerCase();
  if (tagStr.includes('fire') || tagStr.includes('flame')) return 'fire';
  if (tagStr.includes('neon') || tagStr.includes('glow')) return 'neon';
  if (tagStr.includes('glitter') || tagStr.includes('sparkle')) return 'glitter';
  if (tagStr.includes('gold') || tagStr.includes('metal')) return 'gold';
  if (tagStr.includes('3d') || tagStr.includes('depth')) return '3d';
  if (tagStr.includes('liquid') || tagStr.includes('water')) return 'liquid';
  return 'free';
}

// Main download function
async function startDownload() {
  console.log('🚀 PIXABAY AUTO-DOWNLOADER');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');

  if (PIXABAY_API_KEY === 'YOUR_API_KEY_HERE') {
    console.log('❌ ERROR: No API key set!\n');
    console.log('📝 Get your FREE API key:');
    console.log('   1. Visit: https://pixabay.com/api/docs/');
    console.log('   2. Sign up (free)');
    console.log('   3. Copy your API key');
    console.log('   4. Paste it in pixabay_downloader.js (line 10)\n');
    console.log('💡 Then run: node pixabay_downloader.js\n');
    return;
  }

  console.log(`🎯 Target: ${MAX_DOWNLOADS} videos`);
  console.log(`📂 Destination: ${ASSET_DIR}`);
  console.log(`🔍 Searching ${SEARCH_QUERIES.length} categories\n`);

  for (const query of SEARCH_QUERIES) {
    if (downloadCount >= MAX_DOWNLOADS) break;

    console.log(`\n🔎 Searching: "${query}"`);
    console.log('─────────────────────────────────────────\n');

    try {
      await waitForRateLimit();
      const results = await searchPixabay(query, 1);

      if (!results.hits || results.hits.length === 0) {
        console.log('   No results found.\n');
        continue;
      }

      console.log(`   Found ${results.totalHits} videos!\n`);

      for (const video of results.hits) {
        if (downloadCount >= MAX_DOWNLOADS) break;

        // Get highest quality video
        const videoUrl = video.videos.large?.url || video.videos.medium?.url || video.videos.small?.url;
        if (!videoUrl) continue;

        const category = categorizeVideo(video.tags);
        const filename = `${video.id}_${category}.mp4`;

        try {
          await downloadVideo(videoUrl, filename, category);
          await new Promise(resolve => setTimeout(resolve, 1000)); // 1s delay between downloads
        } catch (err) {
          console.error(`❌ Failed: ${filename} - ${err.message}`);
        }
      }

    } catch (err) {
      console.error(`❌ Search failed: ${err.message}`);
    }
  }

  console.log('\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
  console.log(`✅ DOWNLOAD COMPLETE!`);
  console.log(`📊 Downloaded: ${downloadCount} videos`);
  console.log(`💾 Location: ${ASSET_DIR}`);
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');
}

// Run downloader
startDownload().catch(err => {
  console.error('❌ Fatal error:', err);
  process.exit(1);
});
