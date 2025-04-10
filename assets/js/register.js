document.addEventListener('DOMContentLoaded', function () {
    const forms = [
        { id: 'createOwner', url: 'owner_registration.php', formContainer: 'create-owner-form' },
        { id: 'createManager', url: 'manager_registration.php', formContainer: 'create-manager-form' }
    ];

    function showTemporaryAlert(message, duration = 1000) {
        // Create alert element
        const alertBox = document.createElement('div');
        alertBox.textContent = message;
        alertBox.style.position = 'fixed';
        alertBox.style.top = '20px';
        alertBox.style.left = '50%';
        alertBox.style.transform = 'translateX(-50%)';
        alertBox.style.padding = '10px 20px';
        alertBox.style.backgroundColor = '#f8d7da';
        alertBox.style.color = '#721c24';
        alertBox.style.border = '1px solid #f5c6cb';
        alertBox.style.borderRadius = '4px';
        alertBox.style.zIndex = '1000';
        
        // Add to document
        document.body.appendChild(alertBox);
        
        // Remove after duration
        setTimeout(() => {
            alertBox.remove();
            }, duration);
        }

    forms.forEach(({ id, url, formContainer }) => {
        const form = document.getElementById(id);
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault(); // Prevent the default form submission

                const formData = new FormData(this); // Collect form data

                fetch(url, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showTemporaryAlert(data.message); // Show success message
                            alert(data.message); // Show success message
                            document.getElementById(formContainer).style.display = 'none'; // Optionally hide the form
                            form.reset(); // Clear the form fields
                        } else {
                            alert(data.message); // Show error message
                        }
                    })
                    .catch(error => console.error('Error:', error)); // Handle network or server errors
            });
        } else {
            console.error(`Form with ID "${id}" not found.`);
        }
    });
});