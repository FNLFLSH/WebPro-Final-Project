const board = document.getElementById("puzzle-board");
let tiles = [];
let emptyIndex = 0;
let allowPlayerMoves = false; 
let timerInterval;
let timeElapsed = 0;
let moveCount = 0; // Track player moves for snow intensity
let gridSize = 4; // Default, will be loaded from level
let totalTiles = 16; // Default, will be calculated from gridSize
let currentSessionId = null; // Track current game session
let isTimerFrozen = false; // Track if timer is frozen
let freezeTimeout = null; // Track freeze timeout
let userPowerups = { freeze_timer: 0, smart_shuffle: 0 }; // Track available power-ups

// Make allowPlayerMoves accessible globally for pause.js
window.allowPlayerMoves = allowPlayerMoves;

//  TIMER FUNCTIONS
const timerDisplay = document.getElementById("timer");

function startTimer() {
    // Clear any existing timer
    if (timerInterval) {
        clearInterval(timerInterval);
    }
    
    timerInterval = setInterval(() => {
        // Don't increment if paused or frozen
        if (typeof window !== 'undefined' && window.isPaused && window.isPaused()) {
            return;
        }
        if (isTimerFrozen) {
            return;
        }
        
        timeElapsed++;
        let minutes = String(Math.floor(timeElapsed / 60)).padStart(2, "0");
        let seconds = String(timeElapsed % 60).padStart(2, "0");
        timerDisplay.textContent = `⏱️ ${minutes}:${seconds}`;
        
        // Update snow intensity based on time elapsed and level
        if (typeof updateSnowIntensity === 'function') {
            const level = typeof getCurrentLevel === 'function' ? getCurrentLevel() : 1;
            updateSnowIntensity(moveCount, timeElapsed, level);
        }
    }, 1000);
}

function stopTimer() {
    clearInterval(timerInterval);
}

// Export timer functions globally for pause.js
window.startTimer = startTimer;
window.stopTimer = stopTimer;

function resetTimer() {
    clearInterval(timerInterval);
    timeElapsed = 0;
    moveCount = 0; // Reset move count
    timerDisplay.textContent = "⏱️ 00:00";
    
    // Clear freeze state
    isTimerFrozen = false;
    if (freezeTimeout) {
        clearTimeout(freezeTimeout);
        freezeTimeout = null;
    }
    if (timerDisplay) {
        timerDisplay.classList.remove('frozen');
    }
    
    // Reset snow intensity to base level
    if (window.snowManager) {
        window.snowManager.setIntensity(1);
    }
}

//  COUNTDOWN BEFORE GAME START
function startCountdown(afterFinish) {
    const countdown = document.getElementById("countdown");
    let num = 3;

    countdown.textContent = num;
    countdown.classList.remove("hidden");
    countdown.classList.add("show");

    let interval = setInterval(() => {
        num--;

        if (num > 0) {
            countdown.textContent = num;
        } else {
            clearInterval(interval);

            countdown.textContent = "GO!";

            setTimeout(() => {
                countdown.classList.remove("show");
                countdown.classList.add("hidden");
                afterFinish();
            }, 600);
        }
    }, 1000);
}

//  CREATE BOARD - Dynamic size based on level
function initBoard() {
    resetTimer();
    allowPlayerMoves = false; // lock moves

    // Get grid size from level system
    if (typeof getCurrentGridSize === 'function') {
        gridSize = getCurrentGridSize();
    }
    totalTiles = gridSize * gridSize;
    emptyIndex = totalTiles - 1; // Last tile is empty

    tiles = [];
    board.innerHTML = "";

    // Update board CSS grid
    board.style.gridTemplateColumns = `repeat(${gridSize}, 1fr)`;
    board.style.width = `${Math.min(340 + (gridSize - 4) * 40, 600)}px`;
    board.style.height = `${Math.min(340 + (gridSize - 4) * 40, 600)}px`;

    // Create tiles
    for (let i = 0; i < totalTiles; i++) {
        const tile = document.createElement("div");

        if (i === emptyIndex) {
            tile.classList.add("empty");
            tiles.push(null);
        } else {
            tile.classList.add("tile");
            tile.textContent = i + 1;
            tile.addEventListener("click", () => moveTile(i, true)); // true = player click
            tiles.push(i + 1);
        }

        board.appendChild(tile);
    }

    startCountdown(async () => {
        // Start a new game session
        try {
            const response = await fetch('/api/start-session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    gridSize: gridSize
                })
            });
            
            const data = await response.json();
            if (data.success && data.sessionId) {
                currentSessionId = data.sessionId;
            }
        } catch (error) {
            console.error('Error starting session:', error);
        }
        
        shuffleBoard();
        setTimeout(() => {
            allowPlayerMoves = true; // players can now move
            window.allowPlayerMoves = true; // Update global reference
            startTimer();
        }, 300);
    });
}

