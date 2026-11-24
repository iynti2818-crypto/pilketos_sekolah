@extends('layouts.app')

@section('content')
<div class="vote-hero">
    <div>
        <span class="eyebrow">Verifikasi pemilih</span>
        <h1 class="display-title">Masuk ke<br><em>bilik suara.</em></h1>
        <p class="lead">Masukkan NISN dan tanggal lahir sesuai data yang tercatat di sekolah.</p>
    </div>
</div>

<section class="form-panel login-panel">
    @if($errors->any())
        <div class="flash flash-error" role="alert"><span class="flash-dot"></span>{{ $errors->first() }}</div>
    @endif
    <div class="login-layout">
        <form action="{{ route('voter.login.submit') }}" method="POST">
            @csrf
            <div class="form-grid">
                <div class="field field-wide">
                    <label for="nisn">NISN</label>
                    <input id="nisn" type="text" name="nisn" value="{{ old('nisn') }}" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" required autofocus autocomplete="username">
                </div>
                <div class="field field-wide">
                    <label for="birth_date">Tanggal lahir</label>
                    <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date') }}" required autocomplete="bday">
                </div>
            </div>
            <div class="form-actions">
                <a class="btn-secondary" href="{{ route('home') }}">Kembali ke beranda</a>
                <button type="submit" class="btn-primary">Masuk ke bilik suara</button>
            </div>
        </form>

        <aside class="login-guide" aria-label="Panduan pemilih">
            <h2 class="login-guide-title">Panduan singkat</h2>
            <ol class="login-guide-list">
                <li>
                    <span class="login-guide-number">01</span>
                    <p>Pastikan NISN dan tanggal lahir sesuai data siswa sekolah.</p>
                </li>
                <li>
                    <span class="login-guide-number">02</span>
                    <p>Setiap siswa hanya memiliki satu hak suara dan otomatis terkunci setelah memilih.</p>
                </li>
                <li>
                    <span class="login-guide-number">03</span>
                    <p>Gunakan tombol “Kembali ke beranda” jika ingin melihat profil paslon terlebih dahulu.</p>
                </li>
            </ol>
        </aside>
    </div>
</section>
@endsection
