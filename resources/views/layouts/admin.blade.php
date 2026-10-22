{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>
        window.authUserRole = "{{ auth()->user()->role ?? 'user' }}";
    </script>
    <title>@yield('title', 'Admin - Pojok Bapelit')</title>
    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('image/logo.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- WAJIB: Cegah FOUC dari Alpine.js (navbar mobile & dropdown notif) --}}
    <style>[x-cloak]{display:none !important;}</style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
        // Set cookie buat skip ngrok warning page
        document.cookie = "ngrok-skip-browser-warning=true; path=/; max-age=31536000; SameSite=Lax";
    </script>
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    {{-- Navbar --}}
    @include('navbar.index')

    {{-- Konten Utama --}}
    <main class="flex-1 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-tax-alert-banner />
            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <x-footer />

        <x-chatbot />

    <!-- SweetAlert Notifikasi -->
    @include('components.sweetalert')

    @stack('scripts')

    
</body>
</html>