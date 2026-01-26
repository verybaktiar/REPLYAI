<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Documentation - REPLYAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "whatsapp": "#25D366",
                        "background-dark": "#111722",
                        "surface-dark": "#192233",
                        "border-dark": "#324467",
                        "text-secondary": "#92a4c9",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .prose h2 { margin-top: 2rem; margin-bottom: 1rem; font-size: 1.5rem; font-weight: 700; color: white; }
        .prose h3 { margin-top: 1.5rem; margin-bottom: 0.75rem; font-size: 1.25rem; font-weight: 600; color: #e2e8f0; }
        .prose p { margin-bottom: 1rem; line-height: 1.7; color: #cbd5e1; }
        .prose ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1rem; color: #cbd5e1; }
        .prose li { margin-bottom: 0.5rem; }
        .prose strong { color: white; font-weight: 600; }
        .prose code { background-color: rgba(255,255,255,0.1); padding: 0.2rem 0.4rem; rounded: 4px; font-family: monospace; font-size: 0.9em; color: #92a4c9; }
        .nav-link.active { background-color: rgba(19, 91, 236, 0.1); color: #60a5fa; border-left: 3px solid #135bec; }
    </style>
</head>
<body class="bg-background-dark font-display text-white overflow-hidden h-screen flex" x-data="{ activeSection: 'intro' }">
    
    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        
        <!-- Header -->
        <div class="h-16 border-b border-border-dark flex items-center px-8 bg-surface-dark flex-shrink-0">
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">menu_book</span>
                    Panduan Penggunaan
                </h1>
                @include('components.page-help', [
                    'title' => 'Panduan Penggunaan',
                    'description' => 'Dokumentasi lengkap cara menggunakan ReplyAI.',
                    'tips' => [
                        'Klik menu di sebelah kiri untuk navigasi',
                        'Baca dari Introduction untuk pemula',
                        'Cari topik spesifik yang Anda butuhkan',
                        'Dokumentasi selalu di-update'
                    ]
                ])
            </div>
        </div>

        <div class="flex flex-1 overflow-hidden">
            <!-- Navigation -->
            <div class="w-64 bg-surface-dark border-r border-border-dark overflow-y-auto p-4 flex-shrink-0">
                <h3 class="text-xs font-bold text-text-secondary uppercase mb-4 px-2">Table of Contents</h3>
                <nav class="space-y-1">
                    <button @click="activeSection = 'intro'" :class="{ 'active': activeSection === 'intro' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        Introduction
                    </button>
                    <button @click="activeSection = 'dashboard'" :class="{ 'active': activeSection === 'dashboard' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        Dashboard
                    </button>
                    <button @click="activeSection = 'live-inbox'" :class="{ 'active': activeSection === 'live-inbox' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        Live Inbox
                    </button>
                    <button @click="activeSection = 'bot-rules'" :class="{ 'active': activeSection === 'bot-rules' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        Bot Management (Rules)
                    </button>
                    <button @click="activeSection = 'kb'" :class="{ 'active': activeSection === 'kb' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        Knowledge Base (KB)
                    </button>
                    <button @click="activeSection = 'quick-replies'" :class="{ 'active': activeSection === 'quick-replies' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        Quick Replies
                    </button>
                    <button @click="activeSection = 'simulator'" :class="{ 'active': activeSection === 'simulator' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        Bot Simulator
                    </button>
                    <button @click="activeSection = 'sequences'" :class="{ 'active': activeSection === 'sequences' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        Sequences (Drip Campaign)
                    </button>
                    <div class="pt-2 pb-1 px-2">
                        <span class="text-[10px] font-bold text-text-secondary uppercase">WhatsApp Integration</span>
                    </div>
                    <button @click="activeSection = 'wa-connect'" :class="{ 'active': activeSection === 'wa-connect' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        Connection & Settings
                    </button>
                    <button @click="activeSection = 'wa-inbox'" :class="{ 'active': activeSection === 'wa-inbox' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        Inbox Feature
                    </button>
                    <button @click="activeSection = 'wa-broadcast'" :class="{ 'active': activeSection === 'wa-broadcast' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        Broadcast System
                    </button>
                    <button @click="activeSection = 'wa-analytics'" :class="{ 'active': activeSection === 'wa-analytics' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        Analytics Dashboard
                    </button>
                    <div class="pt-2 pb-1 px-2">
                        <span class="text-[10px] font-bold text-text-secondary uppercase">Management</span>
                    </div>
                    <button @click="activeSection = 'crm'" :class="{ 'active': activeSection === 'crm' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        CRM / Contacts
                    </button>
                    <button @click="activeSection = 'settings'" :class="{ 'active': activeSection === 'settings' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        App Settings
                    </button>

                    <div class="pt-2 pb-1 px-2">
                        <span class="text-[10px] font-bold text-text-secondary uppercase">System</span>
                    </div>
                    <button @click="activeSection = 'logs'" :class="{ 'active': activeSection === 'logs' }" class="nav-link w-full text-left px-3 py-2 rounded text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                        Logs & Troubleshooting
                    </button>
                </nav>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-8 bg-background-dark scroll-smooth">
                <div class="max-w-4xl mx-auto prose prose-invert">

                    <!-- Intro -->
                    <div x-show="activeSection === 'intro'" x-transition.opacity>
                        <h2>Selamat Datang di ReplyAI</h2>
                        <p>
                            ReplyAI adalah platform cerdas yang mengubah WhatsApp Anda menjadi asisten otomatis bertenaga AI. 
                            Sistem ini menggunakan teknologi <strong>RAG (Retrieval-Augmented Generation)</strong>, yang artinya bot tidak hanya "mengarang" jawaban, 
                            tetapi mencari jawaban berdasarkan **Materi (Knowledge Base)** yang Anda upload.
                        </p>
                        <div class="bg-blue-900/20 border border-blue-500/30 p-4 rounded-lg my-4">
                            <h3 class="!mt-0 text-blue-400">Prinsip Kerja Singkat:</h3>
                            <ol class="list-decimal pl-4 space-y-2 text-sm text-gray-300">
                                <li>Anda upload dokumen PDF/Teks ke menu <strong>Materi</strong>.</li>
                                <li>Sistem "membaca" dan mengingat isi dokumen tersebut.</li>
                                <li>Saat ada pesan masuk di WhatsApp, sistem mencari potongan info yang relevan dari materi Anda.</li>
                                <li>AI menyusun jawaban ramah berdasarkan info tersebut.</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Dashboard -->
                    <div x-show="activeSection === 'dashboard'" x-transition.opacity style="display: none;">
                        <h2>Dashboard</h2>
                        <p>Halaman utama <strong>Dashboard</strong> memberikan ringkasan cepat tentang status sistem Anda.</p>
                        
                        <h3>Statistik Utama:</h3>
                        <ul>
                            <li><strong>Total Pesan:</strong> Ringkasan aktivitas pesan hari ini.</li>
                            <li><strong>Status Bot:</strong> Indikator apakah bot sedang aktif atau mati.</li>
                            <li><strong>Recent Activity:</strong> Feed log aktivitas terbaru (siapa yang chat, apa respon bot).</li>
                        </ul>
                    </div>

                    <!-- Live Inbox -->
                    <div x-show="activeSection === 'live-inbox'" x-transition.opacity style="display: none;">
                        <h2>Live Inbox (Unified)</h2>
                        <p>Menu <strong>Kotak Masuk</strong> adalah pusat pesan terpadu. Berbeda dengan "WhatsApp Inbox" yang spesifik untuk WA, Live Inbox didesain untuk menangani berbagai saluran (jika kedepannya ada integrasi selain WA).</p>
                        
                        <h3>Fitur:</h3>
                        <ul>
                            <li><strong>Realtime:</strong> Pesan masuk tanpa refresh.</li>
                            <li><strong>Manajemen Chat:</strong> Tandai selesai (Resolve), balas cepat.</li>
                            <li><strong>Badge Notifikasi:</strong> Melihat jumlah pesan yang belum dibaca.</li>
                        </ul>
                    </div>

                    <!-- Bot Rules -->
                    <div x-show="activeSection === 'bot-rules'" x-transition.opacity style="display: none;">
                        <h2>Manajemen Bot (Rules)</h2>
                        <p>Menu <strong>Manajemen Bot</strong> digunakan untuk mengatur logika manual bot (Rule-Based) di luar kecerdasan AI (RAG).</p>
                        
                        <h3>Kapan menggunakan Rules?</h3>
                        <p>Gunakan Rules jika Anda ingin jawaban yang <strong>PASTI</strong> dan <strong>KONSISTEN</strong> untuk kata kunci tertentu, tanpa variasi AI.</p>

                        <h3>Cara Membuat Rule:</h3>
                        <ol>
                            <li>Klik <strong>Tambah Rule Baru</strong>.</li>
                            <li><strong>Keyword:</strong> Masukkan kata kunci pemicu (misal: "menu", "info").</li>
                            <li><strong>Response:</strong> Tulis jawaban yang diinginkan.</li>
                            <li><strong>Strictness:</strong> Pilih `Exact Match` (Harus persis) atau `Fuzzy` (Mirip-mirip).</li>
                        </ol>
                    </div>

                    <!-- Quick Replies -->
                    <div x-show="activeSection === 'quick-replies'" x-transition.opacity style="display: none;">
                        <h2>Quick Replies</h2>
                        <p>Fitur <strong>Quick Replies</strong> adalah template jawaban cepat untuk CS (Customer Service) agar tidak perlu mengetik ulang kalimat panjang.</p>
                        
                        <h3>Cara Menggunakan:</h3>
                        <ul>
                            <li>Di halaman <strong>Inbox</strong>, ketik simbol slash `/`.</li>
                            <li>Akan muncul daftar Quick Reply yang sudah Anda buat.</li>
                            <li>Pilih template, dan teks akan otomatis terisi.</li>
                        </ul>
                        
                        <h3>Manajemen Template:</h3>
                        <p>Masuk ke menu <strong>Quick Replies</strong> di sidebar untuk menambah, mengedit, atau menghapus template jawaban.</p>
                    </div>

                    <!-- KB -->
                    <div x-show="activeSection === 'kb'" x-transition.opacity style="display: none;">
                        <h2>Knowledge Base (Materi)</h2>
                        <p>Menu <strong>Materi</strong> adalah otak dari bot Anda. Di sini Anda melatih bot agar pintar.</p>
                        
                        <h3>1. Upload Dokumen</h3>
                        <ul>
                            <li>Klik tombol <strong>"Tambah Materi"</strong>.</li>
                            <li>Pilih file PDF atau ketik teks manual.</li>
                            <li>Beri judul yang jelas, misal: "Daftar Harga 2024" atau "Syarat Ketentuan".</li>
                        </ul>

                        <h3>2. Import dari URL</h3>
                        <p>Anda juga bisa memasukkan link website Anda, dan sistem akan mencoba mengambil teksnya.</p>
                        
                        <h3>3. Toggle Status</h3>
                        <p>Gunakan tombol toggle (ON/OFF) pada daftar materi untuk mengaktifkan atau menonaktifkan materi tertentu tanpa menghapusnya.</p>
                    </div>

                    <!-- Simulator -->
                    <div x-show="activeSection === 'simulator'" x-transition.opacity style="display: none;">
                        <h2>Bot Simulator</h2>
                        <p>Sebelum menghubungkan ke WhatsApp asli, Anda bisa mengetes kecerdasan bot di menu <strong>Simulator</strong>.</p>
                        
                        <h3>Cara Menggunakan:</h3>
                        <ul>
                            <li>Buka menu Simulator.</li>
                            <li>Ketik pertanyaan di kolom chat seolah-olah Anda adalah customer.</li>
                            <li>Lihat jawaban bot.</li>
                        </ul>
                        
                        <div class="p-4 bg-surface-dark border border-border-dark rounded-lg">
                            <strong>Tips:</strong> Simulator juga akan menampilkan "Thinking Process" atau sumber dokumen mana yang digunakan bot untuk menjawab. Ini berguna untuk debugging jika jawaban bot salah.
                        </div>
                    </div>

                    <!-- Connect -->
                    <div x-show="activeSection === 'wa-connect'" x-transition.opacity style="display: none;">
                        <h2>WhatsApp Connection</h2>
                        <p>Menu <strong>Integrasi > WA Settings</strong> digunakan untuk menghubungkan nomor WhatsApp Anda.</p>

                        <h3>Cara Koneksi (Scan QR):</h3>
                        <ol>
                            <li>Pastikan server Node.js `wa-service` berjalan.</li>
                            <li>Buka menu <strong>WA Settings</strong>.</li>
                            <li>Tunggu muncul QR Code.</li>
                            <li>Buka WhatsApp di HP Anda -> Perangkat Tertaut -> Tautkan Perangkat.</li>
                            <li>Scan QR Code di layar.</li>
                        </ol>

                        <h3>Pengaturan Auto-Reply</h3>
                        <p>Di halaman ini juga terdapat switch <strong>"Aktifkan Auto Reply"</strong>. Jika dimatikan, bot tidak akan menjawab pesan otomatis, tapi fitur lain (Inbox/Broadcast) tetap jalan.</p>
                    </div>

                    <!-- Inbox -->
                    <div x-show="activeSection === 'wa-inbox'" x-transition.opacity style="display: none;">
                        <h2>WhatsApp Inbox</h2>
                        <p>Fitur <strong>Inbox</strong> memungkinkan Anda dan tim CS membalas pesan secara manual lewat dashboard, mirip WhatsApp Web.</p>

                        <h3>Fitur Utama:</h3>
                        <ul>
                            <li><strong>List Chat:</strong> Melihat semua percakapan masuk.</li>
                            <li><strong>Filter Cerdas:</strong> Sistem otomatis menyembunyikan pesan Grup, Channel, dan Status Update agar inbox tetap bersih.</li>
                            <li><strong>Media Support:</strong> Anda bisa melihat gambar yang dikirim user dan mengirim gambar balasannya.</li>
                            <li><strong>Realtime:</strong> Pesan baru akan muncul otomatis (polling setiap beberapa detik).</li>
                        </ul>
                    </div>

                    <!-- Broadcast -->
                    <div x-show="activeSection === 'wa-broadcast'" x-transition.opacity style="display: none;">
                        <h2>Broadcast System</h2>
                        <p>Kirim pesan massal ke ratusan kontak sekaligus tanpa repot.</p>

                        <h3>Keamanan (Anti-Banned):</h3>
                        <p>Kami menerapkan sistem antrian (Queue) dengan jeda acak (1-5 detik) antar pesan agar aktivitas terlihat natural dan aman dari blokir WhatsApp.</p>

                        <h3>Cara Kirim:</h3>
                        <ol>
                            <li>Menu <strong>Broadcast -> Buat Baru</strong>.</li>
                            <li>Isi Pesan (Bisa pakai *Bold*, _Italic_).</li>
                            <li>Upload Gambar (Opsional).</li>
                            <li>Pilih Target:
                                <ul class="mt-1 ml-4 text-sm text-gray-400">
                                    <li><strong>All Contacts:</strong> Kirim ke semua orang yang pernah chat bot.</li>
                                    <li><strong>Manual:</strong> Copy-paste daftar nomor HP.</li>
                                </ul>
                            </li>
                            <li>Klik <strong>Start Broadcast</strong>.</li>
                        </ol>
                    </div>

                    <!-- Analytics -->
                    <div x-show="activeSection === 'wa-analytics'" x-transition.opacity style="display: none;">
                        <h2>Analytics Dashboard</h2>
                        <p>Pantau performa bot dan interaksi pelanggan Anda.</p>

                        <h3>Grafik & Data:</h3>
                        <ul>
                            <li><strong>Activity Chart:</strong> Grafik garis hijau (Pesan Masuk) vs biru (Respon Keluar). Berguna untuk melihat jam/hari sibuk.</li>
                            <li><strong>Message Distribution:</strong> Berapa persen pesan yang dijawab Bot vs Admin?</li>
                            <li><strong>Top Active Users:</strong> 5 orang yang paling sering chat. Bisa untuk identifikasi pelanggan setia atau spammer.</li>
                        </ul>
                    </div>

                    <!-- Sequences -->
                    <div x-show="activeSection === 'sequences'" x-transition.opacity style="display: none;">
                        <h2>Sequences (Drip Campaign)</h2>
                        <p>Fitur <strong>Sequences</strong> memungkinkan Anda mengirim serangkaian pesan otomatis berdasarkan waktu atau trigger tertentu. Cocok untuk welcome series, follow-up leads, reminder appointment, dan nurturing customer.</p>
                        
                        <div class="bg-blue-900/20 border border-blue-500/30 p-4 rounded-lg my-4">
                            <h3 class="!mt-0 text-blue-400">Contoh Penggunaan:</h3>
                            <ul class="text-sm text-gray-300">
                                <li><strong>Welcome Series:</strong> Hari 1: "Selamat datang!", Hari 3: "Info layanan kami", Hari 7: "Promo spesial"</li>
                                <li><strong>Reminder Appointment:</strong> H-3, H-1, dan H+1 (feedback)</li>
                                <li><strong>Follow-up Leads:</strong> Setelah inquiry, +2 jam, +1 hari, +3 hari</li>
                            </ul>
                        </div>

                        <h3>Cara Membuat Sequence:</h3>
                        <ol>
                            <li>Buka menu <strong>Sequences</strong> di sidebar.</li>
                            <li>Klik <strong>"Buat Sequence Baru"</strong>.</li>
                            <li>Isi informasi dasar:
                                <ul class="mt-1 ml-4 text-sm text-gray-400">
                                    <li><strong>Nama:</strong> Berikan nama yang jelas, misal "Welcome Series".</li>
                                    <li><strong>Trigger:</strong> Pilih kapan sequence dimulai (Manual, Pesan Pertama, Keyword, atau Tag).</li>
                                    <li><strong>Platform:</strong> Pilih platform target (WhatsApp, Instagram, Web, atau Semua).</li>
                                </ul>
                            </li>
                            <li>Tambahkan langkah-langkah pesan:
                                <ul class="mt-1 ml-4 text-sm text-gray-400">
                                    <li><strong>Delay:</strong> Tentukan kapan pesan dikirim (Langsung, Menit, Jam, atau Hari).</li>
                                    <li><strong>Isi Pesan:</strong> Tulis pesan yang akan dikirim.</li>
                                </ul>
                            </li>
                            <li>Klik <strong>"Simpan Sequence"</strong>.</li>
                        </ol>

                        <h3>Tipe Trigger:</h3>
                        <ul>
                            <li><strong>Manual:</strong> Anda mendaftarkan kontak secara manual dari dashboard.</li>
                            <li><strong>Pesan Pertama:</strong> Otomatis enroll saat user pertama kali chat.</li>
                            <li><strong>Keyword:</strong> Otomatis enroll saat user mengetik keyword tertentu.</li>
                            <li><strong>Tag Ditambahkan:</strong> Otomatis enroll saat tag tertentu ditambahkan ke kontak.</li>
                        </ul>

                        <h3>Mengelola Kontak Terdaftar:</h3>
                        <p>Di halaman detail sequence, Anda bisa:</p>
                        <ul>
                            <li>Melihat daftar kontak yang terdaftar beserta progress-nya.</li>
                            <li>Menambah kontak secara manual dengan tombol "Tambah Kontak Manual".</li>
                            <li>Membatalkan enrollment untuk kontak tertentu.</li>
                        </ul>

                        <div class="p-4 bg-yellow-900/20 border border-yellow-500/30 rounded-lg mt-4">
                            <strong class="text-yellow-400">Penting:</strong>
                            <p class="text-sm text-gray-300 mt-1">Pastikan Laravel Scheduler berjalan agar sequence dapat memproses pesan secara otomatis. Jalankan <code>php artisan schedule:run</code> setiap menit via cron job.</p>
                        </div>
                    </div>

                    <!-- CRM -->
                    <div x-show="activeSection === 'crm'" x-transition.opacity style="display: none;">
                        <h2>CRM & Contact Management</h2>
                        <p>Fitur <strong>CRM (Patient/User Profiling)</strong> membantu Anda menyimpan dan mengelola database pelanggan.</p>
                        
                        <h3>Fungsi Utama:</h3>
                        <ul>
                            <li><strong>Auto-Save:</strong> Setiap nomor baru yang chat ke bot otomatis tersimpan ke database.</li>
                            <li><strong>Profiling:</strong> Klik nomor di Inbox untuk melihat history chat lengkap.</li>
                            <li><strong>Filtering:</strong> (Coming Soon) Filter kontak berdasarkan Tag/Label seperti VIP, BPJS, Umum.</li>
                        </ul>
                    </div>

                    <!-- Settings -->
                    <div x-show="activeSection === 'settings'" x-transition.opacity style="display: none;">
                        <h2>Application Settings</h2>
                        <p>Menu <strong>Settings</strong> digunakan untuk konfigurasi global aplikasi.</p>

                        <h3>Jam Operasional (Business Hours):</h3>
                        <p>Anda dapat mengatur kapan bot aktif atau kapan bot mengirim pesan "Di luar jam kerja".</p>
                        <ul>
                            <li>Buka menu <strong>Settings</strong>.</li>
                            <li>Tentukan jam buka dan tutup untuk setiap hari.</li>
                            <li>Pesan Auto-reply diluar jam kerja akan dikirim jika ada chat masuk di malam hari/hari libur.</li>
                        </ul>
                    </div>

                    <!-- Logs -->
                    <div x-show="activeSection === 'logs'" x-transition.opacity style="display: none;">
                        <h2>Log Aktivitas</h2>
                        <p>Menu <strong>System > Log Aktivitas</strong> mencatat semua "pemikiran" AI.</p>
                        
                        <p>Anda bisa melihat:</p>
                        <ul>
                            <li>Pesan apa yang masuk.</li>
                            <li>Dokumen apa yang ditemukan AI (Relevansi).</li>
                            <li>Jawaban apa yang diberikan.</li>
                        </ul>
                        <p>Gunakan ini untuk mengevaluasi apakah AI mengambil materi yang benar atau tidak.</p>
                    </div>

                </div>
                
                <!-- Footer -->
                <div class="mt-20 pt-8 border-t border-border-dark text-center text-text-secondary text-sm">
                    <p>&copy; {{ date('Y') }} ReplyAI Internal Knowledge Base.</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
