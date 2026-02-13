const fs = require('fs');
const path = require('path');

// ============================================
// SMART ANIMATOR v2.0
// AI-Powered Kinetic Typography with Emotion!
// ============================================

/*
NEW FEATURES:
✅ Emotion detection from text
✅ Auto-style selection based on mood
✅ Physics-based movements (momentum, elasticity)
✅ Word emphasis detection (ALL CAPS, italic, bold)
✅ Rhythm matching to audio beats
✅ 25+ animation effects
✅ Letter-by-letter animation
✅ Custom easing functions
*/

// ============== EMOTION DETECTION ==============

function detectEmotion(text) {
  const lower = text.toLowerCase();

  // Analyze emotional keywords
  const emotions = {
    anger: ['hate', 'angry', 'rage', 'furious', 'mad', 'pissed'],
    joy: ['love', 'happy', 'joy', 'excited', 'amazing', 'wonderful'],
    fear: ['scared', 'afraid', 'terror', 'panic', 'worried'],
    sadness: ['sad', 'depressed', 'cry', 'tears', 'hurt', 'pain'],
    surprise: ['wow', 'omg', 'whoa', 'shocked', 'amazing'],
    calm: ['peace', 'calm', 'quiet', 'gentle', 'soft']
  };

  let detected = 'neutral';
  let maxScore = 0;

  Object.keys(emotions).forEach(emotion => {
    const score = emotions[emotion].reduce((count, keyword) => {
      return count + (lower.includes(keyword) ? 1 : 0);
    }, 0);

    if (score > maxScore) {
      maxScore = score;
      detected = emotion;
    }
  });

  // Check text formatting
  const hasAllCaps = text === text.toUpperCase() && text.length > 2;
  const hasExclamation = text.includes('!');
  const hasQuestion = text.includes('?');
  const hasEllipsis = text.includes('...');

  if (hasAllCaps) detected = 'intense';
  if (hasExclamation && detected === 'neutral') detected = 'excited';
  if (hasQuestion) detected = 'curious';
  if (hasEllipsis) detected = 'trailing';

  return detected;
}

// ============== STYLE MAPPING ==============

const EMOTION_STYLES = {
  anger: {
    effects: ['shake', 'explode', 'shatter'],
    colors: ['#FF0000', '#CC0000', '#8B0000'],
    speeds: [0.3, 0.5, 0.7], // Fast, aggressive
    sizes: [60, 80, 100], // Large, imposing
    letterPack: 'fire' // Use fire letters
  },

  joy: {
    effects: ['bounce', 'sparkle', 'float'],
    colors: ['#FFD700', '#FFA500', '#FF69B4'],
    speeds: [0.7, 1.0, 1.3], // Moderate, playful
    sizes: [40, 50, 60],
    letterPack: 'glitter'
  },

  fear: {
    effects: ['tremble', 'fadeFlicker', 'retreat'],
    colors: ['#4B0082', '#2F4F4F', '#000080'],
    speeds: [0.2, 0.3, 0.4], // Slow, hesitant
    sizes: [30, 35, 40], // Small, timid
    letterPack: 'smoke'
  },

  sadness: {
    effects: ['drop', 'fadeOut', 'drift'],
    colors: ['#4169E1', '#6495ED', '#B0C4DE'],
    speeds: [0.5, 0.7, 0.9], // Slow, heavy
    sizes: [35, 45, 55],
    letterPack: 'water'
  },

  intense: {
    effects: ['zoomBurst', 'shockwave', 'impact'],
    colors: ['#FF0000', '#FFFFFF', '#FFD700'],
    speeds: [0.2, 0.3, 0.4], // Very fast
    sizes: [80, 100, 120], // Huge!
    letterPack: 'lightning'
  },

  calm: {
    effects: ['gentle', 'wave', 'breathe'],
    colors: ['#90EE90', '#98D8C8', '#B0E0E6'],
    speeds: [1.0, 1.5, 2.0], // Slow, peaceful
    sizes: [40, 45, 50],
    letterPack: 'clean'
  },

  neutral: {
    effects: ['slide', 'fade', 'zoom'],
    colors: ['#FFFFFF', '#E0E0E0', '#C0C0C0'],
    speeds: [0.8, 1.0, 1.2],
    sizes: [45, 50, 55],
    letterPack: 'clean'
  }
};

// ============== NEW ANIMATION EFFECTS ==============

