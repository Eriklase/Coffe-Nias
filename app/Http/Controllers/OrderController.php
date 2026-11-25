<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function showProduct(Product $product)
    {
        return view('orders.product', compact('product'));
    }

    public function cart()
    {
        $cart = session('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');
        $cartItems = collect($cart)
            ->map(fn ($quantity, $productId) => [
                'product' => $products->get($productId),
                'quantity' => (int) $quantity,
            ])
            ->filter(fn ($item) => $item['product'] !== null)
            ->values();

        return view('orders.cart', [
            'cartItems' => $cartItems,
            'cartCount' => array_sum($cart),
        ]);
    }

    public function addToCart(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'next' => 'nullable|in:cart,checkout',
        ]);
        $cart = session('cart', []);
        $quantity = (int) ($cart[$product->id] ?? 0) + (int) $data['quantity'];

        if ($product->stok < 1 || $quantity > $product->stok) {
            return back()->withErrors([
                'quantity' => 'Jumlah melebihi stok '.$product->nama_produk.' yang tersedia.',
            ]);
        }

        $cart[$product->id] = $quantity;
        session(['cart' => $cart]);

        $redirectUrl = route('keranjang.index');
        if (($data['next'] ?? 'cart') === 'checkout') {
            $redirectUrl .= '#checkout';
        }

        return redirect()->to($redirectUrl)->with('success', $product->nama_produk.' ditambahkan ke keranjang.');
    }

    public function updateCart(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($data['quantity'] > $product->stok) {
            return back()->withErrors([
                'quantity' => 'Jumlah melebihi stok '.$product->nama_produk.' yang tersedia.',
            ]);
        }

        $cart = session('cart', []);
        $cart[$product->id] = (int) $data['quantity'];
        session(['cart' => $cart]);

        return redirect()->route('keranjang.index')->with('success', 'Jumlah produk berhasil diperbarui.');
    }

    public function removeFromCart(Product $product)
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return redirect()->route('keranjang.index')->with('success', 'Produk dihapus dari keranjang.');
    }

    public function checkout(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('keranjang.index')->withErrors([
                'items' => 'Keranjang Anda masih kosong.',
            ]);
        }

        $request->merge(['items' => $cart]);

        return $this->store($request);
    }

    public function index(Request $request)
    {
        $products = Product::where('stok', '>', 0)->orderBy('nama_produk')->get();
        $selectedType = $request->query('jenis') === 'takein' ? 'takein' : 'delivery';

        return view('orders.create', compact('products', 'selectedType'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*' => 'required|integer|min:0',
            'catatan' => 'nullable|array',
            'catatan.*' => 'nullable|string|max:500',
            'nama' => 'required|string|max:150',
            'nomor_hp' => 'required|string|max:20',
            'tipe_pesanan' => 'required|in:delivery,takein',
            'alamat' => 'required_if:tipe_pesanan,delivery|nullable|string|max:1000',
            'metode_pembayaran' => 'required|in:qris,cash',
        ]);

        $items = collect($data['items'])
            ->mapWithKeys(fn ($qty, $productId) => [(int) $productId => (int) $qty])
            ->filter(fn ($qty) => $qty > 0);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Pilih setidaknya satu produk untuk dipesan.',
            ]);
        }

        $result = DB::transaction(function () use ($data, $items) {
            $products = Product::whereIn('id', $items->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $items->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu produk tidak lagi tersedia. Silakan periksa pesanan Anda.',
                ]);
            }

            $total = 0;
            foreach ($items as $productId => $qty) {
                $product = $products->get($productId);
                if ($product->stok < $qty) {
                    throw ValidationException::withMessages([
                        'items' => 'Stok '.$product->nama_produk.' tidak mencukupi.',
                    ]);
                }

                $total += $product->harga * $qty;
            }

            $orderId = DB::table('pesanan')->insertGetId([
                'user_id' => auth()->id() ?? null,
                'nama' => $data['nama'],
                'nomor_hp' => $data['nomor_hp'],
                'alamat' => $data['tipe_pesanan'] === 'delivery' ? $data['alamat'] : null,
                'jumlah_produk' => $items->sum(),
                'total' => $total,
                'status' => 'pending',
                'tipe_pesanan' => $data['tipe_pesanan'],
                'metode_pembayaran' => $data['metode_pembayaran'],
                'tanggal' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($items as $productId => $qty) {
                $product = $products->get($productId);
                DB::table('detail_pesanan')->insert([
                    'pesanan_id' => $orderId,
                    'produk_id' => $product->id,
                    'qty' => $qty,
                    'subtotal' => $product->harga * $qty,
                    'catatan' => $data['catatan'][$productId] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $product->decrement('stok', $qty);
            }

            return [$orderId, $total];
        });
        [$orderId, $total] = $result;

        $isCartCheckout = $request->routeIs('keranjang.checkout');
        if ($isCartCheckout) {
            $request->session()->forget('cart');
        }

        return redirect()->route($isCartCheckout ? 'keranjang.index' : 'pesan.index', $isCartCheckout ? [] : ['jenis' => $data['tipe_pesanan']])
            ->with('order_number', $orderId)
            ->with('order_total', $total)
            ->with('order_payment_method', $data['metode_pembayaran'])
            ->with('order_type', $data['tipe_pesanan']);
    }
}
