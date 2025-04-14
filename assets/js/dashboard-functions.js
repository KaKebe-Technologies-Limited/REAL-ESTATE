// Global functions for dashboard navigation

// Initialize the dashboard when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded event fired in dashboard-functions.js');

    // Add console log to check if elements exist
    console.log('dashboard-content element exists:', !!document.getElementById('dashboard-content'));
    console.log('settings-content element exists:', !!document.getElementById('settings-content'));
    console.log('rental-listing element exists:', !!document.getElementById('rental-listing'));
    console.log('sales-listing element exists:', !!document.getElementById('sales-listing'));
    console.log('managers-listing element exists:', !!document.getElementById('managers-listing'));
    console.log('profile-content element exists:', !!document.getElementById('profile-content'));

    // List all elements in main-content to debug
    const mainContent = document.getElementById('main-content');
    if (mainContent) {
        console.log('Main content found, listing all child elements:');
        const children = mainContent.children;
        for (let i = 0; i < children.length; i++) {
            console.log(`Child ${i}: id=${children[i].id}, tagName=${children[i].tagName}`);
        }
    } else {
        console.error('Main content element not found');
    }

    // Ensure critical sections exist in the DOM
    ensureCriticalSectionsExist();

    // Add event listeners for manager listing and profile links
    // First, remove all existing onclick attributes to prevent conflicts
    document.querySelectorAll('[onclick*="showManagersListing"]').forEach(el => {
        el.removeAttribute('onclick');
    });
    document.querySelectorAll('[onclick*="showProfile"]').forEach(el => {
        el.removeAttribute('onclick');
    });

    // Add new event listeners for manager listing links
    const managerLinks = document.querySelectorAll('[data-form="managers-listing"], a.view-details');
    if (managerLinks && managerLinks.length > 0) {
        console.log('Found manager listing links:', managerLinks.length);
        managerLinks.forEach(link => {
            // Check if this is a manager-related link
            const isManagerLink = link.closest('.summary-card') &&
                                 link.closest('.summary-card').querySelector('.summary-title') &&
                                 link.closest('.summary-card').querySelector('.summary-title').textContent.includes('Manager');

            if (link.getAttribute('data-form') === 'managers-listing' || isManagerLink) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Manager listing link clicked');
                    showManagersListing();
                });
            }
        });
    } else {
        console.warn('No manager listing links found, adding event listeners to all potential manager links');
        // Try to find links by text content
        document.querySelectorAll('a').forEach(link => {
            if (link.textContent.includes('Manager') || link.textContent.includes('manager')) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Potential manager link clicked');
                    showManagersListing();
                });
            }
        });
    }

    // Add new event listeners for profile links
    const profileLinks = document.querySelectorAll('.dropdown-item');
    if (profileLinks && profileLinks.length > 0) {
        console.log('Found dropdown items:', profileLinks.length);
        profileLinks.forEach(link => {
            // Check if this is a profile-related link
            const hasProfileIcon = link.querySelector('i.fa-user') !== null;
            const hasProfileText = link.textContent.includes('Profile');

            if (hasProfileIcon || hasProfileText) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Profile link clicked');
                    showProfile();
                });
            }
        });
    } else {
        console.warn('No profile links found, adding manual event listener');
        // Add a manual event listener to the document
        document.addEventListener('click', function(e) {
            const target = e.target;
            if (target.tagName === 'A' || target.closest('a')) {
                const link = target.tagName === 'A' ? target : target.closest('a');
                if (link.textContent.includes('Profile') ||
                    (link.querySelector('i') && link.querySelector('i').classList.contains('fa-user'))) {
                    e.preventDefault();
                    console.log('Profile link clicked via document listener');
                    showProfile();
                }
            }
        });
    }

    // Initialize profile form handlers if profile content exists
    if (document.getElementById('profile-content')) {
        initializeProfileFormHandlers();
    }

    // Show the dashboard content by default
    setTimeout(function() {
        showDashboard();
    }, 100); // Small delay to ensure DOM is fully processed
});

// Function to ensure critical sections exist in the DOM
function ensureCriticalSectionsExist() {
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;

    // Check if managers-listing exists
    if (!document.getElementById('managers-listing')) {
        console.log('managers-listing not found in DOM, checking HTML');
        const htmlContent = document.documentElement.innerHTML;
        if (htmlContent.includes('id="managers-listing"')) {
            console.log('managers-listing found in HTML, attempting to extract it');
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = htmlContent;
            const managersSection = tempDiv.querySelector('#managers-listing');
            if (managersSection) {
                console.log('Successfully extracted managers-listing from HTML');
                mainContent.appendChild(managersSection);
                managersSection.style.display = 'none';
            }
        }
    }

    // Check if profile-content exists
    if (!document.getElementById('profile-content')) {
        console.log('profile-content not found in DOM, checking HTML');
        const htmlContent = document.documentElement.innerHTML;
        if (htmlContent.includes('id="profile-content"')) {
            console.log('profile-content found in HTML, attempting to extract it');
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = htmlContent;
            const profileSection = tempDiv.querySelector('#profile-content');
            if (profileSection) {
                console.log('Successfully extracted profile-content from HTML');
                mainContent.appendChild(profileSection);
                profileSection.style.display = 'none';
            }
        }
    }
}

