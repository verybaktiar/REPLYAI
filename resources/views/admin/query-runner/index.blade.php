@extends('admin.layouts.app')

@section('title', 'Query Runner')
@section('page_title', 'Database Query Runner')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-slate-400">Execute readonly SELECT queries on the database</p>
        <div class="flex items-center gap-2 mt-2">
            <span class="px-2 py-1 bg-green-500/20 text-green-400 text-xs rounded font-semibold">READONLY MODE</span>
            <span class="text-xs text-slate-500">Only SELECT queries are allowed</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6" x-data="queryRunner()">
    {{-- Sidebar: Tables & Predefined Queries --}}
    <div class="lg:col-span-1 space-y-6">
        {{-- Tables List --}}
        <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <h3 class="font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">table</span>
                    Tables
                </h3>
                <button @click="loadTables()" class="text-sm text-primary hover:text-primary/80">
                    <span class="material-symbols-outlined text-lg">refresh</span>
                </button>
            </div>
            <div class="max-h-64 overflow-y-auto p-2" id="tables-list">
                <div x-show="loadingTables" class="p-4 text-center text-slate-500">
                    <span class="material-symbols-outlined animate-spin">refresh</span>
                    <p class="text-sm mt-2">Loading...</p>
                </div>
                <div x-show="!loadingTables && tables.length === 0" class="p-4 text-center text-slate-500 text-sm">
                    Click refresh to load tables
                </div>
                <template x-for="table in tables" :key="table.name">
                    <div class="group">
                        <button @click="showTableSchema(table.name)" 
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-surface-light text-left text-sm transition">
                            <span class="font-mono text-slate-300" x-text="table.name"></span>
                            <span class="text-xs text-slate-500" x-text="table.rows + ' rows'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Predefined Queries --}}
        <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800">
                <h3 class="font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">bookmark</span>
                    Quick Queries
                </h3>
            </div>
            <div class="max-h-64 overflow-y-auto p-2">
                @foreach($predefinedQueries as $key => $query)
                <button @click="loadPredefinedQuery('{{ $key }}', '{{ addslashes($query['query']) }}')" 
                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-surface-light text-sm transition mb-1">
                    <div class="text-slate-300">{{ $query['name'] }}</div>
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Main Query Area --}}
    <div class="lg:col-span-3 space-y-6">
        {{-- Query Editor --}}
        <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <h3 class="font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">code</span>
                    SQL Editor
                </h3>
                <div class="flex items-center gap-2">
                    <button @click="clearQuery()" class="px-3 py-1.5 text-sm text-slate-400 hover:text-white transition">
                        Clear
                    </button>
                    <button @click="executeQuery()" 
                            :disabled="executing || !query.trim()"
                            class="px-4 py-2 bg-primary hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg font-medium text-sm transition flex items-center gap-2">
                        <span x-show="!executing" class="material-symbols-outlined text-lg">play_arrow</span>
                        <span x-show="executing" class="material-symbols-outlined animate-spin">refresh</span>
                        <span x-text="executing ? 'Running...' : 'Execute'"></span>
                    </button>
                </div>
            </div>
            <div class="p-4">
                <textarea x-model="query" 
                          @keydown.ctrl.enter="executeQuery()"
                          placeholder="Enter your SELECT query here...&#10;Example: SELECT * FROM users LIMIT 10"
                          class="w-full h-40 bg-surface-light border border-slate-700 rounded-xl p-4 font-mono text-sm text-slate-300 focus:border-primary focus:outline-none resize-none"
                          spellcheck="false"></textarea>
                <div class="mt-2 text-xs text-slate-500 flex items-center justify-between">
                    <span>Press Ctrl+Enter to execute</span>
                    <span x-show="lastExecutionTime > 0" x-text="'Last execution: ' + lastExecutionTime + 'ms'"></span>
                </div>
            </div>
        </div>

        {{-- Results --}}
        <div x-show="hasExecuted" x-cloak class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <h3 class="font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">table_chart</span>
                        Results
                    </h3>
                    <span x-show="rowCount > 0" 
                          class="px-2 py-1 bg-surface-light rounded text-xs text-slate-400"
                          x-text="rowCount + ' rows'">
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="exportToCSV()" x-show="results.length > 0" class="px-3 py-1.5 text-sm text-slate-400 hover:text-white transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-lg">download</span>
                        Export CSV
                    </button>
                </div>
            </div>

            {{-- Error Message --}}
            <div x-show="error" class="p-4 bg-red-500/10 border-b border-red-500/20">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-red-400">error</span>
                    <div>
                        <p class="text-red-400 font-medium">Query Error</p>
                        <p class="text-red-300/80 text-sm mt-1" x-text="error"></p>
                    </div>
                </div>
            </div>

            {{-- Success Results --}}
            <div x-show="!error && results.length > 0" class="overflow-x-auto max-h-96">
                <table class="w-full text-sm">
                    <thead class="bg-surface-light sticky top-0">
                        <tr>
                            <template x-for="col in columns" :key="col">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-700" x-text="col"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <template x-for="(row, idx) in results" :key="idx">
                            <tr class="hover:bg-surface-light/50">
                                <template x-for="col in columns" :key="col">
                                    <td class="px-4 py-3 text-slate-300 whitespace-nowrap max-w-xs truncate">
                                        <span x-text="formatValue(row[col])"></span>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Empty Result --}}
            <div x-show="!error && results.length === 0 && hasExecuted" class="p-12 text-center">
                <span class="material-symbols-outlined text-5xl text-slate-600 mb-3">inbox</span>
                <p class="text-slate-500">Query executed successfully but returned no results</p>
            </div>
        </div>
    </div>

    {{-- Table Schema Modal --}}
    <div x-show="showSchemaModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/80" @click="showSchemaModal = false"></div>
        <div class="relative bg-surface-dark rounded-2xl border border-slate-700 w-full max-w-3xl max-h-[80vh] overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary">table</span>
                    <h3 class="font-semibold" x-text="currentTable + ' Schema'"></h3>
                    <span class="px-2 py-0.5 bg-surface-light rounded text-xs text-slate-400" x-text="schemaData.rowCount + ' rows'"></span>
                </div>
                <button @click="showSchemaModal = false" class="text-slate-400 hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="overflow-y-auto max-h-[60vh] p-4">
                {{-- Columns --}}
                <h4 class="text-sm font-semibold text-slate-400 mb-3">Columns</h4>
                <table class="w-full text-sm mb-6">
                    <thead class="bg-surface-light">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-slate-400">Field</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-slate-400">Type</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-slate-400">Null</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-slate-400">Key</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-slate-400">Default</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <template x-for="col in schemaData.columns" :key="col.Field">
                            <tr class="hover:bg-surface-light/30">
                                <td class="px-3 py-2 font-mono text-primary" x-text="col.Field"></td>
                                <td class="px-3 py-2 text-slate-400" x-text="col.Type"></td>
                                <td class="px-3 py-2" x-text="col.Null"></td>
                                <td class="px-3 py-2">
                                    <span x-show="col.Key" 
                                          :class="col.Key === 'PRI' ? 'text-yellow-400' : 'text-blue-400'"
                                          x-text="col.Key"></span>
                                </td>
                                <td class="px-3 py-2 text-slate-500" x-text="col.Default || 'NULL'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                {{-- Quick Query Buttons --}}
                <div class="flex gap-2 mt-4">
                    <button @click="setQuery('SELECT * FROM ' + currentTable + ' LIMIT 10'); showSchemaModal = false" 
                            class="px-3 py-2 bg-surface-light hover:bg-slate-700 rounded-lg text-sm transition">
                        SELECT * LIMIT 10
                    </button>
                    <button @click="setQuery('SELECT COUNT(*) as total FROM ' + currentTable); showSchemaModal = false" 
                            class="px-3 py-2 bg-surface-light hover:bg-slate-700 rounded-lg text-sm transition">
                        COUNT
                    </button>
                    <button @click="setQuery('SELECT * FROM ' + currentTable + ' ORDER BY id DESC LIMIT 10'); showSchemaModal = false" 
                            class="px-3 py-2 bg-surface-light hover:bg-slate-700 rounded-lg text-sm transition">
                        LATEST 10
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function queryRunner() {
    return {
        query: '',
        results: [],
        columns: [],
        rowCount: 0,
        lastExecutionTime: 0,
        executing: false,
        hasExecuted: false,
        error: null,
        tables: [],
        loadingTables: false,
        showSchemaModal: false,
        currentTable: '',
        schemaData: { columns: [], indexes: [], rowCount: 0 },

        async loadTables() {
            this.loadingTables = true;
            try {
                const response = await fetch('{{ route("admin.query-runner.tables") }}');
                const data = await response.json();
                if (data.success) {
                    this.tables = data.tables;
                }
            } catch (e) {
                console.error('Failed to load tables:', e);
            }
            this.loadingTables = false;
        },

        async showTableSchema(tableName) {
            this.currentTable = tableName;
            try {
                const response = await fetch('{{ route("admin.query-runner.schema", ["table" => "__table__"]) }}'.replace('__table__', tableName));
                const data = await response.json();
                if (data.success) {
                    this.schemaData = data;
                    this.showSchemaModal = true;
                }
            } catch (e) {
                console.error('Failed to load schema:', e);
            }
        },

        loadPredefinedQuery(key, query) {
            this.query = query;
            this.hasExecuted = false;
            this.error = null;
        },

        setQuery(query) {
            this.query = query;
            this.hasExecuted = false;
            this.error = null;
        },

        clearQuery() {
            this.query = '';
            this.hasExecuted = false;
            this.error = null;
            this.results = [];
            this.columns = [];
        },

        async executeQuery() {
            if (!this.query.trim() || this.executing) return;

            this.executing = true;
            this.hasExecuted = true;
            this.error = null;

            try {
                const response = await fetch('{{ route("admin.query-runner.execute") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ query: this.query })
                });

                const data = await response.json();

                if (data.success) {
                    this.results = data.results;
                    this.columns = data.columns;
                    this.rowCount = data.rowCount;
                    this.lastExecutionTime = data.executionTime;
                } else {
                    this.error = data.error || 'Unknown error occurred';
                    this.results = [];
                    this.columns = [];
                }
            } catch (e) {
                this.error = 'Failed to execute query: ' + e.message;
                this.results = [];
                this.columns = [];
            }

            this.executing = false;
        },

        formatValue(value) {
            if (value === null) return 'NULL';
            if (typeof value === 'boolean') return value ? 'true' : 'false';
            if (typeof value === 'object') return JSON.stringify(value);
            return String(value);
        },

        exportToCSV() {
            if (this.results.length === 0) return;

            const headers = this.columns.join(',');
            const rows = this.results.map(row => {
                return this.columns.map(col => {
                    let val = row[col];
                    if (val === null) return '';
                    val = String(val).replace(/"/g, '""');
                    if (val.includes(',') || val.includes('"') || val.includes('\n')) {
                        val = '"' + val + '"';
                    }
                    return val;
                }).join(',');
            });

            const csv = [headers, ...rows].join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'query_results_' + new Date().toISOString().slice(0, 19).replace(/:/g, '-') + '.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    }
}
</script>

@endsection
