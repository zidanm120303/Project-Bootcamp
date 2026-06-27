<x-guest-layout>
    <div x-data="{role:'{{ old('role',request('role','customer')) }}'}"><p class="section-kicker">Mulai bersama Rentra</p><h1 class="mt-2 text-3xl font-black tracking-tight text-ink">Buat akun baru</h1><p class="mt-2 text-sm text-slate-500">Pilih peran sesuai kebutuhan Anda.</p>
    @if($errors->any())<div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">@csrf
        <div class="grid grid-cols-2 gap-3"><label class="cursor-pointer"><input x-model="role" class="peer sr-only" type="radio" name="role" value="customer"><span class="block rounded-xl border border-slate-200 p-3 text-center text-sm font-bold peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700">Saya Customer</span></label><label class="cursor-pointer"><input x-model="role" class="peer sr-only" type="radio" name="role" value="mitra"><span class="block rounded-xl border border-slate-200 p-3 text-center text-sm font-bold peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700">Saya Mitra</span></label></div>
        <div><label class="label">Nama lengkap</label><input name="name" value="{{ old('name') }}" class="input" required autofocus></div>
        <div x-show="role==='mitra'" x-cloak><label class="label">Nama usaha</label><input name="business_name" value="{{ old('business_name') }}" class="input" :required="role==='mitra'" placeholder="Contoh: Kita Event Solution"></div>
        <div><label class="label">Nomor telepon</label><input name="phone" value="{{ old('phone') }}" class="input" required></div>
        <div><label class="label">Email</label><input name="email" type="email" value="{{ old('email') }}" class="input" required></div>
        <div class="grid grid-cols-2 gap-3"><div><label class="label">Password</label><input name="password" type="password" class="input" required></div><div><label class="label">Konfirmasi</label><input name="password_confirmation" type="password" class="input" required></div></div>
        <button class="btn-primary w-full py-3.5">Daftar sekarang →</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-indigo-600">Masuk</a></p></div>
</x-guest-layout>
