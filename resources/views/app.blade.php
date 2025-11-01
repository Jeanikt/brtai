<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @routes

        @if(app()->isProduction())
            @php
                $manifestPath = public_path('build/manifest.json');
                if (file_exists($manifestPath)) {
                    $manifest = json_decode(file_get_contents($manifestPath), true);
                }
            @endphp

            @if(isset($manifest['resources/js/app.ts']))
                <script type="module" src="{{ asset('build/' . $manifest['resources/js/app.ts']['file']) }}"></script>
                <link rel="stylesheet" href="{{ asset('build/' . $manifest['resources/js/app.ts']['css'][0]) }}">
            @else
                @vite(['resources/css/app.css', 'resources/js/app.ts'])
            @endif
        @else
            @vite(['resources/css/app.css', 'resources/js/app.ts'])
        @endif

        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
