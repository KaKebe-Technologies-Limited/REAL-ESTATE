// Remove old toggle handlers
// ...existing code...

// Remove this duplicate code
// document.getElementById('sidebar-toggle').addEventListener('click', function() {
//     document.getElementById('sidebar').classList.toggle('collapsed');
//     document.getElementById('main-content').classList.toggle('expanded');
// });

// Replace with this single toggle handler
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const isMobile = () => window.innerWidth <= 768;

    sidebarToggle.addEventListener('click', function(e) {
        e.preventDefault();
        sidebar.classList.toggle('collapsed');
        
        if (!isMobile()) {
            mainContent.classList.toggle('expanded');
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
    document.getElementById('dashboard-content').style.display = 'none';
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
    const forms = ['dashboard-content', 'settings-content', 'create-manager-form', 'create-owner-form', 'add-sale-form', 'add-rental-form'];
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

// Update sidebar toggle (replace existing toggle code)
const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('main-content');

sidebarToggle.addEventListener('click', function(e) {
    e.preventDefault();
    sidebar.classList.toggle('collapsed');
    
    if (window.innerWidth > 768) {
        mainContent.classList.toggle('expanded');
    }
});

// Remove all backdrop-related event listeners and code
// ...rest of existing code...