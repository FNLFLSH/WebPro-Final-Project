/**
 * Pause functionality for the game
 */

let isPaused = false;
let pauseModal = null;
let resumeBtn = null;
let quitBtn = null;
let pauseBtn = null;

document.addEventListener('DOMContentLoaded', () => {
    pauseModal = document.getElementById('pauseModal');
    resumeBtn = document.getElementById('resumeBtn');
    quitBtn = document.getElementById('quitBtn');
    pauseBtn = document.getElementById('pauseBtn');

    if (!pauseModal || !resumeBtn || !quitBtn || !pauseBtn) {
        console.error('Pause elements not found');
        return;
    }

    // Pause button click
    pauseBtn.addEventListener('click', () => {
        pauseGame();
    });

    // Resume button
    resumeBtn.addEventListener('click', () => {
        resumeGame();
    });

    // Quit button
    quitBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to quit? Your progress will be lost.')) {
            window.location.href = '/frontend/home.php';
        }
    });

    // ESC key to pause/resume
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (isPaused) {
                resumeGame();
            } else {
                pauseGame();
            }
        }
    });
});

function pauseGame() {
    if (isPaused) return;
    
    isPaused = true;
    pauseModal.classList.remove('hidden');
    
    // Stop timer (access via window if available)
    if (typeof window !== 'undefined' && typeof window.stopTimer === 'function') {
        window.stopTimer();
    }
    
    // Disable player moves
    if (typeof window !== 'undefined' && window.allowPlayerMoves !== undefined) {
        window.allowPlayerMoves = false;
    }
    
    // Blur the game board to prevent cheating
    const puzzleBoard = document.getElementById('puzzle-board');
    if (puzzleBoard) {
        puzzleBoard.style.filter = 'blur(10px)';
        puzzleBoard.style.pointerEvents = 'none';
        puzzleBoard.style.opacity = '0.5';
    }
    
    // Also blur the entire puzzle container
    const puzzleContainer = document.querySelector('.puzzle-container');
    if (puzzleContainer) {
        puzzleContainer.style.filter = 'blur(10px)';
    }
}

function resumeGame() {
    if (!isPaused) return;
    
    isPaused = false;
    pauseModal.classList.add('hidden');
    
    // Resume timer (access via window if available)
    if (typeof window !== 'undefined' && typeof window.startTimer === 'function') {
        window.startTimer();
    }
    
    // Enable player moves
    if (typeof window !== 'undefined' && window.allowPlayerMoves !== undefined) {
        window.allowPlayerMoves = true;
    }
    
    // Restore game board
    const puzzleBoard = document.getElementById('puzzle-board');
    if (puzzleBoard) {
        puzzleBoard.style.filter = 'none';
        puzzleBoard.style.pointerEvents = 'auto';
        puzzleBoard.style.opacity = '1';
    }
    
    // Restore puzzle container
    const puzzleContainer = document.querySelector('.puzzle-container');
    if (puzzleContainer) {
        puzzleContainer.style.filter = 'none';
    }
}

// Export for use in game.js
window.pauseGame = pauseGame;
window.resumeGame = resumeGame;
window.isPaused = () => isPaused;

