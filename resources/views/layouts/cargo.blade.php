<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark uk-theme-zinc">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Andromeda Cargo &amp; Billing System')</title>

    <!-- Immediate Theme Initialization to avoid flash -->
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = savedTheme ? (savedTheme === 'dark') : prefersDark;
            const root = document.documentElement;
            if (isDark) {
                root.classList.add('dark');
                root.classList.remove('light');
                root.style.colorScheme = 'dark';
            } else {
                root.classList.remove('dark');
                root.classList.add('light');
                root.style.colorScheme = 'light';
            }
        })();
    </script>

    <!-- Google Fonts (Geist Sans & Mono) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800&family=Geist+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Franken UI Stylesheet (UIkit 3 + Shadcn/ui) -->
    <link rel="stylesheet" href="{{ asset('vendor/franken-ui/css/core.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/franken-ui/css/utilities.min.css') }}">

    <!-- Franken UI Scripts -->
    <script src="{{ asset('vendor/franken-ui/js/core.iife.js') }}"></script>
    <script src="{{ asset('vendor/franken-ui/js/icon.iife.js') }}"></script>

    <!-- jQuery & Select2 (Local Assets) -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script>
        if (typeof window.jQuery !== 'undefined') {
            if (!window.jQuery.isArray) {
                window.jQuery.isArray = Array.isArray;
            }
            if (!window.jQuery.trim) {
                window.jQuery.trim = function(str) {
                    return str == null ? '' : (str + '').trim();
                };
            }
            if (!window.jQuery.isFunction) {
                window.jQuery.isFunction = function(obj) {
                    return typeof obj === 'function';
                };
            }
        }
    </script>
    <link rel="stylesheet" href="{{ asset('vendor/select2/select2.min.css') }}">
    <script src="{{ asset('vendor/select2/select2.min.js') }}"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html {
            font-size: 13.5px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        body {
            font-family: 'Geist', ui-sans-serif, system-ui, -apple-system, sans-serif;
            font-size: 0.925rem;
            line-height: 1.45;
        }
        .font-mono {
            font-family: 'Geist Mono', ui-monospace, SFMono-Regular, monospace;
        }
        /* Compact table cell heights & refined typography */
        .uk-table th, .uk-table td {
            padding-top: 0.55rem !important;
            padding-bottom: 0.55rem !important;
            font-size: 0.8125rem;
        }
        .uk-table th {
            font-size: 0.725rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        /* Compact button sizing */
        .uk-btn-sm, .uk-button-sm {
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
            font-size: 0.775rem !important;
        }
        /* Compact input sizing */
        .uk-input, .uk-select, .uk-textarea {
            font-size: 0.8125rem !important;
        }
        /* Compact badge styling */
        .uk-badge {
            font-size: 0.675rem !important;
            padding: 0.15rem 0.45rem !important;
        }
        /* Sidebar slide transition */
        #app-sidebar {
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* ==========================================================================
           Select2 Theme (Franken UI / Zinc Dark & Light Adaptive)
           ========================================================================== */
        .select2-container {
            width: 100% !important;
            font-size: 0.8125rem !important;
        }
        .select2-container .select2-selection--single {
            height: 34px !important;
            border-radius: 0.375rem !important;
            display: flex !important;
            align-items: center !important;
            transition: all 0.15s ease-in-out !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 32px !important;
            padding-left: 0.75rem !important;
            padding-right: 2rem !important;
            font-size: 0.8125rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 32px !important;
            right: 8px !important;
            display: flex !important;
            align-items: center !important;
        }
        .select2-dropdown {
            border-radius: 0.375rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -4px rgba(0, 0, 0, 0.2) !important;
            z-index: 9999 !important;
            overflow: hidden !important;
        }
        .select2-container--default .select2-search--dropdown {
            padding: 0.45rem !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border-radius: 0.25rem !important;
            padding: 0.35rem 0.6rem !important;
            font-size: 0.8125rem !important;
            outline: none !important;
            width: 100% !important;
        }
        .select2-results__option {
            padding: 0.45rem 0.75rem !important;
            font-size: 0.8125rem !important;
            cursor: pointer !important;
        }

        /* Dark Mode Select2 Styles */
        html.dark .select2-container .select2-selection--single {
            background-color: #18181b !important;
            border: 1px solid #27272a !important;
        }
        html.dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fafafa !important;
        }
        html.dark .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #71717a !important;
        }
        html.dark .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #a1a1aa transparent transparent transparent !important;
        }
        html.dark .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #a1a1aa transparent !important;
        }
        html.dark .select2-container--default.select2-container--focus .select2-selection--single,
        html.dark .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #a1a1aa !important;
            box-shadow: 0 0 0 1px #a1a1aa !important;
        }
        html.dark .select2-dropdown {
            background-color: #18181b !important;
            border: 1px solid #27272a !important;
        }
        html.dark .select2-container--default .select2-search--dropdown {
            background-color: #18181b !important;
            border-bottom: 1px solid #27272a !important;
        }
        html.dark .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #09090b !important;
            border: 1px solid #27272a !important;
            color: #fafafa !important;
        }
        html.dark .select2-results__option {
            color: #fafafa !important;
        }
        html.dark .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #27272a !important;
            color: #ffffff !important;
        }
        html.dark .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: rgba(99, 102, 241, 0.25) !important;
            color: #818cf8 !important;
            font-weight: 600 !important;
        }

        /* Light Mode Select2 Styles */
        html.light .select2-container .select2-selection--single {
            background-color: #ffffff !important;
            border: 1px solid #e4e4e7 !important;
        }
        html.light .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #09090b !important;
        }
        html.light .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #a1a1aa !important;
        }
        html.light .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #71717a transparent transparent transparent !important;
        }
        html.light .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #71717a transparent !important;
        }
        html.light .select2-container--default.select2-container--focus .select2-selection--single,
        html.light .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #18181b !important;
            box-shadow: 0 0 0 1px #18181b !important;
        }
        html.light .select2-dropdown {
            background-color: #ffffff !important;
            border: 1px solid #e4e4e7 !important;
        }
        html.light .select2-container--default .select2-search--dropdown {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e4e4e7 !important;
        }
        html.light .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #f4f4f5 !important;
            border: 1px solid #e4e4e7 !important;
            color: #09090b !important;
        }
        html.light .select2-results__option {
            color: #09090b !important;
        }
        html.light .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #f4f4f5 !important;
            color: #09090b !important;
        }
        html.light .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: rgba(99, 102, 241, 0.12) !important;
            color: #4f46e5 !important;
            font-weight: 600 !important;
        }

        /* ==========================================================================
           High-Contrast Badges (Light & Dark Mode)
           ========================================================================== */
        .badge-tipe-pernama {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.55rem;
            border-radius: 0.25rem;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.03em;
            background-color: #dbeafe !important; /* blue-100 */
            color: #1e3a8a !important;            /* blue-900 high contrast */
            border: 1px solid #93c5fd !important;
        }
        html.dark .badge-tipe-pernama {
            background-color: rgba(30, 58, 138, 0.35) !important;
            color: #93c5fd !important;
            border-color: #1e40af !important;
        }

        .badge-tipe-pecahan {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.55rem;
            border-radius: 0.25rem;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.03em;
            background-color: #f3e8ff !important; /* purple-100 */
            color: #581c87 !important;            /* purple-900 high contrast */
            border: 1px solid #d8b4fe !important;
        }
        html.dark .badge-tipe-pecahan {
            background-color: rgba(107, 33, 168, 0.35) !important;
            color: #d8b4fe !important;
            border-color: #7e22ce !important;
        }

        .badge-metode-transfer {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.55rem;
            border-radius: 0.25rem;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            background-color: #e0f2fe !important; /* sky-100 */
            color: #0369a1 !important;            /* sky-800 */
            border: 1px solid #7dd3fc !important;
        }
        html.dark .badge-metode-transfer {
            background-color: rgba(3, 105, 161, 0.3) !important;
            color: #7dd3fc !important;
            border-color: #0284c7 !important;
        }

        .badge-metode-tunai {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.55rem;
            border-radius: 0.25rem;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            background-color: #d1fae5 !important; /* emerald-100 */
            color: #065f46 !important;            /* emerald-800 */
            border: 1px solid #6ee7b7 !important;
        }
        html.dark .badge-metode-tunai {
            background-color: rgba(6, 95, 70, 0.3) !important;
            color: #6ee7b7 !important;
            border-color: #059669 !important;
        }

        .badge-metode-giro {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.55rem;
            border-radius: 0.25rem;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            background-color: #fef3c7 !important; /* amber-100 */
            color: #92400e !important;            /* amber-800 */
            border: 1px solid #fcd34d !important;
        }
        html.dark .badge-metode-giro {
            background-color: rgba(146, 64, 14, 0.3) !important;
            color: #fcd34d !important;
            border-color: #d97706 !important;
        }

        .badge-metode-qris {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.55rem;
            border-radius: 0.25rem;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            background-color: #ede9fe !important; /* violet-100 */
            color: #5b21b6 !important;            /* violet-800 */
            border: 1px solid #c4b5fd !important;
        }
        html.dark .badge-metode-qris {
            background-color: rgba(91, 33, 182, 0.3) !important;
            color: #c4b5fd !important;
            border-color: #7c3aed !important;
        }

        /* App Brand Logo Theme Switching */
        .app-logo-transparent {
            display: inline-block !important;
        }
        .app-logo-white {
            display: none !important;
        }
        html.dark .app-logo-transparent {
            display: none !important;
        }
        html.dark .app-logo-white {
            display: inline-block !important;
        }
    </style>
