document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('createOwner');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('http://localhost/REAL-ESTATE/owner_registration.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Optionally, hide the form after successful submission
                        document.getElementById('create-owner-form').style.display = 'none';
                        form.reset();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    } else {
        console.error('Form with ID "createOwner" not found.');
    }
});