//  CHECK ADJACENT - Works with any grid size
function isAdjacent(i1, i2) {
    const r1 = Math.floor(i1 / gridSize);
    const r2 = Math.floor(i2 / gridSize);
    const c1 = i1 % gridSize;
    const c2 = i2 % gridSize;
    return Math.abs(r1 - r2) + Math.abs(c1 - c2) === 1;
}

//  MOVE TILE
// playerMove = true → block until countdown done
// playerMove = false → shuffle allowed anytime
function moveTile(index, playerMove = false) {
    if (playerMove && !allowPlayerMoves) return;
    
    // Check if game is paused
    if (playerMove && typeof window !== 'undefined' && window.isPaused && window.isPaused()) {
        return;
    }

    if (isAdjacent(index, emptyIndex)) {
        const temp = tiles[index];
        tiles[index] = null;
        tiles[emptyIndex] = temp;

        updateBoard();
        if (playerMove) {
            moveCount++; // Increment move count for player moves
            // Update snow intensity based on moves, time, and level
            if (typeof updateSnowIntensity === 'function') {
                const level = typeof getCurrentLevel === 'function' ? getCurrentLevel() : 1;
                updateSnowIntensity(moveCount, timeElapsed, level);
            }
            checkWin(); // only player moves can trigger win
        }
    }
}

//  UPDATE UI
function updateBoard() {
    board.innerHTML = "";

    tiles.forEach((val, idx) => {
        const tile = document.createElement("div");

        if (val === null) {
            tile.className = "empty";
            emptyIndex = idx;
        } else {
            tile.className = "tile";
            tile.textContent = val;
            tile.addEventListener("click", () => moveTile(idx, true));
        }

        board.appendChild(tile);
    });

    // Update grid template
    board.style.gridTemplateColumns = `repeat(${gridSize}, 1fr)`;
}

//  SHUFFLE - Works with any grid size
function shuffleBoard() {
    let moves = gridSize * 50; // More moves for larger grids

    while (moves--) {
        const neighbors = [];
        for (let i = 0; i < totalTiles; i++) {
            if (isAdjacent(emptyIndex, i)) neighbors.push(i);
        }

        // shuffle MUST bypass player lock
        const move = neighbors[Math.floor(Math.random() * neighbors.length)];
        moveTile(move, false); // false = shuffle move, ALWAYS allowed
    }
}

//  CHECK WIN - Works with any grid size
function checkWin() {
    // Check all tiles except the last one (empty)
    for (let i = 0; i < totalTiles - 1; i++) {
        if (tiles[i] !== i + 1) return;
    }
    
    // Check that last tile is empty
    if (tiles[totalTiles - 1] !== null) return;

    stopTimer();
    handleWin();
}

// Track if level was advanced during this win
let levelWasAdvanced = false;

//  HANDLE WIN - Advance level and show modal
async function handleWin() {
    // Check if level was specified in URL (from level selection)
    const urlParams = new URLSearchParams(window.location.search);
    const levelParam = urlParams.get('level');
    
    const currentLevel = typeof getCurrentLevel === 'function' ? getCurrentLevel() : 1;
    const maxLevel = typeof MAX_LEVEL !== 'undefined' ? MAX_LEVEL : 8;
    
    // Mark session as completed
    if (currentSessionId) {
        try {
            await fetch('/api/update-session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    sessionId: currentSessionId,
                    board: tiles,
                    moves: moveCount,
                    completed: true
                })
            });
        } catch (error) {
            console.error('Error updating session:', error);
        }
    }
    
    // Award coins for completing level
    try {
        await fetch('/api/award-coins.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                coins: 50
            })
        });
        // Reload coin balance
        await loadCoinBalance();
    } catch (error) {
        console.error('Error awarding coins:', error);
    }
    
    // Unlock next level if not at max
    if (currentLevel < maxLevel) {
        try {
            // Unlock the next level
            const nextLevel = currentLevel + 1;
            await fetch('/api/unlock-level.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    level: nextLevel
                })
            });
        } catch (error) {
            console.error('Error unlocking level:', error);
        }
    }
    
    levelWasAdvanced = false;
    
    // Only advance if no level was specified in URL (playing from home/current level)
    if (!levelParam && currentLevel < maxLevel && typeof advanceLevel === 'function') {
        levelWasAdvanced = await advanceLevel();
    }
    
    showWinModal(currentLevel, levelWasAdvanced);
}

