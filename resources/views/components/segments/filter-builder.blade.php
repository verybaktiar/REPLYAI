{{-- Filter Builder Component for Contact Segments --}}
<div class="flex flex-col gap-5" x-data="filterBuilder()">
    
    <!-- Platform Filter -->
    <div>
        <label class="block text-sm font-medium text-text-secondary mb-2">Platform</label>
        <div class="flex flex-wrap gap-2">
            <label class="cursor-pointer">
                <input type="radio" x-model="filters.platform" value="both" class="sr-only peer">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border-dark text-sm transition-colors peer-checked:bg-primary peer-checked:border-primary peer-checked:text-white text-text-secondary hover:bg-white/5">
                    <span class="material-symbols-outlined text-sm">devices</span>
                    Semua
                </span>
            </label>
            <label class="cursor-pointer">
                <input type="radio" x-model="filters.platform" value="whatsapp" class="sr-only peer">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border-dark text-sm transition-colors peer-checked:bg-green-500 peer-checked:border-green-500 peer-checked:text-white text-text-secondary hover:bg-white/5">
                    <span class="material-symbols-outlined text-sm">chat</span>
                    WhatsApp
                </span>
            </label>
            <label class="cursor-pointer">
                <input type="radio" x-model="filters.platform" value="instagram" class="sr-only peer">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border-dark text-sm transition-colors peer-checked:bg-pink-500 peer-checked:border-pink-500 peer-checked:text-white text-text-secondary hover:bg-white/5">
                    <span class="material-symbols-outlined text-sm">photo_camera</span>
                    Instagram
                </span>
            </label>
        </div>
    </div>

    <!-- Tags Filter -->
    @if(!empty($tags) && count($tags) > 0)
    <div>
        <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium text-text-secondary">Tags</label>
            <span class="text-xs text-text-secondary" x-show="filters.tags.length > 0" x-text="filters.tags.length + ' dipilih'" x-cloak></span>
        </div>
        <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto custom-scrollbar p-1">
            @foreach($tags as $tag)
            <label class="cursor-pointer">
                <input type="checkbox" x-model="filters.tags" value="{{ $tag }}" class="sr-only peer">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs transition-colors peer-checked:bg-purple-500/30 peer-checked:border-purple-500 peer-checked:text-purple-300 border border-border-dark text-text-secondary hover:bg-white/5">
                    <span class="material-symbols-outlined text-xs">label</span>
                    {{ $tag }}
                </span>
            </label>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Last Active Filter -->
    <div>
        <label class="block text-sm font-medium text-text-secondary mb-2">Aktivitas Terakhir</label>
        <div class="flex items-center gap-3">
            <span class="text-sm text-text-secondary">Dalam</span>
            <input type="number" x-model.number="filters.last_active_days" min="1" max="365"
                   class="w-20 bg-background-dark border border-border-dark rounded-lg px-3 py-1.5 text-white text-sm text-center focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary"
                   placeholder="30">
            <span class="text-sm text-text-secondary">hari terakhir</span>
            <button x-show="filters.last_active_days" @click="filters.last_active_days = null" 
                    class="p-1 text-text-secondary hover:text-red-400 transition-colors" x-cloak>
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    </div>

    <!-- Message Count Range -->
    <div>
        <label class="block text-sm font-medium text-text-secondary mb-2">Jumlah Pesan</label>
        <div class="flex items-center gap-3">
            <input type="number" x-model.number="filters.message_count_min" min="0" 
                   class="w-24 bg-background-dark border border-border-dark rounded-lg px-3 py-1.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary"
                   placeholder="Min">
            <span class="text-text-secondary">-</span>
            <input type="number" x-model.number="filters.message_count_max" min="0" 
                   class="w-24 bg-background-dark border border-border-dark rounded-lg px-3 py-1.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary"
                   placeholder="Max">
            <span class="text-sm text-text-secondary">pesan</span>
        </div>
        <p class="text-xs text-text-secondary mt-1">Kosongkan untuk tidak membatasi</p>
    </div>

    <!-- Custom Fields Filter (if available) -->
    @if(!empty($customFields) && count($customFields) > 0)
    <div>
        <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium text-text-secondary">Custom Fields</label>
            <button @click="addCustomFieldFilter" class="text-xs text-primary hover:text-white flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">add</span>
                Tambah
            </button>
        </div>
        <div class="space-y-2">
            <template x-for="(field, index) in filters.custom_fields" :key="index">
                <div class="flex items-center gap-2 p-2 bg-background-dark rounded-lg border border-border-dark">
                    <select x-model="field.field_id" class="bg-surface-dark border border-border-dark rounded px-2 py-1 text-sm text-white flex-1">
                        <option value="">Pilih field...</option>
                        @foreach($customFields as $cf)
                        <option value="{{ $cf->id }}">{{ $cf->name }}</option>
                        @endforeach
                    </select>
                    <select x-model="field.operator" class="bg-surface-dark border border-border-dark rounded px-2 py-1 text-sm text-white w-24">
                        <option value="equals">=</option>
                        <option value="contains">contains</option>
                        <option value="greater">&gt;</option>
                        <option value="less">&lt;</option>
                    </select>
                    <input type="text" x-model="field.value" 
                           class="bg-surface-dark border border-border-dark rounded px-2 py-1 text-sm text-white flex-1"
                           placeholder="Nilai">
                    <button @click="removeCustomFieldFilter(index)" class="p-1 text-text-secondary hover:text-red-400">
                        <span class="material-symbols-outlined text-sm">delete</span>
                    </button>
                </div>
            </template>
        </div>
        <div x-show="filters.custom_fields.length === 0" class="text-sm text-text-secondary italic">
            Tidak ada custom field filter
        </div>
    </div>
    @endif

    <!-- Active Filters Summary -->
    <div x-show="hasActiveFilters" x-collapse class="pt-4 border-t border-border-dark">
        <label class="block text-sm font-medium text-text-secondary mb-2">Filter Aktif</label>
        <div class="flex flex-wrap gap-2">
            <template x-if="filters.platform !== 'both'">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-blue-500/20 text-blue-400 border border-blue-500/30">
                    <span class="material-symbols-outlined text-xs" x-text="filters.platform === 'whatsapp' ? 'chat' : 'photo_camera'"></span>
                    <span x-text="filters.platform === 'whatsapp' ? 'WhatsApp' : 'Instagram'"></span>
                    <button @click="filters.platform = 'both'" class="ml-1 hover:text-white">
                        <span class="material-symbols-outlined text-xs">close</span>
                    </button>
                </span>
            </template>
            
            <template x-for="tag in filters.tags" :key="tag">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-purple-500/20 text-purple-400 border border-purple-500/30">
                    <span class="material-symbols-outlined text-xs">label</span>
                    <span x-text="tag"></span>
                    <button @click="filters.tags = filters.tags.filter(t => t !== tag)" class="ml-1 hover:text-white">
                        <span class="material-symbols-outlined text-xs">close</span>
                    </button>
                </span>
            </template>
            
            <template x-if="filters.last_active_days">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-amber-500/20 text-amber-400 border border-amber-500/30">
                    <span class="material-symbols-outlined text-xs">schedule</span>
                    <span x-text="filters.last_active_days + ' hari terakhir'"></span>
                    <button @click="filters.last_active_days = null" class="ml-1 hover:text-white">
                        <span class="material-symbols-outlined text-xs">close</span>
                    </button>
                </span>
            </template>
            
            <template x-if="filters.message_count_min !== null || filters.message_count_max !== null">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    <span class="material-symbols-outlined text-xs">chat_bubble</span>
                    <span x-text="(filters.message_count_min || 0) + ' - ' + (filters.message_count_max || '∞') + ' pesan'"></span>
                    <button @click="filters.message_count_min = null; filters.message_count_max = null" class="ml-1 hover:text-white">
                        <span class="material-symbols-outlined text-xs">close</span>
                    </button>
                </span>
            </template>
        </div>
    </div>

    <!-- Reset Button -->
    <button x-show="hasActiveFilters" @click="resetFilters" 
            class="self-start text-sm text-text-secondary hover:text-white flex items-center gap-1 transition-colors" x-cloak>
        <span class="material-symbols-outlined text-sm">restart_alt</span>
        Reset Filter
    </button>

