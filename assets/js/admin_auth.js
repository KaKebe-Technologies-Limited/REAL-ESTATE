document.getElementById('adminLogin').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Try to navigate to the admin dashboard with error handling
            try {
                console.log('Login successful, redirecting to admin dashboard...');
                window.location.href = 'adminDashboard.php';

                // Set a timeout to check if the redirect worked
                setTimeout(function() {
                    // If we're still on the same page after 2 seconds, try alternative URLs
                    if (document.title.includes('Login')) {
                        console.log('Redirect failed, trying alternative URL...');
                        window.location.href = 'admindashboard.php';
                    }
                }, 2000);
            } catch (e) {
                console.error('Redirect error:', e);
                alert('Login successful but redirect failed. Please try going to the dashboard manually.');
            }
        } else {
            console.error('Login failed:', data.message);
            alert('Login failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error during login:', error);
        alert('An error occurred during login. Please try again.');
    });
});