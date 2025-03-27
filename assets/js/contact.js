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