<?php

use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminPageController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminTestimoniController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\TestimoniController;
use Illuminate\Auth\Middleware\Authenticate;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CashierMiddleware;
use Illuminate\Support\Facades\Route;

use App\Models\Product;
use App\Models\Testimoni;

Route::get('/', function () {
    $products = Product::where('stok', '>', 0)->get();
    $approvedTestimonials = Testimoni::where('disetujui', true)->latest()->get();

    return view('home', compact('products', 'approvedTestimonials'));
})->name('home');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/produk/{product}', [OrderController::class, 'showProduct'])->name('produk.show');
Route::get('/keranjang', [OrderController::class, 'cart'])->name('keranjang.index');
Route::post('/keranjang/checkout', [OrderController::class, 'checkout'])->name('keranjang.checkout');
Route::post('/keranjang/{product}', [OrderController::class, 'addToCart'])->name('keranjang.add');
Route::patch('/keranjang/{product}', [OrderController::class, 'updateCart'])->name('keranjang.update');
Route::delete('/keranjang/{product}', [OrderController::class, 'removeFromCart'])->name('keranjang.remove');
Route::get('/pesan', [OrderController::class, 'index'])->name('pesan.index');
Route::post('/pesan', [OrderController::class, 'store'])->name('pesan.store');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.send');
Route::post('/testimoni', [TestimoniController::class, 'store'])->name('testimoni.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware([Authenticate::class, CashierMiddleware::class])->prefix('kasir')->group(function () {
    Route::get('/dashboard', [CashierController::class, 'dashboard'])->name('kasir.dashboard');
    Route::get('/pesanan', [CashierController::class, 'orders'])->name('kasir.pesanan.index');
    Route::patch('/pesanan/{id}/status', [OrderStatusController::class, 'update'])->name('kasir.pesanan.status');
    Route::get('/pesanan/{id}', [CashierController::class, 'show'])->name('kasir.pesanan.show');
    Route::get('/pesanan/{id}/cetak', [CashierController::class, 'cetak'])->name('kasir.pesanan.cetak');
});

Route::middleware([Authenticate::class, AdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::get('/products', [AdminProductController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{id}/edit', [AdminProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{id}', [AdminProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy'])->name('admin.products.destroy');

    Route::get('/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/sales-data', [\App\Http\Controllers\AdminDashboardController::class, 'salesData'])->name('admin.sales.data');
    Route::get('/produk', [AdminProductController::class, 'index'])->name('admin.produk.index');
    Route::get('/pesanan', [AdminOrderController::class, 'index'])->name('admin.pesanan.index');
    Route::patch('/pesanan/{id}/status', [OrderStatusController::class, 'update'])->name('admin.pesanan.status');
    Route::get('/pesanan/{id}', [AdminOrderController::class, 'show'])->name('admin.pesanan.show');
    Route::get('/pesanan/{id}/cetak', [AdminOrderController::class, 'cetak'])->name('admin.pesanan.cetak');
    Route::get('/testimoni', [AdminTestimoniController::class, 'index'])->name('admin.testimoni.index');
    Route::post('/testimoni/{id}/approve', [AdminTestimoniController::class, 'approve'])->name('admin.testimoni.approve');
    Route::post('/testimoni/{id}/reject', [AdminTestimoniController::class, 'reject'])->name('admin.testimoni.reject');
    Route::get('/laporan', [AdminReportController::class, 'index'])->name('admin.laporan.index');
    Route::get('/laporan/pdf', [AdminReportController::class, 'exportPdf'])->name('admin.laporan.export_pdf');
    Route::get('/laporan/excel', [AdminReportController::class, 'exportExcel'])->name('admin.laporan.export_excel');
    Route::get('/transaksi-delivery', [AdminPageController::class, 'delivery'])->name('admin.delivery.index');
    Route::get('/transaksi-takein', [AdminPageController::class, 'takein'])->name('admin.takein.index');
});
