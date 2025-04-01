document.getElementById('managerLogin').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('http://localhost/REAL-ESTATE/manager_login.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                window.location.href = 'http://localhost/REAL-ESTATE/managerDashboard.php';
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error: ', error))
});