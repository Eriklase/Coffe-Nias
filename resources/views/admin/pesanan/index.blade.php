@extends('admin.layout')

@section('title', 'Kelola Pesanan')
@section('section-title', 'Kasir')
@section('page-title', 'Pesanan & Transaksi')

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

    <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200/10 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-lg font-semibold">Pesanan Belum Selesai</h2>
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-6 py-4 text-left">No Pesanan</th>
                    <th class="px-6 py-4 text-left">Nama Pembeli</th>
                    <th class="px-6 py-4 text-left">Tanggal</th>
                    <th class="px-6 py-4 text-left">Jumlah Produk</th>
                    <th class="px-6 py-4 text-left">Total Bayar</th>
                    <th class="px-6 py-4 text-left">Pembayaran</th>
                    <th class="px-6 py-4 text-left">Jenis</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($orders as $o)
                    <tr>
                        <td class="px-6 py-4">{{ $o->id }}</td>
                        <td class="px-6 py-4">{{ $o->nama ?? $o->user_id ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $o->tanggal ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $o->jumlah_produk ?? '-' }}</td>
                        <td class="px-6 py-4">Rp{{ number_format($o->total ?? 0,0,',','.') }}</td>
                        <td class="px-6 py-4">{{ $o->metode_pembayaran === 'qris' ? 'QRIS' : 'Tunai' }}</td>
                        <td class="px-6 py-4">{{ ($o->tipe_pesanan ?? 'delivery') === 'takein' ? 'Take In' : 'Delivery' }}</td>
                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('admin.pesanan.status', $o->id) }}" class="flex min-w-48 items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <label for="status-{{ $o->id }}" class="sr-only">Status transaksi #{{ $o->id }}</label>
                                <select id="status-{{ $o->id }}" name="status" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-xs">
                                    <option value="pending" @selected(($o->status ?? 'pending') === 'pending')>Pending</option>
                                    <option value="proses" @selected(($o->status ?? '') === 'proses')>Proses</option>
                                    <option value="selesai" @selected(($o->status ?? '') === 'selesai')>Selesai</option>
                                </select>
                                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-700">Simpan</button>
                            </form>
                        </td>
                        <td class="px-6 py-4"><a href="{{ route('admin.pesanan.show', $o->id) }}" class="text-amber-500 hover:underline">Detail</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-10 text-center text-slate-400">Belum ada pesanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200/10 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-lg font-semibold text-emerald-700">Pesanan Selesai</h2>
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-6 py-4 text-left">No Pesanan</th>
                    <th class="px-6 py-4 text-left">Nama Pembeli</th>
                    <th class="px-6 py-4 text-left">Tanggal</th>
                    <th class="px-6 py-4 text-left">Jumlah Produk</th>
                    <th class="px-6 py-4 text-left">Total Bayar</th>
                    <th class="px-6 py-4 text-left">Pembayaran</th>
                    <th class="px-6 py-4 text-left">Jenis</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($completedOrders as $o)
                    <tr class="bg-emerald-50/40">
                        <td class="px-6 py-4">{{ $o->id }}</td>
                        <td class="px-6 py-4">{{ $o->nama ?? $o->user_id ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $o->tanggal ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $o->jumlah_produk ?? '-' }}</td>
                        <td class="px-6 py-4">Rp{{ number_format($o->total ?? 0,0,',','.') }}</td>
                        <td class="px-6 py-4">{{ $o->metode_pembayaran === 'qris' ? 'QRIS' : 'Tunai' }}</td>
                        <td class="px-6 py-4">{{ ($o->tipe_pesanan ?? 'delivery') === 'takein' ? 'Take In' : 'Delivery' }}</td>
                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('admin.pesanan.status', $o->id) }}" class="flex min-w-48 items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <label for="completed-status-{{ $o->id }}" class="sr-only">Status transaksi #{{ $o->id }}</label>
                                <select id="completed-status-{{ $o->id }}" name="status" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-xs">
                                    <option value="pending" @selected(($o->status ?? '') === 'pending')>Pending</option>
                                    <option value="proses" @selected(($o->status ?? '') === 'proses')>Proses</option>
                                    <option value="selesai" @selected(($o->status ?? '') === 'selesai')>Selesai</option>
                                </select>
                                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-700">Simpan</button>
                            </form>
                        </td>
                        <td class="px-6 py-4"><a href="{{ route('admin.pesanan.show', $o->id) }}" class="text-amber-500 hover:underline">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-6 py-10 text-center text-slate-400">Belum ada pesanan selesai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection