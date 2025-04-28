// Function to populate owner and manager dropdowns
function populateDropdowns() {
    // Fetch owners
    const ownerFormData = new FormData();
    ownerFormData.append('action', 'get_owners');

    fetch('handle_rental.php', {
        method: 'POST',
        body: ownerFormData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Owners data received:', data);
        // Get all owner select elements (there might be multiple forms)
        const ownerSelects = document.querySelectorAll('select[name="owner_id"]');

        if (data.success && data.owners && ownerSelects.length > 0) {
            ownerSelects.forEach(ownerSelect => {
                // Clear existing options
                ownerSelect.innerHTML = '<option value="">Select Owner</option>';
                // Add new options
                data.owners.forEach(owner => {
                    const option = document.createElement('option');
                    option.value = owner.owner_id;
                    option.textContent = owner.name;
                    ownerSelect.appendChild(option);
                });
            });
        }
    })
    .catch(error => console.error('Error fetching owners:', error));

    // Fetch managers
    const managerFormData = new FormData();
    managerFormData.append('action', 'get_managers');

    fetch('handle_rental.php', {
        method: 'POST',
        body: managerFormData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Managers data received:', data);
        // Get all manager select elements (there might be multiple forms)
        const managerSelects = document.querySelectorAll('select[name="manager_id"]');

        if (data.success && data.managers && managerSelects.length > 0) {
            managerSelects.forEach(managerSelect => {
                // Clear existing options
                managerSelect.innerHTML = '<option value="">Select Manager</option>';
                // Add new options
                data.managers.forEach(manager => {
                    const option = document.createElement('option');
                    option.value = manager.manager_id;
                    option.textContent = manager.name;
                    managerSelect.appendChild(option);
                });
            });
        }
    })
    .catch(error => console.error('Error fetching managers:', error));
}

// Make sure we call this function when the document is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded - Initializing dropdowns');
    populateDropdowns();

    // Initialize Select2 if jQuery and Select2 exist
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
        jQuery('.select2-multiple').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    } else {
        console.log('jQuery or Select2 is not loaded. Skipping Select2 initialization.');
    }
});

// Also call populateDropdowns when any modal with these selects is shown
document.addEventListener('show.bs.modal', function (event) {
    console.log('Modal shown - Refreshing dropdowns');
    populateDropdowns();
});

// Use the existingImages variable from search.js
// (No need to redeclare it here)

// Use the displayExistingImages function from search.js
// (No need to redefine it here)

