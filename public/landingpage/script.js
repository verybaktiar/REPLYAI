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
    loadPricingFromAPI(); // Load dynamic pricing
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
        anchor.addEventListener('click', function (e) {
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
// Load Pricing from API (Dynamic)
// ========================================
async function loadPricingFromAPI() {
    const pricingGrid = document.querySelector('.pricing-grid');
    if (!pricingGrid) return;

    try {
        const response = await fetch('/api/plans');
        const plans = await response.json();

        if (plans && plans.length > 0) {
            // Clear existing static pricing
            pricingGrid.innerHTML = '';

            plans.forEach(plan => {
                const card = createPricingCard(plan);
                pricingGrid.appendChild(card);
            });

            // Re-initialize Lucide icons for new elements
            lucide.createIcons();

            // Re-apply scroll animations
            initScrollAnimations();
        }
    } catch (error) {
        console.log('Using static pricing fallback');
    }
}

function createPricingCard(plan) {
    const card = document.createElement('div');
    card.className = `pricing-card animate-on-scroll ${plan.is_popular ? 'popular' : ''}`;

    // Format price
    let priceFormatted = plan.price_monthly_display;
    if (!priceFormatted) {
        priceFormatted = formatPrice(plan.price_monthly);
    }

    // Original Price & Discount Calculation
    let originalPriceHtml = '';
    let discountPercent = 0;
    
    if (plan.price_monthly_original > plan.price_monthly && plan.price_monthly_original > 0) {
        discountPercent = Math.round(((plan.price_monthly_original - plan.price_monthly) / plan.price_monthly_original) * 100);
    }

    if (plan.price_monthly_original_display || plan.price_monthly_original > plan.price_monthly) {
        const displayOrig = plan.price_monthly_original_display || `Rp ${plan.price_monthly_original.toLocaleString('id-ID')}`;
        originalPriceHtml = `
            <div class="original-price-row">
                <span class="original-price">${displayOrig}</span>
                ${discountPercent > 0 ? `<span class="save-badge">Hemat ${discountPercent}%</span>` : ''}
            </div>`;
    } else {
        originalPriceHtml = `<div class="original-price-row"></div>`;
    }

    // Build features list
    const features = buildFeaturesList(plan.features, plan.features_list);

    card.innerHTML = `
        ${plan.is_popular ? '<div class="popular-badge">🔥 Paling Laris</div>' : ''}
        <div class="pricing-header">
            <h3>${plan.name}</h3>
            <p class="pricing-desc">${plan.description}</p>
        </div>
        <div class="pricing-price">
            <div class="price-container">
                ${originalPriceHtml}
                <div class="price-main">
                    ${plan.price_monthly_display ? '' : '<span class="currency">Rp</span>'}
                    <span class="amount">${priceFormatted}</span>
                    <span class="period">/bulan</span>
                </div>
            </div>
        </div>
        <ul class="pricing-features">
            ${features}
        </ul>
        <a href="/pricing?plan=${plan.slug}" class="btn ${plan.is_popular ? 'btn-primary' : 'btn-secondary'} btn-block">Mulai Paket ${plan.name}</a>
    `;

    return card;
}

function formatPrice(price) {
    if (price >= 1000000) {
        let val = price / 1000000;
        return (val % 1 === 0 ? val.toFixed(0) : val.toFixed(1)).replace('.', ',') + 'jt';
    } else if (price >= 1000) {
        return Math.round(price / 1000) + 'rb';
    }
    return price.toLocaleString('id-ID');
}

function buildFeaturesList(features, features_list) {
    // Priority 1: Use the descriptive features_list from DB
    if (features_list && Array.isArray(features_list) && features_list.length > 0) {
        return features_list.map(item => `<li><i data-lucide="check"></i> ${item}</li>`).join('');
    }

    if (!features) return '';

    const featureItems = [];

    // Kuantitas
    if (features.ai_messages) {
        const val = features.ai_messages === -1 ? 'Unlimited' : features.ai_messages.toLocaleString();
        featureItems.push(`${val} Pesan AI`);
    }

    if (features.contacts) {
        const val = features.contacts === -1 ? 'Unlimited' : features.contacts.toLocaleString();
        featureItems.push(`${val} Kontak`);
    }

    if (features.wa_devices) {
        const val = features.wa_devices === -1 ? 'Multi' : features.wa_devices;
        featureItems.push(`${val} Perangkat WhatsApp`);
    }

    if (features.team_members && features.team_members > 1) {
        const val = features.team_members === -1 ? 'Unlimited' : features.team_members;
        featureItems.push(`${val} Admin`);
    }

    // Fitur Boolean
    if (features.broadcasts && features.broadcasts > 0) {
        const val = features.broadcasts === -1 ? 'Unlimited' : features.broadcasts.toLocaleString();
        featureItems.push(`${val} Broadcast/bulan`);
    }

    if (features.sequences && features.sequences > 0) {
        featureItems.push('Drip Sequences');
    }

    if (features.web_widgets && features.web_widgets > 0) {
        featureItems.push('Web Chat Widget');
    }

    if (features.analytics_export) {
        featureItems.push('Export Laporan');
    }

    if (features.api_access) {
        featureItems.push('Akses API');
    }

    if (features.priority_support) {
        featureItems.push('Dukungan Prioritas');
    }

    if (features.remove_branding) {
        featureItems.push('Tanpa Branding');
    }

    if (features.sla) {
        featureItems.push('SLA Guarantee');
    }

    return featureItems.map(item => `<li><i data-lucide="check"></i> ${item}</li>`).join('');
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

