document.addEventListener('DOMContentLoaded', function() {
    // Check session data first
    fetch('check_session.php')
        .then(response => response.json())
        .then(data => {
            console.log('Session data:', data);
            // Now load the profile
            loadUserProfile();
        })
        .catch(error => {
            console.error('Error checking session:', error);
            // Still try to load the profile
            loadUserProfile();
        });

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

            // Force the correct endpoint based on the current page
            let endpoint;

            // Get the current path and log it for debugging
            const currentPath = window.location.pathname;
            console.log('Current full pathname (update):', currentPath);

            // Extract just the filename from the path
            const pathParts = currentPath.split('/');
            const filename = pathParts[pathParts.length - 1];
            console.log('Current filename (update):', filename);

            // Always use the correct endpoint based on the current page
            if (filename === 'managerDashboard.php' || currentPath.includes('managerDashboard.php')) {
                endpoint = 'update_manager_profile.php';
                console.log('Using manager update endpoint');
            } else if (filename === 'ownerDashboard.php' || currentPath.includes('ownerDashboard.php')) {
                endpoint = 'update_owner_profile.php';
                console.log('Using owner update endpoint');
            } else {
                endpoint = 'update_user_profile.php';
                console.log('Using default update endpoint');
            }

            console.log('Using update endpoint:', endpoint);

            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                    console.log('Raw response:', text);
                    throw new Error('Invalid JSON response');
                }
            });
        })
        .then(data => {
            console.log('Update response:', data);
            if (data.success) {
                alert('Profile updated successfully');
                loadUserProfile();
                document.getElementById('profile-form').style.display = 'none';
                document.getElementById('profile-view').style.display = 'block';
                document.getElementById('edit-profile-btn').style.display = 'block';

                // Hide password fields if they exist
                const passwordFields = document.getElementById('password-change-fields');
                if (passwordFields) passwordFields.style.display = 'none';

                // Clear password fields if they exist
                const currentPassword = document.getElementById('current_password');
                const newPassword = document.getElementById('new_password');
                const confirmPassword = document.getElementById('confirm_password');

                if (currentPassword) currentPassword.value = '';
                if (newPassword) newPassword.value = '';
                if (confirmPassword) confirmPassword.value = '';
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
    console.log('loadUserProfile function called');

    // Get the current path to determine which dashboard we're on
    const currentPath = window.location.pathname;
    console.log('Current full pathname:', currentPath);

    // Extract just the filename from the path
    const pathParts = currentPath.split('/');
    const filename = pathParts[pathParts.length - 1];
    console.log('Current filename:', filename);

    // Set the endpoint based on the current page
    let endpoint;

    // Force the correct endpoint based on the current page
    if (filename === 'managerDashboard.php' || currentPath.includes('managerDashboard.php')) {
        endpoint = 'get_manager_profile.php';
        console.log('Using manager profile endpoint');
    } else if (filename === 'ownerDashboard.php' || currentPath.includes('ownerDashboard.php')) {
        endpoint = 'get_owner_profile.php';
        console.log('Using owner profile endpoint');
    } else {
        // Fallback to session check if we can't determine from the path
        fetch('check_session.php')
            .then(response => response.json())
            .then(sessionData => {
                console.log('Session data in loadUserProfile:', sessionData);

                if (sessionData.session && sessionData.session.user_type) {
                    const userType = sessionData.session.user_type;
                    console.log('User type from session:', userType);

                    if (userType === 'owner') {
                        endpoint = 'get_owner_profile.php';
                    } else if (userType === 'manager') {
                        endpoint = 'get_manager_profile.php';
                    } else {
                        endpoint = 'get_user_profile.php';
                    }
                } else {
                    endpoint = 'get_user_profile.php';
                }

                console.log('Using endpoint from session:', endpoint);
                fetchProfileData(endpoint);
            })
            .catch(error => {
                console.error('Error checking session:', error);
                endpoint = 'get_user_profile.php';
                fetchProfileData(endpoint);
            });

        return; // Exit early as we're handling the fetch in the promise chain
    }

    // If we determined the endpoint from the path, fetch the profile data
    fetchProfileData(endpoint);
}

function fetchProfileData(endpoint) {
    console.log('Using profile endpoint:', endpoint);
    console.log('Making fetch request to:', window.location.origin + '/' + endpoint);

    // Add a timestamp to prevent caching
    const fetchUrl = endpoint + '?t=' + new Date().getTime();

    fetch(fetchUrl)
        .then(response => {
            console.log('Response status:', response.status);
            return response.text().then(text => {
                console.log('Raw response text:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                    console.log('Raw response:', text);
                    throw new Error('Invalid JSON response');
                }
            });
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

                // If we're on the owner or manager dashboard and the profile failed to load,
                // try to get the profile data from the page itself
                if (window.location.pathname.includes('ownerDashboard.php') ||
                    window.location.pathname.includes('managerDashboard.php')) {
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
}

});
