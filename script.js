// Scroll Reveal Animation
const revealElements = document.querySelectorAll('.reveal-up');

const revealOnScroll = () => {
    revealElements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;

        if (elementTop < windowHeight - 100) {
            element.classList.add('active');
        }
    });
};

window.addEventListener('scroll', revealOnScroll);
window.addEventListener('load', revealOnScroll);

// Testimonials Slider
const track = document.querySelector('.testimonial-track');
const slides = Array.from(track.children);
const nextButton = document.querySelector('.next-btn');
const prevButton = document.querySelector('.prev-btn');
const dotsContainer = document.querySelector('.slider-dots');
let currentIndex = 0;

// Create dots
slides.forEach((_, index) => {
    const dot = document.createElement('button');
    dot.classList.add('dot');
    if (index === 0) dot.classList.add('active');
    dot.addEventListener('click', () => {
        currentIndex = index;
        updateSlider();
    });
    dotsContainer.appendChild(dot);
});

const dots = Array.from(dotsContainer.children);

// Update slider position and dots
const updateSlider = () => {
    // Update slides
    slides.forEach((slide, index) => {
        slide.style.transform = `translateX(${100 * (index - currentIndex)}%)`;
    });

    // Update dots
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentIndex);
    });
};

// Next slide
const moveToNextSlide = () => {
    if (currentIndex === slides.length - 1) {
        currentIndex = 0;
    } else {
        currentIndex++;
    }
    updateSlider();
};

// Previous slide
const moveToPrevSlide = () => {
    if (currentIndex === 0) {
        currentIndex = slides.length - 1;
    } else {
        currentIndex--;
    }
    updateSlider();
};

nextButton.addEventListener('click', moveToNextSlide);
prevButton.addEventListener('click', moveToPrevSlide);

// Touch events for mobile swipe
let touchStartX = 0;
let touchEndX = 0;

track.addEventListener('touchstart', e => {
    touchStartX = e.touches[0].clientX;
});

track.addEventListener('touchmove', e => {
    touchEndX = e.touches[0].clientX;
});

track.addEventListener('touchend', () => {
    const swipeDistance = touchStartX - touchEndX;
    if (Math.abs(swipeDistance) > 50) { // Minimum swipe distance
        if (swipeDistance > 0) {
            moveToNextSlide();
        } else {
            moveToPrevSlide();
        }
    }
});

// Auto-advance slides with pause on hover
let slideInterval = setInterval(moveToNextSlide, 5000);

track.addEventListener('mouseenter', () => {
    clearInterval(slideInterval);
});

track.addEventListener('mouseleave', () => {
    slideInterval = setInterval(moveToNextSlide, 5000);
});

// Form Submission
const form = document.querySelector('.contact-form');
const submitButton = form.querySelector('button[type="submit"]');

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Disable submit button and show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    
    // Get form data
    const formData = {
        name: form.querySelector('#name').value,
        email: form.querySelector('#email').value,
        interest: form.querySelector('#interest').value
    };

    try {
        const response = await fetch('api/submit.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        let result;
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            result = await response.json();
        } else {
            throw new Error('Server returned non-JSON response');
        }

        if (response.ok) {
            // Show success message
            const successMessage = document.createElement('div');
            successMessage.className = 'alert alert-success';
            successMessage.innerHTML = `
                <i class="fas fa-check-circle"></i>
                Thank you for your interest! We will contact you soon.
            `;
            form.insertBefore(successMessage, form.firstChild);
            
            // Reset form
            form.reset();
            
            // Remove success message after 5 seconds
            setTimeout(() => {
                successMessage.remove();
            }, 5000);
        } else {
            throw new Error(result.error || 'Something went wrong');
        }
    } catch (error) {
        console.error('Form submission error:', error);
        // Show error message
        const errorMessage = document.createElement('div');
        errorMessage.className = 'alert alert-error';
        errorMessage.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            ${error.message}
        `;
        form.insertBefore(errorMessage, form.firstChild);
        
        // Remove error message after 5 seconds
        setTimeout(() => {
            errorMessage.remove();
        }, 5000);
    } finally {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = 'Get Started';
    }
}); 