document.getElementById('managerLogin').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('manager_login.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                window.location.href = 'managerDashboard.php';
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error: ', error))
});