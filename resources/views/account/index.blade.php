<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>My Account - ReplyAI</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-dark": "#101622",
                        "surface-dark": "#1a2230",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-background-dark text-white font-display antialiased min-h-screen">
    
    <!-- Navbar -->
    <nav class="bg-surface-dark border-b border-slate-700">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <a href="/" class="flex items-center gap-2 text-2xl font-black">
                    <span class="text-primary">REPLY</span>
                    <span class="text-white">AI</span>
                </a>
                <div class="flex items-center gap-4">
                    @if(auth()->user()->subscription && auth()->user()->subscription->status === 'active')
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-primary hover:bg-primary/90 rounded-lg font-semibold transition">
                        Dashboard
                    </a>
                    @else
                    <a href="/pricing" class="text-slate-300 hover:text-white transition">Pricing</a>
                    @endif
                    <a href="/account" class="text-primary font-semibold">My Account</a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-lg transition">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-6 py-12">
        
        <h1 class="text-4xl font-black mb-8">My Account</h1>

        <!-- User Info -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-700 mb-6">
            <h2 class="font-bold text-xl mb-4">Account Information</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <div class="text-slate-400 text-sm mb-1">Name</div>
                    <div class="font-semibold">{{ auth()->user()->name }}</div>
                </div>
                <div>
                    <div class="text-slate-400 text-sm mb-1">Email</div>
                    <div class="font-semibold">{{ auth()->user()->email }}</div>
                </div>
            </div>
        </div>

        <!-- Subscription Status -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-700 mb-6">
            <h2 class="font-bold text-xl mb-4">Subscription Status</h2>
            
            @if(auth()->user()->subscription)
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-slate-400 text-sm">Current Plan</div>
                            <div class="text-2xl font-bold">{{ auth()->user()->subscription->plan->name }}</div>
                        </div>
                        <div>
                            @if(auth()->user()->subscription->status === 'active')
                            <span class="px-4 py-2 rounded-full text-sm font-bold bg-green-500/20 text-green-400 border border-green-500/30">
                                ✓ Active
                            </span>
                            @elseif(auth()->user()->subscription->status === 'pending')
                            <span class="px-4 py-2 rounded-full text-sm font-bold bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                                ⏳ Pending
                            </span>
                            @else
                            <span class="px-4 py-2 rounded-full text-sm font-bold bg-red-500/20 text-red-400 border border-red-500/30">
                                Expired
                            </span>
                            @endif
                        </div>
                    </div>

                    @if(auth()->user()->subscription->status === 'pending')
                    <div class="p-4 rounded-xl bg-yellow-500/10 border border-yellow-500/30 text-yellow-300 text-sm">
                        ⏳ Pembayaran Anda sedang diverifikasi. Silakan tunggu 1-24 jam.
                    </div>
                    @endif

                    @if(auth()->user()->subscription->status === 'active')
                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-slate-400 mb-1">Starts</div>
                            <div>{{ auth()->user()->subscription->starts_at?->format('d M Y') ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-slate-400 mb-1">Ends</div>
                            <div>{{ auth()->user()->subscription->expires_at?->format('d M Y') ?? '-' }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-slate-400 mb-4">You don't have an active subscription yet.</p>
                    <a href="/pricing" class="inline-flex px-6 py-3 bg-primary hover:bg-primary/90 rounded-xl font-semibold transition">
                        Choose a Plan
                    </a>
                </div>
            @endif
        </div>

        <!-- Payment History -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-700">
            <h2 class="font-bold text-xl mb-4">Payment History</h2>
            
            @php
                $payments = auth()->user()->payments()->with('plan')->orderBy('created_at', 'desc')->get();
            @endphp

            @if($payments->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-slate-700">
                        <tr class="text-left text-sm text-slate-400">
                            <th class="pb-3">Invoice</th>
                            <th class="pb-3">Plan</th>
                            <th class="pb-3">Amount</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3">Date</th>
                            <th class="pb-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @foreach($payments as $payment)
                        <tr class="text-sm">
                            <td class="py-4">
                                <span class="font-mono">{{ $payment->invoice_number }}</span>
                            </td>
                            <td class="py-4">{{ $payment->plan->name }}</td>
                            <td class="py-4 font-semibold">Rp {{ number_format($payment->total, 0, ',', '.') }}</td>
                            <td class="py-4">
                                @if($payment->status === 'pending')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-500/20 text-yellow-400">Pending</span>
                                @elseif($payment->status === 'paid')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-500/20 text-green-400">Paid</span>
                                @else
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-500/20 text-red-400">Rejected</span>
                                @endif
                            </td>
                            <td class="py-4 text-slate-400">{{ $payment->created_at->format('d M Y') }}</td>
                            <td class="py-4">
                                <a href="{{ route('checkout.payment', $payment->invoice_number) }}" 
                                   class="text-primary hover:underline text-sm font-semibold">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8 text-slate-500">
                <p>No payment history yet</p>
            </div>
            @endif
        </div>

    </div>

</body>
</html>
