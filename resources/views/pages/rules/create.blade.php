@extends('layouts.dark')

@section('content')
<div class="max-w-2xl space-y-6">

  <div>
    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Tambah Rule</h1>
    <p class="text-sm text-gray-500">Isi keyword pemicu dan jawaban bot</p>
  </div>

  <form action="{{ route('rules.store') }}" method="POST"
        class="space-y-4 bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800">
    @csrf

    <div>
      <label class="text-sm font-medium">Nama Rule</label>
      <input name="name" class="mt-1 w-full rounded-lg border px-3 py-2 dark:bg-gray-800"
             placeholder="contoh: Layanan RS" value="{{ old('name') }}">
      @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="text-sm font-medium">Trigger Keyword</label>
      <input name="trigger_keyword" class="mt-1 w-full rounded-lg border px-3 py-2 dark:bg-gray-800"
             placeholder="contoh: pelayanan" value="{{ old('trigger_keyword') }}">
      @error('trigger_keyword') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="text-sm font-medium">Response Text</label>
      <textarea name="response_text" rows="5"
                class="mt-1 w-full rounded-lg border px-3 py-2 dark:bg-gray-800"
                placeholder="isi jawaban otomatis">{{ old('response_text') }}</textarea>
      @error('response_text') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="text-sm font-medium">Priority (opsional)</label>
      <input name="priority" type="number"
             class="mt-1 w-full rounded-lg border px-3 py-2 dark:bg-gray-800"
             value="{{ old('priority',0) }}">
    </div>

    <label class="flex items-center gap-2 text-sm">
      <input type="checkbox" name="is_active" checked>
      Aktifkan rule
    </label>

    <div class="flex gap-2">
      <button class="px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-medium">
        Simpan
      </button>
      <a href="{{ route('rules.index') }}" class="px-4 py-2 border rounded-lg text-sm">
        Batal
      </a>
    </div>

  </form>
</div>
@endsection
