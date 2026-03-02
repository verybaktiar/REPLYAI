@extends('layouts.dark')

@section('title', 'Setup Two-Factor Authentication')

@section('content')
<div class="max-w-3xl mx-auto py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white mb-2">Pengaturan Two-Factor Authentication</h1>
        <p class="text-slate-400">Tingkatkan keamanan akun Anda dengan mengaktifkan 2FA.</p>
    </div>
    
    <!-- Warning Banner -->
    <div class="mb-6 p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-xl flex items-start gap-3">
        <span class="material-symbols-outlined text-yellow-500 mt-0.5">warning</span>
        <div>
            <h3 class="font-semibold text-yellow-400 mb-1">Penting!</h3>
            <p class="text-sm text-yellow-200/70">
                Simpan recovery codes dengan aman. Jika Anda kehilangan akses ke authenticator, 
                recovery codes adalah satu-satunya cara untuk mengakses akun Anda.
            </p>
        </div>
    </div>
    
    <div class="space-y-6">
        <!-- Step 1: QR Code -->
        <div class="bg-slate-800/50 rounded-2xl border border-slate-700 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold">1</div>
                <h2 class="font-semibold">Scan QR Code</h2>
            </div>
            
            <p class="text-sm text-slate-400 mb-4">
                Buka aplikasi authenticator (Google Authenticator, Authy, Microsoft Authenticator) 
                dan scan QR code di bawah ini.
            </p>
            
            <div class="flex flex-col items-center gap-4">
                <!-- QR Code Placeholder -->
                <div class="p-4 bg-white rounded-xl">
                    <div class="w-48 h-48 bg-slate-200 flex items-center justify-center text-slate-400 text-xs text-center p-4">
                        QR Code<br>
                        <span class="break-all mt-2">{{ substr($qrCodeUrl, 0, 50) }}...</span>
                    </div>
                </div>
                
                <div class="text-center">
                    <p class="text-xs text-slate-500 mb-1">Atau masukkan secret key:</p>
                    <code class="px-3 py-1 bg-slate-900 rounded text-sm font-mono">{{ substr(Auth::user()->two_factor_secret, 0, 16) }}...</code>
                </div>
            </div>
        </div>
        
        <!-- Step 2: Recovery Codes -->
        <div class="bg-slate-800/50 rounded-2xl border border-slate-700 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold">2</div>
                <h2 class="font-semibold">Simpan Recovery Codes</h2>
            </div>
            
            <p class="text-sm text-slate-400 mb-4">
                Simpan recovery codes berikut di tempat yang aman. Setiap kode hanya dapat digunakan sekali.
            </p>
            
            <div class="grid grid-cols-2 gap-2 mb-4 p-4 bg-slate-900 rounded-xl">
                @foreach($recoveryCodes as $code)
                <div class="px-3 py-2 bg-slate-800 rounded-lg font-mono text-sm text-center">
                    {{ $code }}
                </div>
                @endforeach
            </div>
            
            <button onclick="copyRecoveryCodes()" class="w-full py-2 border border-slate-700 hover:bg-slate-700 rounded-lg text-sm transition flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-sm">content_copy</span>
                <span>Salin Recovery Codes</span>
            </button>
        </div>
        
        <!-- Step 3: Verify -->
        <div class="bg-slate-800/50 rounded-2xl border border-slate-700 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold">3</div>
                <h2 class="font-semibold">Verifikasi Setup</h2>
            </div>
            
            <p class="text-sm text-slate-400 mb-4">
                Masukkan kode 6 digit dari aplikasi authenticator untuk memverifikasi setup.
            </p>
            
            <form method="POST" action="{{ route('2fa.enable') }}">
                @csrf
                <div class="flex gap-2 mb-4">
                    <input type="text" name="code" maxlength="6"
                           class="flex-1 px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-center text-2xl font-bold tracking-widest focus:border-primary focus:outline-none"
                           placeholder="000000"
                           inputmode="numeric"
                           pattern="[0-9]*"
                           required>
                </div>
                
                @if($errors->has('code'))
                <p class="text-sm text-red-400 mb-4">{{ $errors->first('code') }}</p>
                @endif
                
                <div class="flex gap-3">
                    <a href="{{ route('settings.index') }}" class="flex-1 py-3 border border-slate-700 hover:bg-slate-700 rounded-xl font-medium text-center transition">
                        Batal
                    </a>
                    <button type="submit" class="flex-1 py-3 bg-primary hover:bg-primary/90 rounded-xl font-semibold transition">
                        Aktifkan 2FA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyRecoveryCodes() {
    const codes = @json($recoveryCodes);
    const text = codes.join('\n');
    
    navigator.clipboard.writeText(text).then(() => {
        alert('Recovery codes telah disalin ke clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}
</script>
@endsection
