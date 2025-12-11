<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Christmas Puzzle</title>

    <!-- Christmas Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Mountains+of+Christmas:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Snowburst+One&display=swap" rel="stylesheet">

    <!-- Base Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="/public/assets/css/login.css">
</head>
<body>

<!-- BACKGROUND MUSIC -->
<audio id="bg-music" src="/public/assets/audio/christmas.mp3" loop muted></audio>

<!-- Music toggle button -->
<button id="musicToggle" class="music-btn">🔇</button>

<!-- Floating Clouds -->
<div class="cloud cloud1"></div>
<div class="cloud cloud2"></div>
<div class="cloud cloud3"></div>
<div class="cloud cloud4"></div>

<!-- Snowflakes container -->
<div id="snow-container"></div>

<div class="auth-container">

    <!-- Santa image (optional) -->
    <img src="/public/assets/img/santa.png" class="santa-pic" alt="Santa">

    <h1 class="title"> Welcome to the Christmas Puzzle </h1>

    <div class="form-toggle">
        <button id="showLogin" class="active">Login</button>
        <button id="showRegister">Register</button>
    </div>

    <!-- LOGIN FORM -->
    <form id="loginForm" class="auth-form">
        <input type="text" id="login_username" placeholder="Username" required>
        <input type="password" id="login_password" placeholder="Password" required>
        <button type="submit">Login</button>
        <p class="message" id="loginMessage"></p>
    </form>

    <!-- REGISTER FORM -->
    <form id="registerForm" class="auth-form hidden">
        <input type="text" id="reg_username" placeholder="Username" required>
        <input type="email" id="reg_email" placeholder="Email" required>
        <input type="password" id="reg_password" placeholder="Password" required>
        <button type="submit">Create Account</button>
        <p class="message" id="registerMessage"></p>
    </form>
</div>

<!-- Login JS -->
<script src="/public/assets/js/login.js"></script>

<!-- Music JS -->
<script src="/public/assets/js/music.js"></script>

<!-- Snowflake Animation -->
<script>
function createSnowflake() {
    const snowflake = document.createElement("div");
    snowflake.classList.add("snowflake");
    snowflake.textContent = "❄";

    // random position
    snowflake.style.left = Math.random() * window.innerWidth + "px";

    // random animation speed
    snowflake.style.animationDuration = (3 + Math.random() * 5) + "s";

    document.body.appendChild(snowflake);

    // remove after falling
    setTimeout(() => snowflake.remove(), 8000);
}

setInterval(createSnowflake, 200);
</script>

</body>
</html>