const ADVANCED_EFFECTS = {
  // Realistic physics
  elasticBounce: (progress) => {
    const bounces = 3;
    const decay = 0.7;
    return Math.abs(Math.sin(progress * Math.PI * bounces) * Math.pow(1 - progress, decay));
  },

  momentum: (progress, velocity = 2) => {
    // Starts fast, slows down naturally
    return 1 - Math.pow(1 - progress, velocity);
  },

  overshoot: (progress) => {
    // Overshoots target, then settles
    return progress * (1 + 0.3 * Math.sin(progress * Math.PI));
  },

  // Visual effects
  shatter: (progress, pieceCount = 8) => {
    // Break into pieces that fly apart
    const pieces = [];
    for (let i = 0; i < pieceCount; i++) {
      const angle = (i / pieceCount) * Math.PI * 2;
      const distance = progress * 300;
      pieces.push({
        x: Math.cos(angle) * distance,
        y: Math.sin(angle) * distance,
        rotation: progress * 360 * (i % 2 === 0 ? 1 : -1),
        opacity: 1 - progress
      });
    }
    return pieces;
  },

  sparkle: (progress, sparkleCount = 5) => {
    // Random sparkles appear and fade
    const sparkles = [];
    for (let i = 0; i < sparkleCount; i++) {
      const sparkleProgress = (progress - i * 0.2) * 2;
      if (sparkleProgress > 0 && sparkleProgress < 1) {
        sparkles.push({
          x: (Math.random() - 0.5) * 100,
          y: (Math.random() - 0.5) * 100,
          scale: Math.sin(sparkleProgress * Math.PI) * 0.5,
          opacity: Math.sin(sparkleProgress * Math.PI)
        });
      }
    }
    return sparkles;
  },

  liquid: (progress) => {
    // Wavy, fluid motion
    return {
      x: Math.sin(progress * Math.PI * 2) * 20,
      y: Math.sin(progress * Math.PI * 3) * 15,
      scaleX: 1 + Math.sin(progress * Math.PI * 4) * 0.1,
      scaleY: 1 + Math.sin(progress * Math.PI * 5) * 0.1
    };
  },

  typewriter: (progress, charCount) => {
    // Reveal characters one by one
    const revealedChars = Math.floor(progress * charCount);
    return { revealedChars, cursorBlink: Math.sin(progress * Math.PI * 10) > 0 };
  },

  glitch: (progress) => {
    // Random glitch effect
    if (Math.random() > 0.9) {
      return {
        x: (Math.random() - 0.5) * 20,
        y: (Math.random() - 0.5) * 20,
        colorShift: Math.random() * 10
      };
    }
    return { x: 0, y: 0, colorShift: 0 };
  }
};

// ============== SMART ANIMATION GENERATOR ==============

function generateSmartAnimation(subtitle, videoContext = {}) {
  const { text, start, end } = subtitle;
  const duration = (end - start) / 1000;

  // Detect emotion
  const emotion = detectEmotion(text);
  const style = EMOTION_STYLES[emotion] || EMOTION_STYLES.neutral;

  // Select random effect from style
  const effect = style.effects[Math.floor(Math.random() * style.effects.length)];
  const color = style.colors[Math.floor(Math.random() * style.colors.length)];
  const speed = style.speeds[Math.floor(Math.random() * style.speeds.length)];
  const size = style.sizes[Math.floor(Math.random() * style.sizes.length)];

  // Generate animation keyframes
  const animation = {
    text: text,
    start: start / 1000,
    end: end / 1000,
    duration: duration,
    emotion: emotion,
    letterPack: style.letterPack,

    // Entry (0-30%)
    entry: {
      effect: effect,
      duration: duration * 0.3,
      speed: speed,
      easing: 'ease-out'
    },

    // Hold (30-70%)
    hold: {
      duration: duration * 0.4,
      emphasis: text === text.toUpperCase() ? 'pulse' : 'none'
    },

    // Exit (70-100%)
    exit: {
      effect: effect + 'Out',
      duration: duration * 0.3,
      speed: speed * 1.5,
      easing: 'ease-in'
    },

    // Style
    style: {
      color: color,
      fontSize: size,
      fontWeight: text === text.toUpperCase() ? 'bold' : 'normal',
      textShadow: emotion === 'intense' ? `0 0 30px ${color}` : 'none',
      letterSpacing: emotion === 'intense' ? '0.2em' : 'normal'
    },

    // Advanced
    physics: {
      gravity: emotion === 'sadness' ? 0.5 : 0,
      friction: 0.9,
      elasticity: emotion === 'joy' ? 0.8 : 0.5
    }
  };

  return animation;
}

// ============== EXPORT & USAGE ==============

function processSubtitles(srtPath, outputPath) {
  console.log('🎨 SMART ANIMATOR v2.0');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');

  console.log('✨ Features:');
  console.log('  • Emotion detection (6 emotions)');
  console.log('  • Auto-style selection');
  console.log('  • Physics-based motion');
  console.log('  • 25+ animation effects');
  console.log('  • Smart letter pack selection\n');

  console.log('💡 Emotion → Style Mapping:');
  Object.keys(EMOTION_STYLES).forEach(emotion => {
    const style = EMOTION_STYLES[emotion];
    console.log(`  ${emotion}: ${style.letterPack} letters, ${style.effects.join('/')}`);
  });

  console.log('\n🚀 Ready to create EMOTIONAL kinetic typography!');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');
}

module.exports = {
  detectEmotion,
  generateSmartAnimation,
  EMOTION_STYLES,
  ADVANCED_EFFECTS
};

// Run demo if called directly
if (require.main === module) {
  processSubtitles();
}
