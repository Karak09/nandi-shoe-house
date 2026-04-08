// resources/js/offline/app.js

// 1. Setup Axios/Fetch CSRF Token globally for Laravel
// resources/js/offline/app.js

document.addEventListener('DOMContentLoaded', () => {
    // 1. Check CSRF Token existence
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.warn('CSRF token not found on this page.');
    }

    // 2. Alert Close Logic
    const alertCloseBtns = document.querySelectorAll('.close-alert');
    alertCloseBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            this.parentElement.style.display = 'none';
        });
    });
});


// REGISTRATION PAGE LOGIC
document.addEventListener('DOMContentLoaded', () => {
    const regForm = document.getElementById('registrationForm');
    
    if (regForm) {
        // Handle Profile Image Upload
        document.getElementById('imageFile').addEventListener('change', function() {
            encodeBase64(this, 'image_doc_base64', 'image_file_name');
            if(this.files[0]) document.getElementById('avatarText').innerText = 'Selected';
        });

        // Handle Proof Image Upload
        document.getElementById('proofFile').addEventListener('change', function() {
            encodeBase64(this, 'proof_doc_base64', 'proof_file_name');
        });

        function encodeBase64(element, base64Id, fileNameId) {
            const file = element.files[0];
            if (file) {
                document.getElementById(fileNameId).value = file.name;
                const reader = new FileReader();
                reader.onloadend = function() {
                    document.getElementById(base64Id).value = reader.result;
                }
                reader.readAsDataURL(file);
            }
        }
    }
});