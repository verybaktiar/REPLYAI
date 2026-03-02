{{-- Command Palette (Cmd+K) - Global Search & Navigation --}}
<div x-data="commandPalette()" 
     x-show="isOpen" 
     x-cloak
     @keydown.window.cmd.k.prevent="toggle()"
     @keydown.window.ctrl.k.prevent="toggle()"
     @keydown.escape.window="close()"
     class="fixed inset-0 z-50 flex items-start justify-center pt-[20vh]"
     style="display: none;">
    
    {{-- Backdrop --}}
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="close()"
         class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
    
    {{-- Modal --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.stop
         class="relative w-full max-w-2xl mx-4 bg-surface-dark rounded-2xl border border-slate-700 shadow-2xl overflow-hidden">
        
        {{-- Search Input --}}
        <div class="flex items-center gap-3 px-4 py-4 border-b border-slate-700">
            <span class="material-symbols-outlined text-slate-400">search</span>
            <input 
                type="text" 
                x-model="query"
                @keydown.arrow-down.prevent="next()"
                @keydown.arrow-up.prevent="prev()"
                @keydown.enter.prevent="select()"
                placeholder="Search commands, pages, or users..."
                class="flex-1 bg-transparent border-none outline-none text-white placeholder-slate-500 text-lg"
                x-ref="searchInput"
                autofocus>
            <kbd class="hidden sm:flex items-center gap-1 px-2 py-1 bg-slate-700 rounded text-xs text-slate-400">
                <span>ESC</span>
            </kbd>
        </div>
        
        {{-- Results --}}
        <div class="max-h-[60vh] overflow-y-auto py-2" x-ref="resultsContainer">
            <template x-for="(group, groupIndex) in filteredGroups" :key="groupIndex">
                <div x-show="group.items.length > 0">
                    <div class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider" x-text="group.name"></div>
                    <template x-for="(item, itemIndex) in group.items" :key="item.id">
                        <a :href="item.url"
                           @click.prevent="navigate(item.url)"
                           @mouseenter="selectedIndex = getGlobalIndex(groupIndex, itemIndex)"
                           :class="{ 'bg-primary/20 border-l-4 border-primary': isSelected(groupIndex, itemIndex), 'border-l-4 border-transparent': !isSelected(groupIndex, itemIndex) }"
                           class="flex items-center gap-3 px-4 py-3 mx-2 rounded-lg hover:bg-slate-800/50 transition cursor-pointer">
                            <span class="material-symbols-outlined text-slate-400" x-text="item.icon"></span>
                            <div class="flex-1">
                                <div class="font-medium text-white" x-text="highlightMatch(item.name)"></div>
                                <div class="text-sm text-slate-400" x-text="item.description" x-show="item.description"></div>
                            </div>
                            <kbd x-show="item.shortcut" class="hidden sm:block px-2 py-1 bg-slate-700 rounded text-xs text-slate-400" x-text="item.shortcut"></kbd>
                        </a>
                    </template>
                </div>
            </template>
            
            {{-- Empty State --}}
            <div x-show="isEmpty" class="px-4 py-12 text-center text-slate-500">
                <span class="material-symbols-outlined text-4xl mb-2 block">search_off</span>
                <p>No results found for "<span x-text="query"></span>"</p>
            </div>
        </div>
        
        {{-- Footer --}}
        <div class="flex items-center justify-between px-4 py-3 border-t border-slate-700 bg-surface-light/30 text-xs text-slate-400">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-slate-700 rounded">↑</kbd> <kbd class="px-1.5 py-0.5 bg-slate-700 rounded">↓</kbd> to navigate</span>
                <span class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-slate-700 rounded">↵</kbd> to select</span>
            </div>
            <span x-text="filteredCount + ' results'"></span>
        </div>
    </div>
</div>

<script>
function commandPalette() {
    return {
        isOpen: false,
        query: '',
        selectedIndex: 0,
        
        // Define all available commands
        groups: [
            {
                name: 'Navigation',
                items: [
                    { id: 'dashboard', name: 'Dashboard', icon: 'dashboard', url: '{{ route("admin.dashboard") }}', description: 'Go to main dashboard' },
                    { id: 'users', name: 'Users', icon: 'group', url: '{{ route("admin.users.index") }}', description: 'Manage users' },
                    { id: 'payments', name: 'Payments', icon: 'payments', url: '{{ route("admin.payments.index") }}', description: 'View and approve payments' },
                    { id: 'plans', name: 'Plans', icon: 'package_2', url: '{{ route("admin.plans.index") }}', description: 'Manage subscription plans' },
                    { id: 'support', name: 'Support Tickets', icon: 'support_agent', url: '{{ route("admin.support.index") }}', description: 'Handle support tickets' },
                ]
            },
            {
                name: 'System',
                items: [
                    { id: 'settings', name: 'Settings', icon: 'settings', url: '{{ route("admin.settings.index") }}', description: 'System configuration' },
                    { id: 'security', name: 'Security Center', icon: 'security', url: '{{ route("admin.security.index") }}', description: 'Security dashboard' },
                    { id: 'activity-logs', name: 'Activity Logs', icon: 'history', url: '{{ route("admin.activity-logs.index") }}', description: 'View activity logs' },
                    { id: 'system-health', name: 'System Health', icon: 'monitor_heart', url: '{{ route("admin.system-health.index") }}', description: 'Monitor system health' },
                    { id: 'system-alerts', name: 'System Alerts', icon: 'notification_important', url: '{{ route("admin.system-alerts.index") }}', description: 'Configure system alerts' },
                    { id: 'devices', name: 'Devices', icon: 'devices', url: '{{ route("admin.devices.index") }}', description: 'Manage WA & IG devices' },
                    { id: 'schedule', name: 'Schedule Monitor', icon: 'schedule', url: '{{ route("admin.schedule.index") }}', description: 'Scheduled tasks & cron jobs' },
                    { id: 'ai-providers', name: 'AI Providers', icon: 'psychology', url: '{{ route("admin.ai-providers.index") }}', description: 'Monitor AI providers & failover' },
                    { id: 'api-monitor', name: 'API Monitor', icon: 'speed', url: '{{ route("admin.api-monitor.index") }}', description: 'Monitor API usage and rate limits' },
                    { id: 'failed-jobs', name: 'Failed Jobs', icon: 'error', url: '{{ route("admin.failed-jobs.index") }}', description: 'Queue failed jobs' },
                    { id: 'webhook-logs', name: 'Webhook Logs', icon: 'webhook', url: '{{ route("admin.webhook-logs.index") }}', description: 'Integration & webhook logs' },
                    { id: 'maintenance-mode', name: 'Maintenance Mode', icon: 'construction', url: '{{ route("admin.maintenance-mode.index") }}', description: 'Enable/disable maintenance mode' },
                    { id: 'data-transfer', name: 'Data Transfer', icon: 'sync_alt', url: '{{ route("admin.data-transfer.index") }}', description: 'Import/Export data and backups' },
                    { id: 'query-runner', name: 'Query Runner', icon: 'database', url: '{{ route("admin.query-runner.index") }}', description: 'Database query runner (readonly)' },
                    { id: 'templates', name: 'Message Templates', icon: 'description', url: '{{ route("admin.templates.index") }}', description: 'Manage message templates' },
                ]
            },
            {
                name: 'Analytics',
                items: [
                    { id: 'revenue', name: 'Revenue', icon: 'payments', url: '{{ route("admin.revenue.index") }}', description: 'Revenue analytics' },
                    { id: 'stats', name: 'Platform Stats', icon: 'bar_chart', url: '{{ route("admin.stats.index") }}', description: 'Platform statistics' },
                    { id: 'user-analytics', name: 'User Analytics', icon: 'person_search', url: '{{ route("admin.user-analytics.index") }}', description: 'Advanced user analytics' },
                ]
            },
            {
                name: 'Actions',
                items: [
                    { id: 'clear-cache', name: 'Clear Cache', icon: 'cleaning_services', url: '{{ route("admin.maintenance.clear-cache") }}', description: 'Clear application cache' },
                    { id: 'broadcast', name: 'Send Broadcast', icon: 'campaign', url: '{{ route("admin.broadcast.index") }}', description: 'Send announcement' },
                    { id: 'create-user', name: 'Create User', icon: 'person_add', url: '{{ route("admin.users.create") }}', description: 'Create new user' },
                ]
            }
        ],
        
        get filteredGroups() {
            if (!this.query) return this.groups;
            
            const q = this.query.toLowerCase();
            return this.groups.map(group => ({
                name: group.name,
                items: group.items.filter(item => 
                    item.name.toLowerCase().includes(q) || 
                    (item.description && item.description.toLowerCase().includes(q))
                )
            })).filter(group => group.items.length > 0);
        },
        
        get filteredCount() {
            return this.filteredGroups.reduce((sum, group) => sum + group.items.length, 0);
        },
        
        get isEmpty() {
            return this.filteredCount === 0 && this.query.length > 0;
        },
        
        toggle() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.$nextTick(() => {
                    this.$refs.searchInput.focus();
                });
            }
        },
        
        close() {
            this.isOpen = false;
            this.query = '';
            this.selectedIndex = 0;
        },
        
        next() {
            const max = this.filteredCount - 1;
            this.selectedIndex = this.selectedIndex >= max ? 0 : this.selectedIndex + 1;
        },
        
        prev() {
            const max = this.filteredCount - 1;
            this.selectedIndex = this.selectedIndex <= 0 ? max : this.selectedIndex - 1;
        },
        
        select() {
            let currentIndex = 0;
            for (const group of this.filteredGroups) {
                for (const item of group.items) {
                    if (currentIndex === this.selectedIndex) {
                        this.navigate(item.url);
                        return;
                    }
                    currentIndex++;
                }
            }
        },
        
        navigate(url) {
            window.location.href = url;
        },
        
        getGlobalIndex(groupIndex, itemIndex) {
            let index = 0;
            for (let i = 0; i < groupIndex; i++) {
                index += this.filteredGroups[i].items.length;
            }
            return index + itemIndex;
        },
        
        isSelected(groupIndex, itemIndex) {
            return this.getGlobalIndex(groupIndex, itemIndex) === this.selectedIndex;
        },
        
        highlightMatch(text) {
            if (!this.query) return text;
            const regex = new RegExp(`(${this.query})`, 'gi');
            return text.replace(regex, '<span class="text-primary font-bold">$1</span>');
        }
    }
}
</script>
