/**
 * Common profile functionality for all dashboards
 */
document.addEventListener('DOMContentLoaded', function() {
    // Profile form elements
    const profileView = document.getElementById('profile-view');
    const profileForm = document.getElementById('profile-form');
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const changePasswordToggle = document.getElementById('change-password-toggle');
    const passwordChangeFields = document.getElementById('password-change-fields');
    const profilePictureInput = document.getElementById('profile-picture-input');
    const profilePicturePreview = document.getElementById('profile-picture-preview');
    const changePictureBtn = document.getElementById('change-picture-btn');

    // Edit profile button click handler
    if (editProfileBtn) {
        editProfileBtn.addEventListener('click', function() {
            if (profileView) profileView.style.display = 'none';
            if (profileForm) profileForm.style.display = 'block';
        });
    }

    // Cancel edit button click handler
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function() {
            if (profileForm) profileForm.style.display = 'none';
            if (profileView) profileView.style.display = 'block';
        });
    }

    // Change password toggle click handler
    if (changePasswordToggle) {
        changePasswordToggle.addEventListener('click', function() {
            if (passwordChangeFields) {
                passwordChangeFields.style.display = 
                    passwordChangeFields.style.display === 'none' ? 'block' : 'none';
            }
        });
    }

    // Profile picture change handler
    if (changePictureBtn && profilePictureInput) {
        changePictureBtn.addEventListener('click', function() {
            profilePictureInput.click();
        });
    }

    // Profile picture preview
    if (profilePictureInput && profilePicturePreview) {
        profilePictureInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePicturePreview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Make profile content responsive
    function adjustProfileLayout() {
        const windowWidth = window.innerWidth;
        const profileContent = document.getElementById('profile-content');
        
        if (!profileContent) return;
        
        if (windowWidth < 768) {
            // Mobile layout adjustments
            const formGroups = profileContent.querySelectorAll('.form-group.row');
            formGroups.forEach(group => {
                const label = group.querySelector('.col-form-label');
                const input = group.querySelector('.col-sm-9');
                
                if (label) label.classList.add('mb-1');
                if (input) input.classList.add('mb-3');
            });
        } else {
            // Desktop layout reset
            const labels = profileContent.querySelectorAll('.col-form-label');
            const inputs = profileContent.querySelectorAll('.col-sm-9');
            
            labels.forEach(label => label.classList.remove('mb-1'));
            inputs.forEach(input => input.classList.remove('mb-3'));
        }
    }

    // Initial adjustment and listen for window resize
    adjustProfileLayout();
    window.addEventListener('resize', adjustProfileLayout);
});
