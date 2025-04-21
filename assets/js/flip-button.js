document.addEventListener('DOMContentLoaded', function() {
    // Get all flip containers
    const flipContainers = document.querySelectorAll('.flip-container');

    // Function to handle the flip action
    function handleFlip(container) {
        container.classList.toggle('hover');

        // If this is a phone button, we'll handle the flip animation
        if (container.classList.contains('phone-flip')) {
            const isFlipped = container.classList.contains('hover');

            // If flipped to show the number, set a timeout to flip back
            if (isFlipped) {
                // Clear any existing timeout to prevent multiple timers
                if (container.flipTimeout) {
                    clearTimeout(container.flipTimeout);
                }

                // Set a new timeout
                container.flipTimeout = setTimeout(() => {
                    // After showing the number for 5 seconds, flip back
                    container.classList.remove('hover');
                }, 5000);
            }
        }
    }

    // Add event listeners to each flip container
    flipContainers.forEach(container => {
        // Use touchstart for mobile devices to reduce delay
        container.addEventListener('touchstart', function(e) {
            e.preventDefault(); // Prevent default touch behavior
            handleFlip(this);
        }, { passive: false });

        // Also keep click for desktop devices
        container.addEventListener('click', function(e) {
            // Only handle click if it's not a touch device or the event wasn't triggered by touch
            if (!('ontouchstart' in window) || !e.sourceCapabilities || !e.sourceCapabilities.firesTouchEvents) {
                handleFlip(this);
            }
        });

        // Fix for iOS Safari which sometimes has issues with hover states
        if ('ontouchstart' in window) {
            container.addEventListener('touchend', function(e) {
                e.preventDefault();
            }, { passive: false });
        }
    });

    // Fix for some mobile browsers that might have issues with 3D transforms
    if ('ontouchstart' in window) {
        document.documentElement.classList.add('touch-device');
    }
});