</div>

<script>
function filterBuilder() {
    return {
        filters: this.form?.filters || {
            platform: 'both',
            tags: [],
            last_active_days: null,
            message_count_min: null,
            message_count_max: null,
            custom_fields: []
        },
        
        get hasActiveFilters() {
            const f = this.filters;
            return f.platform !== 'both' || 
                   (f.tags && f.tags.length > 0) || 
                   f.last_active_days !== null || 
                   f.message_count_min !== null || 
                   f.message_count_max !== null ||
                   (f.custom_fields && f.custom_fields.length > 0);
        },
        
        init() {
            // Watch for changes and sync with parent form
            this.$watch('filters', (value) => {
                if (this.form) {
                    this.form.filters = value;
                }
            }, { deep: true });
        },
        
        addCustomFieldFilter() {
            if (!this.filters.custom_fields) {
                this.filters.custom_fields = [];
            }
            this.filters.custom_fields.push({
                field_id: '',
                operator: 'equals',
                value: ''
            });
        },
        
        removeCustomFieldFilter(index) {
            if (this.filters.custom_fields) {
                this.filters.custom_fields.splice(index, 1);
            }
        },
        
        resetFilters() {
            this.filters = {
                platform: 'both',
                tags: [],
                last_active_days: null,
                message_count_min: null,
                message_count_max: null,
                custom_fields: []
            };
        }
    }
}
</script>
