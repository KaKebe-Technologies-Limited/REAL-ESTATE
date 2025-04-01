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
            field.input.disabled = true;
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const addRentalForm = document.getElementById('addRental');

    addRentalForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission

        const formData = new FormData(addRentalForm);

        // Send the form data to the server using Fetch API
        fetch('http://localhost/REAL-ESTATE/add_rental.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Rental property added successfully!');
                    addRentalForm.reset(); // Reset the form
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the form.');
            });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    fetch('get_owners_managers.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const ownerSelect = document.getElementById('owner_id');
                const managerSelect = document.getElementById('manager_id');

                // Populate owners
                data.owners.forEach(owner => {
                    const option = document.createElement('option');
                    option.value = owner.owner_id;
                    option.textContent = owner.username;
                    ownerSelect.appendChild(option);
                });

                // Populate managers
                data.managers.forEach(manager => {
                    const option = document.createElement('option');
                    option.value = manager.manager_id;
                    option.textContent = manager.username;
                    managerSelect.appendChild(option);
                });
            } else {
                alert('Failed to fetch owners and managers');
            }
        })
        .catch(error => {
            console.error('Error fetching owners and managers:', error);
        });
});

document.addEventListener('DOMContentLoaded', function () {
    const addSaleForm = document.getElementById('addSales');

    addSaleForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission

        const formData = new FormData(addSaleForm);

        // Send the form data to the server using Fetch API
        fetch('http://localhost/REAL-ESTATE/add_sales.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Sales Property added successfully!');
                    addSaleForm.reset(); // Reset the form
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the form.');
            });
    });
});