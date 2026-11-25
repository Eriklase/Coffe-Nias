<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keranjang | Nias Coffee</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: radial-gradient(circle at top, rgba(255, 200, 120, 0.12), transparent 35%),
                linear-gradient(180deg, #0f0a05 0%, #231507 48%, #0b0602 100%);
        }
    </style>
</head>
<body class="min-h-screen text-white antialiased">
    <header class="border-b border-white/10 bg-black/40">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-6 py-5">
            <a href="{{ route('home') }}" class="text-lg font-bold text-amber-100">☕ Nias Coffee</a>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('home') }}#menu-kopi" class="text-slate-200 hover:text-amber-200">Pilih produk lain</a>
                <a href="{{ route('keranjang.index') }}" class="text-amber-200">Keranjang ({{ $cartCount }})</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl space-y-8 px-6 py-12">
        <div>
            <p class="text-sm uppercase tracking-[0.24em] text-amber-200/80">Pesanan Anda</p>
            <h1 class="mt-3 text-3xl font-semibold sm:text-4xl">Keranjang belanja</h1>
            <p class="mt-3 text-slate-300">Periksa pilihan produk, atur jumlah, lalu checkout semua produk sekaligus.</p>
        </div>

        @if(session('success'))
            <div role="status" class="rounded-2xl border border-emerald-300/30 bg-emerald-950/50 p-4 text-emerald-100">{{ session('success') }}</div>
        @endif

        @if(session('order_number'))
            <section aria-live="polite" class="rounded-3xl border border-emerald-300/30 bg-emerald-950/50 p-6">
                <p class="text-sm uppercase tracking-[0.2em] text-emerald-200">Pesanan berhasil dicatat</p>
                <h2 class="mt-3 text-2xl font-semibold">Nomor transaksi: <span class="text-amber-200">#{{ session('order_number') }}</span></h2>
                <p class="mt-3 text-slate-200">Total: <strong class="text-white">Rp{{ number_format(session('order_total'), 0, ',', '.') }}</strong></p>
                <p class="mt-2 text-sm text-slate-300">Jenis pesanan: {{ session('order_type') === 'delivery' ? 'Delivery' : 'Take In / Beli di Tempat' }}</p>
                @if(session('order_payment_method') === 'qris')
                    <p class="mt-2 text-sm text-slate-300">Metode pembayaran: QRIS. Tunjukkan nomor transaksi ini kepada kasir saat konfirmasi pembayaran.</p>
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
                    @if($qrisAvailable)
                        <div class="mt-5 max-w-xs rounded-2xl bg-white p-4 text-center">
                            <img src="{{ $qrisUrl }}" alt="QRIS Nias Coffee" class="mx-auto aspect-square w-full object-contain">
                            <p class="mt-2 text-sm font-semibold text-slate-700">Scan QRIS untuk membayar Rp{{ number_format(session('order_total'), 0, ',', '.') }}</p>
                        </div>
                    @endif
                @else
                    <p class="mt-2 text-sm text-slate-300">Metode pembayaran: Tunai. {{ session('order_type') === 'delivery' ? 'Pembayaran dilakukan saat pesanan diantar.' : 'Pembayaran dilakukan kepada kasir saat mengambil pesanan.' }}</p>
                @endif
                <p class="mt-3 text-sm text-slate-400">Simpan nomor transaksi ini untuk memeriksa pesanan Anda.</p>
            </section>
        @endif

        @if($errors->any())
            <div role="alert" class="rounded-2xl border border-rose-400/30 bg-rose-950/60 p-5 text-rose-100">
                <p class="font-semibold">Keranjang belum dapat diproses:</p>
                <ul class="mt-2 list-inside list-disc text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($cartItems->isEmpty())
            <section class="rounded-3xl border border-white/10 bg-white/5 p-8 text-center">
                <p class="text-slate-300">Keranjang Anda masih kosong.</p>
                <a href="{{ route('home') }}#menu-kopi" class="mt-5 inline-flex rounded-full bg-amber-500 px-6 py-3 font-semibold text-black transition hover:bg-amber-400">Jelajahi produk</a>
            </section>
        @else
            @php $total = $cartItems->sum(fn ($item) => $item['product']->harga * $item['quantity']); @endphp
            <section class="overflow-hidden rounded-3xl border border-white/10 bg-white/5">
                <div class="divide-y divide-white/10">
                    @foreach($cartItems as $item)
                        @php
                            $product = $item['product'];
                            $image = $product->gambar ? asset($product->gambar) : 'https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&w=320&q=80';
                        @endphp
                        <article class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center">
                            <img src="{{ $image }}" alt="{{ $product->nama_produk }}" class="h-28 w-full rounded-2xl bg-slate-900 object-cover sm:w-36">
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('produk.show', $product) }}" class="text-lg font-semibold hover:text-amber-200">{{ $product->nama_produk }}</a>
                                <p class="mt-1 text-sm text-slate-400">Rp{{ number_format($product->harga, 0, ',', '.') }} · Stok {{ $product->stok }}</p>
                                <p class="mt-2 font-semibold text-amber-200">Subtotal: Rp{{ number_format($product->harga * $item['quantity'], 0, ',', '.') }}</p>
                            </div>
                            <div class="flex items-end gap-3">
                                <form method="POST" action="{{ route('keranjang.update', $product) }}" class="flex items-end gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label class="text-xs text-slate-400">
                                        Jumlah
                                        <input type="number" name="quantity" min="1" max="{{ max(1, $product->stok) }}" value="{{ $item['quantity'] }}" required class="mt-1 block w-20 rounded-lg border border-white/15 bg-black/40 px-2 py-2 text-center text-white">
                                    </label>
                                    <button type="submit" class="rounded-full border border-white/20 px-4 py-2 text-sm hover:border-amber-300">Ubah</button>
                                </form>
                                <form method="POST" action="{{ route('keranjang.remove', $product) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-full border border-rose-300/30 px-4 py-2 text-sm text-rose-200 hover:bg-rose-950/50">Hapus</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="border-t border-white/10 px-6 py-5 text-right">
                    <span class="text-slate-300">Total sementara</span>
                    <strong class="ml-3 text-xl text-amber-200">Rp{{ number_format($total, 0, ',', '.') }}</strong>
                </div>
            </section>

            <form id="checkout" method="POST" action="{{ route('keranjang.checkout') }}" class="space-y-8">
                @csrf
                <section class="grid gap-8 rounded-3xl border border-white/10 bg-white/5 p-6 sm:grid-cols-2 lg:grid-cols-3 sm:p-8">
                    <div class="space-y-5">
                        <h2 class="text-xl font-semibold">Data pelanggan</h2>
                        <label class="block text-sm text-slate-300">
                            Nama
                            <input type="text" name="nama" value="{{ old('nama') }}" required maxlength="150" autocomplete="name" class="mt-2 w-full rounded-xl border border-white/15 bg-black/40 px-4 py-3 text-white focus:border-amber-300 focus:outline-none">
                        </label>
                        <label class="block text-sm text-slate-300">
                            Nomor HP
                            <input type="tel" name="nomor_hp" value="{{ old('nomor_hp') }}" required maxlength="20" autocomplete="tel" class="mt-2 w-full rounded-xl border border-white/15 bg-black/40 px-4 py-3 text-white focus:border-amber-300 focus:outline-none">
                        </label>
                    </div>
                    <div class="space-y-5">
                        <label class="block text-sm text-slate-300">
                            Jenis pesanan
                            <select id="tipe-pesanan" name="tipe_pesanan" class="mt-2 w-full rounded-xl border border-white/15 bg-black/40 px-4 py-3 text-white focus:border-amber-300 focus:outline-none">
                                <option value="delivery" @selected(old('tipe_pesanan', 'delivery') === 'delivery')>Delivery</option>
                                <option value="takein" @selected(old('tipe_pesanan') === 'takein')>Take In / Beli di Tempat</option>
                            </select>
                        </label>
                        <label id="alamat-pelanggan" class="block text-sm text-slate-300">
                            Alamat pengantaran
                            <textarea id="alamat-input" name="alamat" rows="3" maxlength="1000" autocomplete="street-address" placeholder="Masukkan alamat jika memilih Delivery" class="mt-2 w-full rounded-xl border border-white/15 bg-black/40 px-4 py-3 text-white placeholder:text-slate-500 focus:border-amber-300 focus:outline-none">{{ old('alamat') }}</textarea>
                        </label>
                    </div>
                    <fieldset class="space-y-4">
                        <legend class="text-xl font-semibold">Metode pembayaran</legend>
                        <label class="flex cursor-pointer gap-3 rounded-2xl border border-white/10 bg-black/20 p-4">
                            <input type="radio" name="metode_pembayaran" value="qris" @checked(old('metode_pembayaran') === 'qris') required class="mt-1 accent-amber-400">
                            <span><span class="block font-semibold">QRIS</span><span class="mt-1 block text-sm text-slate-400">Tunjukkan nomor transaksi kepada kasir.</span></span>
                        </label>
                        <label class="flex cursor-pointer gap-3 rounded-2xl border border-white/10 bg-black/20 p-4">
                            <input type="radio" name="metode_pembayaran" value="cash" @checked(old('metode_pembayaran', 'cash') === 'cash') required class="mt-1 accent-amber-400">
                            <span><span class="block font-semibold">Tunai</span><span class="mt-1 block text-sm text-slate-400">Bayar kepada kasir.</span></span>
                        </label>
                    </fieldset>
                </section>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('home') }}#menu-kopi" class="text-sm text-slate-300 hover:text-white">Tambah produk lain</a>
                    <button type="submit" class="rounded-full bg-amber-500 px-8 py-3 font-semibold text-black transition hover:bg-amber-400">Checkout semua produk</button>
                </div>
            </form>
        @endif
    </main>
    <script>
        const orderType = document.getElementById('tipe-pesanan');
        const addressInput = document.getElementById('alamat-input');
        const addressLabel = document.getElementById('alamat-pelanggan');

        if (orderType && addressInput && addressLabel) {
            const updateAddress = () => {
                const needsAddress = orderType.value === 'delivery';
                addressInput.required = needsAddress;
                addressLabel.hidden = !needsAddress;
            };
            orderType.addEventListener('change', updateAddress);
            updateAddress();
        }
    </script>
</body>
</html>
