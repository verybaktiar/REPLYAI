/**
 * ReplyAI Onboarding Tour
 * Step-by-step guide for new users using driver.js style
 */
class OnboardingTour {
    constructor() {
        this.steps = [];
        this.currentStep = 0;
        this.overlay = null;
        this.tooltip = null;
        this.isActive = false;
    }

    // Define tour steps
    defineSteps(steps) {
        this.steps = steps;
        return this;
    }

    // Start the tour
    start() {
        if (this.steps.length === 0) return;

        this.isActive = true;
        this.currentStep = 0;
        this.createOverlay();
        this.showStep(this.currentStep);
    }

    // Create overlay
    createOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.id = 'onboarding-overlay';
        this.overlay.innerHTML = `
            <style>
                #onboarding-overlay {
                    position: fixed;
                    inset: 0;
                    z-index: 10000;
                    pointer-events: none;
                }
                .tour-highlight {
                    position: absolute;
                    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.75);
                    border-radius: 8px;
                    pointer-events: auto;
                    z-index: 10001;
                    transition: all 0.3s ease;
                }
                .tour-tooltip {
                    position: absolute;
                    background: white;
                    border-radius: 12px;
                    padding: 20px;
                    max-width: 350px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                    pointer-events: auto;
                    z-index: 10002;
                    animation: tooltipFadeIn 0.3s ease;
                }
                @keyframes tooltipFadeIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .tour-tooltip h3 {
                    font-size: 18px;
                    font-weight: 700;
                    color: #1a1a2e;
                    margin-bottom: 8px;
                }
                .tour-tooltip p {
                    font-size: 14px;
                    color: #64748b;
                    line-height: 1.6;
                    margin-bottom: 20px;
                }
                .tour-progress {
                    display: flex;
                    gap: 6px;
                    margin-bottom: 15px;
                }
                .tour-progress span {
                    width: 24px;
                    height: 4px;
                    background: #e2e8f0;
                    border-radius: 2px;
                }
                .tour-progress span.active {
                    background: linear-gradient(135deg, #135bec, #8b5cf6);
                }
                .tour-actions {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .tour-btn {
                    padding: 10px 20px;
                    border-radius: 8px;
                    font-weight: 600;
                    font-size: 14px;
                    cursor: pointer;
                    transition: all 0.2s;
                    border: none;
                }
                .tour-btn-skip {
                    background: transparent;
                    color: #64748b;
                }
                .tour-btn-skip:hover {
                    color: #1a1a2e;
                }
                .tour-btn-next {
                    background: linear-gradient(135deg, #135bec, #8b5cf6);
                    color: white;
                }
                .tour-btn-next:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(19, 91, 236, 0.3);
                }
                .tour-btn-prev {
                    background: #f1f5f9;
                    color: #475569;
                }
            </style>
        `;
        document.body.appendChild(this.overlay);
    }

    // Show specific step
    showStep(index) {
        const step = this.steps[index];
        if (!step) return;

        const element = document.querySelector(step.element);
        if (!element) {
            this.nextStep();
            return;
        }

        // Clear previous
        this.clearHighlight();

        // Scroll element into view
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });

        setTimeout(() => {
            // Create highlight
            const rect = element.getBoundingClientRect();
            const highlight = document.createElement('div');
            highlight.className = 'tour-highlight';
            highlight.style.top = (rect.top + window.scrollY - 5) + 'px';
            highlight.style.left = (rect.left + window.scrollX - 5) + 'px';
            highlight.style.width = (rect.width + 10) + 'px';
            highlight.style.height = (rect.height + 10) + 'px';
            this.overlay.appendChild(highlight);

            // Create tooltip
            this.createTooltip(step, rect);
        }, 300);
    }

    // Create tooltip
    createTooltip(step, rect) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tour-tooltip';

        // Progress dots
        const progressDots = this.steps.map((_, i) =>
            `<span class="${i <= this.currentStep ? 'active' : ''}"></span>`
        ).join('');

        tooltip.innerHTML = `
            <div class="tour-progress">${progressDots}</div>
            <h3>${step.title}</h3>
            <p>${step.description}</p>
            <div class="tour-actions">
                <button class="tour-btn tour-btn-skip" onclick="window.onboardingTour.end()">Lewati</button>
                <div>
                    ${this.currentStep > 0 ? '<button class="tour-btn tour-btn-prev" onclick="window.onboardingTour.prevStep()">Kembali</button>' : ''}
                    <button class="tour-btn tour-btn-next" onclick="window.onboardingTour.nextStep()">
                        ${this.currentStep === this.steps.length - 1 ? 'Selesai' : 'Lanjut'}
                    </button>
                </div>
            </div>
        `;

        // Position tooltip
        const position = step.position || 'bottom';
        tooltip.style.top = (rect.bottom + window.scrollY + 15) + 'px';
        tooltip.style.left = (rect.left + window.scrollX) + 'px';

        this.overlay.appendChild(tooltip);
        this.tooltip = tooltip;
    }

    // Clear highlight
    clearHighlight() {
        const highlight = this.overlay?.querySelector('.tour-highlight');
        const tooltip = this.overlay?.querySelector('.tour-tooltip');
        if (highlight) highlight.remove();
        if (tooltip) tooltip.remove();
    }

    // Next step
    nextStep() {
        if (this.currentStep < this.steps.length - 1) {
            this.currentStep++;
            this.showStep(this.currentStep);
        } else {
            this.end();
        }
    }

    // Previous step
    prevStep() {
        if (this.currentStep > 0) {
            this.currentStep--;
            this.showStep(this.currentStep);
        }
    }

    // End tour
    end() {
        this.isActive = false;
        this.clearHighlight();
        if (this.overlay) {
            this.overlay.remove();
            this.overlay = null;
        }

        // Mark tour as completed
        localStorage.setItem('replyai_onboarding_completed', 'true');

        // Optional callback
        if (this.onComplete) {
            this.onComplete();
        }
    }

    // Check if tour should show
    static shouldShowTour() {
        return !localStorage.getItem('replyai_onboarding_completed');
    }

    // Reset tour (for testing)
    static resetTour() {
        localStorage.removeItem('replyai_onboarding_completed');
    }
}

