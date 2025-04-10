document.addEventListener('DOMContentLoaded', function() {
    // Image preview for rentals
    document.getElementById('rental-images')?.addEventListener('change', function(e) {
        const previewContainer = document.getElementById('rental-preview-container');
        handleImagePreviews(this.files, previewContainer);
    });

    // Image preview for sales
    document.getElementById('sale-images')?.addEventListener('change', function(e) {
        const previewContainer = document.getElementById('sale-preview-container');
        handleImagePreviews(this.files, previewContainer);
    });

    // Generic function to handle image previews
    function handleImagePreviews(files, container) {
        container.innerHTML = '';
        Array.from(files).forEach((file, index) => {
            if (index >= 5) return; // Limit to 5 images
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = createPreviewElement(e.target.result, index);
                    container.appendChild(preview);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Create preview element
    function createPreviewElement(src, index) {
        const wrapper = document.createElement('div');
        wrapper.className = 'position-relative';
        wrapper.innerHTML = `
            <img src="${src}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
            <button type="button" class="btn btn-danger btn-sm position-absolute" 
                    style="top: -10px; right: -10px; border-radius: 50%; padding: 0.25rem 0.5rem;"
                    onclick="removeImage(this, ${index})">×</button>
        `;
        return wrapper;
    }

    // Handle carousel image display
    window.displayPropertyImages = function(images, type) {
        const carouselId = type === 'rental' ? 'rentalImageCarousel' : 'saleImageCarousel';
        const carousel = document.getElementById(carouselId);
        const carouselInner = carousel.querySelector('.carousel-inner');
        const thumbnailContainer = carousel.nextElementSibling;

        carouselInner.innerHTML = '';
        thumbnailContainer.innerHTML = '';

        images.forEach((image, index) => {
            // Add carousel slide
            carouselInner.innerHTML += `
                <div class="carousel-item ${index === 0 ? 'active' : ''}">
                    <img src="${image}" class="d-block w-100" style="height: 400px; object-fit: cover;">
                </div>
            `;

            // Add thumbnail
            thumbnailContainer.innerHTML += `
                <img src="${image}" 
                     class="thumbnail ${index === 0 ? 'active' : ''}" 
                     onclick="switchImage('${carouselId}', ${index})"
                     style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;">
            `;
        });
    };

    // Switch carousel image
    window.switchImage = function(carouselId, index) {
        const carousel = bootstrap.Carousel.getInstance(document.getElementById(carouselId));
        carousel.to(index);

        // Update thumbnail active state
        const thumbnails = document.querySelectorAll(`#${carouselId} + .property-thumbnails .thumbnail`);
        thumbnails.forEach(thumb => thumb.classList.remove('active'));
        thumbnails[index].classList.add('active');
    };

    // Remove image
    window.removeImage = function(button, index) {
        const wrapper = button.closest('.position-relative');
        const container = wrapper.parentElement;
        const input = container.id === 'rental-preview-container' ? 
            document.getElementById('rental-images') : 
            document.getElementById('sale-images');

        // Remove preview
        wrapper.remove();

        // Create new FileList without the removed image
        const dt = new DataTransfer();
        Array.from(input.files).forEach((file, i) => {
            if (i !== index) dt.items.add(file);
        });
        input.files = dt.files;
    };
});