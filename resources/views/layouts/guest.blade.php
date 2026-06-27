<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<main class="grid min-h-screen lg:grid-cols-[.9fr_1.1fr]">
    <section class="flex items-center justify-center bg-white px-5 py-10 sm:px-10">
        <div class="w-full max-w-md"><a href="{{ route('home') }}"><x-application-logo /></a><div class="mt-9">{{ $slot }}</div></div>
    </section>
    <section class="relative hidden overflow-hidden bg-ink lg:block">
        <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1400&q=90" class="absolute inset-0 h-full w-full object-cover opacity-55" alt="Event UMKM">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-950/85 via-ink/70 to-indigo-600/50"></div>
        <div class="relative flex h-full flex-col justify-between p-12 text-white">
            <a href="{{ route('home') }}" class="self-end rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-bold backdrop-blur">← Kembali ke beranda</a>
            <div class="max-w-xl"><span class="grid h-14 w-14 place-items-center rounded-2xl bg-white/15 backdrop-blur"><x-icon name="shield" class="h-7 w-7" /></span><h1 class="mt-6 text-4xl font-black leading-tight">Sewa mudah.<br>Jadwal tetap aman.</h1><p class="mt-4 max-w-md leading-7 text-indigo-100/80">Marketplace barang dan jasa UMKM dengan ketersediaan terverifikasi dan alur booking transparan.</p><div class="mt-8 flex gap-6 text-sm"><span>✓ Mitra terverifikasi</span><span>✓ Stok real-time</span></div></div>
        </div>
    </section>
</main>
</body>
</html>
