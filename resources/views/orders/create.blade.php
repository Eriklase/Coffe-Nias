<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pemesanan Online | Nias Coffee</title>
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
                <a href="{{ route('home') }}" class="text-slate-200 hover:text-amber-200">Profil Kompeni</a>
                <a href="{{ route('keranjang.index') }}" class="text-amber-200 hover:text-amber-100">Keranjang ({{ array_sum(session('cart', [])) }})</a>
                <a href="{{ route('login') }}" class="text-slate-200 hover:text-amber-200">Kasir</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl space-y-8 px-6 py-12">
        <div>
            <p class="text-sm uppercase tracking-[0.24em] text-amber-200/80">Halaman pelanggan</p>
            <h1 class="mt-3 text-3xl font-semibold sm:text-4xl">Buat pesanan Anda</h1>
            <p class="mt-3 max-w-2xl text-slate-300">Pilih Delivery untuk pesanan yang diantar atau Take In untuk mengambil/membeli di tempat. Nomor transaksi diberikan setelah pesanan berhasil dibuat.</p>
        </div>

        @if($errors->any())
            <div role="alert" class="rounded-2xl border border-rose-400/30 bg-rose-950/60 p-5 text-rose-100">
                <p class="font-semibold">Pesanan belum dapat diproses:</p>
                <ul class="mt-2 list-inside list-disc text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('order_number'))
            <section aria-live="polite" class="rounded-3xl border border-emerald-300/30 bg-emerald-950/50 p-6 sm:p-8">
                <p class="text-sm uppercase tracking-[0.2em] text-emerald-200">Pesanan berhasil dicatat</p>
                <h2 class="mt-3 text-2xl font-semibold">Nomor transaksi: <span class="text-amber-200">#{{ session('order_number') }}</span></h2>
                <p class="mt-3 text-slate-200">Total: <strong class="text-white">Rp{{ number_format(session('order_total'), 0, ',', '.') }}</strong></p>
                <p class="mt-2 text-sm text-slate-300">Jenis pesanan: {{ session('order_type') === 'delivery' ? 'Delivery (pesanan online)' : 'Take In (ambil di tempat)' }}</p>
                @if(session('order_payment_method') === 'qris')
                    <p class="mt-2 text-sm text-slate-300">Metode: QRIS. Tunjukkan nomor transaksi ini kepada kasir saat konfirmasi pembayaran.</p>
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
                    <p class="mt-2 text-sm text-slate-300">Metode: Tunai. {{ session('order_type') === 'delivery' ? 'Pembayaran dilakukan saat pesanan diantar.' : 'Pembayaran dilakukan kepada kasir saat mengambil pesanan.' }}</p>
                @endif
                <p class="mt-3 text-sm text-slate-400">Simpan nomor transaksi ini untuk memeriksa pesanan Anda.</p>
            </section>
        @endif

        <form method="POST" action="{{ route('pesan.store') }}" class="space-y-8">
            @csrf
            <input type="hidden" name="tipe_pesanan" value="{{ $selectedType }}">
            <section class="overflow-hidden rounded-3xl border border-white/10 bg-white/5">
                <div class="border-b border-white/10 px-6 py-5">
                    <h2 class="text-xl font-semibold">Pilih menu</h2>
                    <p class="mt-1 text-sm text-slate-400">Isi jumlah untuk menu yang ingin dipesan. Biarkan 0 jika tidak dipilih.</p>
                </div>
                @if($products->isNotEmpty())
                    <div class="divide-y divide-white/10">
                        @foreach($products as $product)
                            @php $image = $product->gambar ? asset($product->gambar) : 'https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&w=320&q=80'; @endphp
                            <article class="flex flex-col gap-5 px-6 py-5 sm:flex-row">
                                <img src="{{ $image }}" alt="{{ $product->nama_produk }}" class="h-28 w-full rounded-2xl bg-slate-900 object-cover sm:h-28 sm:w-36">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                                        <div>
                                            <h3 class="font-semibold">{{ $product->nama_produk }}</h3>
                                            <p class="mt-1 text-sm text-slate-400">{{ $product->kategori ?? 'Kopi' }} · Stok {{ $product->stok }}</p>
                                            <p class="mt-1 text-amber-200">Rp{{ number_format($product->harga, 0, ',', '.') }}</p>
                                        </div>
                                        <label for="item-{{ $product->id }}" class="flex items-center gap-3 text-sm text-slate-300">
                                            <span>Jumlah</span>
                                            <input id="item-{{ $product->id }}" type="number" name="items[{{ $product->id }}]" min="0" max="{{ $product->stok }}" value="{{ old('items.'.$product->id, 0) }}" inputmode="numeric" class="w-24 rounded-xl border border-white/15 bg-black/40 px-3 py-2 text-center text-white focus:border-amber-300 focus:outline-none">
                                        </label>
                                    </div>
                                    <label for="catatan-{{ $product->id }}" class="mt-4 block text-sm text-slate-300">
                                        Keterangan untuk {{ $product->nama_produk }} <span class="text-slate-500">(opsional)</span>
                                        <textarea id="catatan-{{ $product->id }}" name="catatan[{{ $product->id }}]" rows="2" maxlength="500" placeholder="Contoh: panas, dingin, tambah gula..." class="mt-2 w-full rounded-xl border border-white/15 bg-black/40 px-4 py-3 text-white placeholder:text-slate-500 focus:border-amber-300 focus:outline-none">{{ old('catatan.'.$product->id) }}</textarea>
                                    </label>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="px-6 py-8 text-slate-300">Belum ada produk yang tersedia. Silakan kembali lagi nanti.</p>
                @endif
            </section>

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
                    <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                        <p class="font-semibold">{{ $selectedType === 'delivery' ? 'Delivery' : 'Take In / Beli di Tempat' }}</p>
                        <p class="mt-1 text-sm text-slate-400">{{ $selectedType === 'delivery' ? 'Pesanan online diantar ke alamat.' : 'Pesanan disiapkan untuk diambil di kedai.' }}</p>
                    </div>
                    @if($selectedType === 'delivery')
                        <label class="block text-sm text-slate-300">
                            Alamat pengantaran
                            <textarea name="alamat" rows="3" maxlength="1000" autocomplete="street-address" placeholder="Masukkan alamat lengkap untuk pesanan Delivery" required class="mt-2 w-full rounded-xl border border-white/15 bg-black/40 px-4 py-3 text-white placeholder:text-slate-500 focus:border-amber-300 focus:outline-none">{{ old('alamat') }}</textarea>
                        </label>
                    @endif
                </div>
                <fieldset class="space-y-4">
                    <legend class="text-xl font-semibold">Metode pembayaran</legend>
                    <label class="flex cursor-pointer gap-3 rounded-2xl border border-white/10 bg-black/20 p-4">
                        <input type="radio" name="metode_pembayaran" value="qris" @checked(old('metode_pembayaran') === 'qris') required class="mt-1 accent-amber-400">
                        <span><span class="block font-semibold">QRIS</span><span class="mt-1 block text-sm text-slate-400">Tunjukkan nomor transaksi kepada kasir untuk pembayaran.</span></span>
                    </label>
                    <label class="flex cursor-pointer gap-3 rounded-2xl border border-white/10 bg-black/20 p-4">
                        <input type="radio" name="metode_pembayaran" value="cash" @checked(old('metode_pembayaran', 'cash') === 'cash') required class="mt-1 accent-amber-400">
                        <span><span class="block font-semibold">Tunai</span><span class="mt-1 block text-sm text-slate-400">Bayar kepada kasir saat mengambil pesanan.</span></span>
                    </label>
                </fieldset>
            </section>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="text-sm text-slate-300 hover:text-white">Kembali ke profil kompeni</a>
                @if($products->isNotEmpty())
                    <button type="submit" class="rounded-full bg-amber-500 px-8 py-3 font-semibold text-black transition hover:bg-amber-400">Buat pesanan</button>
                @endif
            </div>
        </form>
    </main>
</body>
</html>
