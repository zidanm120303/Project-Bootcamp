@if(session('success'))
    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
        <x-icon name="shield" class="mt-0.5 h-5 w-5 shrink-0" />{{ session('success') }}
    </div>
@endif
@if($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
        <div class="flex gap-3"><x-icon name="alert" class="h-5 w-5 shrink-0" /><div><p class="font-bold">Periksa kembali data Anda.</p><ul class="mt-1 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
    </div>
@endif
