const board = document.getElementById("puzzle-board");
let tiles = [];
let emptyIndex = 15;
let allowPlayerMoves = false; 
let timerInterval;
let timeElapsed = 0;

//  TIMER FUNCTIONS
const timerDisplay = document.getElementById("timer");

function startTimer() {
    timerInterval = setInterval(() => {
        timeElapsed++;
        let minutes = String(Math.floor(timeElapsed / 60)).padStart(2, "0");
        let seconds = String(timeElapsed % 60).padStart(2, "0");
        timerDisplay.textContent = `⏱️ ${minutes}:${seconds}`;
    }, 1000);
}

function stopTimer() {
    clearInterval(timerInterval);
}

function resetTimer() {
    clearInterval(timerInterval);
    timeElapsed = 0;
    timerDisplay.textContent = "⏱️ 00:00";
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

//  CREATE BOARD
function initBoard() {
    resetTimer();
    allowPlayerMoves = false; // lock moves

    tiles = [];
    board.innerHTML = "";

    for (let i = 0; i < 16; i++) {
        const tile = document.createElement("div");

        if (i === 15) {
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
            startTimer();
        }, 300);
    });
}

//  CHECK ADJACENT
function isAdjacent(i1, i2) {
    const r1 = Math.floor(i1 / 4);
    const r2 = Math.floor(i2 / 4);
    const c1 = i1 % 4;
    const c2 = i2 % 4;
    return Math.abs(r1 - r2) + Math.abs(c1 - c2) === 1;
}

//  MOVE TILE
// playerMove = true → block until countdown done
// playerMove = false → shuffle allowed anytime
function moveTile(index, playerMove = false) {
    if (playerMove && !allowPlayerMoves) return;

    if (isAdjacent(index, emptyIndex)) {
        const temp = tiles[index];
        tiles[index] = null;
        tiles[emptyIndex] = temp;

        updateBoard();
        if (playerMove) checkWin(); // only player moves can trigger win
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
}

//  SHUFFLE 
function shuffleBoard() {
    let moves = 200;

    while (moves--) {
        const neighbors = [];
        for (let i = 0; i < 16; i++) {
            if (isAdjacent(emptyIndex, i)) neighbors.push(i);
        }

        // shuffle MUST bypass player lock
        const move = neighbors[Math.floor(Math.random() * neighbors.length)];
        moveTile(move, false); // false = shuffle move, ALWAYS allowed
    }
}

//  CHECK WIN
function checkWin() {
    for (let i = 0; i < 15; i++) {
        if (tiles[i] !== i + 1) return;
    }

    stopTimer();
    showWinModal();
}


//  WIN POPUP LOGIC

const winModal = document.getElementById("winModal");
const closeBtn = document.querySelector(".modal-close");
const okBtn = document.getElementById("modal-ok");

function showWinModal() {
    winModal.classList.remove("hidden");
}

closeBtn.onclick = () => winModal.classList.add("hidden");

okBtn.onclick = () => {
    winModal.classList.add("hidden");
    initBoard();
};


//  BUTTONS

document.getElementById("shuffleBtn").onclick = () => {
    allowPlayerMoves = false;
    shuffleBoard();
    setTimeout(() => allowPlayerMoves = true, 300);
};

document.getElementById("hintBtn").onclick = () => {
    fetch("/api/hint.php")
        .then(r => r.json())
        .then(d => alert("Hint: " + d.hint));
};

//  START GAME
initBoard();
