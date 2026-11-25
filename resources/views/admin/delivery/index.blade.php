@extends('admin.layout')

@section('title', 'Transaksi Delivery')
@section('section-title', 'Transaksi Delivery')
@section('page-title', 'Transaksi Delivery')

@section('content')
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl border border-slate-200/10 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Pesanan Delivery</p>
            <p class="mt-4 text-3xl font-semibold text-slate-950">{{ $orderCount }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200/10 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Pesanan Dalam Proses</p>
            <p class="mt-4 text-3xl font-semibold text-slate-950">{{ $processingCount }}</p>
        </div>
    </div>

    <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200/10 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-lg font-semibold">Pesanan Delivery Belum Selesai</h2>
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-6 py-4 text-left">No Pesanan</th>
                    <th class="px-6 py-4 text-left">Nama</th>
                    <th class="px-6 py-4 text-left">Alamat</th>
                    <th class="px-6 py-4 text-left">Waktu Order</th>
                    <th class="px-6 py-4 text-left">Total</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($orders as $order)
                    <tr>
                        <td class="px-6 py-4">#{{ $order->id }}</td>
                        <td class="px-6 py-4">{{ $order->nama ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $order->alamat ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $order->tanggal ?? '-' }}</td>
                        <td class="px-6 py-4">Rp{{ number_format($order->total ?? 0, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ $order->status ?? 'pending' }}</td>
                        <td class="px-6 py-4"><a href="{{ route('admin.pesanan.show', $order->id) }}" class="text-amber-500 hover:underline">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-10 text-center text-slate-400">Belum ada pesanan delivery.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200/10 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-lg font-semibold text-emerald-700">Pesanan Delivery Selesai</h2>
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-6 py-4 text-left">No Pesanan</th>
                    <th class="px-6 py-4 text-left">Nama</th>
                    <th class="px-6 py-4 text-left">Alamat</th>
                    <th class="px-6 py-4 text-left">Waktu Order</th>
                    <th class="px-6 py-4 text-left">Total</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($completedOrders as $order)
                    <tr class="bg-emerald-50/40">
                        <td class="px-6 py-4">#{{ $order->id }}</td>
                        <td class="px-6 py-4">{{ $order->nama ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $order->alamat ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $order->tanggal ?? '-' }}</td>
                        <td class="px-6 py-4">Rp{{ number_format($order->total ?? 0, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ $order->status ?? 'selesai' }}</td>
                        <td class="px-6 py-4"><a href="{{ route('admin.pesanan.show', $order->id) }}" class="text-amber-500 hover:underline">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-10 text-center text-slate-400">Belum ada pesanan delivery selesai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection