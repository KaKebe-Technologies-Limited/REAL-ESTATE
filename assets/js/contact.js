document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Add your form submission logic here
    // For example, using fetch to send the data to your backend
    
    const formData = new FormData(this);
    console.log('Form submitted:', Object.fromEntries(formData));
    
    // Reset form after submission
    this.reset();
    alert('Thank you for your message! We will get back to you soon.');
});

document.getElementById('newsletter-form').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent default form submission

    const form = e.target;
    const formData = new FormData(form);

    // Send the form data to Formspree
    fetch(form.action, {
        method: form.method,
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            // Show success message
            alert('Thank you for subscribing to our newsletter!');
            form.reset(); // Reset the form
        } else {
            // Show error message
            alert('Oops! There was a problem submitting your form.');
        }
    }).catch(error => {
        // Handle network errors
        alert('Oops! There was a problem submitting your form.');
    });
});