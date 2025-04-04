document.getElementById('adminLogin').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('https://eastafricanlandandrentals.net/login.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                window.location.href = 'https://eastafricanlandandrentals.net/adminDashboard.php';
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error: ', error))
});