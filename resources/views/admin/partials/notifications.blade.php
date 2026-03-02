{{-- Notification Dropdown Component --}}
<div x-data="notifications()" 
     x-init="init()"
     class="relative"
     @click.away="open = false">
    
    {{-- Bell Icon --}}
    <button @click="toggle()" 
            class="relative p-2 text-slate-400 hover:text-white transition">
        <span class="material-symbols-outlined text-xl">notifications</span>
        
        {{-- Badge --}}
        <template x-if="unreadCount > 0">
            <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold"
                  :class="hasUrgent ? 'bg-red-500 text-white animate-pulse' : 'bg-primary text-white'"
                  x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
        </template>
    </button>
    
    {{-- Dropdown --}}
    <div x-show="open" 
         x-transition
         class="absolute right-0 mt-2 w-96 bg-surface-dark rounded-xl border border-slate-700 shadow-2xl z-50 overflow-hidden"
         style="display: none;">
        
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-700 bg-surface-light/30">
            <h3 class="font-bold">Notifications</h3>
            <div class="flex items-center gap-2">
                <button @click="markAllRead()" 
                        x-show="unreadCount > 0"
                        class="text-xs text-primary hover:text-primary/80">
                    Mark all read
                </button>
                <a href="{{ route('admin.notifications.index') }}" class="text-xs text-slate-400 hover:text-white">
                    View all
                </a>
            </div>
        </div>
        
        {{-- Notifications List --}}
        <div class="max-h-96 overflow-y-auto">
            <template x-if="loading">
                <div class="px-4 py-8 text-center text-slate-500">
                    <span class="material-symbols-outlined animate-spin">refresh</span>
                    <p class="mt-2 text-sm">Loading...</p>
                </div>
            </template>
            
            <template x-if="!loading && notifications.length === 0">
                <div class="px-4 py-8 text-center text-slate-500">
                    <span class="material-symbols-outlined text-4xl mb-2 block">notifications_off</span>
                    <p class="text-sm">No notifications</p>
                </div>
            </template>
            
            <template x-for="notification in notifications" :key="notification.id">
                <div :class="{ 'bg-primary/5': !notification.is_read }"
                     class="px-4 py-3 border-b border-slate-800 hover:bg-slate-800/50 transition">
                    <div class="flex items-start gap-3">
                        {{-- Icon --}}
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
                             :class="{
                                 'bg-red-500/20 text-red-400': notification.priority === 'urgent',
                                 'bg-orange-500/20 text-orange-400': notification.priority === 'high',
                                 'bg-blue-500/20 text-blue-400': notification.priority === 'medium',
                                 'bg-slate-700 text-slate-400': notification.priority === 'low',
                             }">
                            <span class="material-symbols-outlined" x-text="notification.icon"></span>
                        </div>
                        
                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-sm truncate" x-text="notification.title"></p>
                                <span x-show="!notification.is_read" class="w-2 h-2 bg-primary rounded-full"></span>
                            </div>
                            <p class="text-sm text-slate-400 line-clamp-2" x-text="notification.message"></p>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-slate-500" x-text="notification.time_ago"></span>
                                <div class="flex items-center gap-2">
                                    <template x-if="notification.action_url">
                                        <a :href="notification.action_url" 
                                           @click="markRead(notification.id)"
                                           class="text-xs text-primary hover:text-primary/80">
                                            View
                                        </a>
                                    </template>
                                    <button @click="markRead(notification.id)"
                                            x-show="!notification.is_read"
                                            class="text-xs text-slate-500 hover:text-white">
                                        Mark read
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function notifications() {
    return {
        open: false,
        loading: true,
        notifications: [],
        unreadCount: 0,
        hasUrgent: false,
        pollInterval: null,
        
        init() {
            this.fetchNotifications();
            // Poll every 30 seconds
            this.pollInterval = setInterval(() => this.fetchUnreadCount(), 30000);
        },
        
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.fetchNotifications();
            }
        },
        
        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("admin.notifications.ajax") }}');
                const data = await response.json();
                this.notifications = data.notifications;
                this.unreadCount = data.unread_count;
                this.hasUrgent = data.notifications.some(n => n.priority === 'urgent' && !n.is_read);
            } catch (error) {
                console.error('Failed to fetch notifications:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async fetchUnreadCount() {
            try {
                const response = await fetch('{{ route("admin.notifications.unread-count") }}');
                const data = await response.json();
                this.unreadCount = data.count;
                this.hasUrgent = data.has_urgent;
            } catch (error) {
                console.error('Failed to fetch unread count:', error);
            }
        },
        
        async markRead(id) {
            try {
                await fetch(`/admin/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                });
                
                const notification = this.notifications.find(n => n.id === id);
                if (notification) {
                    notification.is_read = true;
                }
                this.unreadCount = Math.max(0, this.unreadCount - 1);
            } catch (error) {
                console.error('Failed to mark as read:', error);
            }
        },
        
        async markAllRead() {
            try {
                await fetch('{{ route("admin.notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                });
                
                this.notifications.forEach(n => n.is_read = true);
                this.unreadCount = 0;
            } catch (error) {
                console.error('Failed to mark all as read:', error);
            }
        }
    }
}
</script>
