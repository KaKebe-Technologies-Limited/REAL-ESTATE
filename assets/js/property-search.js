document.addEventListener('DOMContentLoaded', function() {
    // Get the search form
    const searchForm = document.getElementById('property-search-form');

    if (searchForm) {
        // Add submit event to the search form
        searchForm.addEventListener('submit', function(e) {
            // Remove empty fields to keep the URL clean
            const inputs = this.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.value === '' || input.value === 'all') {
                    input.disabled = true;
                }
            });
        });
    }

    // Populate form fields from URL parameters
    function populateFieldsFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);

        // City parameter
        if (urlParams.has('city')) {
            const cityValue = urlParams.get('city');
            const cityInput = document.getElementById('city');

            if (cityInput && cityValue) {
                cityInput.value = cityValue;
            }
        }

        // Area parameter
        if (urlParams.has('area')) {
            const areaValue = urlParams.get('area');
            const areaInput = document.getElementById('area');

            if (areaInput && areaValue) {
                areaInput.value = areaValue;
            }
        }

        // Type parameter
        if (urlParams.has('type')) {
            const typeValue = urlParams.get('type');
            const typeSelect = document.getElementById('type');

            if (typeSelect && typeValue) {
                typeSelect.value = typeValue;
            }
        }

        // Property Size parameter
        if (urlParams.has('property_size')) {
            const sizeValue = urlParams.get('property_size');
            const sizeInput = document.getElementById('property_size');

            if (sizeInput && sizeValue) {
                sizeInput.value = sizeValue;
            }
        }

        // Utilities parameter
        if (urlParams.has('utilities')) {
            const utilitiesValue = urlParams.get('utilities');
            const utilitiesInput = document.getElementById('utilities');

            if (utilitiesInput && utilitiesValue) {
                utilitiesInput.value = utilitiesValue;
            }
        }
    }

    // Call the function to populate form fields from URL parameters
    populateFieldsFromUrl();

    // Add event listener to the type select to update the form
    const typeSelect = document.getElementById('type');
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            // You can add additional logic here if needed
            // For example, showing/hiding specific fields based on property type
        });
    }
});
