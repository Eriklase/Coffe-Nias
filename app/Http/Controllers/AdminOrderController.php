<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Schema::hasTable('pesanan')
            ? DB::table('pesanan')->where('status', '!=', 'selesai')->orderByDesc('tanggal')->get()
            : collect();
        $completedOrders = Schema::hasTable('pesanan')
            ? DB::table('pesanan')->where('status', 'selesai')->orderByDesc('tanggal')->get()
            : collect();
        $todayOrderCount = 0;
        $todayOrderTotal = 0;
        $pendingOrderCount = 0;
        $completedOrderCount = 0;

        if (Schema::hasTable('pesanan')) {
            $todayOrders = DB::table('pesanan')->whereDate('tanggal', now()->toDateString());
            $todayOrderCount = (clone $todayOrders)->count();
            $todayOrderTotal = (clone $todayOrders)->where('status', 'selesai')->sum('total');
            $pendingOrderCount = DB::table('pesanan')->where('status', 'pending')->count();
            $completedOrderCount = DB::table('pesanan')->where('status', 'selesai')->count();
        }

        return view('admin.pesanan.index', compact(
            'orders',
            'completedOrders',
            'todayOrderCount',
            'todayOrderTotal',
            'pendingOrderCount',
            'completedOrderCount'
        ));
    }

    public function show($id)
    {
        if (! Schema::hasTable('pesanan')) {
            abort(404);
        }

        $order = DB::table('pesanan')->where('id', $id)->first();
        $details = Schema::hasTable('detail_pesanan')
            ? DB::table('detail_pesanan')
                ->leftJoin('produk', 'detail_pesanan.produk_id', '=', 'produk.id')
                ->where('pesanan_id', $id)
                ->select('detail_pesanan.*', 'produk.nama_produk')
                ->get()
            : collect();

        return view('admin.pesanan.show', compact('order', 'details'));
    }

    public function cetak($id)
    {
        if (! Schema::hasTable('pesanan')) {
            abort(404);
        }

        $order = DB::table('pesanan')->where('id', $id)->first();
        $details = Schema::hasTable('detail_pesanan')
            ? DB::table('detail_pesanan')
                ->leftJoin('produk', 'detail_pesanan.produk_id', '=', 'produk.id')
                ->where('pesanan_id', $id)
                ->select('detail_pesanan.*', 'produk.nama_produk')
                ->get()
            : collect();

        return view('admin.pesanan.cetak', compact('order', 'details'));
    }
}

