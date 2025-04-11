document.addEventListener('DOMContentLoaded', function() {
    loadUserProfile();

    // Toggle between edit and view mode
    document.getElementById('edit-profile-btn').addEventListener('click', function() {
        document.getElementById('profile-form').style.display = 'block';
        document.getElementById('profile-view').style.display = 'none';
        this.style.display = 'none';
    });

    document.getElementById('cancel-edit-btn').addEventListener('click', function() {
        document.getElementById('profile-form').style.display = 'none';
        document.getElementById('profile-view').style.display = 'block';
        document.getElementById('edit-profile-btn').style.display = 'block';
        document.getElementById('password-change-fields').style.display = 'none';
        // Clear password fields
        document.getElementById('current_password').value = '';
        document.getElementById('new_password').value = '';
        document.getElementById('confirm_password').value = '';
    });

    // Handle profile picture change
    document.getElementById('change-picture-btn').addEventListener('click', function() {
        document.getElementById('profile-picture-input').click();
    });

    document.getElementById('profile-picture-input').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profile-picture-preview').src = e.target.result;
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // Toggle password change fields
    document.getElementById('change-password-toggle').addEventListener('click', function() {
        const passwordFields = document.getElementById('password-change-fields');
        passwordFields.style.display = passwordFields.style.display === 'none' ? 'block' : 'none';
    });

    // Handle form submission
    document.getElementById('profile-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate password if being changed
        if (document.getElementById('new_password').value) {
            if (document.getElementById('new_password').value !== document.getElementById('confirm_password').value) {
                alert('New passwords do not match');
                return;
            }
            if (!document.getElementById('current_password').value) {
                alert('Please enter your current password');
                return;
            }
        }

        const formData = new FormData(this);
        
        fetch('update_user_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Profile updated successfully');
                loadUserProfile();
                document.getElementById('profile-form').style.display = 'none';
                document.getElementById('profile-view').style.display = 'block';
                document.getElementById('edit-profile-btn').style.display = 'block';
                document.getElementById('password-change-fields').style.display = 'none';
                // Clear password fields
                document.getElementById('current_password').value = '';
                document.getElementById('new_password').value = '';
                document.getElementById('confirm_password').value = '';
            } else {
                alert('Error updating profile: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the profile');
        });
    });
});

function loadUserProfile() {
    fetch('get_user_profile.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                
                // Helper function to safely update elements
                const updateElement = (selector, value) => {
                    const element = document.querySelector(selector);
                    if (element) {
                        if (element.tagName.toLowerCase() === 'img') {
                            element.src = value || 'uploads/profile_picture/default-profile.jpg';
                        } else {
                            element.textContent = value || '';
                        }
                    }
                };

                // Update profile view
                updateElement('#profile-view .profile-picture', user.profile_picture);
                updateElement('#profile-view [data-field="first_name"]', user.first_name);
                updateElement('#profile-view [data-field="last_name"]', user.last_name);
                updateElement('#profile-view [data-field="email"]', user.email);
                updateElement('#profile-view [data-field="phone"]', user.phone);
                updateElement('#profile-view [data-field="username"]', user.username);

                // Update form inputs
                const updateInput = (name, value) => {
                    const input = document.querySelector(`#profile-form input[name="${name}"]`);
                    if (input) {
                        input.value = value || '';
                    }
                };

                updateInput('first_name', user.first_name);
                updateInput('last_name', user.last_name);
                updateInput('email', user.email);
                updateInput('phone', user.phone);
                updateInput('username', user.username);

                // Update profile picture preview
                const previewImg = document.querySelector('#profile-picture-preview');
                if (previewImg) {
                    previewImg.src = user.profile_picture || 'uploads/profile_picture/default-profile.jpg';
                }
            } else {
                console.error('Failed to load profile:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading profile:', error);
        });
}
