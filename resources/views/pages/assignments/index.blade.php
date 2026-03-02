@extends('layouts.dark')

@section('title', 'My Assignments')

@section('content')
<div x-data="myAssignments()" x-init="init()" class="h-full flex flex-col">
    <!-- Header -->
    <div class="h-16 flex-shrink-0 flex items-center justify-between px-6 border-b border-gray-800 bg-gray-950/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-white">My Assignments</h1>
                <p class="text-xs text-gray-500">Conversations assigned to you</p>
            </div>
        </div>

        @include('components.page-help', [
            'title' => 'Tugas Saya',
            'description' => 'Kelola percakapan yang sedang ditangani oleh Anda.',
            'tips' => ['Lihat daftar percakapan yang Anda takeover', 'Balas pesan dengan cepat', 'Handover ke bot jika sudah selesai', 'Monitor waktu respons Anda']
        ])

        <!-- Stats -->
        </div>

        <!-- Stats -->
        <div class="flex items-center gap-4">
            <div class="text-right">
                <span class="text-2xl font-bold text-white" x-text="stats.total"></span>
                <p class="text-xs text-gray-500">Total Assigned</p>
            </div>
            <div class="w-px h-10 bg-gray-800"></div>
            <div class="text-right">
                <span class="text-2xl font-bold text-green-400" x-text="stats.active"></span>
                <p class="text-xs text-gray-500">Active</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex-shrink-0 px-6 py-4 border-b border-gray-800/50">
        <div class="flex items-center gap-4">
            <!-- Search -->
            <div class="relative flex-1 max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input 
                    x-model="filters.search"
                    @input.debounce.300ms="loadAssignments()"
                    type="text" 
                    placeholder="Search conversations..."
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-950 border border-gray-800 rounded-lg text-sm text-gray-300 placeholder-gray-600 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30"
                >
            </div>

            <!-- Type Filter -->
            <select 
                x-model="filters.type"
                @change="loadAssignments()"
                class="px-4 py-2.5 bg-gray-950 border border-gray-800 rounded-lg text-sm text-gray-300 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30"
            >
                <option value="">All Channels</option>
                <option value="instagram">Instagram</option>
                <option value="whatsapp">WhatsApp</option>
                <option value="web">Web Chat</option>
            </select>

            <!-- Status Filter -->
            <select 
                x-model="filters.status"
                @change="loadAssignments()"
                class="px-4 py-2.5 bg-gray-950 border border-gray-800 rounded-lg text-sm text-gray-300 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30"
            >
                <option value="active">Active</option>
                <option value="resolved">Resolved</option>
                <option value="transferred">Transferred</option>
            </select>

            <!-- Refresh Button -->
            <button 
                @click="loadAssignments()"
                :disabled="loading"
                class="p-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors disabled:opacity-50"
            >
                <svg :class="{ 'animate-spin': loading }" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Assignments List -->
    <div class="flex-1 overflow-y-auto p-6">
        <!-- Loading State -->
        <div x-show="loading" x-cloak class="flex items-center justify-center h-64">
            <div class="text-center">
                <svg class="animate-spin w-8 h-8 mx-auto text-blue-500 mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm text-gray-500">Loading assignments...</span>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && assignments.length === 0" x-cloak class="flex items-center justify-center h-64">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-800/50 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-400 mb-1">No assignments found</h3>
                <p class="text-sm text-gray-600">You don't have any active conversations assigned to you.</p>
            </div>
        </div>

        <!-- Assignments Grid -->
        <div x-show="!loading && assignments.length > 0" x-cloak class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
            <template x-for="assignment in assignments" :key="assignment.id">
                <div class="group bg-gray-900/50 border border-gray-800 rounded-xl p-4 hover:border-gray-700 hover:bg-gray-900 transition-all">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div class="w-10 h-10 rounded-full bg-gray-800 border border-gray-700 flex items-center justify-center text-gray-400 font-bold text-sm">
                                    <span x-text="getInitials(assignment.conversation?.display_name || 'U')"></span>
                                </div>
                                <!-- Channel Icon -->
                                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-gray-900 border border-gray-800 flex items-center justify-center">
                                    <template x-if="assignment.conversation_type === 'instagram'">
                                        <svg class="w-2.5 h-2.5 text-pink-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                        </svg>
                                    </template>
                                    <template x-if="assignment.conversation_type === 'whatsapp'">
                                        <svg class="w-2.5 h-2.5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                        </svg>
                                    </template>
                                    <template x-if="assignment.conversation_type === 'web'">
                                        <svg class="w-2.5 h-2.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-white" x-text="assignment.conversation?.display_name || 'Unknown'"></h3>
                                <p class="text-xs text-gray-500" x-text="formatDate(assignment.assigned_at)"></p>
                            </div>
                        </div>
                        
                        <!-- Status Badge -->
                        <span :class="getStatusClass(assignment.status)" class="px-2 py-0.5 rounded-full text-[10px] font-medium capitalize" x-text="assignment.status"></span>
                    </div>

                    <!-- Last Message -->
                    <div class="mb-4 p-3 bg-gray-950/50 rounded-lg">
                        <p class="text-sm text-gray-400 truncate" x-text="assignment.conversation?.last_message || 'No messages yet'"></p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <a 
                            :href="getConversationUrl(assignment)"
                            class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-lg transition-colors text-center"
                        >
                            Open Conversation
                        </a>
                        <button 
                            @click="unassign(assignment)"
                            :disabled="assignment.processing"
                            class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors disabled:opacity-50"
                            title="Unassign"
                        >
                            <svg x-show="!assignment.processing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <svg x-show="assignment.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Pagination -->
        <div x-show="pagination.last_page > 1" x-cloak class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Showing <span x-text="(pagination.current_page - 1) * pagination.per_page + 1"></span> - 
                <span x-text="Math.min(pagination.current_page * pagination.per_page, pagination.total)"></span> of 
                <span x-text="pagination.total"></span>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    @click="changePage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="px-3 py-1.5 bg-gray-800 text-gray-300 rounded-lg text-sm hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Previous
                </button>
                <span class="text-sm text-gray-500 px-2">
                    Page <span x-text="pagination.current_page"></span> of <span x-text="pagination.last_page"></span>
                </span>
                <button 
                    @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-3 py-1.5 bg-gray-800 text-gray-300 rounded-lg text-sm hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function myAssignments() {
    return {
        loading: false,
        assignments: [],
        stats: {
            total: 0,
            active: 0,
            resolved: 0
        },
        filters: {
            search: '',
            type: '',
            status: 'active'
        },
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 20,
            total: 0
        },

        init() {
            this.loadAssignments();
        },

        loadAssignments() {
            this.loading = true;
            const params = new URLSearchParams({
                page: this.pagination.current_page,
                type: this.filters.type,
                status: this.filters.status
            });

            fetch(`/api/my-assignments?${params}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.assignments = data.data.map(a => ({ ...a, processing: false }));
                        this.pagination = data.pagination;
                        this.updateStats();
                    }
                    this.loading = false;
                })
                .catch(err => {
                    console.error('Failed to load assignments:', err);
                    this.loading = false;
                });
        },

        updateStats() {
            this.stats.total = this.pagination.total;
            this.stats.active = this.assignments.filter(a => a.status === 'active').length;
        },

        unassign(assignment) {
            assignment.processing = true;
            fetch(`/api/chat/${assignment.conversation_type}/${assignment.conversation_id}/assign`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.assignments = this.assignments.filter(a => a.id !== assignment.id);
                    this.updateStats();
                    this.showToast('Conversation unassigned', 'success');
                } else {
                    this.showToast(data.error || 'Failed to unassign', 'error');
                }
                assignment.processing = false;
            })
            .catch(err => {
                console.error('Unassign failed:', err);
                this.showToast('Failed to unassign conversation', 'error');
                assignment.processing = false;
            });
        },

        getConversationUrl(assignment) {
            if (assignment.conversation_type === 'instagram') {
                return `/inbox?conversation_id=${assignment.conversation_id}`;
            } else if (assignment.conversation_type === 'whatsapp') {
                return `/whatsapp/inbox?phone=${assignment.conversation_id}`;
            } else if (assignment.conversation_type === 'web') {
                return `/web-widgets?conversation_id=${assignment.conversation_id}`;
            }
            return '#';
        },

        getStatusClass(status) {
            const classes = {
                'active': 'bg-green-500/20 text-green-400 border border-green-500/30',
                'resolved': 'bg-gray-500/20 text-gray-400 border border-gray-500/30',
                'transferred': 'bg-amber-500/20 text-amber-400 border border-amber-500/30'
            };
            return classes[status] || classes['active'];
        },

        getInitials(name) {
            return name.split(' ').map(w => w[0]?.toUpperCase()).filter(Boolean).slice(0, 2).join('');
        },

        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins}m ago`;
            if (diffHours < 24) return `${diffHours}h ago`;
            if (diffDays < 7) return `${diffDays}d ago`;
            return date.toLocaleDateString();
        },

        changePage(page) {
            if (page < 1 || page > this.pagination.last_page) return;
            this.pagination.current_page = page;
            this.loadAssignments();
        },

        showToast(message, type = 'info') {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message, type }
            }));
        }
    };
}
</script>
@endsection
