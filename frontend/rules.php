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
    <title>Game Rules – Christmas Puzzle</title>

    <!-- Christmas Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Mountains+of+Christmas:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="/public/assets/css/login.css">
    <link rel="stylesheet" href="/public/assets/css/rules.css">
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

<!-- Sleighs (separate from clouds to break stacking context) -->
<img src="/public/assets/img/santa.png" class="cloud-sleigh sleigh1" alt="Santa's Sleigh">
<img src="/public/assets/img/santa.png" class="cloud-sleigh sleigh2" alt="Santa's Sleigh">
<img src="/public/assets/img/santa.png" class="cloud-sleigh sleigh3" alt="Santa's Sleigh">
<img src="/public/assets/img/santa.png" class="cloud-sleigh sleigh4" alt="Santa's Sleigh">

<!-- Snowflakes container -->
<div id="snow-container"></div>

<div class="rules-container">
    <h1 class="title">Game Rules</h1>
    
    <div class="rules-content">
        <div class="rule-section">
            <h2>🎮 How to Play</h2>
            <p>The Christmas Fifteen Puzzle is a sliding puzzle game. Your goal is to arrange the numbered tiles in order from 1 to 15, with the empty space in the bottom-right corner.</p>
        </div>
        
        <div class="rule-section">
            <h2>🎯 Objective</h2>
            <p>Click on any tile adjacent to the empty space to move it. Continue moving tiles until all numbers are in the correct order.</p>
        </div>
        
        <div class="rule-section">
            <h2>💡 Tips</h2>
            <ul>
                <li>Plan your moves ahead</li>
                <li>Work on one row or column at a time</li>
                <li>Use the hint button if you get stuck</li>
                <li>Try to solve it in as few moves as possible!</li>
            </ul>
        </div>
        
        <div class="rule-section">
            <h2>🏆 Scoring</h2>
            <p>Your score is based on the number of moves and time taken. The faster you solve it with fewer moves, the higher your score!</p>
        </div>
    </div>
    
    <div class="button-group">
        <a href="/frontend/home.php" class="home-btn">← Back to Home</a>
        <a href="/frontend/index.php" class="home-btn play-btn">🎮 Start Playing</a>
    </div>
</div>

<!-- Universal Snowflakes -->
<script src="/public/assets/js/snowflakes.js"></script>

<!-- Music JS -->
<script src="/public/assets/js/music.js"></script>

<!-- Dark Mode JS -->
<script src="/public/assets/js/darkmode.js"></script>

</body>
</html>