//  WIN POPUP LOGIC

const winModal = document.getElementById("winModal");
const closeBtn = document.querySelector(".modal-close");
const okBtn = document.getElementById("modal-ok");

function showWinModal(level, levelAdvanced) {
    const modalTitle = document.getElementById("winModalTitle");
    const modalText = document.getElementById("winModalText");
    const okBtn = document.getElementById("modal-ok");
    const homeBtn = document.getElementById("modal-home");
    
    if (levelAdvanced) {
        const newLevel = level + 1;
        modalTitle.textContent = `🎉 Level ${level} Complete! 🎄`;
        modalText.textContent = `Congratulations! You've advanced to Level ${newLevel}! The puzzle is getting bigger! ❄️✨`;
        okBtn.textContent = "Next Level";
    } else if (typeof MAX_LEVEL !== 'undefined' && level >= MAX_LEVEL) {
        modalTitle.textContent = `🏆 Master Puzzle Solver! 🎄`;
        modalText.textContent = `Incredible! You've completed all ${MAX_LEVEL} levels! You're a true Christmas puzzle master! ❄️✨`;
        okBtn.textContent = "Play Again";
    } else {
        modalTitle.textContent = `🎉 Level ${level} Complete! 🎄`;
        modalText.textContent = `Amazing job! Ready for the next challenge? ❄️✨`;
        okBtn.textContent = "Next Level";
    }
    
    // Show home button (it's always available)
    if (homeBtn) {
        homeBtn.style.display = "inline-block";
    }
    
    winModal.classList.remove("hidden");
}

closeBtn.onclick = () => winModal.classList.add("hidden");

okBtn.onclick = async () => {
    winModal.classList.add("hidden");
    
    // Check if we should advance to next level
    const urlParams = new URLSearchParams(window.location.search);
    const levelParam = urlParams.get('level');
    const currentLevel = typeof getCurrentLevel === 'function' ? getCurrentLevel() : 1;
    const maxLevel = typeof MAX_LEVEL !== 'undefined' ? MAX_LEVEL : 8;
    
    // If level was already advanced in handleWin() and no level param, reload to show next level
    if (levelWasAdvanced && !levelParam && currentLevel < maxLevel) {
        // Level was already advanced and saved, just reload
        window.location.reload();
        return;
    }
    
    // If no level param and we're not at max, try to advance
    if (!levelParam && currentLevel < maxLevel) {
        // Advance level and save
        if (typeof advanceLevel === 'function') {
            const advanced = await advanceLevel();
            if (advanced) {
                // Reload page to show next level
                window.location.reload();
                return;
            }
        }
    }
    
    // Just restart current level (or if we're at max level)
    initBoard();
};

// Home button handler
const homeBtn = document.getElementById("modal-home");
if (homeBtn) {
    homeBtn.onclick = () => {
        // Progress is already saved (session marked as completed, level unlocked)
        // Just navigate to home
        window.location.href = "/frontend/home.php";
    };
}


//  BUTTONS

document.getElementById("shuffleBtn").onclick = () => {
    // Don't allow shuffle if paused
    if (typeof window !== 'undefined' && window.isPaused && window.isPaused()) {
        return;
    }
    allowPlayerMoves = false;
    window.allowPlayerMoves = false;
    shuffleBoard();
    setTimeout(() => {
        allowPlayerMoves = true;
        window.allowPlayerMoves = true;
    }, 300);
};

