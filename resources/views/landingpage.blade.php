<!doctype html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta
            name="description"
            content="ReplyAI - AI auto-reply untuk tim digital marketing dan bisnis berbasis chat. Balas chat WhatsApp & Instagram 24/7 secara otomatis."
        />
        <meta
            name="keywords"
            content="AI chatbot, auto reply WhatsApp, auto reply Instagram, digital marketing, lead generation"
        />
        <title>ReplyAI — Chat Jalan Terus, Admin Nggak Harus Lembur</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet"
        />

        <!-- Lucide Icons -->
        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

        <link rel="stylesheet" href="{{ asset('landingpage/style.css') }}" />
        <link rel="stylesheet" href="{{ asset('landingpage/partners.css') }}" />
    </head>
    
    <!-- ReplyAI Chat Widget -->
    <script src="{{ asset('widget/replyai-widget.js') }}" async></script>

    <body>
        <!-- Navigation -->
        <nav class="navbar" id="navbar">
            <div class="container nav-container">
                <a href="#" class="logo">
                    <span class="logo-icon">💬</span>
                    <span class="logo-text">ReplyAI</span>
                </a>
                <ul class="nav-links" id="navLinks">
                    <li><a href="#masalah">Masalah</a></li>
                    <li><a href="#solusi">Solusi</a></li>
                    <li><a href="#cara-kerja">Cara Kerja</a></li>
                    <li><a href="#fitur">Fitur</a></li>
                    <li><a href="#harga">Harga</a></li>
                    <li>
                        <a href="/login" style="color: var(--primary)">Login</a>
                    </li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn-primary nav-cta"
                    >Minta Demo</a
                >
                <button class="hamburger" id="hamburger" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero" id="hero">
            <div class="hero-bg"></div>
            <div class="container hero-container">
                <div class="hero-content">
                    <div class="hero-badge animate-fade-in">
                        <span class="badge-icon">🚀</span>
                        <span>AI Auto-Reply untuk Bisnis Modern</span>
                    </div>
                    <h1 class="hero-title animate-fade-in-up">
                        Chat Jalan Terus,<br />
                        <span class="gradient-text"
                            >Admin Nggak Harus Lembur</span
                        >
                    </h1>
                    <p class="hero-subtitle animate-fade-in-up delay-1">
                        ReplyAI menjaga semua chat WhatsApp & Instagram tetap
                        dibalas instan 24 jam, supaya lead dari iklan nggak
                        kebuang cuma karena telat respon.
                    </p>
                    <div class="hero-cta animate-fade-in-up delay-2">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                            <span>Minta Demo Gratis</span>
                            <i data-lucide="arrow-right"></i>
                        </a>
                        <a href="{{ route('register', ['trial' => 1]) }}" class="btn btn-secondary btn-lg">
                            <span>Coba 7 Hari</span>
                        </a>
                    </div>
                    <div class="hero-stats animate-fade-in-up delay-3">
                        <div class="stat">
                            <span class="stat-value">90%</span>
                            <span class="stat-label">Response Time ↓</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat">
                            <span class="stat-value">24/7</span>
                            <span class="stat-label">Auto Reply</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat">
                            <span class="stat-value">0</span>
                            <span class="stat-label">Chat Missed</span>
                        </div>
                    </div>
                </div>
                <div class="hero-visual animate-fade-in delay-2">
                    <div class="chat-preview">
                        <div class="chat-header">
                            <div class="chat-avatar">👤</div>
                            <div class="chat-info">
                                <span class="chat-name">Calon Customer</span>
                                <span class="chat-status">WhatsApp</span>
                            </div>
                            <span class="chat-time">23:45</span>
                        </div>
                        <div class="chat-messages">
                            <div class="message incoming">
                                <p>Halo, mau tanya soal produknya dong</p>
                                <span class="message-time">23:45</span>
                            </div>
                            <div class="message outgoing ai-reply">
                                <div class="ai-badge">🤖 ReplyAI</div>
                                <p>
                                    Hai! Terima kasih sudah menghubungi kami 😊
                                    Ada yang bisa kami bantu?
                                </p>
                                <span class="message-time">23:45</span>
                            </div>
                            <div class="message incoming">
                                <p>Harganya berapa ya?</p>
                                <span class="message-time">23:46</span>
                            </div>
                            <div class="message outgoing ai-reply">
                                <div class="ai-badge">🤖 ReplyAI</div>
                                <p>
                                    Untuk harga mulai dari Rp 500.000/bulan. Mau
                                    saya jelaskan detail paketnya?
                                </p>
                                <span class="message-time">23:46</span>
                            </div>
                        </div>
                        <div class="typing-indicator">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero-wave">
                <svg
                    viewBox="0 0 1440 120"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z"
                        fill="var(--bg-primary)"
                    />
                </svg>
            </div>
        </section>

        <!-- Integration Logos -->
        <section class="integrations-section">
            <div class="container">
                <p class="integrations-label">
                    Terintegrasi dengan platform favorit Anda
                </p>
                <div class="integrations-logos">
                    <div class="integration-logo">
                        <span class="integration-icon">💬</span>
                        <span class="integration-name">WhatsApp</span>
                    </div>
                    <div class="integration-logo">
                        <span class="integration-icon">📸</span>
                        <span class="integration-name">Instagram</span>
                    </div>
                    <div class="integration-logo">
                        <span class="integration-icon">🌐</span>
                        <span class="integration-name">Web Chat</span>
                    </div>
                    <div class="integration-logo coming-soon">
                        <span class="integration-icon">📱</span>
                        <span class="integration-name">Telegram</span>
                        <span class="coming-badge">Soon</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Problem Section -->
        <section class="section problem-section" id="masalah">
            <div class="container">
                <div class="section-header">
                    <span class="section-badge">😰 Masalah Nyata</span>
                    <h2 class="section-title">
                        Kenapa Banyak Lead
                        <span class="gradient-text">Mati di Chat?</span>
                    </h2>
                    <p class="section-subtitle">
                        Setiap chat yang nggak kebalas cepat, itu bukan cuma
                        pesan yang hilang — tapi potensi revenue.
                    </p>
                </div>
                <div class="problems-grid">
                    <div class="problem-card animate-on-scroll">
                        <div class="problem-icon">🌙</div>
                        <h3>Chat Masuk di Luar Jam Kerja</h3>
                        <p>
                            Lead datang jam 11 malam, admin sudah tidur. Besok
                            pagi? Lead sudah chat kompetitor.
                        </p>
                    </div>
                    <div class="problem-card animate-on-scroll">
                        <div class="problem-icon">😵</div>
                        <h3>Admin Pegang Banyak Tugas</h3>
                        <p>
                            Balas chat, input data, follow up — semua dikerjakan
                            satu orang. Response time? Amburadul.
                        </p>
                    </div>
                    <div class="problem-card animate-on-scroll">
                        <div class="problem-icon">🔄</div>
                        <h3>Pertanyaan Itu-itu Lagi</h3>
                        <p>
                            "Harganya berapa?" "Bisa COD?" "Pengiriman berapa
                            hari?" — Makan waktu, tapi harus dijawab.
                        </p>
                    </div>
                    <div class="problem-card animate-on-scroll">
                        <div class="problem-icon">💸</div>
                        <h3>Telat Balas = Iklan Boncos</h3>
                        <p>
                            Sudah bayar iklan mahal, lead masuk, tapi nggak
                            dibalas cepat. Hasilnya? Budget hangus.
                        </p>
                    </div>
                </div>
                <div class="problem-summary animate-on-scroll">
                    <div class="summary-icon">⚠️</div>
                    <p>
                        <strong>Fakta:</strong> 78% customer membeli dari bisnis
                        yang merespon pertama. Setiap menit keterlambatan =
                        peluang hilang.
                    </p>
                </div>
            </div>
        </section>

        <!-- Solution Section -->
        <section class="section solution-section" id="solusi">
            <div class="container">
                <div class="solution-content">
                    <div class="solution-text animate-on-scroll">
                        <span class="section-badge">✨ Solusi</span>
                        <h2 class="section-title">
                            ReplyAI Hadir untuk
                            <span class="gradient-text"
                                >Nutup Kebocoran Itu</span
                            >
                        </h2>
                        <p class="solution-desc">
                            ReplyAI otomatis membalas pertanyaan pelanggan
                            dengan jawaban yang relevan dan ramah, lalu
                            menyerahkan ke admin saat sudah waktunya closing.
                        </p>
                        <div class="solution-benefits">
                            <div class="benefit">
                                <i data-lucide="check-circle-2"></i>
                                <span>Chat tetap hidup 24 jam</span>
                            </div>
                            <div class="benefit">
                                <i data-lucide="check-circle-2"></i>
                                <span
                                    >Admin fokus ke closing, bukan balas
                                    FAQ</span
                                >
                            </div>
                            <div class="benefit">
                                <i data-lucide="check-circle-2"></i>
                                <span
                                    >Lead lebih siap saat ditangani
                                    manusia</span
                                >
                            </div>
                        </div>
                        <a href="/register" class="btn btn-primary btn-lg">
                            <span>Lihat Demo</span>
                            <i data-lucide="play-circle"></i>
                        </a>
                    </div>
                    <div class="solution-visual animate-on-scroll">
                        <div class="comparison-card">
                            <div class="comparison-side before">
                                <div class="comparison-header">
                                    <span class="comparison-label"
                                        >❌ Tanpa ReplyAI</span
                                    >
                                </div>
                                <ul>
                                    <li>Chat diabaikan berjam-jam</li>
                                    <li>Lead kabur ke kompetitor</li>
                                    <li>Admin kewalahan</li>
                                    <li>Iklan boncos</li>
                                </ul>
                            </div>
                            <div class="comparison-divider">
                                <span>VS</span>
                            </div>
                            <div class="comparison-side after">
                                <div class="comparison-header">
                                    <span class="comparison-label"
                                        >✅ Dengan ReplyAI</span
                                    >
                                </div>
                                <ul>
                                    <li>Chat dibalas instan 24/7</li>
                                    <li>Lead hangat & siap closing</li>
                                    <li>Admin fokus high-value tasks</li>
                                    <li>ROI iklan meningkat</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="section how-section" id="cara-kerja">
            <div class="container">
                <div class="section-header">
                    <span class="section-badge">⚙️ Cara Kerja</span>
                    <h2 class="section-title">
                        Setup dalam
                        <span class="gradient-text">3 Langkah Mudah</span>
                    </h2>
                    <p class="section-subtitle">
                        Tidak perlu coding. Tidak perlu skill teknis. Cukup 15
                        menit untuk mulai.
                    </p>
                </div>
                <div class="steps-container">
                    <div class="step animate-on-scroll">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <div class="step-icon">
                                <i data-lucide="link"></i>
                            </div>
                            <h3>Hubungkan WhatsApp & Instagram</h3>
                            <p>
                                Sambungkan akun chat bisnis kamu ke ReplyAI.
                                Proses aman dan terenkripsi.
                            </p>
                        </div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step animate-on-scroll">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <div class="step-icon">
                                <i data-lucide="book-open"></i>
                            </div>
                            <h3>Isi Knowledge & Atur Alur</h3>
                            <p>
                                Masukkan informasi bisnis, produk, dan jawaban
                                yang boleh dijawab bot.
                            </p>
                        </div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step animate-on-scroll">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <div class="step-icon">
                                <i data-lucide="bot"></i>
                            </div>
                            <h3>ReplyAI Jaga Chat 24/7</h3>
                            <p>
                                Bot menjawab otomatis, admin bisa takeover kapan
                                saja dengan satu klik.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="section features-section" id="fitur">
            <div class="container">
                <div class="section-header">
                    <span class="section-badge">🎯 Fitur Utama</span>
                    <h2 class="section-title">
                        Kenapa ReplyAI
                        <span class="gradient-text">Beda dari yang Lain?</span>
                    </h2>
                    <p class="section-subtitle">
                        Bukan chatbot kaku berbasis keyword. ReplyAI dibangun
                        dari masalah tim marketing nyata.
                    </p>
                </div>
                <div class="features-grid">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i data-lucide="zap"></i>
                        </div>
                        <h3>Auto Reply AI 24/7</h3>
                        <p>
                            AI yang memahami konteks, bukan sekadar keyword
                            matching. Jawaban natural seperti manusia.
                        </p>
                    </div>
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i data-lucide="inbox"></i>
                        </div>
                        <h3>Unified Inbox WA & IG</h3>
                        <p>
                            Semua chat dari WhatsApp dan Instagram dalam satu
                            dashboard. Tidak perlu buka banyak aplikasi.
                        </p>
                    </div>
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i data-lucide="hand"></i>
                        </div>
                        <h3>Takeover & Handback</h3>
                        <p>
                            Admin bisa ambil alih chat kapan saja, lalu
                            kembalikan ke bot setelah selesai. Kontrol penuh.
                        </p>
                    </div>
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i data-lucide="database"></i>
                        </div>
                        <h3>Knowledge Base Custom</h3>
                        <p>
                            Bot hanya menjawab dari informasi yang kamu setujui.
                            Tidak ada jawaban ngawur.
                        </p>
                    </div>
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i data-lucide="bar-chart-3"></i>
                        </div>
                        <h3>Analytics Performa Chat</h3>
                        <p>
                            Pantau response time, chat handled, dan performa tim
                            dalam dashboard yang mudah dibaca.
                        </p>
                    </div>
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i data-lucide="shield-check"></i>
                        </div>
                        <h3>Aman & Terenkripsi</h3>
                        <p>
                            Data chat terenkripsi end-to-end. Privasi pelanggan
                            terjaga sepenuhnya.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Target Audience Section -->
        <section class="section audience-section">
            <div class="container">
                <div class="section-header">
                    <span class="section-badge">👥 Untuk Siapa?</span>
                    <h2 class="section-title">
                        Siapa yang
                        <span class="gradient-text">Paling Cocok?</span>
                    </h2>
                </div>
                <div class="audience-grid">
                    <div class="audience-card animate-on-scroll">
                        <div class="audience-icon">📱</div>
                        <h3>Digital Marketing Agency</h3>
                        <p>
                            Handle banyak client dengan tim terbatas? ReplyAI
                            bantu jawab chat semua akun client 24/7.
                        </p>
                    </div>
                    <div class="audience-card animate-on-scroll">
                        <div class="audience-icon">🏢</div>
                        <h3>Tim Marketing Internal</h3>
                        <p>
                            Fokus ke strategi dan campaign, biar ReplyAI yang
                            handle pertanyaan berulang dari leads.
                        </p>
                    </div>
                    <div class="audience-card animate-on-scroll">
                        <div class="audience-icon">🏥</div>
                        <h3>Klinik, RS & Layanan Kesehatan</h3>
                        <p>
                            Pasien butuh respons cepat. ReplyAI jaga chat tetap
                            terlayani meski admin terbatas.
                        </p>
                    </div>
                    <div class="audience-card animate-on-scroll">
                        <div class="audience-icon">🛍️</div>
                        <h3>UMKM dengan Chat Tinggi</h3>
                        <p>
                            Bisnis kamu hidup dari chat? ReplyAI pastikan tidak
                            ada lead yang terlewat.
                        </p>
                    </div>
                </div>
                <div class="audience-cta animate-on-scroll">
                    <p>
                        Kalau bisnis kamu hidup dari chat,
                        <strong>ReplyAI dibuat buat kamu.</strong>
                    </p>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section class="section pricing-section" id="harga">
            <div class="container">
                <div class="section-header">
                    <span class="section-badge">🏷️ Harga</span>
                    <h2 class="section-title">
                        Investasi yang
                        <span class="gradient-text">Terjangkau</span>
                    </h2>
                    <p class="section-subtitle">
                        Pilih paket yang sesuai dengan skala bisnis Anda. Upgrade kapan saja.
                    </p>
                </div>
                <div class="pricing-grid">
                    @foreach($plans as $plan)
                        @php $isPro = (bool) $plan->is_popular; @endphp
                        <div class="pricing-card {{ $isPro ? 'popular' : '' }} animate-on-scroll">
                            @if($isPro)
                                <div class="popular-badge">Paling Laris</div>
                            @endif
                            <div class="pricing-header">
                                <h3>{{ $plan->name }}</h3>
                                <p class="pricing-desc">{{ $plan->description }}</p>
                            </div>
                            <div class="pricing-price">
                                <div class="price-container">
                                    @php
                                        $discountPercent = 0;
                                        if ($plan->price_monthly_original > $plan->price_monthly && $plan->price_monthly_original > 0) {
                                            $discountPercent = round((($plan->price_monthly_original - $plan->price_monthly) / $plan->price_monthly_original) * 100);
                                        }
                                    @endphp

                                    <div class="original-price-row">
                                        @if($plan->price_monthly_original_display)
                                            <span class="original-price">{{ $plan->price_monthly_original_display }}</span>
                                        @elseif($plan->price_monthly_original > $plan->price_monthly)
                                            <span class="original-price">Rp {{ number_format($plan->price_monthly_original, 0, ',', '.') }}</span>
                                        @endif

                                        @if($discountPercent > 0)
                                            <span class="save-badge">Hemat {{ $discountPercent }}%</span>
                                        @endif
                                    </div>
                                    
                                    <div class="price-main">
                                        @if($plan->price_monthly_display)
                                            <span class="amount">{{ $plan->price_monthly_display }}</span>
                                        @else
                                            <span class="currency">Rp</span>
                                            <span class="amount">{{ $plan->price_monthly > 0 ? number_format($plan->price_monthly, 0, ',', '.') : $plan->name }}</span>
                                        @endif

                                        @if($plan->price_monthly > 0)
                                            <span class="period">/bulan</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <ul class="pricing-features">
                                @if(!empty($plan->features_list))
                                    @foreach($plan->features_list as $feature)
                                        <li>
                                            <i data-lucide="check"></i> {!! $feature !!}
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                            
                            @if($plan->slug === 'enterprise')
                                <a href="https://wa.me/6285168842886" target="_blank" class="btn btn-secondary btn-block">Hubungi Tim Sales</a>
                            @elseif($plan->slug === 'custom')
                                <a href="https://wa.me/6285168842886" target="_blank" class="btn btn-secondary btn-block">Konsultasi Custom</a>
                            @else
                                <a href="{{ route('register', ['plan' => $plan->slug]) }}" 
                                   class="btn {{ $isPro ? 'btn-primary btn-pro' : 'btn-secondary' }} btn-block">
                                    Mulai Paket {{ $plan->name }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="pricing-note animate-on-scroll">
                    <p>
                        🏢 <strong>Butuh skala lebih besar?</strong> Hubungi
                        kami untuk penawaran khusus yang disesuaikan dengan
                        infrastruktur bisnis Anda.
                    </p>
                </div>
            </div>
        </section>

        <!-- Testimonial Section -->
        <section class="section testimonial-section">
            <div class="container">
                <div class="section-header">
                    <span class="section-badge">💬 Testimoni</span>
                    <h2 class="section-title">
                        Apa Kata
                        <span class="gradient-text">Pengguna ReplyAI?</span>
                    </h2>
                </div>
                <div class="testimonials-grid">
                    <div class="testimonial-card animate-on-scroll">
                        <div class="testimonial-content">
                            <p>
                                "Sejak pakai ReplyAI, chat malam tetap kebalas
                                dan admin nggak kewalahan.
                                <strong
                                    >Closing lebih enak karena lead sudah
                                    hangat.</strong
                                >"
                            </p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">👨‍💼</div>
                            <div class="author-info">
                                <span class="author-name">Andi Pratama</span>
                                <span class="author-role"
                                    >Digital Marketing Manager</span
                                >
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card animate-on-scroll">
                        <div class="testimonial-content">
                            <p>
                                "Response time turun dari 2 jam jadi
                                <strong>hitungan detik</strong>. Customer lebih
                                puas, conversion rate naik signifikan."
                            </p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">👩‍💼</div>
                            <div class="author-info">
                                <span class="author-name">Sari Dewi</span>
                                <span class="author-role">Agency Owner</span>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card animate-on-scroll">
                        <div class="testimonial-content">
                            <p>
                                "Klinik kami jadi bisa
                                <strong>handle lebih banyak pasien</strong>
                                tanpa nambah admin. ReplyAI benar-benar
                                membantu."
                            </p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">👨‍⚕️</div>
                            <div class="author-info">
                                <span class="author-name"
                                    >Dr. Budi Santoso</span
                                >
                                <span class="author-role">Direktur Klinik</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="section faq-section">
            <div class="container">
                <div class="section-header">
                    <span class="section-badge">❓ FAQ</span>
                    <h2 class="section-title">
                        Pertanyaan yang
                        <span class="gradient-text">Sering Ditanyakan</span>
                    </h2>
                </div>
                <div class="faq-container">
                    <div class="faq-item animate-on-scroll">
                        <button class="faq-question">
                            <span
                                >Kami sudah punya admin, apakah masih butuh
                                ReplyAI?</span
                            >
                            <i data-lucide="chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>
                                ReplyAI bukan menggantikan admin, tapi menutup
                                jam-jam rawan saat admin nggak bisa balas.
                                Malam, weekend, atau saat volume chat tinggi —
                                ReplyAI yang handle.
                            </p>
                        </div>
                    </div>
                    <div class="faq-item animate-on-scroll">
                        <button class="faq-question">
                            <span
                                >Takut jawabannya salah atau tidak sesuai</span
                            >
                            <i data-lucide="chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>
                                Bot hanya menjawab dari knowledge base yang Anda
                                setujui. Tidak ada jawaban ngawur. Dan admin
                                bisa takeover kapan saja jika dibutuhkan.
                            </p>
                        </div>
                    </div>
                    <div class="faq-item animate-on-scroll">
                        <button class="faq-question">
                            <span
                                >Chatbot itu terasa kaku, apakah ReplyAI
                                juga?</span
                            >
                            <i data-lucide="chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>
                                ReplyAI menggunakan AI dengan konteks bisnis
                                Anda, bukan keyword kaku. Jawabannya natural dan
                                bisa disesuaikan dengan tone brand Anda.
                            </p>
                        </div>
                    </div>
                    <div class="faq-item animate-on-scroll">
                        <button class="faq-question">
                            <span
                                >Bagaimana dengan keamanan data chat
                                pelanggan?</span
                            >
                            <i data-lucide="chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p>
                                Semua data terenkripsi end-to-end. Kami tidak
                                menyimpan atau menggunakan data chat untuk
                                keperluan lain. Privasi pelanggan Anda terjaga
                                sepenuhnya.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA Section -->
        <section class="section cta-section" id="demo">
            <div class="container">
                <div class="cta-content animate-on-scroll">
                    <h2 class="cta-title">
                        Berhenti Buang Lead Cuma Karena
                        <span class="gradient-text">Chat Telat Dibalas</span>
                    </h2>
                    <p class="cta-subtitle">
                        Mulai sekarang, biarkan ReplyAI yang jaga chat bisnis
                        kamu 24/7.
                    </p>
                    <div class="cta-buttons">
                        <a
                            href="https://wa.me/6285168842886?text=Halo,%20saya%20mau%20demo%20ReplyAI"
                            class="btn btn-primary btn-xl"
                            target="_blank"
                        >
                            <span>Minta Demo Gratis Sekarang</span>
                            <i data-lucide="message-circle"></i>
                        </a>
                    </div>
                    <p class="cta-note">
                        💬 Demo gratis via WhatsApp. Tidak ada komitmen.
                    </p>
                </div>
            </div>
        </section>

        <!-- Official Channel Partners -->
        <section class="partners-section" id="partners">
            <div class="container">
                <div class="section-header text-center">
                    <span class="badge">🤝 Trusted Partners</span>
                    <h2>Official Channel Partners</h2>
                    <p>Bermitra dengan platform terkemuka untuk memberikan layanan terbaik</p>
                </div>
                
                <div class="partners-row">
                    <!-- Partner 1 -->
                    <div class="partner-item">
                        <div class="partner-box">
                            <div class="logo-wrap">
                                <img src="{{ asset('images/partners/inovas.png') }}" alt="Inovas">
                            </div>
                            <span class="partner-name">Inovas</span>
                            <span class="partner-desc">Marketing Management</span>
                        </div>
                    </div>
                    
                    <!-- Partner 2 -->
                    <div class="partner-item">
                        <a href="https://wa.me/628819173276" target="_blank" class="partner-box wa-clickable">
                            <div class="logo-wrap">
                                <img src="{{ asset('images/partners/inds.png') }}" alt="Inds.id">
                            </div>
                            <span class="partner-name">Inds.id</span>
                            <span class="partner-desc">Digital Solutions</span>
                            <div class="wa-badge">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                <span>Chat WhatsApp</span>
                            </div>
                        </a>
                    </div>
                    
                    <!-- CTA Partner -->
                    <div class="partner-item partner-cta">
                        <div class="partner-box">
                            <div class="logo-wrap cta-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </div>
                            <span class="partner-name">Jadi Partner?</span>
                            <span class="partner-desc">Hubungi Kami</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-brand">
                        <a href="#" class="logo">
                            <span class="logo-icon">💬</span>
                            <span class="logo-text">ReplyAI</span>
                        </a>
                        <p class="footer-tagline">
                            ReplyAI bukan chatbot lucu.<br />ReplyAI adalah alat
                            penjaga performa chat bisnis kamu.
                        </p>
                    </div>
                    <div class="footer-links">
                        <div class="footer-column">
                            <h4>Produk</h4>
                            <ul>
                                <li><a href="#fitur">Fitur</a></li>
                                <li><a href="#harga">Harga</a></li>
                                <li><a href="#demo">Demo</a></li>
                            </ul>
                        </div>
                        <div class="footer-column">
                            <h4>Company</h4>
                            <ul>
                                <li><a href="#">Tentang Kami</a></li>
                                <li><a href="#">Blog</a></li>
                                <li><a href="#">Karir</a></li>
                            </ul>
                        </div>
                        <div class="footer-column">
                            <h4>Support</h4>
                            <ul>
                                <li><a href="#">Bantuan</a></li>
                                <li><a href="#">Dokumentasi</a></li>
                                <li><a href="#">Kontak</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2026 ReplyAI. All rights reserved.</p>
                    <div class="footer-legal">
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                    </div>
                </div>
            </div>
        </footer>

        <script src="{{ asset('landingpage/script.js') }}"></script>
    </body>
</html>
