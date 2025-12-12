/**
 * Universal Snowflake Animation
 * Can be used on any page with difficulty-based intensity
 */

class SnowflakeManager {
    constructor(options = {}) {
        this.intensity = options.intensity || 1; // 1 = normal, higher = more snow
        this.container = options.container || document.body;
        this.snowflakes = [];
        this.intervalId = null;
        this.isRunning = false;
        
        // Calculate spawn rate based on intensity
        // Base: 200ms interval, scales down with intensity
        this.spawnInterval = Math.max(50, 200 / this.intensity);
    }

    start() {
        if (this.isRunning) return;
        this.isRunning = true;
        this.createSnowflake();
        this.intervalId = setInterval(() => this.createSnowflake(), this.spawnInterval);
    }

    stop() {
        if (!this.isRunning) return;
        this.isRunning = false;
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }

    setIntensity(intensity) {
        this.intensity = Math.max(0.5, Math.min(5, intensity)); // Clamp between 0.5 and 5
        this.spawnInterval = Math.max(50, 200 / this.intensity);
        
        // Restart with new intensity
        if (this.isRunning) {
            this.stop();
            this.start();
        }
    }

    createSnowflake() {
        const snowflake = document.createElement("div");
        snowflake.classList.add("snowflake");
        snowflake.textContent = "❄";

        // Random position across screen width
        snowflake.style.left = Math.random() * window.innerWidth + "px";

        // Random animation speed (faster with higher intensity)
        const baseSpeed = 3;
        const speedVariation = 5;
        const speed = baseSpeed + Math.random() * speedVariation;
        snowflake.style.animationDuration = speed + "s";

        // Random size variation
        const size = 0.8 + Math.random() * 0.6; // 0.8x to 1.4x
        snowflake.style.fontSize = (1.4 * size) + "rem";

        // Random opacity
        snowflake.style.opacity = (0.7 + Math.random() * 0.3).toString();

        this.container.appendChild(snowflake);
        this.snowflakes.push(snowflake);

        // Remove after animation completes
        setTimeout(() => {
            if (snowflake.parentNode) {
                snowflake.parentNode.removeChild(snowflake);
            }
            const index = this.snowflakes.indexOf(snowflake);
            if (index > -1) {
                this.snowflakes.splice(index, 1);
            }
        }, (speed + 1) * 1000);
    }
}

// Auto-initialize if snow-container exists
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('snow-container');
    if (container) {
        // Default intensity (can be overridden by page-specific code)
        window.snowManager = new SnowflakeManager({
            container: container,
            intensity: 1
        });
        window.snowManager.start();
    }
});

