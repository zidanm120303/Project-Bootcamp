@extends('layouts.dashboard')
@section('title', 'Profil Akun')
@section('page-title', 'Profil Akun')
@section('page-subtitle', 'Kelola identitas, alamat, keamanan, dan kontak darurat Anda.')
@section('content')
    @php
        $profileFields = [
            $user->name,
            $user->email,
            $user->phone,
            $user->date_of_birth,
            $user->address,
            $user->city,
            $user->province,
        ];
        if ($user->role === 'customer') {
            $profileFields = array_merge($profileFields, [
                $user->identity_type,
                $user->identity_number,
                $user->identity_file,
            ]);
        }
        $completion = (int) round((collect($profileFields)->filter()->count() / count($profileFields)) * 100);
    @endphp

    <div class="grid items-start gap-6 xl:grid-cols-[1fr_360px]">
        <div class="space-y-6">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf @method('PATCH')
                <section class="card p-6">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                        @if ($user->avatar_url)
                            <img src="{{ $user->avatar_url }}"
                                class="h-24 w-24 rounded-3xl object-cover ring-4 ring-indigo-50" alt="{{ $user->name }}">
                        @else
                            <span
                                class="grid h-24 w-24 shrink-0 place-items-center rounded-3xl bg-gradient-to-br from-indigo-100 to-blue-100 text-3xl font-black text-indigo-700">{{ str($user->name)->substr(0, 1) }}</span>
                        @endif
                        <div class="flex-1">
                            <h2 class="text-xl font-black text-ink">{{ $user->name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ ucfirst($user->role) }} · Bergabung
                                {{ $user->created_at->translatedFormat('F Y') }}</p><label
                                class="btn-secondary mt-4 inline-flex cursor-pointer py-2"><input type="file"
                                    name="avatar" class="sr-only" accept=".jpg,.jpeg,.png">Ganti foto profil</label>
                            <p class="mt-2 text-xs text-slate-400">JPG atau PNG, maksimal 2 MB.</p>
                        </div>
                    </div>
                </section>

                <section class="card p-6">
                    <div>
                        <h2 class="text-lg font-extrabold text-ink">Informasi pribadi</h2>
                        <p class="mt-1 text-sm text-slate-500">Gunakan data sesuai identitas resmi agar proses penyewaan
                            lebih cepat.</p>
                    </div>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div><label class="label">Nama lengkap</label><input name="name"
                                value="{{ old('name', $user->name) }}" class="input" required></div>
                        <div><label class="label">Email</label><input type="email" name="email"
                                value="{{ old('email', $user->email) }}" class="input" required></div>
                        <div>
                            <label class="label">Nomor HP / WhatsApp</label>
                            <input name="phone" value="{{ old('phone', $user->phone) }}" class="input" type="tel"
                                inputmode="numeric" pattern="[0-9]{10,12}" minlength="10" maxlength="12"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'')" placeholder="08xxxxxxxxxx" required>
                            <p class="mt-1 text-xs text-slate-400">Angka saja, 10–12 digit (contoh: 081234567890)</p>
                        </div>
                        <div><label class="label">Tanggal lahir</label><input type="date" name="date_of_birth"
                                value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}" class="input">
                        </div>
                        <div><label class="label">Jenis kelamin</label><select name="gender" class="input">
                                <option value="">Pilih</option>
                                <option value="male" @selected(old('gender', $user->gender) === 'male')>Laki-laki</option>
                                <option value="female" @selected(old('gender', $user->gender) === 'female')>Perempuan
                                </option>
                            </select></div>
                        <div><label class="label">Pekerjaan</label><input name="profession"
                                value="{{ old('profession', $user->profession) }}" class="input"
                                placeholder="Fotografer, mahasiswa, karyawan..."></div>
                    </div>
                </section>

                <section class="card p-6">
                    <h2 class="text-lg font-extrabold text-ink">Alamat domisili</h2>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2"><label class="label">Alamat lengkap</label>
                            <textarea name="address" rows="3" class="input" @required($user->role === 'customer')>{{ old('address', $user->address) }}</textarea>
                        </div>
                        <div><label class="label">Kota / Kabupaten</label><input name="city"
                                value="{{ old('city', $user->city) }}" class="input" @required($user->role === 'customer')>
                        </div>
                        <div><label class="label">Provinsi</label><input name="province"
                                value="{{ old('province', $user->province) }}" class="input" @required($user->role === 'customer')></div>
                        <div><label class="label">Kode pos</label><input name="postal_code"
                                value="{{ old('postal_code', $user->postal_code) }}" class="input"></div>
                    </div>
                </section>

                @if ($user->role === 'customer')
                    <section class="card p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-extrabold text-ink">Dokumen identitas</h2>
                                <p class="mt-1 text-sm text-slate-500">Identitas tersimpan privat dan dapat digunakan saat
                                    checkout.</p>
                            </div>
                            @if ($user->identity_file)
                                <span
                                    class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">Dokumen
                                    tersedia</span>
                            @endif
                        </div>
                        <div class="mt-6 grid gap-5 sm:grid-cols-2">
                            <div><label class="label">Jenis identitas</label><select name="identity_type" class="input"
                                    required>
                                    @foreach ([
            'ktp' => 'KTP',
            'sim' => 'SIM',
            'kartu_pelajar' => 'Kartu
                                Pelajar/Mahasiswa',
            'paspor' => 'Paspor',
        ] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('identity_type', $user->identity_type) === $value)>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div><label class="label">Nomor identitas</label><input name="identity_number"
                                    value="{{ old('identity_number', $user->identity_number) }}" class="input" required>
                            </div>
                            <div class="sm:col-span-2"><label
                                    class="label">{{ $user->identity_file ? 'Ganti dokumen identitas' : 'Upload dokumen identitas' }}</label><input
                                    type="file" name="identity_file" class="input" accept=".jpg,.jpeg,.png,.pdf"
                                    @required(!$user->identity_file)>
                                <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs"><span
                                        class="text-slate-400">JPG, PNG, atau PDF maksimal 2
                                        MB.</span>
                                    @if ($user->identity_file)
                                        <a href="{{ route('profile.identity', $user) }}"
                                            class="font-bold text-indigo-600">Buka dokumen saat ini →</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="card p-6">
                        <h2 class="text-lg font-extrabold text-ink">Kontak darurat</h2>
                        <p class="mt-1 text-sm text-slate-500">Dapat dihubungi jika terjadi kendala selama masa penyewaan.
                        </p>
                        <div class="mt-6 grid gap-5 sm:grid-cols-2">
                            <div><label class="label">Nama kontak</label><input name="emergency_contact_name"
                                    value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}"
                                    class="input">
                            </div>
                            <div><label class="label">Nomor HP kontak</label><input name="emergency_contact_phone"
                                    value="{{ old('emergency_contact_phone', $user->emergency_contact_phone) }}"
                                    class="input">
                            </div>
                        </div>
                    </section>
                @endif

                <div
                    class="sticky bottom-4 flex items-center justify-between rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-xl backdrop-blur">
                    <p class="text-sm text-slate-500">Pastikan data sudah sesuai dokumen resmi.</p><button
                        class="btn-primary">Simpan perubahan</button>
                </div>
            </form>
        </div>

        <aside class="space-y-6 xl:sticky xl:top-6">
            <section class="card p-5">
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Kelengkapan profil</p>
                        <p class="mt-1 text-3xl font-black text-ink">{{ $completion }}%</p>
                    </div><span class="grid h-12 w-12 place-items-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <x-icon name="users" />
                    </span>
                </div>
                <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-blue-500"
                        style="width: {{ $completion }}%"></div>
                </div>
                <p class="mt-3 text-xs leading-5 text-slate-500">Profil lengkap mempercepat checkout dan verifikasi saat
                    pengambilan kamera.</p>
            </section>
            <section class="card p-5">@include('profile.partials.update-password-form')</section>
            <section class="card border-rose-100 p-5">@include('profile.partials.delete-user-form')</section>
        </aside>
    </div>
@endsection
