document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');

    // Check if elements exist
    if (!sidebarToggle || !sidebar || !mainContent) {
        console.error('Required elements not found for sidebar toggle');
        return;
    }

    // Toggle function
    function toggleSidebar(e) {
        if (e) e.preventDefault();
        sidebar.classList.toggle('collapsed');

        // Only adjust main content on desktop
        if (window.innerWidth > 768) {
            mainContent.classList.toggle('expanded');
        }
    }

    // Add click event listener
    sidebarToggle.addEventListener('click', toggleSidebar);

    // Handle responsive behavior
    window.addEventListener('resize', () => {
        if (window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
            mainContent.classList.remove('expanded');
        }
    });
});

// Add submenu toggle function
function toggleSubmenu(el) {
    const parent = el.parentElement;
    parent.classList.toggle('open');
}

// Initialize Charts
let incomeChart;
let distributionChart;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Income Chart
    const incomeChartCtx = document.getElementById('incomeChart');
    if (incomeChartCtx) {
        incomeChart = new Chart(incomeChartCtx, {
            type: 'bar',
            data: {
                labels: ['Loading...'],
                datasets: [
                    {
                        label: 'Rental Income',
                        backgroundColor: '#007bff',
                        borderColor: '#0056b3',
                        borderWidth: 1,
                        data: [0]
                    },
                    {
                        label: 'Sales Income',
                        backgroundColor: '#dc3545',
                        borderColor: '#a71d2a',
                        borderWidth: 1,
                        data: [0]
                    },
                    {
                        label: 'Subscription Income',
                        backgroundColor: '#28a745',
                        borderColor: '#1e7e34',
                        borderWidth: 1,
                        data: [0]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'UGX ' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'UGX ' + context.parsed.y.toLocaleString();
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // Initialize Distribution Chart
    const distributionChartCtx = document.getElementById('distributionChart');
    if (distributionChartCtx) {
        distributionChart = new Chart(distributionChartCtx, {
            type: 'doughnut',
            data: {
                labels: ['Rental Income', 'Sales Income', 'Subscription Income'],
                datasets: [{
                    data: [0, 0, 0],
                    backgroundColor: ['#007bff', '#dc3545', '#28a745']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += 'UGX ' + context.parsed.toLocaleString();
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // Fetch income data
    fetchAdminIncomeData();

    // Add event listener for period selector
    const incomePeriodSelector = document.getElementById('income-period-selector');
    if (incomePeriodSelector) {
        incomePeriodSelector.addEventListener('change', function() {
            fetchAdminIncomeData(this.value);
        });
    }
});

function showSettings() {
    hideAllForms();
    document.getElementById('settings-content').style.display = 'block';
    // Update active state
    document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
    document.querySelector('.nav-link i.fa-cog').parentElement.parentElement.classList.add('active');
}

// Add to existing sidebar toggle handler
document.querySelector('.nav-link i.fa-tachometer-alt').parentElement.addEventListener('click', function() {
    document.getElementById('settings-content').style.display = 'none';
    document.getElementById('dashboard-content').style.display = 'block';
    // Update active state
    document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
    this.parentElement.classList.add('active');
});

let tempTheme = '';

// Theme switching functionality
function setTheme(theme, isPermanent = false) {
    document.body.className = ''; // Remove existing theme classes
    document.body.classList.add(`theme-${theme}`);

    if (isPermanent) {
        localStorage.setItem('preferred-theme', theme);
    } else {
        tempTheme = theme;
    }

    // Update active state of theme options
    document.querySelectorAll('.theme-option').forEach(option => {
        option.classList.remove('active');
    });
    document.getElementById(`${theme}-theme`).classList.add('active');
}



// Save settings including theme
function saveSettings(event) {
    event.preventDefault();

    // Make the temporary theme permanent if one was selected
    if (tempTheme) {
        setTheme(tempTheme, true);
    }

    // Show success message
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        <strong>Success!</strong> Settings have been saved. Theme: ${tempTheme || localStorage.getItem('preferred-theme') || 'light'}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    // Insert alert at the top of settings form
    const form = document.querySelector('.settings-form');
    form.insertBefore(alertDiv, form.firstChild);

    // Auto dismiss after 3 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Restore saved theme when leaving settings without saving
document.querySelector('.nav-link i.fa-tachometer-alt').parentElement.addEventListener('click', function() {
    const savedTheme = localStorage.getItem('preferred-theme') || 'light';
    setTheme(savedTheme, true);
    tempTheme = ''; // Reset temporary theme
    // ...rest of existing dashboard click handler code...
});

// Set initial theme from localStorage
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('preferred-theme') || 'light';
    setTheme(savedTheme, true);

    // Add click handlers for form links
    document.querySelector('[data-form="create-manager"]').addEventListener('click', showCreateManager);
    document.querySelector('[data-form="create-owner"]').addEventListener('click', showCreateOwner);
    document.querySelector('[data-form="add-sale"]').addEventListener('click', showAddSale);
    document.querySelector('[data-form="add-rental"]').addEventListener('click', showAddRental);
});

function togglePropertySubmenu(el) {
    const parent = el.parentElement;
    parent.classList.toggle('open');
    // Stop event propagation
    event.stopPropagation();
}

function hideAllForms() {
    const forms = ['dashboard-content', 'settings-content', 'create-manager-form', 'create-owner-form', 'add-sale-form', 'add-rental-form', 'rental-listing', 'sales-listing', 'owners-listing', 'managers-listing', 'profile-content', 'owner-subscriptions'];
    forms.forEach(form => {
        document.getElementById(form).style.display = 'none';
    });
}

// Form display handlers
function showCreateManager() {
    hideAllForms();
    document.getElementById('create-manager-form').style.display = 'block';
    updateActiveNav('create-manager');
}

function showCreateOwner() {
    hideAllForms();
    document.getElementById('create-owner-form').style.display = 'block';
    updateActiveNav('create-owner');
}

function showAddSale() {
    hideAllForms();
    document.getElementById('add-sale-form').style.display = 'block';
    updateActiveNav('add-sale');
}

function showAddRental() {
    hideAllForms();
    document.getElementById('add-rental-form').style.display = 'block';
    updateActiveNav('add-rental');
}

function updateActiveNav(formId) {
    document.querySelectorAll('.submenu-link').forEach(link => {
        link.classList.remove('active');
    });

    // Check if the element exists before trying to access its classList
    const navElement = document.querySelector(`[data-form="${formId}"]`);
    if (navElement) {
        navElement.classList.add('active');
    }
}

// Remove all backdrop-related event listeners and code

function togglePropertySubmenu(element) {
    event.preventDefault();

    // Toggle submenu visibility
    const submenu = element.nextElementSibling;
    submenu.classList.toggle('show');

    // Toggle arrow rotation
    const arrow = element.querySelector('.property-submenu-arrow');
    arrow.classList.toggle('rotate');

    // Close other property submenus if open
    const allPropertySubmenus = document.querySelectorAll('.property-submenu');
    const allPropertyArrows = document.querySelectorAll('.property-submenu-arrow');

    allPropertySubmenus.forEach(menu => {
        if (menu !== submenu && menu.classList.contains('show')) {
            menu.classList.remove('show');
        }
    });

    allPropertyArrows.forEach(arr => {
        if (arr !== arrow && arr.classList.contains('rotate')) {
            arr.classList.remove('rotate');
        }
    });
}

// Close submenu when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.has-submenu')) {
        const propertySubmenus = document.querySelectorAll('.property-submenu');
        const propertyArrows = document.querySelectorAll('.property-submenu-arrow');

        propertySubmenus.forEach(menu => menu.classList.remove('show'));
        propertyArrows.forEach(arrow => arrow.classList.remove('rotate'));
    }
});

function showRentalListing() {
    // Hide all other content
    hideAllForms();

    // Show rental listing
    document.getElementById('rental-listing').style.display = 'block';
    updateActiveNav('rental-listing');
}

// Update your existing click handler
document.querySelector('[data-form="rental-listing"]').addEventListener('click', function(e) {
    e.preventDefault();
    showRentalListing();
});

function showSalesListing() {
    // Hide all other content
    hideAllForms();

    // Show sales listing
    document.getElementById('sales-listing').style.display = 'block';
    updateActiveNav('sales-listing');
}

// Add event listener for the sales listing menu item
document.querySelector('[data-form="sales-listing"]').addEventListener('click', function (e) {
    e.preventDefault();
    showSalesListing();
});

function showOwnersListing() {
    // Hide all other content
    hideAllForms();

    // Show owners listing
    document.getElementById('owners-listing').style.display = 'block';
    updateActiveNav('owners-listing');
}

// Add event listener for the owners listing menu item
document.querySelector('[data-form="owners-listing"]').addEventListener('click', function (e) {
    e.preventDefault();
    showOwnersListing();
});

function showManagersListing() {
    // Hide all other content
    hideAllForms();

    // Show managers listing
    document.getElementById('managers-listing').style.display = 'block';
    updateActiveNav('managers-listing');
}

// Add event listener for the managers listing menu item
document.querySelector('[data-form="managers-listing"]').addEventListener('click', function (e) {
    e.preventDefault();
    showManagersListing();
});

function showOwnerSubscriptions() {
    // Hide all other content
    hideAllForms();

    // Show managers listing
    document.getElementById('owner-subscriptions').style.display = 'block';
    updateActiveNav('owner-subscriptions');
}

// Add event listener for the managers listing menu item
document.querySelector('[data-form="owner-subscriptions"]').addEventListener('click', function (e) {
    e.preventDefault();
    showOwnerSubscriptions();
});

function showDashboard() {
    // Hide all other content
    hideAllForms();

    // Show managers listing
    document.getElementById('dashboard-content').style.display = 'block';
    updateActiveNav('dashboard-content');
}

// Add event listener for the managers listing menu item
document.querySelector('[data-form="dashboard-content"]').addEventListener('click', function (e) {
    e.preventDefault();
    showDashboard();
});

function showProfile() {
    // Hide all other content
    hideAllForms();

    // Show profile content
    const profileContent = document.getElementById('profile-content');
    if (profileContent) {
        profileContent.style.display = 'block';
        updateActiveNav('profile-content');
    } else {
        console.warn('Profile content element not found');
    }
}

// Add event listener for the profile link
document.querySelector('.profile-link').addEventListener('click', function (e) {
    e.preventDefault();
    showProfile();
});

// Handle edit profile button
document.getElementById('edit-profile-btn').addEventListener('click', function () {
    document.getElementById('profile-view').style.display = 'none';
    document.getElementById('profile-form').style.display = 'block';
});

// Handle cancel edit button
document.getElementById('cancel-edit-btn').addEventListener('click', function () {
    document.getElementById('profile-view').style.display = 'block';
    document.getElementById('profile-form').style.display = 'none';
});

// Handle profile picture change
document.getElementById('change-picture-btn').addEventListener('click', function () {
    document.getElementById('profile-picture-input').click();
});

document.getElementById('profile-picture-input').addEventListener('change', function (event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('profile-picture-preview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

document.querySelectorAll('.language-option').forEach(option => {
    option.addEventListener('click', function (e) {
        e.preventDefault();

        // Get the selected language and flag
        const selectedLanguage = this.getAttribute('data-lang');
        const selectedFlag = this.getAttribute('data-flag');

        // Update the dropdown display
        document.getElementById('selected-language-name').textContent = selectedLanguage;
        document.getElementById('selected-language-flag').src = selectedFlag;
    });
});

// Function to fetch income data for admin dashboard
function fetchAdminIncomeData(period = '6months') {
    fetch(`get_admin_income.php?period=${period}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update income charts
                updateIncomeChart(data, incomeChart);

                // Update distribution chart
                updateDistributionChart(data, distributionChart);

                // Update top owners table
                updateTopOwnersTable(data.income_by_owner);

                // Update top managers table
                updateTopManagersTable(data.income_by_manager);
            } else {
                console.error('Error fetching income data:', data.message);
                alert('Failed to load income data. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load income data. Please try again.');
        });
}

// Function to update income chart
function updateIncomeChart(data, chart) {
    if (chart && data.monthly_data) {
        chart.data.labels = data.monthly_data.months;
        chart.data.datasets[0].data = data.monthly_data.rental;
        chart.data.datasets[1].data = data.monthly_data.sales;
        chart.data.datasets[2].data = data.monthly_data.subscription;
        chart.update();
    }
}

// Function to update distribution chart
function updateDistributionChart(data, chart) {
    if (chart) {
        chart.data.datasets[0].data = [
            data.rental_income,
            data.sales_income,
            data.subscription_income
        ];
        chart.update();
    }
}

// Function to update top owners table
function updateTopOwnersTable(owners) {
    const tableBody = document.getElementById('top-owners-table').querySelector('tbody');
    if (!tableBody) return;

    // Clear existing rows
    tableBody.innerHTML = '';

    if (owners && owners.length > 0) {
        owners.forEach(owner => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${owner.owner_name}</td>
                <td>UGX ${owner.rental_income.toLocaleString()}</td>
                <td>UGX ${owner.sales_income.toLocaleString()}</td>
                <td>UGX ${owner.total_income.toLocaleString()}</td>
            `;
            tableBody.appendChild(row);
        });
    } else {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="4" class="text-center">No data available</td>';
        tableBody.appendChild(row);
    }
}

// Function to update top managers table
function updateTopManagersTable(managers) {
    const tableBody = document.getElementById('top-managers-table').querySelector('tbody');
    if (!tableBody) return;

    // Clear existing rows
    tableBody.innerHTML = '';

    if (managers && managers.length > 0) {
        managers.forEach(manager => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${manager.manager_name}</td>
                <td>UGX ${manager.rental_income.toLocaleString()}</td>
                <td>UGX ${manager.sales_income.toLocaleString()}</td>
                <td>UGX ${manager.total_income.toLocaleString()}</td>
            `;
            tableBody.appendChild(row);
        });
    } else {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="4" class="text-center">No data available</td>';
        tableBody.appendChild(row);
    }
}
