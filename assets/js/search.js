// Global variable to store existing images
let existingImages = [];

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

// Function to display existing images
function displayExistingImages(imageUrls, containerId) {
    // Determine which container to use
    let container;

    if (containerId) {
        // If a specific container ID is provided, use that
        container = document.getElementById(containerId);
    } else {
        // Otherwise, try to find either the rental or sale container
        container = document.getElementById('rental-current-images') || document.getElementById('sale-current-images');
    }

    // If no container is found, log a warning and return
    if (!container) {
        console.warn('No image container found. Tried rental-current-images and sale-current-images');
        return;
    }

    // Clear the container and store the images
    container.innerHTML = '';
    existingImages = imageUrls || []; // Store the current images

    // Add images to the container if there are any
    if (imageUrls && imageUrls.length > 0) {
        imageUrls.forEach(url => {
            container.innerHTML += `
                <div class="image-preview">
                    <img src="${url}" class="img-thumbnail" style="max-width: 100px;">
                    <button type="button" class="btn btn-sm btn-danger remove-existing-image"
                            data-image="${url}">Remove</button>
                </div>
            `;
        });
    } else {
        console.log('No images to display');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dropdowns
    populateDropdowns();

    // Initialize Select2 if it exists
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2-multiple').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }

    // Initialize search handlers for each listing type
    const searchHandlers = {
        rentals: initializeSearch('rentals', updateRentalTable),
        sales: initializeSearch('sales', updateSalesTable),
        owners: initializeSearch('owners', updateOwnersTable),
        managers: initializeSearch('managers', updateManagersTable)
    };

    function initializeSearch(type, updateFunction) {
        const listingElement = document.querySelector(`[data-type="${type}"]`);
        if (!listingElement) return null;

        return {
            type: type,
            input: listingElement,
            container: listingElement.closest('.card').querySelector('tbody'),
            pagination: listingElement.closest('.card').querySelector('.pagination'),
            showingEntries: listingElement.closest('.card').querySelector('.showing-entries'),
            timeoutId: null,
            currentPage: 1,
            updateFunction
        };
    }

    // Add event listeners to search inputs
    Object.values(searchHandlers).forEach(handler => {
        if (handler && handler.input) {
            handler.input.addEventListener('input', function() {
                clearTimeout(handler.timeoutId);
                handler.timeoutId = setTimeout(() => {
                    handler.currentPage = 1;
                    performSearch(handler);
                }, 300);
            });
        }
    });

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

    function performSearch(handler) {
        const searchTerm = handler.input.value;
        const url = `search_all.php?type=${handler.type}&search=${encodeURIComponent(searchTerm)}&page=${handler.currentPage}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    handler.updateFunction(data.data, handler);
                } else {
                    console.error('Search failed:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function updateRentalTable(data, handler) {
        updateTable(data, handler, row => `
            <tr>
                <td>${escapeHtml(row.property_name)}</td>
                <td>${escapeHtml(row.owner_name || '')}</td>
                <td>${escapeHtml(row.manager_name || '')}</td>
                <td>${escapeHtml(row.location)}</td>
                <td>$${escapeHtml(row.price)}</td>
                <td>
                    <span class="badge ${row.property_type === 'Available' ? 'bg-success' : 'bg-danger'}">
                        ${escapeHtml(row.property_type || 'N/A')}
                    </span>
                </td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary view-rental"
                                data-id="${escapeHtml(row.property_id)}"
                                data-bs-toggle="modal"
                                data-bs-target="#viewRentalModal">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning edit-rental"
                                data-id="${escapeHtml(row.property_id)}"
                                data-bs-toggle="modal"
                                data-bs-target="#editRentalModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-rental"
                                data-id="${escapeHtml(row.property_id)}"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteRentalModal">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);
    }

    function updateSalesTable(data, handler) {
        updateTable(data, handler, row => `
            <tr>
                <td>${escapeHtml(row.property_name)}</td>
                <td>${escapeHtml(row.owner_name || '')}</td>
                <td>${escapeHtml(row.manager_name || '')}</td>
                <td>${escapeHtml(row.location)}</td>
                <td>${escapeHtml(row.price)}</td>
                <td>${escapeHtml(row.property_type)}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary view-sale"
                                data-id="${escapeHtml(row.property_id)}"
                                data-bs-toggle="modal"
                                data-bs-target="#viewSaleModal">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning edit-sale"
                                data-id="${escapeHtml(row.property_id)}"
                                data-bs-toggle="modal"
                                data-bs-target="#editSaleModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-sale"
                                data-id="${escapeHtml(row.property_id)}"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteSaleModal">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);
    }

    function updateOwnersTable(data, handler) {
        updateTable(data, handler, row => `
            <tr>
                <td>${escapeHtml(row.username)}</td>
                <td>${escapeHtml(row.property_count)}</td>
                <td>${escapeHtml(row.email)}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary view-owner"
                                data-id="${escapeHtml(row.owner_id)}"
                                data-bs-toggle="modal"
                                data-bs-target="#viewOwnerModal">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn btn-sm btn-outline-warning edit-owner"
                                data-id="${escapeHtml(row.owner_id)}"
                                data-bs-toggle="modal"
                                data-bs-target="#editOwnerModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-owner"
                                data-id="${escapeHtml(row.owner_id)}"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteOwnerModal">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);
    }

    function updateManagersTable(data, handler) {
        updateTable(data, handler, row => `
            <tr>
                <td>${escapeHtml(row.username)}</td>
                <td>${escapeHtml(row.phone)}</td>
                <td>${escapeHtml(row.property_count)}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary view-manager"
                                data-id="${escapeHtml(row.manager_id)}"
                                data-bs-toggle="modal"
                                data-bs-target="#viewManagerModal">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn btn-sm btn-outline-warning edit-manager"
                                data-id="${escapeHtml(row.manager_id)}"
                                data-bs-toggle="modal"
                                data-bs-target="#editManagerModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-manager"
                                data-id="${escapeHtml(row.manager_id)}"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteManagerModal">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);
    }

    // Add after your existing event listeners
    document.addEventListener('click', function(e) {

        // Owner handlers
        if (e.target.closest('.view-owner')) {
            handleOwnerView(e.target.closest('.view-owner'));
        } else if (e.target.closest('.edit-owner')) {
            handleOwnerEdit(e.target.closest('.edit-owner'));
        } else if (e.target.closest('.delete-owner')) {
            handleOwnerDelete(e.target.closest('.delete-owner'));
        }

        // Manager handlers
        else if (e.target.closest('.view-manager')) {
            handleManagerView(e.target.closest('.view-manager'));
        } else if (e.target.closest('.edit-manager')) {
            handleManagerEdit(e.target.closest('.edit-manager'));
        } else if (e.target.closest('.delete-manager')) {
            handleManagerDelete(e.target.closest('.delete-manager'));
        }

         // Rental handlers
        else if (e.target.closest('.view-rental')) {
            handleRentalView(e.target.closest('.view-rental'));
        } else if (e.target.closest('.edit-rental')) {
            handleRentalEdit(e.target.closest('.edit-rental'));
        } else if (e.target.closest('.delete-rental')) {
            handleRentalDelete(e.target.closest('.delete-rental'));
        }

        // Sale handlers
        else if (e.target.closest('.view-sale')) {
            handleSaleView(e.target.closest('.view-sale'));
        } else if (e.target.closest('.edit-sale')) {
            handleSaleEdit(e.target.closest('.edit-sale'));
        } else if (e.target.closest('.delete-sale')) {
            handleSaleDelete(e.target.closest('.delete-sale'));
        }
    });

    function handleOwnerView(button) {
        const ownerId = button.getAttribute('data-id');
        const formData = new FormData();
        formData.append('action', 'view');
        formData.append('owner_id', ownerId);

        fetch('handle_owner.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.querySelector('#viewOwnerModal');
                if (modal) {
                    // Update modal content with owner details
                    document.getElementById('view-first-name').textContent = data.data.first_name;
                    document.getElementById('view-last-name').textContent = data.data.last_name;
                    document.getElementById('view-email').textContent = data.data.email;
                    document.getElementById('view-phone').textContent = data.data.phone;
                    document.getElementById('view-username').textContent = data.data.username;
                    document.getElementById('view-id-type').textContent = data.data.id_type;
                    document.getElementById('view-id-number').textContent = data.data.id_num;
                    document.getElementById('view-address').textContent = data.data.address;
                }
            } else {
                console.error('Error:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function handleOwnerEdit(button) {
        const ownerId = button.getAttribute('data-id');
        const formData = new FormData();
        formData.append('action', 'view'); // Changed to 'view' to get owner data
        formData.append('owner_id', ownerId);

        // First fetch owner data
        fetch('handle_owner.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Owner data received:', data); // Debug log
                const modal = document.querySelector('#editOwnerModal');
                if (modal) {
                    const form = modal.querySelector('form');
                    // Populate form fields
                    form.querySelector('[name="owner_id"]').value = data.data.owner_id;
                    form.querySelector('[name="first_name"]').value = data.data.first_name;
                    form.querySelector('[name="last_name"]').value = data.data.last_name;
                    form.querySelector('[name="email"]').value = data.data.email;
                    form.querySelector('[name="phone"]').value = data.data.phone;
                    form.querySelector('[name="username"]').value = data.data.username;
                    form.querySelector('[name="id_type"]').value = data.data.id_type;
                    form.querySelector('[name="id_num"]').value = data.data.id_num;
                    form.querySelector('[name="address"]').value = data.data.address;

                    // Handle form submission
                    form.onsubmit = function(e) {
                        e.preventDefault();
                        const submitFormData = new FormData(form);
                        submitFormData.append('action', 'edit');
                        submitFormData.append('owner_id', data.data.owner_id);

                        console.log('Submitting data:', Object.fromEntries(submitFormData)); // Debug log

                        fetch('handle_owner.php', {
                            method: 'POST',
                            body: submitFormData
                        })
                        .then(response => response.json())
                        .then(result => {
                            console.log('Update response:', result); // Debug log
                            if (result.success) {
                                console.log(data)
                                location.reload();
                            } else {
                                console.log('Error updating owner: ' + result.message)
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to update owner');
                        });
                    };
                }
            } else {
                alert('Error loading owner data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load owner data');
        });
    }

    function handleOwnerDelete(button) {
        const ownerId = button.getAttribute('data-id');
        const modal = document.querySelector('#deleteOwnerModal');
        if (modal) {
            const confirmDeleteBtn = modal.querySelector('.confirm-delete');
            confirmDeleteBtn.onclick = function() {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('owner_id', ownerId);

                fetch('handle_owner.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            };
        }
    }

    function handleManagerView(button) {
        const managerId = button.getAttribute('data-id');
        const formData = new FormData();
        formData.append('action', 'view');
        formData.append('manager_id', managerId);

        fetch('handle_manager.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.querySelector('#viewManagerModal');
                if (modal) {
                    // Update modal content with manager details
                    document.getElementById('manager-view-first-name').textContent = data.data.first_name;
                    document.getElementById('manager-view-last-name').textContent = data.data.last_name;
                    document.getElementById('manager-view-email').textContent = data.data.email;
                    document.getElementById('manager-view-phone').textContent = data.data.phone;
                    document.getElementById('manager-view-username').textContent = data.data.username;
                    document.getElementById('manager-view-id-type').textContent = data.data.id_type;
                    document.getElementById('manager-view-id-number').textContent = data.data.id_num;
                    document.getElementById('manager-view-address').textContent = data.data.address;
                }
            } else {
                console.error('Error:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function handleManagerEdit(button) {
        const managerId = button.getAttribute('data-id');
        const formData = new FormData();
        formData.append('action', 'view');
        formData.append('manager_id', managerId);

        fetch('handle_manager.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.querySelector('#editManagerModal');
                if (modal) {
                    // Populate form fields
                    modal.querySelector('#manager-edit-manager-id').value = data.data.manager_id;
                    modal.querySelector('#manager-edit-first-name').value = data.data.first_name;
                    modal.querySelector('#manager-edit-last-name').value = data.data.last_name;
                    modal.querySelector('#manager-edit-email').value = data.data.email;
                    modal.querySelector('#manager-edit-phone').value = data.data.phone;
                    modal.querySelector('#manager-edit-username').value = data.data.username;
                    modal.querySelector('#manager-edit-id-type').value = data.data.id_type;
                    modal.querySelector('#manager-edit-id-number').value = data.data.id_num;
                    modal.querySelector('#manager-edit-address').value = data.data.address;

                    // Handle form submission
                    const form = modal.querySelector('#editManagerForm');
                    form.onsubmit = function(e) {
                        e.preventDefault();
                        const formData = new FormData(form);
                        formData.append('action', 'edit');

                        fetch('handle_manager.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                location.reload();
                            } else {
                                console.error('Error:', result.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    };
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function handleManagerDelete(button) {
        const managerId = button.getAttribute('data-id');
        const modal = document.querySelector('#deleteManagerModal');
        if (modal) {
            const confirmDeleteBtn = modal.querySelector('#confirmDeleteManager');
            confirmDeleteBtn.onclick = function() {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('manager_id', managerId);

                fetch('handle_manager.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            };
        }
    }

    function handleRentalView(button) {
        const rentalId = button.getAttribute('data-id');
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
                                    <p><strong>Price:</strong> $${data.data.price}</p>
                                    <p><strong>Property Type:</strong> ${data.data.property_type}</p>
                                    <p><strong>Property Size:</strong> ${data.data.property_size}</p>
                                    <p><strong>Location:</strong> ${data.data.region}, ${data.data.country}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Owner:</strong> ${data.data.owner_name}</p>
                                    <p><strong>Manager:</strong> ${data.data.manager_name}</p>
                                    <p><strong>Utilities:</strong> ${data.data.utilities}</p>
                                    <p><strong>Amenities:</strong> ${data.data.amenities}</p>
                                </div>
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
    }

    function handleRentalEdit(button) {
        // Populate dropdowns first to ensure they're loaded
        populateDropdowns();

        const rentalId = button.getAttribute('data-id');
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
                    // Set basic form values
                    form.querySelector('[name="rental_id"]').value = rentalId;
                    form.querySelector('[name="property_name"]').value = data.data.property_name;
                    form.querySelector('[name="price"]').value = data.data.price;
                    form.querySelector('[name="property_type"]').value = data.data.property_type;
                    form.querySelector('[name="property_size"]').value = data.data.property_size;
                    form.querySelector('[name="utilities"]').value = data.data.utilities;
                    form.querySelector('[name="country"]').value = data.data.country;
                    form.querySelector('[name="region"]').value = data.data.region;
                    form.querySelector('[name="subregion"]').value = data.data.subregion;
                    form.querySelector('[name="parish"]').value = data.data.parish;
                    form.querySelector('[name="ward"]').value = data.data.ward;
                    form.querySelector('[name="cell"]').value = data.data.cell;
                    form.querySelector('[name="landlord"]').value = data.data.landlord;
                    form.querySelector('[name="parking"]').value = data.data.parking;
                    form.querySelector('[name="convenience"]').value = data.data.convenience;
                    form.querySelector('[name="property_class"]').value = data.data.property_class;

                    // Set owner and manager
                    const ownerSelect = form.querySelector('[name="owner_id"]');
                    const managerSelect = form.querySelector('[name="manager_id"]');

                    if (ownerSelect) ownerSelect.value = data.data.owner_id || '';
                    if (managerSelect) managerSelect.value = data.data.manager_id || '';

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
                    try {
                        if (data.data.image_urls) {
                            if (typeof displayExistingImages === 'function') {
                                // Explicitly specify the container ID for rentals
                                displayExistingImages(data.data.image_urls, 'rental-current-images');
                            } else {
                                console.warn('displayExistingImages function not found');
                            }
                        } else {
                            console.log('No image URLs found in data');
                        }
                    } catch (error) {
                        console.error('Error displaying images:', error);
                    }
                }
            } else {
                alert('Error loading rental property: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load rental property data');
        });
    }

    function handleRentalDelete(button) {
        const rentalId = button.getAttribute('data-id');
        const modal = document.querySelector('#deleteRentalModal');
        if (modal) {
            const confirmDeleteBtn = modal.querySelector('#confirmDeleteRental');
            confirmDeleteBtn.onclick = function() {
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
                        location.reload();
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            };
        }
    }

    function handleSaleView(button) {
        const saleId = button.getAttribute('data-id');
        const formData = new FormData();
        formData.append('action', 'view');
        formData.append('sale_id', saleId);

        fetch('handle_sale.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.querySelector('#viewSaleModal');
                if (modal) {
                    const modalBody = modal.querySelector('.modal-body');
                    modalBody.innerHTML = `
                        <div class="rental-info">
                            <h4>${data.data.property_name}</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Price:</strong> $${data.data.price}</p>
                                    <p><strong>Property Type:</strong> ${data.data.property_type}</p>
                                    <p><strong>Property Size:</strong> ${data.data.property_size}</p>
                                    <p><strong>Location:</strong> ${data.data.region}, ${data.data.country}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Owner:</strong> ${data.data.owner_name}</p>
                                    <p><strong>Manager:</strong> ${data.data.manager_name}</p>
                                    <p><strong>Utilities:</strong> ${data.data.utilities}</p>
                                    <p><strong>Amenities:</strong> ${data.data.amenities}</p>
                                </div>
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
    }

    function handleSaleEdit(button) {
        console.log('handleSaleEdit called with button:', button);
        // Populate dropdowns first to ensure they're loaded
        populateDropdowns();

        const saleId = button.getAttribute('data-id');
        console.log('Sale ID:', saleId);
        const formData = new FormData();
        formData.append('action', 'view');
        formData.append('sale_id', saleId);

        console.log('Form data prepared:', Object.fromEntries(formData));

        console.log('Sending request to handle_sale.php');
        fetch('handle_sale.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.success) {
                const form = document.getElementById('editSaleForm');
                if (form) {
                    // Helper function to safely set form field values
                    const setFieldValue = (fieldName, value) => {
                        const field = form.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            field.value = value || '';
                        } else {
                            console.warn(`Field [name="${fieldName}"] not found in form`);
                        }
                    };

                    // Set basic form values with error handling
                    try {
                        setFieldValue('sale_id', saleId);
                        setFieldValue('property_name', data.data.property_name);
                        setFieldValue('price', data.data.price);
                        setFieldValue('property_type', data.data.property_type);
                        setFieldValue('property_size', data.data.property_size);
                        setFieldValue('utilities', data.data.utilities);
                        setFieldValue('country', data.data.country);
                        setFieldValue('region', data.data.region);
                        setFieldValue('subregion', data.data.subregion);
                        setFieldValue('parish', data.data.parish);
                        setFieldValue('ward', data.data.ward);
                        setFieldValue('cell', data.data.cell);
                        setFieldValue('title', data.data.title);
                    } catch (error) {
                        console.error('Error setting basic form values:', error);
                    }

                    // Set owner and manager using the helper function
                    try {
                        setFieldValue('owner_id', data.data.owner_id);
                        setFieldValue('manager_id', data.data.manager_id);
                    } catch (error) {
                        console.error('Error setting owner/manager values:', error);
                    }

                    // Helper function to set multiple select values
                    const setMultiSelectValues = (fieldName, values) => {
                        try {
                            const field = form.querySelector(`[name="${fieldName}"]`);
                            if (!field) {
                                console.warn(`Field [name="${fieldName}"] not found in form`);
                                return;
                            }

                            const valueArray = typeof values === 'string'
                                ? values.split(',')
                                : values || [];

                            Array.from(field.options).forEach(option => {
                                option.selected = valueArray.includes(option.value);
                            });

                            try {
                                $(field).trigger('change'); // Trigger Select2 update
                            } catch (e) {
                                console.warn('Error triggering change event:', e);
                            }
                        } catch (error) {
                            console.error(`Error setting values for ${fieldName}:`, error);
                        }
                    };

                    // Handle multiple selects (security and amenities)
                    try {
                        // Set security features if available
                        if (data.data.security) {
                            setMultiSelectValues('security[]', data.data.security);
                        }

                        // Set amenities if available
                        if (data.data.amenities) {
                            setMultiSelectValues('amenities[]', data.data.amenities);
                        }
                    } catch (error) {
                        console.error('Error setting multiple select values:', error);
                    }

                    // Display existing images
                    try {
                        if (data.data.image_urls) {
                            if (typeof displayExistingImages === 'function') {
                                // Explicitly specify the container ID for sales
                                displayExistingImages(data.data.image_urls, 'sale-current-images');
                            } else {
                                console.warn('displayExistingImages function not found');
                            }
                        } else {
                            console.log('No image URLs found in data');
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
    }

    function handleSaleDelete(button) {
        const saleId = button.getAttribute('data-id');
        const modal = document.querySelector('#deleteSaleModal');
        if (modal) {
            const confirmDeleteBtn = modal.querySelector('#confirmDeleteSale');
            confirmDeleteBtn.onclick = function() {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('sale_id', saleId);

                fetch('handle_sale.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            };
        }
    }

    function updateTable(data, handler, rowTemplate) {
        if (!handler.container) return;

        handler.container.innerHTML = '';

        if (!data.items || data.items.length === 0) {
            handler.container.innerHTML = `
                <tr><td colspan="7" class="text-center">No results found</td></tr>`;
            return;
        }

        data.items.forEach(item => {
            handler.container.innerHTML += rowTemplate(item);
        });

        updatePagination(data, handler);
        updateShowingEntries(data, handler);
    }

    function updatePagination(data, handler) {
        if (!handler.pagination) return;

        const pagination = [];

        pagination.push(`
            <li class="page-item ${data.page <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${data.page - 1}">Previous</a>
            </li>
        `);

        for (let i = 1; i <= data.total_pages; i++) {
            pagination.push(`
                <li class="page-item ${i === data.page ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        pagination.push(`
            <li class="page-item ${data.page >= data.total_pages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${data.page + 1}">Next</a>
            </li>
        `);

        handler.pagination.innerHTML = pagination.join('');

        handler.pagination.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                handler.currentPage = parseInt(this.dataset.page);
                performSearch(handler);
            });
        });
    }

    function updateShowingEntries(data, handler) {
        if (!handler.showingEntries) return;

        const start = (data.page - 1) * 10 + 1;
        const end = Math.min(data.page * 10, data.total);
        handler.showingEntries.textContent = `Showing ${start} to ${end} of ${data.total} entries`;
    }

    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Handle rental form submission
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

        // Check if there are any files being uploaded
        const fileInput = this.querySelector('input[type="file"]');
        if (fileInput && fileInput.files.length > 0) {
            console.log(`Files selected: ${fileInput.files.length}`);
            for (let i = 0; i < fileInput.files.length; i++) {
                console.log(`File ${i+1}: ${fileInput.files[i].name} (${fileInput.files[i].size} bytes)`);
            }
        } else {
            console.log('No files selected for upload');
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

    // Handle sale form submission
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

        // Check if there are any files being uploaded
        const fileInput = this.querySelector('input[type="file"]');
        if (fileInput && fileInput.files.length > 0) {
            console.log(`Files selected: ${fileInput.files.length}`);
            for (let i = 0; i < fileInput.files.length; i++) {
                console.log(`File ${i+1}: ${fileInput.files[i].name} (${fileInput.files[i].size} bytes)`);
            }
        } else {
            console.log('No files selected for upload');
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
});


