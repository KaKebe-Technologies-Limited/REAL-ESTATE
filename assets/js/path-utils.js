/**
 * Utility functions for handling paths consistently across the application
 */

/**
 * Get the base URL for API requests, handling both localhost and live site
 * @returns {string} The base URL to use for API requests
 */
function getBaseUrl() {
    // Check if we're on localhost
    const isLocalhost = window.location.hostname === 'localhost' || 
                        window.location.hostname === '127.0.0.1' ||
                        window.location.hostname.includes('192.168.');
    
    // Return appropriate base URL
    return isLocalhost ? '' : '/REAL-ESTATE/';
}

/**
 * Get the full URL for an API endpoint
 * @param {string} endpoint - The API endpoint (e.g., 'get_user_profile.php')
 * @returns {string} The full URL to the API endpoint
 */
function getApiUrl(endpoint) {
    return getBaseUrl() + endpoint;
}

/**
 * Get the full URL for an image path
 * @param {string} imagePath - The image path (e.g., 'uploads/profile_picture/image.jpg')
 * @returns {string} The full URL to the image
 */
function getImageUrl(imagePath) {
    // If the image path already starts with http, return it as is
    if (imagePath && (imagePath.startsWith('http://') || imagePath.startsWith('https://'))) {
        return imagePath;
    }
    
    // Otherwise, prepend the base URL
    return getBaseUrl() + (imagePath || 'uploads/profile_picture/default-profile.jpg');
}
