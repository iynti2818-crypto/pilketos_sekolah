@extends('layouts.app')

@section('content')
<div class="vote-hero">
    <div>
        <span class="eyebrow">Papan pantau · diperbarui langsung</span>
        <h1 class="display-title">Suara yang<br><em>mulai berbicara.</em></h1>
        <p class="lead">Lihat perolehan suara sementara dari setiap pasangan calon dalam satu ruang yang jernih.</p>
    </div>
    <aside class="hero-note"><span>{{ $candidates->sum('votes_count') }}</span><p>suara masuk sejauh ini. Setiap pilihan punya arti.</p></aside>
</div>

<div class="section-heading">
    <div><span class="eyebrow">Perolehan sementara</span><h2>Hasil pemilihan</h2></div>
    <p>Urutan mengikuti nomor pasangan calon.</p>
</div>

<section class="results-panel">
    @if($candidates->isEmpty())
        <div class="empty-state">Belum ada data pasangan calon untuk ditampilkan.</div>
    @else
        <div class="result-list">
            @foreach($candidates as $c)
                <div class="result-row">
                    <div class="result-number">{{ str_pad($c->nomor_urut, 2, '0', STR_PAD_LEFT) }}</div>
                    <h3 class="result-name">{{ $c->nama_ketua }} <small>bersama {{ $c->nama_wakil }}</small></h3>
                    <div class="result-score">{{ $c->votes_count }}<small>suara</small></div>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
