// Use the existingImages variable from search.js
// (No need to redeclare it here)

// Function to populate owner and manager dropdowns
function populateDropdowns() {
    // Fetch owners
    const ownerFormData = new FormData();
    ownerFormData.append('action', 'get_owners');

    fetch('handle_sale.php', {
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
                // Store the currently selected value
                const currentValue = ownerSelect.value;

                // Clear existing options
                ownerSelect.innerHTML = '<option value="">Select Owner</option>';

                // Add new options
                data.owners.forEach(owner => {
                    const option = document.createElement('option');
                    option.value = owner.owner_id;
                    option.textContent = owner.name;
                    ownerSelect.appendChild(option);
                });

                // Restore the selected value if it exists
                if (currentValue) {
                    ownerSelect.value = currentValue;
                }
            });
        }
    })
    .catch(error => console.error('Error fetching owners:', error));

    // Fetch managers
    const managerFormData = new FormData();
    managerFormData.append('action', 'get_managers');

    fetch('handle_sale.php', {
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
                // Store the currently selected value
                const currentValue = managerSelect.value;

                // Clear existing options
                managerSelect.innerHTML = '<option value="">Select Manager</option>';

                // Add new options
                data.managers.forEach(manager => {
                    const option = document.createElement('option');
                    option.value = manager.manager_id;
                    option.textContent = manager.name;
                    managerSelect.appendChild(option);
                });

                // Restore the selected value if it exists
                if (currentValue) {
                    managerSelect.value = currentValue;
                }
            });
        }
    })
    .catch(error => console.error('Error fetching managers:', error));
}

