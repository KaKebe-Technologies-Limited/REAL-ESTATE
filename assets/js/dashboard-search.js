/**
 * Search functionality for dashboard listings
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard search script loaded');

    // Get all search input fields
    const searchInputs = document.querySelectorAll('.search-box input[data-type]');

    // Add event listeners to each search input
    searchInputs.forEach(input => {
        console.log('Found search input for type:', input.getAttribute('data-type'));

        input.addEventListener('keyup', debounce(function() {
            const searchTerm = this.value.trim();
            const type = this.getAttribute('data-type');

            console.log('Searching for:', searchTerm, 'in', type);

            if (searchTerm.length > 0) {
                searchProperties(searchTerm, type);
            } else {
                // If search is cleared, fetch all items instead of reloading the page
                fetchAllItems(type);
            }
        }, 500));
    });

    /**
     * Debounce function to limit how often a function can be called
     */
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(context, args);
            }, wait);
        };
    }

    /**
     * Search properties by type and term
     */
    function searchProperties(searchTerm, type) {
        // Show loading indicator
        showLoading(type);

        console.log(`Fetching search results from search_all.php?type=${type}&search=${encodeURIComponent(searchTerm)}`);

        // Make AJAX request to search_all.php
        fetch(`search_all.php?type=${type}&search=${encodeURIComponent(searchTerm)}`)
            .then(response => {
                console.log('Response received:', response);
                return response.json();
            })
            .then(data => {
                console.log('Search results:', data);

                if (data.success) {
                    updateTable(data.data.items, type);
                    updatePagination(data.data, type);
                    // Attach event handlers to the new elements only if we're handling search results
                    if (window.isHandlingSearchResults) {
                        attachEventHandlers(type);
                    }
                } else {
                    console.error('Search failed:', data.message);
                    showError(type, data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError(type, 'An error occurred while searching. Please try again.');
            })
            .finally(() => {
                hideLoading(type);
            });
    }

    /**
     * Fetch all items of a specific type
     */
    function fetchAllItems(type) {
        // Show loading indicator
        showLoading(type);

        console.log(`Fetching all items of type: ${type}`);

        // Make AJAX request to search_all.php with empty search term
        fetch(`search_all.php?type=${type}&search=`)
            .then(response => {
                console.log('Response received:', response);
                return response.json();
            })
            .then(data => {
                console.log('All items results:', data);

                if (data.success) {
                    updateTable(data.data.items, type);
                    updatePagination(data.data, type);
                    // Attach event handlers to the new elements only if we're handling search results
                    if (window.isHandlingSearchResults) {
                        attachEventHandlers(type);
                    }
                } else {
                    console.error('Fetch failed:', data.message);
                    showError(type, data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError(type, 'An error occurred while fetching data. Please try again.');
            })
            .finally(() => {
                hideLoading(type);
            });
    }

    /**
     * Update table with search results
     */
    function updateTable(items, type) {
        let tableBody;
        let emptyMessage;

        switch (type) {
            case 'rentals':
                tableBody = document.querySelector('#rental-listing .table tbody');
                emptyMessage = 'No rental properties found matching your search.';
                break;
            case 'sales':
                tableBody = document.querySelector('#sales-listing .table tbody');
                emptyMessage = 'No sales properties found matching your search.';
                break;
            case 'owners':
                tableBody = document.querySelector('#owners-listing .table tbody');
                emptyMessage = 'No property owners found matching your search.';
                break;
            case 'managers':
                tableBody = document.querySelector('#managers-listing .table tbody');
                emptyMessage = 'No property managers found matching your search.';
                break;
            default:
                console.error('Unknown type:', type);
                return;
        }

        if (!tableBody) {
            console.error(`Table body for ${type} not found`);
            return;
        }

        // Set the flag to indicate we're handling search results
        window.isHandlingSearchResults = true;

        // Clear existing rows
        tableBody.innerHTML = '';

        if (items.length === 0) {
            // No results found
            const row = document.createElement('tr');
            row.innerHTML = `<td colspan="6" class="text-center">${emptyMessage}</td>`;
            tableBody.appendChild(row);
            return;
        }

        // Add new rows based on type
        items.forEach(item => {
            const row = document.createElement('tr');

            switch (type) {
                case 'rentals':
                    row.innerHTML = `
                        <td>${escapeHtml(item.property_name)}</td>
                        <td>${escapeHtml(item.owner_name || 'Not Assigned')}</td>
                        <td>${escapeHtml(item.location || item.parish + ', ' + item.ward)}</td>
                        <td>UGX ${escapeHtml(item.price || item.rent)}</td>
                        <td>
                            ${escapeHtml(item.status)}
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary view-rental search-result-btn"
                                        data-id="${escapeHtml(item.property_id)}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewRentalModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning edit-rental search-result-btn"
                                        data-id="${escapeHtml(item.property_id)}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editRentalModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-rental search-result-btn"
                                        data-id="${escapeHtml(item.property_id)}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteRentalModal">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    break;
                case 'sales':
                    row.innerHTML = `
                        <td>${escapeHtml(item.property_name)}</td>
                        <td>${escapeHtml(item.owner_name || 'Not Assigned')}</td>
                        <td>${escapeHtml(item.location || item.parish + ', ' + item.ward)}</td>
                        <td>UGX ${escapeHtml(item.price)}</td>
                        <td>
                            ${escapeHtml(item.status || item.property_type || 'N/A')}
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary view-sale search-result-btn"
                                        data-id="${escapeHtml(item.property_id)}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewSaleModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning edit-sale search-result-btn"
                                        data-id="${escapeHtml(item.property_id)}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editSaleModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-sale search-result-btn"
                                        data-id="${escapeHtml(item.property_id)}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteSaleModal">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    break;
                case 'managers':
                    row.innerHTML = `
                        <td>${escapeHtml(item.username || item.manager_name)}</td>
                        <td>${escapeHtml(item.property_count || '0')}</td>
                        <td>${escapeHtml(item.phone || 'N/A')}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary view-manager search-result-btn"
                                        data-id="${escapeHtml(item.manager_id)}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewManagerModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning edit-manager search-result-btn"
                                        data-id="${escapeHtml(item.manager_id)}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editManagerModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-manager search-result-btn"
                                        data-id="${escapeHtml(item.manager_id)}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteManagerModal">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    break;
                case 'owners':
                    row.innerHTML = `
                        <td>${escapeHtml(item.username)}</td>
                        <td>${escapeHtml(item.property_count || '0')}</td>
                        <td>${escapeHtml(item.subscription_end || 'N/A')}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary view-owner"
                                        data-id="${escapeHtml(item.owner_id)}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewOwnerModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    break;
            }

            tableBody.appendChild(row);
        });
    }

    /**
     * Update pagination with search results
     */
    function updatePagination(data, type) {
        let paginationContainer;
        let showingEntriesContainer;

        switch (type) {
            case 'rentals':
                paginationContainer = document.querySelector('#rental-listing .pagination');
                showingEntriesContainer = document.querySelector('#rental-listing .showing-entries');
                break;
            case 'sales':
                paginationContainer = document.querySelector('#sales-listing .pagination');
                showingEntriesContainer = document.querySelector('#sales-listing .showing-entries');
                break;
            case 'owners':
                paginationContainer = document.querySelector('#owners-listing .pagination');
                showingEntriesContainer = document.querySelector('#owners-listing .showing-entries');
                break;
            case 'managers':
                paginationContainer = document.querySelector('#managers-listing .pagination');
                showingEntriesContainer = document.querySelector('#managers-listing .showing-entries');
                break;
            default:
                console.error('Unknown type:', type);
                return;
        }

        if (!paginationContainer || !showingEntriesContainer) {
            console.error(`Pagination container for ${type} not found`);
            return;
        }

        // Update showing entries text
        const start = (data.page - 1) * 10 + 1;
        const end = Math.min(data.page * 10, data.total);
        showingEntriesContainer.textContent = `Showing ${start} to ${end} of ${data.total} entries`;

        // Clear existing pagination
        paginationContainer.innerHTML = '';

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${data.page <= 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="javascript:void(0);" data-page="${data.page - 1}">Previous</a>`;
        paginationContainer.appendChild(prevLi);

        // Page numbers
        for (let i = 1; i <= data.total_pages; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === data.page ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>`;
            paginationContainer.appendChild(pageLi);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${data.page >= data.total_pages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="javascript:void(0);" data-page="${data.page + 1}">Next</a>`;
        paginationContainer.appendChild(nextLi);

        // Add event listeners to pagination links
        paginationContainer.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (page > 0) {
                    const searchTerm = document.querySelector(`.search-box input[data-type="${type}"]`).value.trim();
                    if (searchTerm.length > 0) {
                        fetch(`search_all.php?type=${type}&search=${encodeURIComponent(searchTerm)}&page=${page}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    updateTable(data.data.items, type);
                                    updatePagination(data.data, type);
                                }
                            })
                            .catch(error => console.error('Error:', error));
                    }
                }
            });
        });
    }

    /**
     * Show loading indicator
     */
    function showLoading(type) {
        // Implementation depends on your UI
        console.log(`Loading ${type}...`);

        // Add a loading overlay to the table
        const tableContainer = document.querySelector(`#${type}-listing .table-responsive`);
        if (tableContainer) {
            // Check if loading overlay already exists
            if (!tableContainer.querySelector('.loading-overlay')) {
                const loadingOverlay = document.createElement('div');
                loadingOverlay.className = 'loading-overlay';
                loadingOverlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
                loadingOverlay.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255,255,255,0.7); display: flex; justify-content: center; align-items: center; z-index: 1000;';

                // Make sure the container has position relative
                tableContainer.style.position = 'relative';
                tableContainer.appendChild(loadingOverlay);
            }
        }
    }

    /**
     * Hide loading indicator
     */
    function hideLoading(type) {
        // Implementation depends on your UI
        console.log(`Finished loading ${type}`);

        // Remove the loading overlay
        const tableContainer = document.querySelector(`#${type}-listing .table-responsive`);
        if (tableContainer) {
            const loadingOverlay = tableContainer.querySelector('.loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.remove();
            }
        }
    }

    /**
     * Show error message
     */
    function showError(type, message) {
        // Implementation depends on your UI
        console.error(`Error in ${type}: ${message}`);

        const tableBody = document.querySelector(`#${type}-listing .table tbody`);
        if (tableBody) {
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${message}</td></tr>`;
        }
    }

    /**
     * Attach event handlers to the newly created elements
     */
    function attachEventHandlers(type) {
        console.log(`Attaching event handlers for ${type}`);

        // Only attach event handlers if we're handling search results
        if (!window.isHandlingSearchResults) {
            console.log('Not handling search results, skipping event handler attachment');
            return;
        }

        switch (type) {
            case 'rentals':
                // View rental handlers
                document.querySelectorAll('.view-rental.search-result-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const rentalId = this.getAttribute('data-id');
                        console.log('View rental clicked:', rentalId);

                        // Create form data
                        const formData = new FormData();
                        formData.append('action', 'view');
                        formData.append('rental_id', rentalId);

                        // Send AJAX request
                        fetch('handle_rental.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Populate modal with data
                                const modal = document.getElementById('viewRentalModal');
                                if (modal) {
                                    const modalBody = modal.querySelector('.modal-body');
                                    if (modalBody) {
                                        // Create HTML content for the modal
                                        let content = `
                                            <div class="rental-details">
                                                <h4>${escapeHtml(data.data.property_name)}</h4>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Location:</strong> ${escapeHtml(data.data.parish)}, ${escapeHtml(data.data.ward)}</p>
                                                        <p><strong>Price:</strong> UGX ${escapeHtml(data.data.price)}</p>
                                                        <p><strong>Property Type:</strong> ${escapeHtml(data.data.property_type || 'N/A')}</p>
                                                        <p><strong>Property Size:</strong> ${escapeHtml(data.data.property_size || 'N/A')}</p>
                                                        <p><strong>Bedrooms:</strong> ${escapeHtml(data.data.bedrooms || '0')}</p>
                                                        <p><strong>Bathrooms:</strong> ${escapeHtml(data.data.bathrooms || '0')}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Owner:</strong> ${escapeHtml(data.data.owner_name || 'Not Assigned')}</p>
                                                        <p><strong>Manager:</strong> ${escapeHtml(data.data.manager_name || 'Not Assigned')}</p>
                                                        <p><strong>Status:</strong> ${escapeHtml(data.data.status || 'N/A')}</p>
                                                        <p><strong>Utilities:</strong> ${escapeHtml(data.data.utilities || 'N/A')}</p>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <h5>Description</h5>
                                                    <p>${escapeHtml(data.data.description || 'No description available')}</p>
                                                </div>
                                            `;

                                        // Add images if available
                                        if (data.data.image_urls && data.data.image_urls.length > 0) {
                                            content += '<div class="property-images mt-3"><h5>Property Images</h5><div class="row">';
                                            data.data.image_urls.forEach(url => {
                                                content += `
                                                    <div class="col-md-4 mb-2">
                                                        <img src="${escapeHtml(url)}" class="img-fluid img-thumbnail" alt="Property Image">
                                                    </div>
                                                `;
                                            });
                                            content += '</div></div>';
                                        } else {
                                            content += '<p class="mt-3">No images available</p>';
                                        }

                                        content += '</div>';
                                        modalBody.innerHTML = content;
                                    }
                                }
                            } else {
                                console.error('Error fetching rental details:', data.message);
                                alert('Error loading rental details. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while loading rental details');
                        });
                    });
                });

                // Edit rental handlers
                document.querySelectorAll('.edit-rental.search-result-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const rentalId = this.getAttribute('data-id');
                        console.log('Edit rental clicked:', rentalId);

                        // Create form data
                        const formData = new FormData();
                        formData.append('action', 'view');
                        formData.append('rental_id', rentalId);

                        // Send AJAX request
                        fetch('handle_rental.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const form = document.getElementById('editRentalForm');
                                if (form) {
                                    // Populate form fields
                                    form.querySelector('[name="rental_id"]').value = rentalId;
                                    form.querySelector('[name="property_name"]').value = data.data.property_name || '';
                                    form.querySelector('[name="price"]').value = data.data.price || '';

                                    // Set select fields if they exist
                                    if (form.querySelector('[name="property_type"]')) {
                                        form.querySelector('[name="property_type"]').value = data.data.property_type || '';
                                    }

                                    if (form.querySelector('[name="property_size"]')) {
                                        form.querySelector('[name="property_size"]').value = data.data.property_size || '';
                                    }

                                    if (form.querySelector('[name="utilities"]')) {
                                        form.querySelector('[name="utilities"]').value = data.data.utilities || '';
                                    }

                                    if (form.querySelector('[name="status"]')) {
                                        form.querySelector('[name="status"]').value = data.data.status || '';
                                    }

                                    if (form.querySelector('[name="property_class"]')) {
                                        form.querySelector('[name="property_class"]').value = data.data.property_class || '';
                                    }

                                    // Set location fields
                                    if (form.querySelector('[name="parish"]')) {
                                        form.querySelector('[name="parish"]').value = data.data.parish || '';
                                    }

                                    if (form.querySelector('[name="ward"]')) {
                                        form.querySelector('[name="ward"]').value = data.data.ward || '';
                                    }

                                    // Set owner and manager if applicable
                                    if (form.querySelector('[name="owner_id"]')) {
                                        form.querySelector('[name="owner_id"]').value = data.data.owner_id || '';
                                    }

                                    if (form.querySelector('[name="manager_id"]')) {
                                        form.querySelector('[name="manager_id"]').value = data.data.manager_id || '';
                                    }

                                    // Display existing images if available
                                    const imagesContainer = form.querySelector('#rental-current-images');
                                    if (imagesContainer && data.data.image_urls) {
                                        imagesContainer.innerHTML = '';
                                        data.data.image_urls.forEach(url => {
                                            imagesContainer.innerHTML += `
                                                <div class="image-preview">
                                                    <img src="${escapeHtml(url)}" class="img-thumbnail" style="max-width: 100px;">
                                                    <button type="button" class="btn btn-sm btn-danger remove-image" data-url="${escapeHtml(url)}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            `;
                                        });
                                    }
                                }
                            } else {
                                console.error('Error fetching rental details:', data.message);
                                alert('Error loading rental details. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while loading rental details');
                        });
                    });
                });

                // Delete rental handlers
                document.querySelectorAll('.delete-rental.search-result-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const rentalId = this.getAttribute('data-id');
                        console.log('Delete rental clicked:', rentalId);

                        const modal = document.getElementById('deleteRentalModal');
                        if (modal) {
                            const confirmButton = modal.querySelector('.confirm-delete');
                            if (confirmButton) {
                                // Remove any existing event listeners
                                const newConfirmButton = confirmButton.cloneNode(true);
                                confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);

                                // Set up the confirm button to delete the rental
                                newConfirmButton.onclick = function() {
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
                                            alert('Rental property deleted successfully');
                                            // Close the modal
                                            const bsModal = bootstrap.Modal.getInstance(modal);
                                            if (bsModal) {
                                                bsModal.hide();
                                            }
                                            // Refresh the data
                                            fetchAllItems('rentals');
                                        } else {
                                            console.error('Error deleting rental:', data.message);
                                            alert('Error deleting rental property. Please try again.');
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert('An error occurred while deleting the rental property');
                                    });
                                };
                            }
                        }
                    });
                });
                break;

            case 'sales':
                // View sale handlers
                document.querySelectorAll('.view-sale.search-result-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const saleId = this.getAttribute('data-id');
                        console.log('View sale clicked:', saleId);

                        // Create form data
                        const formData = new FormData();
                        formData.append('action', 'view');
                        formData.append('property_id', saleId); // Use property_id as that's what the backend expects

                        // Send AJAX request
                        fetch('handle_sale.php', {
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
                            console.log('Parsed data:', data);
                            if (data.success) {
                                // Populate modal with data
                                const modal = document.getElementById('viewSaleModal');
                                if (modal) {
                                    const modalBody = modal.querySelector('.modal-body');
                                    if (modalBody) {
                                        // Create HTML content for the modal
                                        let content = `
                                            <div class="sale-details">
                                                <h4>${escapeHtml(data.data.property_name)}</h4>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Location:</strong> ${escapeHtml(data.data.parish || data.data.region)}, ${escapeHtml(data.data.ward || data.data.country)}</p>
                                                        <p><strong>Price:</strong> UGX ${escapeHtml(data.data.price)}</p>
                                                        <p><strong>Property Type:</strong> ${escapeHtml(data.data.property_type || 'N/A')}</p>
                                                        <p><strong>Property Size:</strong> ${escapeHtml(data.data.property_size || 'N/A')}</p>
                                                        <p><strong>Bedrooms:</strong> ${escapeHtml(data.data.bedrooms || '0')}</p>
                                                        <p><strong>Bathrooms:</strong> ${escapeHtml(data.data.bathrooms || '0')}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Owner:</strong> ${escapeHtml(data.data.owner_name || 'Not Assigned')}</p>
                                                        <p><strong>Manager:</strong> ${escapeHtml(data.data.manager_name || 'Not Assigned')}</p>
                                                        <p><strong>Status:</strong> ${escapeHtml(data.data.status || data.data.property_type || 'N/A')}</p>
                                                        <p><strong>Utilities:</strong> ${escapeHtml(data.data.utilities || 'N/A')}</p>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <h5>Description</h5>
                                                    <p>${escapeHtml(data.data.description || 'No description available')}</p>
                                                </div>
                                            `;

                                        // Add images if available
                                        if (data.data.image_urls && data.data.image_urls.length > 0) {
                                            content += '<div class="property-images mt-3"><h5>Property Images</h5><div class="row">';
                                            data.data.image_urls.forEach(url => {
                                                content += `
                                                    <div class="col-md-4 mb-2">
                                                        <img src="${escapeHtml(url)}" class="img-fluid img-thumbnail" alt="Property Image">
                                                    </div>
                                                `;
                                            });
                                            content += '</div></div>';
                                        } else if (data.data.images) {
                                            // Handle comma-separated images string
                                            const imageUrls = data.data.images.split(',').filter(url => url.trim() !== '');
                                            if (imageUrls.length > 0) {
                                                content += '<div class="property-images mt-3"><h5>Property Images</h5><div class="row">';
                                                imageUrls.forEach(url => {
                                                    content += `
                                                        <div class="col-md-4 mb-2">
                                                            <img src="${escapeHtml(url.trim())}" class="img-fluid img-thumbnail" alt="Property Image">
                                                        </div>
                                                    `;
                                                });
                                                content += '</div></div>';
                                            } else {
                                                content += '<p class="mt-3">No images available</p>';
                                            }
                                        } else {
                                            content += '<p class="mt-3">No images available</p>';
                                        }

                                        content += '</div>';
                                        modalBody.innerHTML = content;
                                    }
                                }
                            } else {
                                console.error('Error fetching sale details:', data.message);
                                alert('Error loading sale details: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while loading sale details: ' + error.message);
                        });
                    });
                });

                // Edit sale handlers
                document.querySelectorAll('.edit-sale.search-result-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const saleId = this.getAttribute('data-id');
                        console.log('Edit sale clicked:', saleId);

                        // Populate dropdowns first to ensure they're loaded
                        if (typeof populateDropdowns === 'function') {
                            populateDropdowns();
                        }

                        // Create form data
                        const formData = new FormData();
                        formData.append('action', 'view');
                        formData.append('sale_id', saleId);

                        // Send AJAX request
                        fetch('handle_sale.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const form = document.getElementById('editSaleForm');
                                if (form) {
                                    console.log('Setting form values for sale:', data.data);

                                    // Populate form fields
                                    form.querySelector('[name="sale_id"]').value = saleId;
                                    form.querySelector('[name="property_name"]').value = data.data.property_name || '';
                                    form.querySelector('[name="price"]').value = data.data.price || '';

                                    // Set select fields if they exist
                                    if (form.querySelector('[name="property_type"]')) {
                                        form.querySelector('[name="property_type"]').value = data.data.property_type || '';
                                    }

                                    if (form.querySelector('[name="property_size"]')) {
                                        form.querySelector('[name="property_size"]').value = data.data.property_size || '';
                                    }

                                    // Set bedrooms, bathrooms, and description fields
                                    if (form.querySelector('[name="bedrooms"]')) {
                                        form.querySelector('[name="bedrooms"]').value = data.data.bedrooms || '0';
                                    }

                                    if (form.querySelector('[name="bathrooms"]')) {
                                        form.querySelector('[name="bathrooms"]').value = data.data.bathrooms || '0';
                                    }

                                    if (form.querySelector('[name="description"]')) {
                                        form.querySelector('[name="description"]').value = data.data.description || '';
                                    }

                                    if (form.querySelector('[name="utilities"]')) {
                                        form.querySelector('[name="utilities"]').value = data.data.utilities || '';
                                    }

                                    if (form.querySelector('[name="status"]')) {
                                        form.querySelector('[name="status"]').value = data.data.status || '';
                                    }

                                    // Set location fields
                                    if (form.querySelector('[name="parish"]')) {
                                        form.querySelector('[name="parish"]').value = data.data.parish || '';
                                    }

                                    if (form.querySelector('[name="ward"]')) {
                                        form.querySelector('[name="ward"]').value = data.data.ward || '';
                                    }

                                    // Set region and country if they exist
                                    if (form.querySelector('[name="region"]')) {
                                        form.querySelector('[name="region"]').value = data.data.region || '';
                                    }

                                    if (form.querySelector('[name="country"]')) {
                                        form.querySelector('[name="country"]').value = data.data.country || '';
                                    }

                                    // Set owner and manager if applicable
                                    if (form.querySelector('[name="owner_id"]')) {
                                        form.querySelector('[name="owner_id"]').value = data.data.owner_id || '';
                                    }

                                    if (form.querySelector('[name="manager_id"]')) {
                                        form.querySelector('[name="manager_id"]').value = data.data.manager_id || '';
                                    }

                                    // Display existing images if available
                                    const imagesContainer = form.querySelector('#sale-current-images');
                                    if (imagesContainer) {
                                        imagesContainer.innerHTML = '';

                                        // Check if image_urls exists
                                        if (data.data.image_urls && data.data.image_urls.length > 0) {
                                            data.data.image_urls.forEach(url => {
                                                imagesContainer.innerHTML += `
                                                    <div class="image-preview">
                                                        <img src="${escapeHtml(url)}" class="img-thumbnail" style="max-width: 100px;">
                                                        <button type="button" class="btn btn-sm btn-danger remove-image" data-url="${escapeHtml(url)}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                `;
                                            });
                                        } else if (data.data.images) {
                                            // Handle comma-separated images string
                                            const imageUrls = data.data.images.split(',').filter(url => url.trim() !== '');
                                            imageUrls.forEach(url => {
                                                imagesContainer.innerHTML += `
                                                    <div class="image-preview">
                                                        <img src="${escapeHtml(url.trim())}" class="img-thumbnail" style="max-width: 100px;">
                                                        <button type="button" class="btn btn-sm btn-danger remove-image" data-url="${escapeHtml(url.trim())}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                `;
                                            });
                                        }
                                    }
                                }
                            } else {
                                console.error('Error fetching sale details:', data.message);
                                alert('Error loading sale details. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while loading sale details');
                        });
                    });
                });

                // Delete sale handlers
                document.querySelectorAll('.delete-sale.search-result-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const saleId = this.getAttribute('data-id');
                        console.log('Delete sale clicked:', saleId);

                        const modal = document.getElementById('deleteSaleModal');
                        if (modal) {
                            // Store the sale ID in a data attribute on the modal for reference
                            modal.setAttribute('data-sale-id', saleId);

                            const confirmButton = modal.querySelector('.confirm-delete');
                            if (confirmButton) {
                                // Remove any existing event listeners
                                const newConfirmButton = confirmButton.cloneNode(true);
                                confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);

                                // Set up the confirm button to delete the sale
                                newConfirmButton.onclick = function() {
                                    // Get the sale ID from the modal's data attribute
                                    const saleIdToDelete = modal.getAttribute('data-sale-id');
                                    console.log('Confirming delete for sale ID:', saleIdToDelete);

                                    const formData = new FormData();
                                    formData.append('action', 'delete');
                                    formData.append('property_id', saleIdToDelete); // Use property_id as that's what the backend expects

                                    fetch('handle_sale.php', {
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
                                            alert('Sale property deleted successfully');
                                            // Close the modal
                                            const bsModal = bootstrap.Modal.getInstance(modal);
                                            if (bsModal) {
                                                bsModal.hide();
                                            }
                                            // Refresh the data
                                            fetchAllItems('sales');
                                        } else {
                                            console.error('Error deleting sale:', data.message);
                                            alert('Error deleting sale property: ' + (data.message || 'Unknown error'));
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert('An error occurred while deleting the sale property: ' + error.message);
                                    });
                                };
                            }
                        }
                    });
                });
                break;

            case 'managers':
                // View manager handlers
                document.querySelectorAll('.view-manager.search-result-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const managerId = this.getAttribute('data-id');
                        console.log('View manager clicked:', managerId);

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
                            console.log('Parsed data:', data);
                            if (data.success) {
                                // Populate modal with data
                                const modal = document.getElementById('viewManagerModal');
                                if (modal) {
                                    const modalBody = modal.querySelector('.modal-body');
                                    if (modalBody) {
                                        // Create HTML content for the modal
                                        let content = `
                                            <div class="manager-details">
                                                <h4>${escapeHtml(data.data.first_name)} ${escapeHtml(data.data.last_name)}</h4>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Username:</strong> ${escapeHtml(data.data.username)}</p>
                                                        <p><strong>Email:</strong> ${escapeHtml(data.data.email)}</p>
                                                        <p><strong>Phone:</strong> ${escapeHtml(data.data.phone)}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Properties Managed:</strong> ${escapeHtml(data.data.property_count || '0')}</p>
                                                        <p><strong>ID Type:</strong> ${escapeHtml(data.data.id_type || 'N/A')}</p>
                                                        <p><strong>ID Number:</strong> ${escapeHtml(data.data.id_num || 'N/A')}</p>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-12">
                                                        <p><strong>Address:</strong> ${escapeHtml(data.data.address || 'N/A')}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        `;

                                        // Add profile picture if available
                                        if (data.data.profile_picture) {
                                            content = `
                                                <div class="text-center mb-4">
                                                    <img src="${escapeHtml(data.data.profile_picture)}" class="img-fluid rounded-circle" style="max-width: 150px;" alt="Profile Picture">
                                                </div>
                                            ` + content;
                                        }

                                        modalBody.innerHTML = content;
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
                document.querySelectorAll('.edit-manager.search-result-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const managerId = this.getAttribute('data-id');
                        console.log('Edit manager clicked:', managerId);

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
                            console.log('Parsed data:', data);
                            if (data.success) {
                                const form = document.getElementById('editManagerForm');
                                if (form) {
                                    // Populate form fields
                                    form.querySelector('[name="manager_id"]').value = managerId;
                                    form.querySelector('[name="first_name"]').value = data.data.first_name || '';
                                    form.querySelector('[name="last_name"]').value = data.data.last_name || '';
                                    form.querySelector('[name="email"]').value = data.data.email || '';
                                    form.querySelector('[name="phone"]').value = data.data.phone || '';
                                    form.querySelector('[name="username"]').value = data.data.username || '';

                                    // Set ID fields if they exist
                                    if (form.querySelector('[name="id_type"]')) {
                                        form.querySelector('[name="id_type"]').value = data.data.id_type || '';
                                    }

                                    if (form.querySelector('[name="id_num"]')) {
                                        form.querySelector('[name="id_num"]').value = data.data.id_num || '';
                                    }

                                    // Set address field if it exists
                                    if (form.querySelector('[name="address"]')) {
                                        form.querySelector('[name="address"]').value = data.data.address || '';
                                    }

                                    // Display profile picture if available
                                    const profilePreview = form.querySelector('#manager-edit-profile-preview');
                                    if (profilePreview && data.data.profile_picture) {
                                        profilePreview.src = data.data.profile_picture;
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
                document.querySelectorAll('.delete-manager.search-result-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const managerId = this.getAttribute('data-id');
                        console.log('Delete manager clicked:', managerId);

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
                                            // Refresh the data
                                            fetchAllItems('managers');
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
                break;
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Initialize by attaching event handlers to existing elements
    document.addEventListener('DOMContentLoaded', function() {
        // Only attach event handlers for search results
        // The original buttons will use their existing handlers from the specific JS files

        // Add form submission handlers
        setupFormHandlers();

        // Add a flag to track if we're handling search results
        window.isHandlingSearchResults = false;
    });

    /**
     * Set up form submission handlers
     */
    function setupFormHandlers() {
        // Handle rental form submission
        const editRentalForm = document.getElementById('editRentalForm');
        if (editRentalForm) {
            editRentalForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('action', 'edit');

                // Add existing images that weren't removed
                const imagesContainer = this.querySelector('#rental-current-images');
                if (imagesContainer) {
                    const remainingImages = Array.from(imagesContainer.querySelectorAll('img')).map(img => img.src);
                    formData.append('existing_images', JSON.stringify(remainingImages));
                }

                fetch('handle_rental.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Rental property updated successfully');
                        // Close the modal
                        const modal = document.getElementById('editRentalModal');
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                        // Refresh the data
                        fetchAllItems('rentals');
                    } else {
                        console.error('Error updating rental:', data.message);
                        alert('Error updating rental property. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the rental property');
                });
            });
        }

        // Handle manager form submission
        const editManagerForm = document.getElementById('editManagerForm');
        if (editManagerForm) {
            editManagerForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Manager form submitted');

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
                        // Refresh the data
                        fetchAllItems('managers');
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

        // Handle sale form submission
        const editSaleForm = document.getElementById('editSaleForm');
        if (editSaleForm) {
            editSaleForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Sale form submitted');

                // Get the sale ID first
                const saleId = this.querySelector('[name="sale_id"]').value;
                if (!saleId) {
                    alert('Missing property ID');
                    return;
                }

                const formData = new FormData(this);
                formData.set('action', 'edit');
                formData.set('property_id', saleId); // Ensure we use property_id for PHP

                // Add existing images that weren't removed
                const imagesContainer = this.querySelector('#sale-current-images');
                if (imagesContainer) {
                    const remainingImages = Array.from(imagesContainer.querySelectorAll('img')).map(img => {
                        // Extract the path from the URL
                        const url = new URL(img.src, window.location.origin);
                        return url.pathname.substring(1); // Remove leading slash
                    });

                    console.log('Remaining images:', remainingImages);

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
                .then(response => response.text()) // First get the raw response
                .then(text => {
                    console.log('Raw response:', text); // Log the raw response
                    try {
                        return JSON.parse(text); // Then try to parse it
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        throw new Error('Server returned invalid JSON: ' + text);
                    }
                })
                .then(data => {
                    console.log('Parsed response:', data);
                    if (data.success) {
                        alert('Sale property updated successfully');
                        // Close the modal
                        const modal = document.getElementById('editSaleModal');
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                        // Refresh the data
                        fetchAllItems('sales');
                    } else {
                        console.error('Error updating sale:', data.message);
                        alert('Error updating sale property: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the sale property: ' + error.message);
                });
            });
        }

        // Handle removing images
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-image')) {
                const button = e.target.closest('.remove-image');
                const imagePreview = button.closest('.image-preview');
                if (imagePreview) {
                    imagePreview.remove();
                }
            }
        });
    }
});
