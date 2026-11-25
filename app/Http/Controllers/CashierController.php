<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function dashboard()
    {
        $todayOrders = DB::table('pesanan')->whereDate('tanggal', now()->toDateString());
        $todayOrderCount = (clone $todayOrders)->count();
        $todayOrderTotal = (clone $todayOrders)->where('status', 'selesai')->sum('total');
        $pendingOrderCount = DB::table('pesanan')->where('status', 'pending')->count();
        $completedOrderCount = DB::table('pesanan')->where('status', 'selesai')->count();
        $orders = DB::table('pesanan')
            ->where('status', '!=', 'selesai')
            ->orderByDesc('tanggal')
            ->limit(10)
            ->get();
        $completedOrders = DB::table('pesanan')
            ->where('status', 'selesai')
            ->orderByDesc('tanggal')
            ->limit(10)
            ->get();

        return view('kasir.dashboard', compact(
            'todayOrderCount',
            'todayOrderTotal',
            'pendingOrderCount',
            'completedOrderCount',
            'orders',
            'completedOrders'
        ));
    }

    public function orders()
    {
        $orders = DB::table('pesanan')
            ->where('status', '!=', 'selesai')
            ->orderByDesc('tanggal')
            ->get();
        $completedOrders = DB::table('pesanan')
            ->where('status', 'selesai')
            ->orderByDesc('tanggal')
            ->get();

        return view('kasir.pesanan.index', compact('orders', 'completedOrders'));
    }

    public function show($id)
    {
        $order = DB::table('pesanan')->where('id', $id)->first();
        abort_if(! $order, 404);

        $details = $this->orderDetails($id);

        return view('kasir.pesanan.show', compact('order', 'details'));
    }

    public function cetak($id)
    {
        $order = DB::table('pesanan')->where('id', $id)->first();
        abort_if(! $order, 404);

        $details = $this->orderDetails($id);

        return view('admin.pesanan.cetak', compact('order', 'details'));
    }

    private function orderDetails($id)
    {
        return DB::table('detail_pesanan')
            ->leftJoin('produk', 'detail_pesanan.produk_id', '=', 'produk.id')
            ->where('detail_pesanan.pesanan_id', $id)
            ->select('detail_pesanan.*', 'produk.nama_produk')
            ->get();
    }
}
