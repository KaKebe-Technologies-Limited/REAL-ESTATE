document.addEventListener('DOMContentLoaded', function() {
    // Get all flip containers
    const flipContainers = document.querySelectorAll('.flip-container');

    // Add click event listener to each flip container
    flipContainers.forEach(container => {
        container.addEventListener('click', function() {
            this.classList.toggle('hover');

            // If this is a phone button, we'll handle the flip animation
            if (this.classList.contains('phone-flip')) {
                const isFlipped = this.classList.contains('hover');

                // If flipped to show the number, set a timeout to flip back
                if (isFlipped) {
                    // Store the container reference for use in the timeout
                    const containerRef = this;

                    setTimeout(() => {
                        // After showing the number for 5 seconds, flip back
                        containerRef.classList.remove('hover');
                    }, 5000);
                }
            }
        });
    });
});
