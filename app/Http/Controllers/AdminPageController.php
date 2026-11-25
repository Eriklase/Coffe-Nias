<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AdminPageController extends Controller
{
    public function delivery()
    {
        $orders = DB::table('pesanan')
            ->where('tipe_pesanan', 'delivery')
            ->where('status', '!=', 'selesai')
            ->orderByDesc('tanggal')
            ->get();
        $completedOrders = DB::table('pesanan')
            ->where('tipe_pesanan', 'delivery')
            ->where('status', 'selesai')
            ->orderByDesc('tanggal')
            ->get();
        $orderCount = $orders->count() + $completedOrders->count();
        $processingCount = $orders->where('status', 'proses')->count();

        return view('admin.delivery.index', compact('orders', 'completedOrders', 'orderCount', 'processingCount'));
    }

    public function takein()
    {
        $orders = DB::table('pesanan')
            ->where('tipe_pesanan', 'takein')
            ->where('status', '!=', 'selesai')
            ->orderByDesc('tanggal')
            ->get();
        $completedOrders = DB::table('pesanan')
            ->where('tipe_pesanan', 'takein')
            ->where('status', 'selesai')
            ->orderByDesc('tanggal')
            ->get();
        $orderCount = $orders->count() + $completedOrders->count();
        $pendingCount = $orders->where('status', 'pending')->count();

        return view('admin.takein.index', compact('orders', 'completedOrders', 'orderCount', 'pendingCount'));
    }
}