// Initialize global instance
window.onboardingTour = new OnboardingTour();

// Default dashboard tour
window.startDashboardTour = function () {
    window.onboardingTour.defineSteps([
        {
            element: '[data-tour="sidebar"]',
            title: '👋 Selamat Datang di ReplyAI!',
            description: 'Ini adalah menu navigasi utama. Semua fitur bisa diakses dari sini.',
        },
        {
            element: '[data-tour="inbox"]',
            title: '📥 Kotak Masuk',
            description: 'Semua pesan dari WhatsApp dan Instagram akan muncul di sini.',
        },
        {
            element: '[data-tour="instagram"]',
            title: '📸 Hubungkan Instagram',
            description: 'Klik di sini untuk menghubungkan akun Instagram bisnis kamu.',
        },
        {
            element: '[data-tour="settings"]',
            title: '⚙️ Pengaturan Bot',
            description: 'Atur pesan otomatis, knowledge base, dan preferensi lainnya.',
        },
        {
            element: '[data-tour="help"]',
            title: '❓ Butuh Bantuan?',
            description: 'Klik di sini kapan saja untuk mendapatkan bantuan dari tim kami.',
        }
    ]).start();
};

// Auto-start tour for new users
document.addEventListener('DOMContentLoaded', function () {
    if (OnboardingTour.shouldShowTour()) {
        // Delay to let page fully load
        setTimeout(() => {
            if (document.querySelector('[data-tour="sidebar"]')) {
                window.startDashboardTour();
            }
        }, 1000);
    }
});
