@extends('layouts.dark')

@section('title', 'Pengaturan WhatsApp')

@section('content')
<!-- Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Pengaturan WhatsApp Multi-Channel</h1>
            <p class="text-text-secondary">Kelola beberapa nomor WhatsApp sekaligus dengan sesi terpisah</p>
        </div>
        <button onclick="openAddDeviceModal()" class="inline-flex items-center gap-2 px-6 py-3 bg-whatsapp hover:bg-whatsapp/90 text-white rounded-lg font-medium transition-all shadow-lg shadow-whatsapp/20">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Device Baru
        </button>
    </div>
</div>

<!-- Info Alert -->
<div class="mb-8 bg-whatsapp/10 border border-whatsapp/20 rounded-lg p-5">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-10 h-10 bg-whatsapp rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-whatsapp mb-1">Multi-Channel WhatsApp</h3>
            <p class="text-text-secondary text-sm leading-relaxed">
                Anda dapat menghubungkan beberapa nomor WhatsApp sekaligus. Setiap device akan memiliki sesi terpisah dan dapat dikelola secara independen.
            </p>
        </div>
    </div>
</div>

<!-- Devices Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @forelse($devices as $device)
    <div class="bg-surface-dark border border-border-dark rounded-lg overflow-hidden hover:border-primary/50 transition-all" 
         data-device-id="{{ $device->id }}" 
         data-session-id="{{ $device->session_id }}">
        
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-border-dark bg-surface-dark/50">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-white truncate">{{ $device->device_name }}</h3>
                <span class="device-status-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium
                    @if($device->status === 'connected') bg-whatsapp/20 text-whatsapp
                    @elseif($device->status === 'scanning') bg-yellow-500/20 text-yellow-400
                    @elseif($device->status === 'disconnected') bg-red-500/20 text-red-400
                    @else bg-gray-500/20 text-gray-400
                    @endif">
                    <span class="w-1.5 h-1.5 rounded-full 
                        @if($device->status === 'connected') bg-whatsapp animate-pulse
                        @elseif($device->status === 'scanning') bg-yellow-400 animate-pulse
                        @elseif($device->status === 'disconnected') bg-red-400
                        @else bg-gray-400
                        @endif"></span>
                    <span class="device-status-text">{{ ucfirst($device->status) }}</span>
                </span>
            </div>
        </div>

        <!-- Card Body -->
        <div class="p-6">
            <!-- Device Info -->
            <div class="space-y-3 mb-5">
                @if($device->phone_number)
                <div class="flex items-center gap-3 text-sm">
                    <svg class="w-4 h-4 text-whatsapp flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span class="text-text-secondary">{{ $device->phone_number }}</span>
                </div>
                @endif

                @if($device->profile_name)
                <div class="flex items-center gap-3 text-sm">
                    <svg class="w-4 h-4 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-text-secondary">{{ $device->profile_name }}</span>
                </div>
                @endif

                <div class="flex items-center gap-3 text-sm">
                    <svg class="w-4 h-4 text-text-secondary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-text-secondary">{{ $device->updated_at->diffForHumans() }}</span>
                </div>

                <div class="flex items-center gap-3 text-sm">
                    <svg class="w-4 h-4 text-text-secondary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-text-secondary text-xs">{{ $device->session_id }}</span>
                </div>

                <!-- Business Profile Selector -->
                <div class="pt-3 border-t border-border-dark">
                    <label class="block text-xs font-medium text-text-secondary mb-2">Profil Bisnis AI</label>
                    <div class="flex gap-2">
                        <select onchange="updateDeviceProfile('{{ $device->session_id }}', this.value)" 
                                class="profile-select flex-1 px-3 py-2 bg-background-dark border border-border-dark rounded-lg text-sm text-white focus:outline-none focus:border-primary transition-colors">
                            <option value="">-- Default Profile --</option>
                            @foreach($businessProfiles as $profile)
                                <option value="{{ $profile->id }}" 
                                    {{ $device->business_profile_id == $profile->id ? 'selected' : '' }}>
                                    {{ $profile->business_name }} ({{ $profile->getIndustryLabel() }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if($device->businessProfile)
                    <p class="mt-2 text-xs text-primary flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        AI menggunakan: {{ $device->businessProfile->business_name }}
                    </p>
                    @endif
                </div>
            </div>

            <!-- QR Code Container (for scanning status) -->
            @if($device->status === 'scanning')
            <div class="qr-code-container mb-5 p-4 bg-background-dark rounded-lg border border-border-dark">
                <div class="text-center">
                    <div class="mb-3 flex justify-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-2 border-primary border-t-transparent"></div>
                    </div>
                    <p class="text-sm text-text-secondary mb-2">Memuat QR Code...</p>
                    <img class="qr-code-image hidden mx-auto rounded-lg" alt="QR Code" style="max-width: 200px;">
                    <p class="qr-instructions hidden text-xs text-text-secondary mt-2">Scan dengan WhatsApp Anda</p>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="flex gap-3">
                @if($device->status === 'connected')
                <a href="{{ route('whatsapp.inbox') }}" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-medium transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    Inbox
                </a>
                @endif

                <button onclick="disconnectDevice('{{ $device->session_id }}')" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg text-sm font-medium transition-all border border-red-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </div>
        </div>
    </div>
    @empty
    <!-- Empty State -->
    <div class="col-span-full">
        <div class="bg-surface-dark border border-border-dark rounded-lg p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="w-20 h-20 bg-whatsapp/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-whatsapp" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Belum Ada Device</h3>
                <p class="text-text-secondary mb-6">Mulai dengan menambahkan device WhatsApp pertama Anda untuk menggunakan fitur multi-channel.</p>
                <button onclick="openAddDeviceModal()" class="inline-flex items-center gap-2 px-6 py-3 bg-whatsapp hover:bg-whatsapp/90 text-white rounded-lg font-medium transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Device Pertama
                </button>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Add Device Modal -->
<div id="addDeviceModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/80 backdrop-blur-sm px-4">
    <div class="w-full max-w-md bg-surface-dark border border-border-dark rounded-lg shadow-2xl">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-border-dark">
            <h3 class="text-xl font-bold text-white">Tambah Device WhatsApp Baru</h3>
            <button onclick="closeAddDeviceModal()" class="text-text-secondary hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="addDeviceForm" onsubmit="addDevice(event)" class="p-6">
            <div class="mb-6">
                <label class="block text-sm font-medium text-white mb-2">
                    Nama Device <span class="text-red-400">*</span>
                </label>
                <input type="text" 
                       name="device_name" 
                       placeholder="Contoh: HP Admin Utama" 
                       required
                       class="w-full px-4 py-3 bg-background-dark border border-border-dark rounded-lg text-white placeholder-text-secondary focus:outline-none focus:border-primary transition-colors">
                <p class="mt-2 text-xs text-text-secondary">Berikan nama yang mudah dikenali untuk device ini</p>
            </div>

            <div class="flex gap-3">
                <button type="button" 
                        onclick="closeAddDeviceModal()" 
                        class="flex-1 px-6 py-3 bg-background-dark border border-border-dark text-white rounded-lg font-medium hover:bg-surface-dark transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-whatsapp hover:bg-whatsapp/90 text-white rounded-lg font-medium transition-colors">
                    Tambah
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let statusCheckIntervals = {};

// Modal Functions
function openAddDeviceModal() {
    const modal = document.getElementById('addDeviceModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeAddDeviceModal() {
    const modal = document.getElementById('addDeviceModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('addDeviceForm').reset();
}

// Add Device
async function addDevice(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const deviceName = formData.get('device_name');
    
    try {
        const response = await fetch('{{ route('whatsapp.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ device_name: deviceName })
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeAddDeviceModal();
            showNotification('Device berhasil ditambahkan! Silakan scan QR code.', 'success');
            
            // Reload page to show new device
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(result.error || 'Gagal menambahkan device', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menambahkan device', 'error');
    }
}

// Update Device Profile
async function updateDeviceProfile(sessionId, profileId) {
    try {
        const response = await fetch(`/whatsapp/device/${sessionId}/profile`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ business_profile_id: profileId || null })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Profil bisnis berhasil diperbarui!', 'success');
        } else {
            showNotification(result.error || 'Gagal memperbarui profil', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat memperbarui profil', 'error');
    }
}

// Disconnect Device
async function disconnectDevice(sessionId) {
    if (!confirm('Apakah Anda yakin ingin menghapus device ini?')) {
        return;
    }
    
    try {
        const response = await fetch(`/whatsapp/device/${sessionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Device berhasil dihapus', 'success');
            
            // Remove from DOM
            const deviceCard = document.querySelector(`[data-session-id="${sessionId}"]`);
            if (deviceCard) {
                deviceCard.style.opacity = '0';
                deviceCard.style.transform = 'scale(0.95)';
                deviceCard.style.transition = 'all 0.3s';
                
                setTimeout(() => {
                    deviceCard.remove();
                    
                    // Check if no devices left
                    if (document.querySelectorAll('[data-session-id]').length === 0) {
                        window.location.reload();
                    }
                }, 300);
            }
            
            // Clear status check interval
            if (statusCheckIntervals[sessionId]) {
                clearInterval(statusCheckIntervals[sessionId]);
                delete statusCheckIntervals[sessionId];
            }
        } else {
            showNotification(result.error || 'Gagal menghapus device', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menghapus device', 'error');
    }
}

// Check Device Status
async function checkDeviceStatus(sessionId) {
    try {
        const response = await fetch(`/whatsapp/status/${sessionId}`);
        const result = await response.json();
        
        const deviceCard = document.querySelector(`[data-session-id="${sessionId}"]`);
        if (!deviceCard) return;
        
        const statusBadge = deviceCard.querySelector('.device-status-badge');
        const statusText = deviceCard.querySelector('.device-status-text');
        const qrContainer = deviceCard.querySelector('.qr-code-container');
        
        // Update status badge
        if (statusBadge && statusText) {
            // Remove all status classes
            statusBadge.className = 'device-status-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium';
            
            if (result.status === 'connected') {
                statusBadge.classList.add('bg-whatsapp/20', 'text-whatsapp');
                statusText.textContent = 'Connected';
                
                // Stop checking if connected
                if (statusCheckIntervals[sessionId]) {
                    clearInterval(statusCheckIntervals[sessionId]);
                    delete statusCheckIntervals[sessionId];
                }
                
                // Reload to update UI
                setTimeout(() => window.location.reload(), 1000);
            } else if (result.status === 'scanning' || result.status === 'waiting_qr') {
                statusBadge.classList.add('bg-yellow-500/20', 'text-yellow-400');
                statusText.textContent = 'Scanning';
                
                // Load QR code
                if (qrContainer) {
                    loadQrCode(sessionId, qrContainer);
                }
            } else if (result.status === 'disconnected') {
                statusBadge.classList.add('bg-red-500/20', 'text-red-400');
                statusText.textContent = 'Disconnected';
            } else {
                statusBadge.classList.add('bg-gray-500/20', 'text-gray-400');
                statusText.textContent = 'Unknown';
            }
        }
    } catch (error) {
        console.error('Error checking status:', error);
    }
}

// Load QR Code
async function loadQrCode(sessionId, container) {
    console.log(`[DEBUG] Loading QR code for session: ${sessionId}`);
    
    try {
        const response = await fetch(`/whatsapp/qr/${sessionId}`);
        console.log(`[DEBUG] QR response status: ${response.status}`);
        
        const result = await response.json();
        console.log(`[DEBUG] QR result:`, result);
        
        if (result.success && result.qr) {
            const qrImage = container.querySelector('.qr-code-image');
            const spinner = container.querySelector('.animate-spin');
            const loadingText = container.querySelector('p');
            const instructions = container.querySelector('.qr-instructions');
            
            if (qrImage) {
                qrImage.src = result.qr;
                qrImage.classList.remove('hidden');
                if (spinner) spinner.remove();
                if (loadingText) loadingText.remove();
                if (instructions) instructions.classList.remove('hidden');
                console.log(`[DEBUG] QR code displayed successfully`);
            }
        } else {
            console.log(`[DEBUG] No QR code available yet, will retry...`);
        }
    } catch (error) {
        console.error('[DEBUG] Error loading QR code:', error);
    }
}

// Show Notification
function showNotification(message, type = 'info') {
    // Simple notification using alert for now
    // You can replace this with a toast library
    alert(message);
}

// Initialize status checking for scanning devices
document.addEventListener('DOMContentLoaded', function() {
    console.log('[DEBUG] Page loaded, initializing status checks...');
    const scanningDevices = document.querySelectorAll('[data-session-id]');
    console.log(`[DEBUG] Found ${scanningDevices.length} devices`);
    
    scanningDevices.forEach(device => {
        const sessionId = device.dataset.sessionId;
        const statusText = device.querySelector('.device-status-text');
        
        console.log(`[DEBUG] Device ${sessionId} status: ${statusText?.textContent.trim()}`);
        
        if (statusText && statusText.textContent.trim().toLowerCase() === 'scanning') {
            console.log(`[DEBUG] Starting status check for ${sessionId}`);
            
            // Initial immediate check
            checkDeviceStatus(sessionId);
            
            // Check status every 2 seconds (faster than before)
            statusCheckIntervals[sessionId] = setInterval(() => {
                checkDeviceStatus(sessionId);
            }, 2000);
        }
    });
});

// Cleanup intervals on page unload
window.addEventListener('beforeunload', function() {
    Object.values(statusCheckIntervals).forEach(interval => clearInterval(interval));
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddDeviceModal();
    }
});
</script>
@endpush
@endsection
