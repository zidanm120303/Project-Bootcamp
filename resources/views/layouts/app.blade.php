<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="border-b border-slate-200 bg-white"><div class="container-app flex h-[72px] items-center justify-between"><a href="{{ route('home') }}"><x-application-logo /></a><div class="flex items-center gap-3"><a href="{{ route((auth()->user()->role ?: 'customer').'.dashboard') }}" class="btn-secondary py-2">Kembali ke dashboard</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-xl p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600"><x-icon name="logout" /></button></form></div></div></header>
    @if(isset($header))<div class="border-b border-slate-200 bg-white"><div class="container-app py-6">{{ $header }}</div></div>@endif
    <main>{{ $slot }}</main>
</body>
</html>
