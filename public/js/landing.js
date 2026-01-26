/* ========================================
   ReplyAI Landing Page - JavaScript
   ======================================== */

// Initialize Lucide icons
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    initNavbar();
    initMobileMenu();
    initScrollAnimations();
    initFAQ();
    initSmoothScroll();
});

// ========================================
// Navbar scroll effect
// ========================================
function initNavbar() {
    const navbar = document.getElementById('navbar');
    
    const handleScroll = () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    };
    
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll(); // Check initial state
}

// ========================================
// Mobile menu toggle
// ========================================
function initMobileMenu() {
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');
    
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navLinks.classList.toggle('active');
    });
    
    // Close menu when clicking a link
    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            hamburger.classList.remove('active');
            navLinks.classList.remove('active');
        });
    });
}

// ========================================
// Scroll-triggered animations
// ========================================
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    const observerOptions = {
        root: null,
        rootMargin: '0px 0px -100px 0px',
        threshold: 0.1
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // Optional: Unobserve after animation
                // observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    animatedElements.forEach(el => {
        observer.observe(el);
    });
}

// ========================================
// FAQ accordion
// ========================================
function initFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            
            // Close all other items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Toggle current item
            item.classList.toggle('active', !isActive);
        });
    });
}

// ========================================
// Smooth scroll for anchor links
// ========================================
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href === '#') return;
            
            const target = document.querySelector(href);
            
            if (target) {
                e.preventDefault();
                
                const navbarHeight = document.getElementById('navbar').offsetHeight;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// ========================================
// Typing animation for chat preview (optional enhancement)
// ========================================
function simulateChatTyping() {
    const typingIndicator = document.querySelector('.typing-indicator');
    
    if (!typingIndicator) return;
    
    // Add/remove typing indicator to simulate real chat
    setInterval(() => {
        typingIndicator.style.opacity = typingIndicator.style.opacity === '0' ? '1' : '0';
    }, 3000);
}

// ========================================
// Counter animation for stats (optional enhancement)
// ========================================
function animateCounters() {
    const counters = document.querySelectorAll('.stat-value');
    
    counters.forEach(counter => {
        const target = counter.innerText;
        
        // Check if it's a number
        if (!isNaN(parseInt(target))) {
            const targetNum = parseInt(target);
            const duration = 2000;
            const increment = targetNum / (duration / 16);
            let current = 0;
            
            const updateCounter = () => {
                current += increment;
                if (current < targetNum) {
                    counter.innerText = Math.floor(current);
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.innerText = target; // Restore original (with % or other suffix)
                }
            };
            
            updateCounter();
        }
    });
}

// ========================================
// Add parallax effect to hero background (optional)
// ========================================
function initParallax() {
    const heroBackground = document.querySelector('.hero-bg');
    
    if (!heroBackground) return;
    
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        heroBackground.style.transform = `translateY(${scrolled * 0.3}px)`;
    }, { passive: true });
}

// Initialize optional enhancements
document.addEventListener('DOMContentLoaded', () => {
    simulateChatTyping();
    initParallax();
});
