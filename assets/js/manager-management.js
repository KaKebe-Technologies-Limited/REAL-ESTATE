// Add this to your existing JavaScript file or create a new one
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const profileImageInput = document.getElementById('profile-image-input');
    const profilePreview = document.getElementById('manager-edit-profile-preview');
    const changeProfileBtn = document.getElementById('change-profile-btn');

    // Handle click on change picture button
    changeProfileBtn.addEventListener('click', function() {
        profileImageInput.click();
    });

    // Handle click on preview image
    profilePreview.addEventListener('click', function() {
        profileImageInput.click();
    });

    // Handle image selection
    profileImageInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profilePreview.src = e.target.result;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Handle view manager modal
    const viewManagerButtons = document.querySelectorAll('.view-manager');
    viewManagerButtons.forEach(button => {
        button.addEventListener('click', function() {
            const managerId = this.getAttribute('data-id');
            
            fetch('handle_manager.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=view&manager_id=${managerId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const manager = data.data;
                    console.log(manager);
                    document.getElementById('manager-view-profile-image').src = manager.profile_picture || 'uploads/managers/default-profile.jpg';
                    document.getElementById('manager-view-first-name').textContent = manager.first_name;
                    document.getElementById('manager-view-last-name').textContent = manager.last_name;
                    document.getElementById('manager-view-email').textContent = manager.email;
                    document.getElementById('manager-view-phone').textContent = manager.phone;
                    document.getElementById('manager-view-username').textContent = manager.username;
                    document.getElementById('manager-view-property-count').textContent = manager.property_count;
                    document.getElementById('manager-view-id-type').textContent = manager.id_type;
                    document.getElementById('manager-view-id-number').textContent = manager.id_num;
                    document.getElementById('manager-view-address').textContent = manager.address;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Edit manager
    document.querySelectorAll('.edit-manager').forEach(button => {
        button.addEventListener('click', function() {
            const managerId = this.getAttribute('data-id');
            
            fetch('handle_manager.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=view&manager_id=${managerId}`
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                if (data.success) {
                    console.log(data)
                    const manager = data.data;
                    document.getElementById('manager-edit-manager-id').value = manager.manager_id;
                    document.getElementById('manager-edit-first-name').value = manager.first_name;
                    document.getElementById('manager-edit-last-name').value = manager.last_name;
                    document.getElementById('manager-edit-email').value = manager.email;
                    document.getElementById('manager-edit-phone').value = manager.phone;
                    document.getElementById('manager-edit-username').value = manager.username;
                    document.getElementById('manager-edit-id-type').value = manager.id_type;
                    document.getElementById('manager-edit-id-number').value = manager.id_num;
                    document.getElementById('manager-edit-address').value = manager.address;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Handle edit manager form submission
    const editManagerForm = document.getElementById('editManagerForm');
    editManagerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'edit');
        
        fetch('handle_manager.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Delete manager
    document.querySelectorAll('.delete-manager').forEach(button => {
        button.addEventListener('click', function() {
            const managerId = this.getAttribute('data-id');
            
            document.getElementById('confirmDeleteManager').onclick = function() {
                fetch('handle_manager.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&manager_id=${managerId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Refresh the page to show updated data
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            };
        });
    });
});
