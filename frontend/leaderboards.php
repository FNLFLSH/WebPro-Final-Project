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
    <title>Leaderboards – Christmas Puzzle</title>

    <!-- Christmas Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Mountains+of+Christmas:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="/public/assets/css/login.css">
    <link rel="stylesheet" href="/public/assets/css/leaderboards.css">
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

<div class="leaderboards-container">
    <h1 class="title">🏆 Leaderboards</h1>
    
    <div class="leaderboards-content">
        <div class="leaderboard-section">
            <h2>⏱️ Fastest Times</h2>
            <div id="timeLeaderboard" class="leaderboard-list">
                <p class="loading">Loading leaderboard...</p>
            </div>
        </div>
        
        <div class="leaderboard-section">
            <h2>🎯 Fewest Moves</h2>
            <div id="movesLeaderboard" class="leaderboard-list">
                <p class="loading">Loading leaderboard...</p>
            </div>
        </div>
    </div>
    
    <div class="button-group">
        <a href="/frontend/home.php" class="home-btn">← Back to Home</a>
    </div>
</div>

<!-- Universal Snowflakes -->
<script src="/public/assets/js/snowflakes.js"></script>

<!-- Music JS -->
<script src="/public/assets/js/music.js"></script>

<!-- Dark Mode JS -->
<script src="/public/assets/js/darkmode.js"></script>

<!-- Leaderboards JS -->
<script>
    // Load leaderboards from API
    async function loadLeaderboards() {
        try {
            const response = await fetch('/api/analytics.php?type=leaderboards');
            const data = await response.json();
            
            if (data.error) {
                document.getElementById('timeLeaderboard').innerHTML = '<p class="error">' + data.error + '</p>';
                document.getElementById('movesLeaderboard').innerHTML = '<p class="error">' + data.error + '</p>';
                return;
            }
            
            // Display time leaderboard
            if (data.fastest_times && data.fastest_times.length > 0) {
                const timeList = data.fastest_times.map((entry, index) => 
                    `<div class="leaderboard-item">
                        <span class="rank">${index + 1}</span>
                        <span class="username">${entry.username}</span>
                        <span class="score">${formatTime(entry.completion_time)}</span>
                    </div>`
                ).join('');
                document.getElementById('timeLeaderboard').innerHTML = timeList;
            } else {
                document.getElementById('timeLeaderboard').innerHTML = '<p class="empty">No records yet. Be the first!</p>';
            }
            
            // Display moves leaderboard
            if (data.fewest_moves && data.fewest_moves.length > 0) {
                const movesList = data.fewest_moves.map((entry, index) => 
                    `<div class="leaderboard-item">
                        <span class="rank">${index + 1}</span>
                        <span class="username">${entry.username}</span>
                        <span class="score">${entry.moves} moves</span>
                    </div>`
                ).join('');
                document.getElementById('movesLeaderboard').innerHTML = movesList;
            } else {
                document.getElementById('movesLeaderboard').innerHTML = '<p class="empty">No records yet. Be the first!</p>';
            }
        } catch (error) {
            console.error('Error loading leaderboards:', error);
            document.getElementById('timeLeaderboard').innerHTML = '<p class="error">Failed to load leaderboards</p>';
            document.getElementById('movesLeaderboard').innerHTML = '<p class="error">Failed to load leaderboards</p>';
        }
    }
    
    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
    
    // Load leaderboards on page load
    document.addEventListener('DOMContentLoaded', loadLeaderboards);
</script>

</body>
</html>

