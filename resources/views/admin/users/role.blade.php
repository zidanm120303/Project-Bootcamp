@extends('layouts.dashboard')
@section('title', 'Pengguna '.$roleLabel)
@section('page-title', 'Pengguna '.$roleLabel)
@section('page-subtitle', $roleDescription)
@section('content')
@php
    $roleRoutes = [
        'admin' => 'admin.users.admin',
        'mitra' => 'admin.users.mitra',
        'customer' => 'admin.users.customer',
    ];
@endphp

<div class="mb-6 flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 lg:hidden">
    @foreach($roleRoutes as $role => $route)
        <a href="{{ route($route) }}" class="flex-1 whitespace-nowrap rounded-xl px-4 py-2.5 text-center text-sm font-bold transition {{ $managedRole === $role ? 'bg-indigo-600 text-white' : 'text-slate-500 hover:bg-indigo-50 hover:text-indigo-700' }}">
            {{ ucfirst($role) }}
        </a>
    @endforeach
</div>

<div class="grid gap-4 sm:grid-cols-3">
    <x-stat-card label="Total {{ $roleLabel }}" :value="$stats['total']" icon="users" />
    <x-stat-card label="Akun aktif" :value="$stats['active']" icon="shield" tone="emerald" />
    <x-stat-card label="Ditangguhkan" :value="$stats['suspended']" icon="alert" tone="amber" />
</div>

<form class="mt-6 grid gap-3 sm:grid-cols-[1fr_220px_auto]">
    <div class="relative">
        <x-icon name="search" class="pointer-events-none absolute left-4 top-3.5 h-5 w-5 text-slate-400" />
        <input name="q" value="{{ request('q') }}" class="input pl-12" placeholder="Cari nama, email, atau telepon...">
    </div>
    <select name="status" class="input">
        <option value="">Semua status</option>
        @foreach(['active' => 'Aktif', 'inactive' => 'Nonaktif', 'suspended' => 'Ditangguhkan'] as $status => $label)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="btn-primary">Filter</button>
</form>

@if($users->count())
    <div class="table-shell mt-6">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ $managedRole === 'mitra' ? 'Mitra & Pemilik' : 'Pengguna' }}</th>
                    <th>Kontak</th>
                    @if($managedRole === 'mitra')
                        <th>Verifikasi</th>
                        <th>Aktivitas</th>
                    @elseif($managedRole === 'customer')
                        <th>Booking</th>
                        <th>Nilai Transaksi</th>
                    @else
                        <th>Role</th>
                    @endif
                    <th>Status</th>
                    <th>Terdaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-indigo-100 font-extrabold text-indigo-700">
                                    {{ str($user->name)->substr(0, 1) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-ink">{{ $user->name }}</p>
                                    @if($managedRole === 'mitra')
                                        <p class="mt-1 max-w-[220px] truncate text-xs text-slate-400">{{ $user->partnerProfile?->business_name ?? 'Profil usaha belum lengkap' }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <p>{{ $user->email }}</p>
                            <p class="text-xs text-slate-400">{{ $user->phone ?: 'Nomor belum tersedia' }}</p>
                        </td>
                        @if($managedRole === 'mitra')
                            <td>
                                @if($user->partnerProfile)
                                    <x-status-badge :status="$user->partnerProfile->verification_status" />
                                @else
                                    <span class="text-xs text-slate-400">Belum ada profil</span>
                                @endif
                            </td>
                            <td>
                                <p class="font-bold text-ink">{{ $user->partnerProfile?->products_count ?? 0 }} produk</p>
                                <p class="text-xs text-slate-400">{{ $user->partnerProfile?->bookings_count ?? 0 }} booking</p>
                            </td>
                        @elseif($managedRole === 'customer')
                            <td>
                                <p class="font-bold text-ink">{{ $user->bookings_count }}</p>
                                <p class="text-xs text-slate-400">Total booking</p>
                            </td>
                            <td class="font-bold text-ink">Rp{{ number_format($user->transaction_total ?? 0, 0, ',', '.') }}</td>
                        @else
                            <td><span class="rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700">Administrator</span></td>
                        @endif
                        <td><x-status-badge :status="$user->status" /></td>
                        <td class="whitespace-nowrap">{{ $user->created_at->translatedFormat('d M Y') }}</td>
                        <td>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex min-w-[210px] gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="rounded-lg border-slate-200 py-2 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="active" @selected($user->status === 'active')>Aktifkan</option>
                                        <option value="inactive" @selected($user->status === 'inactive')>Nonaktifkan</option>
                                        <option value="suspended" @selected($user->status === 'suspended')>Tangguhkan</option>
                                    </select>
                                    <button class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-indigo-700">Simpan</button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400">Akun Anda</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $users->links() }}</div>
@else
    <div class="mt-6">
        <x-empty-state title="Pengguna {{ $roleLabel }} tidak ditemukan" description="Coba ubah kata kunci atau filter status yang digunakan." />
    </div>
@endif
@endsection
