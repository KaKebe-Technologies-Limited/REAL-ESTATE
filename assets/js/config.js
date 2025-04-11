/**
 * Global configuration for the application
 */

// Configuration object
const AppConfig = {
    // Base URL for API requests
    baseUrl: '',
    
    // Default image path
    defaultProfileImage: 'uploads/profile_picture/default-profile.jpg',
    
    // Initialize the configuration
    init: function() {
        // Detect environment
        const hostname = window.location.hostname;
        const isLocalhost = hostname === 'localhost' || 
                           hostname === '127.0.0.1' || 
                           hostname.includes('192.168.');
        
        // Set base URL based on environment
        if (isLocalhost) {
            this.baseUrl = '';
        } else if (hostname === 'eastafricanlandandrentals.net') {
            this.baseUrl = '/REAL-ESTATE/';
        } else {
            // Default for other domains
            this.baseUrl = '/';
        }
        
        console.log('AppConfig initialized with baseUrl:', this.baseUrl);
        return this;
    },
    
    // Get API URL
    getApiUrl: function(endpoint) {
        return this.baseUrl + endpoint;
    },
    
    // Get image URL
    getImageUrl: function(imagePath) {
        if (!imagePath) {
            return this.baseUrl + this.defaultProfileImage;
        }
        
        if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
            return imagePath;
        }
        
        return this.baseUrl + imagePath;
    }
};

// Initialize configuration
AppConfig.init();

// For debugging
console.log('AppConfig loaded with baseUrl:', AppConfig.baseUrl);
