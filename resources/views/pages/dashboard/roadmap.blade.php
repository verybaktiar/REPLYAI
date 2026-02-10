<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-100">Plan & Roadmap Perbaikan</h1>
            <p class="text-gray-400 mt-2">Daftar perbaikan dan optimasi sistem yang sedang berjalan.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content: Roadmap -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Status Mismatch Fix -->
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-orange-500/10 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-orange-500/20 transition-all"></div>
                    <div class="flex items-start gap-4 relative z-10">
                        <div class="size-10 rounded-xl bg-orange-500/10 flex items-center justify-center border border-orange-500/20">
                            <span class="material-symbols-outlined text-orange-400">sync_problem</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-white">Sinkronisasi Status WhatsApp</h3>
                                <span class="px-2 py-1 rounded-md bg-orange-500/10 border border-orange-500/20 text-[10px] font-bold text-orange-400 uppercase">In Progress</span>
                            </div>
                            <p class="text-sm text-gray-400 mb-4">Memperbaiki ketidaksesuaian status antara halaman Settings dan Dashboard. Memastikan indikator "Connected" selalu akurat.</p>
                            <div class="bg-gray-950 rounded-xl p-4 border border-gray-800">
                                <ul class="space-y-2">
                                    <li class="flex items-center gap-2 text-xs text-gray-400">
                                        <span class="material-symbols-outlined text-[14px] text-green-500">check_circle</span>
                                        <span>Debug status device via Database</span>
                                    </li>
                                    <li class="flex items-center gap-2 text-xs text-gray-400">
                                        <span class="material-symbols-outlined text-[14px] text-green-500">check_circle</span>
                                        <span>Fix Dashboard Logic (Tampilkan '0' jika connected tapi kosong)</span>
                                    </li>
                                    <li class="flex items-center gap-2 text-xs text-gray-400">
                                        <span class="material-symbols-outlined text-[14px] text-yellow-500">radio_button_checked</span>
                                        <span>Sync Node.js Service Status dengan DB</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Optimization -->
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-blue-500/20 transition-all"></div>
                    <div class="flex items-start gap-4 relative z-10">
                        <div class="size-10 rounded-xl bg-blue-500/10 flex items-center justify-center border border-blue-500/20">
                            <span class="material-symbols-outlined text-blue-400">speed</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-white">Optimasi Performa Dashboard</h3>
                                <span class="px-2 py-1 rounded-md bg-blue-500/10 border border-blue-500/20 text-[10px] font-bold text-blue-400 uppercase">Completed</span>
                            </div>
                            <p class="text-sm text-gray-400 mb-4">Implementasi Caching dan Eager Loading untuk mempercepat loading dashboard.</p>
                            <div class="bg-gray-950 rounded-xl p-4 border border-gray-800">
                                <ul class="space-y-2">
                                    <li class="flex items-center gap-2 text-xs text-gray-400">
                                        <span class="material-symbols-outlined text-[14px] text-green-500">check_circle</span>
                                        <span>Implementasi Cache::remember (5 menit)</span>
                                    </li>
                                    <li class="flex items-center gap-2 text-xs text-gray-400">
                                        <span class="material-symbols-outlined text-[14px] text-green-500">check_circle</span>
                                        <span>Refactor Route Grouping</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="bg-gradient-to-br from-indigo-900 to-gray-900 border border-indigo-500/20 rounded-2xl p-6">
                    <h3 class="font-bold text-white mb-2">Butuh Bantuan Prioritas?</h3>
                    <p class="text-sm text-gray-400 mb-4">Jika Anda menemukan bug kritis lainnya, silakan hubungi tim teknis kami.</p>
                    <a href="https://wa.me/6281234567890" class="flex items-center justify-center w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold text-sm transition-all">
                        Hubungi Support
                    </a>
                </div>

                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                    <h3 class="font-bold text-gray-400 text-xs uppercase tracking-widest mb-4">Changelog Terbaru</h3>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                <div class="w-px h-full bg-gray-800 my-1"></div>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-white">Dashboard Logic Fix</p>
                                <p class="text-[10px] text-gray-500">Hari ini</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
                                <div class="w-px h-full bg-gray-800 my-1"></div>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-white">Route Refactoring</p>
                                <p class="text-[10px] text-gray-500">Hari ini</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
