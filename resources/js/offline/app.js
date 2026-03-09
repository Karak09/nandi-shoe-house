// resources/js/offline/app.js

// 1. Setup Axios/Fetch CSRF Token globally for Laravel
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        window.axios = require('axios');
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
    } else {
        console.error('CSRF token not found');
    }

    // 2. Add common logic here (e.g., closing flash messages, dropdowns)
    const alertCloseBtns = document.querySelectorAll('.close-alert');
    alertCloseBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            this.parentElement.style.display = 'none';
        });
    });
});