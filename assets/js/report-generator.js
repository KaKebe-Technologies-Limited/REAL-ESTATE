/**
 * Report Generator Script
 * Handles the generation of reports for the owner dashboard
 */

// Function to show the report selection modal
function showReportModal() {
    const modal = document.getElementById('reportModal');
    if (modal) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    } else {
        console.error('Report modal not found');
    }
}

// Function to generate a report based on the selected type
function generateReport(type) {
    // Get the user type from the page content or data attribute
    let userType = '';

    // Check which dashboard we're on to determine user type
    if (document.location.pathname.includes('managerDashboard.php')) {
        userType = 'manager';
    } else if (document.location.pathname.includes('adminDashboard.php')) {
        userType = 'admin';
    } else {
        userType = 'owner';
    }

    // Determine the correct report URL based on user type
    let reportUrl;
    if (userType === 'manager') {
        reportUrl = 'generate_manager_report.php';
    } else if (userType === 'admin') {
        reportUrl = 'generate_admin_report.php';
    } else {
        reportUrl = 'generate_report.php';
    }

    // Create a form to post to the report page to maintain the session
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = reportUrl;
    form.target = '_blank';

    // Add a hidden field for the report type
    const reportTypeField = document.createElement('input');
    reportTypeField.type = 'hidden';
    reportTypeField.name = 'report_type';
    reportTypeField.value = type;
    form.appendChild(reportTypeField);

    // Add a hidden field for the user type
    const userTypeField = document.createElement('input');
    userTypeField.type = 'hidden';
    userTypeField.name = 'user_type';
    userTypeField.value = userType;
    form.appendChild(userTypeField);

    // Add the form to the document body and submit it
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    // Close the modal if it's open
    const modal = document.getElementById('reportModal');
    if (modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    }

    // Log the report generation
    console.log(`Generated ${type} report for ${userType} using ${reportUrl}`);
}

// Initialize report generation buttons when the document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add click event listeners to all report generation buttons
    const reportButtons = document.querySelectorAll('.generate-report-btn');
    reportButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            showReportModal();
        });
    });

    // Add click event listeners to report type buttons in the modal
    const reportTypeButtons = document.querySelectorAll('.report-type-btn');
    reportTypeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const reportType = this.getAttribute('data-report-type');
            generateReport(reportType);
        });
    });
});
