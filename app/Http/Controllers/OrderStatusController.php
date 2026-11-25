<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderStatusController extends Controller
{
    public function update(Request $request, $id)
    {
        abort_unless(DB::table('pesanan')->where('id', $id)->exists(), 404);

        $data = $request->validate([
            'status' => 'required|in:pending,proses,selesai',
        ]);

        DB::table('pesanan')->where('id', $id)->update([
            'status' => $data['status'],
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Status transaksi #'.$id.' berhasil diperbarui.');
    }
}
