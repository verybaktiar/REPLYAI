<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? config('app.name', 'ReplyAI') }}</title>
    
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind & App JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Layout Algorithm Helpers */
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Prevent elastic scroll on iOS */
        body { overscroll-behavior-y: none; }
        
        /* Custom Scrollbar for Dark Theme */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #4b5563; }
    </style>
</head>
<body class="bg-gray-950 text-white font-sans antialiased overflow-hidden selection:bg-blue-600/30">
    
    <!-- THE ROOT CAGE: h-[100dvh] + flex + overflow-hidden -->
    <div 
        class="h-[100dvh] bg-gray-950 flex overflow-hidden" 
        x-data="{ sidebarOpen: false, activeView: 'master' }"
        @keydown.escape.window="sidebarOpen = false"
    >
        
        <!-- SIDEBAR (Left) -->
        <!-- z-50 for mobile drawer, w-64 fixed width, flex-shrink-0 prevents compression -->
        <aside 
            id="sidebar"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed lg:static inset-y-0 left-0 z-50 w-64 flex-shrink-0 flex flex-col border-r border-gray-800 bg-gray-900 transition-transform duration-300 ease-in-out"
        >
            <x-enterprise-sidebar />
        </aside>

        <!-- BACKDROP (Mobile) -->
        <!-- z-40 sits between sidebar and content -->
        <div 
            x-show="sidebarOpen" 
            x-transition:enter="transition opacity-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition opacity-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
            x-cloak
        ></div>

        <!-- MAIN LAYOUT WRAPPER (Middle + Right) -->
        <!-- flex-1 + min-w-0 ensures children can shrink properly and respect flex rules -->
        <div class="flex-1 min-w-0 flex flex-row">
            {{ $slot }}
        </div>
        
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