// Toggle submenu visibility
function toggleSubmenu(el) {
    const parent = el.parentElement;
    parent.classList.toggle('open');
}

// Toggle property submenu visibility
function togglePropertySubmenu(element) {
    // Prevent default action
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

// Hide all content forms
function hideAllForms() {
    console.log('hideAllForms called from dashboard-functions.js');

    try {
        // First, hide all divs that are direct children of main-content
        const mainContent = document.getElementById('main-content');
        if (mainContent) {
            const contentDivs = mainContent.children;
            for (let i = 0; i < contentDivs.length; i++) {
                const div = contentDivs[i];
                if (div && div.tagName === 'DIV') {
                    console.log(`Hiding div with id: ${div.id}`);
                    div.style.display = 'none';
                }
            }
        } else {
            console.warn('Main content element not found');
        }

        // Also try to hide specific forms by ID as a fallback
        const forms = [
            'dashboard-content',
            'settings-content',
            'create-manager-form',
            'create-owner-form',
            'add-sale-form',
            'add-rental-form',
            'rental-listing',
            'sales-listing',
            'owners-listing',
            'managers-listing',
            'profile-content'
        ];

        forms.forEach(form => {
            try {
                const element = document.getElementById(form);
                if (element) {
                    console.log(`Hiding element: ${form}`);
                    element.style.display = 'none';
                } else {
                    console.log(`Element not found for hiding: ${form}`);
                }
            } catch (err) {
                console.warn(`Error hiding element ${form}:`, err);
            }
        });
    } catch (error) {
        console.error('Error in hideAllForms function:', error);
    }
}

// Update active navigation item
function updateActiveNav(formId) {
    try {
        const submenuLinks = document.querySelectorAll('.submenu-link');
        if (submenuLinks && submenuLinks.length > 0) {
            submenuLinks.forEach(link => {
                if (link) {
                    link.classList.remove('active');
                }
            });
        }

        const activeLink = document.querySelector(`[data-form="${formId}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    } catch (error) {
        console.error('Error in updateActiveNav function:', error);
    }
}

// Show dashboard content
function showDashboard() {
    console.log('showDashboard called from dashboard-functions.js');
    try {
        hideAllForms();
        const dashboardContent = document.getElementById('dashboard-content');
        if (dashboardContent) {
            console.log('Dashboard content found, setting display to block');
            dashboardContent.style.display = 'block';
            updateActiveNav('dashboard-content');
        } else {
            console.error('Dashboard content element not found');
            // Try to find it by class or other means
            const mainContent = document.getElementById('main-content');
            if (mainContent) {
                const contentDivs = mainContent.children;
                if (contentDivs.length > 0) {
                    for (let i = 0; i < contentDivs.length; i++) {
                        if (contentDivs[i].tagName === 'DIV') {
                            console.log('Found div in main content, showing it instead:', contentDivs[i].id);
                            contentDivs[i].style.display = 'block';
                            break;
                        }
                    }
                }
            }
        }
    } catch (error) {
        console.error('Error in showDashboard function:', error);
    }
}

// Show settings content
function showSettings() {
    hideAllForms();
    const settingsContent = document.getElementById('settings-content');
    if (settingsContent) {
        settingsContent.style.display = 'block';
        // Update active state
        document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
        const settingsLink = document.querySelector('.nav-link i.fa-cog');
        if (settingsLink) {
            settingsLink.parentElement.parentElement.classList.add('active');
        }
    }
}

// Show rental listing
function showRentalListing() {
    hideAllForms();
    const rentalListing = document.getElementById('rental-listing');
    if (rentalListing) {
        rentalListing.style.display = 'block';
        updateActiveNav('rental-listing');
    }
}

// Show sales listing
function showSalesListing() {
    hideAllForms();
    const salesListing = document.getElementById('sales-listing');
    if (salesListing) {
        salesListing.style.display = 'block';
        updateActiveNav('sales-listing');
    }
}

// Show owners listing
function showOwnersListing() {
    hideAllForms();
    const ownersListing = document.getElementById('owners-listing');
    if (ownersListing) {
        ownersListing.style.display = 'block';
        updateActiveNav('owners-listing');
    }
}

// Show managers listing
function showManagersListing() {
    console.log('showManagersListing called');
    hideAllForms();

    // Create the managers listing content directly
    const mainContent = document.getElementById('main-content');
    if (!mainContent) {
        console.error('Main content element not found');
        return;
    }

    // Check if managers-listing already exists
    let managersListing = document.getElementById('managers-listing');

    // If it doesn't exist, create it
    if (!managersListing) {
        console.log('Creating managers-listing element');
        managersListing = document.createElement('div');
        managersListing.id = 'managers-listing';
        managersListing.innerHTML = `
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="card-title">Property Managers</h4>
                                    <div class="d-flex gap-2">
                                        <div class="search-box">
                                            <input type="text" class="form-control" placeholder="Search managers...">
                                        </div>
                                        <button class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Manager Name</th>
                                                <th>Number of Properties Managed</th>
                                                <th>Contact</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Michael Brown</td>
                                                <td>12</td>
                                                <td>+256 751 123 456</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Sarah Johnson</td>
                                                <td>8</td>
                                                <td>+256 772 987 654</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="showing-entries">
                                        Showing 1 to 10 of 15 entries
                                    </div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination mb-0">
                                            <li class="page-item disabled">
                                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                                            </li>
                                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                                            <li class="page-item">
                                                <a class="page-link" href="#">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        mainContent.appendChild(managersListing);
    }

    // Show the managers listing
    managersListing.style.display = 'block';
    updateActiveNav('managers-listing');
}

// Helper function to create a fallback managers listing
function createFallbackManagersListing(mainContent) {
    if (mainContent) {
        console.log('Creating fallback managers-listing element');
        const newManagersListing = document.createElement('div');
        newManagersListing.id = 'managers-listing';
        newManagersListing.innerHTML = `
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Property Managers</h4>
                                <p>Unable to load the managers listing content. Please refresh the page and try again.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        mainContent.appendChild(newManagersListing);
        newManagersListing.style.display = 'block';
        console.log('Created and displayed fallback managers-listing element');
    }
}

// Show profile content
function showProfile() {
    console.log('showProfile called');
    hideAllForms();

    // Create the profile content directly
    const mainContent = document.getElementById('main-content');
    if (!mainContent) {
        console.error('Main content element not found');
        return;
    }

    // Check if profile-content already exists
    let profileContent = document.getElementById('profile-content');

    // If it doesn't exist, create it
    if (!profileContent) {
        console.log('Creating profile-content element');
        profileContent = document.createElement('div');
        profileContent.id = 'profile-content';
        profileContent.innerHTML = `
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="card-title">User Profile</h4>
                                    <button class="btn btn-primary" id="edit-profile-btn">Edit Profile</button>
                                </div>
                                <div id="profile-view">
                                    <div class="row mb-4">
                                        <div class="col-md-4 text-center">
                                            <img src="assets/images/profile.jpg" alt="Profile Picture" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px;">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <p><strong>First Name:</strong> John</p>
                                                    <p><strong>Last Name:</strong> Doe</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Email:</strong> john.doe@example.com</p>
                                                    <p><strong>Phone:</strong> +256 123 456 789</p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Address:</strong> 123 Main St, Kampala</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Member Since:</strong> January 2023</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <h5 class="mb-3">About Me</h5>
                                            <p>Experienced property owner with multiple properties in the Kampala area. Focused on providing quality housing solutions for both short-term and long-term tenants.</p>
                                        </div>
                                    </div>
                                </div>
                                <form id="profile-form" class="form-sample" action="update_owner_profile.php" method="post" enctype="multipart/form-data" style="display: none;">
                                    <input type="hidden" name="owner_id" value="1">
                                    <div class="row mb-4">
                                        <div class="col-md-4 text-center">
                                            <div class="profile-picture-wrapper">
                                                <img id="profile-picture-preview" src="assets/images/profile.jpg" alt="Profile Picture" class="rounded-circle img-thumbnail mb-3" style="width: 150px; height: 150px;">
                                                <input type="file" id="profile-picture-input" name="profile_picture" class="form-control" accept="image/*" style="display: none;">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" id="change-picture-btn">Change Picture</button>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">First Name</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="first_name" class="form-control" value="John">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Last Name</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="last_name" class="form-control" value="Doe">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-success me-3">Save Changes</button>
                                        <button type="button" class="btn btn-light" id="cancel-edit-btn">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        mainContent.appendChild(profileContent);
    }

    // Show the profile content
    profileContent.style.display = 'block';
    updateActiveNav('profile-content');

    // Initialize profile form event handlers
    initializeProfileFormHandlers();
}

// Helper function to create a fallback profile content
function createFallbackProfileContent(mainContent) {
    if (mainContent) {
        console.log('Creating fallback profile-content element');
        const newProfileContent = document.createElement('div');
        newProfileContent.id = 'profile-content';
        newProfileContent.innerHTML = `
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">User Profile</h4>
                                <p>Unable to load the profile content. Please refresh the page and try again.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        mainContent.appendChild(newProfileContent);
        newProfileContent.style.display = 'block';
        console.log('Created and displayed fallback profile-content element');
    }
}

// Initialize profile form event handlers
function initializeProfileFormHandlers() {
    console.log('Initializing profile form handlers');

    // Remove any existing event listeners to prevent duplicates
    const oldEditBtn = document.getElementById('edit-profile-btn');
    if (oldEditBtn) {
        const newEditBtn = oldEditBtn.cloneNode(true);
        if (oldEditBtn.parentNode) {
            oldEditBtn.parentNode.replaceChild(newEditBtn, oldEditBtn);
        }
    }

    const oldCancelBtn = document.getElementById('cancel-edit-btn');
    if (oldCancelBtn) {
        const newCancelBtn = oldCancelBtn.cloneNode(true);
        if (oldCancelBtn.parentNode) {
            oldCancelBtn.parentNode.replaceChild(newCancelBtn, oldCancelBtn);
        }
    }

    // Get fresh references to the elements
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const cancelProfileBtn = document.getElementById('cancel-edit-btn');
    const profileView = document.getElementById('profile-view');
    const profileForm = document.getElementById('profile-form');

    if (editProfileBtn && cancelProfileBtn && profileView && profileForm) {
        console.log('Setting up profile form event handlers');

        editProfileBtn.addEventListener('click', function() {
            console.log('Edit profile button clicked');
            profileView.style.display = 'none';
            profileForm.style.display = 'block';
        });

        cancelProfileBtn.addEventListener('click', function() {
            console.log('Cancel button clicked');
            profileForm.style.display = 'none';
            profileView.style.display = 'block';
        });

        // Profile picture change
        const profilePictureInput = document.getElementById('profile-picture-input');
        const changePictureBtn = document.getElementById('change-picture-btn');
        const profilePicturePreview = document.getElementById('profile-picture-preview');

        if (profilePictureInput && changePictureBtn && profilePicturePreview) {
            // Remove existing event listeners
            const newChangePictureBtn = changePictureBtn.cloneNode(true);
            if (changePictureBtn.parentNode) {
                changePictureBtn.parentNode.replaceChild(newChangePictureBtn, changePictureBtn);
            }

            const newProfilePictureInput = profilePictureInput.cloneNode(true);
            if (profilePictureInput.parentNode) {
                profilePictureInput.parentNode.replaceChild(newProfilePictureInput, profilePictureInput);
            }

            // Get fresh references
            const freshChangePictureBtn = document.getElementById('change-picture-btn');
            const freshProfilePictureInput = document.getElementById('profile-picture-input');

            if (freshChangePictureBtn && freshProfilePictureInput) {
                freshChangePictureBtn.addEventListener('click', function() {
                    console.log('Change picture button clicked');
                    freshProfilePictureInput.click();
                });

                freshProfilePictureInput.addEventListener('change', function() {
                    console.log('Profile picture input changed');
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            profilePicturePreview.src = e.target.result;
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
        }
    } else {
        console.error('One or more profile form elements not found:');
        console.log('editProfileBtn:', !!editProfileBtn);
        console.log('cancelProfileBtn:', !!cancelProfileBtn);
        console.log('profileView:', !!profileView);
        console.log('profileForm:', !!profileForm);
    }
}

// Show create manager form
function showCreateManager() {
    hideAllForms();
    const createManagerForm = document.getElementById('create-manager-form');
    if (createManagerForm) {
        createManagerForm.style.display = 'block';
        updateActiveNav('create-manager');
    }
}

// Show create owner form
function showCreateOwner() {
    hideAllForms();
    const createOwnerForm = document.getElementById('create-owner-form');
    if (createOwnerForm) {
        createOwnerForm.style.display = 'block';
        updateActiveNav('create-owner');
    }
}

// Show add sale form
function showAddSale() {
    hideAllForms();
    const addSaleForm = document.getElementById('add-sale-form');
    if (addSaleForm) {
        addSaleForm.style.display = 'block';
        updateActiveNav('add-sale');
    }
}

// Show add rental form
function showAddRental() {
    hideAllForms();
    const addRentalForm = document.getElementById('add-rental-form');
    if (addRentalForm) {
        addRentalForm.style.display = 'block';
        updateActiveNav('add-rental');
    }
}

// Make functions available globally
window.showManagersListing = showManagersListing;
window.showProfile = showProfile;
window.showDashboard = showDashboard;
window.showSettings = showSettings;
window.showRentalListing = showRentalListing;
window.showSalesListing = showSalesListing;
window.showCreateManager = showCreateManager;
window.showAddSale = showAddSale;
window.showAddRental = showAddRental;
