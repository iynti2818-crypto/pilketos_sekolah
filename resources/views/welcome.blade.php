@extends('layouts.app')

@section('content')
<section class="home-hero">
    <div>
        <span class="eyebrow">Pemilihan Ketua OSIS · 2026</span>
        <h1 class="display-title">Suaramu menentukan<br><em>arah sekolah.</em></h1>
        <p class="lead">Pilih langkah yang ingin kamu lakukan. Kenali calon, gunakan hak pilih, atau masuk ke halaman petugas.</p>
    </div>
</section>

<section class="section-heading home-section-heading">
    <div>
        <span class="eyebrow">Menu utama</span>
        <h2>Pilih tujuanmu</h2>
    </div>
    <p>Tiga langkah, satu halaman yang mudah diikuti.</p>
</section>

<div class="home-actions">
    <a class="home-action-card home-action-admin" href="{{ auth()->guest() ? route('login') : (auth()->user()->isAdmin() ? '/admin' : '/login') }}">
        <span class="home-action-number">01</span>
        <span class="home-action-illustration" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none">
                <rect x="11" y="9" width="26" height="31" rx="7" stroke="currentColor" stroke-width="2.5"/>
                <path d="M18 9.5V8a3 3 0 0 1 3-3h6a3 3 0 0 1 3 3v1.5M18 22h12M18 29h8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                <path d="m27 34 3 3 6-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span class="home-action-copy">
            <strong>Lanjut ke halaman admin</strong>
            <small>Kelola paslon dan pantau hasil suara.</small>
        </span>
        <span class="home-action-arrow" aria-hidden="true">&rarr;</span>
    </a>
    <a class="home-action-card home-action-vote" href="{{ auth()->check() && !auth()->user()->isAdmin() ? '/vote' : (auth()->check() ? '/admin' : route('voter.login')) }}">
        <span class="home-action-number">02</span>
        <span class="home-action-illustration" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none">
                <path d="M12 17.5 24 11l12 6.5v17a3 3 0 0 1-3 3H15a3 3 0 0 1-3-3v-17Z" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/>
                <path d="M18 24h12M18 30h7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                <path d="m31 8 1.4 3.6L36 13l-3.6 1.4L31 18l-1.4-3.6L26 13l3.6-1.4L31 8Z" fill="currentColor"/>
            </svg>
        </span>
        <span class="home-action-copy">
            <strong>Lanjut untuk memilih</strong>
            <small>Isi nama dan kelas, lalu pilih paslon.</small>
        </span>
        <span class="home-action-arrow" aria-hidden="true">&rarr;</span>
    </a>
    <a class="home-action-card home-action-candidates" href="{{ route('candidates') }}">
        <span class="home-action-number">03</span>
        <span class="home-action-illustration" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none">
                <circle cx="19" cy="17" r="6" stroke="currentColor" stroke-width="2.5"/>
                <path d="M8 37c.8-6.2 4.7-9.5 11-9.5s10.2 3.3 11 9.5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                <path d="m34 15 1.6 3.8 3.9 1.6-3.9 1.6-1.6 3.9-1.6-3.9-3.9-1.6 3.9-1.6L34 15Z" fill="currentColor"/>
            </svg>
        </span>
        <span class="home-action-copy">
            <strong>Lanjut mengenal paslon</strong>
            <small>Lihat foto, visi, dan misi setiap calon.</small>
        </span>
        <span class="home-action-arrow" aria-hidden="true">&rarr;</span>
    </a>
</div>
@endsection
