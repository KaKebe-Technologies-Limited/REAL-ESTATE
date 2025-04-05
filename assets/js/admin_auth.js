document.getElementById('adminLogin').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('http://localhost/REAL-ESTATE/login.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                window.location.href = 'http://localhost/REAL-ESTATE/adminDashboard.php';
            } else {
                console.log(data.message);
            }
        })
        .catch(error => console.error('Error: ', error))
});