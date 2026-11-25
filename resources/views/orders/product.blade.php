<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->nama_produk }} | Nias Coffee</title>
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
                <a href="{{ route('home') }}#menu-kopi" class="text-slate-200 hover:text-amber-200">Menu</a>
                <a href="{{ route('keranjang.index') }}" class="text-amber-200 hover:text-amber-100">Keranjang ({{ array_sum(session('cart', [])) }})</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-6 py-12">
        <a href="{{ route('home') }}#menu-kopi" class="text-sm text-slate-300 hover:text-amber-200">← Kembali ke menu</a>

        @if($errors->any())
            <div role="alert" class="mt-6 rounded-2xl border border-rose-400/30 bg-rose-950/60 p-4 text-rose-100">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <article class="mt-6 grid overflow-hidden rounded-3xl border border-white/10 bg-white/5 md:grid-cols-2">
            @php $image = $product->gambar ? asset($product->gambar) : 'https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&w=900&q=80'; @endphp
            <img src="{{ $image }}" alt="{{ $product->nama_produk }}" class="h-80 w-full bg-slate-900 object-cover md:h-full">
            <div class="flex flex-col p-6 sm:p-9">
                <p class="text-sm uppercase tracking-[0.22em] text-amber-200/80">{{ $product->kategori ?? 'Kopi' }}</p>
                <h1 class="mt-3 text-3xl font-semibold sm:text-4xl">{{ $product->nama_produk }}</h1>
                <p class="mt-5 leading-relaxed text-slate-300">{{ $product->deskripsi ?: 'Nikmati pilihan menu Nias Coffee yang diracik dengan bahan berkualitas.' }}</p>
                <p class="mt-6 text-2xl font-semibold text-amber-200">Rp{{ number_format($product->harga, 0, ',', '.') }}</p>
                <p class="mt-2 text-sm text-slate-400">Stok tersedia: {{ $product->stok }}</p>

                @if($product->stok > 0)
                    <form method="POST" action="{{ route('keranjang.add', $product) }}" class="mt-8 flex flex-wrap items-end gap-3">
                        @csrf
                        <label class="text-sm text-slate-300">
                            Jumlah
                            <input type="number" name="quantity" min="1" max="{{ $product->stok }}" value="{{ old('quantity', 1) }}" required class="mt-2 block w-24 rounded-xl border border-white/15 bg-black/40 px-3 py-3 text-center text-white focus:border-amber-300 focus:outline-none">
                        </label>
                        <div class="flex flex-col gap-2">
                            <button type="submit" name="next" value="cart" class="rounded-full bg-amber-500 px-6 py-3 font-semibold text-black transition hover:bg-amber-400">Tambah ke keranjang</button>
                            <button type="submit" name="next" value="checkout" class="rounded-full border border-amber-200/50 px-6 py-3 font-semibold text-amber-100 transition hover:border-amber-200">Pesan sekarang</button>
                        </div>
                    </form>
                    <a href="{{ route('keranjang.index') }}" class="mt-4 text-sm text-amber-200 hover:text-amber-100">Lanjut ke keranjang →</a>
                @else
                    <p class="mt-8 rounded-xl border border-rose-300/20 bg-rose-950/40 p-4 text-rose-200">Produk sedang habis.</p>
                @endif
            </div>
        </article>
    </main>
</body>
</html>
