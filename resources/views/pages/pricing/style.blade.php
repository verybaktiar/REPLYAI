/* ========================================
   ReplyAI Landing Page - Premium Styles
   ======================================== */

/* CSS Variables */
:root {
    /* Colors */
    --primary-hue: 250;
    --primary: hsl(var(--primary-hue), 100%, 65%);
    --primary-dark: hsl(var(--primary-hue), 100%, 55%);
    --primary-light: hsl(var(--primary-hue), 100%, 75%);
    --primary-glow: hsla(var(--primary-hue), 100%, 65%, 0.3);
    
    --secondary: hsl(280, 100%, 65%);
    --accent: hsl(200, 100%, 60%);
    
    --bg-dark: hsl(240, 20%, 8%);
    --bg-primary: hsl(240, 15%, 12%);
    --bg-secondary: hsl(240, 15%, 16%);
    --bg-tertiary: hsl(240, 15%, 20%);
    
    --text-primary: hsl(0, 0%, 98%);
    --text-secondary: hsl(240, 10%, 70%);
    --text-muted: hsl(240, 10%, 50%);
    
    --border: hsla(0, 0%, 100%, 0.1);
    --border-light: hsla(0, 0%, 100%, 0.05);
    
    --success: hsl(150, 80%, 50%);
    --warning: hsl(40, 100%, 60%);
    --error: hsl(0, 80%, 60%);
    
    /* Typography */
    --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    
    /* Spacing */
    --section-padding: 100px 0;
    --container-max: 1200px;
    
    /* Effects */
    --glass-bg: hsla(240, 15%, 20%, 0.6);
    --glass-border: hsla(0, 0%, 100%, 0.1);
    --shadow-sm: 0 2px 8px hsla(0, 0%, 0%, 0.2);
    --shadow-md: 0 8px 32px hsla(0, 0%, 0%, 0.3);
    --shadow-lg: 0 16px 64px hsla(0, 0%, 0%, 0.4);
    --shadow-glow: 0 0 40px var(--primary-glow);
    
    /* Transitions */
    --transition-fast: 0.15s ease;
    --transition-normal: 0.3s ease;
    --transition-slow: 0.5s ease;
    
    /* Border Radius */
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 20px;
    --radius-xl: 28px;
}

/* Reset & Base */
*, *::before, *::after {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
    scroll-padding-top: 80px;
}

body {
    font-family: var(--font-family);
    background-color: var(--bg-dark);
    color: var(--text-primary);
    line-height: 1.6;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

a {
    text-decoration: none;
    color: inherit;
    transition: var(--transition-fast);
}

ul {
    list-style: none;
}

img {
    max-width: 100%;
    height: auto;
}

/* Container */
.container {
    width: 100%;
    max-width: var(--container-max);
    margin: 0 auto;
    padding: 0 24px;
}

/* Typography */
h1, h2, h3, h4, h5, h6 {
    font-weight: 700;
    line-height: 1.2;
    letter-spacing: -0.02em;
}

.gradient-text {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 50%, var(--accent) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 28px;
    font-family: var(--font-family);
    font-size: 16px;
    font-weight: 600;
    border-radius: var(--radius-md);
    border: none;
    cursor: pointer;
    transition: all var(--transition-normal);
    text-decoration: none;
}

.btn svg {
    width: 20px;
    height: 20px;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    box-shadow: var(--shadow-glow), var(--shadow-sm);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 60px var(--primary-glow), var(--shadow-md);
}

.btn-secondary {
    background: var(--glass-bg);
    color: var(--text-primary);
    border: 1px solid var(--border);
    backdrop-filter: blur(10px);
}

.btn-secondary:hover {
    background: var(--bg-tertiary);
    border-color: var(--primary);
}

.btn-lg {
    padding: 18px 36px;
    font-size: 17px;
}

.btn-xl {
    padding: 22px 44px;
    font-size: 18px;
    border-radius: var(--radius-lg);
}

.btn-block {
    width: 100%;
}

/* ========================================
   Navigation
   ======================================== */
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    padding: 16px 0;
    transition: all var(--transition-normal);
}

