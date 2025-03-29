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
const propertyChart = new Chart(document.getElementById('propertyChart'), {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Properties Listed',
            data: [65, 59, 80, 81, 56, 55],
            borderColor: '#007bff',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

const distributionChart = new Chart(document.getElementById('distributionChart'), {
    type: 'doughnut',
    data: {
        labels: ['For Sale', 'For Rent', 'Sold', 'Rented'],
        datasets: [{
            data: [45, 25, 20, 10],
            backgroundColor: ['#007bff', '#28a745', '#dc3545', '#ffc107']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
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
    const forms = ['dashboard-content', 'settings-content', 'create-manager-form', 'create-owner-form', 'add-sale-form', 'add-rental-form', 'rental-listing', 'sales-listing', 'owners-listing', 'managers-listing', 'profile-content'];
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
    document.querySelector(`[data-form="${formId}"]`).classList.add('active');
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
    document.getElementById('profile-content').style.display = 'block';
    updateActiveNav('profile-content');
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