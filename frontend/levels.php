<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
    // Start session with persistent configuration
    require_once __DIR__ . '/../backend/session.php';
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: /frontend/login.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Level – Christmas Puzzle</title>

    <!-- Christmas Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Mountains+of+Christmas:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Snowburst+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="/public/assets/css/login.css">
    <link rel="stylesheet" href="/public/assets/css/levels.css">
</head>
<body>

<!-- BACKGROUND MUSIC -->
<audio id="bg-music" src="/public/assets/audio/christmas.mp3" loop muted></audio>

<!-- Music toggle button -->
<button id="musicToggle" class="music-btn">🔇</button>

<!-- Dark Mode Toggle Button (Universal - Top Right) -->
<button id="darkModeToggle" class="dark-mode-btn">
    <span class="tree-icon">
        <span class="tree-top"></span>
        <span class="tree-middle"></span>
        <span class="tree-bottom"></span>
        <span class="tree-trunk"></span>
        <span class="star"></span>
    </span>
</button>

<!-- Floating Clouds -->
<div class="cloud cloud1"></div>
<div class="cloud cloud2"></div>
<div class="cloud cloud3"></div>
<div class="cloud cloud4"></div>

<!-- Sleighs -->
<img src="/public/assets/img/santa.png" class="cloud-sleigh sleigh1" alt="Santa's Sleigh">
<img src="/public/assets/img/santa.png" class="cloud-sleigh sleigh2" alt="Santa's Sleigh">
<img src="/public/assets/img/santa.png" class="cloud-sleigh sleigh3" alt="Santa's Sleigh">
<img src="/public/assets/img/santa.png" class="cloud-sleigh sleigh4" alt="Santa's Sleigh">

<!-- Snowflakes container -->
<div id="snow-container"></div>

<div class="levels-container">
    <h1 class="title">Select Level</h1>
    
    <div class="levels-grid" id="levelsGrid">
        <!-- Levels will be loaded here -->
    </div>
    
    <div class="back-button">
        <a href="/frontend/home.php" class="home-btn back-btn">
            <span class="btn-icon">←</span>
            <span class="btn-text">Back to Home</span>
        </a>
    </div>
</div>

<!-- Universal Snowflakes -->
<script src="/public/assets/js/snowflakes.js"></script>

<!-- Music JS -->
<script src="/public/assets/js/music.js"></script>

<!-- Dark Mode JS -->
<script src="/public/assets/js/darkmode.js"></script>

<!-- Levels Script -->
<script>
// Level to grid size mapping
const LEVEL_TO_SIZE = {
    1: 3,   // Level 1: 3x3
    2: 4,   // Level 2: 4x4
    3: 5,   // Level 3: 5x5
    4: 6,   // Level 4: 6x6
    5: 7,   // Level 5: 7x7
    6: 8,   // Level 6: 8x8
    7: 9,   // Level 7: 9x9
    8: 10,  // Level 8: 10x10
};

const MAX_LEVEL = 8;

async function loadLevels() {
    try {
        const response = await fetch('/api/get-unlocked-levels.php');
        const data = await response.json();
        
        if (data.success) {
            const unlockedLevels = data.unlockedLevels || [1];
            const currentLevel = data.currentLevel || 1;
            
            const grid = document.getElementById('levelsGrid');
            grid.innerHTML = '';
            
            for (let level = 1; level <= MAX_LEVEL; level++) {
                const isUnlocked = unlockedLevels.includes(level);
                const isCurrent = level === currentLevel;
                const gridSize = LEVEL_TO_SIZE[level];
                
                const levelCard = document.createElement('div');
                levelCard.className = `level-card ${isUnlocked ? 'unlocked' : 'locked'}`;
                
                if (isUnlocked) {
                    levelCard.innerHTML = `
                        <div class="level-number">${level}</div>
                        <div class="level-info">
                            <div class="level-size">${gridSize}×${gridSize}</div>
                            ${isCurrent ? '<div class="level-badge current">Current</div>' : ''}
                        </div>
                        <a href="/frontend/index.php?level=${level}" class="level-play-btn">Play</a>
                    `;
                } else {
                    levelCard.innerHTML = `
                        <div class="level-number locked-icon">🔒</div>
                        <div class="level-info">
                            <div class="level-size">${gridSize}×${gridSize}</div>
                            <div class="level-badge locked">Locked</div>
                        </div>
                        <div class="level-lock-message">Complete Level ${level - 1} to unlock</div>
                    `;
                }
                
                grid.appendChild(levelCard);
            }
        }
    } catch (error) {
        console.error('Error loading levels:', error);
        alert('Failed to load levels. Please try again.');
    }
}

// Load levels when page loads
document.addEventListener('DOMContentLoaded', loadLevels);
</script>

</body>
</html>