document.getElementById("hintBtn").onclick = () => {
    // Don't allow hints if paused
    if (typeof window !== 'undefined' && window.isPaused && window.isPaused()) {
        return;
    }
    
    // Check if board is initialized
    if (!tiles || tiles.length === 0) {
        alert("Please start a game first!");
        return;
    }
    
    // Disable button temporarily to prevent spam
    const hintBtn = document.getElementById("hintBtn");
    hintBtn.disabled = true;
    hintBtn.textContent = "💡 Loading...";
    
    // Send current board state and grid size to hint API
    fetch("/api/hint.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            board: tiles,
            gridSize: gridSize
        })
    })
    .then(r => {
        if (!r.ok) {
            throw new Error(`HTTP error! status: ${r.status}`);
        }
        return r.json();
    })
    .then(d => {
        if (d.error) {
            console.error("Hint API error:", d.error);
            alert("Error: " + d.error);
            return;
        }
        
        if (d.hintAvailable && d.hintIndex !== undefined) {
            // Highlight the suggested tile
            const tileElements = board.querySelectorAll('.tile');
            if (tileElements[d.hintIndex]) {
                // Remove any existing animation
                tileElements[d.hintIndex].style.animation = '';
                // Force reflow
                void tileElements[d.hintIndex].offsetWidth;
                // Add pulse animation
                tileElements[d.hintIndex].style.animation = 'pulse 0.5s ease 3';
            } else {
                console.warn("Hint index", d.hintIndex, "not found in DOM. Total tiles:", tileElements.length);
            }
        } else {
            alert("No hints available at this time.");
        }
    })
    .catch(error => {
        console.error("Hint fetch error:", error);
        alert("Failed to get hint. Please check the browser console (F12) for details.");
    })
    .finally(() => {
        // Re-enable button
        hintBtn.disabled = false;
        hintBtn.textContent = "💡 Hint";
    });
};

//  INITIALIZE GAME - Load level first, then init board
document.addEventListener('DOMContentLoaded', async () => {
    // Check for level parameter in URL
    const urlParams = new URLSearchParams(window.location.search);
    const levelParam = urlParams.get('level');
    
    if (levelParam) {
        // Level specified in URL - use it
        const requestedLevel = parseInt(levelParam, 10);
        if (requestedLevel >= 1 && requestedLevel <= 8) {
            // Set level directly using levels.js functions
            if (typeof window !== 'undefined' && window.LEVEL_TO_SIZE) {
                // Set the level in levels.js
                if (typeof window.setLevel === 'function') {
                    await window.setLevel(requestedLevel);
                }
                gridSize = window.LEVEL_TO_SIZE[requestedLevel] || 4;
                totalTiles = gridSize * gridSize;
                emptyIndex = totalTiles - 1;
                
                // Update level display if function exists
                if (typeof updateLevelDisplay === 'function') {
                    updateLevelDisplay();
                }
            }
        }
    } else {
        // No level parameter - load user's current level
        if (typeof loadUserLevel === 'function') {
            await loadUserLevel();
            gridSize = typeof getCurrentGridSize === 'function' ? getCurrentGridSize() : 4;
            totalTiles = gridSize * gridSize;
            emptyIndex = totalTiles - 1;
        }
    }
    
    // Initialize the board with the correct size
    initBoard();
    
    // Load coin balance and power-ups
    await loadCoinBalance();
    await loadPowerups();
});

// Load coin balance
async function loadCoinBalance() {
    try {
        const response = await fetch('/api/get-user-coins.php');
        const data = await response.json();
        
        if (data.success) {
            const coinDisplay = document.getElementById('coinAmountGame');
            if (coinDisplay) {
                coinDisplay.textContent = data.coins;
            }
        }
    } catch (error) {
        console.error('Error loading coins:', error);
    }
}

// Load power-ups
async function loadPowerups() {
    try {
        const response = await fetch('/api/get-user-powerups.php');
        const data = await response.json();
        
        if (data.success) {
            userPowerups = data.powerups;
            updatePowerupButtons();
        }
    } catch (error) {
        console.error('Error loading power-ups:', error);
    }
}

// Update power-up button states
function updatePowerupButtons() {
    const freezeBtn = document.getElementById('freezeBtn');
    const smartShuffleBtn = document.getElementById('smartShuffleBtn');
    const freezeQuantity = document.getElementById('freezeQuantity');
    const smartShuffleQuantity = document.getElementById('smartShuffleQuantity');
    
    if (freezeBtn && freezeQuantity) {
        const quantity = userPowerups.freeze_timer || 0;
        freezeQuantity.textContent = quantity;
        freezeBtn.disabled = quantity === 0;
    }
    
    if (smartShuffleBtn && smartShuffleQuantity) {
        const quantity = userPowerups.smart_shuffle || 0;
        smartShuffleQuantity.textContent = quantity;
        smartShuffleBtn.disabled = quantity === 0;
    }
}

