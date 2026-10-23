<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Andromeda Flow') }} — Modern Task & Kanban Management</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex flex-col selection:bg-indigo-500 selection:text-white transition-colors duration-300">
    <!-- Confetti Canvas -->
    <canvas id="confetti-canvas" class="fixed inset-0 pointer-events-none z-50 w-full h-full"></canvas>

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none"></div>

    <!-- Main Navigation Bar -->
    <header class="sticky top-0 z-40 backdrop-blur-xl bg-slate-900/80 border-b border-slate-800/80 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard.index') }}" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-cyan-400 p-0.5 shadow-lg shadow-indigo-500/25 group-hover:shadow-indigo-500/40 transition-all duration-300">
                            <div class="w-full h-full bg-slate-950/80 backdrop-blur-sm rounded-[10px] flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-400 group-hover:scale-110 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <span class="text-xl font-bold tracking-tight bg-gradient-to-r from-white via-slate-200 to-indigo-300 bg-clip-text text-transparent font-heading">
                                Andromeda<span class="text-indigo-400 font-extrabold">Flow</span>
                            </span>
                            <span class="hidden sm:inline-block ml-2 px-1.5 py-0.5 text-[10px] uppercase font-bold tracking-wider rounded-md bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                v2.0
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Center Search Bar -->
                <div class="flex-1 max-w-md hidden md:block">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" id="global-search" placeholder="Cari tugas, deskripsi, atau proyek... (Tekan '/' untuk fokus)" 
                               class="w-full pl-10 pr-9 py-2 text-sm bg-slate-800/60 border border-slate-700/60 rounded-xl text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all">
                        <button id="clear-search" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300 hidden">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Right Action Controls -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Analytics Drawer Toggle Button -->
                    <button id="btn-open-analytics" title="Lihat Statistik & Metrik" 
                            class="p-2 sm:px-3 sm:py-2 text-sm font-medium rounded-xl bg-slate-800/80 hover:bg-slate-700/80 text-slate-300 hover:text-white border border-slate-700/60 flex items-center gap-2 transition-all">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="hidden sm:inline">Statistik</span>
                    </button>

                    <!-- Add New Project Button -->
                    <button id="btn-open-new-project-modal" title="Buat Proyek Baru"
                            class="p-2 sm:px-3 sm:py-2 text-sm font-medium rounded-xl bg-slate-800/80 hover:bg-slate-700/80 text-slate-300 hover:text-white border border-slate-700/60 flex items-center gap-2 transition-all">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="hidden sm:inline">Proyek Baru</span>
                    </button>

                    <!-- Add New Task Button -->
                    <button id="btn-open-new-task-modal" 
                            class="px-3.5 py-2 text-sm font-semibold rounded-xl bg-gradient-to-r from-indigo-500 via-indigo-600 to-indigo-700 hover:from-indigo-400 hover:to-indigo-600 text-white shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 flex items-center gap-2 transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Tugas Baru</span>
                    </button>

                    <!-- Dark/Light Mode Switcher -->
                    <button id="theme-toggle" title="Ganti Mode Gelap/Terang" 
                            class="p-2 rounded-xl text-slate-400 hover:text-amber-400 hover:bg-slate-800/80 transition-colors">
                        <svg id="theme-icon-dark" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg id="theme-icon-light" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Search Bar -->
            <div class="pb-3 md:hidden">
                <input type="text" id="mobile-search" placeholder="Cari tugas atau proyek..." 
                       class="w-full pl-3 pr-3 py-2 text-sm bg-slate-800/60 border border-slate-700/60 rounded-xl text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
            </div>
        </div>
    </header>

    <!-- Main Content Slot -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 bg-slate-900/40 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Andromeda Flow &copy; {{ date('Y') }} — Built with Laravel 12 &amp; Modern Vanilla JavaScript</span>
            </div>
            <div class="flex items-center gap-4">
                <span>Database: SQLite</span>
                <span>•</span>
                <span>Drag &amp; Drop: Native HTML5 + AJAX Sync</span>
            </div>
        </div>
    </footer>
</body>
</html>
