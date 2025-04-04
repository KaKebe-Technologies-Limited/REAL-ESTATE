document.addEventListener('DOMContentLoaded', function() {
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
                    <span class="badge ${row.availability === 'Available' ? 'bg-success' : 'bg-danger'}">
                        ${escapeHtml(row.availability || 'N/A')}
                    </span>
                </td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
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
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
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
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> View
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
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </div>
                </td>
            </tr>
        `);
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
});