// Freeze timer for 30 seconds
async function freezeTimer() {
    if (isTimerFrozen || userPowerups.freeze_timer === 0) {
        return;
    }
    
    // Use power-up
    try {
        const response = await fetch('/api/use-powerup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                powerup_type: 'freeze_timer'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update local count
            userPowerups.freeze_timer = data.remaining;
            updatePowerupButtons();
            
            // Freeze timer
            isTimerFrozen = true;
            const timerDisplay = document.getElementById('timer');
            if (timerDisplay) {
                timerDisplay.classList.add('frozen');
            }
            
            // Resume after 30 seconds
            freezeTimeout = setTimeout(() => {
                isTimerFrozen = false;
                if (timerDisplay) {
                    timerDisplay.classList.remove('frozen');
                }
                freezeTimeout = null;
            }, 30000);
        } else {
            alert('Failed to use freeze timer: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error using freeze timer:', error);
        alert('Failed to use freeze timer. Please try again.');
    }
}

// Smart shuffle - reshuffle to easier state
async function smartShuffle() {
    if (userPowerups.smart_shuffle === 0) {
        return;
    }
    
    // Use power-up
    try {
        const response = await fetch('/api/use-powerup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                powerup_type: 'smart_shuffle'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update local count
            userPowerups.smart_shuffle = data.remaining;
            updatePowerupButtons();
            
            // Perform smart shuffle
            performSmartShuffle();
        } else {
            alert('Failed to use smart shuffle: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error using smart shuffle:', error);
        alert('Failed to use smart shuffle. Please try again.');
    }
}

// Perform smart shuffle algorithm
function performSmartShuffle() {
    // Save current state
    const currentTiles = [...tiles];
    const currentEmptyIndex = emptyIndex;
    
    // Generate multiple shuffle attempts and pick the easiest one
    const solvedState = [];
    for (let i = 1; i < totalTiles; i++) {
        solvedState.push(i);
    }
    solvedState.push(null);
    
    let bestShuffle = null;
    let bestDistance = Infinity;
    
    // Try 20 different shuffles (using valid moves to ensure solvability)
    for (let attempt = 0; attempt < 20; attempt++) {
        // Reset to solved state
        tiles = [...solvedState];
        emptyIndex = totalTiles - 1;
        
        // Perform a shorter shuffle (fewer moves = easier)
        const shuffleMoves = Math.floor(gridSize * 20 + Math.random() * gridSize * 10); // 20-30 moves per grid size
        
        for (let move = 0; move < shuffleMoves; move++) {
            const neighbors = [];
            for (let i = 0; i < totalTiles; i++) {
                if (isAdjacent(emptyIndex, i)) neighbors.push(i);
            }
            
            if (neighbors.length > 0) {
                const moveIndex = neighbors[Math.floor(Math.random() * neighbors.length)];
                // Use moveTile without playerMove flag to shuffle
                const temp = tiles[moveIndex];
                tiles[moveIndex] = null;
                tiles[emptyIndex] = temp;
                emptyIndex = moveIndex;
            }
        }
        
        // Calculate Manhattan distance to solved state
        const distance = calculateManhattanDistance(tiles, solvedState);
        
        if (distance < bestDistance) {
            bestDistance = distance;
            bestShuffle = [...tiles];
        }
    }
    
    // Apply the best shuffle
    if (bestShuffle) {
        tiles = bestShuffle;
        emptyIndex = tiles.indexOf(null);
        updateBoard();
    } else {
        // Fallback: restore original state
        tiles = currentTiles;
        emptyIndex = currentEmptyIndex;
        updateBoard();
    }
}

// Calculate Manhattan distance between two states
function calculateManhattanDistance(state1, state2) {
    let distance = 0;
    const size = Math.sqrt(state1.length);
    
    for (let i = 0; i < state1.length; i++) {
        if (state1[i] === null) continue;
        
        const pos1 = i;
        const pos2 = state2.indexOf(state1[i]);
        
        const row1 = Math.floor(pos1 / size);
        const col1 = pos1 % size;
        const row2 = Math.floor(pos2 / size);
        const col2 = pos2 % size;
        
        distance += Math.abs(row1 - row2) + Math.abs(col1 - col2);
    }
    
    return distance;
}

// Add event listeners for power-up buttons
document.addEventListener('DOMContentLoaded', () => {
    const freezeBtn = document.getElementById('freezeBtn');
    const smartShuffleBtn = document.getElementById('smartShuffleBtn');
    
    if (freezeBtn) {
        freezeBtn.addEventListener('click', freezeTimer);
    }
    
    if (smartShuffleBtn) {
        smartShuffleBtn.addEventListener('click', smartShuffle);
    }
});
