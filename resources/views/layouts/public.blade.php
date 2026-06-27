<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Rentalpro — Sewa Mudah, Jadwal Aman')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body x-data="{ mobile: false }">
    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">
        <div class="container-app flex h-[72px] items-center justify-between">
            <a href="{{ route('home') }}"><x-application-logo /></a>
            <nav class="hidden items-center gap-8 lg:flex">
                <a href="{{ route('home') }}"
                    class="text-sm font-bold {{ request()->routeIs('home') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600' }}">Beranda</a>
                <a href="{{ route('catalog') }}"
                    class="text-sm font-bold {{ request()->routeIs('catalog', 'products.*') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600' }}">Katalog</a>
                <a href="{{ route('home') }}#cara-kerja"
                    class="text-sm font-bold text-slate-600 hover:text-indigo-600">Cara Kerja</a>
                <a href="{{ route('home') }}#mitra"
                    class="text-sm font-bold text-slate-600 hover:text-indigo-600">Mitra</a>
                <a href="{{ route('home') }}#bantuan"
                    class="text-sm font-bold text-slate-600 hover:text-indigo-600">Bantuan</a>
            </nav>
            <div class="hidden items-center gap-3 lg:flex">
                @auth
                    <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="btn-secondary py-2.5">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary py-2.5">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary py-2.5">Daftar</a>
                @endauth
            </div>
            <button @click="mobile = !mobile" class="rounded-xl p-2 text-slate-700 lg:hidden"><x-icon
                    name="menu" /></button>
        </div>
        <div x-show="mobile" x-cloak x-transition class="border-t border-slate-100 bg-white p-4 lg:hidden">
            <div class="flex flex-col gap-2">
                <a class="rounded-xl px-4 py-3 font-bold text-slate-700 hover:bg-indigo-50"
                    href="{{ route('home') }}">Beranda</a>
                <a class="rounded-xl px-4 py-3 font-bold text-slate-700 hover:bg-indigo-50"
                    href="{{ route('catalog') }}">Katalog</a>
                @auth<a class="btn-primary mt-2"
                    href="{{ route(auth()->user()->role . '.dashboard') }}">Dashboard</a>@else<a class="btn-primary mt-2"
                    href="{{ route('login') }}">Masuk</a>@endauth
            </div>
        </div>
    </header>
    <main>@yield('content')</main>
    <footer id="bantuan" class="border-t border-slate-200 bg-white py-12">
        <div class="container-app grid gap-8 md:grid-cols-4">
            <div class="md:col-span-2"><x-application-logo />
                <p class="mt-4 max-w-md text-sm leading-6 text-slate-500">Marketplace rental barang dan jasa UMKM dengan
                    jadwal aman, stok terjaga, dan mitra terverifikasi.</p>
            </div>
            <div>
                <h3 class="font-extrabold text-ink">Jelajahi</h3>
                <div class="mt-4 space-y-3 text-sm text-slate-500"><a class="block hover:text-indigo-600"
                        href="{{ route('catalog') }}">Katalog</a><a class="block hover:text-indigo-600"
                        href="#cara-kerja">Cara kerja</a><a class="block hover:text-indigo-600"
                        href="{{ route('register') }}">Daftar mitra</a></div>
            </div>
            <div>
                <h3 class="font-extrabold text-ink">Bantuan</h3>
                <p class="mt-4 text-sm text-slate-500">support@rentra.test<br>Senin–Sabtu, 08.00–20.00</p>
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>

</html>
