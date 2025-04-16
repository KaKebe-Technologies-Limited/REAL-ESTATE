// Custom Logo Loader for Real Estate Website

// Global variable to track if loader has been created
let loaderCreated = false;
let loaderContainer = null;

// Function to create and show loader
function createAndShowLoader() {
    // Only create loader if it hasn't been created yet
    if (loaderCreated) return;
    loaderCreated = true;

    // Create loader elements
    loaderContainer = document.createElement('div');
    loaderContainer.id = 'custom-page-loader'; // Add unique ID
    loaderContainer.className = 'loader-container';

    // Create loader HTML directly for better performance
    loaderContainer.innerHTML = `
        <div class="logo-loader">
            <img src="assets/images/logo1.png" alt="ALLEA Properties Logo">
        </div>
        <div class="loading-text">LOADING</div>
    `;

    // Add loader to the body if it doesn't already exist
    if (!document.getElementById('custom-page-loader')) {
        document.body.appendChild(loaderContainer);
    }

    // Set up removal of loader
    setupLoaderRemoval();
}

// Function to set up loader removal
function setupLoaderRemoval() {
    // Hide loader when page is fully loaded
    const removeLoader = function() {
        // Get the loader by ID to ensure we're working with the correct element
        const loader = document.getElementById('custom-page-loader');
        if (loader) {
            loader.classList.add('hidden');

            // Remove loader from DOM after transition
            setTimeout(function() {
                if (loader && document.body.contains(loader)) {
                    try {
                        document.body.removeChild(loader);
                    } catch (e) {
                        console.log('Loader already removed');
                    }
                }
            }, 500);
        }
    };

    // Set up event listener for page load
    if (document.readyState === 'complete') {
        setTimeout(removeLoader, 600);
    } else {
        window.addEventListener('load', function() {
            setTimeout(removeLoader, 600);
        });
    }

    // Fallback: Hide loader after 3 seconds in case of slow loading resources
    setTimeout(removeLoader, 3000);
}

// Wait for DOM content to be loaded before executing
document.addEventListener('DOMContentLoaded', createAndShowLoader);

// Fallback for pages where the script might be loaded after DOMContentLoaded
if (document.readyState !== 'loading') {
    createAndShowLoader();
}
