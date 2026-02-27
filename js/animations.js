/**
 * Nelle Tue Mani - Studio Onicotecnico
 * Animations & Interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all animations
    initScrollReveal();
    initParallax();
    initSmoothScroll();
    initCounters();
    initPortfolioLightbox();
});

/**
 * Scroll Reveal Animation
 * Elements with .scroll-reveal class animate when entering viewport
 */
function initScrollReveal() {
    const revealElements = document.querySelectorAll('.scroll-reveal');
    
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                // Optionally unobserve after animation
                // revealObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    revealElements.forEach(el => revealObserver.observe(el));
}

/**
 * Parallax Effect for Hero Section
 */
function initParallax() {
    const heroSection = document.querySelector('#home');
    const heroImage = heroSection?.querySelector('img');
    
    if (!heroSection || !heroImage) return;
    
    // Check if device supports hover (not touch)
    const isTouchDevice = window.matchMedia('(pointer: coarse)').matches;
    
    if (!isTouchDevice) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * 0.5;
            
            if (scrolled < window.innerHeight) {
                heroImage.style.transform = `translateY(${rate}px)`;
            }
        });
    }
}

/**
 * Smooth Scroll for Anchor Links
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Animated Counters (if needed for stats)
 */
function initCounters() {
    const counters = document.querySelectorAll('.counter');
    
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000; // 2 seconds
                const step = target / (duration / 16); // 60fps
                let current = 0;
                
                const updateCounter = () => {
                    current += step;
                    if (current < target) {
                        counter.textContent = Math.floor(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                updateCounter();
                counterObserver.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => counterObserver.observe(counter));
}

/**
 * Portfolio Lightbox (simple implementation)
 */
function initPortfolioLightbox() {
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    
    portfolioItems.forEach(item => {
        item.addEventListener('click', function() {
            const img = this.querySelector('img');
            const caption = this.querySelector('span')?.textContent || '';
            
            // Create lightbox
            const lightbox = document.createElement('div');
            lightbox.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/90 opacity-0 transition-opacity duration-300';
            lightbox.innerHTML = `
                <div class="relative max-w-5xl max-h-[90vh] p-4">
                    <button class="absolute -top-12 right-0 text-white hover:text-primary transition-colors text-3xl">
                        <i class="fas fa-times"></i>
                    </button>
                    <img src="${img.src}" alt="${img.alt}" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl">
                    ${caption ? `<p class="text-white text-center mt-4 text-lg font-medium">${caption}</p>` : ''}
                </div>
            `;
            
            document.body.appendChild(lightbox);
            document.body.style.overflow = 'hidden';
            
            // Animate in
            requestAnimationFrame(() => {
                lightbox.classList.remove('opacity-0');
            });
            
            // Close handlers
            const closeLightbox = () => {
                lightbox.classList.add('opacity-0');
                setTimeout(() => {
                    lightbox.remove();
                    document.body.style.overflow = '';
                }, 300);
            };
            
            lightbox.querySelector('button').addEventListener('click', closeLightbox);
            lightbox.addEventListener('click', (e) => {
                if (e.target === lightbox) closeLightbox();
            });
            
            // Keyboard close
            document.addEventListener('keydown', function handler(e) {
                if (e.key === 'Escape') {
                    closeLightbox();
                    document.removeEventListener('keydown', handler);
                }
            });
        });
    });
}

/**
 * Stagger Animation for Lists
 * Usage: Add 'stagger-item' class to list items
 */
function initStaggerAnimation() {
    const staggerContainers = document.querySelectorAll('.stagger-container');
    
    staggerContainers.forEach(container => {
        const items = container.querySelectorAll('.stagger-item');
        
        const staggerObserver = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                items.forEach((item, index) => {
                    setTimeout(() => {
                        item.classList.add('revealed');
                    }, index * 100); // 100ms delay between items
                });
                staggerObserver.unobserve(container);
            }
        }, { threshold: 0.2 });
        
        staggerObserver.observe(container);
    });
}

/**
 * Text Typing Animation
 * Usage: Add 'type-text' class to element with data-text attribute
 */
function initTypeAnimation() {
    const typeElements = document.querySelectorAll('.type-text');
    
    typeElements.forEach(el => {
        const text = el.getAttribute('data-text');
        if (!text) return;
        
        let i = 0;
        el.textContent = '';
        
        const typeWriter = () => {
            if (i < text.length) {
                el.textContent += text.charAt(i);
                i++;
                setTimeout(typeWriter, 50);
            }
        };
        
        // Start when in view
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                typeWriter();
                observer.unobserve(el);
            }
        });
        
        observer.observe(el);
    });
}

/**
 * Magnetic Button Effect
 * Buttons follow cursor slightly on hover
 */
function initMagneticButtons() {
    const isTouchDevice = window.matchMedia('(pointer: coarse)').matches;
    if (isTouchDevice) return;
    
    const buttons = document.querySelectorAll('.magnetic');
    
    buttons.forEach(button => {
        button.addEventListener('mousemove', (e) => {
            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            
            button.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px)`;
        });
        
        button.addEventListener('mouseleave', () => {
            button.style.transform = 'translate(0, 0)';
        });
    });
}

/**
 * Scroll Progress Indicator
 */
function initScrollProgress() {
    const progressBar = document.createElement('div');
    progressBar.className = 'fixed top-0 left-0 h-1 bg-primary z-50 transition-all duration-100';
    progressBar.style.width = '0%';
    document.body.appendChild(progressBar);
    
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
        const progress = (scrolled / maxScroll) * 100;
        progressBar.style.width = `${progress}%`;
    });
}

// Initialize scroll progress if needed
// initScrollProgress();

/**
 * Intersection Observer Helper
 */
function observeElements(selector, callback, options = {}) {
    const defaultOptions = {
        threshold: 0.1,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                callback(entry.target);
            }
        });
    }, { ...defaultOptions, ...options });
    
    document.querySelectorAll(selector).forEach(el => observer.observe(el));
    
    return observer;
}

/**
 * Debounce function for performance
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function for performance
 */
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Export functions for global access
window.NelleTueMani = {
    initScrollReveal,
    initParallax,
    initSmoothScroll,
    initCounters,
    initPortfolioLightbox,
    observeElements,
    debounce,
    throttle
};
