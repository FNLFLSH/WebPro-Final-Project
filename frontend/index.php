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

    <!-- Countdown -->
    <div id="countdown" class="countdown hidden">3</div>

    <!-- Timer -->
    <div id="timer" class="timer">⏱️ 00:00</div>

    <!-- WIN POPUP MODAL -->
    <div id="winModal" class="modal hidden">
        <div class="modal-content">
            <span class="modal-close">&times;</span>

            <h2 class="modal-title">🎉 You Solved the Puzzle! 🎄</h2>
            <p class="modal-text">Amazing job — you're a true Christmas puzzle master! ❄️✨</p>

            <button id="modal-ok" class="modal-btn">Play Again</button>
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

    <!-- GAME LOGIC -->
    <script src="/public/assets/js/game.js"></script>

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
