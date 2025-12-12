console.log("login.js loaded");

// Toggle Buttons
const showLogin = document.getElementById("showLogin");
const showRegister = document.getElementById("showRegister");

// Forms
const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");

// SWITCH TO LOGIN UI
showLogin.addEventListener("click", () => {
    loginForm.classList.remove("hidden");
    registerForm.classList.add("hidden");
    showLogin.classList.add("active");
    showRegister.classList.remove("active");
});

// SWITCH TO REGISTER UI
showRegister.addEventListener("click", () => {
    loginForm.classList.add("hidden");
    registerForm.classList.remove("hidden");
    showRegister.classList.add("active");
    showLogin.classList.remove("active");
});

// LOGIN REQUEST (JSON)
loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const username = document.getElementById("login_username").value;
    const password = document.getElementById("login_password").value;

    const response = await fetch("/api/login.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ username, password })
    });

    const data = await response.json();
    document.getElementById("loginMessage").textContent = data.error || data.message;

    if (!data.error) {
        // Successful login - redirect to home page
        window.location.href = "/frontend/home.php";
    }
});

// REGISTER REQUEST (JSON)
registerForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const username = document.getElementById("reg_username").value;
    const email = document.getElementById("reg_email").value;
    const password = document.getElementById("reg_password").value;

    const response = await fetch("/api/register.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ username, email, password })
    });

    const data = await response.json();
    document.getElementById("registerMessage").textContent = data.error || data.message;

    if (!data.error) {
        alert("Account created! Please log in.");

        // Switch back to login UI automatically
        showLogin.click();
    }
});
