// Responsive Tables and Search Inputs

document.addEventListener('DOMContentLoaded', function() {
    // Make search inputs more responsive
    const searchInputs = document.querySelectorAll('.search-box input');
    searchInputs.forEach(input => {
        // Add responsive classes
        input.classList.add('form-control-sm');
        
        // Add event listener for search functionality
        input.addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const dataType = e.target.getAttribute('data-type');
            const tableBody = e.target.closest('.card-body').querySelector('tbody');
            
            if (tableBody) {
                const rows = tableBody.querySelectorAll('tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    });
    
    // Make filter buttons more responsive
    const filterButtons = document.querySelectorAll('.btn-primary i.fas.fa-filter');
    filterButtons.forEach(button => {
        const parentButton = button.closest('.btn');
        if (parentButton) {
            parentButton.classList.add('btn-sm');
            
            // On small screens, show only the icon
            if (window.innerWidth < 576) {
                const text = parentButton.textContent;
                parentButton.setAttribute('data-original-text', text.trim());
                parentButton.innerHTML = '<i class="fas fa-filter"></i>';
                parentButton.classList.add('icon-only');
            }
        }
    });
    
    // Handle window resize for filter buttons
    window.addEventListener('resize', function() {
        const filterButtons = document.querySelectorAll('.btn-primary.icon-only');
        filterButtons.forEach(button => {
            if (window.innerWidth < 576) {
                if (!button.classList.contains('icon-only')) {
                    const text = button.textContent;
                    button.setAttribute('data-original-text', text.trim());
                    button.innerHTML = '<i class="fas fa-filter"></i>';
                    button.classList.add('icon-only');
                }
            } else {
                if (button.classList.contains('icon-only')) {
                    const originalText = button.getAttribute('data-original-text');
                    if (originalText) {
                        button.innerHTML = `<i class="fas fa-filter"></i> ${originalText}`;
                        button.classList.remove('icon-only');
                    }
                }
            }
        });
    });
    
    // Make table action buttons more responsive
    const actionButtons = document.querySelectorAll('.btn-group .btn-sm');
    actionButtons.forEach(button => {
        // On very small screens, show only icons
        if (window.innerWidth < 576) {
            const text = button.textContent;
            if (text && text.trim()) {
                button.setAttribute('data-original-text', text.trim());
                const icon = button.querySelector('i');
                if (icon) {
                    button.innerHTML = '';
                    button.appendChild(icon.cloneNode(true));
                }
            }
        }
    });
    
    // Enhance table responsiveness
    const tables = document.querySelectorAll('.table-responsive');
    tables.forEach(tableWrapper => {
        // Add a horizontal scroll indicator
        const scrollIndicator = document.createElement('div');
        scrollIndicator.className = 'scroll-indicator d-block d-sm-none text-center text-muted small mb-2';
        scrollIndicator.innerHTML = '<i class="fas fa-arrows-left-right"></i> Swipe to see more';
        tableWrapper.parentNode.insertBefore(scrollIndicator, tableWrapper);
        
        // Add touch-based horizontal scrolling for mobile
        let isDown = false;
        let startX;
        let scrollLeft;
        
        tableWrapper.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - tableWrapper.offsetLeft;
            scrollLeft = tableWrapper.scrollLeft;
        });
        
        tableWrapper.addEventListener('mouseleave', () => {
            isDown = false;
        });
        
        tableWrapper.addEventListener('mouseup', () => {
            isDown = false;
        });
        
        tableWrapper.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - tableWrapper.offsetLeft;
            const walk = (x - startX) * 2;
            tableWrapper.scrollLeft = scrollLeft - walk;
        });
        
        // Touch events for mobile
        tableWrapper.addEventListener('touchstart', (e) => {
            isDown = true;
            startX = e.touches[0].pageX - tableWrapper.offsetLeft;
            scrollLeft = tableWrapper.scrollLeft;
        });
        
        tableWrapper.addEventListener('touchend', () => {
            isDown = false;
        });
        
        tableWrapper.addEventListener('touchmove', (e) => {
            if (!isDown) return;
            const x = e.touches[0].pageX - tableWrapper.offsetLeft;
            const walk = (x - startX) * 2;
            tableWrapper.scrollLeft = scrollLeft - walk;
        });
    });
});
