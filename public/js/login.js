function showReset() {
    const loginForm = document.getElementById('login-form');
    const resetForm = document.getElementById('resetPassword-form');

    loginForm.classList.add('hidden');
    resetForm.classList.remove('hidden');
}

// Go back to login form
function goBack() {
    const loginForm = document.getElementById('login-form');
    const resetForm = document.getElementById('resetPassword-form');

    resetForm.classList.add('hidden');
    loginForm.classList.remove('hidden');
}

// Show/Hide input Password
document.addEventListener("DOMContentLoaded", () => {
    const togglePassword = document.querySelector(".password-container span i");
    const passwordInput = document.querySelector(".password-container input");

    togglePassword.addEventListener("click", () => {
        const isPassword = passwordInput.getAttribute("type") === "password";

        // Toggle input type
        passwordInput.setAttribute("type", isPassword ? "text" : "password");

        // Toggle icon
        togglePassword.classList.toggle("fa-eye");
        togglePassword.classList.toggle("fa-eye-slash");
    });
    });