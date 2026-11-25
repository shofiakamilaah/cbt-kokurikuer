<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Kokurikuler')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/kokurikuler.css') }}">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#35858E',
                        forest: '#174E47',
                        sage: '#7DA78C',
                        olive: '#C2D099',
                        cream: '#E6EEC9',
                    },
                },
            },
        };
    </script>
</head>
<body class="panel-body min-h-screen text-slate-800">
    <div class="panel-shell">
        <x-app-navbar />
        <main class="panel-content">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</body>
</html>
