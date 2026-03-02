@extends('admin.layouts.app')

@section('title', 'Schedule Monitor')
@section('page_title', 'Schedule Monitor')

@section('content')

<!-- Pending Jobs Overview -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    @foreach($pendingJobs as $queue)
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-blue-400">queue</span>
            <span class="text-xs font-bold text-slate-400 uppercase">{{ $queue['queue'] }} Queue</span>
        </div>
        <p class="text-2xl font-bold text-white">{{ $queue['count'] }}</p>
        <p class="text-xs text-slate-500">Oldest: {{ $queue['oldest'] ? \Carbon\Carbon::parse($queue['oldest'])->diffForHumans() : 'N/A' }}</p>
    </div>
    @endforeach
    
    @php $failedCount = DB::table('failed_jobs')->count(); @endphp
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-red-400">error</span>
            <span class="text-xs font-bold text-slate-400 uppercase">Failed Jobs</span>
        </div>
        <p class="text-2xl font-bold {{ $failedCount > 0 ? 'text-red-400' : 'text-white' }}">{{ $failedCount }}</p>
        <a href="{{ route('admin.failed-jobs.index') }}" class="text-xs text-primary hover:underline">View all →</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Scheduled Tasks -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 bg-surface-light/30 flex items-center justify-between">
            <h3 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-500">schedule</span>
                Scheduled Tasks
            </h3>
            <span class="text-xs text-slate-500">{{ count($tasks) }} active tasks</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-background-dark/50 text-slate-400 text-xs">
                    <tr>
                        <th class="px-4 py-3">Task</th>
                        <th class="px-4 py-3">Frequency</th>
                        <th class="px-4 py-3">Last Run</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($tasks as $task)
                    <tr class="hover:bg-surface-light/10 transition">
                        <td class="px-4 py-3">
                            <div class="font-medium text-white">{{ $task['name'] }}</div>
                            <div class="text-xs text-slate-500">{{ $task['description'] }}</div>
                            <code class="text-[10px] text-slate-600">{{ $task['command'] }}</code>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-400 text-xs">{{ $task['frequency'] }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($task['last_run'])
                            <span class="text-green-400 text-xs">{{ \Carbon\Carbon::parse($task['last_run'])->diffForHumans() }}</span>
                            @else
                            <span class="text-slate-500 text-xs">Never</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.schedule.run') }}" class="inline">
                                @csrf
                                <input type="hidden" name="task" value="{{ $task['name'] }}">
                                <button type="submit" class="p-1.5 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white transition" title="Run Now">
                                    <span class="material-symbols-outlined text-sm">play_arrow</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Execution History -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 bg-surface-light/30 flex items-center justify-between">
            <h3 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-green-500">history</span>
                Execution History
            </h3>
            <span class="text-xs text-slate-500">Last 50 runs</span>
        </div>
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-background-dark/50 text-slate-400 text-xs sticky top-0">
                    <tr>
                        <th class="px-4 py-3">Task</th>
                        <th class="px-4 py-3">Run At</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Output</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse(array_reverse($executionHistory) as $history)
                    <tr class="hover:bg-surface-light/10 transition">
                        <td class="px-4 py-3 font-medium text-white">{{ $history['task'] }}</td>
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ \Carbon\Carbon::parse($history['run_at'])->diffForHumans() }}</td>
                        <td class="px-4 py-3">
                            @if($history['status'] === 'success')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-500/10 text-green-400">SUCCESS</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-500/10 text-red-400">FAILED</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs max-w-xs truncate" title="{{ $history['output'] ?? '' }}">
                            {{ $history['output'] ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                            No execution history yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Auto-refresh indicator -->
<div class="mt-4 flex items-center justify-between text-xs text-slate-500">
    <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
        <span>Auto-refresh every 30 seconds</span>
    </div>
    <span id="last-updated">Last updated: {{ now()->format('H:i:s') }}</span>
</div>

@endsection

@push('scripts')
<script>
    // Auto-refresh queue status
    setInterval(() => {
        fetch('{{ route("admin.schedule.queue-status") }}')
            .then(r => r.json())
            .then(data => {
                document.getElementById('last-updated').textContent = 'Last updated: ' + data.timestamp;
                // Could update queue counts dynamically here
            });
    }, 30000);
</script>
@endpush
