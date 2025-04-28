// Add this to your existing JavaScript file or create a new one
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const profileImageInput = document.getElementById('profile-image-input');
    const profilePreview = document.getElementById('manager-edit-profile-preview');
    const changeProfileBtn = document.getElementById('change-profile-btn');

    // Only add event listeners if elements exist
    if (changeProfileBtn && profileImageInput) {
        // Handle click on change picture button
        changeProfileBtn.addEventListener('click', function() {
            profileImageInput.click();
        });
    }

    if (profilePreview && profileImageInput) {
        // Handle click on preview image
        profilePreview.addEventListener('click', function() {
            profileImageInput.click();
        });
    }

    if (profileImageInput && profilePreview) {
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
    }

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

                    // Check if elements exist before setting values
                    const profileImage = document.getElementById('manager-view-profile-image');
                    if (profileImage) profileImage.src = manager.profile_picture || 'uploads/managers/default-profile.jpg';

                    const elements = {
                        'manager-view-first-name': manager.first_name,
                        'manager-view-last-name': manager.last_name,
                        'manager-view-email': manager.email,
                        'manager-view-phone': manager.phone,
                        'manager-view-username': manager.username,
                        'manager-view-property-count': manager.property_count,
                        'manager-view-id-type': manager.id_type,
                        'manager-view-id-number': manager.id_num,
                        'manager-view-address': manager.address
                    };

                    // Set values only if elements exist
                    for (const [id, value] of Object.entries(elements)) {
                        const element = document.getElementById(id);
                        if (element) element.textContent = value;
                    }
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

                    // Check if elements exist before setting values
                    const elements = {
                        'manager-edit-manager-id': manager.manager_id,
                        'manager-edit-first-name': manager.first_name,
                        'manager-edit-last-name': manager.last_name,
                        'manager-edit-email': manager.email,
                        'manager-edit-phone': manager.phone,
                        'manager-edit-username': manager.username,
                        'manager-edit-id-type': manager.id_type,
                        'manager-edit-id-number': manager.id_num,
                        'manager-edit-address': manager.address
                    };

                    // Set values only if elements exist
                    for (const [id, value] of Object.entries(elements)) {
                        const element = document.getElementById(id);
                        if (element) element.value = value;
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Handle edit manager form submission
    const editManagerForm = document.getElementById('editManagerForm');
    if (editManagerForm) {
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
    }

    // Delete manager
    document.querySelectorAll('.delete-manager').forEach(button => {
        button.addEventListener('click', function() {
            const managerId = this.getAttribute('data-id');

            const confirmDeleteBtn = document.getElementById('confirmDeleteManager');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.onclick = function() {
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
            }
        });
    });
});
