@extends('kasir.layout')

@section('title', 'Dashboard Kasir')
@section('page-title', 'Dashboard Kasir')

@section('content')
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Pesanan Hari Ini</p>
            <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $todayOrderCount }}</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Menunggu Diproses</p>
            <p class="mt-3 text-3xl font-semibold text-amber-600">{{ $pendingOrderCount }}</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Pesanan Selesai</p>
            <p class="mt-3 text-3xl font-semibold text-emerald-600">{{ $completedOrderCount }}</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Nilai Pesanan Hari Ini</p>
            <p class="mt-3 text-3xl font-semibold text-slate-950">Rp{{ number_format($todayOrderTotal, 0, ',', '.') }}</p>
        </article>
    </div>

    <section class="mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-5">
            <div>
                <h2 class="text-lg font-semibold">Pesanan Terbaru - Belum Selesai</h2>
                <p class="mt-1 text-sm text-slate-500">Periksa nomor transaksi, pembayaran, dan keterangan pelanggan.</p>
            </div>
            <a href="{{ route('kasir.pesanan.index') }}" class="rounded-full bg-amber-500 px-4 py-2 text-sm font-semibold text-black hover:bg-amber-400">Semua pesanan</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-6 py-4">No. Transaksi</th>
                        <th class="px-6 py-4">Pelanggan</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Jumlah</th>
                        <th class="px-6 py-4">Total</th>
                        <th class="px-6 py-4">Pembayaran</th>
                        <th class="px-6 py-4">Jenis</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($orders as $order)
                        <tr>
                            <td class="px-6 py-4 font-semibold">#{{ $order->id }}</td>
                            <td class="px-6 py-4">{{ $order->nama ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $order->tanggal ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $order->jumlah_produk ?? 0 }}</td>
                            <td class="px-6 py-4">Rp{{ number_format($order->total ?? 0, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">{{ ($order->metode_pembayaran ?? 'cash') === 'qris' ? 'QRIS' : 'Tunai' }}</td>
                            <td class="px-6 py-4">{{ ($order->tipe_pesanan ?? 'delivery') === 'takein' ? 'Take In' : 'Delivery' }}</td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('kasir.pesanan.status', $order->id) }}" class="flex min-w-48 items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label for="status-{{ $order->id }}" class="sr-only">Status transaksi #{{ $order->id }}</label>
                                    <select id="status-{{ $order->id }}" name="status" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-xs">
                                        <option value="pending" @selected(($order->status ?? 'pending') === 'pending')>Pending</option>
                                        <option value="proses" @selected(($order->status ?? '') === 'proses')>Proses</option>
                                        <option value="selesai" @selected(($order->status ?? '') === 'selesai')>Selesai</option>
                                    </select>
                                    <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-700">Simpan</button>
                                </form>
                            </td>
                            <td class="px-6 py-4"><a href="{{ route('kasir.pesanan.show', $order->id) }}" class="font-semibold text-amber-700 hover:underline">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-6 py-10 text-center text-slate-400">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-lg font-semibold text-emerald-700">Pesanan Selesai</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-6 py-4">No. Transaksi</th>
                        <th class="px-6 py-4">Pelanggan</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Jumlah</th>
                        <th class="px-6 py-4">Total</th>
                        <th class="px-6 py-4">Pembayaran</th>
                        <th class="px-6 py-4">Jenis</th>
                        <th class="px-6 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($completedOrders as $order)
                        <tr class="bg-emerald-50/40">
                            <td class="px-6 py-4 font-semibold">#{{ $order->id }}</td>
                            <td class="px-6 py-4">{{ $order->nama ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $order->tanggal ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $order->jumlah_produk ?? 0 }}</td>
                            <td class="px-6 py-4">Rp{{ number_format($order->total ?? 0, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">{{ ($order->metode_pembayaran ?? 'cash') === 'qris' ? 'QRIS' : 'Tunai' }}</td>
                            <td class="px-6 py-4">{{ ($order->tipe_pesanan ?? 'delivery') === 'takein' ? 'Take In' : 'Delivery' }}</td>
                            <td class="px-6 py-4"><a href="{{ route('kasir.pesanan.show', $order->id) }}" class="font-semibold text-amber-700 hover:underline">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-10 text-center text-slate-400">Belum ada pesanan selesai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
