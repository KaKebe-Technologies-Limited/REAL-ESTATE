document.addEventListener('DOMContentLoaded', function() {
    const inquiryForm = document.getElementById('property-inquiry-form');
    
    if (inquiryForm) {
        inquiryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitButton.disabled = true;
            
            // Clear previous alerts
            const previousAlerts = document.querySelectorAll('.alert');
            previousAlerts.forEach(alert => alert.remove());
            
            // Get form data
            const formData = new FormData(this);
            
            // Send AJAX request
            fetch('send_property_inquiry.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Reset button
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
                
                // Create alert element
                const alertDiv = document.createElement('div');
                alertDiv.className = data.success ? 'alert alert-success' : 'alert alert-danger';
                
                // Set alert content
                if (data.success) {
                    alertDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + (data.message || 'Your message has been sent successfully! We\'ll get back to you soon.');
                    // Reset form on success
                    inquiryForm.reset();
                    
                    // Pre-fill the message field with the default message
                    const propertyName = document.querySelector('input[name="property_name"]').value;
                    const messageField = inquiryForm.querySelector('textarea[name="message"]');
                    if (messageField && propertyName) {
                        messageField.value = 'I am interested in ' + propertyName + '. Please provide more information.';
                    }
                } else {
                    let errorMessage = 'There was an error sending your message. Please try again.';
                    if (data.errors && data.errors.length > 0) {
                        errorMessage = data.errors.join('<br>');
                    }
                    alertDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + errorMessage;
                }
                
                // Insert alert before the form
                inquiryForm.parentNode.insertBefore(alertDiv, inquiryForm);
                
                // Scroll to the alert
                alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Auto-remove alert after 5 seconds on success
                if (data.success) {
                    setTimeout(() => {
                        alertDiv.style.opacity = '0';
                        setTimeout(() => alertDiv.remove(), 500);
                    }, 5000);
                }
            })
            .catch(error => {
                // Reset button
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
                
                // Create error alert
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> An unexpected error occurred. Please try again later.';
                
                // Insert alert before the form
                inquiryForm.parentNode.insertBefore(alertDiv, inquiryForm);
                
                console.error('Error:', error);
            });
        });
    }
});
