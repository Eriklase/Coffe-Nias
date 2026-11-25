<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kasir') | Nias Coffee</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-6 py-4">
            <a href="{{ route('kasir.dashboard') }}" class="text-lg font-bold text-slate-950">☕ Nias Coffee <span class="font-normal text-amber-600">Kasir</span></a>
            <nav class="flex flex-wrap items-center gap-3 text-sm">
                <a href="{{ route('kasir.dashboard') }}" class="rounded-full px-4 py-2 font-medium {{ request()->routeIs('kasir.dashboard') ? 'bg-amber-100 text-amber-900' : 'text-slate-600 hover:bg-slate-100' }}">Dashboard</a>
                <a href="{{ route('kasir.pesanan.index') }}" class="rounded-full px-4 py-2 font-medium {{ request()->routeIs('kasir.pesanan.*') ? 'bg-amber-100 text-amber-900' : 'text-slate-600 hover:bg-slate-100' }}">Pesanan</a>
                <a href="{{ route('home') }}" class="rounded-full px-4 py-2 font-medium text-slate-600 hover:bg-slate-100">Profil Website</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-full bg-slate-900 px-4 py-2 font-semibold text-white hover:bg-slate-700">Keluar</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-8">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-600">Panel kasir</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-950">@yield('page-title', 'Dashboard')</h1>
        </div>
        @if(session('success'))
            <div role="status" class="mb-6 rounded-2xl bg-emerald-100 px-5 py-3 text-sm font-medium text-emerald-900">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div role="alert" class="mb-6 rounded-2xl bg-rose-100 px-5 py-3 text-sm text-rose-900">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
