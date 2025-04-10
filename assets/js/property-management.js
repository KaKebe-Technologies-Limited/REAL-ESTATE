document.addEventListener('DOMContentLoaded', function() {
    // View Property
    document.querySelectorAll('.view-property').forEach(button => {
        button.addEventListener('click', function() {
            const propertyId = this.getAttribute('data-id');
            fetch(`handle_property.php?id=${propertyId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('propertyDetails').innerHTML = `
                            <div class="property-info">
                                <h4>${data.property.property_name}</h4>
                                <p><strong>Price:</strong> $${data.property.price}</p>
                                <p><strong>Location:</strong> ${data.property.location}</p>
                                <!-- Add more property details as needed -->
                            </div>
                        `;
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });

    // Edit Property
    document.querySelectorAll('.edit-property').forEach(button => {
        button.addEventListener('click', function() {
            const propertyId = this.getAttribute('data-id');
            document.getElementById('editPropertyId').value = propertyId;
            
            fetch(`property-detail.php?id=${propertyId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('editPropertyName').value = data.property.property_name;
                        document.getElementById('editPropertyPrice').value = data.property.price;
                        // Set more field values as needed
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });

    // Submit Edit Form
    document.getElementById('editPropertyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('update-property.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal and refresh page or update UI
                bootstrap.Modal.getInstance(document.getElementById('editPropertyModal')).hide();
                location.reload();
            } else {
                alert('Error updating property');
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Delete Property
    let propertyToDelete = null;
    
    document.querySelectorAll('.delete-property').forEach(button => {
        button.addEventListener('click', function() {
            propertyToDelete = this.getAttribute('data-id');
        });
    });

    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (propertyToDelete) {
            fetch('delete-property.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `property_id=${propertyToDelete}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal and refresh page or update UI
                    bootstrap.Modal.getInstance(document.getElementById('deletePropertyModal')).hide();
                    location.reload();
                } else {
                    alert('Error deleting property');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
});