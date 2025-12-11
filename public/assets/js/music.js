const music = document.getElementById("bg-music");
const toggleBtn = document.getElementById("musicToggle");

toggleBtn.addEventListener("click", () => {
    if (music.muted) {
        music.muted = false;
        music.volume = 0.6;
        music.play().catch(() => {});
        toggleBtn.textContent = "🔊";
    } else {
        music.muted = true;
        toggleBtn.textContent = "🔇";
    }
});
