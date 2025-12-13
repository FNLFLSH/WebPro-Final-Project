<?php
    // Enable PHP error display for debugging (remove in production)
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Christmas Fifteen Puzzle</title>

    <!-- Game Page CSS -->
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body>

    <!-- Background Christmas Music -->
    <audio id="bg-music" src="/public/assets/audio/christmas.mp3" loop></audio>

    <!-- Music Toggle Button -->
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

    <!-- Pause Button -->
    <button id="pauseBtn" class="pause-btn">⏸️ Pause</button>

    <!-- Snowflakes container -->
    <div id="snow-container"></div>

    <!-- Countdown -->
    <div id="countdown" class="countdown hidden">3</div>

    <!-- Level Display -->
    <div id="levelDisplay" class="level-display">Level 1</div>

    <!-- Coin Display -->
    <div id="coinDisplay" class="coin-display-game">
        <span class="coin-icon">🪙</span>
        <span id="coinAmountGame">0</span>
    </div>

    <!-- Timer -->
    <div id="timer" class="timer">⏱️ 00:00</div>

    <!-- WIN POPUP MODAL -->
    <div id="winModal" class="modal hidden">
        <div class="modal-content">
            <span class="modal-close">&times;</span>

            <h2 class="modal-title" id="winModalTitle">🎉 You Solved the Puzzle! 🎄</h2>
            <p class="modal-text" id="winModalText">Amazing job — you're a true Christmas puzzle master! ❄️✨</p>

            <div class="modal-buttons">
                <button id="modal-ok" class="modal-btn">Next Level</button>
                <button id="modal-home" class="modal-btn modal-btn-secondary">🏠 Go Home</button>
            </div>
        </div>
    </div>

    <!-- PAUSE MODAL -->
    <div id="pauseModal" class="modal hidden">
        <div class="modal-content pause-modal-content">
            <h2 class="modal-title">⏸️ Game Paused</h2>
            
            <!-- In-Game Rules Section -->
            <div class="pause-rules">
                <h3 class="rules-title">📖 How to Play</h3>
                <ul class="rules-list">
                    <li>Click tiles adjacent to the empty space to move them</li>
                    <li>Arrange numbers 1-15 in order from left to right, top to bottom</li>
                    <li>The empty space should be in the bottom-right corner when solved</li>
                    <li>Use the hint button (💡) if you need help</li>
                    <li>Try to solve in as few moves as possible!</li>
                </ul>
            </div>

            <!-- Pause Actions -->
            <div class="pause-actions">
                <button id="resumeBtn" class="modal-btn resume-btn">▶️ Resume</button>
                <button id="quitBtn" class="modal-btn quit-btn">🚪 Quit Game</button>
            </div>
        </div>
    </div>

    <!-- GAME TITLE -->
    <h1 class="title"> Christmas Fifteen Puzzle </h1>

    <!-- PUZZLE BOARD -->
    <div class="puzzle-container">
        <div id="puzzle-board"></div>
    </div>

    <!-- BUTTONS -->
    <div class="controls">
        <button id="shuffleBtn">🔀 Shuffle</button>
        <button id="hintBtn">💡 Hint</button>
    </div>
    
    <!-- POWER-UPS -->
    <div class="powerups-section">
        <h3 class="powerups-title">Power-ups</h3>
        <div class="powerups-buttons">
            <button id="freezeBtn" class="powerup-btn" disabled>
                <span class="powerup-icon">❄️</span>
                <span class="powerup-name">Freeze Timer</span>
                <span class="powerup-quantity" id="freezeQuantity">0</span>
            </button>
            <button id="smartShuffleBtn" class="powerup-btn" disabled>
                <span class="powerup-icon">🔀</span>
                <span class="powerup-name">Smart Shuffle</span>
                <span class="powerup-quantity" id="smartShuffleQuantity">0</span>
            </button>
        </div>
    </div>

    <!-- Universal Snowflakes -->
    <script src="/public/assets/js/snowflakes.js"></script>
    
    <!-- Dark Mode & Pause Scripts (load before game.js) -->
    <script src="/public/assets/js/darkmode.js"></script>
    <script src="/public/assets/js/pause.js"></script>
    
    <!-- Level System (load before game.js) -->
    <script src="/public/assets/js/levels.js"></script>
    
    <!-- GAME LOGIC -->
    <script src="/public/assets/js/game.js"></script>
    
    <!-- Difficulty-based snow intensity -->
    <script>
        // Initialize snow with base intensity
        document.addEventListener('DOMContentLoaded', () => {
            if (window.snowManager) {
                // Start with normal intensity
                window.snowManager.setIntensity(1);
            }
        });
        
        // Increase snow intensity based on game difficulty/progress and level
        // This will be called from game.js as the game progresses
        function updateSnowIntensity(moves, timeElapsed, level = 1) {
            if (!window.snowManager) return;
            
            // Base intensity increases with:
            // - Level (higher level = more snow)
            // - Number of moves (more moves = harder puzzle)
            // - Time elapsed (longer time = more challenging)
            let intensity = 1;
            
            // Base intensity from level (each level adds 0.3 intensity)
            intensity += (level - 1) * 0.3;
            
            // Increase intensity based on moves (every 20 moves = +0.2 intensity)
            intensity += Math.floor(moves / 20) * 0.2;
            
            // Increase intensity based on time (every 30 seconds = +0.1 intensity)
            intensity += Math.floor(timeElapsed / 30) * 0.1;
            
            // Cap at maximum intensity of 5 (higher for higher levels)
            intensity = Math.min(5, intensity);
            
            window.snowManager.setIntensity(intensity);
        }
    </script>

    <!--  MUSIC LOGIC -->
    <script>
        const music = document.getElementById("bg-music");
        const toggleBtn = document.getElementById("musicToggle");
        let musicOn= false;

        toggleBtn.addEventListener("click", () => {
            if (!musicOn) {
                music.play();
                toggleBtn.textContent = "🔊";
                musicOn = true;
            } else {
                music.pause();
                toggleBtn.textContent = "🔇";
                musicOn = false;
            }
        });

        document.addEventListener("click", () => {
            if (!musicOn) {
                music.play();
                toggleBtn.textContent = "🔊";
                musicOn = true;
            }
        }, { once: true });
    </script>

</body>
</html>
