document.addEventListener('DOMContentLoaded', function() {
    // Get all gallery images
    const mainImage = document.querySelector('.main-gallery-image');
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    // Function to open image in a modal or lightbox
    function openImageModal(imageSrc) {
        // Create modal elements
        const modal = document.createElement('div');
        modal.className = 'image-modal';
        
        const modalContent = document.createElement('div');
        modalContent.className = 'modal-content';
        
        const closeBtn = document.createElement('span');
        closeBtn.className = 'close-modal';
        closeBtn.innerHTML = '&times;';
        
        const img = document.createElement('img');
        img.src = imageSrc;
        img.alt = 'Property Image';
        
        // Append elements to modal
        modalContent.appendChild(closeBtn);
        modalContent.appendChild(img);
        modal.appendChild(modalContent);
        document.body.appendChild(modal);
        
        // Prevent scrolling when modal is open
        document.body.style.overflow = 'hidden';
        
        // Show modal with animation
        setTimeout(() => {
            modal.style.opacity = '1';
        }, 10);
        
        // Close modal when clicking close button or outside the modal
        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
        
        // Close modal with escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        function closeModal() {
            modal.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(modal);
                document.body.style.overflow = '';
            }, 300);
        }
    }
    
    // Extract background image URL from style
    function getBackgroundImageUrl(element) {
        const style = window.getComputedStyle(element);
        const url = style.backgroundImage.match(/url\(['"]?([^'"]+)['"]?\)/);
        return url ? url[1] : null;
    }
    
    // Add click event to main image
    if (mainImage) {
        mainImage.addEventListener('click', function() {
            const imageSrc = getBackgroundImageUrl(this);
            if (imageSrc) {
                openImageModal(imageSrc);
            }
        });
    }
    
    // Add click events to gallery items
    galleryItems.forEach(item => {
        item.addEventListener('click', function() {
            const imageSrc = getBackgroundImageUrl(this);
            if (imageSrc) {
                openImageModal(imageSrc);
            }
        });
    });
});
