document.addEventListener('DOMContentLoaded', function() {
    // Initialize rating stars in the form
    const ratingStars = document.querySelectorAll('.rating-select i');
    const ratingInput = document.getElementById('rating-input');
    const ratingForm = document.getElementById('rating-form');

    // Handle star rating selection
    if (ratingStars.length > 0) {
        ratingStars.forEach(star => {
            star.addEventListener('click', function() {
                const value = parseInt(this.getAttribute('data-value'));
                ratingInput.value = value;

                // Update visual state of stars
                ratingStars.forEach(s => {
                    if (parseInt(s.getAttribute('data-value')) <= value) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
        });
    }

    // Handle rating form submission
    if (ratingForm) {
        ratingForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form data
            const formData = new FormData(this);

            // Validate form
            if (!formData.get('rating') || formData.get('rating') === '0') {
                showAlert('Please select a rating', 'error');
                return;
            }

            if (!formData.get('user_name') || !formData.get('user_email')) {
                showAlert('Please provide your name and email', 'error');
                return;
            }

            // Submit rating
            fetch('submit_rating.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    // Reset form
                    ratingForm.reset();
                    ratingStars.forEach(s => s.classList.remove('active'));

                    // Reload ratings after submission
                    loadPropertyRatings();
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'error');
            });
        });
    }

    // Load property ratings on page load
    loadPropertyRatings();

    // Load ratings for property cards on properties.php
    loadPropertyCardRatings();
});

// Function to load property ratings
function loadPropertyRatings() {
    const propertyId = document.getElementById('property-id')?.value;
    let propertyType = document.getElementById('property-type')?.value;
    const ratingSummaryContainer = document.getElementById('rating-summary');
    const reviewsContainer = document.getElementById('reviews-list');

    if (!propertyId || !propertyType || (!ratingSummaryContainer && !reviewsContainer)) {
        return;
    }

    // Normalize property type for API call
    if (propertyType === 'rent') {
        propertyType = 'rental';
    }

    console.log(`Loading detailed ratings for property ${propertyId} of type ${propertyType}`);

    fetch(`get_property_ratings.php?property_id=${propertyId}&property_type=${propertyType}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('Rating data received:', data);

                // Update rating summary
                if (ratingSummaryContainer) {
                    updateRatingSummary(data, ratingSummaryContainer);
                }

                // Update reviews list
                if (reviewsContainer) {
                    updateReviewsList(data.reviews, reviewsContainer);
                }

                // Update all rating displays on the page
                updateRatingDisplays(data.average_rating, data.rating_count);
            } else {
                console.error('Error in rating data:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading ratings:', error);
        });
}

// Function to update rating summary
function updateRatingSummary(data, container) {
    const totalRatings = data.rating_count;
    const averageRating = data.average_rating;
    const distribution = data.distribution;

    let summaryHTML = `
        <div class="average-rating">
            <div class="average-score">${averageRating}</div>
            <div class="rating-stars">
                ${generateStars(averageRating)}
            </div>
            <div class="total-ratings">${totalRatings} ${totalRatings === 1 ? 'rating' : 'ratings'}</div>
        </div>
        <div class="rating-bars">
    `;

    for (let i = 5; i >= 1; i--) {
        const count = distribution[i] || 0;
        const percentage = totalRatings > 0 ? (count / totalRatings) * 100 : 0;

        summaryHTML += `
            <div class="rating-bar">
                <div class="rating-label">${i}</div>
                <div class="rating-progress">
                    <div class="rating-progress-fill" style="width: ${percentage}%"></div>
                </div>
                <div class="rating-count">${count}</div>
            </div>
        `;
    }

    summaryHTML += '</div>';
    container.innerHTML = summaryHTML;
}

// Function to update reviews list
function updateReviewsList(reviews, container) {
    if (reviews.length === 0) {
        container.innerHTML = '<p class="no-reviews">No reviews yet. Be the first to leave a review!</p>';
        return;
    }

    let reviewsHTML = '<h3>Recent Reviews</h3>';

    reviews.forEach(review => {
        reviewsHTML += `
            <div class="review-item">
                <div class="review-header">
                    <div class="review-author">${review.user_name}</div>
                    <div class="review-date">${review.formatted_date}</div>
                </div>
                <div class="review-rating">
                    <div class="rating-stars small">
                        ${generateStars(review.rating)}
                    </div>
                </div>
                <div class="review-text">${review.review_text}</div>
            </div>
        `;
    });

    container.innerHTML = reviewsHTML;
}

// Function to update all rating displays on the page
function updateRatingDisplays(averageRating, ratingCount) {
    const ratingDisplays = document.querySelectorAll('.property-rating-display');

    ratingDisplays.forEach(display => {
        display.innerHTML = `
            <div class="rating-stars">
                ${generateStars(averageRating)}
                <span class="rating-text">${averageRating} (${ratingCount})</span>
            </div>
        `;
    });
}

// Function to generate star HTML based on rating
function generateStars(rating) {
    let starsHTML = '';
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5;

    for (let i = 1; i <= 5; i++) {
        if (i <= fullStars) {
            starsHTML += '<i class="fas fa-star"></i>';
        } else if (i === fullStars + 1 && halfStar) {
            starsHTML += '<i class="fas fa-star-half-alt"></i>';
        } else {
            starsHTML += '<i class="far fa-star"></i>';
        }
    }

    return starsHTML;
}

// Function to show alerts (uses custom-alerts.js if available)
function showAlert(message, type) {
    if (typeof customAlert === 'function') {
        customAlert(message, type);
    } else {
        alert(message);
    }
}

// Function to load ratings for property cards on properties.php
function loadPropertyCardRatings() {
    const propertyCards = document.querySelectorAll('.property-card-rating');

    if (propertyCards.length === 0) {
        return;
    }

    console.log('Found property cards:', propertyCards.length);

    // Process each property card
    propertyCards.forEach(card => {
        const propertyId = card.getAttribute('data-property-id');
        let propertyType = card.getAttribute('data-property-type');

        if (!propertyId || !propertyType) {
            console.error('Missing property ID or type for card:', card);
            return;
        }

        // Normalize property type for API call
        if (propertyType === 'rent') {
            propertyType = 'rental';
        }

        console.log(`Fetching ratings for property ${propertyId} of type ${propertyType}`);

        // Fetch ratings for this property
        fetch(`get_property_ratings.php?property_id=${propertyId}&property_type=${propertyType}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update the rating display for this card
                    card.innerHTML = `
                        <div class="rating-stars small">
                            ${generateStars(data.average_rating)}
                            <span class="rating-text">${data.average_rating} (${data.rating_count})</span>
                        </div>
                    `;
                } else {
                    console.error('Error fetching ratings:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading ratings for property:', propertyId, error);
            });
    });
}
