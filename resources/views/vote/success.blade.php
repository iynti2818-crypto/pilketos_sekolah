@extends('layouts.app')

@section('content')
<section class="home-hero">
    <span class="eyebrow">Suara berhasil disimpan</span>
    <h1 class="display-title">Terima kasih<br><em>sudah memilih.</em></h1>
    <p class="lead">Hak pilih Anda telah tercatat. Sesi pemilih sudah diakhiri.</p>
    <a class="btn-primary" href="{{ route('home') }}">Kembali ke beranda</a>
</section>
@endsection
