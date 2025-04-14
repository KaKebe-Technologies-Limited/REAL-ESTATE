// Custom handlers for manager buttons in ownerDashboard.php
document.addEventListener('DOMContentLoaded', function() {
    console.log('Loading owner-manager-handlers.js');
    
    // View manager handlers
    document.querySelectorAll('.view-manager').forEach(button => {
        button.addEventListener('click', function() {
            const managerId = this.getAttribute('data-id');
            console.log('View manager clicked in ownerDashboard:', managerId);
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'view');
            formData.append('manager_id', managerId);
            
            // Send AJAX request
            fetch('handle_manager.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                console.log('Raw response:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    throw new Error('Server returned invalid JSON: ' + text);
                }
            })
            .then(data => {
                console.log('Parsed response:', data);
                if (data.success) {
                    const modal = document.getElementById('viewManagerModal');
                    if (modal) {
                        const modalBody = modal.querySelector('.modal-body');
                        if (modalBody) {
                            // Format the manager details
                            modalBody.innerHTML = `
                                <div class="row">
                                    <div class="col-md-4 text-center mb-3">
                                        <img src="${data.data.profile_picture || 'assets/images/profile.jpg'}" 
                                             class="img-fluid rounded-circle" style="max-width: 150px;">
                                    </div>
                                    <div class="col-md-8">
                                        <h4>${data.data.first_name} ${data.data.last_name}</h4>
                                        <p class="text-muted">${data.data.username}</p>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <p><strong>Email:</strong> ${data.data.email}</p>
                                        <p><strong>Phone:</strong> ${data.data.phone || 'N/A'}</p>
                                        <p><strong>ID Type:</strong> ${data.data.id_type || 'N/A'}</p>
                                        <p><strong>ID Number:</strong> ${data.data.id_num || 'N/A'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Properties Managed:</strong> ${data.data.property_count || '0'}</p>
                                        <p><strong>Address:</strong> ${data.data.address || 'N/A'}</p>
                                    </div>
                                </div>
                            `;
                        }
                    }
                } else {
                    console.error('Error fetching manager details:', data.message);
                    alert('Error loading manager details: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while loading manager details: ' + error.message);
            });
        });
    });
    
    // Edit manager handlers
    document.querySelectorAll('.edit-manager').forEach(button => {
        button.addEventListener('click', function() {
            const managerId = this.getAttribute('data-id');
            console.log('Edit manager clicked in ownerDashboard:', managerId);
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'view');
            formData.append('manager_id', managerId);
            
            // Send AJAX request
            fetch('handle_manager.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                console.log('Raw response:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    throw new Error('Server returned invalid JSON: ' + text);
                }
            })
            .then(data => {
                console.log('Parsed response:', data);
                if (data.success) {
                    // Populate the edit form with manager data
                    const form = document.getElementById('editManagerForm');
                    if (form) {
                        form.querySelector('[name="manager_id"]').value = data.data.manager_id;
                        form.querySelector('[name="first_name"]').value = data.data.first_name || '';
                        form.querySelector('[name="last_name"]').value = data.data.last_name || '';
                        form.querySelector('[name="username"]').value = data.data.username || '';
                        form.querySelector('[name="email"]').value = data.data.email || '';
                        form.querySelector('[name="phone"]').value = data.data.phone || '';
                        
                        const idTypeSelect = form.querySelector('[name="id_type"]');
                        if (idTypeSelect) {
                            const options = idTypeSelect.options;
                            for (let i = 0; i < options.length; i++) {
                                if (options[i].value === data.data.id_type) {
                                    options[i].selected = true;
                                    break;
                                }
                            }
                        }
                        
                        form.querySelector('[name="id_num"]').value = data.data.id_num || '';
                        form.querySelector('[name="address"]').value = data.data.address || '';
                        
                        // Show profile picture preview if available
                        const profilePreview = document.getElementById('manager-edit-profile-preview');
                        if (profilePreview && data.data.profile_picture) {
                            profilePreview.src = data.data.profile_picture;
                            profilePreview.style.display = 'block';
                        }
                    }
                } else {
                    console.error('Error fetching manager details:', data.message);
                    alert('Error loading manager details: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while loading manager details: ' + error.message);
            });
        });
    });
    
    // Delete manager handlers
    document.querySelectorAll('.delete-manager').forEach(button => {
        button.addEventListener('click', function() {
            const managerId = this.getAttribute('data-id');
            console.log('Delete manager clicked in ownerDashboard:', managerId);
            
            const modal = document.getElementById('deleteManagerModal');
            if (modal) {
                // Store the manager ID in a data attribute on the modal for reference
                modal.setAttribute('data-manager-id', managerId);
                
                const confirmButton = modal.querySelector('.confirm-delete');
                if (confirmButton) {
                    // Remove any existing event listeners
                    const newConfirmButton = confirmButton.cloneNode(true);
                    confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
                    
                    // Set up the confirm button to delete the manager
                    newConfirmButton.onclick = function() {
                        // Get the manager ID from the modal's data attribute
                        const managerIdToDelete = modal.getAttribute('data-manager-id');
                        console.log('Confirming delete for manager ID:', managerIdToDelete);
                        
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('manager_id', managerIdToDelete);
                        
                        fetch('handle_manager.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(text => {
                            console.log('Raw response:', text);
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('Failed to parse JSON:', e);
                                throw new Error('Server returned invalid JSON: ' + text);
                            }
                        })
                        .then(data => {
                            console.log('Parsed response:', data);
                            if (data.success) {
                                alert('Manager deleted successfully');
                                // Close the modal
                                const bsModal = bootstrap.Modal.getInstance(modal);
                                if (bsModal) {
                                    bsModal.hide();
                                }
                                // Refresh the page to show updated data
                                location.reload();
                            } else {
                                console.error('Error deleting manager:', data.message);
                                alert('Error deleting manager: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the manager: ' + error.message);
                        });
                    };
                }
            }
        });
    });
    
    // Handle manager form submission
    const editManagerForm = document.getElementById('editManagerForm');
    if (editManagerForm) {
        editManagerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Manager form submitted in ownerDashboard');
            
            const formData = new FormData(this);
            formData.append('action', 'edit');
            
            // Debug log all form data
            console.log('Form data being sent:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            
            fetch('handle_manager.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                console.log('Raw response:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    throw new Error('Server returned invalid JSON: ' + text);
                }
            })
            .then(data => {
                console.log('Parsed response:', data);
                if (data.success) {
                    alert('Manager updated successfully');
                    // Close the modal
                    const modal = document.getElementById('editManagerModal');
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                    // Refresh the page to show updated data
                    location.reload();
                } else {
                    console.error('Error updating manager:', data.message);
                    alert('Error updating manager: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the manager: ' + error.message);
            });
        });
    }
});
