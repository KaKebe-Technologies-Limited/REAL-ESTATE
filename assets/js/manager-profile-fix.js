/**
 * Direct fix for manager profile functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Manager profile fix loaded');
    
    // Add direct event listeners to profile elements
    setupProfileEventListeners();
    
    // Check if we need to show profile on page load (e.g., if coming from a link)
    checkUrlForProfileAction();
});

/**
 * Set up all event listeners for profile functionality
 */
function setupProfileEventListeners() {
    // Profile picture click in navbar
    const profilePictures = document.querySelectorAll('.profile-picture');
    profilePictures.forEach(pic => {
        pic.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Profile picture clicked');
            showManagerProfile();
            return false;
        });
    });
    
    // Profile menu item click
    const profileMenuItem = document.querySelector('a[data-form="profile-content"]');
    if (profileMenuItem) {
        profileMenuItem.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Profile menu item clicked');
            showManagerProfile();
        });
    }
    
    // Edit profile button
    const editProfileBtn = document.getElementById('edit-profile-btn');
    if (editProfileBtn) {
        editProfileBtn.addEventListener('click', function() {
            const profileForm = document.getElementById('profile-form');
            const profileView = document.getElementById('profile-view');
            
            if (profileForm && profileView) {
                profileForm.style.display = 'block';
                profileView.style.display = 'none';
                this.style.display = 'none';
            }
        });
    }
    
    // Cancel edit button
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function() {
            const profileForm = document.getElementById('profile-form');
            const profileView = document.getElementById('profile-view');
            const editProfileBtn = document.getElementById('edit-profile-btn');
            
            if (profileForm && profileView && editProfileBtn) {
                profileForm.style.display = 'none';
                profileView.style.display = 'block';
                editProfileBtn.style.display = 'block';
            }
        });
    }
    
    // Profile picture change
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
    
    // Profile form submission
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Profile form submitted');
            
            const formData = new FormData(this);
            
            // Send the form data to the server
            fetch('update_manager_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        alert('Profile updated successfully');
                        // Reload profile data
                        loadManagerProfile();
                        // Switch back to view mode
                        profileForm.style.display = 'none';
                        document.getElementById('profile-view').style.display = 'block';
                        document.getElementById('edit-profile-btn').style.display = 'block';
                    } else {
                        alert('Error updating profile: ' + data.message);
                    }
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                    alert('An error occurred while updating the profile');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the profile');
            });
        });
    }
}

/**
 * Check URL for profile action
 */
function checkUrlForProfileAction() {
    if (window.location.hash === '#profile') {
        showManagerProfile();
    }
}

/**
 * Show manager profile content
 */
function showManagerProfile() {
    console.log('Showing manager profile');
    
    // Hide all content sections
    document.querySelectorAll('#main-content > div').forEach(div => {
        div.style.display = 'none';
    });
    
    // Show profile content
    const profileContent = document.getElementById('profile-content');
    if (profileContent) {
        profileContent.style.display = 'block';
        console.log('Profile content displayed');
        
        // Load profile data
        loadManagerProfile();
    } else {
        console.error('Profile content element not found');
    }
}

/**
 * Load manager profile data
 */
function loadManagerProfile() {
    console.log('Loading manager profile data');
    
    // Add timestamp to prevent caching
    const url = 'get_manager_profile.php?t=' + new Date().getTime();
    
    fetch(url)
        .then(response => response.text())
        .then(text => {
            console.log('Raw profile data:', text);
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    updateProfileUI(data.user);
                } else {
                    console.error('Failed to load profile:', data.message);
                }
            } catch (e) {
                console.error('Error parsing profile data:', e);
            }
        })
        .catch(error => {
            console.error('Error loading profile:', error);
        });
}

/**
 * Update profile UI with user data
 */
function updateProfileUI(userData) {
    console.log('Updating profile UI with data:', userData);
    
    // Update profile view
    updateElement('#profile-view .profile-picture', userData.profile_picture);
    updateElement('#profile-view [data-field="first_name"]', userData.first_name);
    updateElement('#profile-view [data-field="last_name"]', userData.last_name);
    updateElement('#profile-view [data-field="email"]', userData.email);
    updateElement('#profile-view [data-field="phone"]', userData.phone);
    updateElement('#profile-view [data-field="username"]', userData.username);
    
    // Update form inputs
    updateInput('first_name', userData.first_name);
    updateInput('last_name', userData.last_name);
    updateInput('email', userData.email);
    updateInput('phone', userData.phone);
    updateInput('username', userData.username);
    
    // Update profile picture preview
    const previewImg = document.querySelector('#profile-picture-preview');
    if (previewImg) {
        previewImg.src = userData.profile_picture || 'assets/images/profile.jpg';
    }
}

/**
 * Helper function to update elements
 */
function updateElement(selector, value) {
    const element = document.querySelector(selector);
    if (element) {
        if (element.tagName.toLowerCase() === 'img') {
            element.src = value || 'assets/images/profile.jpg';
        } else {
            element.textContent = value || '';
        }
    }
}

/**
 * Helper function to update form inputs
 */
function updateInput(name, value) {
    const input = document.querySelector(`#profile-form input[name="${name}"]`);
    if (input) {
        input.value = value || '';
    }
}

// Make functions globally available
window.showManagerProfile = showManagerProfile;
window.loadManagerProfile = loadManagerProfile;
