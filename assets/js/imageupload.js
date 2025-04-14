class ImageUploadHandler {
    constructor(inputId, previewContainerId, maxFiles = 5, maxFileSize = 5) {
        this.input = document.getElementById(inputId);
        this.previewContainer = document.getElementById(previewContainerId);
        this.maxFiles = maxFiles;
        this.maxFileSize = maxFileSize * 1024 * 1024; // Convert to bytes
        this.selectedFiles = new Set();

        if (this.input && this.previewContainer) {
            this.initialize();
        }
    }

    initialize() {
        this.input.addEventListener('change', (e) => this.handleFileSelect(e));
        this.setupDragAndDrop();
    }

    setupDragAndDrop() {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this.previewContainer.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            this.previewContainer.addEventListener(eventName, () => {
                this.previewContainer.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            this.previewContainer.addEventListener(eventName, () => {
                this.previewContainer.classList.remove('drag-over');
            });
        });

        this.previewContainer.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            this.handleFiles(files);
        });
    }

    handleFileSelect(e) {
        const files = Array.from(e.target.files);
        this.handleFiles(files);
    }

    handleFiles(files) {
        if (this.selectedFiles.size + files.length > this.maxFiles) {
            alert(`You can only upload a maximum of ${this.maxFiles} images`);
            return;
        }

        files.forEach(file => this.processFile(file));
    }

    processFile(file) {
        if (!file.type.startsWith('image/')) {
            alert(`${file.name} is not an image file`);
            return;
        }

        if (file.size > this.maxFileSize) {
            alert(`${file.name} is too large. Maximum file size is ${this.maxFileSize / 1024 / 1024}MB`);
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => this.createPreviewElement(e.target.result, file);
        reader.readAsDataURL(file);
    }

    createPreviewElement(previewUrl, file) {
        const preview = document.createElement('div');
        preview.className = 'image-preview';
        preview.innerHTML = `
            <img src="${previewUrl}" alt="Preview">
            <button type="button" class="remove-image">
                <i class="fas fa-times"></i>
            </button>
        `;

        preview.querySelector('.remove-image').addEventListener('click', () => {
            preview.remove();
            this.selectedFiles.delete(file);
            this.updateInputFiles();
        });

        this.previewContainer.appendChild(preview);
        this.selectedFiles.add(file);
        this.updateInputFiles();
    }

    updateInputFiles() {
        const dataTransfer = new DataTransfer();
        this.selectedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });
        this.input.files = dataTransfer.files;
    }

    getFiles() {
        return Array.from(this.selectedFiles);
    }

    clear() {
        this.selectedFiles.clear();
        this.previewContainer.innerHTML = '';
        this.updateInputFiles();
    }
}

