@props(['title' => 'Belum ada data', 'description' => 'Data akan muncul di sini setelah tersedia.'])
<div class="card grid min-h-64 place-items-center p-8 text-center">
    <div>
        <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-indigo-50 text-indigo-600"><x-icon name="box" class="h-8 w-8" /></span>
        <h3 class="mt-4 text-lg font-extrabold text-ink">{{ $title }}</h3>
        <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">{{ $description }}</p>
        @if(isset($action))<div class="mt-5">{{ $action }}</div>@endif
    </div>
</div>
