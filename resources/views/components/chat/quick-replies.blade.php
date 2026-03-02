{{--
    Quick Replies Component
    
    Usage:
    <x-chat.quick-replies 
        :input-selector="'#message-input'" 
        :on-select="'insertMessage'"
    />
    
    Or use the inline version for chat inputs:
    <x-chat.quick-replies.inline 
        wire:model="message" 
        :show-trigger="true"
    />
--}}

@props([
    'inputSelector' => null,
    'onSelect' => null,
    'showManageButton' => true,
    'class' => '',
])

<div 
    x-data="quickReplies('{{ $inputSelector }}', '{{ $onSelect }}')"
    x-init="init()"
    @keydown.escape.window="closeAll()"
    class="relative {{ $class }}"
>
    {{-- Trigger Button --}}
    @if($showManageButton)
    <button 
        @click="openModal()"
        type="button"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-400 hover:text-white bg-surface-lighter hover:bg-[#232f48] rounded-lg transition-colors border border-[#232f48]"
        title="Kelola Balasan Cepat"
    >
        <span class="material-symbols-outlined text-[16px]">bolt</span>
        <span>Quick Reply</span>
    </button>
    @endif

    {{-- Shortcut Trigger Popup (appears when typing /) --}}
    <div 
        x-show="showShortcutPopup"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        x-cloak
        class="fixed z-50 w-80 max-w-[90vw] bg-surface-dark border border-[#232f48] rounded-xl shadow-2xl overflow-hidden"
        :style="popupStyle"
    >
        {{-- Search/Filter --}}
        <div class="p-3 border-b border-[#232f48]">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary text-[16px]">search</span>
                <input 
                    x-model="shortcutFilter"
                    @keydown.enter.prevent="selectHighlighted()"
                    @keydown.up.prevent="highlightPrev()"
                    @keydown.down.prevent="highlightNext()"
                    type="text" 
                    placeholder="Cari shortcut..."
                    class="w-full h-9 pl-9 pr-3 bg-[#111722] border border-[#232f48] rounded-lg text-sm text-white placeholder-text-secondary focus:outline-none focus:ring-1 focus:ring-primary focus:border-transparent"
                >
            </div>
        </div>

        {{-- Quick Replies List Grouped by Category --}}
        <div class="max-h-64 overflow-y-auto scrollbar-thin">
            <template x-for="(items, category) in filteredGroupedReplies" :key="category">
                <div>
                    <div 
                        x-show="items.length > 0"
                        class="px-3 py-1.5 text-[10px] font-bold text-text-secondary uppercase tracking-wider bg-[#111722]/50"
                        x-text="category"
                    ></div>
                    <template x-for="(reply, index) in items" :key="reply.id">
                        <button
                            @click="selectReply(reply)"
                            @mouseenter="highlightedIndex = getGlobalIndex(category, index)"
                            type="button"
                            class="w-full px-3 py-2.5 text-left hover:bg-[#232f48] transition-colors flex items-start gap-3"
                            :class="{ 'bg-[#232f48]': getGlobalIndex(category, index) === highlightedIndex }"
                        >
                            <span 
                                class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono bg-primary/10 text-primary border border-primary/20"
                                x-text="'/' + reply.shortcut"
                            ></span>
                            <div class="flex-1 min-w-0">
                                <p 
                                    class="text-sm text-white truncate"
                                    x-text="truncateMessage(reply.message, 40)"
                                ></p>
                                <p 
                                    x-show="reply.usage_count > 0"
                                    class="text-[10px] text-text-secondary mt-0.5"
                                    x-text="'Digunakan ' + reply.usage_count + 'x'"
                                ></p>
                            </div>
                        </button>
                    </template>
                </div>
            </template>

            {{-- Empty State --}}
            <div 
                x-show="Object.keys(filteredGroupedReplies).length === 0"
                class="p-4 text-center"
            >
                <span class="material-symbols-outlined text-2xl text-text-secondary/50 mb-1">search_off</span>
                <p class="text-sm text-text-secondary">Tidak ada quick reply ditemukan</p>
            </div>
        </div>

        {{-- Footer Hint --}}
        <div class="px-3 py-2 bg-[#111722] border-t border-[#232f48] flex items-center justify-between text-[10px] text-text-secondary">
            <span>↑↓ navigasi</span>
            <span>Enter pilih</span>
            <span>Esc tutup</span>
        </div>
    </div>

    {{-- Management Modal --}}
    <div 
        x-show="showModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        class="fixed inset-0 z-50"
    >
        {{-- Backdrop --}}
        <div 
            @click="closeModal()"
            class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        ></div>

        {{-- Modal Content --}}
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div 
                @click.stop
                class="w-full max-w-2xl max-h-[90vh] flex flex-col bg-surface-dark border border-[#232f48] rounded-2xl shadow-2xl"
            >
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-[#232f48]">
                    <div>
                        <h3 class="text-lg font-bold text-white">Balasan Cepat</h3>
                        <p class="text-xs text-text-secondary mt-0.5">Kelola template balasan untuk respons lebih cepat</p>
                    </div>
                    <button 
                        @click="closeModal()"
                        type="button"
                        class="p-2 rounded-lg hover:bg-[#232f48] text-text-secondary hover:text-white transition-colors"
                    >
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                {{-- Body --}}
                <div class="flex-1 overflow-hidden flex flex-col md:flex-row">
                    {{-- Sidebar: Categories --}}
                    <div class="w-full md:w-48 border-b md:border-b-0 md:border-r border-[#232f48] p-3">
                        <button
                            @click="selectedCategory = ''; filter = ''"
                            :class="{ 'bg-primary/10 text-primary border-primary/20': selectedCategory === '' }"
                            class="w-full px-3 py-2 rounded-lg text-sm text-left text-white hover:bg-[#232f48] transition-colors border border-transparent mb-1"
                        >
                            Semua Kategori
                        </button>
                        <template x-for="cat in categories" :key="cat">
                            <button
                                @click="selectedCategory = cat; filter = ''"
                                :class="{ 'bg-primary/10 text-primary border-primary/20': selectedCategory === cat }"
                                class="w-full px-3 py-2 rounded-lg text-sm text-left text-white hover:bg-[#232f48] transition-colors border border-transparent mb-1"
                                x-text="cat"
                            ></button>
                        </template>
                    </div>

                    {{-- Main Content --}}
                    <div class="flex-1 flex flex-col min-h-0">
                        {{-- Toolbar --}}
                        <div class="p-3 border-b border-[#232f48] flex items-center gap-2">
                            <div class="relative flex-1">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary text-[16px]">search</span>
                                <input 
                                    x-model="filter"
                                    type="text" 
                                    placeholder="Cari shortcut atau pesan..."
                                    class="w-full h-9 pl-9 pr-3 bg-[#111722] border border-[#232f48] rounded-lg text-sm text-white placeholder-text-secondary focus:outline-none focus:ring-1 focus:ring-primary focus:border-transparent"
                                >
                            </div>
                            <button 
                                @click="openCreateForm()"
                                type="button"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-primary hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                <span class="material-symbols-outlined text-[18px]">add</span>
                                <span class="hidden sm:inline">Tambah</span>
                            </button>
                        </div>

                        {{-- Quick Replies List --}}
                        <div class="flex-1 overflow-y-auto p-3 space-y-2">
                            <template x-for="reply in filteredReplies" :key="reply.id">
                                <div 
                                    class="group p-3 bg-[#111722] rounded-xl border border-[#232f48] hover:border-primary/30 transition-colors"
                                >
                                    <div class="flex items-start gap-3">
                                        <span 
                                            class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-mono bg-primary/10 text-primary border border-primary/20"
                                            x-text="'/' + reply.shortcut"
                                        ></span>
                                        <div class="flex-1 min-w-0">
                                            <p 
                                                class="text-sm text-white whitespace-pre-wrap break-words"
                                                x-text="reply.message"
                                            ></p>
                                            <div class="flex items-center gap-3 mt-2">
                                                <span 
                                                    class="text-[10px] text-text-secondary px-2 py-0.5 bg-[#232f48] rounded"
                                                    x-text="reply.category"
                                                ></span>
                                                <span 
                                                    x-show="reply.usage_count > 0"
                                                    class="text-[10px] text-text-secondary"
                                                    x-text="'Digunakan ' + reply.usage_count + 'x'"
                                                ></span>
                                                <span 
                                                    :class="reply.is_active ? 'text-green-500' : 'text-slate-500'"
                                                    class="text-[10px]"
                                                    x-text="reply.is_active ? 'Aktif' : 'Nonaktif'"
                                                ></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button 
                                                @click="editReply(reply)"
                                                type="button"
                                                class="p-1.5 text-text-secondary hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                                                title="Edit"
                                            >
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </button>
                                            <button 
                                                @click="confirmDelete(reply)"
                                                type="button"
                                                class="p-1.5 text-text-secondary hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-colors"
                                                title="Hapus"
                                            >
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Empty State --}}
                            <div 
                                x-show="filteredReplies.length === 0"
                                class="flex flex-col items-center justify-center py-12 text-center"
                            >
                                <div class="w-16 h-16 bg-[#232f48] rounded-full flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-2xl text-text-secondary">bolt</span>
                                </div>
                                <h4 class="text-sm font-medium text-white mb-1">
                                    <span x-show="!loading && quickReplies.length === 0">Belum ada quick reply</span>
                                    <span x-show="filter || selectedCategory">Tidak ada hasil</span>
                                </h4>
                                <p class="text-xs text-text-secondary max-w-xs" x-show="!loading && quickReplies.length === 0">
                                    Tambahkan template balasan untuk respons lebih cepat saat chat dengan pelanggan.
                                </p>
                                <button 
                                    x-show="!loading && quickReplies.length === 0"
                                    @click="openCreateForm()"
                                    type="button"
                                    class="mt-4 px-4 py-2 bg-primary hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors"
                                >
                                    Buat Quick Reply Pertama
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create/Edit Form Modal --}}
    <div 
        x-show="showForm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        class="fixed inset-0 z-[60]"
    >
        <div 
            @click="closeForm()"
            class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        ></div>

        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div 
                @click.stop
                class="w-full max-w-lg bg-surface-dark border border-[#232f48] rounded-2xl shadow-2xl"
            >
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-[#232f48]">
                    <h3 class="text-lg font-bold text-white" x-text="isEditing ? 'Edit Quick Reply' : 'Tambah Quick Reply'"></h3>
                </div>

                {{-- Form --}}
                <form @submit.prevent="saveReply()" class="p-6 space-y-4">
                    {{-- Shortcut --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-200 mb-2">Shortcut</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary">/</span>
                            <input 
                                x-model="form.shortcut"
                                type="text" 
                                required
                                maxlength="50"
                                placeholder="contoh: greeting"
                                class="w-full h-10 pl-7 pr-3 bg-[#111722] border rounded-lg text-sm text-white placeholder-text-secondary focus:outline-none focus:ring-1 focus:ring-primary focus:border-transparent"
                                :class="errors.shortcut ? 'border-red-500' : 'border-[#232f48]'"
                            >
                        </div>
                        <p class="text-xs text-red-400 mt-1" x-show="errors.shortcut" x-text="errors.shortcut"></p>
                        <p class="text-xs text-text-secondary mt-1" x-show="!errors.shortcut">Ketik / diikuti shortcut untuk mengakses cepat</p>
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-200 mb-2">Kategori</label>
                        <div class="relative">
                            <input 
                                x-model="form.category"
                                type="text" 
                                list="category-suggestions"
                                maxlength="50"
                                placeholder="Pilih atau buat kategori baru"
                                class="w-full h-10 px-3 bg-[#111722] border border-[#232f48] rounded-lg text-sm text-white placeholder-text-secondary focus:outline-none focus:ring-1 focus:ring-primary focus:border-transparent"
                            >
                            <datalist id="category-suggestions">
                                <template x-for="cat in categories" :key="cat">
                                    <option :value="cat" x-text="cat"></option>
                                </template>
                            </datalist>
                        </div>
                    </div>

                    {{-- Message --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-200 mb-2">Pesan</label>
                        <textarea 
                            x-model="form.message"
                            required
                            rows="5"
                            maxlength="5000"
                            placeholder="Tulis template pesan di sini..."
                            class="w-full px-3 py-2 bg-[#111722] border rounded-lg text-sm text-white placeholder-text-secondary focus:outline-none focus:ring-1 focus:ring-primary focus:border-transparent resize-none"
                            :class="errors.message ? 'border-red-500' : 'border-[#232f48]'"
                        ></textarea>
                        <p class="text-xs text-red-400 mt-1" x-show="errors.message" x-text="errors.message"></p>
                        <div class="flex justify-between mt-1">
                            <p class="text-xs text-text-secondary">Template pesan yang akan dikirim</p>
                            <p class="text-xs text-text-secondary" x-text="form.message.length + '/5000'"></p>
                        </div>
                    </div>

                    {{-- Active Toggle --}}
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                x-model="form.is_active"
                                type="checkbox" 
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-[#232f48] peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                        <span class="text-sm text-gray-200">Aktifkan template ini</span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#232f48]">
                        <button 
                            @click="closeForm()"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white hover:bg-[#232f48] rounded-lg transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit"
                            :disabled="saving"
                            class="px-4 py-2 bg-primary hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2"
                        >
                            <span x-show="saving" class="animate-spin material-symbols-outlined text-[16px]">progress_activity</span>
                            <span x-text="saving ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Tambah Quick Reply')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation --}}
    <div 
        x-show="showDeleteConfirm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        class="fixed inset-0 z-[70] flex items-center justify-center p-4"
    >
        <div 
            @click="showDeleteConfirm = false"
            class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        ></div>

        <div class="relative w-full max-w-sm bg-surface-dark border border-[#232f48] rounded-2xl shadow-2xl p-6 text-center">
            <div class="w-14 h-14 bg-red-500/10 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-2xl">delete_forever</span>
            </div>
            <h3 class="text-lg font-bold text-white mb-2">Hapus Quick Reply?</h3>
            <p class="text-sm text-text-secondary mb-6">
                Template <span class="text-white font-medium" x-text="replyToDelete ? '/' + replyToDelete.shortcut : ''"></span> akan dihapus secara permanen.
            </p>
            <div class="flex gap-3 justify-center">
                <button 
                    @click="showDeleteConfirm = false"
                    type="button"
                    class="px-5 py-2.5 rounded-lg border border-[#232f48] text-gray-300 hover:text-white hover:bg-[#232f48] text-sm font-medium transition-colors"
                >
                    Batal
                </button>
                <button 
                    @click="deleteReply()"
                    :disabled="deleting"
                    type="button"
                    class="px-5 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white text-sm font-bold shadow-lg shadow-red-900/20 transition-colors flex items-center gap-2"
                >
                    <span x-show="deleting" class="animate-spin material-symbols-outlined text-[16px]">progress_activity</span>
                    <span x-text="deleting ? 'Menghapus...' : 'Hapus'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Toast Notification --}}
    <div 
        x-show="toast.show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        x-cloak
        class="fixed bottom-5 right-5 z-[80] rounded-xl bg-surface-dark border border-[#232f48] px-4 py-3 shadow-xl flex items-center gap-3"
    >
        <span 
            class="material-symbols-outlined"
            :class="toast.type === 'success' ? 'text-green-500' : 'text-red-500'"
            x-text="toast.type === 'success' ? 'check_circle' : 'error'"
        ></span>
        <span class="text-sm text-white" x-text="toast.message"></span>
    </div>
</div>

@once
@push('scripts')
<script>
function quickReplies(inputSelector = null, onSelectCallback = null) {
    return {
        // Data
        quickReplies: [],
        categories: [],
        loading: false,
        
        // Filter & Search
        filter: '',
        selectedCategory: '',
        shortcutFilter: '',
        
        // UI States
        showModal: false,
        showForm: false,
        showDeleteConfirm: false,
        showShortcutPopup: false,
        popupStyle: 'top: 0; left: 0;',
        
        // Form
        isEditing: false,
        form: {
            id: null,
            shortcut: '',
            message: '',
            category: 'Umum',
            is_active: true
        },
        errors: {},
        saving: false,
        
        // Delete
        replyToDelete: null,
        deleting: false,
        
        // Shortcut navigation
        highlightedIndex: 0,
        flatReplies: [],
        
        // Toast
        toast: {
            show: false,
            message: '',
            type: 'success'
        },

        init() {
            this.loadQuickReplies();
            
            // Listen for shortcut trigger
            if (inputSelector) {
                this.setupInputListener();
            }
            
            // Listen for global events
            window.addEventListener('quick-reply:open', () => this.openModal());
            window.addEventListener('quick-reply:refresh', () => this.loadQuickReplies());
        },

        // Load data from server
        async loadQuickReplies() {
            this.loading = true;
            try {
                const response = await fetch('/api/quick-replies');
                const data = await response.json();
                
                if (data.success) {
                    // Convert grouped object to array
                    const grouped = data.data;
                    this.quickReplies = [];
                    this.categories = Object.keys(grouped).sort();
                    
                    Object.entries(grouped).forEach(([category, items]) => {
                        items.forEach(item => {
                            this.quickReplies.push({ ...item, category });
                        });
                    });
                }
            } catch (error) {
                console.error('Failed to load quick replies:', error);
                this.showToast('Gagal memuat data', 'error');
            } finally {
                this.loading = false;
            }
        },

        // Computed: Filtered replies for management view
        get filteredReplies() {
            let filtered = this.quickReplies;
            
            if (this.selectedCategory) {
                filtered = filtered.filter(r => r.category === this.selectedCategory);
            }
            
            if (this.filter) {
                const q = this.filter.toLowerCase();
                filtered = filtered.filter(r => 
                    r.shortcut.toLowerCase().includes(q) ||
                    r.message.toLowerCase().includes(q)
                );
            }
            
            return filtered.sort((a, b) => b.usage_count - a.usage_count);
        },

        // Computed: Grouped replies for shortcut popup
        get filteredGroupedReplies() {
            const grouped = {};
            
            // Group by category
            this.quickReplies.forEach(reply => {
                if (!reply.is_active) return;
                
                // Apply filter
                if (this.shortcutFilter) {
                    const q = this.shortcutFilter.toLowerCase();
                    if (!reply.shortcut.toLowerCase().includes(q) && 
                        !reply.message.toLowerCase().includes(q)) {
                        return;
                    }
                }
                
                if (!grouped[reply.category]) {
                    grouped[reply.category] = [];
                }
                grouped[reply.category].push(reply);
            });
            
            // Update flat replies for navigation
            this.flatReplies = [];
            Object.entries(grouped).forEach(([category, items]) => {
                items.forEach(item => {
                    this.flatReplies.push({ ...item, category });
                });
            });
            
            return grouped;
        },

        // Setup input listener for / trigger
        setupInputListener() {
            const input = document.querySelector(inputSelector);
            if (!input) return;
            
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                const lastSlashIndex = value.lastIndexOf('/');
                
                if (lastSlashIndex !== -1) {
                    const afterSlash = value.substring(lastSlashIndex + 1);
                    const hasSpace = afterSlash.includes(' ');
                    
                    if (!hasSpace) {
                        this.shortcutFilter = afterSlash;
                        this.showShortcutPopup = true;
                        this.highlightedIndex = 0;
                        this.updatePopupPosition(input);
                    } else {
                        this.showShortcutPopup = false;
                    }
                } else {
                    this.showShortcutPopup = false;
                }
            });
            
            input.addEventListener('blur', () => {
                setTimeout(() => {
                    this.showShortcutPopup = false;
                }, 200);
            });
        },

        updatePopupPosition(input) {
            const rect = input.getBoundingClientRect();
            this.popupStyle = `top: ${rect.top - 320}px; left: ${rect.left}px; width: ${rect.width}px;`;
        },

        // Navigation
        getGlobalIndex(category, index) {
            let count = 0;
            for (const [cat, items] of Object.entries(this.filteredGroupedReplies)) {
                if (cat === category) {
                    return count + index;
                }
                count += items.length;
            }
            return 0;
        },

        highlightNext() {
            const max = this.flatReplies.length - 1;
            this.highlightedIndex = this.highlightedIndex >= max ? 0 : this.highlightedIndex + 1;
        },

        highlightPrev() {
            const max = this.flatReplies.length - 1;
            this.highlightedIndex = this.highlightedIndex <= 0 ? max : this.highlightedIndex - 1;
        },

        selectHighlighted() {
            if (this.flatReplies[this.highlightedIndex]) {
                this.selectReply(this.flatReplies[this.highlightedIndex]);
            }
        },

        selectReply(reply) {
            const input = document.querySelector(inputSelector);
            if (input) {
                const value = input.value;
                const lastSlashIndex = value.lastIndexOf('/');
                input.value = value.substring(0, lastSlashIndex) + reply.message;
                
                // Trigger input event for Livewire/other frameworks
                input.dispatchEvent(new Event('input', { bubbles: true }));
                
                // Focus back to input
                input.focus();
                
                // Track usage
                this.trackUsage(reply.id);
            }
            
            // Execute callback if provided
            if (onSelectCallback && typeof window[onSelectCallback] === 'function') {
                window[onSelectCallback](reply);
            }
            
            this.showShortcutPopup = false;
        },

        async trackUsage(id) {
            try {
                await fetch(`/api/quick-replies/${id}/track`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
            } catch (e) {
                // Silent fail
            }
        },

        // Modal Actions
        openModal() {
            this.showModal = true;
            this.loadQuickReplies();
        },

        closeModal() {
            this.showModal = false;
        },

        openCreateForm() {
            this.resetForm();
            this.isEditing = false;
            this.showForm = true;
        },

        closeForm() {
            this.showForm = false;
            this.errors = {};
        },

        resetForm() {
            this.form = {
                id: null,
                shortcut: '',
                message: '',
                category: 'Umum',
                is_active: true
            };
            this.errors = {};
        },

        editReply(reply) {
            this.form = {
                id: reply.id,
                shortcut: reply.shortcut,
                message: reply.message,
                category: reply.category,
                is_active: reply.is_active
            };
            this.isEditing = true;
            this.showForm = true;
        },

        // CRUD Operations
        async saveReply() {
            this.saving = true;
            this.errors = {};
            
            const url = this.isEditing ? `/quick-replies/${this.form.id}` : '/quick-replies';
            const method = this.isEditing ? 'PUT' : 'POST';
            
            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        shortcut: this.form.shortcut,
                        message: this.form.message,
                        category: this.form.category || 'Umum',
                        is_active: this.form.is_active
                    })
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    if (response.status === 422) {
                        this.errors = data.errors;
                        return;
                    }
                    throw new Error(data.message || 'Terjadi kesalahan');
                }
                
                this.showToast(data.message, 'success');
                this.closeForm();
                this.loadQuickReplies();
                
            } catch (error) {
                this.showToast(error.message || 'Gagal menyimpan', 'error');
            } finally {
                this.saving = false;
            }
        },

        confirmDelete(reply) {
            this.replyToDelete = reply;
            this.showDeleteConfirm = true;
        },

        async deleteReply() {
            if (!this.replyToDelete) return;
            
            this.deleting = true;
            
            try {
                const response = await fetch(`/quick-replies/${this.replyToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Gagal menghapus');
                }
                
                this.showToast(data.message, 'success');
                this.loadQuickReplies();
                
            } catch (error) {
                this.showToast(error.message, 'error');
            } finally {
                this.deleting = false;
                this.showDeleteConfirm = false;
                this.replyToDelete = null;
            }
        },

        // Utilities
        truncateMessage(message, length) {
            if (message.length <= length) return message;
            return message.substring(0, length) + '...';
        },

        closeAll() {
            this.showModal = false;
            this.showForm = false;
            this.showDeleteConfirm = false;
            this.showShortcutPopup = false;
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => {
                this.toast.show = false;
            }, 3000);
        }
    };
}
</script>
@endpush
@endonce
