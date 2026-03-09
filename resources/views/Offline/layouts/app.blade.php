<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Shoe ERP Offline System')</title>

    @vite(['resources/css/offline/app.css', 'resources/js/offline/app.js'])
    
    @stack('styles')
</head>
<body>

    @include('Offline.partials.sidebar')

    <main class="main-content">
        @include('Offline.partials.header')

        <div class="workspace">
            @if(session('success'))
                <div style="background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; display: flex; justify-content: space-between;">
                    {{ session('success') }}
                    <button class="close-alert" style="background:none; border:none; font-weight:bold; cursor:pointer;">×</button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>