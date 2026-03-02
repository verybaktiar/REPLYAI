@props([
    'conversationType' => 'instagram',
    'conversationId' => null,
    'triggerLabel' => 'Media Gallery',
    'triggerIcon' => 'photo_library'
])

<div x-data="mediaGallery({{ $conversationId }}, '{{ $conversationType }}')" class="relative">
    {{-- Trigger Button --}}
    <button 
        @click="open = true; loadMedia()"
        class="flex items-center gap-2 px-3 py-2 bg-surface-dark hover:bg-surface-dark/80 border border-border-dark rounded-lg transition-all duration-200 group"
        title="{{ $triggerLabel }}"
    >
        <span class="material-symbols-outlined text-lg text-text-secondary group-hover:text-white transition-colors">{{ $triggerIcon }}</span>
        <span class="text-sm font-medium text-text-secondary group-hover:text-white transition-colors hidden sm:inline">{{ $triggerLabel }}</span>
        <span x-show="mediaCount > 0" x-text="mediaCount" class="px-1.5 py-0.5 bg-primary/20 text-primary text-[10px] font-bold rounded-full min-w-[18px] text-center" x-cloak></span>
    </button>

    {{-- Modal Overlay --}}
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm"
        @click="closeGallery()"
        x-cloak
    ></div>

    {{-- Modal Content --}}
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-4 lg:inset-10 z-50 bg-background-dark border border-border-dark rounded-2xl shadow-2xl flex flex-col overflow-hidden"
        @keydown.escape.window="closeGallery()"
        x-cloak
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-border-dark bg-surface-dark/50">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-2xl text-primary">photo_library</span>
                    <h2 class="text-lg font-bold text-white">Media Gallery</h2>
                </div>
                <span x-show="mediaCount > 0" class="px-2 py-0.5 bg-surface-dark text-text-secondary text-xs font-medium rounded-full" x-text="mediaCount + ' items'"></span>
            </div>
            <div class="flex items-center gap-3">
                {{-- Search --}}
                <div class="relative hidden sm:block">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary text-lg">search</span>
                    <input 
                        type="text" 
                        x-model="search"
                        @input.debounce.300ms="applyFilters()"
                        placeholder="Search files..."
                        class="w-48 lg:w-64 pl-10 pr-4 py-2 bg-background-dark border border-border-dark rounded-lg text-sm text-white placeholder-text-secondary focus:outline-none focus:border-primary transition-colors"
                    >
                </div>
                <button @click="closeGallery()" class="p-2 hover:bg-white/5 rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-text-secondary hover:text-white">close</span>
                </button>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="flex items-center gap-1 px-6 py-3 border-b border-border-dark overflow-x-auto no-scrollbar">
            <template x-for="tab in tabs" :key="tab.value">
                <button 
                    @click="activeFilter = tab.value; applyFilters()"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 whitespace-nowrap flex items-center gap-2"
                    :class="activeFilter === tab.value ? 'bg-primary text-black' : 'text-text-secondary hover:text-white hover:bg-white/5'"
                >
                    <span class="material-symbols-outlined text-lg" x-text="tab.icon"></span>
                    <span x-text="tab.label"></span>
                    <span 
                        x-show="tab.count > 0" 
                        x-text="tab.count" 
                        class="px-1.5 py-0.5 text-[10px] font-bold rounded-full"
                        :class="activeFilter === tab.value ? 'bg-black/20 text-black' : 'bg-surface-dark text-text-secondary'"
                    ></span>
                </button>
            </template>
        </div>

        {{-- Mobile Search (visible only on small screens) --}}
        <div class="sm:hidden px-4 py-2 border-b border-border-dark">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary text-lg">search</span>
                <input 
                    type="text" 
                    x-model="search"
                    @input.debounce.300ms="applyFilters()"
                    placeholder="Search files..."
                    class="w-full pl-10 pr-4 py-2 bg-background-dark border border-border-dark rounded-lg text-sm text-white placeholder-text-secondary focus:outline-none focus:border-primary transition-colors"
                >
            </div>
        </div>

        {{-- Content Area --}}
        <div class="flex-1 overflow-y-auto p-4 lg:p-6 custom-scrollbar">
            {{-- Loading State --}}
            <div x-show="loading" class="flex items-center justify-center h-64">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-10 h-10 border-3 border-primary/30 border-t-primary rounded-full animate-spin"></div>
                    <p class="text-text-secondary text-sm">Loading media...</p>
                </div>
            </div>

            {{-- Empty State --}}
            <div x-show="!loading && filteredMedia.length === 0" class="flex flex-col items-center justify-center h-64 text-center">
                <div class="w-20 h-20 bg-surface-dark rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-4xl text-text-secondary">folder_open</span>
                </div>
                <h3 class="text-white font-semibold mb-2">No media found</h3>
                <p class="text-text-secondary text-sm max-w-xs" x-text="search ? 'No files matching your search.' : 'No media files in this conversation yet.'"></p>
            </div>

            {{-- Image Grid --}}
            <div 
                x-show="!loading && activeFilter !== 'documents' && activeFilter !== 'links' && hasVisibleMedia()"
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3"
            >
                <template x-for="item in filteredMedia" :key="item.id">
                    <div 
                        x-show="isImageOrVideo(item)"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="group relative aspect-square bg-surface-dark rounded-xl overflow-hidden cursor-pointer border border-border-dark hover:border-primary/50 transition-all duration-200"
                        @click="openPreview(item)"
                    >
                        {{-- Image Thumbnail --}}
                        <template x-if="item.type === 'image'">
                            <img 
                                :src="item.url" 
                                :alt="item.filename"
                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                loading="lazy"
                                @error="$event.target.src = '/images/placeholder-image.png'"
                            >
                        </template>

                        {{-- Video Thumbnail --}}
                        <template x-if="item.type === 'video'">
                            <div class="w-full h-full relative">
                                <video class="w-full h-full object-cover">
                                    <source :src="item.url" :type="item.mime_type">
                                </video>
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center group-hover:bg-black/40 transition-colors">
                                    <div class="w-12 h-12 bg-primary/90 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <span class="material-symbols-outlined text-black text-2xl">play_arrow</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Hover Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <div class="absolute bottom-0 left-0 right-0 p-3">
                                <p class="text-white text-xs font-medium truncate" x-text="item.filename"></p>
                                <p class="text-white/60 text-[10px]" x-text="item.human_readable_size + ' • ' + item.created_at_formatted"></p>
                            </div>
                        </div>

                        {{-- Type Badge --}}
                        <div class="absolute top-2 right-2 px-1.5 py-0.5 bg-black/60 rounded text-[10px] font-medium text-white uppercase" x-text="item.type"></div>
                    </div>
                </template>
            </div>

            {{-- Document List --}}
            <div 
                x-show="!loading && (activeFilter === 'documents' || activeFilter === 'all') && hasVisibleDocuments()"
                class="mt-4 space-y-2"
            >
                <template x-for="item in filteredMedia" :key="item.id">
                    <div 
                        x-show="isDocument(item)"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-x-4"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        class="flex items-center gap-4 p-4 bg-surface-dark rounded-xl border border-border-dark hover:border-primary/30 transition-all duration-200 group"
                    >
                        {{-- File Icon --}}
                        <div class="w-12 h-12 bg-background-dark rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-2xl text-primary" x-text="getFileIcon(item)"></span>
                        </div>

                        {{-- File Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-medium text-sm truncate group-hover:text-primary transition-colors" x-text="item.filename"></p>
                            <div class="flex items-center gap-3 text-xs text-text-secondary mt-1">
                                <span x-text="item.human_readable_size"></span>
                                <span class="w-1 h-1 bg-text-secondary rounded-full"></span>
                                <span x-text="item.created_at_formatted"></span>
                                <span class="px-1.5 py-0.5 bg-surface-dark rounded text-[10px] uppercase" x-text="item.type"></span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2">
                            <button 
                                @click.stop="downloadMedia(item)"
                                class="p-2 hover:bg-white/5 rounded-lg transition-colors"
                                title="Download"
                            >
                                <span class="material-symbols-outlined text-text-secondary hover:text-white">download</span>
                            </button>
                            <button 
                                @click.stop="deleteMedia(item)"
                                class="p-2 hover:bg-red-500/10 rounded-lg transition-colors"
                                title="Delete"
                            >
                                <span class="material-symbols-outlined text-text-secondary hover:text-red-400">delete</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Load More --}}
            <div x-show="hasMorePages" class="flex justify-center mt-6">
                <button 
                    @click="loadMore()"
                    :disabled="loadingMore"
                    class="px-6 py-2.5 bg-surface-dark hover:bg-surface-dark/80 border border-border-dark rounded-lg text-sm font-medium text-text-secondary hover:text-white transition-all duration-200 flex items-center gap-2 disabled:opacity-50"
                >
                    <span x-show="loadingMore" class="w-4 h-4 border-2 border-primary/30 border-t-primary rounded-full animate-spin"></span>
                    <span x-text="loadingMore ? 'Loading...' : 'Load More'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Preview Modal --}}
    <div 
        x-show="previewOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[60] bg-black/95 backdrop-blur-sm flex items-center justify-center"
        @click="closePreview()"
        x-cloak
    >
        {{-- Preview Content --}}
        <div class="relative w-full h-full flex items-center justify-center p-4" @click.stop>
            {{-- Close Button --}}
            <button @click="closePreview()" class="absolute top-4 right-4 z-10 p-2 bg-black/50 hover:bg-black/70 rounded-full transition-colors">
                <span class="material-symbols-outlined text-white text-2xl">close</span>
            </button>

            {{-- Navigation --}}
            <template x-if="previewItem">
                <div class="absolute inset-y-0 left-0 right-0 flex items-center justify-between px-4 pointer-events-none">
                    <button 
                        @click.stop="prevPreview()"
                        class="pointer-events-auto p-2 bg-black/50 hover:bg-black/70 rounded-full transition-colors disabled:opacity-30"
                        :disabled="!hasPrevPreview()"
                    >
                        <span class="material-symbols-outlined text-white text-2xl">chevron_left</span>
                    </button>
                    <button 
                        @click.stop="nextPreview()"
                        class="pointer-events-auto p-2 bg-black/50 hover:bg-black/70 rounded-full transition-colors disabled:opacity-30"
                        :disabled="!hasNextPreview()"
                    >
                        <span class="material-symbols-outlined text-white text-2xl">chevron_right</span>
                    </button>
                </div>
            </template>

            {{-- Image Preview --}}
            <template x-if="previewItem && previewItem.type === 'image'">
                <img 
                    :src="previewItem.url" 
                    :alt="previewItem.filename"
                    class="max-w-full max-h-full object-contain rounded-lg"
                    @error="$event.target.src = '/images/placeholder-image.png'"
                >
            </template>

            {{-- Video Preview --}}
            <template x-if="previewItem && previewItem.type === 'video'">
                <video 
                    :src="previewItem.url" 
                    controls
                    class="max-w-full max-h-full rounded-lg"
                ></video>
            </template>

            {{-- Document Preview --}}
            <template x-if="previewItem && isDocument(previewItem)">
                <div class="bg-surface-dark rounded-2xl p-8 max-w-md w-full text-center">
                    <div class="w-20 h-20 bg-background-dark rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-4xl text-primary" x-text="getFileIcon(previewItem)"></span>
                    </div>
                    <h3 class="text-white font-semibold text-lg mb-2" x-text="previewItem.filename"></h3>
                    <p class="text-text-secondary text-sm mb-6" x-text="previewItem.human_readable_size + ' • ' + previewItem.created_at_formatted"></p>
                    <div class="flex items-center justify-center gap-3">
                        <button 
                            @click="downloadMedia(previewItem)"
                            class="px-6 py-2.5 bg-primary hover:bg-primary/90 text-black font-medium rounded-lg transition-colors flex items-center gap-2"
                        >
                            <span class="material-symbols-outlined">download</span>
                            Download
                        </button>
                        <button 
                            @click="deleteMedia(previewItem)"
                            class="px-6 py-2.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-medium rounded-lg transition-colors flex items-center gap-2"
                        >
                            <span class="material-symbols-outlined">delete</span>
                            Delete
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Preview Info Bar --}}
        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black to-transparent">
            <div class="flex items-center justify-between max-w-4xl mx-auto">
                <div class="text-white">
                    <p class="font-medium" x-text="previewItem?.filename"></p>
                    <p class="text-sm text-white/60" x-text="previewItem ? previewItem.human_readable_size + ' • ' + previewItem.created_at_formatted : ''"></p>
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        @click="downloadMedia(previewItem)"
                        class="p-2 bg-white/10 hover:bg-white/20 rounded-lg transition-colors"
                        title="Download"
                    >
                        <span class="material-symbols-outlined text-white">download</span>
                    </button>
                    <button 
                        @click="deleteMedia(previewItem)"
                        class="p-2 bg-white/10 hover:bg-red-500/30 rounded-lg transition-colors"
                        title="Delete"
                    >
                        <span class="material-symbols-outlined text-white">delete</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div 
        x-show="deleteModalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        x-cloak
    >
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="deleteModalOpen = false"></div>
        <div class="relative bg-surface-dark border border-border-dark rounded-2xl p-6 max-w-sm w-full shadow-2xl">
            <div class="text-center">
                <div class="w-14 h-14 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-3xl text-red-400">delete_forever</span>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Delete Media?</h3>
                <p class="text-text-secondary text-sm mb-6">This action cannot be undone. The file will be permanently deleted.</p>
                <div class="flex items-center gap-3">
                    <button 
                        @click="deleteModalOpen = false"
                        class="flex-1 px-4 py-2.5 bg-background-dark hover:bg-background-dark/80 border border-border-dark rounded-lg text-sm font-medium text-text-secondary hover:text-white transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="confirmDelete()"
                        :disabled="deleting"
                        class="flex-1 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50"
                    >
                        <span x-show="deleting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span x-text="deleting ? 'Deleting...' : 'Delete'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function mediaGallery(conversationId, conversationType) {
    return {
        open: false,
        previewOpen: false,
        deleteModalOpen: false,
        loading: false,
        loadingMore: false,
        deleting: false,
        search: '',
        activeFilter: 'all',
        media: [],
        filteredMedia: [],
        mediaCount: 0,
        currentPage: 1,
        hasMorePages: false,
        previewItem: null,
        itemToDelete: null,
        tabs: [
            { value: 'all', label: 'All', icon: 'apps', count: 0 },
            { value: 'images', label: 'Images', icon: 'image', count: 0 },
            { value: 'videos', label: 'Videos', icon: 'videocam', count: 0 },
            { value: 'documents', label: 'Documents', icon: 'description', count: 0 },
            { value: 'audio', label: 'Audio', icon: 'audio_file', count: 0 },
        ],

        init() {
            this.conversationId = conversationId;
            this.conversationType = conversationType;
        },

        async loadMedia() {
            if (this.loading) return;
            
            this.loading = true;
            this.currentPage = 1;

            try {
                const response = await fetch(`/chat/${this.conversationType}/${this.conversationId}/media?page=1&filter=${this.activeFilter}&search=${encodeURIComponent(this.search)}`);
                const data = await response.json();

                if (response.ok) {
                    this.media = data.data;
                    this.filteredMedia = [...this.media];
                    this.mediaCount = data.total;
                    this.hasMorePages = data.current_page < data.last_page;
                    this.updateTabCounts();
                }
            } catch (error) {
                console.error('Error loading media:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadMore() {
            if (this.loadingMore || !this.hasMorePages) return;

            this.loadingMore = true;
            this.currentPage++;

            try {
                const response = await fetch(`/chat/${this.conversationType}/${this.conversationId}/media?page=${this.currentPage}&filter=${this.activeFilter}&search=${encodeURIComponent(this.search)}`);
                const data = await response.json();

                if (response.ok) {
                    this.media = [...this.media, ...data.data];
                    this.filteredMedia = [...this.media];
                    this.hasMorePages = data.current_page < data.last_page;
                }
            } catch (error) {
                console.error('Error loading more media:', error);
            } finally {
                this.loadingMore = false;
            }
        },

        applyFilters() {
            this.loadMedia();
        },

        updateTabCounts() {
            // In a real implementation, you might want to fetch counts from the backend
            // For now, we'll calculate from the loaded media
            this.tabs[0].count = this.mediaCount;
        },

        isImageOrVideo(item) {
            return item.type === 'image' || item.type === 'video';
        },

        isDocument(item) {
            return item.type === 'document' || item.type === 'audio' || item.type === 'voice';
        },

        hasVisibleMedia() {
            return this.filteredMedia.some(item => this.isImageOrVideo(item));
        },

        hasVisibleDocuments() {
            return this.filteredMedia.some(item => this.isDocument(item));
        },

        getFileIcon(item) {
            const iconMap = {
                'image': 'image',
                'video': 'videocam',
                'audio': 'audio_file',
                'voice': 'mic',
                'document': 'description',
            };
            
            // Check file extension for specific document types
            if (item.type === 'document') {
                const ext = item.filename.split('.').pop().toLowerCase();
                if (['pdf'].includes(ext)) return 'picture_as_pdf';
                if (['doc', 'docx'].includes(ext)) return 'article';
                if (['xls', 'xlsx'].includes(ext)) return 'table_chart';
                if (['ppt', 'pptx'].includes(ext)) return 'slideshow';
                if (['txt'].includes(ext)) return 'text_snippet';
                if (['zip', 'rar', '7z'].includes(ext)) return 'folder_zip';
            }
            
            return iconMap[item.type] || 'insert_drive_file';
        },

        openPreview(item) {
            this.previewItem = item;
            this.previewOpen = true;
            document.body.style.overflow = 'hidden';
        },

        closePreview() {
            this.previewOpen = false;
            this.previewItem = null;
            document.body.style.overflow = '';
        },

        hasPrevPreview() {
            if (!this.previewItem) return false;
            const index = this.getPreviewableItems().findIndex(item => item.id === this.previewItem.id);
            return index > 0;
        },

        hasNextPreview() {
            if (!this.previewItem) return false;
            const items = this.getPreviewableItems();
            const index = items.findIndex(item => item.id === this.previewItem.id);
            return index < items.length - 1;
        },

        prevPreview() {
            const items = this.getPreviewableItems();
            const index = items.findIndex(item => item.id === this.previewItem.id);
            if (index > 0) {
                this.previewItem = items[index - 1];
            }
        },

        nextPreview() {
            const items = this.getPreviewableItems();
            const index = items.findIndex(item => item.id === this.previewItem.id);
            if (index < items.length - 1) {
                this.previewItem = items[index + 1];
            }
        },

        getPreviewableItems() {
            return this.filteredMedia.filter(item => this.isImageOrVideo(item) || this.isDocument(item));
        },

        downloadMedia(item) {
            if (!item) return;
            window.open(`/api/chat-media/${item.id}/download`, '_blank');
        },

        deleteMedia(item) {
            this.itemToDelete = item;
            this.deleteModalOpen = true;
        },

        async confirmDelete() {
            if (!this.itemToDelete) return;

            this.deleting = true;

            try {
                const response = await fetch(`/api/chat-media/${this.itemToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                if (response.ok) {
                    this.media = this.media.filter(m => m.id !== this.itemToDelete.id);
                    this.filteredMedia = this.filteredMedia.filter(m => m.id !== this.itemToDelete.id);
                    this.mediaCount--;
                    
                    if (this.previewOpen && this.previewItem?.id === this.itemToDelete.id) {
                        this.closePreview();
                    }
                    
                    this.updateTabCounts();
                }
            } catch (error) {
                console.error('Error deleting media:', error);
            } finally {
                this.deleting = false;
                this.deleteModalOpen = false;
                this.itemToDelete = null;
            }
        },

        closeGallery() {
            this.open = false;
            this.search = '';
            this.activeFilter = 'all';
        }
    }
}
</script>
@endpush
