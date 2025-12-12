/**
 * Level System for Christmas Puzzle
 * Maps levels to grid sizes and manages level progression
 */

// Level to grid size mapping
const LEVEL_TO_SIZE = {
    1: 3,   // Level 1: 3x3 (8 tiles)
    2: 4,   // Level 2: 4x4 (15 tiles)
    3: 5,   // Level 3: 5x5 (24 tiles)
    4: 6,   // Level 4: 6x6 (35 tiles)
    5: 7,   // Level 5: 7x7 (48 tiles)
    6: 8,   // Level 6: 8x8 (63 tiles)
    7: 9,   // Level 7: 9x9 (80 tiles)
    8: 10,  // Level 8: 10x10 (99 tiles)
};

// Maximum level
const MAX_LEVEL = 8;

let currentLevel = 1;
let currentGridSize = 3;

/**
 * Get grid size for a given level
 */
function getGridSizeForLevel(level) {
    return LEVEL_TO_SIZE[level] || LEVEL_TO_SIZE[1];
}

/**
 * Load user's current level from server
 */
async function loadUserLevel() {
    try {
        const response = await fetch('/api/get-user-level.php');
        const data = await response.json();
        
        if (data.success && data.level) {
            currentLevel = Math.max(1, Math.min(MAX_LEVEL, data.level));
            currentGridSize = getGridSizeForLevel(currentLevel);
            updateLevelDisplay();
            return currentLevel;
        }
    } catch (error) {
        console.error('Error loading user level:', error);
    }
    
    // Default to level 1 if loading fails
    currentLevel = 1;
    currentGridSize = 3;
    updateLevelDisplay();
    return currentLevel;
}

/**
 * Save user's current level to server
 */
async function saveUserLevel(level) {
    try {
        const response = await fetch('/api/save-user-level.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ level: level })
        });
        
        const data = await response.json();
        return data.success;
    } catch (error) {
        console.error('Error saving user level:', error);
        return false;
    }
}

/**
 * Advance to next level
 */
async function advanceLevel() {
    if (currentLevel < MAX_LEVEL) {
        currentLevel++;
        currentGridSize = getGridSizeForLevel(currentLevel);
        await saveUserLevel(currentLevel);
        updateLevelDisplay();
        return true;
    }
    return false; // Already at max level
}

/**
 * Update level display in UI
 */
function updateLevelDisplay() {
    const levelDisplay = document.getElementById('levelDisplay');
    if (levelDisplay) {
        levelDisplay.textContent = `Level ${currentLevel}`;
    }
}

/**
 * Get current level
 */
function getCurrentLevel() {
    return currentLevel;
}

/**
 * Get current grid size
 */
function getCurrentGridSize() {
    return currentGridSize;
}

// Export for use in game.js
window.loadUserLevel = loadUserLevel;
window.saveUserLevel = saveUserLevel;
window.advanceLevel = advanceLevel;
window.getCurrentLevel = getCurrentLevel;
window.getCurrentGridSize = getCurrentGridSize;
window.LEVEL_TO_SIZE = LEVEL_TO_SIZE;
window.MAX_LEVEL = MAX_LEVEL;

