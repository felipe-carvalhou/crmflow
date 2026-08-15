<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-canvas px-4 pt-6 sm:pt-0">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-600 via-fuchsia-500 to-orange-400 shadow-soft">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                </div>
                <p class="text-base font-bold text-slate-900">AutoFlow <span class="font-medium text-violet-500">CRM</span></p>
            </div>

            <div class="mt-6 w-full overflow-hidden rounded-2xl border border-slate-200/70 bg-white px-6 py-6 shadow-card sm:max-w-md">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
