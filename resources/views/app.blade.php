<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'SINDORA') }}</title>

        <!-- Primary Meta Tags -->
        <meta name="title" content="SINDORA — Database Olahraga & Prestasi Kota Tanjungpinang">
        <meta name="description" content="Portal resmi data atlet binaan, pelatih bersertifikasi, dan statistik perolehan medali kejuaraan Dinas Pemuda dan Olahraga Pemerintah Kota Tanjungpinang.">
        <meta name="keywords" content="sindora, dispora tanjungpinang, olahraga tanjungpinang, atlet tanjungpinang, prestasi olahraga tanjungpinang, data atlet">
        <meta name="author" content="Dinas Pemuda dan Olahraga Pemerintah Kota Tanjungpinang">
        <meta name="theme-color" content="#1e40af">

        <!-- Open Graph / Facebook / WhatsApp / Telegram -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="SINDORA Kota Tanjungpinang">
        <meta property="og:title" content="SINDORA — Database Olahraga & Prestasi Kota Tanjungpinang">
        <meta property="og:description" content="Portal resmi data atlet binaan, pelatih bersertifikasi, dan statistik perolehan medali kejuaraan Dinas Pemuda dan Olahraga Pemerintah Kota Tanjungpinang.">
        <meta property="og:image" content="{{ asset('logo-tanjungpinang.png') }}">
        <meta property="og:locale" content="id_ID">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url()->current() }}">
        <meta name="twitter:title" content="SINDORA — Database Olahraga & Prestasi Kota Tanjungpinang">
        <meta name="twitter:description" content="Portal resmi data atlet binaan, pelatih bersertifikasi, dan statistik perolehan medali kejuaraan Dinas Pemuda dan Olahraga Pemerintah Kota Tanjungpinang.">
        <meta name="twitter:image" content="{{ asset('logo-tanjungpinang.png') }}">

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('logo-tanjungpinang.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('logo-tanjungpinang.png') }}">
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('logo-tanjungpinang.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <script>
            (function () {
                try {
                    const stored = localStorage.getItem('sindora-theme');
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    const theme = stored || (prefersDark ? 'dark' : 'light');
                    if (theme === 'dark') document.documentElement.classList.add('dark');
                    else document.documentElement.classList.remove('dark');
                } catch (e) {}
            })();
        </script>
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
        @inertia
    </body>
</html>
