<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Suara Kita' }} | Pemilihan Ketua OSIS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell">
    <div class="ambient ambient-one"></div>
    <div class="ambient ambient-two"></div>

    <header class="site-header">
        <a href="{{ route('home') }}" class="brand" aria-label="SMKN 1 Ciomas - Beranda">
            <span class="brand-primary">
                <img src="{{ asset('images/smkn1-ciomas.png') }}" alt="Logo SMKN 1 Ciomas" class="brand-school-logo">
                <span class="brand-copy">
                    <strong>SMKN 1 Ciomas</strong>
                    <small>Pemilihan Ketua OSIS</small>
                </span>
            </span>
        </a>
        <nav class="main-nav" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
            <a href="{{ route('candidates') }}" class="{{ request()->routeIs('candidates') ? 'active' : '' }}">Kenali paslon</a>
            @auth
                @unless(auth()->user()->isAdmin())
                    <a href="/vote" class="{{ request()->is('vote') ? 'active' : '' }}">Bilik suara</a>
                @endunless
            @endauth
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="/admin" class="{{ request()->is('admin') ? 'active' : '' }}">Panel admin</a>
                @endif
                <span class="nav-user">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-logout">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="{{ request()->is('login') || request()->is('admin*') ? 'active' : '' }}">Halaman admin</a>
            @endauth
        </nav>
    </header>

    <main class="page-wrap">
        @if(session('success'))
            <div class="flash flash-success" role="status"><span class="flash-dot"></span>{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error" role="alert"><span class="flash-dot"></span>{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="site-footer-content">
            <section class="footer-school">
                <h2>SMKN 1 Ciomas</h2>
                <p>Aplikasi Pemilihan Ketua OSIS yang mendorong proses pemungutan suara secara transparan, tertib, dan bertanggung jawab.</p>
                <address>Jl. Raya Laladon, Desa Laladon, Kec. Ciomas, Kabupaten Bogor, Jawa Barat 16610</address>
                <span class="footer-npsn">NPSN 20254135</span>
            </section>

            <nav class="footer-nav" aria-label="Navigasi footer">
                <h2>Navigasi</h2>
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('candidates') }}">Kenali Paslon</a>
                <a href="{{ route('login') }}">Halaman Admin</a>
            </nav>

            <section class="footer-contact">
                <h2>Kontak</h2>
                <a href="tel:02517520933">0251-7520933</a>
                <a href="mailto:smkn1_ciomas@yahoo.co.id">smkn1_ciomas@yahoo.co.id</a>
                <div class="footer-socials" aria-label="Media sosial SMKN 1 Ciomas">
                    <a href="https://instagram.com/smkn1ciomas" target="_blank" rel="noopener noreferrer" aria-label="Instagram SMKN 1 Ciomas">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="18" cy="6" r="1.2" fill="currentColor"/></svg>
                    </a>
                    <a href="https://tiktok.com/@smkn1ciomas" target="_blank" rel="noopener noreferrer" aria-label="TikTok SMKN 1 Ciomas">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19.6 8.2a7.1 7.1 0 0 1-4.4-1.6v7.1a5.7 5.7 0 1 1-5-5.7v3.7a2.1 2.1 0 1 0 1.4 2V2.8h3.6a7 7 0 0 0 4.4 3.3v2.1Z" fill="currentColor"/></svg>
                    </a>
                    <a href="https://www.youtube.com/results?search_query=SMKN+1+Ciomas" target="_blank" rel="noopener noreferrer" aria-label="Cari SMKN 1 Ciomas di YouTube">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23 7.2a3 3 0 0 0-2.1-2.1C19 4.6 12 4.6 12 4.6s-7 0-8.9.5A3 3 0 0 0 1 7.2 31 31 0 0 0 .5 12a31 31 0 0 0 .5 4.8 3 3 0 0 0 2.1 2.1c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .5-4.8 31 31 0 0 0-.5-4.8ZM9.7 15.4V8.6l6 3.4-6 3.4Z" fill="currentColor"/></svg>
                    </a>
                </div>
            </section>
        </div>
        <div class="footer-copyright">
            <span>© 2026 Suara Kita — SMKN 1 Ciomas | PPLG</span>
        </div>
    </footer>
</body>
</html>
