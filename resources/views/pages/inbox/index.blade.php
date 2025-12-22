@extends('layouts.app')

@section('content')
<div class="grid grid-cols-12 h-[calc(100vh-120px)] gap-0">

  {{-- SIDEBAR LIST CONVERSATION --}}
  <div class="col-span-12 md:col-span-4 xl:col-span-3 border-r border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex flex-col">
    <div class="p-4 border-b border-gray-200 dark:border-gray-800 shrink-0">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Inbox Instagram</h2>
      <p class="text-xs text-gray-500 mt-1">Daftar DM terbaru</p>
    </div>

    <div class="overflow-y-auto flex-1">
      @forelse($conversations as $conv)
        @php
          $active = ($selectedId == $conv->id);
          $time = $conv->last_activity_at
              ? \Carbon\Carbon::createFromTimestamp($conv->last_activity_at)->diffForHumans()
              : '';
        @endphp

        <a href="{{ route('inbox', ['conversation_id' => $conv->id]) }}"
           class="flex gap-3 px-4 py-3 border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800
           {{ $active ? 'bg-brand-50 dark:bg-gray-800' : '' }}">

          {{-- avatar --}}
          <div class="w-11 h-11 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden shrink-0">
            @if($conv->avatar)
              <img src="{{ $conv->avatar }}" class="w-full h-full object-cover" />
            @else
              <div class="w-full h-full flex items-center justify-center text-xs font-semibold text-gray-600 dark:text-gray-300">
                {{ strtoupper(substr(ltrim($conv->display_name,'@'),0,1)) }}
              </div>
            @endif
          </div>

          {{-- name + preview --}}
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-center">
              <p class="font-medium text-sm text-gray-900 dark:text-white truncate">
                {{ $conv->display_name }}
              </p>
              <span class="text-[10px] text-gray-400 shrink-0">{{ $time }}</span>
            </div>
            <p class="text-xs text-gray-500 truncate mt-1">
              {{ \Illuminate\Support\Str::limit($conv->last_message, 40) }}
            </p>
          </div>
        </a>

      @empty
        <div class="p-4 text-sm text-gray-500">Belum ada percakapan.</div>
      @endforelse
    </div>
  </div>

  {{-- CHAT PANEL --}}
  <div class="col-span-12 md:col-span-8 xl:col-span-9 flex flex-col bg-gray-50 dark:bg-gray-950">

    @if($selectedId && $contact)
      @php
        $name = $contact['name'] ?? 'Instagram User';
        $avatar = $contact['avatar'] ?? null;
      @endphp

      {{-- header --}}
      <div class="flex items-center gap-3 p-4 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shrink-0">
        <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
          @if($avatar)
            <img src="{{ $avatar }}" class="w-full h-full object-cover" />
          @endif
        </div>
        <div>
          <p class="font-semibold text-gray-900 dark:text-white">{{ $name }}</p>
          <p class="text-xs text-gray-500">Conversation #{{ $selectedId }}</p>
        </div>
      </div>

      {{-- messages --}}
      <div data-chat-body class="flex-1 overflow-y-auto p-4 space-y-2">
        @forelse($messages as $msg)
          @php
            // sender_type dari chatwoot biasanya "contact" atau "user"
            $sender = $msg->sender_type ?? 'unknown';
            $isContact = ($sender === 'contact');

            // BEDA WARNA:
            // - pesan dari dia (contact) => abu/putih kiri
            // - pesan dari kita (user/agent) => biru kanan
            $bubbleClass = $isContact
                ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded-bl-md border border-gray-200 dark:border-gray-800'
                : 'bg-brand-500 text-white rounded-br-md ml-auto';

            $time = $msg->message_created_at
                ? \Carbon\Carbon::createFromTimestamp($msg->message_created_at)->format('H:i')
                : '';
          @endphp

          <div class="flex {{ $isContact ? 'justify-start' : 'justify-end' }}">
            <div class="max-w-[70%] px-3 py-2 rounded-2xl text-sm {{ $bubbleClass }}">
              {{ $msg->content }}
              @if($time)
                <div class="text-[10px] opacity-70 mt-1 text-right">{{ $time }}</div>
              @endif
            </div>
          </div>
        @empty
          <div class="text-center text-sm text-gray-500 mt-10">
            Tidak ada pesan di percakapan ini.
          </div>
        @endforelse
      </div>

      {{-- composer --}}
      <div class="p-3 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shrink-0">
        @if(session('success'))
          <div class="text-xs text-green-600 mb-2">{{ session('success') }}</div>
        @endif

        <form action="{{ route('inbox.send') }}" method="POST" class="flex gap-2">
          @csrf
          <input type="hidden" name="conversation_id" value="{{ $selectedId }}">

          <input name="content" type="text" placeholder="Ketik balasan..."
              class="flex-1 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none"
              autocomplete="off"/>

          <button type="submit"
              class="px-4 py-2 rounded-lg bg-brand-500 text-white text-sm font-medium hover:bg-brand-600">
            Kirim
          </button>
        </form>

        @error('content')
          <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
        @enderror
      </div>

    @else
      <div class="flex-1 flex items-center justify-center text-gray-500 text-sm">
        Pilih percakapan dari panel kiri
      </div>
    @endif
  </div>
</div>

{{-- AUTO SCROLL + AUTO REFRESH ONLY IF NEW --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  const chatBody = document.querySelector('[data-chat-body]');
  if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;

  const selectedId = @json($selectedId);
  if (!selectedId) return;

  // ambil timestamp pesan terakhir yang tampil
  let lastTs = 0;
  @if(count($messages))
    lastTs = {{ (int)($messages->last()->message_created_at ?? 0) }};
  @endif

  async function checkNew() {
    try {
      const res = await fetch(`{{ route('inbox.hasNew') }}?conversation_id=${selectedId}&since=${lastTs}`);
      const data = await res.json();

      // kalau ada baru -> reload halaman (ambil data DB yg sudah di-sync)
      if (data.has_new) {
        window.location.reload();
        return;
      }

      // update lastTs biar akurat
      if (data.latest && data.latest > lastTs) {
        lastTs = data.latest;
      }
    } catch (e) {
      console.error(e);
    }
  }

  // polling ringan tiap 5 detik
  setInterval(checkNew, 5000);
});
</script>
@endsection
