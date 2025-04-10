document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const profileImageInput = document.getElementById('profile-image-input');
    const profilePreview = document.getElementById('owner-edit-profile-preview');
    const changeProfileBtn = document.getElementById('change-profile-btn');

    // Handle click on change picture button
    changeProfileBtn?.addEventListener('click', function() {
        profileImageInput.click();
    });

    // Handle click on preview image
    profilePreview?.addEventListener('click', function() {
        profileImageInput.click();
    });

    // Handle image selection
    profileImageInput?.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profilePreview.src = e.target.result;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Handle view owner modal
    document.querySelectorAll('.view-owner').forEach(button => {
        button.addEventListener('click', function() {
            const ownerId = this.getAttribute('data-id');
            
            fetch('handle_owner.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=view&owner_id=${ownerId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const owner = data.data;
                    console.log('Owner data:', owner);

                    // Helper function to safely update element content
                    const updateElement = (id, value) => {
                        const element = document.getElementById(id);
                        if (element) {
                            if (element.tagName.toLowerCase() === 'img') {
                                element.src = value || 'uploads/owners/default-profile.jpg';
                            } else {
                                element.textContent = value || 'N/A';
                            }
                        } else {
                            console.error(`Element with id '${id}' not found`);
                        }
                    };

                    // Update all elements with error handling
                    updateElement('view-profile-image', owner.profile_picture);
                    updateElement('view-first-name', owner.first_name);
                    updateElement('view-last-name', owner.last_name);
                    updateElement('view-email', owner.email);
                    updateElement('view-phone', owner.phone);
                    updateElement('view-username', owner.username);
                    updateElement('view-property-count', owner.property_count);
                    updateElement('view-id-type', owner.id_type);
                    updateElement('view-id-number', owner.id_num);
                    updateElement('view-address', owner.address);
                } else {
                    alert('Error loading owner details: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load owner details');
            });
        });
    });

    // Edit owner
    document.querySelectorAll('.edit-owner').forEach(button => {
        button.addEventListener('click', function() {
            const ownerId = this.getAttribute('data-id');
            
            fetch('handle_owner.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=view&owner_id=${ownerId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Owner edit data:', data);
                    const owner = data.data;
                    // Populate edit form with owner data
                    document.getElementById('edit-owner-id').value = owner.owner_id;
                    document.getElementById('edit-first-name').value = owner.first_name;
                    document.getElementById('edit-last-name').value = owner.last_name;
                    document.getElementById('edit-email').value = owner.email;
                    document.getElementById('edit-phone').value = owner.phone;
                    document.getElementById('edit-username').value = owner.username;
                    document.getElementById('edit-id-type').value = owner.id_type;
                    document.getElementById('edit-id-number').value = owner.id_num;
                    document.getElementById('edit-address').value = owner.address;
                    
                    if (owner.profile_picture) {
                        document.getElementById('owner-edit-profile-preview').src = owner.profile_picture;
                    }
                } else {
                    alert('Error loading owner data: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load owner data for editing');
            });
        });
    });

    // Handle edit owner form submission
    const editOwnerForm = document.getElementById('editOwnerForm');
    editOwnerForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Ensure owner_id is included
        const ownerId = document.getElementById('edit-owner-id').value;
        if (!ownerId) {
            alert('Owner ID is missing');
            return;
        }
        
        // Add required fields and action
        formData.append('action', 'edit');
        formData.append('owner_id', ownerId);
        
        // Debug log to check form data
        console.log('Form data being sent:', Object.fromEntries(formData));
        
        fetch('handle_owner.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Server response:', data);
            if (data.success) {
                location.reload();
            } else {
                // Show detailed error message
                const errorMsg = data.message || 'Unknown error occurred';
                console.error('Update failed:', data);
                alert('Error updating owner: ' + errorMsg);
            }
        })
        .catch(error => {
            console.error('Network error:', error);
            alert('Failed to update owner: ' + error.message);
        });
    });

    // Delete owner
    document.querySelectorAll('.delete-owner').forEach(button => {
        button.addEventListener('click', function() {
            const ownerId = this.getAttribute('data-id');
            
            // Show confirmation modal
            const confirmModal = new bootstrap.Modal(document.getElementById('deleteOwnerModal'));
            confirmModal.show();
            
            document.getElementById('confirmDeleteOwner').onclick = function() {
                fetch('handle_owner.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&owner_id=${ownerId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Refresh page to show updated data
                    } else {
                        alert('Error deleting owner: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete owner');
                });
            };
        });
    });
});