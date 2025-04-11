document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const mobileMenuWrapper = document.querySelector('.mobile-menu-wrapper');

    if (mobileMenuButton && mobileMenuWrapper) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenuWrapper.classList.toggle('show');
            this.classList.toggle('active');
        });
    }

    // Newsletter form submission
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            console.log('Newsletter subscription for:', email);
        });
    }

    // Search wrapper toggle functionality
    const searchToggle = document.getElementById('searchToggle');
    const mobileSearchToggle = document.getElementById('mobileSearchToggle');
    const searchWrapper = document.getElementById('search_wrapper');

    if (searchWrapper) {
        function toggleSearch(button) {
            if (!button) return;

            searchWrapper.classList.toggle('show');
            const icon = button.querySelector('i');

            if (icon) {
                if (searchWrapper.classList.contains('show')) {
                    icon.className = 'fas fa-times';
                    const span = button.querySelector('span');
                    if (span) {
                        span.textContent = ' Close';
                    } else {
                        button.textContent = ' Close';
                    }
                } else {
                    icon.className = 'fas fa-search';
                    const span = button.querySelector('span');
                    if (span) {
                        span.textContent = ' Search';
                    } else {
                        button.textContent = ' Search';
                    }
                }
            }
        }

        if (searchToggle) {
            searchToggle.addEventListener('click', () => toggleSearch(searchToggle));
        }

        if (mobileSearchToggle) {
            mobileSearchToggle.addEventListener('click', () => toggleSearch(mobileSearchToggle));
        }
    }

    // Add scroll event handler for header
    window.addEventListener('scroll', function() {
        const headerWrapper = document.querySelector('.header_wrapper');
        const topBarWrapper = document.querySelector('.top_bar_wrapper');
        const scrollPosition = window.scrollY;

        if (headerWrapper) {
            if (scrollPosition > 100) {
                headerWrapper.classList.add('scrolled');
                headerWrapper.style.background = 'white';
                if (topBarWrapper) {
                    topBarWrapper.classList.add('hidden');
                }
            } else {
                headerWrapper.classList.remove('scrolled');
                if (topBarWrapper) {
                    topBarWrapper.classList.remove('hidden');
                }
            }
        }
    });

    // Back to top button functionality
    const backToTopButton = document.getElementById('backToTop');

    if (backToTopButton) {
        window.addEventListener('scroll', function() {
            if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
                backToTopButton.style.display = "block";
            } else {
                backToTopButton.style.display = "none";
            }
        });

        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Add mobile header scroll behavior
    let lastScroll = 0;
    const header = document.querySelector('.master_header');

    if (header) {
        window.addEventListener('scroll', () => {
            if (window.innerWidth <= 768) {
                const currentScroll = window.pageYOffset;

                if (currentScroll <= 0) {
                    header.classList.remove('nav-up');
                    header.classList.add('nav-down');
                    return;
                }

                if (currentScroll > lastScroll && currentScroll > 60) {
                    // Scrolling down & past header height
                    header.classList.remove('nav-down');
                    header.classList.add('nav-up');
                } else {
                    // Scrolling up
                    header.classList.remove('nav-up');
                    header.classList.add('nav-down');
                }
                lastScroll = currentScroll;
            }
        });
    }

    // Property filtering
    const filterButtons = document.querySelectorAll('.filter-btn');

    if (filterButtons.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                console.log(`Filter button clicked: ${button.getAttribute('data-filter')}`);

                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));

                // Add active class to clicked button
                button.classList.add('active');

                // Reset pagination to first page after filtering
                if (typeof currentPage !== 'undefined' && typeof setupPagination === 'function') {
                    currentPage = 1;
                    setupPagination();
                }
            });
        });
    }

    // Pagination
    const itemsPerPage = 9; // Changed to 9 for better grid layout (3x3)
    let currentPage = 1;

    const propertyGrid = document.querySelector('.property-grid');
    const pageNumbers = document.querySelector('.page-numbers');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const paginationContainer = document.querySelector('.pagination-container');

    if (propertyGrid && pageNumbers && prevBtn && nextBtn) {
        // Debug function to log pagination state
        function logPaginationState(message) {
            console.log(`${message} - Current Page: ${currentPage}, Total Properties: ${propertyGrid.querySelectorAll('.property_listing').length}`);
        }

        function setupPagination() {
            logPaginationState('Setting up pagination');

            // Get all property listings
            const allProperties = Array.from(propertyGrid.querySelectorAll('.property_listing'));
            console.log(`Total properties: ${allProperties.length}`);

            // Get only visible properties (respecting filters)
            const activeFilter = document.querySelector('.filter-btn.active');
            const filterValue = activeFilter ? activeFilter.getAttribute('data-filter') : 'all';
            console.log(`Active filter: ${filterValue}`);

            const visibleProperties = allProperties.filter(prop => {
                if (filterValue === 'all') return true;
                return prop.getAttribute('data-category') === filterValue;
            });

            console.log(`Visible properties: ${visibleProperties.length}`);

            // Calculate total pages
            const totalPages = Math.max(1, Math.ceil(visibleProperties.length / itemsPerPage));
            console.log(`Total pages: ${totalPages}, Items per page: ${itemsPerPage}`);

            // Show pagination only if we have more than one page
            if (paginationContainer) {
                paginationContainer.style.display = totalPages > 1 ? 'flex' : 'none';
            }

            // Ensure current page is valid
            if (currentPage > totalPages) {
                currentPage = totalPages;
            }

            // Generate page numbers
            pageNumbers.innerHTML = '';

            // Limit the number of page buttons shown
            const maxPageButtons = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxPageButtons / 2));
            let endPage = Math.min(totalPages, startPage + maxPageButtons - 1);

            // Adjust start page if we're near the end
            if (endPage - startPage + 1 < maxPageButtons && startPage > 1) {
                startPage = Math.max(1, endPage - maxPageButtons + 1);
            }

            // Add first page button if not included
            if (startPage > 1) {
                const firstPageBtn = document.createElement('button');
                firstPageBtn.className = 'page-number';
                firstPageBtn.type = 'button';
                firstPageBtn.textContent = '1';
                firstPageBtn.addEventListener('click', function() {
                    goToPage(1);
                });
                pageNumbers.appendChild(firstPageBtn);

                // Add ellipsis if there's a gap
                if (startPage > 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'page-ellipsis';
                    ellipsis.textContent = '...';
                    pageNumbers.appendChild(ellipsis);
                }
            }

            // Add page numbers
            for (let i = startPage; i <= endPage; i++) {
                const pageNumber = document.createElement('button');
                pageNumber.className = `page-number ${i === currentPage ? 'active' : ''}`;
                pageNumber.type = 'button';
                pageNumber.textContent = i;
                pageNumber.addEventListener('click', function() {
                    goToPage(i);
                });
                pageNumbers.appendChild(pageNumber);
            }

            // Add last page button if not included
            if (endPage < totalPages) {
                // Add ellipsis if there's a gap
                if (endPage < totalPages - 1) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'page-ellipsis';
                    ellipsis.textContent = '...';
                    pageNumbers.appendChild(ellipsis);
                }

                const lastPageBtn = document.createElement('button');
                lastPageBtn.className = 'page-number';
                lastPageBtn.type = 'button';
                lastPageBtn.textContent = totalPages;
                lastPageBtn.addEventListener('click', function() {
                    goToPage(totalPages);
                });
                pageNumbers.appendChild(lastPageBtn);
            }

            // Update prev/next buttons
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages || totalPages === 0;

            // Calculate start and end indices for current page
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, visibleProperties.length);
            console.log(`Showing properties from index ${startIndex} to ${endIndex-1}`);

            // First hide all properties
            allProperties.forEach(property => {
                property.style.display = 'none';
            });

            // Then show only the filtered properties for the current page
            for (let i = 0; i < visibleProperties.length; i++) {
                if (i >= startIndex && i < endIndex) {
                    visibleProperties[i].style.display = 'block';
                    console.log(`Showing property at index ${i}`);
                }
            }

            // Show a message if no properties are found
            const noPropertiesMessage = document.querySelector('.no-properties');
            if (noPropertiesMessage) {
                if (visibleProperties.length === 0) {
                    noPropertiesMessage.style.display = 'block';
                } else {
                    noPropertiesMessage.style.display = 'none';
                }
            }
        }

        function goToPage(page) {
            console.log(`Going to page ${page}`);
            currentPage = page;
            setupPagination();

            // Scroll to the top of the property grid instead of the page top
            if (propertyGrid) {
                propertyGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // Add event listeners for prev/next buttons
        prevBtn.addEventListener('click', function() {
            console.log('Previous button clicked');
            if (currentPage > 1) {
                goToPage(currentPage - 1);
            }
        });

        nextBtn.addEventListener('click', function() {
            console.log('Next button clicked');
            // Get visible properties based on current filter
            const activeFilter = document.querySelector('.filter-btn.active');
            const filterValue = activeFilter ? activeFilter.getAttribute('data-filter') : 'all';

            const visibleProperties = Array.from(propertyGrid.querySelectorAll('.property_listing')).filter(prop => {
                if (filterValue === 'all') return true;
                return prop.getAttribute('data-category') === filterValue;
            });

            const totalPages = Math.ceil(visibleProperties.length / itemsPerPage);
            console.log(`Current page: ${currentPage}, Total pages: ${totalPages}`);

            if (currentPage < totalPages) {
                goToPage(currentPage + 1);
            }
        });

        // Initialize pagination
        window.addEventListener('load', function() {
            console.log('Window loaded, initializing pagination');
            // Add a small delay to ensure all DOM elements are fully loaded and rendered
            setTimeout(function() {
                setupPagination();
            }, 100);
        });

        // Also initialize on DOMContentLoaded to be safe
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM content loaded, initializing pagination');
            setTimeout(function() {
                setupPagination();
            }, 100);
        });

        // Initial setup
        setupPagination();
    }
});
