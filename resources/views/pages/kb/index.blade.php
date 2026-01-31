<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('kb.title') }} - REPLYAI</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "brand": { 500: "#135bec", 600: "#0f4bc2" }, // Alias for KB script
                        "background-light": "#f6f6f8",
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
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #111722; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #324467; border-radius: 10px; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-white overflow-hidden h-screen flex flex-col lg:flex-row">

<!-- Sidebar Navigation -->
@include('components.sidebar')

<main class="flex-1 flex flex-col h-full overflow-hidden relative">
    <!-- Top Header for KB -->
    <header class="h-14 border-b border-border-dark bg-background-dark/80 backdrop-blur-md flex items-center justify-between px-6 z-20 shrink-0">
        <div class="flex items-center gap-2 text-text-secondary text-xs font-bold uppercase tracking-widest">
            <span class="material-symbols-outlined text-[18px]">calendar_today</span>
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 px-3 py-1 bg-whatsapp/10 rounded-full border border-whatsapp/20">
                <div class="size-1.5 bg-whatsapp rounded-full animate-pulse"></div>
                <span class="text-[10px] font-bold text-whatsapp uppercase tracking-widest">{{ __('common.system_online', ['default' => 'System Online']) }}</span>
            </div>
            @include('components.language-switcher')
        </div>
    </header>
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6 lg:p-10 pb-20">
        <div class="max-w-[1200px] mx-auto flex flex-col gap-6">
            
            <!-- Header KB -->
           <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
              <div class="flex items-center gap-3">
                <h1 class="text-3xl font-black text-white">{{ __('kb.title') }}</h1>
                @include('components.page-help', [
                    'title' => __('kb.help_title'),
                    'description' => __('kb.help_description'),
                    'tips' => [
                        __('kb.tip_1'),
                        __('kb.tip_2'),
                        __('kb.tip_3'),
                        __('kb.tip_4')
                    ]
                ])
              </div>
              <p class="text-text-secondary mt-1">{{ __('kb.subtitle') }}</p>
            </div>
             <!-- Tombol Test AI -->
             <button
                type="button"
                data-modal-open="ai-test-modal"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#135bec] text-white text-sm font-bold shadow-lg shadow-blue-900/20 hover:bg-blue-600 transition"
              >
                <span class="material-symbols-outlined text-[20px]">smart_toy</span> Test AI
              </button>
          </div>

          <!-- Import Section (Tabs) -->
          <div class="rounded-xl border border-border-dark bg-surface-dark overflow-hidden">
              <div class="flex border-b border-border-dark">
                  <button id="tab-url" class="flex-1 px-4 py-3 text-sm font-bold text-white bg-[#1f2b40] border-b-2 border-primary transition-colors">
                      <span class="material-symbols-outlined align-middle mr-1 text-[18px]">link</span> {{ __('kb.tab_url') }}
                  </button>
                  <button id="tab-file" class="flex-1 px-4 py-3 text-sm font-bold text-gray-400 hover:text-white hover:bg-[#1f2b40] border-b-2 border-transparent transition-colors">
                      <span class="material-symbols-outlined align-middle mr-1 text-[18px]">upload_file</span> {{ __('kb.tab_file') }}
                  </button>
                  <button id="tab-manual" class="flex-1 px-4 py-3 text-sm font-bold text-gray-400 hover:text-white hover:bg-[#1f2b40] border-b-2 border-transparent transition-colors">
                      <span class="material-symbols-outlined align-middle mr-1 text-[18px]">edit_note</span> Input Manual
                  </button>
              </div>

              <!-- Content URL -->
              <div id="panel-url" class="p-6 space-y-4">
                <div class="grid grid-cols-12 gap-3">
                  <div class="col-span-12 sm:col-span-7">
                    <input id="kb-url" type="text" placeholder="https://web-resmi.com/jadwal"
                      class="w-full rounded-lg border border-border-dark bg-[#111722] px-3 py-2.5 text-sm text-white focus:outline-none focus:border-primary">
                  </div>
                  <div class="col-span-12 sm:col-span-3">
                    <input id="kb-title" type="text" placeholder="Judul (opsional)"
                      class="w-full rounded-lg border border-border-dark bg-[#111722] px-3 py-2.5 text-sm text-white focus:outline-none focus:border-primary">
                  </div>
                  <div class="col-span-12 sm:col-span-2">
                    <button id="btn-import-url"
                      class="w-full px-4 py-2.5 rounded-lg bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition">
                      {{ __('kb.button_import') }}
                    </button>
                  </div>
                </div>
                <p class="text-xs text-text-secondary">Gunakan URL resmi untuk akurasi data AI.</p>
              </div>

              <!-- Content File -->
              <div id="panel-file" class="hidden p-6 space-y-4">
                  <div class="border-2 border-dashed border-border-dark rounded-xl p-8 text-center hover:bg-[#111722]/50 transition-colors relative" id="drop-zone">
                      <input type="file" id="kb-file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.txt">
                      <div class="flex flex-col items-center gap-2 pointer-events-none">
                          <span class="material-symbols-outlined text-[40px] text-text-secondary">cloud_upload</span>
                          <p class="text-sm text-gray-300 font-medium">{{ __('kb.placeholder_file') }}</p>
                          <p class="text-xs text-text-secondary">{{ __('kb.help_file') }}</p>
                          <p id="file-name" class="text-sm text-primary font-bold mt-2 hidden"></p>
                      </div>
                  </div>
                  
                  <div class="flex items-center gap-3">
                      <input id="kb-file-tags" type="text" placeholder="Tags (koma dipisah, misal: ukm, panduan)"
                        class="flex-1 rounded-lg border border-border-dark bg-[#111722] px-3 py-2.5 text-sm text-white focus:outline-none focus:border-primary">
                      
                      <button id="btn-import-file" disabled
                        class="px-6 py-2.5 rounded-lg bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ __('kb.button_upload') }}
                      </button>
                  </div>
              </div>

              <!-- Content Manual -->
              <div id="panel-manual" class="hidden p-6 space-y-4">
                  <form action="{{ route('kb.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                      @csrf
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <div>
                              <label class="block text-xs font-bold text-text-secondary uppercase tracking-widest mb-2">Judul Artikel</label>
                              <input name="title" type="text" placeholder="Contoh: Daftar Harga Kamar" required
                                     class="w-full rounded-lg border border-border-dark bg-[#111722] px-3 py-2.5 text-sm text-white focus:outline-none focus:border-primary">
                          </div>
                          <div>
                              <label class="block text-xs font-bold text-text-secondary uppercase tracking-widest mb-2">Pilih Profil Bisnis</label>
                              <select name="business_profile_id" class="w-full rounded-lg border border-border-dark bg-[#111722] px-3 py-2.5 text-sm text-white focus:outline-none focus:border-primary">
                                  <option value="">📋 Semua Profile (Umum)</option>
                                  @foreach($businessProfiles as $bp)
                                      <option value="{{ $bp->id }}">{{ $bp->getIndustryIcon() }} {{ $bp->business_name }}</option>
                                  @endforeach
                              </select>
                          </div>
                      </div>
                      <div>
                          <label class="block text-xs font-bold text-text-secondary uppercase tracking-widest mb-2">Isi Artikel / Jawaban</label>
                          <textarea name="content" rows="5" placeholder="Tulis informasi detail yang akan digunakan AI untuk menjawab..." required
                                    class="w-full rounded-lg border border-border-dark bg-[#111722] px-3 py-2.5 text-sm text-white focus:outline-none focus:border-primary"></textarea>
                      </div>
                      
                      <div class="flex flex-col md:flex-row md:items-center gap-4 pt-2">
                          <div class="flex-1">
                              <label class="block text-xs font-bold text-text-secondary uppercase tracking-widest mb-2">📸 Media Lampiran (Opsional)</label>
                              <input name="image" type="file" accept="image/*"
                                     class="w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 cursor-pointer">
                              <p class="mt-1 text-[10px] text-text-secondary italic">Gambar akan dikirim bot jika user tanya hal terkait.</p>
                          </div>
                          <button type="submit" class="px-8 py-3 rounded-lg bg-primary text-white text-sm font-bold hover:bg-blue-600 transition shadow-lg shadow-primary/20">
                              Simpan ke Knowledge Base
                          </button>
                      </div>
                  </form>
              </div>
          </div>

          <!-- List KB -->
          <div class="rounded-xl border border-border-dark bg-surface-dark overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-[#1f2b40]/50">
              <p class="font-bold text-white">{{ __('kb.list_title') }}</p>
              <div class="text-xs text-text-secondary font-bold bg-[#111722] px-2 py-1 rounded">Total: {{ $articles->count() }}</div>
            </div>

            <div class="divide-y divide-border-dark">
              @forelse($articles as $a)
                <div id="kb-{{ $a->id }}" class="p-6 hover:bg-[#1f2b40] transition-colors group">
                  <div class="flex flex-col gap-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                          <h4 class="font-bold text-white text-lg group-hover:text-primary transition-colors">
                            {{ $a->title ?? 'Untitled' }}
                          </h4>
                          @if($a->source_url)
                            <a href="{{ $a->source_url }}" target="_blank" class="text-xs text-blue-400 hover:underline break-all flex items-center gap-1 mt-1">
                                <span class="material-symbols-outlined text-[14px]">link</span> {{ $a->source_url }}
                            </a>
                          @endif
                           @if($a->tags)
                             <div class="flex gap-2 mt-2">
                                @foreach(explode(',', $a->tags) as $tag)
                                    <span class="text-[10px] bg-[#111722] text-text-secondary px-2 py-0.5 rounded border border-border-dark">{{ trim($tag) }}</span>
                                @endforeach
                             </div>
                          @endif
                        </div>

                        <div class="flex items-center gap-2">
                          <!-- Profile Selector -->
                          <select onchange="updateKbProfile({{ $a->id }}, this.value)" 
                                  class="px-2 py-1.5 rounded-lg text-xs bg-[#111722] text-text-secondary border border-border-dark focus:outline-none focus:border-primary cursor-pointer">
                              <option value="">📋 Semua Profile</option>
                              @foreach($businessProfiles as $bp)
                                  <option value="{{ $bp->id }}" {{ $a->business_profile_id == $bp->id ? 'selected' : '' }}>
                                      {{ $bp->getIndustryIcon() }} {{ $bp->business_name }}
                                  </option>
                              @endforeach
                          </select>
                            <button
                            data-action="detail"
                            data-title="{{ e($a->title ?? 'Untitled') }}"
                            data-url="{{ e($a->source_url ?? '-') }}"
                            data-tags="{{ e($a->tags ?? '-') }}"
                            data-content="{{ e($a->content) }}"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold bg-[#111722] text-text-secondary hover:text-white border border-border-dark transition">
                            {{ __('kb.button_detail') }}
                          </button>
                          
                          <button
                             data-action="edit"
                             data-id="{{ $a->id }}"
                             data-title="{{ e($a->title ?? '') }}"
                             data-tags="{{ e($a->tags ?? '') }}"
                             data-profile-id="{{ $a->business_profile_id }}"
                             data-content="{{ e($a->content) }}"
                             class="px-3 py-1.5 rounded-lg text-xs font-bold bg-[#111722] text-text-secondary hover:text-white border border-border-dark transition">
                             Edit
                           </button>

                          <button data-action="toggle" data-id="{{ $a->id }}"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold border border-transparent
                              {{ $a->is_active ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-gray-700/50 text-gray-400' }}">
                            {{ $a->is_active ? __('kb.status_active') : __('kb.status_inactive') }}
                          </button>

                          <button data-action="delete" data-id="{{ $a->id }}"
                            class="p-1.5 rounded-lg text-red-400 hover:bg-red-500/10 transition" title="Delete">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                          </button>
                        </div>
                    </div>
                    
                    @if($a->image_path)
                      <div class="mt-3">
                        <img src="{{ asset('storage/' . $a->image_path) }}" class="w-48 h-32 object-cover rounded-lg border border-border-dark" alt="Media preview">
                      </div>
                    @endif

                    <p class="text-sm text-gray-400 line-clamp-2 mt-2 font-mono bg-[#111722]/50 p-2 rounded border border-border-dark/50">
                        {{ \Illuminate\Support\Str::limit($a->content, 220) }}
                    </p>
                  </div>
                </div>
              @empty
                <div class="py-12">
                    <x-empty-state 
                        icon="menu_book" 
                        title="{{ __('kb.empty_title') }}" 
                        description="{{ __('kb.empty_description') }}"
                    />
                </div>
              @endforelse
            </div>
          </div>

        </div>
    </div>
</main>


{{-- ================= MODAL DETAIL KB ================= --}}
<div id="kb-detail-modal" class="hidden fixed inset-0 z-50">
  <div data-modal-close="kb-detail-modal" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-3xl rounded-2xl bg-[#1e2634] border border-[#324467] shadow-2xl flex flex-col max-h-[90vh]">
      <div class="flex items-center justify-between px-6 py-4 border-b border-[#324467]">
        <div>
          <h3 id="kb-detail-title" class="text-lg font-bold text-white">{{ __('kb.modal_detail_title') }}</h3>
          <p id="kb-detail-url" class="text-xs text-[#92a4c9] mt-1 break-all"></p>
        </div>
        <button type="button" data-modal-close="kb-detail-modal" class="text-[#92a4c9] hover:text-white">
            <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div class="px-6 py-6 overflow-y-auto custom-scrollbar">
        <pre id="kb-detail-content" class="whitespace-pre-wrap text-sm text-gray-300 font-mono bg-[#111722] p-4 rounded-lg border border-[#324467]"></pre>
      </div>
      <div class="flex items-center justify-end px-6 py-4 border-t border-[#324467]">
        <button type="button" data-modal-close="kb-detail-modal" class="px-4 py-2 rounded-lg bg-[#324467] text-white text-sm font-semibold hover:bg-[#405580]">{{ __('kb.button_close') }}</button>
      </div>
    </div>
  </div>
</div>

{{-- ================= MODAL EDIT KB ================= --}}
<div id="kb-edit-modal" class="hidden fixed inset-0 z-50">
  <div data-modal-close="kb-edit-modal" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-[#1e2634] border border-[#324467] shadow-2xl">
      <div class="flex items-center justify-between px-6 py-4 border-b border-[#324467]">
        <div>
          <h3 class="text-lg font-bold text-white">Edit Knowledge Base</h3>
          <p class="text-xs text-[#92a4c9]">Perbarui informasi artikel atau lampiran gambar.</p>
        </div>
        <button type="button" data-modal-close="kb-edit-modal" class="text-[#92a4c9] hover:text-white">
            <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <form id="kb-edit-form" method="POST" enctype="multipart/form-data" class="px-6 py-6 space-y-4">
        @csrf
        <input type="hidden" name="id" id="edit-kb-id">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-text-secondary uppercase tracking-widest mb-2">Judul Artikel</label>
                <input name="title" id="edit-kb-title" type="text" required
                       class="w-full rounded-lg border border-border-dark bg-[#111722] px-3 py-2.5 text-sm text-white focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-xs font-bold text-text-secondary uppercase tracking-widest mb-2">Pilih Profil Bisnis</label>
                <select name="business_profile_id" id="edit-kb-profile-id" class="w-full rounded-lg border border-border-dark bg-[#111722] px-3 py-2.5 text-sm text-white focus:outline-none focus:border-primary">
                    <option value="">📋 Semua Profile (Umum)</option>
                    @foreach($businessProfiles as $bp)
                        <option value="{{ $bp->id }}">{{ $bp->getIndustryIcon() }} {{ $bp->business_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-xs font-bold text-text-secondary uppercase tracking-widest mb-2">Isi Artikel / Jawaban</label>
            <textarea name="content" id="edit-kb-content" rows="5" required
                      class="w-full rounded-lg border border-border-dark bg-[#111722] px-3 py-2.5 text-sm text-white focus:outline-none focus:border-primary"></textarea>
        </div>
        
        <div>
            <label class="block text-xs font-bold text-text-secondary uppercase tracking-widest mb-2">Tags (Koma dipisah)</label>
            <input name="tags" id="edit-kb-tags" type="text"
                   class="w-full rounded-lg border border-border-dark bg-[#111722] px-3 py-2.5 text-sm text-white focus:outline-none focus:border-primary">
        </div>

        <div>
            <label class="block text-xs font-bold text-text-secondary uppercase tracking-widest mb-2">📸 Media Lampiran (Opsional)</label>
            <input name="image" type="file" accept="image/*"
                   class="w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/20 file:text-primary hover:file:bg-primary/30 cursor-pointer">
            <p class="mt-1 text-[10px] text-text-secondary italic">Upload gambar baru untuk mengganti gambar lama.</p>
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button type="button" data-modal-close="kb-edit-modal" class="px-4 py-2 rounded-lg bg-[#324467] text-white text-sm font-semibold hover:bg-[#405580]">Batal</button>
          <button type="submit" id="btn-save-kb" class="px-6 py-2 rounded-lg bg-primary text-white text-sm font-bold hover:bg-blue-600 shadow-lg shadow-primary/20">
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ================= MODAL TEST AI ================= --}}
<div id="ai-test-modal" class="hidden fixed inset-0 z-50">
  <div data-modal-close="ai-test-modal" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-[#1e2634] border border-[#324467] shadow-2xl">
      <div class="flex items-center justify-between px-6 py-4 border-b border-[#324467]">
        <div>
          <h3 class="text-lg font-bold text-white">{{ __('kb.modal_test_title') }}</h3>
          <p class="text-xs text-[#92a4c9]">{{ __('kb.modal_test_subtitle') }}</p>
        </div>
        <button type="button" data-modal-close="ai-test-modal" class="text-[#92a4c9] hover:text-white">
            <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <div class="px-6 py-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-white mb-2">{{ __('kb.label_question') }}</label>
            <textarea id="ai-question" rows="3" placeholder="{{ __('kb.placeholder_question') }}"
              class="w-full rounded-lg border border-[#324467] bg-[#111722] px-4 py-3 text-sm text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
        </div>

        <div class="flex justify-end">
          <button id="btn-ai-test" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-bold hover:bg-blue-600 shadow-lg shadow-blue-900/20 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">play_arrow</span> {{ __('kb.button_test_response') }}
          </button>
        </div>

        <!-- Result Area -->
        <div id="ai-result-wrap" class="hidden mt-4 pt-4 border-t border-[#324467] space-y-4">
           <div class="flex items-center gap-2">
               <span class="text-xs font-bold text-[#92a4c9] uppercase tracking-wider">{{ __('kb.label_confidence') }}</span>
               <span id="ai-confidence" class="text-xs font-mono bg-[#111722] px-2 py-0.5 rounded text-white border border-[#324467]"></span>
           </div>
           
           <div class="bg-[#111722] p-4 rounded-lg border border-[#324467] relative">
               <div class="absolute top-2 left-2 text-[#92a4c9]">
                   <span class="material-symbols-outlined text-[20px]">smart_toy</span>
               </div>
               <div id="ai-answer" class="text-sm text-gray-300 pl-8 whitespace-pre-wrap"></div>
           </div>

           <div>
              <p class="text-xs font-bold text-[#92a4c9] mb-2 uppercase tracking-wider">{{ __('kb.label_sources') }}</p>
              <ul id="ai-sources" class="text-xs text-gray-400 list-disc pl-5 space-y-1"></ul>
           </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const LANG = {
    loading: "{{ __('kb.js_loading') }}",
    import_success: "{{ __('kb.js_import_success') }}",
    import_error: "{{ __('kb.js_import_error') }}",
    uploading: "{{ __('kb.js_uploading') }}",
    upload_success: "{{ __('kb.js_upload_success') }}",
    upload_error: "{{ __('kb.js_upload_error') }}",
    test_ai_error: "{{ __('kb.js_test_ai_error') }}",
    connection_error: "{{ __('kb.js_connection_error') }}",
    no_answer: "{{ __('kb.js_no_answer') }}",
    min_chars: "{{ __('kb.js_min_chars') }}",
    url_required: "{{ __('kb.js_url_required') }}",
};
// Global function for profile update
async function updateKbProfile(kbId, profileId) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    try {
        const res = await fetch(`/kb/${kbId}/profile`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ business_profile_id: profileId || null })
        });
        const data = await res.json();
        if (data.ok) {
            // Show brief success indicator
            console.log('Profile updated for KB', kbId);
        }
    } catch (e) {
        console.error('Error updating KB profile:', e);
        alert(LANG.error_generic || 'Failed to update KB profile');
    }
}

(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  // ===== IMPORT URL
  const btnImport = document.getElementById('btn-import-url');
  btnImport?.addEventListener('click', async () => {
    const url = document.getElementById('kb-url')?.value?.trim();
    const title = document.getElementById('kb-title')?.value?.trim();

    if(!url){ alert(LANG.url_required); return; }

    btnImport.disabled = true;
    btnImport.innerHTML = LANG.loading;

    try{
      const res = await fetch('/kb/import-url', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ url, title }),
      });

      const data = await res.json();
      if(!data.ok) throw data;

      alert(LANG.import_success);
      window.location.reload();

    }catch(err){
      alert(err.message || LANG.js_import_error);
      console.error(err);
    }finally{
      btnImport.disabled = false;
      btnImport.innerHTML = 'Import';
    }
  });

  // ===== TAB SWITCHING
  const tabUrl = document.getElementById('tab-url');
  const tabFile = document.getElementById('tab-file');
  const tabManual = document.getElementById('tab-manual');
  const panelUrl = document.getElementById('panel-url');
  const panelFile = document.getElementById('panel-file');
  const panelManual = document.getElementById('panel-manual');

  function switchTab(mode){
      const tabs = [tabUrl, tabFile, tabManual];
      const panels = [panelUrl, panelFile, panelManual];
      
      tabs.forEach(t => t?.classList.remove('text-white', 'bg-[#1f2b40]', 'border-primary'));
      tabs.forEach(t => t?.classList.add('text-gray-400', 'border-transparent'));
      panels.forEach(p => p?.classList.add('hidden'));

      if(mode === 'url'){
          tabUrl.className = "flex-1 px-4 py-3 text-sm font-bold text-white bg-[#1f2b40] border-b-2 border-primary transition-colors";
          panelUrl.classList.remove('hidden');
      } else if(mode === 'file') {
          tabFile.className = "flex-1 px-4 py-3 text-sm font-bold text-white bg-[#1f2b40] border-b-2 border-primary transition-colors";
          panelFile.classList.remove('hidden');
      } else {
          tabManual.className = "flex-1 px-4 py-3 text-sm font-bold text-white bg-[#1f2b40] border-b-2 border-primary transition-colors";
          panelManual.classList.remove('hidden');
      }
  }

  tabUrl?.addEventListener('click', () => switchTab('url'));
  tabFile?.addEventListener('click', () => switchTab('file'));
  tabManual?.addEventListener('click', () => switchTab('manual'));

  // ===== FILE UPLOAD LOGIC
  const fileInput = document.getElementById('kb-file');
  const fileNameDisplay = document.getElementById('file-name');
  const btnUpload = document.getElementById('btn-import-file');
  
  fileInput?.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if(file){
          fileNameDisplay.textContent = "Selected: " + file.name + " (" + (file.size/1024).toFixed(1) + " KB)";
          fileNameDisplay.classList.remove('hidden');
          btnUpload.disabled = false;
      } else {
          fileNameDisplay.classList.add('hidden');
          btnUpload.disabled = true;
      }
  });

  btnUpload?.addEventListener('click', async () => {
      const file = fileInput.files[0];
      if(!file) return;

      const tags = document.getElementById('kb-file-tags').value;
      const formData = new FormData();
      formData.append('file', file);
      if(tags) formData.append('tags', tags);

      btnUpload.disabled = true;
      btnUpload.innerHTML = LANG.uploading;

      try {
          const res = await fetch('/kb/import-file', {
              method: 'POST',
              headers: {
                  'X-CSRF-TOKEN': csrf,
                  'Accept': 'application/json',
              },
              body: formData
          });

          const data = await res.json();
          if(!res.ok) throw new Error(data.message || 'Gagal upload file');

          alert(LANG.upload_success);
          window.location.reload();
      } catch (e) {
          alert('Error: ' + (e.message || LANG.upload_error));
          console.error(e);
      } finally {
          btnUpload.disabled = false;
          btnUpload.innerHTML = 'Upload & Process';
      }
  });

  // ===== Utilities
  function openModal(id){ 
      const el = document.getElementById(id);
      if(el) {
          el.classList.remove('hidden');
          setTimeout(() => {
              el.querySelector('div[class*="transform"]')?.classList.remove('scale-95', 'opacity-0');
          }, 10);
      }
  }
  function closeModal(id){ document.getElementById(id)?.classList.add('hidden'); }

  document.addEventListener('click', async (e) => {
    const openBtn  = e.target.closest('[data-modal-open]');
    const closeBtn = e.target.closest('[data-modal-close]');
    const tgl = e.target.closest('[data-action="toggle"]');
    const del = e.target.closest('[data-action="delete"]');
    const det = e.target.closest('[data-action="detail"]');

    if(openBtn){
      openModal(openBtn.getAttribute('data-modal-open'));
      return;
    }
    if(closeBtn){
      closeModal(closeBtn.getAttribute('data-modal-close'));
      return;
    }

    if(det){
      document.getElementById('kb-detail-title').textContent = det.dataset.title || 'Detail KB';
      document.getElementById('kb-detail-url').textContent   = det.dataset.url || '-';
      document.getElementById('kb-detail-content').textContent = det.dataset.content || '';
      openModal('kb-detail-modal');
      return;
    }

    const edt = e.target.closest('[data-action="edit"]');
    if(edt){
      document.getElementById('edit-kb-id').value = edt.dataset.id;
      document.getElementById('edit-kb-title').value = edt.dataset.title || '';
      document.getElementById('edit-kb-tags').value = edt.dataset.tags || '';
      document.getElementById('edit-kb-profile-id').value = edt.dataset.profileId || '';
      document.getElementById('edit-kb-content').value = edt.dataset.content || '';
      openModal('kb-edit-modal');
      return;
    }

    if(tgl){
      const id = tgl.dataset.id;
      const res = await fetch(`/kb/${id}/toggle`, {
        method: 'PATCH',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        }
      });
      const data = await res.json();
      if(data.ok) window.location.reload();
      return;
    }

    if(del){
      if(!confirm('Hapus KB ini?')) return;
      const id = del.dataset.id;
      const res = await fetch(`/kb/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        }
      });
      const data = await res.json();
      if(data.ok) document.getElementById(`kb-${id}`)?.remove();
      return;
    }
  });

  // ===== EDIT KB FORM
  const editForm = document.getElementById('kb-edit-form');
  const btnSave = document.getElementById('btn-save-kb');
  editForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('edit-kb-id').value;
    const formData = new FormData(editForm);
    
    btnSave.disabled = true;
    btnSave.innerHTML = '<span class="material-symbols-outlined animate-spin text-[18px]">sync</span> Menyimpan...';
    
    try {
      const res = await fetch(`/kb/${id}/update`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData
      });
      
      const data = await res.json();
      if(!res.ok) throw new Error(data.message || 'Gagal mengupdate KB');
      
      alert('KB Article berhasil diperbarui!');
      window.location.reload();
    } catch(err) {
      alert('Error: ' + err.message);
      console.error(err);
    } finally {
      btnSave.disabled = false;
      btnSave.innerHTML = 'Simpan Perubahan';
    }
  });

  // ===== TEST AI
  const btnTest = document.getElementById('btn-ai-test');
  const qEl = document.getElementById('ai-question');
  const wrap = document.getElementById('ai-result-wrap');
  const confEl = document.getElementById('ai-confidence');
  const ansEl = document.getElementById('ai-answer');
  const srcEl = document.getElementById('ai-sources');

  btnTest?.addEventListener('click', async () => {
    const q = (qEl.value || '').trim();
    if(q.length < 3){
      alert(LANG.min_chars);
      return;
    }

    btnTest.disabled = true;
    btnTest.innerHTML = '<span class="material-symbols-outlined animate-spin text-[18px]">sync</span> Testing...';
    wrap.classList.add('hidden');

    try{
      const res = await fetch(`/kb/test-ai`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ question: q }),
      });

      const data = await res.json();
      if(!data.ok){
        alert('Gagal test AI');
        return;
      }

      const r = data.result;

      if(!r){
        confEl.textContent = '0.00';
        confEl.className = 'text-xs font-mono bg-red-900/50 px-2 py-0.5 rounded text-red-200 border border-red-800';
        ansEl.textContent = LANG.no_answer;
        srcEl.innerHTML = '';
        wrap.classList.remove('hidden');
        return;
      }

      confEl.textContent = Number(r.confidence || 0).toFixed(2);
      if(r.confidence > 0.7) {
          confEl.className = 'text-xs font-mono bg-green-900/50 px-2 py-0.5 rounded text-green-200 border border-green-800';
      } else {
          confEl.className = 'text-xs font-mono bg-yellow-900/50 px-2 py-0.5 rounded text-yellow-200 border border-yellow-800';
      }
      
      ansEl.textContent = r.answer || '-';

      srcEl.innerHTML = '';
      (r.sources || []).forEach(s => {
        const li = document.createElement('li');
        li.innerHTML = `<span class="text-white font-medium">${s.title || 'KB'}</span> ${s.source_url ? `<a href="${s.source_url}" target="_blank" class="text-blue-400 hover:underline">(${s.source_url})</a>` : ''}`;
        srcEl.appendChild(li);
      });

      wrap.classList.remove('hidden');

    }catch(err){
      console.error(err);
      alert(LANG.connection_error);
    }finally{
      btnTest.disabled = false;
      btnTest.innerHTML = '<span class="material-symbols-outlined text-[18px]">play_arrow</span> Test Response';
    }
  });

})();
</script>
</body>
</html>
