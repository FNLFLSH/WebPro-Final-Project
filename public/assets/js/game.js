const board = document.getElementById("puzzle-board");
let tiles = [];
let emptyIndex = 0;
let allowPlayerMoves = false; 
let timerInterval;
let timeElapsed = 0;
let moveCount = 0; // Track player moves for snow intensity
let gridSize = 4; // Default, will be loaded from level
let totalTiles = 16; // Default, will be calculated from gridSize

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
        // Don't increment if paused
        if (typeof window !== 'undefined' && window.isPaused && window.isPaused()) {
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

    startCountdown(() => {
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

//  HANDLE WIN - Advance level and show modal
async function handleWin() {
    const currentLevel = typeof getCurrentLevel === 'function' ? getCurrentLevel() : 1;
    const maxLevel = typeof MAX_LEVEL !== 'undefined' ? MAX_LEVEL : 8;
    
    let levelAdvanced = false;
    if (currentLevel < maxLevel && typeof advanceLevel === 'function') {
        levelAdvanced = await advanceLevel();
    }
    
    showWinModal(currentLevel, levelAdvanced);
}

//  WIN POPUP LOGIC

const winModal = document.getElementById("winModal");
const closeBtn = document.querySelector(".modal-close");
const okBtn = document.getElementById("modal-ok");

function showWinModal(level, levelAdvanced) {
    const modalTitle = document.getElementById("winModalTitle");
    const modalText = document.getElementById("winModalText");
    const okBtn = document.getElementById("modal-ok");
    
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
    
    winModal.classList.remove("hidden");
}

closeBtn.onclick = () => winModal.classList.add("hidden");

okBtn.onclick = () => {
    winModal.classList.add("hidden");
    initBoard();
};


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
    .then(r => r.json())
    .then(d => {
        if (d.hintAvailable && d.hintIndex !== undefined) {
            // Highlight the suggested tile
            const tiles = board.querySelectorAll('.tile');
            if (tiles[d.hintIndex]) {
                tiles[d.hintIndex].style.animation = 'pulse 0.5s ease 3';
            }
        } else {
            alert("No hints available at this time.");
        }
    });
};

//  INITIALIZE GAME - Load level first, then init board
document.addEventListener('DOMContentLoaded', async () => {
    // Load user's current level
    if (typeof loadUserLevel === 'function') {
        await loadUserLevel();
        gridSize = typeof getCurrentGridSize === 'function' ? getCurrentGridSize() : 4;
        totalTiles = gridSize * gridSize;
        emptyIndex = totalTiles - 1;
    }
    
    // Initialize the board with the correct size
    initBoard();
});
