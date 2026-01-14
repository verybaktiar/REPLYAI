@extends('layouts.dark')

@section('title', 'Pengaturan Profil Bisnis')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-white">Profil Bisnis AI</h2>
            <p class="text-sm text-text-secondary">Kelola profil bisnis untuk chatbot AI. Setiap device WhatsApp bisa menggunakan profil berbeda.</p>
        </div>
        <button onclick="openModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white rounded-lg font-medium transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Profil
        </button>
    </div>

    <!-- Success Alert -->
    @if(session('success'))
    <div class="flex items-center gap-3 rounded-lg border border-green-500/20 bg-green-500/10 p-4">
        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <p class="text-sm text-green-400">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Profiles List -->
    <div class="space-y-4">
        @forelse($profiles as $profile)
        <div class="rounded-xl border border-border-dark bg-surface-dark p-5 hover:border-primary/50 transition-all" data-profile-id="{{ $profile->id }}">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Profile Info -->
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center text-2xl">
                        {{ $profile->getIndustryIcon() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-semibold text-white truncate">{{ $profile->business_name }}</h3>
                            @if($profile->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                                ⭐ Default
                            </span>
                            @endif
                        </div>
                        <p class="text-sm text-text-secondary">{{ $profile->getIndustryLabel() }}</p>
                        <p class="text-xs text-text-secondary mt-1 line-clamp-1">{{ Str::limit($profile->system_prompt_template, 80) }}</p>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if(!$profile->is_active)
                    <button onclick="setDefault({{ $profile->id }})" class="px-3 py-2 text-xs font-medium text-yellow-400 bg-yellow-500/10 hover:bg-yellow-500/20 rounded-lg transition-colors" title="Jadikan Default">
                        ⭐ Set Default
                    </button>
                    @endif
                    <button onclick="editProfile({{ $profile->id }})" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    @if(!$profile->is_active || $profiles->count() > 1)
                    <button onclick="deleteProfile({{ $profile->id }})" class="p-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-colors" title="Hapus">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <!-- Empty State -->
        <div class="rounded-xl border border-border-dark bg-surface-dark p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl">🤖</span>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Belum Ada Profil Bisnis</h3>
                <p class="text-text-secondary mb-6">Buat profil bisnis pertama untuk mengkonfigurasi AI chatbot Anda.</p>
                <button onclick="openModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white rounded-lg font-medium transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Profil Pertama
                </button>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Profile Modal -->
<div id="profileModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/80 backdrop-blur-sm px-4 overflow-y-auto py-8">
    <div class="w-full max-w-2xl bg-surface-dark border border-border-dark rounded-xl shadow-2xl my-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-border-dark">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Tambah Profil Bisnis</h3>
            <button onclick="closeModal()" class="text-text-secondary hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="profileForm" class="p-6 space-y-5">
            <input type="hidden" id="profileId" value="">
            
            <!-- Business Name -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">🏢 Nama Bisnis <span class="text-red-400">*</span></label>
                <input type="text" id="business_name" name="business_name" required
                       class="w-full px-4 py-3 bg-background-dark border border-border-dark rounded-lg text-white placeholder-text-secondary focus:outline-none focus:border-primary transition-colors"
                       placeholder="Contoh: RS PKU Solo atau Toko Elektronik ABC">
            </div>

            <!-- Business Type -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">🏭 Tipe Industri <span class="text-red-400">*</span></label>
                <select id="business_type" name="business_type" required
                        class="w-full px-4 py-3 bg-background-dark border border-border-dark rounded-lg text-white focus:outline-none focus:border-primary transition-colors">
                    @php
                        $groupedIndustries = collect($industries)->map(function($item, $key) {
                            $item['key'] = $key;
                            return $item;
                        })->groupBy('group');
                    @endphp
                    @foreach($groupedIndustries as $group => $items)
                        <optgroup label="{{ $group }}">
                            @foreach($items as $industry)
                                <option value="{{ $industry['key'] }}">{{ $industry['icon'] }} {{ $industry['label'] }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <div class="mt-2 flex items-center gap-3">
                    <button type="button" onclick="loadTemplate()" class="text-xs text-primary hover:underline">
                        🔄 Muat Template Default
                    </button>
                </div>
            </div>

            <!-- System Prompt -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">🤖 System Prompt <span class="text-red-400">*</span></label>
                <textarea id="system_prompt_template" name="system_prompt_template" rows="8" required
                          class="w-full px-4 py-3 bg-background-dark border border-border-dark rounded-lg text-white placeholder-text-secondary focus:outline-none focus:border-primary transition-colors font-mono text-sm"
                          placeholder="Instruksi untuk AI chatbot..."></textarea>
                <p class="mt-1 text-xs text-text-secondary">
                    Placeholder: <code class="bg-background-dark px-1 rounded">{business_name}</code>, 
                    <code class="bg-background-dark px-1 rounded">{today}</code>, 
                    <code class="bg-background-dark px-1 rounded">{now}</code>
                </p>
            </div>

            <!-- Fallback Message -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">⚠️ Pesan Fallback (Opsional)</label>
                <textarea id="kb_fallback_message" name="kb_fallback_message" rows="2"
                          class="w-full px-4 py-3 bg-background-dark border border-border-dark rounded-lg text-white placeholder-text-secondary focus:outline-none focus:border-primary transition-colors"
                          placeholder="Pesan jika AI error..."></textarea>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-4 border-t border-border-dark">
                <button type="button" onclick="closeModal()" class="flex-1 px-6 py-3 bg-background-dark border border-border-dark text-white rounded-lg font-medium hover:bg-surface-dark transition-colors">
                    Batal
                </button>
                <button type="submit" class="flex-1 px-6 py-3 bg-primary hover:bg-primary/90 text-white rounded-lg font-medium transition-colors">
                    <span id="submitBtnText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const profilesData = @json($profiles);

function openModal(profile = null) {
    const modal = document.getElementById('profileModal');
    const form = document.getElementById('profileForm');
    const title = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('submitBtnText');
    
    form.reset();
    document.getElementById('profileId').value = '';
    
    if (profile) {
        title.textContent = 'Edit Profil Bisnis';
        submitBtn.textContent = 'Perbarui';
        document.getElementById('profileId').value = profile.id;
        document.getElementById('business_name').value = profile.business_name;
        document.getElementById('business_type').value = profile.business_type;
        document.getElementById('system_prompt_template').value = profile.system_prompt_template;
        document.getElementById('kb_fallback_message').value = profile.kb_fallback_message || '';
    } else {
        title.textContent = 'Tambah Profil Bisnis';
        submitBtn.textContent = 'Simpan';
        loadTemplate(); // Auto-load template for new profile
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('profileModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function editProfile(id) {
    const profile = profilesData.find(p => p.id === id);
    if (profile) {
        openModal(profile);
    }
}

async function loadTemplate() {
    const type = document.getElementById('business_type').value;
    try {
        const response = await fetch(`/api/business/template?type=${type}`);
        const data = await response.json();
        if (data.success) {
            document.getElementById('system_prompt_template').value = data.template;
            document.getElementById('kb_fallback_message').value = data.fallback_message;
        }
    } catch (error) {
        console.error('Error loading template:', error);
    }
}

document.getElementById('business_type').addEventListener('change', function() {
    if (!document.getElementById('profileId').value) {
        loadTemplate();
    }
});

document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const profileId = document.getElementById('profileId').value;
    const isEdit = !!profileId;
    
    const data = {
        business_name: document.getElementById('business_name').value,
        business_type: document.getElementById('business_type').value,
        system_prompt_template: document.getElementById('system_prompt_template').value,
        kb_fallback_message: document.getElementById('kb_fallback_message').value,
    };
    
    try {
        const url = isEdit ? `/settings/business/${profileId}` : '/settings/business';
        const response = await fetch(url, {
            method: isEdit ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeModal();
            showNotification(result.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(result.error || 'Gagal menyimpan profil', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan', 'error');
    }
});

async function deleteProfile(id) {
    if (!confirm('Yakin ingin menghapus profil ini?')) return;
    
    try {
        const response = await fetch(`/settings/business/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            document.querySelector(`[data-profile-id="${id}"]`).remove();
        } else {
            showNotification(result.error || 'Gagal menghapus profil', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan', 'error');
    }
}

async function setDefault(id) {
    try {
        const response = await fetch(`/settings/business/${id}/default`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(result.error || 'Gagal mengubah default', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan', 'error');
    }
}

function showNotification(message, type = 'info') {
    const bg = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-[99999] ${bg} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2`;
    toast.innerHTML = `<span>${type === 'success' ? '✅' : '❌'}</span> ${message}`;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('opacity-0', 'transition-opacity');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Close modal on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
@endpush
