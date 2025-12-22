@extends('layouts.app')

@section('content')
<div class="space-y-6">

  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-white">Dashboard ReplyAIBot</h1>
      <p class="text-sm text-gray-400">
        Instagram DM Overview – RS PKU Muhammadiyah Surakarta
      </p>
    </div>

    {{-- status bot (dummy dulu) --}}
    <div class="rounded-lg bg-meta-4 px-4 py-2 text-sm font-medium text-white">
      Bot Status: <span class="font-bold">ON</span>
    </div>
  </div>

  {{-- STAT CARDS --}}
  <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

    <div class="rounded-sm border border-strokedark bg-boxdark p-4">
      <div class="text-sm text-gray-400">DM Masuk Hari Ini</div>
      <div class="mt-2 text-3xl font-bold text-white">
        0
      </div>
    </div>

    <div class="rounded-sm border border-strokedark bg-boxdark p-4">
      <div class="text-sm text-gray-400">Belum Dibalas</div>
      <div class="mt-2 text-3xl font-bold text-white">
        0
      </div>
    </div>

    <div class="rounded-sm border border-strokedark bg-boxdark p-4">
      <div class="text-sm text-gray-400">Auto-reply Berhasil</div>
      <div class="mt-2 text-3xl font-bold text-white">
        0%
      </div>
    </div>

    <div class="rounded-sm border border-strokedark bg-boxdark p-4">
      <div class="text-sm text-gray-400">Percakapan Aktif</div>
      <div class="mt-2 text-3xl font-bold text-white">
        0
      </div>
    </div>

  </div>

  {{-- RECENT CONVERSATIONS --}}
  <div class="rounded-sm border border-strokedark bg-boxdark p-4">
    <div class="mb-4 flex items-center justify-between">
      <h2 class="text-lg font-semibold text-white">Percakapan Terbaru</h2>
      <a href="/inbox" class="text-sm text-primary hover:underline">
        Lihat semua
      </a>
    </div>

    {{-- versi dummy dulu --}}
    <div class="space-y-3">
      <div class="flex items-center gap-3 rounded-md bg-boxdark-2 p-3">
        <div class="h-10 w-10 rounded-full bg-gray-700"></div>
        <div class="flex-1">
          <div class="text-sm font-semibold text-white">@username</div>
          <div class="text-xs text-gray-400">Preview pesan terakhir...</div>
        </div>
        <div class="text-[11px] text-gray-500">baru saja</div>
      </div>

      <div class="flex items-center gap-3 rounded-md bg-boxdark-2 p-3">
        <div class="h-10 w-10 rounded-full bg-gray-700"></div>
        <div class="flex-1">
          <div class="text-sm font-semibold text-white">@username2</div>
          <div class="text-xs text-gray-400">Preview pesan terakhir...</div>
        </div>
        <div class="text-[11px] text-gray-500">1 jam lalu</div>
      </div>

      <div class="flex items-center gap-3 rounded-md bg-boxdark-2 p-3">
        <div class="h-10 w-10 rounded-full bg-gray-700"></div>
        <div class="flex-1">
          <div class="text-sm font-semibold text-white">@username3</div>
          <div class="text-xs text-gray-400">Preview pesan terakhir...</div>
        </div>
        <div class="text-[11px] text-gray-500">kemarin</div>
      </div>
    </div>
  </div>

</div>
@endsection