.navbar.scrolled {
    background: hsla(240, 20%, 8%, 0.9);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--border);
    padding: 12px 0;
}

.nav-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 24px;
    font-weight: 800;
}

.logo-icon {
    font-size: 28px;
}

.logo-text {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 40px;
}

.nav-links a {
    font-size: 15px;
    font-weight: 500;
    color: var(--text-secondary);
    position: relative;
}

.nav-links a:hover {
    color: var(--text-primary);
}

.nav-links a::after {
    content: '';
    position: absolute;
    bottom: -6px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    transition: width var(--transition-normal);
}

.nav-links a:hover::after {
    width: 100%;
}

.nav-cta {
    padding: 12px 24px;
}

.hamburger {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
}

.hamburger span {
    width: 24px;
    height: 2px;
    background: var(--text-primary);
    transition: var(--transition-fast);
}

/* ========================================
   Hero Section
   ======================================== */
.hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 120px 0 80px;
    overflow: hidden;
}

.hero-bg {
    position: absolute;
    inset: 0;
    background: 
        radial-gradient(circle at 20% 50%, hsla(var(--primary-hue), 100%, 50%, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, hsla(280, 100%, 50%, 0.1) 0%, transparent 40%),
        radial-gradient(circle at 50% 80%, hsla(200, 100%, 50%, 0.08) 0%, transparent 40%);
    pointer-events: none;
}

.hero-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
    position: relative;
    z-index: 1;
}

.hero-content {
    max-width: 600px;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: var(--glass-bg);
    border: 1px solid var(--border);
    border-radius: 100px;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 24px;
    backdrop-filter: blur(10px);
}

.badge-icon {
    font-size: 16px;
}

.hero-title {
    font-size: clamp(40px, 5vw, 64px);
    font-weight: 800;
    margin-bottom: 24px;
    line-height: 1.1;
}

.hero-subtitle {
    font-size: 18px;
    color: var(--text-secondary);
    margin-bottom: 36px;
    line-height: 1.7;
}

.hero-cta {
    display: flex;
    gap: 16px;
    margin-bottom: 48px;
    flex-wrap: wrap;
}

.hero-stats {
    display: flex;
    align-items: center;
    gap: 32px;
}

.stat {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 32px;
    font-weight: 800;
    color: var(--primary-light);
}

