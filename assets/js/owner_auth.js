document.getElementById('ownerLogin').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('owner_login.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Check if subscription has expired
                if(data.subscription_expired) {
                    // Redirect to subscription renewal page
                    window.location.href = data.redirect || 'renew_subscription.php';
                } else {
                    // Normal login flow
                    window.location.href = 'ownerDashboard.php';
                }
            } else {
                // Show error message
                if (typeof showCustomAlert === 'function') {
                    showCustomAlert('error', 'Login Failed', data.message);
                } else {
                    alert(data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error: ', error);
            if (typeof showCustomAlert === 'function') {
                showCustomAlert('error', 'Login Error', 'An error occurred during login. Please try again.');
            } else {
                alert('An error occurred during login. Please try again.');
            }
        });
});