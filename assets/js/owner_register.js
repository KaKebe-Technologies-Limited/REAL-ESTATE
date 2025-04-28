document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('ownerRegister');
    const successAlert = document.querySelector('.registration-success');
    const errorAlert = document.querySelector('.registration-error');
    
    // Password validation
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Passwords don't match");
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('change', validatePassword);
    confirmPassword.addEventListener('keyup', validatePassword);
    
    // Form submission
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Hide any existing alerts
        successAlert.style.display = 'none';
        errorAlert.style.display = 'none';
        
        // Validate passwords match
        if (password.value !== confirmPassword.value) {
            errorAlert.textContent = "Passwords don't match";
            errorAlert.style.display = 'block';
            return;
        }
        
        // Create FormData object
        const formData = new FormData(this);
        
        // Remove confirm_password field as it's not needed in the backend
        formData.delete('confirm_password');
        // Remove terms field as it's only for frontend validation
        formData.delete('terms');
        
        // Send data to server
        fetch('owner_registration.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                successAlert.textContent = data.message || 'Registration successful! Redirecting to login page...';
                successAlert.style.display = 'block';
                
                // Reset form
                registerForm.reset();
                
                // Redirect to login page after 3 seconds
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 3000);
            } else {
                // Show error message
                errorAlert.textContent = data.message || 'An error occurred during registration. Please try again.';
                errorAlert.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorAlert.textContent = 'A network error occurred. Please try again later.';
            errorAlert.style.display = 'block';
        });
    });
    
    // Client-side validation for phone number (must start with +)
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('blur', function() {
        if (this.value && !this.value.startsWith('+')) {
            this.value = '+' + this.value;
        }
    });
});
