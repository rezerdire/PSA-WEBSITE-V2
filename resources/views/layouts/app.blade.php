<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        {{-- icons --}}
        <script src="https://unpkg.com/lucide@latest"></script> 
        <title>@yield('title')</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class = "bg-white text-slate-900 antialiased">
        @livewire('partials.navbar')
      <main class="min-h-screen">
        @yield('content')
    </main>
        @livewireScripts
        @livewire('partials.footer')
    </body>
</html>


{{-- MASTER FILE --}}