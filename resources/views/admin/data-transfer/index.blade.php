@extends('admin.layouts.app')

@section('title', 'Data Import/Export')
@section('page_title', 'Data Import/Export')

@section('content')
<div class="space-y-6" x-data="dataTransfer()">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-surface-dark rounded-xl border border-slate-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center text-blue-500">
                    <span class="material-symbols-outlined">group</span>
                </div>
                <div>
                    <div class="text-2xl font-bold">{{ number_format(\App\Models\User::count()) }}</div>
                    <div class="text-xs text-slate-500">Total Users</div>
                </div>
            </div>
        </div>
        <div class="bg-surface-dark rounded-xl border border-slate-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center text-green-500">
                    <span class="material-symbols-outlined">payments</span>
                </div>
                <div>
                    <div class="text-2xl font-bold">{{ number_format(\App\Models\Payment::count()) }}</div>
                    <div class="text-xs text-slate-500">Total Payments</div>
                </div>
            </div>
        </div>
        <div class="bg-surface-dark rounded-xl border border-slate-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-500/20 rounded-lg flex items-center justify-center text-orange-500">
                    <span class="material-symbols-outlined">support_agent</span>
                </div>
                <div>
                    <div class="text-2xl font-bold">{{ number_format(\App\Models\SupportTicket::count()) }}</div>
                    <div class="text-xs text-slate-500">Support Tickets</div>
                </div>
            </div>
        </div>
        <div class="bg-surface-dark rounded-xl border border-slate-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center text-purple-500">
                    <span class="material-symbols-outlined">local_offer</span>
                </div>
                <div>
                    <div class="text-2xl font-bold">{{ number_format(\App\Models\PromoCode::count()) }}</div>
                    <div class="text-xs text-slate-500">Promo Codes</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Export Section --}}
        <div class="bg-surface-dark rounded-xl border border-slate-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-green-500">file_download</span>
                Export Data
            </h3>
            
            <form @submit.prevent="exportData" class="space-y-4">
                {{-- Data Type --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Select Data Type</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($exportableTypes as $key => $config)
                        <label class="flex items-center gap-3 p-3 bg-surface-light rounded-lg border border-slate-700 cursor-pointer hover:border-primary/50 transition"
                               :class="{ 'border-primary bg-primary/10': exportType === '{{ $key }}' }">
                            <input type="radio" name="type" value="{{ $key }}" x-model="exportType" class="sr-only">
                            <span class="material-symbols-outlined {{ $config['icon'] === 'group' ? 'text-blue-500' : ($config['icon'] === 'payments' ? 'text-green-500' : ($config['icon'] === 'support_agent' ? 'text-orange-500' : 'text-purple-500')) }}">
                                {{ $config['icon'] }}
                            </span>
                            <span class="text-sm font-medium">{{ $config['label'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Format --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Export Format</label>
                    <div class="flex gap-2">
                        <label class="flex-1 flex items-center justify-center gap-2 p-3 bg-surface-light rounded-lg border border-slate-700 cursor-pointer hover:border-primary/50 transition"
                               :class="{ 'border-primary bg-primary/10': exportFormat === 'csv' }">
                            <input type="radio" name="format" value="csv" x-model="exportFormat" class="sr-only">
                            <span class="text-sm font-medium">CSV</span>
                        </label>
                        <label class="flex-1 flex items-center justify-center gap-2 p-3 bg-surface-light rounded-lg border border-slate-700 cursor-pointer hover:border-primary/50 transition"
                               :class="{ 'border-primary bg-primary/10': exportFormat === 'excel' }">
                            <input type="radio" name="format" value="excel" x-model="exportFormat" class="sr-only">
                            <span class="text-sm font-medium">Excel</span>
                        </label>
                    </div>
                </div>

                {{-- Date Range --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Date Range (Optional)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" x-model="dateFrom" 
                               class="bg-surface-light border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-primary transition">
                        <input type="date" x-model="dateTo"
                               class="bg-surface-light border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-primary transition">
                    </div>
                </div>

                {{-- Template Download --}}
                <div class="p-3 bg-surface-light rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-400">Need a template?</span>
                        <a :href="`/admin/data-transfer/template/${exportType}`" 
                           class="text-sm text-primary hover:underline flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">download</span>
                            Download Template
                        </a>
                    </div>
                </div>

                <button type="submit" 
                        :disabled="isExporting"
                        class="w-full px-4 py-3 bg-green-500 hover:bg-green-600 disabled:bg-slate-700 text-white rounded-lg font-medium transition flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined" x-show="!isExporting">download</span>
                    <span class="animate-spin" x-show="isExporting">
                        <span class="material-symbols-outlined">refresh</span>
                    </span>
                    <span x-text="isExporting ? 'Exporting...' : 'Export Data'"></span>
                </button>
            </form>
        </div>

        {{-- Import Section --}}
        <div class="bg-surface-dark rounded-xl border border-slate-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">file_upload</span>
                Import Data
            </h3>
            
            <form @submit.prevent="importData" class="space-y-4">
                {{-- Import Type --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Import Type</label>
                    <select x-model="importType" class="w-full bg-surface-light border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-primary transition">
                        <option value="users">Users</option>
                    </select>
                </div>

                {{-- File Upload --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">CSV File</label>
                    <div class="relative">
                        <input type="file" 
                               ref="importFile"
                               @change="handleFileSelect"
                               accept=".csv,.txt"
                               class="hidden">
                        <button type="button" 
                                @click="$refs.importFile.click()"
                                class="w-full p-4 border-2 border-dashed border-slate-700 rounded-lg hover:border-primary/50 transition text-center">
                            <span class="material-symbols-outlined text-3xl text-slate-500 mb-2">upload_file</span>
                            <p class="text-sm text-slate-400" x-text="selectedFile ? selectedFile.name : 'Click to select CSV file'"></p>
                            <p class="text-xs text-slate-600 mt-1">Max 10MB</p>
                        </button>
                    </div>
                </div>

                {{-- Skip Header --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" x-model="skipHeader" id="skipHeader" class="rounded bg-surface-light border-slate-700 text-primary">
                    <label for="skipHeader" class="text-sm text-slate-400">Skip header row</label>
                </div>

                {{-- Preview Area --}}
                <div x-show="importPreview.length > 0" class="border border-slate-700 rounded-lg overflow-hidden">
                    <div class="bg-surface-light px-4 py-2 text-xs text-slate-500 flex justify-between">
                        <span>Preview (First 5 rows)</span>
                        <span x-text="importTotal + ' total rows'"></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-surface-light text-slate-400">
                                <tr>
                                    <th class="px-3 py-2 text-left">Name</th>
                                    <th class="px-3 py-2 text-left">Email</th>
                                    <th class="px-3 py-2 text-left">Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="row in importPreview" :key="row.row">
                                    <tr class="border-t border-slate-800">
                                        <td class="px-3 py-2" x-text="row.name"></td>
                                        <td class="px-3 py-2" x-text="row.email"></td>
                                        <td class="px-3 py-2" x-text="row.phone"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Import Progress --}}
                <div x-show="isImporting" class="space-y-2">
                    <div class="flex justify-between text-xs text-slate-400">
                        <span>Importing...</span>
                        <span x-text="importProgress + '%'"></span>
                    </div>
                    <div class="h-2 bg-surface-light rounded-full overflow-hidden">
                        <div class="h-full bg-primary rounded-full transition-all" :style="{ width: importProgress + '%' }"></div>
                    </div>
                </div>

                {{-- Import Results --}}
                <div x-show="importResult" 
                     :class="importResult?.success ? 'bg-green-500/20 border-green-500/50' : 'bg-red-500/20 border-red-500/50'"
                     class="p-4 rounded-lg border">
                    <p x-text="importResult?.message" class="text-sm"></p>
                </div>

                <div class="flex gap-2">
                    <button type="button" 
                            @click="previewImport"
                            :disabled="!selectedFile || isPreviewing"
                            class="flex-1 px-4 py-3 bg-slate-700 hover:bg-slate-600 disabled:bg-slate-800 text-white rounded-lg font-medium transition">
                        <span x-text="isPreviewing ? 'Previewing...' : 'Preview'"></span>
                    </button>
                    <button type="submit" 
                            :disabled="!selectedFile || isImporting"
                            class="flex-1 px-4 py-3 bg-primary hover:bg-primary/90 disabled:bg-slate-700 text-white rounded-lg font-medium transition">
                        <span x-text="isImporting ? 'Importing...' : 'Import'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Backup/Restore Section --}}
    <div class="bg-surface-dark rounded-xl border border-slate-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-orange-500">archive</span>
                Backup & Restore
            </h3>
            <button @click="showBackupModal = true" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                <span class="material-symbols-outlined">add</span>
                Create Backup
            </button>
        </div>

        {{-- Backups List --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-surface-light text-slate-400 text-sm">
                    <tr>
                        <th class="px-4 py-3 text-left">Filename</th>
                        <th class="px-4 py-3 text-left">Created</th>
                        <th class="px-4 py-3 text-left">Size</th>
                        <th class="px-4 py-3 text-left">Tables</th>
                        <th class="px-4 py-3 text-left">Created By</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($backups as $backup)
                    <tr class="border-t border-slate-800">
                        <td class="px-4 py-3 font-mono text-xs">{{ $backup['filename'] }}</td>
                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($backup['created_at'])->format('M j, Y g:i A') }}</td>
                        <td class="px-4 py-3">{{ number_format($backup['size'] / 1024, 2) }} KB</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 bg-slate-700 rounded text-xs">{{ count($backup['tables']) }} tables</span>
                        </td>
                        <td class="px-4 py-3">{{ $backup['created_by'] }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.data-transfer.backup.download', ['filename' => $backup['filename']]) }}" 
                                   class="p-2 text-slate-400 hover:text-primary transition" title="Download">
                                    <span class="material-symbols-outlined text-sm">download</span>
                                </a>
                                <form action="{{ route('admin.data-transfer.restore') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to restore this backup? This will overwrite current data.')">
                                    @csrf
                                    <input type="hidden" name="filename" value="{{ $backup['filename'] }}">
                                    <button type="submit" class="p-2 text-slate-400 hover:text-orange-500 transition" title="Restore">
                                        <span class="material-symbols-outlined text-sm">restore</span>
                                    </button>
                                </form>
                                <form action="{{ route('admin.data-transfer.backup.delete') }}" method="POST" class="inline" onsubmit="return confirm('Delete this backup?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="filename" value="{{ $backup['filename'] }}">
                                    <button type="submit" class="p-2 text-slate-400 hover:text-red-400 transition" title="Delete">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                            <span class="material-symbols-outlined text-4xl mb-2">inventory_2</span>
                            <p>No backups found</p>
                            <p class="text-sm">Create your first backup to protect your data</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Backup Modal --}}
    <div x-show="showBackupModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div class="absolute inset-0 bg-black/80" @click="showBackupModal = false"></div>
        <div class="relative bg-surface-dark rounded-2xl border border-slate-700 p-6 max-w-md w-full">
            <h3 class="text-lg font-bold mb-4">Create Backup</h3>
            
            <form action="{{ route('admin.data-transfer.backup') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Select Tables</label>
                    <div class="max-h-[200px] overflow-y-auto space-y-2 p-3 bg-surface-light rounded-lg">
                        @foreach($backupTables as $table)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="tables[]" value="{{ $table }}" 
                                   class="rounded bg-surface-dark border-slate-700 text-primary"
                                   {{ in_array($table, ['users', 'payments']) ? 'checked' : '' }}>
                            <span class="text-sm">{{ ucwords(str_replace('_', ' ', $table)) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Notes (Optional)</label>
                    <textarea name="notes" rows="2" 
                              class="w-full bg-surface-light border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary transition"
                              placeholder="Backup notes..."></textarea>
                </div>

                <div class="flex gap-2">
                    <button type="button" @click="showBackupModal = false" 
                            class="flex-1 px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-sm">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium">
                        Create Backup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function dataTransfer() {
    return {
        // Export
        exportType: 'users',
        exportFormat: 'csv',
        dateFrom: '',
        dateTo: '',
        isExporting: false,

        // Import
        importType: 'users',
        selectedFile: null,
        skipHeader: true,
        importPreview: [],
        importTotal: 0,
        isPreviewing: false,
        isImporting: false,
        importProgress: 0,
        importResult: null,

        // Backup
        showBackupModal: false,

        handleFileSelect(event) {
            this.selectedFile = event.target.files[0];
            this.importPreview = [];
            this.importResult = null;
        },

        async exportData() {
            this.isExporting = true;
            
            const params = new URLSearchParams({
                type: this.exportType,
                format: this.exportFormat,
            });
            
            if (this.dateFrom) params.append('date_from', this.dateFrom);
            if (this.dateTo) params.append('date_to', this.dateTo);

            try {
                const response = await fetch(`{{ route('admin.data-transfer.export') }}?${params}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    }
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = response.headers.get('content-disposition')?.split('filename=')[1] || `export.${this.exportFormat}`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();
                    showToast('Export completed successfully!');
                } else {
                    showToast('Export failed', 'error');
                }
            } catch (error) {
                showToast('Export failed: ' + error.message, 'error');
            }

            this.isExporting = false;
        },

        async previewImport() {
            if (!this.selectedFile) return;

            this.isPreviewing = true;
            const formData = new FormData();
            formData.append('type', this.importType);
            formData.append('file', this.selectedFile);
            formData.append('skip_header', this.skipHeader ? '1' : '0');
            formData.append('preview', '1');
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route('admin.data-transfer.import') }}', {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();
                if (result.success) {
                    this.importPreview = result.preview;
                    this.importTotal = result.total;
                }
            } catch (error) {
                showToast('Preview failed: ' + error.message, 'error');
            }

            this.isPreviewing = false;
        },

        async importData() {
            if (!this.selectedFile) return;

            this.isImporting = true;
            this.importProgress = 0;
            this.importResult = null;

            const formData = new FormData();
            formData.append('type', this.importType);
            formData.append('file', this.selectedFile);
            formData.append('skip_header', this.skipHeader ? '1' : '0');
            formData.append('_token', '{{ csrf_token() }}');

            // Simulate progress
            const progressInterval = setInterval(() => {
                if (this.importProgress < 90) {
                    this.importProgress += 5;
                }
            }, 200);

            try {
                const response = await fetch('{{ route('admin.data-transfer.import') }}', {
                    method: 'POST',
                    body: formData,
                });

                clearInterval(progressInterval);
                this.importProgress = 100;

                const result = await response.json();
                this.importResult = result;

                if (result.success) {
                    showToast(result.message);
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showToast(result.message || 'Import failed', 'error');
                }
            } catch (error) {
                clearInterval(progressInterval);
                this.importResult = { success: false, message: 'Import failed: ' + error.message };
                showToast('Import failed: ' + error.message, 'error');
            }

            this.isImporting = false;
        }
    }
}
</script>
@endsection
