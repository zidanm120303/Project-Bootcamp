<x-guest-layout>
    <div><p class="section-kicker">Selamat datang kembali</p><h1 class="mt-2 text-3xl font-black tracking-tight text-ink">Masuk ke akun Anda</h1><p class="mt-2 text-sm text-slate-500">Kelola booking, produk, dan transaksi dari satu tempat.</p></div>
    <x-auth-session-status class="mt-5" :status="session('status')" />
    @if($errors->any())<div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">@csrf
        <div><label class="label">Email</label><input name="email" type="email" value="{{ old('email') }}" class="input" placeholder="nama@email.com" required autofocus autocomplete="username"></div>
        <div><div class="flex items-center justify-between"><label class="label">Password</label>@if(Route::has('password.request'))@endif</div><input name="password" type="password" class="input" placeholder="Masukkan password" required autocomplete="current-password"></div>
        <!-- <label class="flex items-center gap-2 text-sm text-slate-500"><input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">Ingat saya</label> -->
        <button class="btn-primary w-full py-3.5">Masuk →</button>
    </form>
    <p class="mt-7 text-center text-sm text-slate-500">Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-indigo-600">Daftar gratis</a></p>
    <div class="mt-6 rounded-xl bg-slate-50 p-4 text-xs leading-6 text-slate-500"><p class="font-bold text-slate-700">Akun demo</p><p>Admin: admin@rentra.test • Mitra: mitra@rentra.test</p><p>Customer: customer@rentra.test • Password: password</p></div>
</x-guest-layout>
