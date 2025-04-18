document.addEventListener('DOMContentLoaded', function() {
    loadUserProfile();

    // Toggle between edit and view mode
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const profileForm = document.getElementById('profile-form');
    const profileView = document.getElementById('profile-view');

    if (editProfileBtn && profileForm && profileView) {
        editProfileBtn.addEventListener('click', function() {
            profileForm.style.display = 'block';
            profileView.style.display = 'none';
            this.style.display = 'none';
        });
    }

    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const passwordChangeFields = document.getElementById('password-change-fields');
    const currentPasswordField = document.getElementById('current_password');
    const newPasswordField = document.getElementById('new_password');
    const confirmPasswordField = document.getElementById('confirm_password');

    if (cancelEditBtn && profileForm && profileView && editProfileBtn) {
        cancelEditBtn.addEventListener('click', function() {
            profileForm.style.display = 'none';
            profileView.style.display = 'block';
            editProfileBtn.style.display = 'block';

            if (passwordChangeFields) {
                passwordChangeFields.style.display = 'none';
            }

            // Clear password fields if they exist
            if (currentPasswordField) currentPasswordField.value = '';
            if (newPasswordField) newPasswordField.value = '';
            if (confirmPasswordField) confirmPasswordField.value = '';
        });
    }

    // Handle profile picture change
    const changePictureBtn = document.getElementById('change-picture-btn');
    const profilePictureInput = document.getElementById('profile-picture-input');
    const profilePicturePreview = document.getElementById('profile-picture-preview');

    if (changePictureBtn && profilePictureInput) {
        changePictureBtn.addEventListener('click', function() {
            profilePictureInput.click();
        });
    }

    if (profilePictureInput && profilePicturePreview) {
        profilePictureInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePicturePreview.src = e.target.result;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    // Toggle password change fields
    const changePasswordToggle = document.getElementById('change-password-toggle');
    if (changePasswordToggle) {
        changePasswordToggle.addEventListener('click', function() {
            const passwordFields = document.getElementById('password-change-fields');
            if (passwordFields) {
                passwordFields.style.display = passwordFields.style.display === 'none' ? 'block' : 'none';
            }
        });
    }

    // Handle form submission
    // profileForm is already defined at the top of the file
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate password if being changed
            const newPasswordField = document.getElementById('new_password');
            const confirmPasswordField = document.getElementById('confirm_password');
            const currentPasswordField = document.getElementById('current_password');

            if (newPasswordField && newPasswordField.value) {
                if (confirmPasswordField && newPasswordField.value !== confirmPasswordField.value) {
                    alert('New passwords do not match');
                    return;
                }
                if (currentPasswordField && !currentPasswordField.value) {
                    alert('Please enter your current password');
                    return;
                }
            }

            const formData = new FormData(this);

            // Determine which endpoint to use based on the current page
            let endpoint = 'update_user_profile.php';

            // Check if we're on the owner dashboard
            if (window.location.pathname.includes('ownerDashboard.php')) {
                endpoint = 'update_owner_profile.php';
            }

            console.log('Using update endpoint:', endpoint);

            fetch(endpoint, {
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
};

function loadUserProfile() {
    // Determine which endpoint to use based on the current page
    let endpoint = 'get_user_profile.php';

    // Check if we're on the owner dashboard
    if (window.location.pathname.includes('ownerDashboard.php')) {
        endpoint = 'get_owner_profile.php';
    }

    console.log('Using profile endpoint:', endpoint);
    console.log('Current pathname:', window.location.pathname);

    fetch(endpoint)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Profile data:', data);
            if (data.success) {
                const user = data.user;

                // Helper function to safely update elements
                const updateElement = (selector, value) => {
                    const element = document.querySelector(selector);
                    if (element) {
                        if (element.tagName.toLowerCase() === 'img') {
                            // Use different default image path based on user type
                            const defaultImg = endpoint.includes('owner') ?
                                'assets/images/profile.jpg' :
                                'uploads/profile_picture/default-profile.jpg';
                            element.src = value || defaultImg;
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
                    const defaultImg = endpoint.includes('owner') ?
                        'assets/images/profile.jpg' :
                        'uploads/profile_picture/default-profile.jpg';
                    previewImg.src = user.profile_picture || defaultImg;
                }
            } else {
                console.error('Failed to load profile:', data.message);

                // If we're on the owner dashboard and the profile failed to load,
                // try to get the profile data from the page itself
                if (window.location.pathname.includes('ownerDashboard.php')) {
                    console.log('Attempting to get profile data from the page');

                    // Try to get profile data from the page
                    const firstNameElement = document.querySelector('#profile-view [data-field="first_name"]');
                    const lastNameElement = document.querySelector('#profile-view [data-field="last_name"]');

                    if (firstNameElement && lastNameElement) {
                        console.log('Found profile elements on the page');
                    } else {
                        console.log('Profile elements not found on the page');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error loading profile:', error);
        });
}})
