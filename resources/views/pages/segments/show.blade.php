<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>{{ $segment->name }} - Segment Detail - REPLYAI</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
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
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #111722; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #324467; border-radius: 10px; }
        
        .contact-row {
            transition: background-color 0.15s ease;
        }
        .contact-row:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-white overflow-hidden h-screen flex flex-col lg:flex-row" x-data="segmentDetailApp()">

<!-- Sidebar Navigation -->
@include('components.sidebar')

<main class="flex-1 flex flex-col h-full overflow-hidden relative pt-14 lg:pt-0">
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6 lg:p-10 pb-20">
        <div class="max-w-[1200px] mx-auto flex flex-col gap-8">
            
            <!-- Header -->
            <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6">
                <div class="flex items-start gap-4">
                    <a href="{{ route('segments.index') }}" class="p-2 text-text-secondary hover:text-white rounded-lg hover:bg-white/5 transition-colors mt-1">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg flex items-center justify-center" style="background-color: {{ $segment->color_hex }}20">
                                <span class="material-symbols-outlined" style="color: {{ $segment->color_hex }}">folder</span>
                            </div>
                            <h2 class="text-2xl md:text-3xl font-bold text-white">{{ $segment->name }}</h2>
                            @if($segment->is_auto_update)
                                <span class="text-xs bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">auto_mode</span>
                                    Auto
                                </span>
                            @else
                                <span class="text-xs bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">touch_app</span>
                                    Manual
                                </span>
                            @endif
                        </div>
                        @if($segment->description)
                        <p class="text-text-secondary mt-2">{{ $segment->description }}</p>
                        @endif
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex items-center gap-3">
                    @if(!$segment->is_auto_update)
                    <button @click="openAddModal = true" class="bg-primary hover:bg-blue-600 text-white font-medium rounded-lg text-sm px-4 py-2.5 flex items-center gap-2 transition-colors">
                        <span class="material-symbols-outlined" style="font-size: 20px;">person_add</span>
                        Tambah Kontak
                    </button>
                    @endif
                    <a href="{{ route('segments.edit', $segment) }}" class="bg-surface-dark hover:bg-white/5 border border-border-dark text-white font-medium rounded-lg text-sm px-4 py-2.5 flex items-center gap-2 transition-colors">
                        <span class="material-symbols-outlined" style="font-size: 20px;">edit</span>
                        Edit
                    </a>
                </div>
            </div>

            <!-- Filters Applied (if auto-update) -->
            @if($segment->is_auto_update && !empty($segment->filters))
            <div class="bg-surface-dark border border-border-dark rounded-xl p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-primary">filter_list</span>
                    <span class="font-medium text-white">Filter Criteria</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if(!empty($segment->filters['platform']) && $segment->filters['platform'] !== 'both')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium
                            {{ $segment->filters['platform'] === 'whatsapp' ? 'bg-green-500/20 text-green-400' : 'bg-pink-500/20 text-pink-400' }}">
                            <span class="material-symbols-outlined text-xs">{{ $segment->filters['platform'] === 'whatsapp' ? 'chat' : 'photo_camera' }}</span>
                            Platform: {{ ucfirst($segment->filters['platform']) }}
                        </span>
                    @endif
                    
                    @if(!empty($segment->filters['tags']))
                        @foreach($segment->filters['tags'] as $tag)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-purple-500/20 text-purple-400">
                            <span class="material-symbols-outlined text-xs">label</span>
                            Tag: {{ $tag }}
                        </span>
                        @endforeach
                    @endif
                    
                    @if(!empty($segment->filters['last_active_days']))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400">
                            <span class="material-symbols-outlined text-xs">schedule</span>
                            Aktif dalam {{ $segment->filters['last_active_days'] }} hari terakhir
                        </span>
                    @endif
                    
                    @if(!empty($segment->filters['message_count_min']) || !empty($segment->filters['message_count_max']))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400">
                            <span class="material-symbols-outlined text-xs">chat_bubble</span>
                            Pesan: {{ $segment->filters['message_count_min'] ?? 0 }} - {{ $segment->filters['message_count_max'] ?? '∞' }}
                        </span>
                    @endif
                </div>
            </div>
            @endif

            <!-- Search & Filter -->
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-text-secondary">search</span>
                    <input type="text" x-model="search" @input.debounce.300ms="applySearch"
                           placeholder="Cari kontak..."
                           class="w-full bg-surface-dark border border-border-dark rounded-lg pl-10 pr-4 py-2 text-white placeholder-text-secondary/50 focus:outline-none focus:ring-2 focus:ring-primary/50">
                </div>
                <select x-model="platformFilter" @change="applyFilter"
                        class="bg-surface-dark border border-border-dark rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-primary/50">
                    <option value="all">Semua Platform</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="instagram">Instagram</option>
                </select>
            </div>

            <!-- Contacts Table -->
            <div class="bg-surface-dark border border-border-dark rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-background-dark text-xs uppercase font-semibold text-text-secondary">
                            <tr>
                                <th class="px-4 py-3 text-left">Kontak</th>
                                <th class="px-4 py-3 text-left">Platform</th>
                                <th class="px-4 py-3 text-left">Pesan</th>
                                <th class="px-4 py-3 text-left">Terakhir Aktif</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark">
                            @forelse($contacts as $contact)
                            <tr class="contact-row">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="size-9 rounded-full bg-slate-700 bg-cover shrink-0 flex items-center justify-center text-sm font-medium text-white"
                                             style="background-image: url('{{ $contact['avatar'] ?? '' }}'); background-color: {{ $contact['avatar'] ? 'transparent' : '#374151' }}">
                                            @if(empty($contact['avatar']))
                                                {{ strtoupper(substr($contact['name'], 0, 1)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-white font-medium">{{ $contact['name'] }}</p>
                                            @if($contact['platform'] === 'whatsapp')
                                                <p class="text-text-secondary text-xs">{{ $contact['phone'] }}</p>
                                            @elseif(!empty($contact['username']))
                                                <p class="text-text-secondary text-xs">@{{ $contact['username'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($contact['platform'] === 'whatsapp')
                                        <span class="inline-flex items-center gap-1.5 text-green-400">
                                            <span class="material-symbols-outlined text-sm">chat</span>
                                            WhatsApp
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-pink-400">
                                            <span class="material-symbols-outlined text-sm">photo_camera</span>
                                            Instagram
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-white">{{ $contact['messages_count'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-text-secondary">
                                    {{ \Carbon\Carbon::parse($contact['last_active'])->diffForHumans() }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($contact['platform'] === 'whatsapp')
                                            <a href="{{ route('whatsapp.inbox') }}?phone={{ $contact['type_id'] }}" 
                                               class="p-1.5 text-text-secondary hover:text-primary transition-colors"
                                               title="Lihat Chat">
                                                <span class="material-symbols-outlined">chat</span>
                                            </a>
                                        @else
                                            <a href="{{ route('inbox', ['conversation_id' => $contact['type_id']]) }}" 
                                               class="p-1.5 text-text-secondary hover:text-primary transition-colors"
                                               title="Lihat Chat">
                                                <span class="material-symbols-outlined">chat</span>
                                            </a>
                                        @endif
                                        
                                        @if(!$segment->is_auto_update)
                                        <button @click="confirmRemove('{{ $contact['type'] }}', '{{ $contact['type_id'] }}', '{{ $contact['name'] }}')"
                                                class="p-1.5 text-text-secondary hover:text-red-400 transition-colors"
                                                title="Hapus dari segment">
                                            <span class="material-symbols-outlined">person_remove</span>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center">
                                    <div class="size-16 rounded-full bg-surface-dark border border-border-dark flex items-center justify-center mx-auto mb-4">
                                        <span class="material-symbols-outlined text-3xl text-text-secondary">group_off</span>
                                    </div>
                                    <h3 class="text-lg font-medium text-white mb-1">Belum Ada Kontak</h3>
                                    <p class="text-text-secondary text-sm mb-4">
                                        @if($segment->is_auto_update)
                                            Tidak ada kontak yang memenuhi filter criteria.
                                        @else
                                            Segment ini masih kosong. Tambahkan kontak sekarang.
                                        @endif
                                    </p>
                                    @if(!$segment->is_auto_update)
                                    <button @click="openAddModal = true" class="inline-flex items-center gap-2 bg-primary hover:bg-blue-600 text-white font-medium rounded-lg text-sm px-4 py-2 transition-colors">
                                        <span class="material-symbols-outlined">person_add</span>
                                        Tambah Kontak
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($contacts->hasPages())
                <div class="px-4 py-3 border-t border-border-dark flex items-center justify-between">
                    <p class="text-sm text-text-secondary">
                        Menampilkan {{ $contacts->firstItem() ?? 0 }} - {{ $contacts->lastItem() ?? 0 }} dari {{ $contacts->total() }} kontak
                    </p>
                    <div class="flex items-center gap-2">
                        @if($contacts->onFirstPage())
                            <span class="px-3 py-1 rounded-lg text-sm text-text-secondary cursor-not-allowed">←</span>
                        @else
                            <a href="{{ $contacts->previousPageUrl() }}" class="px-3 py-1 rounded-lg text-sm text-white hover:bg-white/5">←</a>
                        @endif
                        
                        <span class="px-3 py-1 rounded-lg text-sm text-white">{{ $contacts->currentPage() }} / {{ $contacts->lastPage() }}</span>
                        
                        @if($contacts->hasMorePages())
                            <a href="{{ $contacts->nextPageUrl() }}" class="px-3 py-1 rounded-lg text-sm text-white hover:bg-white/5">→</a>
                        @else
                            <span class="px-3 py-1 rounded-lg text-sm text-text-secondary cursor-not-allowed">→</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>
</main>

<!-- Add Contact Modal -->
<div x-show="openAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
    <div x-show="openAddModal" x-transition.opacity class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="openAddModal = false"></div>
    
    <div x-show="openAddModal" x-transition.scale class="relative bg-surface-dark border border-border-dark rounded-xl w-full max-w-2xl max-h-[80vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-border-dark">
            <h3 class="text-lg font-semibold text-white">Tambah Kontak ke Segment</h3>
            <button @click="openAddModal = false" class="text-text-secondary hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <!-- Search -->
        <div class="px-6 py-4 border-b border-border-dark">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-text-secondary">search</span>
                <input type="text" x-model="addContactSearch" @input.debounce.300ms="searchContacts"
                       placeholder="Cari kontak berdasarkan nama, nomor HP, atau username..."
                       class="w-full bg-background-dark border border-border-dark rounded-lg pl-10 pr-4 py-2 text-white placeholder-text-secondary/50 focus:outline-none focus:ring-2 focus:ring-primary/50">
            </div>
        </div>
        
        <!-- Contact List -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-4">
            <div x-show="loadingContacts" class="py-8 text-center">
                <span class="material-symbols-outlined text-3xl text-text-secondary animate-spin">refresh</span>
                <p class="text-text-secondary text-sm mt-2">Mencari kontak...</p>
            </div>
            
            <div x-show="!loadingContacts && availableContacts.length === 0" class="py-8 text-center">
                <span class="material-symbols-outlined text-4xl text-text-secondary">search_off</span>
                <p class="text-text-secondary mt-2">Tidak ada kontak ditemukan</p>
            </div>
            
            <div x-show="!loadingContacts && availableContacts.length > 0" class="space-y-2">
                <template x-for="contact in availableContacts" :key="contact.id">
                    <div class="flex items-center gap-3 p-3 rounded-lg border border-border-dark hover:bg-white/5 cursor-pointer transition-colors"
                         :class="{ 'bg-primary/10 border-primary/50': isSelected(contact.id) }"
                         @click="toggleSelection(contact)">
                        <div class="size-10 rounded-full bg-slate-700 flex items-center justify-center text-sm font-medium text-white shrink-0"
                             x-text="contact.name.charAt(0).toUpperCase()">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-medium truncate" x-text="contact.name"></p>
                            <p class="text-text-secondary text-xs">
                                <span x-show="contact.platform === 'whatsapp'" class="inline-flex items-center gap-1 text-green-400">
                                    <span class="material-symbols-outlined text-xs">chat</span>
                                    <span x-text="contact.phone"></span>
                                </span>
                                <span x-show="contact.platform === 'instagram'" class="inline-flex items-center gap-1 text-pink-400">
                                    <span class="material-symbols-outlined text-xs">photo_camera</span>
                                    <span x-text="contact.username || 'Instagram'"></span>
                                </span>
                            </p>
                        </div>
                        <span x-show="contact.already_in_segment" class="text-xs text-amber-400 bg-amber-400/10 px-2 py-1 rounded-full">Sudah ada</span>
                        <div x-show="!contact.already_in_segment" class="size-5 rounded border border-border-dark flex items-center justify-center"
                             :class="{ 'bg-primary border-primary': isSelected(contact.id) }">
                            <span x-show="isSelected(contact.id)" class="material-symbols-outlined text-xs text-white">check</span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="flex items-center justify-between px-6 py-4 border-t border-border-dark">
            <span class="text-sm text-text-secondary">
                <span x-text="selectedContacts.length"></span> kontak dipilih
            </span>
            <div class="flex items-center gap-3">
                <button @click="openAddModal = false" class="px-4 py-2 text-text-secondary hover:text-white font-medium transition-colors">
                    Batal
                </button>
                <button @click="addSelectedContacts" 
                        :disabled="selectedContacts.length === 0 || addingContacts"
                        class="px-4 py-2 bg-primary hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                    <span x-show="addingContacts" class="material-symbols-outlined text-base animate-spin">refresh</span>
                    <span x-text="addingContacts ? 'Menambahkan...' : 'Tambahkan'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Confirmation Modal -->
<div x-show="removeModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
    <div x-show="removeModalOpen" x-transition.opacity class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="removeModalOpen = false"></div>
    
    <div x-show="removeModalOpen" x-transition.scale class="relative bg-surface-dark border border-border-dark rounded-xl w-full max-w-md p-6">
        <div class="text-center">
            <div class="size-16 rounded-full bg-red-500/20 flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-3xl text-red-400">person_remove</span>
            </div>
            <h3 class="text-lg font-semibold text-white mb-2">Hapus Kontak dari Segment?</h3>
            <p class="text-text-secondary mb-6">
                Kontak "<span x-text="removeContactName" class="text-white"></span>" akan dihapus dari segment ini.
            </p>
            <div class="flex items-center justify-center gap-3">
                <button @click="removeModalOpen = false" class="px-4 py-2 text-text-secondary hover:text-white font-medium transition-colors">
                    Batal
                </button>
                <button @click="removeContact" 
                        :disabled="removingContact"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                    <span x-show="removingContact" class="material-symbols-outlined text-base animate-spin">refresh</span>
                    <span x-text="removingContact ? 'Menghapus...' : 'Hapus'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function segmentDetailApp() {
    return {
        search: '{{ $search }}',
        platformFilter: '{{ $platform }}',
        openAddModal: false,
        addContactSearch: '',
        loadingContacts: false,
        availableContacts: [],
        selectedContacts: [],
        addingContacts: false,
        removeModalOpen: false,
        removeContactType: '',
        removeContactId: '',
        removeContactName: '',
        removingContact: false,
        
        init() {
            if (this.openAddModal) {
                this.searchContacts();
            }
        },
        
        applySearch() {
            const params = new URLSearchParams(window.location.search);
            if (this.search) {
                params.set('search', this.search);
            } else {
                params.delete('search');
            }
            window.location.search = params.toString();
        },
        
        applyFilter() {
            const params = new URLSearchParams(window.location.search);
            if (this.platformFilter !== 'all') {
                params.set('platform', this.platformFilter);
            } else {
                params.delete('platform');
            }
            window.location.search = params.toString();
        },
        
        async searchContacts() {
            this.loadingContacts = true;
            
            try {
                const response = await fetch(`{{ route('segments.available-contacts', $segment) }}?search=${encodeURIComponent(this.addContactSearch)}&platform=all&limit=50`);
                const data = await response.json();
                this.availableContacts = data.contacts || [];
            } catch (error) {
                console.error('Search error:', error);
            } finally {
                this.loadingContacts = false;
            }
        },
        
        isSelected(contactId) {
            return this.selectedContacts.some(c => c.id === contactId);
        },
        
        toggleSelection(contact) {
            if (contact.already_in_segment) return;
            
            const index = this.selectedContacts.findIndex(c => c.id === contact.id);
            if (index === -1) {
                this.selectedContacts.push({
                    type: contact.type,
                    id: contact.type_id
                });
            } else {
                this.selectedContacts.splice(index, 1);
            }
        },
        
        async addSelectedContacts() {
            if (this.selectedContacts.length === 0) return;
            
            this.addingContacts = true;
            
            try {
                const response = await fetch('{{ route("segments.bulk-add", $segment) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ contacts: this.selectedContacts })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Gagal menambahkan kontak');
                }
            } catch (error) {
                console.error('Add error:', error);
                alert('Terjadi kesalahan saat menambahkan kontak');
            } finally {
                this.addingContacts = false;
            }
        },
        
        confirmRemove(type, id, name) {
            this.removeContactType = type;
            this.removeContactId = id;
            this.removeContactName = name;
            this.removeModalOpen = true;
        },
        
        async removeContact() {
            this.removingContact = true;
            
            try {
                const response = await fetch(`{{ url('/segments/' . $segment->id) }}/contacts/${this.removeContactType}/${this.removeContactId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if (response.ok) {
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.error || 'Gagal menghapus kontak');
                }
            } catch (error) {
                console.error('Remove error:', error);
                alert('Terjadi kesalahan saat menghapus kontak');
            } finally {
                this.removingContact = false;
                this.removeModalOpen = false;
            }
        }
    }
}
</script>

</body>
</html>
