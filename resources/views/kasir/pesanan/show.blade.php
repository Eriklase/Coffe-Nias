@extends('kasir.layout')

@section('title', 'Detail Transaksi Kasir')
@section('page-title', 'Transaksi #' . $order->id)

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('kasir.pesanan.index') }}" class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Kembali ke pesanan</a>
        <a href="{{ route('kasir.pesanan.cetak', $order->id) }}" target="_blank" rel="noopener" class="rounded-full bg-amber-500 px-4 py-2 text-sm font-semibold text-black hover:bg-amber-400">Cetak struk</a>
    </div>

    <section class="grid gap-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:grid-cols-2 lg:grid-cols-3">
        <div><p class="text-sm text-slate-500">Pelanggan</p><p class="mt-2 font-semibold">{{ $order->nama ?? '-' }}</p></div>
        <div><p class="text-sm text-slate-500">Nomor HP</p><p class="mt-2 font-semibold">{{ $order->nomor_hp ?? '-' }}</p></div>
        <div><p class="text-sm text-slate-500">Metode Pembayaran</p><p class="mt-2 font-semibold">{{ ($order->metode_pembayaran ?? 'cash') === 'qris' ? 'QRIS' : 'Tunai' }}</p></div>
        <div><p class="text-sm text-slate-500">Jenis Pesanan</p><p class="mt-2 font-semibold">{{ ($order->tipe_pesanan ?? 'delivery') === 'takein' ? 'Take In / Beli di Tempat' : 'Delivery' }}</p></div>
        <div><p class="text-sm text-slate-500">Alamat Pengantaran</p><p class="mt-2 font-semibold">{{ $order->alamat ?: 'Pesanan diambil di kedai.' }}</p></div>
        <div>
            <p class="text-sm text-slate-500">Status</p>
            <form method="POST" action="{{ route('kasir.pesanan.status', $order->id) }}" class="mt-2 flex flex-wrap items-center gap-2">
                @csrf
                @method('PATCH')
                <select name="status" aria-label="Status transaksi" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                    <option value="pending" @selected(($order->status ?? 'pending') === 'pending')>Pending</option>
                    <option value="proses" @selected(($order->status ?? '') === 'proses')>Proses</option>
                    <option value="selesai" @selected(($order->status ?? '') === 'selesai')>Selesai</option>
                </select>
                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700">Simpan</button>
            </form>
        </div>
    </section>

    <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold">Rincian produk & keterangan</h2>
        <ul class="mt-4 divide-y divide-slate-100">
            @forelse($details as $detail)
                <li class="py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <span class="font-semibold">{{ $detail->nama_produk ?? 'Produk ID: '.$detail->produk_id }} <span class="font-normal text-slate-500">× {{ $detail->qty }}</span></span>
                        <span>Rp{{ number_format($detail->subtotal ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @if(!empty($detail->catatan))
                        <p class="mt-2 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-950"><span class="font-semibold">Keterangan:</span> {{ $detail->catatan }}</p>
                    @endif
                </li>
            @empty
                <li class="py-4 text-slate-500">Tidak ada rincian produk.</li>
            @endforelse
        </ul>
        <div class="mt-4 border-t border-slate-200 pt-4 text-right">
            <p class="text-sm text-slate-500">Total transaksi</p>
            <p class="mt-1 text-2xl font-semibold">Rp{{ number_format($order->total ?? 0, 0, ',', '.') }}</p>
        </div>
    </section>

    @if(($order->metode_pembayaran ?? 'cash') === 'qris')
        @php
            $qrisUrl = config('payment.qris_image_url');
            $qrisPath = config('payment.qris_image_path');
            $qrisAvailable = false;

            if (!empty($qrisUrl)) {
                $qrisAvailable = true;
            } elseif (!empty($qrisPath)) {
                $qrisPathUrl = str_starts_with($qrisPath, '/') ? ltrim($qrisPath, '/') : $qrisPath;
                $qrisAvailable = file_exists(public_path($qrisPathUrl));
                $qrisUrl = asset($qrisPathUrl);
            }
        @endphp

        <section id="qris-pembayaran" class="mt-6 grid items-center gap-6 rounded-3xl border border-amber-200 bg-amber-50 p-6 shadow-sm sm:grid-cols-[240px_1fr]">
            <div class="rounded-2xl bg-white p-3 text-center">
                @if($qrisAvailable)
                    <img src="{{ $qrisUrl }}" alt="Kode QRIS untuk pembayaran transaksi #{{ $order->id }}" class="mx-auto aspect-square w-full max-w-52 object-contain">
                @else
                    <div class="flex aspect-square w-full items-center justify-center rounded-xl border-2 border-dashed border-amber-300 px-4 text-sm text-amber-900">
                        QRIS belum dikonfigurasi
                    </div>
                @endif
            </div>
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700">Pembayaran QRIS</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Tunjukkan QR ini kepada pelanggan</h2>
                <p class="mt-3 text-slate-600">Pastikan pembayaran berhasil dan nominal yang diterima sesuai sebelum mengubah status pesanan.</p>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-amber-200 bg-white p-4">
                        <dt class="text-sm text-slate-500">Nomor transaksi</dt>
                        <dd class="mt-1 font-semibold">#{{ $order->id }}</dd>
                    </div>
                    <div class="rounded-2xl border border-amber-200 bg-white p-4">
                        <dt class="text-sm text-slate-500">Nominal</dt>
                        <dd class="mt-1 font-semibold">Rp{{ number_format($order->total ?? 0, 0, ',', '.') }}</dd>
                    </div>
                </dl>
                @unless($qrisAvailable)
                    <p class="mt-4 rounded-xl bg-white px-4 py-3 text-sm text-slate-700">Atur <code class="font-semibold">QRIS_IMAGE_PATH</code> atau <code class="font-semibold">QRIS_IMAGE_URL</code> pada file <code>.env</code>.</p>
                @endunless
            </div>
        </section>
    @endif
@endsection
