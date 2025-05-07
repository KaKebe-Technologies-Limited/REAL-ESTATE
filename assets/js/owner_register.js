document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('ownerRegister');
    const successAlert = document.querySelector('.registration-success');
    const errorAlert = document.querySelector('.registration-error');
    // Payment information elements are already in the HTML

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

    // Check URL parameters for payment status messages
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        const error = urlParams.get('error');
        let errorMessage = 'An error occurred during registration. Please try again.';

        switch (error) {
            case 'payment_failed':
                errorMessage = 'Your payment was not successful. Please try again.';
                break;
            case 'registration_failed':
                errorMessage = 'Registration failed after payment. Please contact support.';
                break;
            case 'payment_verification_failed':
                errorMessage = 'Payment verification failed. Please try again or contact support.';
                break;
            case 'session_expired':
                errorMessage = 'Your session has expired. Please fill the form again.';
                break;
        }

        errorAlert.textContent = errorMessage;
        errorAlert.style.display = 'block';
    }

    if (urlParams.has('status') && urlParams.get('status') === 'payment_pending') {
        successAlert.textContent = 'Your payment is being processed. We will notify you once it is confirmed.';
        successAlert.style.display = 'block';
    }

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

        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

        // Send data to payment processing server
        fetch('process_owner_payment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show payment information
                if (data.redirect_url) {
                    // Redirect to Pesapal payment page
                    window.location.href = data.redirect_url;
                } else {
                    // Show success message
                    successAlert.textContent = data.message || 'Registration initiated. Please complete payment.';
                    successAlert.style.display = 'block';
                }
            } else {
                // Show error message
                errorAlert.textContent = data.message || 'An error occurred during registration. Please try again.';
                errorAlert.style.display = 'block';

                // Reset button
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorAlert.textContent = 'A network error occurred. Please try again later.';
            errorAlert.style.display = 'block';

            // Reset button
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
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
