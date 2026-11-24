@extends('layouts.app')

@section('content')
<div class="vote-hero">
    <div>
        <span class="eyebrow">Akses petugas</span>
        <h1 class="display-title">Masuk ke<br><em>halaman admin.</em></h1>
        <p class="lead">Halaman ini khusus petugas untuk mengelola paslon dan memantau hasil suara.</p>
    </div>
    <aside class="hero-note"><span>01</span><p>Setiap siswa memiliki satu hak suara.</p></aside>
</div>

<section class="form-panel login-panel">
    @if($errors->any())
        <div class="flash flash-error" role="alert"><span class="flash-dot"></span>{{ $errors->first() }}</div>
    @endif
    <form action="/login" method="POST">
        @csrf
        <div class="form-grid">
            <div class="field field-wide">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>
            <div class="field field-wide">
                <label for="password">Kata sandi</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
            </div>
        </div>
        <div class="form-actions">
            <a class="btn-secondary" href="{{ route('home') }}">Kembali ke beranda</a>
            <button type="submit" class="btn-primary">Masuk</button>
        </div>
    </form>
</section>
@endsection