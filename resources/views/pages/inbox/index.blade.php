@extends('layouts.app')

@section('content')
<div class="grid grid-cols-12 h-[calc(100vh-120px)] gap-0">

  {{-- SIDEBAR LIST CONVERSATION --}}
  <div class="col-span-12 md:col-span-4 xl:col-span-3 border-r border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex flex-col">
    
    {{-- Header --}}
    <div class="p-4 border-b border-gray-200 dark:border-gray-800 shrink-0">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">💬 Inbox Instagram</h2>
      <p class="text-xs text-gray-500 mt-1">{{ count($conversations) }} percakapan</p>
    </div>

    {{-- Search --}}
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 shrink-0">
      <input type="text" id="search-conv" placeholder="Cari percakapan..." 
        class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
        autocomplete="off"/>
    </div>

    {{-- Conversations List --}}
    <div class="overflow-y-auto flex-1">
      @forelse($conversations as $conv)
        @php
          $active = ($selectedId == $conv->id);
          $time = $conv->last_activity_at
              ? \Carbon\Carbon::createFromTimestamp($conv->last_activity_at)->diffForHumans()
              : '';
          // FIX: Ambil username atau display_name
          $displayName = $conv->ig_username ? '@' . $conv->ig_username : $conv->display_name;
        @endphp

        <a href="{{ route('inbox', ['conversation_id' => $conv->id]) }}"
           class="flex gap-3 px-4 py-3 border-b border-gray-100 dark:border-gray-800 transition-colors group
           {{ $active 
              ? 'bg-blue-50 dark:bg-blue-900/20 border-l-2 border-l-blue-500' 
              : 'hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">

          {{-- Avatar --}}
          <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 overflow-hidden shrink-0 flex items-center justify-center text-white font-bold text-sm">
            @if($conv->avatar)
              <img src="{{ $conv->avatar }}" class="w-full h-full object-cover" alt="avatar"/>
            @else
              {{ strtoupper(substr($displayName, 0, 1)) }}
            @endif
          </div>

          {{-- Info --}}
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-center gap-2">
              <p class="font-semibold text-sm text-gray-900 dark:text-white truncate">
                {{ $displayName }}
              </p>
              <span class="text-[10px] text-gray-400 shrink-0 font-medium">{{ $time }}</span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1 line-clamp-2">
              {{ \Illuminate\Support\Str::limit($conv->last_message, 50) }}
            </p>
          </div>

          {{-- Status dot --}}
          @if($active)
            <div class="w-2 h-2 rounded-full bg-blue-500 mt-1 shrink-0"></div>
          @endif
        </a>

      @empty
        <div class="p-8 text-center text-sm text-gray-500">
          <p class="text-3xl mb-2">📭</p>
          <p>Belum ada percakapan</p>
          <p class="text-xs mt-1">Pesan dari Instagram akan muncul di sini</p>
        </div>
      @endforelse
    </div>
  </div>

  {{-- CHAT PANEL --}}
  <div class="col-span-12 md:col-span-8 xl:col-span-9 flex flex-col bg-gray-50 dark:bg-gray-950">

    @if($selectedId && $contact)
      @php
        $name = $contact['name'] ?? 'Instagram User';
        $avatar = $contact['avatar'] ?? null;
        $username = $contact['ig_username'] ?? null;
      @endphp

      {{-- Header --}}
      <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 overflow-hidden flex items-center justify-center text-white font-bold">
            @if($avatar)
              <img src="{{ $avatar }}" class="w-full h-full object-cover" alt="avatar"/>
            @else
              {{ strtoupper(substr($name, 0, 1)) }}
            @endif
          </div>
          <div>
            <p class="font-semibold text-gray-900 dark:text-white">{{ $name }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              @if($username)
                @{{ $username }}
              @else
                Instagram User
              @endif
            </p>
          </div>
        </div>
        
        {{-- Action Buttons --}}
        <div class="flex items-center gap-2">
          <button type="button" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </button>
        </div>
      </div>

      {{-- Messages --}}
      <div data-chat-body class="flex-1 overflow-y-auto p-6 space-y-4">
        @forelse($messages as $msg)
          @php
            $isContact = ($msg->sender_type === 'contact');
            $time = $msg->message_created_at
                ? \Carbon\Carbon::createFromTimestamp($msg->message_created_at)->format('H:i')
                : '';
            
            // Cari log untuk tau sumber jawaban
            $log = null;
            if (!$isContact) {
              $log = \App\Models\AutoReplyLog::where('response_text', $msg->content)
                ->latest()
                ->first();
            }
          @endphp

          <div class="flex {{ $isContact ? 'justify-start' : 'justify-end' }} gap-2">
            {{-- Contact Message --}}
            @if($isContact)
              <div class="max-w-[65%]">
                <div class="rounded-3xl rounded-bl-none bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-3 shadow-sm">
                  <p class="text-sm text-gray-900 dark:text-white leading-relaxed break-words">{{ $msg->content }}</p>
                  <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-2 text-right">{{ $time }}</p>
                </div>
              </div>
            @else
              {{-- Bot/Agent Message --}}
              <div class="max-w-[65%]">
                <div class="rounded-3xl rounded-br-none bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 shadow-md">
                  <p class="text-sm text-white leading-relaxed break-words">{{ $msg->content }}</p>
                  <p class="text-[10px] text-blue-100 mt-2 text-right">{{ $time }}</p>
                </div>
                
                {{-- Source Badge --}}
                @if($log)
                  <div class="flex gap-2 mt-2 items-center justify-end">
                    @if($log->response_source === 'ai')
                      <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 border border-green-200 dark:border-green-700">
                        <span class="text-xs font-semibold text-green-700 dark:text-green-300">🤖 AI</span>
                        @if($log->ai_confidence)
                          <span class="text-[10px] font-medium text-green-600 dark:text-green-400">{{ round($log->ai_confidence * 100) }}%</span>
                        @endif
                      </div>
                    @else
                      <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 border border-blue-200 dark:border-blue-700">
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">⚙️ Manual</span>
                      </div>
                    @endif
                  </div>
                @endif
              </div>
            @endif
          </div>

        @empty
          <div class="flex-1 flex flex-col items-center justify-center text-gray-500">
            <p class="text-5xl mb-3 opacity-40">💬</p>
            <p class="text-sm font-medium">Belum ada pesan</p>
            <p class="text-xs mt-1 opacity-75">Mulai percakapan dengan mengirim pesan pertama</p>
          </div>
        @endforelse
      </div>

      {{-- Composer --}}
      <div class="p-4 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shrink-0">
        @if(session('success'))
          <div class="mb-3 px-4 py-2 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs font-semibold border border-green-200 dark:border-green-700">
            ✓ {{ session('success') }}
          </div>
        @endif

        <form action="{{ route('inbox.send') }}" method="POST" class="flex gap-2">
          @csrf
          <input type="hidden" name="conversation_id" value="{{ $selectedId }}">

          <input name="content" type="text" placeholder="Ketik balasan..." required
            class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            autocomplete="off"/>

          <button type="submit"
            class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 text-white text-sm font-semibold hover:shadow-lg transition-all active:scale-95">
            📤
          </button>
        </form>

        @error('content')
          <div class="mt-2 text-xs text-red-500 font-medium">❌ {{ $message }}</div>
        @enderror
      </div>

    @else
      <div class="flex-1 flex flex-col items-center justify-center text-gray-500">
        <div class="text-6xl mb-4 opacity-30">👇</div>
        <p class="text-base font-medium">Pilih percakapan</p>
        <p class="text-xs mt-1 opacity-75">dari panel kiri untuk memulai chat</p>
      </div>
    @endif
  </div>
</div>

{{-- AUTO SCROLL + SEARCH + AUTO REFRESH --}}

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Auto scroll ke bawah
  const chatBody = document.querySelector('[data-chat-body]');
  if (chatBody) {
    chatBody.scrollTop = chatBody.scrollHeight;
    
    // Scroll ke bawah saat ada pesan baru
    const observer = new MutationObserver(() => {
      setTimeout(() => {
        chatBody.scrollTop = chatBody.scrollHeight;
      }, 100);
    });
    observer.observe(chatBody, { childList: true });
  }

  // Search conversation
  const searchInput = document.getElementById('search-conv');
  if (searchInput) {
    const links = document.querySelectorAll('a[href*="conversation_id"]');
    searchInput.addEventListener('input', (e) => {
      const query = e.target.value.toLowerCase();
      links.forEach(link => {
        const text = link.textContent.toLowerCase();
        link.style.display = text.includes(query) ? '' : 'none';
      });
    });
  }

  // Auto refresh
  const selectedId = @json($selectedId);
  if (!selectedId) return;

  let lastTs = 0;
  @if(count($messages))
    lastTs = {{ (int)($messages->last()->message_created_at ?? 0) }};
  @endif

  async function checkNew() {
    try {
      const res = await fetch(`{{ route('inbox.hasNew') }}?conversation_id=${selectedId}&since=${lastTs}`);
      const data = await res.json();

      if (data.has_new) {
        window.location.reload();
        return;
      }

      if (data.latest && data.latest > lastTs) {
        lastTs = data.latest;
      }
    } catch (e) {
      console.error('Check new error:', e);
    }
  }

  // Polling setiap 3 detik
  setInterval(checkNew, 3000);
});
</script>
@endsection