.stat-label {
    font-size: 13px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-divider {
    width: 1px;
    height: 40px;
    background: var(--border);
}

/* Chat Preview */
.hero-visual {
    display: flex;
    justify-content: center;
}

.chat-preview {
    background: var(--glass-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 20px;
    width: 100%;
    max-width: 400px;
    backdrop-filter: blur(20px);
    box-shadow: var(--shadow-lg), var(--shadow-glow);
}

.chat-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 16px;
}

.chat-avatar {
    width: 44px;
    height: 44px;
    background: var(--bg-tertiary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.chat-info {
    flex: 1;
}

.chat-name {
    display: block;
    font-weight: 600;
    font-size: 15px;
}

.chat-status {
    font-size: 12px;
    color: var(--text-muted);
}

.chat-time {
    font-size: 12px;
    color: var(--text-muted);
}

.chat-messages {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 16px;
}

.message {
    max-width: 85%;
    padding: 12px 16px;
    border-radius: var(--radius-md);
    font-size: 14px;
    position: relative;
}

.message p {
    margin: 0;
}

.message-time {
    display: block;
    font-size: 10px;
    color: var(--text-muted);
    margin-top: 4px;
    text-align: right;
}

.message.incoming {
    background: var(--bg-tertiary);
    align-self: flex-start;
    border-bottom-left-radius: 4px;
}

.message.outgoing {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}

.message.outgoing .message-time {
    color: hsla(0, 0%, 100%, 0.7);
}

.ai-badge {
    font-size: 10px;
    font-weight: 600;
    margin-bottom: 6px;
    opacity: 0.8;
}

.typing-indicator {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 12px 16px;
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
    width: fit-content;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background: var(--text-muted);
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
    30% { opacity: 1; transform: translateY(-4px); }
}

.hero-wave {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 2;
}

.hero-wave svg {
    display: block;
    width: 100%;
    height: auto;
}

/* ========================================
   Section Styles
   ======================================== */
.section {
    padding: var(--section-padding);
    position: relative;
}

.section-header {
    text-align: center;
    max-width: 700px;
    margin: 0 auto 60px;
}

.section-badge {
    display: inline-block;
    padding: 8px 20px;
    background: var(--glass-bg);
    border: 1px solid var(--border);
    border-radius: 100px;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 20px;
}

.section-title {
    font-size: clamp(32px, 4vw, 48px);
    margin-bottom: 16px;
}

.section-subtitle {
    font-size: 18px;
    color: var(--text-secondary);
    line-height: 1.7;
}

/* ========================================
   Problem Section
   ======================================== */
.problem-section {
    background: var(--bg-primary);
}

.problems-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-bottom: 48px;
}

.problem-card {
    background: var(--glass-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 32px;
    text-align: center;
    transition: all var(--transition-normal);
    backdrop-filter: blur(10px);
}

.problem-card:hover {
    transform: translateY(-8px);
    border-color: var(--primary);
    box-shadow: var(--shadow-glow);
}

.problem-icon {
    font-size: 48px;
    margin-bottom: 20px;
}

.problem-card h3 {
    font-size: 18px;
    margin-bottom: 12px;
}

.problem-card p {
    font-size: 14px;
    color: var(--text-secondary);
    line-height: 1.6;
}

.problem-summary {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 24px 32px;
    background: linear-gradient(135deg, hsla(40, 100%, 60%, 0.1) 0%, hsla(0, 80%, 60%, 0.1) 100%);
    border: 1px solid hsla(40, 100%, 60%, 0.3);
    border-radius: var(--radius-lg);
    max-width: 800px;
    margin: 0 auto;
}

.summary-icon {
    font-size: 32px;
}

.problem-summary p {
    font-size: 16px;
    color: var(--text-secondary);
}

.problem-summary strong {
    color: var(--warning);
}

/* ========================================
   Solution Section
   ======================================== */
.solution-section {
    background: var(--bg-dark);
}

.solution-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    align-items: center;
}

.solution-text {
    max-width: 520px;
}

.solution-desc {
    font-size: 18px;
    color: var(--text-secondary);
    margin-bottom: 32px;
    line-height: 1.7;
}

.solution-benefits {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 36px;
}

.benefit {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
    color: var(--text-primary);
}

.benefit svg {
    width: 24px;
    height: 24px;
    color: var(--success);
}

.comparison-card {
    background: var(--glass-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 32px;
    display: flex;
    gap: 24px;
    backdrop-filter: blur(10px);
}

.comparison-side {
    flex: 1;
}

.comparison-header {
    margin-bottom: 20px;
}

.comparison-label {
    font-size: 16px;
    font-weight: 600;
}

.comparison-side ul {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.comparison-side li {
    font-size: 14px;
    color: var(--text-secondary);
    padding-left: 20px;
    position: relative;
}

.comparison-side.before li::before {
    content: 'âœ—';
    position: absolute;
    left: 0;
    color: var(--error);
}

.comparison-side.after li::before {
    content: 'âœ“';
    position: absolute;
    left: 0;
    color: var(--success);
}

.comparison-divider {
    display: flex;
    align-items: center;
    padding: 0 8px;
}

.comparison-divider span {
    background: var(--bg-tertiary);
    padding: 8px 12px;
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
}

/* ========================================
   How It Works Section
   ======================================== */
.how-section {
    background: var(--bg-primary);
}

.steps-container {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 0;
    max-width: 1000px;
    margin: 0 auto;
}

.step {
    flex: 1;
    text-align: center;
    position: relative;
    padding: 0 24px;
}

.step-number {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 800;
    margin: 0 auto 24px;
    box-shadow: var(--shadow-glow);
}

.step-content {
    background: var(--glass-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 32px 24px;
    transition: all var(--transition-normal);
}

.step:hover .step-content {
    border-color: var(--primary);
    transform: translateY(-4px);
}

.step-icon {
    width: 64px;
    height: 64px;
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.step-icon svg {
    width: 28px;
    height: 28px;
    color: var(--primary-light);
}

.step-content h3 {
    font-size: 18px;
    margin-bottom: 12px;
}

.step-content p {
    font-size: 14px;
    color: var(--text-secondary);
    line-height: 1.6;
}

.step-connector {
    width: 80px;
    height: 2px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    margin-top: 28px;
    flex-shrink: 0;
}

/* ========================================
   Features Section
   ======================================== */
.features-section {
    background: var(--bg-dark);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.feature-card {
    background: var(--glass-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 36px;
    transition: all var(--transition-normal);
    backdrop-filter: blur(10px);
}

.feature-card:hover {
    border-color: var(--primary);
    transform: translateY(-8px);
    box-shadow: var(--shadow-glow);
}

.feature-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, hsla(var(--primary-hue), 100%, 65%, 0.2) 0%, hsla(280, 100%, 65%, 0.2) 100%);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
}

.feature-icon svg {
    width: 28px;
    height: 28px;
    color: var(--primary-light);
}

.feature-card h3 {
    font-size: 20px;
    margin-bottom: 12px;
}

.feature-card p {
    font-size: 15px;
    color: var(--text-secondary);
    line-height: 1.6;
}

/* ========================================
   Audience Section
   ======================================== */
.audience-section {
    background: var(--bg-primary);
}

.audience-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-bottom: 48px;
}

.audience-card {
    background: var(--glass-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 32px;
    text-align: center;
    transition: all var(--transition-normal);
}

.audience-card:hover {
    border-color: var(--primary);
    transform: translateY(-4px);
}

.audience-icon {
    font-size: 48px;
    margin-bottom: 20px;
}

.audience-card h3 {
    font-size: 18px;
    margin-bottom: 12px;
}

.audience-card p {
    font-size: 14px;
    color: var(--text-secondary);
    line-height: 1.6;
}

.audience-cta {
    text-align: center;
}

.audience-cta p {
    font-size: 20px;
    color: var(--text-secondary);
}

.audience-cta strong {
    color: var(--text-primary);
}

/* ========================================
   Pricing Section
   ======================================== */
.pricing-section {
    background: var(--bg-dark);
}

.pricing-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    align-items: stretch;
    margin-bottom: 48px;
}

.pricing-card {
    background: var(--glass-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 40px;
    position: relative;
    transition: all var(--transition-normal);
    display: flex;
    flex-direction: column;
}

.pricing-card:hover {
    border-color: var(--primary);
    transform: translateY(-8px);
}

.pricing-card.popular {
    background: linear-gradient(180deg, hsla(var(--primary-hue), 100%, 65%, 0.1) 0%, var(--glass-bg) 50%);
    border-color: var(--primary);
    transform: scale(1.05);
    box-shadow: var(--shadow-glow);
}

.pricing-card.popular:hover {
    transform: scale(1.05) translateY(-8px);
}

.popular-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    padding: 6px 20px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}

.pricing-header {
    margin-bottom: 24px;
}

.pricing-header h3 {
    font-size: 28px;
    margin-bottom: 8px;
}

.pricing-desc {
    font-size: 14px;
    color: var(--text-muted);
}

.pricing-price {
    margin-bottom: 32px;
    padding-bottom: 32px;
    border-bottom: 1px solid var(--border);
}

.currency {
    font-size: 24px;
    font-weight: 600;
    vertical-align: top;
}

.amount {
    font-size: 56px;
    font-weight: 800;
    line-height: 1;
}

.period {
    font-size: 16px;
    color: var(--text-muted);
}

.pricing-features {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 32px;
    flex: 1;
}

.pricing-features li {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 15px;
    color: var(--text-secondary);
}

.pricing-features svg {
    width: 20px;
    height: 20px;
    color: var(--success);
    flex-shrink: 0;
}

.pricing-note {
    text-align: center;
    padding: 24px 32px;
    background: var(--glass-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    max-width: 600px;
    margin: 0 auto;
}

.pricing-note p {
    font-size: 16px;
    color: var(--text-secondary);
}

/* ========================================
   Testimonial Section
   ======================================== */
.testimonial-section {
    background: var(--bg-primary);
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.testimonial-card {
    background: var(--glass-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 32px;
    transition: all var(--transition-normal);
}

.testimonial-card:hover {
    border-color: var(--primary);
    transform: translateY(-4px);
}

.testimonial-content {
    margin-bottom: 24px;
}

.testimonial-content p {
    font-size: 16px;
    color: var(--text-secondary);
    line-height: 1.7;
    font-style: italic;
}

.testimonial-content strong {
    color: var(--text-primary);
    font-style: normal;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: 16px;
}

.author-avatar {
    width: 48px;
    height: 48px;
    background: var(--bg-tertiary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.author-name {
    display: block;
    font-weight: 600;
    font-size: 15px;
}

.author-role {
    font-size: 13px;
    color: var(--text-muted);
}

/* ========================================
   FAQ Section
   ======================================== */
.faq-section {
    background: var(--bg-dark);
}

.faq-container {
    max-width: 800px;
    margin: 0 auto;
}

.faq-item {
    background: var(--glass-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    margin-bottom: 16px;
    overflow: hidden;
    transition: all var(--transition-normal);
}

.faq-item:hover {
    border-color: var(--primary);
}

.faq-item.active {
    border-color: var(--primary);
}

.faq-question {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    font-family: var(--font-family);
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
    transition: var(--transition-fast);
}

.faq-question:hover {
    color: var(--primary-light);
}

.faq-question svg {
    width: 20px;
    height: 20px;
    color: var(--text-muted);
    transition: transform var(--transition-normal);
}

.faq-item.active .faq-question svg {
    transform: rotate(180deg);
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height var(--transition-normal);
}

.faq-item.active .faq-answer {
    max-height: 200px;
}

.faq-answer p {
    padding: 0 24px 24px;
    font-size: 15px;
    color: var(--text-secondary);
    line-height: 1.7;
}

/* ========================================
   CTA Section
   ======================================== */
.cta-section {
    background: linear-gradient(180deg, var(--bg-primary) 0%, var(--bg-dark) 100%);
    padding: 120px 0;
}

.cta-content {
    text-align: center;
    max-width: 700px;
    margin: 0 auto;
}

.cta-title {
    font-size: clamp(32px, 4vw, 48px);
    margin-bottom: 20px;
}

.cta-subtitle {
    font-size: 20px;
    color: var(--text-secondary);
    margin-bottom: 40px;
}

.cta-buttons {
    display: flex;
    justify-content: center;
    gap: 16px;
    margin-bottom: 24px;
}

.cta-note {
    font-size: 14px;
    color: var(--text-muted);
}

/* ========================================
   Footer
   ======================================== */
.footer {
    background: var(--bg-dark);
    border-top: 1px solid var(--border);
    padding: 80px 0 40px;
}

.footer-content {
    display: grid;
    grid-template-columns: 1.5fr 2fr;
    gap: 80px;
    margin-bottom: 60px;
}

.footer-tagline {
    font-size: 15px;
    color: var(--text-secondary);
    margin-top: 20px;
    line-height: 1.7;
}

.footer-links {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 40px;
}

.footer-column h4 {
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-primary);
    margin-bottom: 20px;
}

.footer-column ul {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.footer-column a {
    font-size: 14px;
    color: var(--text-muted);
}

.footer-column a:hover {
    color: var(--text-primary);
}

.footer-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 40px;
    border-top: 1px solid var(--border);
}

.footer-bottom p {
    font-size: 14px;
    color: var(--text-muted);
}

.footer-legal {
    display: flex;
    gap: 24px;
}

.footer-legal a {
    font-size: 14px;
    color: var(--text-muted);
}

.footer-legal a:hover {
    color: var(--text-primary);
}

/* ========================================
   Animations
   ======================================== */
.animate-fade-in {
    opacity: 0;
    animation: fadeIn 0.8s ease forwards;
}

.animate-fade-in-up {
    opacity: 0;
    transform: translateY(30px);
    animation: fadeInUp 0.8s ease forwards;
}

.delay-1 { animation-delay: 0.2s; }
.delay-2 { animation-delay: 0.4s; }
.delay-3 { animation-delay: 0.6s; }

@keyframes fadeIn {
    to { opacity: 1; }
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-on-scroll {
    opacity: 0;
    transform: translateY(40px);
    transition: all 0.8s ease;
}

.animate-on-scroll.visible {
    opacity: 1;
    transform: translateY(0);
}

/* ========================================
   Responsive Design
   ======================================== */
@media (max-width: 1024px) {
    .hero-container {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .hero-content {
        max-width: 100%;
    }
    
    .hero-cta {
        justify-content: center;
    }
    
    .hero-stats {
        justify-content: center;
    }
    
    .hero-visual {
        order: -1;
    }
    
    .chat-preview {
        max-width: 360px;
    }
    
    .problems-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .solution-content {
        grid-template-columns: 1fr;
        gap: 48px;
    }
    
    .solution-text {
        max-width: 100%;
        text-align: center;
    }
    
    .solution-benefits {
        align-items: center;
    }
    
    .steps-container {
        flex-direction: column;
        gap: 24px;
    }
    
    .step {
        padding: 0;
    }
    
    .step-connector {
        width: 2px;
        height: 40px;
        margin: 0 auto;
    }
    
    .features-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .audience-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .pricing-grid {
        grid-template-columns: 1fr;
        max-width: 400px;
        margin: 0 auto 48px;
    }
    
    .pricing-card.popular {
        transform: none;
    }
    
    .pricing-card.popular:hover {
        transform: translateY(-8px);
    }
    
    .testimonials-grid {
        grid-template-columns: 1fr;
        max-width: 500px;
        margin: 0 auto;
    }
    
    .footer-content {
        grid-template-columns: 1fr;
        gap: 48px;
        text-align: center;
    }
    
    .footer-links {
        justify-items: center;
    }
}

@media (max-width: 768px) {
    :root {
        --section-padding: 80px 0;
    }
    
    .nav-links,
    .nav-cta {
        display: none;
    }
    
    .hamburger {
        display: flex;
    }
    
    .nav-links.active {
        display: flex;
        flex-direction: column;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: hsla(240, 20%, 8%, 0.98);
        backdrop-filter: blur(20px);
        padding: 24px;
        gap: 20px;
        border-bottom: 1px solid var(--border);
    }
    
    .hero {
        min-height: auto;
        padding: 100px 0 60px;
    }
    
    .hero-title {
        font-size: clamp(32px, 8vw, 48px);
    }
    
    .hero-stats {
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .stat-divider {
        display: none;
    }
    
    .problems-grid {
        grid-template-columns: 1fr;
    }
    
    .problem-summary {
        flex-direction: column;
        text-align: center;
    }
    
    .comparison-card {
        flex-direction: column;
        gap: 16px;
    }
    
    .comparison-divider {
        padding: 8px 0;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .audience-grid {
        grid-template-columns: 1fr;
    }
    
    .footer-links {
        grid-template-columns: 1fr;
        gap: 32px;
    }
    
    .footer-bottom {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
    
    .cta-buttons {
        flex-direction: column;
    }
    
    .btn-xl {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0 16px;
    }
    
    .hero-badge {
        font-size: 12px;
    }
    
    .btn-lg {
        padding: 16px 24px;
        font-size: 15px;
    }
    
    .pricing-card {
        padding: 32px 24px;
    }
    
    .amount {
        font-size: 48px;
    }
}

