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
    <title>Home – Christmas Puzzle</title>

    <!-- Christmas Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Mountains+of+Christmas:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Snowburst+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="/public/assets/css/login.css">
    <link rel="stylesheet" href="/public/assets/css/home.css">
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

<div class="home-container">
    <h1 class="title">Welcome to the Christmas Puzzle</h1>
    
    <div class="button-group">
        <a href="/frontend/levels.php" class="home-btn play-btn">
            <span class="btn-icon">🎮</span>
            <span class="btn-text">Play</span>
        </a>
        
        <a href="/frontend/rules.php" class="home-btn rules-btn">
            <span class="btn-icon">📖</span>
            <span class="btn-text">Game Rules</span>
        </a>
        
        <a href="/frontend/leaderboards.php" class="home-btn leaderboards-btn">
            <span class="btn-icon">🏆</span>
            <span class="btn-text">Leaderboards</span>
        </a>
    </div>
    
    <div class="user-info">
        <p>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></strong></p>
        <button id="logoutBtn" class="logout-btn">
            <span class="btn-icon">🚪</span>
            <span class="btn-text">Logout</span>
        </button>
    </div>
</div>

<!-- Universal Snowflakes -->
<script src="/public/assets/js/snowflakes.js"></script>

<!-- Music JS -->
<script src="/public/assets/js/music.js"></script>

<!-- Dark Mode JS -->
<script src="/public/assets/js/darkmode.js"></script>

<!-- Logout Handler -->
<script>
document.getElementById('logoutBtn').addEventListener('click', async () => {
    try {
        // Call logout endpoint
        const response = await fetch('/api/logout.php', {
            method: 'POST',
            credentials: 'same-origin'
        });
        
        // Redirect to login page (logout.php already redirects, but this ensures it)
        window.location.href = '/frontend/login.php';
    } catch (error) {
        console.error('Logout error:', error);
        // Still redirect even if there's an error
        window.location.href = '/frontend/login.php';
    }
});
</script>

</body>
</html>