document.addEventListener('DOMContentLoaded', function() {
    // View Rental Property
    document.querySelectorAll('.view-rental').forEach(button => {
        button.addEventListener('click', function() {
            const rentalId = this.getAttribute('data-id');
            const formData = new FormData();
            formData.append('action', 'view');
            formData.append('rental_id', rentalId);

            fetch('handle_rental.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = document.querySelector('#viewRentalModal');
                    if (modal) {
                        const modalBody = modal.querySelector('.modal-body');
                        modalBody.innerHTML = `
                            <div class="rental-info">
                                <h4>${data.data.property_name}</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Price:</strong> UGX ${data.data.price}</p>
                                        <p><strong>Property Type:</strong> ${data.data.property_type}</p>
                                        <p><strong>Property Size:</strong> ${data.data.property_size}</p>
                                        <p><strong>Bedrooms:</strong> ${data.data.bedrooms || 0}</p>
                                        <p><strong>Bathrooms:</strong> ${data.data.bathrooms || 0}</p>
                                        <p><strong>Location:</strong> ${data.data.region}, ${data.data.country}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Owner:</strong> ${data.data.owner_name}</p>
                                        <p><strong>Manager:</strong> ${data.data.manager_name}</p>
                                        <p><strong>Utilities:</strong> ${data.data.utilities}</p>
                                        <p><strong>Amenities:</strong> ${data.data.amenities}</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h5>Description</h5>
                                    <p>${data.data.description || 'No description available'}</p>
                                </div>
                                <div class="property-images mt-3">
                                    ${data.data.image_urls ? data.data.image_urls.map(url =>
                                        `<img src="${url}" class="img-thumbnail" style="max-width: 200px;">`
                                    ).join('') : 'No images available'}
                                </div>
                            </div>
                        `;
                    }
                } else {
                    alert('Error viewing rental property: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Edit Rental Property
    document.querySelectorAll('.edit-rental').forEach(button => {
        button.addEventListener('click', function() {
            const rentalId = this.getAttribute('data-id');
            const formData = new FormData();
            formData.append('action', 'view');
            formData.append('rental_id', rentalId);

            fetch('handle_rental.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const form = document.getElementById('editRentalForm');
                    if (form) {
                        // Helper function to safely set form field values
                        const setFieldValue = (fieldName, value) => {
                            try {
                                const field = form.querySelector(`[name="${fieldName}"]`);
                                if (field) {
                                    field.value = value || '';
                                } else {
                                    console.warn(`Field [name="${fieldName}"] not found in form`);
                                }
                            } catch (error) {
                                console.error(`Error setting value for ${fieldName}:`, error);
                            }
                        };

                        // Set basic form values with error handling
                        setFieldValue('rental_id', rentalId);
                        setFieldValue('property_name', data.data.property_name);
                        setFieldValue('price', data.data.price);
                        setFieldValue('property_type', data.data.property_type);
                        setFieldValue('property_size', data.data.property_size);
                        // Set all form fields using the helper function
                        setFieldValue('bedrooms', data.data.bedrooms || 0);
                        setFieldValue('bathrooms', data.data.bathrooms || 0);
                        setFieldValue('description', data.data.description || '');
                        setFieldValue('utilities', data.data.utilities);
                        setFieldValue('status', data.data.status);
                        setFieldValue('country', data.data.country);
                        setFieldValue('region', data.data.region);
                        setFieldValue('subregion', data.data.subregion);
                        setFieldValue('parish', data.data.parish);
                        setFieldValue('ward', data.data.ward);
                        setFieldValue('cell', data.data.cell);
                        setFieldValue('landlord', data.data.landlord);
                        setFieldValue('parking', data.data.parking);
                        setFieldValue('convenience', data.data.convenience);
                        setFieldValue('property_class', data.data.property_class);

                        // Set owner and manager using the helper function
                        setFieldValue('owner_id', data.data.owner_id || '');
                        setFieldValue('manager_id', data.data.manager_id || '');

                        // Handle multiple selects (security and amenities)
                        const securitySelect = form.querySelector('[name="security[]"]');
                        const amenitiesSelect = form.querySelector('[name="amenities[]"]');

                        if (securitySelect) {
                            const security = typeof data.data.security === 'string'
                                ? data.data.security.split(',')
                                : data.data.security || [];

                            Array.from(securitySelect.options).forEach(option => {
                                option.selected = security.includes(option.value);
                            });
                            $(securitySelect).trigger('change'); // Trigger Select2 update
                        }

                        if (amenitiesSelect) {
                            const amenities = typeof data.data.amenities === 'string'
                                ? data.data.amenities.split(',')
                                : data.data.amenities || [];

                            Array.from(amenitiesSelect.options).forEach(option => {
                                option.selected = amenities.includes(option.value);
                            });
                            $(amenitiesSelect).trigger('change'); // Trigger Select2 update
                        }

                        // Display existing images
                        if (data.data.image_urls) {
                            displayExistingImages(data.data.image_urls, 'rental-current-images');
                        }
                    }
                } else {
                    alert('Error loading rental property: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Add image preview handler for new uploads
    document.querySelector('input[name="new_images[]"]')?.addEventListener('change', function() {
        const previewContainer = document.getElementById('rental-new-images-preview');
        if (!previewContainer) return;

        previewContainer.innerHTML = '';

        if (this.files && this.files.length > 0) {
            console.log(`Processing ${this.files.length} image files`);
            Array.from(this.files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    console.log(`Processing image ${index + 1}: ${file.name} (${file.size} bytes)`);
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewContainer.innerHTML += `
                            <div class="image-preview">
                                <img src="${e.target.result}" class="img-thumbnail" style="max-width: 100px;">
                                <button type="button" class="btn btn-sm btn-danger remove-image">Remove</button>
                            </div>
                        `;
                    };
                    reader.onerror = function(error) {
                        console.error('Error reading file:', error);
                    };
                    reader.readAsDataURL(file);
                } else {
                    console.warn(`File ${file.name} is not an image (type: ${file.type})`);
                }
            });
        } else {
            console.log('No files selected');
        }
    });

    // Delete Rental Property
    document.querySelectorAll('.delete-rental').forEach(button => {
        button.addEventListener('click', function() {
            const rentalId = this.getAttribute('data-id');
            // Get property details for confirmation
            const formData = new FormData();
            formData.append('action', 'view');
            formData.append('rental_id', rentalId);

            fetch('handle_rental.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show delete confirmation modal with property details
                    const modal = document.getElementById('deleteRentalModal');
                    if (modal) {
                        document.getElementById('delete-rental-name').textContent = data.data.property_name;
                        if (data.data.image_urls && data.data.image_urls.length > 0) {
                            document.getElementById('delete-rental-preview').src = data.data.image_urls[0];
                        }
                        modal.setAttribute('data-rental-id', rentalId);
                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Confirm Delete
    document.getElementById('confirmDeleteRental')?.addEventListener('click', function() {
        const modal = document.getElementById('deleteRentalModal');
        const rentalId = modal.getAttribute('data-rental-id');

        if (rentalId) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('rental_id', rentalId);

            fetch('handle_rental.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(modal).hide();
                    location.reload();
                } else {
                    alert('Error deleting rental property: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
});

// Handle form submission
document.getElementById('editRentalForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    // Get the rental ID first
    const rentalId = this.querySelector('[name="rental_id"]').value;
    if (!rentalId) {
        alert('Missing property ID');
        return;
    }

    const formData = new FormData(this);
    formData.set('action', 'edit');
    formData.set('property_id', rentalId); // Ensure we use property_id for PHP

    // Collect all select2 multiple values properly
    const securitySelect = this.querySelector('[name="security[]"]');
    const amenitiesSelect = this.querySelector('[name="amenities[]"]');

    if (securitySelect) {
        const security = $(securitySelect).val(); // Get Select2 values
        if (security) {
            // Clear existing values first
            formData.delete('security[]');
            security.forEach(value => formData.append('security[]', value));
        }
    }

    if (amenitiesSelect) {
        const amenities = $(amenitiesSelect).val(); // Get Select2 values
        if (amenities) {
            // Clear existing values first
            formData.delete('amenities[]');
            amenities.forEach(value => formData.append('amenities[]', value));
        }
    }

    // Handle existing images
    const currentImagesContainer = document.getElementById('rental-current-images');
    if (currentImagesContainer) {
        const remainingImages = Array.from(currentImagesContainer.querySelectorAll('.image-preview img'))
            .map(img => {
                const url = new URL(img.src, window.location.origin);
                return url.pathname.substring(1);
            });

        // Clear existing values first
        formData.delete('existing_images[]');
        remainingImages.forEach(imgPath => {
            formData.append('existing_images[]', imgPath);
        });
    }

    // Debug log all form data
    console.log('Form data being sent:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }

    fetch('handle_rental.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())  // First get the raw response
    .then(text => {
        console.log('Raw response:', text);  // Log the raw response
        try {
            return JSON.parse(text);  // Then try to parse it
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            throw new Error('Server returned invalid JSON');
        }
    })
    .then(data => {
        console.log('Parsed response:', data);
        if (data.success) {
            alert('Property updated successfully!');
            location.reload();
        } else {
            alert('Error updating property: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update property: ' + error.message);
    });
});

// Handle removing existing images
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-existing-image')) {
        e.preventDefault();
        e.target.closest('.image-preview').remove();
    }
});
