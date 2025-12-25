const container = document.querySelector('.main-container');
const loginLink = document.querySelectorAll('.SingInlink'); // can be multiple
const registerLink = document.querySelectorAll('.SingUplink'); // can be multiple
const forgotLink = document.querySelector('.show-forgot');
const backToLoginLinks = document.querySelectorAll('.back-to-login');

function showLogin() {
    container.classList.remove('active', 'forgot-active');
}

function showRegister() {
    container.classList.add('active');
    container.classList.remove('forgot-active');
}

function showForgot() {
    container.classList.add('forgot-active');
    container.classList.remove('active');
}

// Login links
loginLink.forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        showLogin();
    });
});

// Register links
registerLink.forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        showRegister();
    });
});

// Forgot password link
if (forgotLink) {
    forgotLink.addEventListener('click', (e) => {
        e.preventDefault();
        showForgot();
    });
}

// Back to login from forgot password
backToLoginLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        showLogin();
    });
});

