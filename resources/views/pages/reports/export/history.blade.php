@extends('layouts.dark')

@section('title', 'Export History')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Export History</h1>
            <p class="text-slate-400 text-sm">View and download your previous report exports</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.export') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-dark border border-slate-700 text-slate-300 rounded-lg hover:bg-slate-800 transition-all text-sm">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Back to Export
            </a>
        </div>
    </div>

    <!-- Export History Table -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
        <div class="p-6 border-b border-slate-800">
            <h2 class="font-semibold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">history</span>
                Recent Exports
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-800/50 text-[10px] uppercase font-black text-slate-500 tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Report Type</th>
                        <th class="px-6 py-4">Format</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <tr class="hover:bg-white/[0.02] transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-400">{{ now()->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-white">Analytics Overview</td>
                        <td class="px-6 py-4"><span class="px-2 py-1 bg-red-500/10 text-red-400 text-xs rounded">PDF</span></td>
                        <td class="px-6 py-4"><span class="px-2 py-1 bg-green-500/10 text-green-400 text-xs rounded">Completed</span></td>
                        <td class="px-6 py-4">
                            <button class="text-primary hover:underline text-sm">Download</button>
                        </td>
                    </tr>
                    <tr class="hover:bg-white/[0.02] transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-400">{{ now()->subDay()->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-white">Conversation Report</td>
                        <td class="px-6 py-4"><span class="px-2 py-1 bg-green-500/10 text-green-400 text-xs rounded">CSV</span></td>
                        <td class="px-6 py-4"><span class="px-2 py-1 bg-green-500/10 text-green-400 text-xs rounded">Completed</span></td>
                        <td class="px-6 py-4">
                            <button class="text-primary hover:underline text-sm">Download</button>
                        </td>
                    </tr>
                    <tr class="hover:bg-white/[0.02] transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-400">{{ now()->subDays(2)->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-white">AI Performance</td>
                        <td class="px-6 py-4"><span class="px-2 py-1 bg-blue-500/10 text-blue-400 text-xs rounded">Excel</span></td>
                        <td class="px-6 py-4"><span class="px-2 py-1 bg-yellow-500/10 text-yellow-400 text-xs rounded">Processing</span></td>
                        <td class="px-6 py-4">
                            <span class="text-slate-500 text-sm">-</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty State (Hidden by default, shown when no exports) -->
    <div class="hidden bg-surface-dark rounded-2xl border border-slate-800 p-12 text-center">
        <span class="material-symbols-outlined text-6xl text-slate-600 mb-4">folder_open</span>
        <h3 class="text-lg font-semibold text-white mb-2">No exports yet</h3>
        <p class="text-slate-400 text-sm mb-6">You haven't generated any report exports.</p>
        <a href="{{ route('reports.export') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary-600 transition-all">
            <span class="material-symbols-outlined">add</span>
            Create Export
        </a>
    </div>
</div>
@endsection
