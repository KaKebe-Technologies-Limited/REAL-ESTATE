// Custom Logo Loader for Real Estate Website

// Wait for DOM content to be loaded before executing
document.addEventListener('DOMContentLoaded', function() {
    // Create loader elements
    const loaderContainer = document.createElement('div');
    loaderContainer.className = 'loader-container';

    // Create loader HTML directly for better performance
    loaderContainer.innerHTML = `
        <div class="logo-loader">
            <img src="assets/images/logo1.png" alt="ALLEA Properties Logo">
        </div>
        <div class="loading-text">LOADING</div>
    `;

    // Add loader to the body
    document.body.appendChild(loaderContainer);

    // Hide loader when page is fully loaded
    window.addEventListener('load', function() {
        setTimeout(function() {
            if (loaderContainer && document.body.contains(loaderContainer)) {
                loaderContainer.classList.add('hidden');

                // Remove loader from DOM after transition
                setTimeout(function() {
                    if (loaderContainer && document.body.contains(loaderContainer)) {
                        document.body.removeChild(loaderContainer);
                    }
                }, 500);
            }
        }, 600); // Reduced delay for faster page display
    });

    // Fallback: Hide loader after 3 seconds in case of slow loading resources
    setTimeout(function() {
        if (loaderContainer && document.body.contains(loaderContainer)) {
            loaderContainer.classList.add('hidden');

            setTimeout(function() {
                if (loaderContainer && document.body.contains(loaderContainer)) {
                    document.body.removeChild(loaderContainer);
                }
            }, 500);
        }
    }, 3000); // Reduced timeout for better user experience
});

// Also create a fallback for pages where the script might be loaded after DOMContentLoaded
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    // Dispatch a custom DOMContentLoaded event to trigger our loader
    document.dispatchEvent(new Event('DOMContentLoaded'));
}