// Initialize image upload handlers for both forms
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('country');
    const inputs = {
        region: {
            label: document.getElementById('region-label'),
            input: document.getElementById('region')
        },
        subregion: {
            label: document.getElementById('subregion-label'),
            input: document.getElementById('subregion')
        },
        parish: {
            label: document.getElementById('parish-label'),
            input: document.getElementById('parish')
        },
        ward: {
            label: document.getElementById('ward-label'),
            input: document.getElementById('ward')
        },
        cell: {
            label: document.getElementById('cell-label'),
            input: document.getElementById('cell')
        }
    };

    const locationLabels = {
        uganda: {
            firstLevel: 'District',
            secondLevel: 'County',
            thirdLevel: 'Parish',
            fourthLevel: 'Ward',
            fifthLevel: 'Cell',
            placeholders: {
                firstLevel: 'Enter district name',
                secondLevel: 'Enter county name',
                thirdLevel: 'Enter parish name',
                fourthLevel: 'Enter ward name',
                fifthLevel: 'Enter cell name'
            }
        },
        kenya: {
            firstLevel: 'County',
            secondLevel: 'Sub-County',
            thirdLevel: 'Ward',
            fourthLevel: 'Location',
            fifthLevel: 'Sub-Location',
            placeholders: {
                firstLevel: 'Enter county name',
                secondLevel: 'Enter sub-county name',
                thirdLevel: 'Enter ward name',
                fourthLevel: 'Enter location name',
                fifthLevel: 'Enter sub-location name'
            }
        },
        tanzania: {
            firstLevel: 'Region',
            secondLevel: 'District',
            thirdLevel: 'Division',
            fourthLevel: 'Ward',
            fifthLevel: 'Village',
            placeholders: {
                firstLevel: 'Enter region name',
                secondLevel: 'Enter district name',
                thirdLevel: 'Enter division name',
                fourthLevel: 'Enter ward name',
                fifthLevel: 'Enter village name'
            }
        },
        rwanda: {
            firstLevel: 'Province',
            secondLevel: 'District',
            thirdLevel: 'Sector',
            fourthLevel: 'Cell',
            fifthLevel: 'Village',
            placeholders: {
                firstLevel: 'Enter province name',
                secondLevel: 'Enter district name',
                thirdLevel: 'Enter sector name',
                fourthLevel: 'Enter cell name',
                fifthLevel: 'Enter village name'
            }
        },
        burundi: {
            firstLevel: 'Province',
            secondLevel: 'Commune',
            thirdLevel: 'Zone',
            fourthLevel: 'Colline',
            fifthLevel: 'Sous-Colline',
            placeholders: {
                firstLevel: 'Enter province name',
                secondLevel: 'Enter commune name',
                thirdLevel: 'Enter zone name',
                fourthLevel: 'Enter colline name',
                fifthLevel: 'Enter sous-colline name'
            }
        },
        south_sudan: {
            firstLevel: 'State',
            secondLevel: 'County',
            thirdLevel: 'Payam',
            fourthLevel: 'Boma',
            fifthLevel: 'Village',
            placeholders: {
                firstLevel: 'Enter state name',
                secondLevel: 'Enter county name',
                thirdLevel: 'Enter payam name',
                fourthLevel: 'Enter boma name',
                fifthLevel: 'Enter village name'
            }
        },
        drc: {
            firstLevel: 'Province',
            secondLevel: 'Territory',
            thirdLevel: 'Sector',
            fourthLevel: 'Groupement',
            fifthLevel: 'Village',
            placeholders: {
                firstLevel: 'Enter province name',
                secondLevel: 'Enter territory name',
                thirdLevel: 'Enter sector name',
                fourthLevel: 'Enter groupement name',
                fifthLevel: 'Enter village name'
            }
        }
    };

    countrySelect.addEventListener('change', function() {
        const selectedCountry = this.value;
        const labels = locationLabels[selectedCountry];

        if (labels) {
            // Update labels and placeholders
            updateField(inputs.region, labels.firstLevel, labels.placeholders.firstLevel);
            updateField(inputs.subregion, labels.secondLevel, labels.placeholders.secondLevel);
            updateField(inputs.parish, labels.thirdLevel, labels.placeholders.thirdLevel);
            updateField(inputs.ward, labels.fourthLevel, labels.placeholders.fourthLevel);
            updateField(inputs.cell, labels.fifthLevel, labels.placeholders.fifthLevel);

            // Enable all inputs
            Object.values(inputs).forEach(field => {
                field.input.disabled = false;
            });
        } else {
            // Reset and disable all inputs if no country is selected
            resetAllFields();
        }
    });

    function updateField(field, labelText, placeholder) {
        field.label.textContent = labelText;
        field.input.placeholder = placeholder;
        field.input.value = '';
    }

    function resetAllFields() {
        const defaultLabels = {
            region: 'Region',
            subregion: 'Sub-Region',
            parish: 'Parish',
            ward: 'Ward',
            cell: 'Cell'
        };

        Object.entries(inputs).forEach(([key, field]) => {
            field.label.textContent = defaultLabels[key];
            field.input.placeholder = `Enter ${defaultLabels[key].toLowerCase()}`;
            field.input.value = '';
            field.input.disabled = false;
        });
    }

    // Function to populate select elements with error handling
    const populateSelect = (selectId, options, valueKey, textKey) => {
        const selectElement = document.getElementById(selectId);
        if (!selectElement) {
            console.warn(`Select element with id '${selectId}' not found`);
            return false;
        }

        try {
            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option[valueKey];
                optionElement.textContent = option[textKey];
                selectElement.appendChild(optionElement);
            });
            return true;
        } catch (error) {
            console.error(`Error populating select ${selectId}:`, error);
            return false;
        }
    };

    // Fetch and populate owners and managers
    fetch('get_owners_managers.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to fetch data');
            }

            const selects = {
                owners: ['rental_owner_id', 'sale_owner_id'],
                managers: ['rental_manager_id', 'sale_manager_id']
            };

            // Debug logging
            console.log('Looking for select elements...');
            selects.owners.forEach(id => {
                const elem = document.getElementById(id);
                console.log(`${id} element:`, elem);
            });
            selects.managers.forEach(id => {
                const elem = document.getElementById(id);
                console.log(`${id} element:`, elem);
            });

            // Modified population logic with checks
            let populatedSelects = 0;

            // Populate owners
            selects.owners.forEach(selectId => {
                if (populateSelect(selectId, data.owners, 'owner_id', 'username')) {
                    populatedSelects++;
                }
            });

            // Populate managers
            selects.managers.forEach(selectId => {
                if (populateSelect(selectId, data.managers, 'manager_id', 'username')) {
                    populatedSelects++;
                }
            });

            if (populatedSelects === 0) {
                throw new Error('No select elements were found to populate');
            }
        })
        .catch(error => {
            console.error('Error fetching owners and managers:', error);
            // Optionally show user-friendly error message
            const errorMessage = document.createElement('div');
            errorMessage.className = 'alert alert-danger';
            errorMessage.textContent = 'Failed to load owners and managers. Please refresh the page.';
            document.querySelector('.main-content')?.prepend(errorMessage);
        });
    });

    const rentalImageHandler = new ImageUploadHandler('rental-images', 'rental-preview-container');
    const saleImageHandler = new ImageUploadHandler('sale-images', 'sale-preview-container');

    // Update form submission handlers
    const addRentalForm = document.getElementById('addRental');
    if (addRentalForm) {
        addRentalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (rentalImageHandler.getFiles().length === 0) {
                alert('Please select at least one image');
                return;
            }
            submitPropertyForm(this, 'add_rental.php', rentalImageHandler);
        });
    }

    const addSaleForm = document.getElementById('addSales');
    if (addSaleForm) {
        addSaleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (saleImageHandler.getFiles().length === 0) {
                alert('Please select at least one image');
                return;
            }
            submitPropertyForm(this, 'add_sales.php', saleImageHandler);
        });
    }

function submitPropertyForm(form, endpoint, imageHandler) {
    const formData = new FormData(form);

    // Clear any existing files from the form data
    if (formData.has('images[]')) {
        formData.delete('images[]');
    }

    // Add files to FormData
    imageHandler.getFiles().forEach((file, index) => {
        formData.append(`images[]`, file);
    });

    // Debug log the form data
    console.log('Form data being sent:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value instanceof File ? value.name : value}`);
    }

    fetch(`${endpoint}`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Property added successfully!');
            form.reset();
            imageHandler.clear();
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add property: ' + error.message);
    });
}