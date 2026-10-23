<!DOCTYPE html>
<html lang="id" class="dark uk-theme-zinc">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Andromeda Cargo &amp; Billing</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800&family=Geist+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Franken UI Stylesheet -->
    <link rel="stylesheet" href="{{ asset('vendor/franken-ui/css/core.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/franken-ui/css/utilities.min.css') }}">

    <!-- Franken UI Scripts -->
    <script src="{{ asset('vendor/franken-ui/js/core.iife.js') }}" type="module"></script>
    <script src="{{ asset('vendor/franken-ui/js/icon.iife.js') }}" type="module"></script>

    <style>
        body {
            font-family: 'Geist', ui-sans-serif, system-ui, -apple-system, sans-serif;
        }
        .font-mono {
            font-family: 'Geist Mono', ui-monospace, SFMono-Regular, monospace;
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
<body class="bg-background text-foreground antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <!-- Logo / Brand Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center mb-2">
                <img src="{{ asset('images/logo-transparent.png') }}" alt="Andro Meda Group" class="app-logo-transparent h-14 w-auto object-contain mx-auto">
                <img src="{{ asset('images/logo-white.png') }}" alt="Andro Meda Group" class="app-logo-white h-14 w-auto object-contain mx-auto">
            </div>
            <p class="text-xs text-muted-foreground mt-2 font-medium">
                Solusi Kargo &amp; Piutang Terintegrasi
            </p>
        </div>

        <!-- Login Card (Franken UI / Shadcn Style) -->
        <div class="uk-card uk-card-default shadow-sm border border-border">
            <div class="uk-card-header pb-2">
                <h2 class="uk-card-title text-lg font-semibold text-foreground">Masuk ke Akun</h2>
                <p class="text-xs text-muted-foreground mt-1">Masukkan email dan kata sandi Anda untuk melanjutkan.</p>
            </div>

            <div class="uk-card-body pt-3 space-y-4">
                @if(session('error'))
                    <div class="uk-alert uk-alert-danger text-xs p-3 rounded-lg" uk-alert>
                        <a href class="uk-alert-close" uk-close></a>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                @if(session('info'))
                    <div class="uk-alert uk-alert-primary text-xs p-3 rounded-lg" uk-alert>
                        <a href class="uk-alert-close" uk-close></a>
                        <p>{{ session('info') }}</p>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="uk-form-stacked space-y-4">
                    @csrf
                    <div>
                        <label class="uk-form-label text-xs font-medium text-foreground mb-1 block" for="email">Email</label>
                        <div class="uk-form-controls">
                            <input class="uk-input @error('email') border-destructive @enderror" 
                                   type="email" name="email" id="email" required 
                                   value="{{ old('email', 'admin@andromeda.com') }}" 
                                   placeholder="nama@andromeda.com">
                        </div>
                        @error('email')
                            <p class="text-[11px] text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="uk-form-label text-xs font-medium text-foreground" for="password">Kata Sandi</label>
                            <span class="text-[10px] text-muted-foreground font-mono">default: password</span>
                        </div>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="password" name="password" id="password" required value="password">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-muted-foreground">
                            <input type="checkbox" name="remember" class="uk-checkbox">
                            <span>Ingat saya</span>
                        </label>
                    </div>

                    <button type="submit" class="uk-btn uk-btn-primary uk-width-1-1 font-semibold text-xs py-2">
                        Masuk Sekarang
                    </button>
                </form>


            </div>
        </div>

        <div class="text-center text-xs text-muted-foreground mt-6 flex items-center justify-center gap-2">
            <span>Andromeda Cargo &copy; {{ date('Y') }}</span>
            <span>•</span>
            <button onclick="toggleTheme()" class="hover:text-foreground text-[11px] underline cursor-pointer">
                Ganti Tema
            </button>
        </div>
    </div>

    <script>

        function toggleTheme() {
            const html = document.documentElement;
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }

        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.remove('dark');
        }
    </script>
</body>
</html>
