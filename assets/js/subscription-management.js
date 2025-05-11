/**
 * Subscription Management JavaScript
 * 
 * Handles the subscription management functionality in the admin dashboard
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let currentPage = 1;
    let totalPages = 1;
    let searchTerm = '';
    let statusFilter = 'all';
    
    // Add function to window object to show subscriptions
    window.showOwnerSubscriptions = function() {
        // Hide all content sections
        document.querySelectorAll('.main-content > div').forEach(div => {
            div.style.display = 'none';
        });
        
        // Show subscriptions section
        document.getElementById('owner-subscriptions').style.display = 'block';
        
        // Load subscriptions data
        loadSubscriptions();
    };
    
    // Load subscriptions data
    function loadSubscriptions(page = 1, search = '', status = 'all') {
        // Show loading indicator
        document.getElementById('subscriptions-table-body').innerHTML = `
            <tr>
                <td colspan="8" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading subscription data...</p>
                </td>
            </tr>
        `;
        
        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'get_subscriptions');
        formData.append('page', page);
        formData.append('limit', 10);
        formData.append('search', search);
        
        // Send request to server
        fetch('admin_manage_subscriptions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update pagination variables
                currentPage = data.pagination.current_page;
                totalPages = data.pagination.total_pages;
                
                // Render subscriptions
                renderSubscriptions(data.subscriptions, status);
                
                // Render pagination
                renderPagination(currentPage, totalPages);
            } else {
                // Show error message
                document.getElementById('subscriptions-table-body').innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            ${data.message || 'Failed to load subscription data'}
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading subscriptions:', error);
            document.getElementById('subscriptions-table-body').innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        An error occurred while loading subscription data
                    </td>
                </tr>
            `;
        });
    }
    
    // Render subscriptions table
    function renderSubscriptions(subscriptions, statusFilter) {
        const tableBody = document.getElementById('subscriptions-table-body');
        
        // Filter subscriptions by status if needed
        if (statusFilter !== 'all') {
            subscriptions = subscriptions.filter(sub => sub.subscription_status === statusFilter);
        }
        
        // Check if there are any subscriptions
        if (subscriptions.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        No subscription data found
                    </td>
                </tr>
            `;
            return;
        }
        
        // Build table rows
        let html = '';
        subscriptions.forEach(sub => {
            // Determine status badge class
            let statusClass = 'bg-secondary';
            if (sub.subscription_status === 'active') statusClass = 'bg-success';
            if (sub.subscription_status === 'expired') statusClass = 'bg-danger';
            if (sub.subscription_status === 'pending') statusClass = 'bg-warning';
            
            // Format days remaining
            let daysRemaining = '';
            if (sub.days_remaining !== null) {
                if (sub.days_remaining < 0) {
                    daysRemaining = `<span class="text-danger">Expired ${Math.abs(sub.days_remaining)} days ago</span>`;
                } else if (sub.days_remaining === 0) {
                    daysRemaining = `<span class="text-warning">Expires today</span>`;
                } else if (sub.days_remaining <= 7) {
                    daysRemaining = `<span class="text-warning">${sub.days_remaining} days left</span>`;
                } else {
                    daysRemaining = `<span class="text-success">${sub.days_remaining} days left</span>`;
                }
            } else {
                daysRemaining = '<span class="text-muted">Not set</span>';
            }
            
            // Build property count
            const propertyCount = `
                <span class="badge bg-primary me-1" title="Rental Properties">
                    <i class="fas fa-key"></i> ${sub.rental_count}
                </span>
                <span class="badge bg-info" title="Sales Properties">
                    <i class="fas fa-home"></i> ${sub.sales_count}
                </span>
            `;
            
            html += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div>
                                <div class="fw-bold">${sub.first_name} ${sub.last_name}</div>
                                <small class="text-muted">${sub.username}</small>
                            </div>
                        </div>
                    </td>
                    <td>${sub.email}</td>
                    <td><span class="badge ${statusClass}">${sub.subscription_status.toUpperCase()}</span></td>
                    <td>${sub.subscription_start_date || 'Not set'}</td>
                    <td>${sub.subscription_end_date || 'Not set'}</td>
                    <td>${daysRemaining}</td>
                    <td>${propertyCount}</td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary extend-subscription-btn" 
                                    data-owner-id="${sub.owner_id}" 
                                    data-owner-name="${sub.first_name} ${sub.last_name}"
                                    data-status="${sub.subscription_status}"
                                    data-end-date="${sub.subscription_end_date || 'Not set'}">
                                <i class="fas fa-calendar-plus"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info view-history-btn"
                                    data-owner-id="${sub.owner_id}">
                                <i class="fas fa-history"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tableBody.innerHTML = html;
        
        // Add event listeners to buttons
        document.querySelectorAll('.extend-subscription-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const ownerId = this.dataset.ownerId;
                const ownerName = this.dataset.ownerName;
                const status = this.dataset.status;
                const endDate = this.dataset.endDate;
                
                showExtendSubscriptionModal(ownerId, ownerName, status, endDate);
            });
        });
        
        document.querySelectorAll('.view-history-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const ownerId = this.dataset.ownerId;
                showSubscriptionHistoryModal(ownerId);
            });
        });
    }
    
    // Render pagination
    function renderPagination(currentPage, totalPages) {
        const pagination = document.getElementById('subscription-pagination');
        
        // Clear pagination
        pagination.innerHTML = '';
        
        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>`;
        pagination.appendChild(prevLi);
        
        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, startPage + 4);
        
        for (let i = startPage; i <= endPage; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            pagination.appendChild(pageLi);
        }
        
        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>`;
        pagination.appendChild(nextLi);
        
        // Add event listeners to pagination links
        document.querySelectorAll('#subscription-pagination .page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                if (page >= 1 && page <= totalPages) {
                    loadSubscriptions(page, searchTerm, statusFilter);
                }
            });
        });
    }
    
    // Show extend subscription modal
    function showExtendSubscriptionModal(ownerId, ownerName, status, endDate) {
        document.getElementById('extend-owner-id').value = ownerId;
        document.getElementById('extend-owner-name').value = ownerName;
        document.getElementById('extend-current-status').value = status.toUpperCase();
        document.getElementById('extend-current-end-date').value = endDate;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('extendSubscriptionModal'));
        modal.show();
    }
    
    // Show subscription history modal
    function showSubscriptionHistoryModal(ownerId) {
        // Show loading indicator
        document.getElementById('subscription-history-table').innerHTML = `
            <tr>
                <td colspan="6" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading subscription history...</p>
                </td>
            </tr>
        `;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('subscriptionHistoryModal'));
        modal.show();
        
        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'get_subscription_history');
        formData.append('owner_id', ownerId);
        
        // Send request to server
        fetch('admin_manage_subscriptions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update owner info
                document.getElementById('history-owner-name').textContent = data.owner.full_name;
                document.getElementById('history-owner-email').textContent = data.owner.email;
                
                // Set status with appropriate badge
                let statusClass = 'bg-secondary';
                if (data.owner.subscription_status === 'active') statusClass = 'bg-success';
                if (data.owner.subscription_status === 'expired') statusClass = 'bg-danger';
                if (data.owner.subscription_status === 'pending') statusClass = 'bg-warning';
                
                document.getElementById('history-owner-status').innerHTML = `
                    <span class="badge ${statusClass}">${data.owner.subscription_status.toUpperCase()}</span>
                `;
                
                document.getElementById('history-owner-end-date').textContent = 
                    data.owner.formatted_end_date || 'Not set';
                
                // Render history table
                renderSubscriptionHistory(data.history);
            } else {
                // Show error message
                document.getElementById('subscription-history-table').innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            ${data.message || 'Failed to load subscription history'}
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading subscription history:', error);
            document.getElementById('subscription-history-table').innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        An error occurred while loading subscription history
                    </td>
                </tr>
            `;
        });
    }
    
    // Render subscription history table
    function renderSubscriptionHistory(history) {
        const tableBody = document.getElementById('subscription-history-table');
        
        // Check if there is any history
        if (history.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        No subscription history found
                    </td>
                </tr>
            `;
            return;
        }
        
        // Build table rows
        let html = '';
        history.forEach(item => {
            // Determine status badge class
            let statusClass = 'bg-secondary';
            if (item.payment_status === 'completed') statusClass = 'bg-success';
            if (item.payment_status === 'failed') statusClass = 'bg-danger';
            if (item.payment_status === 'pending') statusClass = 'bg-warning';
            
            // Format period
            const period = `${item.formatted_start_date || 'N/A'} to ${item.formatted_end_date || 'N/A'}`;
            
            html += `
                <tr>
                    <td>${item.formatted_payment_date || 'N/A'}</td>
                    <td>${item.amount} ${item.currency}</td>
                    <td>${item.payment_method || 'N/A'}</td>
                    <td><span class="badge ${statusClass}">${item.payment_status.toUpperCase()}</span></td>
                    <td>${period}</td>
                    <td><small>${item.transaction_id || 'N/A'}</small></td>
                </tr>
            `;
        });
        
        tableBody.innerHTML = html;
    }
    
    // Event listeners
    
    // Search button
    document.getElementById('subscription-search-btn').addEventListener('click', function() {
        searchTerm = document.getElementById('subscription-search').value.trim();
        loadSubscriptions(1, searchTerm, statusFilter);
    });
    
    // Search input (on enter key)
    document.getElementById('subscription-search').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchTerm = this.value.trim();
            loadSubscriptions(1, searchTerm, statusFilter);
        }
    });
    
    // Status filter
    document.getElementById('subscription-filter').addEventListener('change', function() {
        statusFilter = this.value;
        loadSubscriptions(currentPage, searchTerm, statusFilter);
    });
    
    // Refresh button
    document.getElementById('refresh-subscriptions').addEventListener('click', function() {
        loadSubscriptions(currentPage, searchTerm, statusFilter);
    });
    
    // Extend subscription button
    document.getElementById('confirm-extend-btn').addEventListener('click', function() {
        const ownerId = document.getElementById('extend-owner-id').value;
        const months = document.getElementById('extend-months').value;
        const notes = document.getElementById('extend-notes').value;
        
        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'extend_subscription');
        formData.append('owner_id', ownerId);
        formData.append('months', months);
        formData.append('notes', notes);
        
        // Disable button and show loading
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        
        // Send request to server
        fetch('admin_manage_subscriptions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Re-enable button
            this.disabled = false;
            this.innerHTML = 'Extend Subscription';
            
            if (data.success) {
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('extendSubscriptionModal')).hide();
                
                // Show success message
                alert(data.message);
                
                // Reload subscriptions
                loadSubscriptions(currentPage, searchTerm, statusFilter);
            } else {
                // Show error message
                alert('Error: ' + (data.message || 'Failed to extend subscription'));
            }
        })
        .catch(error => {
            // Re-enable button
            this.disabled = false;
            this.innerHTML = 'Extend Subscription';
            
            console.error('Error extending subscription:', error);
            alert('An error occurred while extending the subscription');
        });
    });
});
