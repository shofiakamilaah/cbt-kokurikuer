<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kokurikuler Wikrama')</title>
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
<body class="app-body min-h-screen text-slate-800">
    <x-app-navbar />
    <main class="app-main mx-auto">
        @include('partials.flash')
        @yield('content')
    </main>
</body>
</html>
