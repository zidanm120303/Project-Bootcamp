@php
    $statusMessages = [
        'profile-updated' => 'Profil akun berhasil diperbarui.',
        'password-updated' => 'Password berhasil diperbarui.',
        'verification-link-sent' => 'Tautan verifikasi baru berhasil dikirim.',
    ];
    $serverErrors = collect($errors->getBags())
        ->reduce(fn (array $messages, $bag) => array_merge($messages, $bag->messages()), []);
    $firstError = collect($serverErrors)->flatten()->first();
    $initialType = $firstError ? 'error' : (session('success') || session('status') ? 'success' : null);
    $initialMessage = $firstError
        ? $firstError
        : (session('success') ?? ($statusMessages[session('status')] ?? session('status')));
@endphp

<div
    x-data="{
        visible: @js((bool) $initialMessage),
        type: @js($initialType ?? 'success'),
        message: @js($initialMessage ?? ''),
        timer: null,
        show(detail) {
            clearTimeout(this.timer);
            this.type = detail.type || 'success';
            this.message = detail.message;
            this.visible = true;
            this.timer = setTimeout(() => this.visible = false, detail.duration || 5000);
        }
    }"
    x-init="if (visible) timer = setTimeout(() => visible = false, 6000)"
    @app-notify.window="show($event.detail)"
    class="pointer-events-none fixed right-4 top-4 z-[100] w-[calc(100%-2rem)] max-w-sm sm:right-6 sm:top-6"
    aria-live="polite"
>
    <div
        x-show="visible"
        x-cloak
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="-translate-y-2 opacity-0"
        class="pointer-events-auto flex items-start gap-3 rounded-2xl border bg-white p-4 shadow-2xl"
        :class="type === 'error' ? 'border-rose-200 shadow-rose-100' : type === 'warning' ? 'border-amber-200 shadow-amber-100' : 'border-emerald-200 shadow-emerald-100'"
        role="alert"
    >
        <span
            class="grid h-9 w-9 shrink-0 place-items-center rounded-xl"
            :class="type === 'error' ? 'bg-rose-50 text-rose-600' : type === 'warning' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600'"
        >
            <x-icon name="alert" class="h-5 w-5" />
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-extrabold text-ink" x-text="type === 'error' ? 'Periksa kembali data' : type === 'warning' ? 'Perhatian' : 'Berhasil'"></p>
            <p class="mt-1 break-words text-xs leading-5 text-slate-500" x-text="message"></p>
        </div>
        <button type="button" @click="visible = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-50 hover:text-slate-700" aria-label="Tutup notifikasi">
            <x-icon name="x" class="h-4 w-4" />
        </button>
    </div>
</div>

<script>
    window.serverValidationErrors = @json($serverErrors);
</script>