</head>
<body class="bg-background text-foreground antialiased min-h-screen flex transition-colors duration-200">
    <!-- Mobile Sidebar Backdrop Overlay -->
    <div id="sidebar-backdrop" 
         class="fixed inset-0 bg-background/80 backdrop-blur-sm z-40 hidden transition-opacity md:hidden"
         onclick="toggleMobileSidebar(false)"></div>

    <!-- Left Sidebar Navigation (Franken UI / Shadcn Style) -->
    <aside id="app-sidebar" 
           class="fixed inset-y-0 left-0 z-50 w-64 bg-card/95 backdrop-blur-md border-r border-border flex flex-col justify-between transition-transform duration-300 ease-in-out -translate-x-full md:translate-x-0 md:static md:sticky md:top-0 md:h-screen md:w-60 lg:w-64 flex-shrink-0 select-none">
        
        <!-- Sidebar Brand Header -->
        <div class="h-14 px-4 flex items-center justify-between border-b border-border">
            <a href="{{ route('cargo.dashboard') }}" class="flex items-center gap-2.5">
                <div class="h-9 flex items-center">
                    <img src="{{ asset('images/logo-transparent.png') }}" alt="Andro Meda Group" class="app-logo-transparent h-8 w-auto object-contain">
                    <img src="{{ asset('images/logo-white.png') }}" alt="Andro Meda Group" class="app-logo-white h-8 w-auto object-contain">
                </div>
                <div class="flex flex-col">
                    <div class="text-xs font-bold tracking-tight text-foreground leading-none">
                        ANDRO MEDA GROUP
                    </div>
                    <span class="text-[9px] font-semibold text-muted-foreground uppercase tracking-wider mt-0.5">
                        Cargo &amp; Piutang ERP
                    </span>
                </div>
            </a>

            <!-- Mobile Close Button -->
            <button type="button" 
                    id="mobile-close-btn"
                    onclick="toggleMobileSidebar(false)" 
                    class="md:hidden p-1.5 text-muted-foreground hover:text-foreground rounded-md hover:bg-muted cursor-pointer"
                    aria-label="Tutup Menu">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Sidebar Scrollable Navigation Links -->
        <div class="flex-1 overflow-y-auto px-3 py-3 space-y-4">
            <!-- Group 1: Operasional -->
            <div>
                <div class="px-2.5 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-muted-foreground/80">
                    Operasional Kargo
                </div>
                <nav class="space-y-0.5">
                    <!-- Dashboard -->
                    <a href="{{ route('cargo.dashboard') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-md transition-colors {{ request()->routeIs('cargo.dashboard') ? 'bg-secondary text-secondary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/70' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('cargo.dashboard') ? 'text-primary' : 'text-muted-foreground' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Master Customer -->
                    <a href="{{ route('customers.index') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-md transition-colors {{ request()->routeIs('customers.*') ? 'bg-secondary text-secondary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/70' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('customers.*') ? 'text-primary' : 'text-muted-foreground' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>Master Customer</span>
                    </a>

                    <!-- Lembar Kerja (SMU) -->
                    <a href="{{ route('worksheets.index') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-md transition-colors {{ request()->routeIs('worksheets.*') ? 'bg-secondary text-secondary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/70' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('worksheets.*') ? 'text-primary' : 'text-muted-foreground' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Lembar Kerja (SMU)</span>
                    </a>

                    @if(auth()->user()->hasPermission('invoices.view'))
                    <!-- Nota & Piutang -->
                    <a href="{{ route('invoices.index') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-md transition-colors {{ request()->routeIs('invoices.*') ? 'bg-secondary text-secondary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/70' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('invoices.*') ? 'text-primary' : 'text-muted-foreground' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                        </svg>
                        <span>Nota &amp; Piutang</span>
                    </a>
                    @endif

                    @if(auth()->user()->hasPermission('payments.view'))
                    <!-- Pembayaran Piutang -->
                    <a href="{{ route('payments.index') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-md transition-colors {{ request()->routeIs('payments.*') ? 'bg-secondary text-secondary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/70' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('payments.*') ? 'text-primary' : 'text-muted-foreground' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>Pembayaran Piutang</span>
                    </a>
                    @endif

                    @if(auth()->user()->hasPermission('ias_payments.view'))
                    <!-- Pembayaran ke IAS (InJourney Aviation Services) -->
                    <a href="{{ route('ias.payments.index') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-md transition-colors {{ request()->routeIs('ias.payments.*') ? 'bg-secondary text-secondary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/70' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('ias.payments.*') ? 'text-primary' : 'text-muted-foreground' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <span>Pembayaran ke IAS</span>
                    </a>
                    @endif
                </nav>
            </div>

            <!-- Group 2: Laporan & Keuangan -->
            <div>
                <div class="px-2.5 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-muted-foreground/80">
                    Laporan &amp; Keuangan
                </div>
                <nav class="space-y-0.5">
                    @if(auth()->user()->hasPermission('reports.worksheets'))
                    <!-- Lap Lembar Kerja -->
                    <a href="{{ route('reports.worksheets') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-md transition-colors {{ request()->routeIs('reports.worksheets') ? 'bg-secondary text-secondary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/70' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('reports.worksheets') ? 'text-primary' : 'text-muted-foreground' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Lap. Lembar Kerja</span>
                    </a>
                    @endif

                    @if(auth()->user()->hasPermission('reports.invoices'))
                    <!-- Lap Nota Penagihan -->
                    <a href="{{ route('reports.invoices') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-md transition-colors {{ request()->routeIs('reports.invoices') ? 'bg-secondary text-secondary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/70' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('reports.invoices') ? 'text-primary' : 'text-muted-foreground' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                        <span>Lap. Nota Penagihan</span>
                    </a>
                    @endif

                    @if(auth()->user()->hasPermission('reports.receivables'))
                    <!-- Monitoring Kartu Piutang -->
                    <a href="{{ route('reports.receivables') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-md transition-colors {{ request()->routeIs('reports.receivables') ? 'bg-secondary text-secondary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/70' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('reports.receivables') ? 'text-primary' : 'text-muted-foreground' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span>Monitoring Piutang</span>
                    </a>
                    @endif

                    @if(auth()->user()->hasPermission('reports.profit_loss'))
                    <!-- Laporan Laba Rugi -->
                    <a href="{{ route('reports.profit_loss') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-md transition-colors {{ request()->routeIs('reports.profit_loss') ? 'bg-secondary text-secondary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/70' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('reports.profit_loss') ? 'text-primary' : 'text-muted-foreground' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Laporan Laba Rugi</span>
                    </a>

                    <!-- Rekap Kas & Profit Harian -->
                    <a href="{{ route('reports.daily_performance') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-md transition-colors {{ request()->routeIs('reports.daily_performance*') ? 'bg-secondary text-secondary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/70' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('reports.daily_performance*') ? 'text-primary' : 'text-muted-foreground' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span>Rekap Kas &amp; Profit</span>
                    </a>
                    @endif
                </nav>
            </div>

            @if(auth()->user()->hasPermission('users.manage') || auth()->user()->hasPermission('roles.manage'))
            <!-- Group 3: Pengaturan & Sistem -->
            <div>
                <div class="px-2.5 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-muted-foreground/80">
                    Pengaturan &amp; Sistem
                </div>
                <nav class="space-y-0.5">
                    @if(auth()->user()->hasPermission('users.manage'))
                    <!-- Manajemen User -->
                    <a href="{{ route('users.index') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-md transition-colors {{ request()->routeIs('users.*') ? 'bg-secondary text-secondary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/70' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('users.*') ? 'text-primary' : 'text-muted-foreground' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span>Manajemen User</span>
                    </a>
                    @endif

                    @if(auth()->user()->hasPermission('roles.manage'))
                    <!-- Hak Akses Role -->
                    <a href="{{ route('roles.index') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-md transition-colors {{ request()->routeIs('roles.*') ? 'bg-secondary text-secondary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/70' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('roles.*') ? 'text-primary' : 'text-muted-foreground' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span>Hak Akses Role</span>
                    </a>
                    @endif
                </nav>
            </div>
            @endif

            <!-- Mini ERP Guidance Box -->
            <div class="px-2 pt-2">
                <div class="p-2.5 rounded-lg bg-muted/40 border border-border/70 text-[11px] space-y-1">
                    <div class="font-semibold text-foreground flex items-center gap-1.5">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Konsolidasi SMU
                    </div>
                    <p class="text-muted-foreground text-[10px] leading-relaxed">
                        1 Master SMU &rarr; Alokasi Banyak Customer &rarr; Terbitkan Nota &rarr; Pelunasan.
                    </p>
                </div>
            </div>
        </div>

        <!-- Sidebar Footer: User Account & Theme Toggle -->
        <div class="p-3 border-t border-border bg-card">
            @auth
                <div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-muted/40 border border-border/60 mb-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 rounded-full bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-xs flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-foreground truncate leading-tight">
                                {{ auth()->user()->name }}
                            </div>
                            <div class="text-[9px] font-mono font-medium text-muted-foreground uppercase leading-tight">
                                {{ auth()->user()->role }}
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" title="Keluar / Logout" class="p-1.5 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            @endauth

            <div class="flex items-center justify-between gap-2 px-1 text-xs">
                <span class="text-[11px] text-muted-foreground">Tampilan Tema</span>
                <button type="button" id="theme-toggle"
                        title="Ganti Mode Gelap/Terang" 
                        class="theme-toggle-btn px-2.5 py-1 text-xs font-medium rounded border border-border bg-muted/60 hover:bg-muted text-foreground flex items-center gap-1.5 transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5 theme-icon-sun hidden text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <svg class="w-3.5 h-3.5 theme-icon-moon hidden text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <span class="theme-text-label text-[11px]">Tema</span>
                </button>
            </div>
        </div>
    </aside>

    <!-- Main Application Area (Topbar + Content + Footer) -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">
        <!-- Compact Topbar -->
        <header class="sticky top-0 z-30 h-13 bg-background/95 backdrop-blur-md border-b border-border flex items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <!-- Mobile Hamburger Button -->
                <button type="button" 
                        id="mobile-menu-btn"
                        onclick="toggleMobileSidebar(true)" 
                        class="md:hidden p-1.5 rounded-md text-muted-foreground hover:text-foreground hover:bg-muted cursor-pointer"
                        aria-label="Buka Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Breadcrumb / Page Title Indicator -->
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-muted-foreground hidden sm:inline">Andro Meda Group</span>
                    <span class="text-muted-foreground hidden sm:inline">/</span>
                    <span class="font-semibold text-foreground">
                        @if(request()->routeIs('cargo.dashboard'))
                            Dashboard Operasional
                        @elseif(request()->routeIs('customers.*'))
                            Master Pelanggan
                        @elseif(request()->routeIs('worksheets.*'))
                            Lembar Kerja SMU
                        @elseif(request()->routeIs('invoices.*'))
                            Nota Penagihan &amp; Piutang
                        @elseif(request()->routeIs('payments.*'))
                            Pembayaran Piutang
                        @elseif(request()->routeIs('ias.payments.*'))
                            Pembayaran ke IAS
                        @elseif(request()->routeIs('reports.worksheets'))
                            Laporan Lembar Kerja
                        @elseif(request()->routeIs('reports.invoices'))
                            Laporan Nota Penagihan
                        @elseif(request()->routeIs('reports.receivables'))
                            Monitoring Kartu Piutang
                        @elseif(request()->routeIs('reports.profit_loss'))
                            Laporan Laba Rugi
                        @elseif(request()->routeIs('users.*'))
                            Manajemen User
                        @else
                            Sistem Kargo
                        @endif
                    </span>
                </div>
            </div>

            <!-- Topbar Right Items -->
            <div class="flex items-center gap-3">
                <span class="hidden lg:inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Sistem Aktif
                </span>
                <span class="text-xs text-muted-foreground hidden sm:inline">
                    {{ now()->translatedFormat('d F Y') }}
                </span>

                <!-- Quick theme toggle button in topbar -->
                <button type="button" 
                        class="theme-toggle-btn md:hidden p-1.5 rounded-md text-muted-foreground hover:text-foreground hover:bg-muted cursor-pointer"
                        title="Ganti Tema">
                    <svg class="w-4 h-4 theme-icon-sun hidden text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <svg class="w-4 h-4 theme-icon-moon hidden text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>
            </div>
        </header>

        <!-- Main Content View -->
        <main class="flex-1 w-full p-4 sm:p-5 md:p-6 overflow-y-auto">
            <!-- Flash Alerts -->
            @if(session('success'))
                <div class="uk-alert uk-alert-primary mb-4 p-3 rounded-lg text-xs flex items-center justify-between" uk-alert>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <a href class="uk-alert-close" uk-close></a>
                </div>
            @endif

            @if(session('error'))
                <div class="uk-alert uk-alert-danger mb-4 p-3 rounded-lg text-xs flex items-center justify-between" uk-alert>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <a href class="uk-alert-close" uk-close></a>
                </div>
            @endif

            @if(session('info'))
                <div class="uk-alert uk-alert-primary mb-4 p-3 rounded-lg text-xs flex items-center justify-between" uk-alert>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ session('info') }}</span>
                    </div>
                    <a href class="uk-alert-close" uk-close></a>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="border-t border-border bg-card/40 py-3 px-4 sm:px-6 text-[11px] text-muted-foreground flex flex-col sm:flex-row items-center justify-between gap-2 mt-auto">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Andro Meda Group &copy; {{ date('Y') }} — Solusi Kargo &amp; Piutang Terintegrasi</span>
            </div>
            <div class="flex items-center gap-4">
                <span>Role: {{ auth()->check() ? ucfirst(auth()->user()->role) : 'Guest' }}</span>
            </div>
        </footer>
    </div>

    <!-- Theme Switcher & Sidebar Helper Script -->
    <script>
        // Mobile Sidebar Drawer Toggle
        function toggleMobileSidebar(open) {
            const sidebar = document.getElementById('app-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (!sidebar || !backdrop) return;
            if (open) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                backdrop.classList.remove('hidden');
                document.body.classList.add('overflow-hidden', 'md:overflow-auto');
            } else {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden', 'md:overflow-auto');
            }
        }
        window.toggleMobileSidebar = toggleMobileSidebar;

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('mobile-menu-btn')?.addEventListener('click', function(e) {
                e.preventDefault();
                toggleMobileSidebar(true);
            });
            document.getElementById('mobile-close-btn')?.addEventListener('click', function(e) {
                e.preventDefault();
                toggleMobileSidebar(false);
            });
        });

        // Universal Theme Switcher Script
        (function () {
            const toggleBtns = document.querySelectorAll('.theme-toggle-btn');
            const sunIcons = document.querySelectorAll('.theme-icon-sun');
            const moonIcons = document.querySelectorAll('.theme-icon-moon');
            const themeTexts = document.querySelectorAll('.theme-text-label');

            function applyTheme(isDark) {
                const root = document.documentElement;
                if (isDark) {
                    root.classList.add('dark');
                    root.classList.remove('light');
                    root.style.colorScheme = 'dark';
                    sunIcons.forEach(el => el.classList.remove('hidden'));
                    moonIcons.forEach(el => el.classList.add('hidden'));
                    themeTexts.forEach(el => el.textContent = 'Terang');
                    toggleBtns.forEach(btn => btn.setAttribute('title', 'Beralih ke Mode Terang (Light)'));
                    document.querySelectorAll('.app-logo-transparent').forEach(el => el.style.setProperty('display', 'none', 'important'));
                    document.querySelectorAll('.app-logo-white').forEach(el => el.style.setProperty('display', 'inline-block', 'important'));
                } else {
                    root.classList.remove('dark');
                    root.classList.add('light');
                    root.style.colorScheme = 'light';
                    sunIcons.forEach(el => el.classList.add('hidden'));
                    moonIcons.forEach(el => el.classList.remove('hidden'));
                    themeTexts.forEach(el => el.textContent = 'Gelap');
                    toggleBtns.forEach(btn => btn.setAttribute('title', 'Beralih ke Mode Gelap (Dark)'));
                    document.querySelectorAll('.app-logo-transparent').forEach(el => el.style.setProperty('display', 'inline-block', 'important'));
                    document.querySelectorAll('.app-logo-white').forEach(el => el.style.setProperty('display', 'none', 'important'));
                }
            }

            // Sync on initial load
            const currentIsDark = document.documentElement.classList.contains('dark');
            applyTheme(currentIsDark);

            toggleBtns.forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const willBeDark = !document.documentElement.classList.contains('dark');
                    localStorage.setItem('theme', willBeDark ? 'dark' : 'light');
                    applyTheme(willBeDark);
                });
            });
        })();

        // Universal Select2 Initializer
        function initAppSelect2(context) {
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                const $target = context ? $(context).find('.select2') : $('.select2');
                $target.each(function() {
                    const $el = $(this);
                    if (!$el.hasClass('select2-hidden-accessible')) {
                        $el.select2({
                            width: '100%',
                            placeholder: $el.data('placeholder') || $el.find('option[value=""]').text() || '-- Pilih --',
                            allowClear: Boolean($el.data('allow-clear'))
                        });
                    }
                });
            }
        }

        $(document).ready(function() {
            initAppSelect2();
        });
    </script>

    @stack('scripts')
</body>
</html>
