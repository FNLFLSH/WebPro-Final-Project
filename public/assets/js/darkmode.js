/**
 * Dark Mode Toggle
 */

let isDarkMode = false;

document.addEventListener('DOMContentLoaded', () => {
    const darkModeToggle = document.getElementById('darkModeToggle');
    
    if (!darkModeToggle) {
        console.error('Dark mode toggle button not found');
        return;
    }

    // Load saved preference
    const savedMode = localStorage.getItem('darkMode');
    if (savedMode === 'true') {
        enableDarkMode();
    }

    // Toggle button click
    darkModeToggle.addEventListener('click', () => {
        toggleDarkMode();
    });
});

function toggleDarkMode() {
    if (isDarkMode) {
        disableDarkMode();
    } else {
        enableDarkMode();
    }
}

function enableDarkMode() {
    isDarkMode = true;
    document.body.classList.add('dark-mode');
    // Tree icon changes automatically via CSS
    localStorage.setItem('darkMode', 'true');
}

function disableDarkMode() {
    isDarkMode = false;
    document.body.classList.remove('dark-mode');
    // Tree icon changes automatically via CSS
    localStorage.setItem('darkMode', 'false');
}

// Export for use elsewhere if needed
window.toggleDarkMode = toggleDarkMode;
window.isDarkMode = () => isDarkMode;

