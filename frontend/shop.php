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
    <title>Shop – Christmas Puzzle</title>

    <!-- Christmas Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Mountains+of+Christmas:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="/public/assets/css/login.css">
    <link rel="stylesheet" href="/public/assets/css/shop.css">
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

<div class="shop-container">
    <h1 class="title">🛒 Shop</h1>
    
    <!-- Coin Balance Display -->
    <div class="coin-balance">
        <span class="coin-icon">🪙</span>
        <span class="coin-amount" id="coinAmount">Loading...</span>
    </div>
    
    <!-- Power-ups Grid -->
    <div class="powerups-grid" id="powerupsGrid">
        <!-- Power-ups will be loaded here -->
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

<!-- Shop Script -->
<script>
// Power-up definitions
const POWERUPS = [
    {
        type: 'freeze_timer',
        name: 'Freeze Timer',
        description: 'Stops the timer for 30 seconds',
        price: 0,  // TESTING: Original price 100
        icon: '❄️'
    },
    {
        type: 'smart_shuffle',
        name: 'Smart Shuffle',
        description: 'Reshuffles the board to an easier state',
        price: 0,  // TESTING: Original price 75
        icon: '🔀'
    }
];

let currentCoins = 0;

// Load coin balance
async function loadCoins() {
    try {
        const response = await fetch('/api/get-user-coins.php');
        const data = await response.json();
        
        if (data.success) {
            currentCoins = data.coins;
            document.getElementById('coinAmount').textContent = currentCoins;
        }
    } catch (error) {
        console.error('Error loading coins:', error);
    }
}

// Load and display power-ups
function displayPowerups() {
    const grid = document.getElementById('powerupsGrid');
    grid.innerHTML = '';
    
    POWERUPS.forEach(powerup => {
        const card = document.createElement('div');
        card.className = 'powerup-card';
        
        const canAfford = currentCoins >= powerup.price;
        
        card.innerHTML = `
            <div class="powerup-icon">${powerup.icon}</div>
            <div class="powerup-info">
                <h3 class="powerup-name">${powerup.name}</h3>
                <p class="powerup-description">${powerup.description}</p>
                <div class="powerup-price">
                    <span class="coin-icon-small">🪙</span>
                    <span>${powerup.price} coins</span>
                </div>
            </div>
            <button 
                class="purchase-btn ${canAfford ? '' : 'disabled'}" 
                data-type="${powerup.type}"
                data-price="${powerup.price}"
                ${!canAfford ? 'disabled' : ''}
            >
                ${canAfford ? 'Purchase' : 'Not Enough Coins'}
            </button>
        `;
        
        grid.appendChild(card);
    });
    
    // Add purchase handlers
    document.querySelectorAll('.purchase-btn:not(.disabled)').forEach(btn => {
        btn.addEventListener('click', async () => {
            const powerupType = btn.dataset.type;
            await purchasePowerup(powerupType);
        });
    });
}

// Purchase power-up
async function purchasePowerup(powerupType) {
    try {
        const response = await fetch('/api/purchase-powerup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                powerup_type: powerupType
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`✅ ${data.message}`);
            // Reload coins and update display
            await loadCoins();
            displayPowerups();
        } else {
            alert('Error: ' + (data.error || 'Failed to purchase'));
        }
    } catch (error) {
        console.error('Error purchasing power-up:', error);
        alert('Failed to purchase power-up. Please try again.');
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', async () => {
    await loadCoins();
    displayPowerups();
});
</script>

</body>
</html>

