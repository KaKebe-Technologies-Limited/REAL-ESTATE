/**
 * Custom alert system for the REAL ESTATE website
 * Replaces standard JavaScript alerts with Bootstrap alerts
 */

// Function to show a custom alert
function showAlert(message, type = 'success', dismissible = true, timeout = 5000) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} ${dismissible ? 'alert-dismissible' : ''} fade show`;
    
    // Set the inner HTML with the message and close button if dismissible
    let innerHTML = `<strong>${type.charAt(0).toUpperCase() + type.slice(1)}!</strong> ${message}`;
    
    if (dismissible) {
        innerHTML += `<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    }
    
    alertDiv.innerHTML = innerHTML;
    
    // Style the alert
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.maxWidth = '400px';
    alertDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
    
    // Add to document
    document.body.appendChild(alertDiv);
    
    // Auto-remove after timeout if specified
    if (timeout > 0) {
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 300); // Wait for fade out animation
        }, timeout);
    }
    
    return alertDiv;
}

// Override the default alert function
const originalAlert = window.alert;
window.alert = function(message) {
    // Check if it's an error message
    const isError = message && (
        message.toLowerCase().includes('error') || 
        message.toLowerCase().includes('failed') || 
        message.toLowerCase().includes('invalid')
    );
    
    // Show appropriate alert type
    showAlert(message, isError ? 'danger' : 'success');
    
    // Also log to console
    console.log(`Alert: ${message}`);
};

// Additional helper functions for specific alert types
function showSuccessAlert(message, timeout = 5000) {
    return showAlert(message, 'success', true, timeout);
}

function showErrorAlert(message, timeout = 8000) {
    return showAlert(message, 'danger', true, timeout);
}

function showWarningAlert(message, timeout = 6000) {
    return showAlert(message, 'warning', true, timeout);
}

function showInfoAlert(message, timeout = 5000) {
    return showAlert(message, 'info', true, timeout);
}