// Use the displayExistingImages function from search.js
// (No need to redefine it here)

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dropdowns
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

    // Handle removing existing images
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-existing-image')) {
            e.preventDefault();
            e.target.closest('.image-preview').remove();
        }
    });

    // Also call populateDropdowns when any modal with these selects is shown
    document.addEventListener('show.bs.modal', function () {
        console.log('Modal shown - Refreshing dropdowns');
        populateDropdowns();
    });
    // View Sale Property
    document.querySelectorAll('.view-sale').forEach(button => {
        button.addEventListener('click', function() {
            const saleId = this.getAttribute('data-id');
            fetch(`handle_sale.php?action=view&sale_id=${saleId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const modal = document.querySelector('#viewSaleModal');
                        if (modal) {
                            const modalBody = modal.querySelector('.modal-body');
                            modalBody.innerHTML = `
                                <div class="sale-info">
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
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });

    // Edit Sale Property
    document.querySelectorAll('.edit-sale').forEach(button => {
        button.addEventListener('click', function() {
            // Populate dropdowns first to ensure they're loaded
            populateDropdowns();

            const saleId = this.getAttribute('data-id');
            const formData = new FormData();
            formData.append('action', 'view');
            formData.append('sale_id', saleId);

            fetch('handle_sale.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Raw response:', response);
                return response.json();
            })
            .then(data => {
                console.log('Parsed data:', data);
                if (data.success) {
                    const form = document.getElementById('editSaleForm');
                    if (form) {
                        // Set basic form values
                        try {
                            console.log('Setting form values...');
                            form.querySelector('[name="sale_id"]').value = saleId;

                            // Check if data.data exists and has the expected properties
                            if (!data.data) {
                                console.error('data.data is undefined or null');
                                throw new Error('Missing data object');
                            }

                            console.log('Property name:', data.data.property_name);
                            form.querySelector('[name="property_name"]').value = data.data.property_name || '';

                            // Check if price input exists
                            const priceInput = form.querySelector('[name="price"]');
                            if (priceInput) {
                                priceInput.value = data.data.price || '';
                            } else {
                                console.error('Price input not found');
                            }

                            form.querySelector('[name="property_type"]').value = data.data.property_type || '';
                            form.querySelector('[name="property_size"]').value = data.data.property_size || '';
                            form.querySelector('[name="status"]').value = data.data.status || '';

                            // Set bedrooms, bathrooms, and description
                            const bedroomsInput = form.querySelector('[name="bedrooms"]');
                            if (bedroomsInput) {
                                bedroomsInput.value = data.data.bedrooms || 0;
                            } else {
                                console.error('Bedrooms input not found');
                            }

                            const bathroomsInput = form.querySelector('[name="bathrooms"]');
                            if (bathroomsInput) {
                                bathroomsInput.value = data.data.bathrooms || 0;
                            } else {
                                console.error('Bathrooms input not found');
                            }

                            const descriptionInput = form.querySelector('[name="description"]');
                            if (descriptionInput) {
                                descriptionInput.value = data.data.description || '';
                            } else {
                                console.error('Description input not found');
                            }

                            form.querySelector('[name="utilities"]').value = data.data.utilities || '';
                            form.querySelector('[name="country"]').value = data.data.country || '';

                            // Check if title input exists
                            const titleInput = form.querySelector('[name="title"]');
                            if (titleInput) {
                                titleInput.value = data.data.title || '';
                            } else {
                                console.error('Title input not found');
                            }

                            form.querySelector('[name="region"]').value = data.data.region || '';
                            form.querySelector('[name="subregion"]').value = data.data.subregion || '';
                            form.querySelector('[name="parish"]').value = data.data.parish || '';
                            form.querySelector('[name="ward"]').value = data.data.ward || '';
                            form.querySelector('[name="cell"]').value = data.data.cell || '';
                        } catch (error) {
                            console.error('Error setting form values:', error);
                            alert('Error setting form values: ' + error.message);
                        }

                        // Set owner and manager
                        try {
                            console.log('Setting owner and manager...');
                            const ownerSelect = form.querySelector('[name="owner_id"]');
                            const managerSelect = form.querySelector('[name="manager_id"]');

                            if (ownerSelect) {
                                console.log('Owner ID:', data.data.owner_id);
                                ownerSelect.value = data.data.owner_id || '';
                            } else {
                                console.error('Owner select not found');
                            }

                            if (managerSelect) {
                                console.log('Manager ID:', data.data.manager_id);
                                managerSelect.value = data.data.manager_id || '';
                            } else {
                                console.error('Manager select not found');
                            }
                        } catch (error) {
                            console.error('Error setting owner/manager:', error);
                        }

                        // Handle multiple selects (amenities)
                        try {
                            console.log('Setting amenities...');
                            const amenitiesSelect = form.querySelector('[name="amenities[]"]');
                            if (amenitiesSelect) {
                                console.log('Amenities data:', data.data.amenities);
                                const amenities = typeof data.data.amenities === 'string'
                                    ? data.data.amenities.split(',')
                                    : data.data.amenities || [];

                                console.log('Processed amenities:', amenities);
                                Array.from(amenitiesSelect.options).forEach(option => {
                                    option.selected = amenities.includes(option.value);
                                    console.log(`Option ${option.value}: ${option.selected ? 'selected' : 'not selected'}`);
                                });

                                try {
                                    $(amenitiesSelect).trigger('change'); // Trigger Select2 update
                                } catch (e) {
                                    console.error('Error triggering Select2 change:', e);
                                }
                            } else {
                                console.error('Amenities select not found');
                            }
                        } catch (error) {
                            console.error('Error setting amenities:', error);
                        }

                        // Display existing images
                        try {
                            console.log('Setting images...');
                            if (data.data.image_urls) {
                                console.log('Image URLs:', data.data.image_urls);
                                if (typeof displayExistingImages === 'function') {
                                    displayExistingImages(data.data.image_urls, 'sale-current-images');
                                } else {
                                    console.error('displayExistingImages function not found');
                                }
                            } else {
                                console.log('No image URLs found');
                            }
                        } catch (error) {
                            console.error('Error displaying images:', error);
                        }
                    }
                } else {
                    alert('Error loading sale property: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load sale property data');
            });
        });
    });

    // Submit Edit Sale Form
    document.getElementById('editSaleForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        // Get the sale ID first
        const saleId = this.querySelector('[name="sale_id"]').value;
        if (!saleId) {
            alert('Missing property ID');
            return;
        }

        const formData = new FormData(this);
        formData.set('action', 'edit');
        formData.set('property_id', saleId); // Ensure we use property_id for PHP

        // Collect all select2 multiple values properly
        const amenitiesSelect = this.querySelector('[name="amenities[]"]');

        if (amenitiesSelect) {
            const amenities = $(amenitiesSelect).val(); // Get Select2 values
            if (amenities) {
                // Clear existing values first
                formData.delete('amenities[]');
                amenities.forEach(value => formData.append('amenities[]', value));
            }
        }

        // Handle existing images
        const currentImagesContainer = document.getElementById('sale-current-images');
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

        fetch('handle_sale.php', {
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

    // Delete Sale Property
    let saleToDelete = null;

    document.querySelectorAll('.delete-sale').forEach(button => {
        button.addEventListener('click', function() {
            saleToDelete = this.getAttribute('data-id');
        });
    });

    document.getElementById('confirmDeleteSale')?.addEventListener('click', function() {
        if (saleToDelete) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('sale_id', saleToDelete);

            fetch('handle_sale.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('deleteSaleModal')).hide();
                    location.reload();
                } else {
                    alert('Error deleting sale property: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
});