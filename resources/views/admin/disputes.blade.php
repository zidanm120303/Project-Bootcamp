@extends('layouts.dashboard')
@section('title', 'Komplain')
@section('page-title', 'Komplain')
@section('page-subtitle', 'Tinjau masalah secara transparan untuk kedua pihak.')
@section('content')
    <form class="mb-5">
        <select name="status" onchange="this.form.submit()" class="input max-w-xs">
            <option value="">Semua status</option>
            @foreach (['open', 'reviewed', 'waiting_partner_response', 'waiting_customer_response', 'resolved', 'rejected'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>
                    {{ str($status)->replace('_', ' ')->title() }}</option>
            @endforeach
        </select>
    </form>
    @if ($disputes->count())
        <div class="space-y-4">
            @foreach ($disputes as $dispute)
                <article class="card p-5">
                    <div class="grid gap-5 xl:grid-cols-[1fr_340px]">
                        <div>
                            <div class="flex flex-wrap items-center gap-2"><span
                                    class="rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-600">{{ $dispute->issue_type }}</span><x-status-badge
                                    :status="$dispute->status" /></div>
                            <h2 class="mt-3 font-black text-ink">{{ $dispute->booking->booking_code }} •
                                {{ $dispute->booking->items->first()->product->name }}</h2>
                            <p class="mt-1 text-xs text-slate-400">Dilaporkan {{ $dispute->reporter->name }} •
                                {{ $dispute->created_at->diffForHumans() }}</p>
                            <p class="mt-4 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                                {{ $dispute->description }}</p>
                        </div>
                        <form action="{{ route('admin.disputes.update', $dispute) }}" method="POST"
                            class="rounded-xl border border-slate-200 p-4">@csrf @method('PATCH')<label
                                class="label">Perbarui status</label><select name="status" class="input">
                                <option value="reviewed">Sedang ditinjau</option>
                                <option value="waiting_partner_response">Menunggu mitra</option>
                                <option value="waiting_customer_response">Menunggu customer</option>
                                <option value="resolved">Selesaikan</option>
                                <option value="rejected">Tolak komplain</option>
                            </select><label class="label mt-3">Catatan admin</label>
                            <textarea name="admin_notes" rows="3" class="input">{{ $dispute->admin_notes }}</textarea><button class="btn-primary mt-3 w-full py-2">Simpan keputusan</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    <div class="mt-6">{{ $disputes->links() }}</div>@else<x-empty-state title="Tidak ada komplain aktif"
            description="Semua transaksi berjalan tanpa masalah yang dilaporkan." />
    @endif
@endsection
