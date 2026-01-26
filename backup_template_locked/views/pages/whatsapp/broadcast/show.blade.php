<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Broadcast Detail - REPLYAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "whatsapp": "#25D366",
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
</head>
<body class="bg-background-dark font-display text-white overflow-hidden h-screen flex">
    
    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-y-auto">
        
        <div class="max-w-5xl mx-auto w-full p-8 border-b border-border-dark mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('whatsapp.broadcast.index') }}" class="p-2 hover:bg-white/5 rounded-full text-text-secondary">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold">{{ $broadcast->title }}</h1>
                        <p class="text-sm text-text-secondary">Created: {{ $broadcast->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
                <div>
                     <span class="px-3 py-1 rounded-full text-xs font-medium border
                        {{ $broadcast->status == 'completed' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 
                           ($broadcast->status == 'processing' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-gray-500/10 text-gray-400') }}">
                        {{ ucfirst($broadcast->status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto w-full px-8 pb-12 flex gap-8">
            <!-- Left: Stats & Content -->
            <div class="w-1/3 space-y-6">
                <!-- Progress Card -->
                <div class="bg-surface-dark border border-border-dark p-6 rounded-xl">
                    <h3 class="font-bold mb-4">Delivery Progress</h3>
                    
                    @php
                        $percent = $stats['total'] > 0 ? ($stats['sent'] / $stats['total']) * 100 : 0;
                    @endphp

                    <div class="flex items-end justify-between mb-2">
                        <span class="text-3xl font-bold text-white">{{ $stats['sent'] }}</span>
                        <span class="text-sm text-text-secondary mb-1">of {{ $stats['total'] }} sent</span>
                    </div>
                    <div class="w-full h-2 bg-gray-700 rounded-full overflow-hidden mb-4">
                        <div class="h-full bg-whatsapp transition-all duration-500" style="width: {{ $percent }}%"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-border-dark">
                        <div>
                            <span class="text-xs text-text-secondary block">Failed</span>
                            <span class="text-red-400 font-bold">{{ $stats['failed'] }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-text-secondary block">Pending</span>
                            <span class="text-orange-400 font-bold">{{ $stats['pending'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Message Content -->
                <div class="bg-surface-dark border border-border-dark p-6 rounded-xl">
                    <h3 class="font-bold mb-4">Message Content</h3>
                    <div class="bg-[#111722] p-4 rounded-lg text-sm text-gray-300 whitespace-pre-wrap">{{ $broadcast->message }}</div>
                    
                    @if($broadcast->media_path)
                    <div class="mt-4 pt-4 border-t border-border-dark">
                        <span class="text-xs text-text-secondary block mb-2">Attachment</span>
                        <div class="flex items-center gap-3 bg-white/5 p-2 rounded-lg">
                            <span class="material-symbols-outlined text-text-secondary">attachment</span>
                            <span class="text-sm truncate flex-1">Media File</span>
                            <a href="{{ asset('storage/' . $broadcast->media_path) }}" target="_blank" class="text-primary text-xs hover:underline">View</a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Right: Target List -->
            <div class="flex-1 bg-surface-dark border border-border-dark rounded-xl overflow-hidden flex flex-col">
                <div class="p-4 border-b border-border-dark bg-[#111722]/50">
                    <h3 class="font-bold">Target List</h3>
                </div>
                
                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-left">
                        <thead class="bg-white/5 text-text-secondary text-xs font-semibold uppercase sticky top-0 bg-surface-dark z-10">
                            <tr>
                                <th class="px-4 py-3">Phone</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Sent At</th>
                                <th class="px-4 py-3">Info</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark">
                            @foreach($broadcast->targets as $target)
                            <tr class="hover:bg-white/5 text-sm">
                                <td class="px-4 py-3 font-mono text-gray-300">{{ $target->phone_number }}</td>
                                <td class="px-4 py-3">
                                    @if($target->status == 'sent')
                                        <span class="inline-flex items-center gap-1 text-whatsapp text-xs font-bold">
                                            <span class="material-symbols-outlined text-[14px]">check_circle</span> Sent
                                        </span>
                                    @elseif($target->status == 'failed')
                                        <span class="inline-flex items-center gap-1 text-red-400 text-xs font-bold">
                                            <span class="material-symbols-outlined text-[14px]">error</span> Failed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-gray-400 text-xs">
                                            <span class="material-symbols-outlined text-[14px]">hourglass_empty</span> Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs">
                                    {{ $target->sent_at ? $target->sent_at->format('H:i:s') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs max-w-[150px] truncate" title="{{ $target->error_message }}">
                                    {{ $target->error_message ?? '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="p-4 border-t border-border-dark">
                    {{ $broadcast->targets->links() }}
                </div>
            </div>
        </div>
    </main>

    <script>
        // Auto refresh page every 10 seconds if processing
        @if($broadcast->status == 'processing')
            setTimeout(function() {
                window.location.reload();
            }, 5000);
        @endif
    </script>
</body>
</html>
