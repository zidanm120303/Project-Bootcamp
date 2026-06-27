@php
$role = auth()->user()->role;
$menus = [
    'customer' => [
        ['customer.dashboard','home','Ringkasan'], ['customer.bookings.index','calendar','Pesanan Saya'],
        ['catalog','search','Cari Produk'], ['profile.edit','settings','Profil'],
    ],
    'mitra' => [
        ['mitra.dashboard','home','Dashboard'], ['mitra.products.index','box','Produk & Jasa'],
        ['mitra.bookings.index','calendar','Booking Masuk'], ['mitra.profile.edit','store','Profil & Dokumen'],
    ],
    'admin' => [
        ['admin.dashboard','home','Dashboard'], ['admin.partners.index','shield','Verifikasi Mitra'],
        ['admin.products.index','box','Produk & Katalog'], ['admin.bookings.index','calendar','Booking'],
        ['admin.payments.index','card','Pembayaran'], ['admin.disputes.index','alert','Komplain'],
        ['admin.categories.index','grid','Kategori'], ['admin.users.index','users','Pengguna'],
        ['admin.settings.index','settings','Pengaturan'],
    ],
];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Rentalpro</title>
    <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebar: false }">
    <div class="min-h-screen">
        <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex w-[270px] flex-col border-r border-slate-200 bg-white px-5 py-6 transition lg:translate-x-0">
            <div class="flex items-center justify-between"><a href="{{ route('home') }}"><x-application-logo /></a><button @click="sidebar=false" class="lg:hidden"><x-icon name="x" /></button></div>
            @if($role === 'mitra' && auth()->user()->partnerProfile)
                <div class="mt-7 rounded-2xl border border-slate-200 bg-slate-50 p-3"><p class="text-xs font-medium text-slate-500">Panel Mitra</p><p class="mt-1 truncate text-sm font-extrabold text-ink">{{ auth()->user()->partnerProfile->business_name }}</p></div>
            @endif
            <nav class="mt-7 min-h-0 flex-1 space-y-1.5 overflow-y-auto pr-1">
                @foreach($menus[$role] as [$route,$icon,$label])
                    @if($role === 'admin' && $route === 'admin.users.index')
                        @php $usersMenuActive = request()->routeIs('admin.users.*'); @endphp
                        <div x-data="{ open: {{ $usersMenuActive ? 'true' : 'false' }} }">
                            <button type="button" @click="open = !open" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-semibold transition {{ $usersMenuActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                                <x-icon :name="$icon" class="h-5 w-5" />
                                <span class="flex-1">{{ $label }}</span>
                                <svg class="h-4 w-4 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak class="ml-6 mt-1.5 space-y-1 border-l border-indigo-100 pl-3">
                                @foreach([
                                    ['admin.users.admin', 'Admin'],
                                    ['admin.users.mitra', 'Mitra'],
                                    ['admin.users.customer', 'Customer'],
                                ] as [$childRoute, $childLabel])
                                    <a href="{{ route($childRoute) }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs($childRoute) ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-md shadow-indigo-100' : 'text-slate-500 hover:bg-indigo-50 hover:text-indigo-700' }}">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>{{ $childLabel }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        @php
                            $menuActive = str_ends_with($route, '.dashboard')
                                ? request()->routeIs($route)
                                : request()->routeIs($route) || request()->routeIs(str($route)->beforeLast('.').'.*');
                        @endphp
                        <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition {{ $menuActive ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-200' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                            <x-icon :name="$icon" class="h-5 w-5" />{{ $label }}
                        </a>
                    @endif
                @endforeach
            </nav>
            <div class="border-t border-slate-200 pt-4">
                <a href="{{ route('catalog') }}" class="flex items-center gap-3 rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50"><x-icon name="store" />Lihat Marketplace</a>
                <form action="{{ route('logout') }}" method="POST">@csrf<button class="mt-1 flex w-full items-center gap-3 rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-rose-50 hover:text-rose-600"><x-icon name="logout" />Keluar</button></form>
            </div>
        </aside>
        <div x-show="sidebar" x-cloak @click="sidebar=false" class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden"></div>
        <div class="min-w-0 lg:ml-[270px]">
            <header class="sticky top-0 z-30 flex h-[76px] items-center justify-between border-b border-slate-200 bg-white/90 px-4 backdrop-blur-xl sm:px-7">
                <div class="flex items-center gap-3"><button @click="sidebar=true" class="rounded-xl border border-slate-200 p-2 lg:hidden"><x-icon name="menu" /></button><div><h1 class="text-lg font-extrabold text-ink">@yield('page-title','Dashboard')</h1><p class="hidden text-xs text-slate-500 sm:block">@yield('page-subtitle','Kelola aktivitas Anda dalam satu tempat.')</p></div></div>
                <div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-full bg-indigo-100 font-extrabold text-indigo-700">{{ str(auth()->user()->name)->substr(0,1) }}</span><div class="hidden sm:block"><p class="text-sm font-bold text-ink">{{ auth()->user()->name }}</p><p class="text-xs capitalize text-slate-500">{{ $role }}</p></div></div>
            </header>
            <main class="p-4 sm:p-7"><x-flash />@yield('content')</main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
