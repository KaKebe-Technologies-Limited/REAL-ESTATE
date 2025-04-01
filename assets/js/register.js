document.addEventListener('DOMContentLoaded', function () {
    const forms = [
        { id: 'createOwner', url: 'http://localhost/REAL-ESTATE/owner_registration.php', formContainer: 'create-owner-form' },
        { id: 'createManager', url: 'http://localhost/REAL-ESTATE/manager_registration.php', formContainer: 'create-manager-form' }
    ];

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