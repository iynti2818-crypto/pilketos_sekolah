@extends('layouts.app')

@section('content')
<div class="vote-hero">
    <div>
        <span class="eyebrow">Rekap petugas</span>
        <h1 class="display-title">Jumlah suara<br><em>setiap paslon.</em></h1>
        <p class="lead">Pantau hasil pemilihan berdasarkan suara yang sudah masuk.</p>
    </div>
    <aside class="hero-note"><span>{{ $totalVotes }}</span><p>total suara tercatat</p></aside>
</div>

<div class="section-heading">
    <div><span class="eyebrow">Hasil pemilihan</span><h2>Perolehan suara</h2></div>
    <p>{{ $totalVotes }} suara masuk</p>
</div>

<section class="results-panel">
    @if($candidates->isEmpty())
        <div class="empty-state">Belum ada data pasangan calon untuk ditampilkan.</div>
    @else
        <div class="result-list">
            @foreach($candidates as $candidate)
                <div class="result-row">
                    <div class="result-number">{{ str_pad($candidate->nomor_urut, 2, '0', STR_PAD_LEFT) }}</div>
                    <h3 class="result-name">{{ $candidate->nama_ketua }}<small>bersama {{ $candidate->nama_wakil }}</small></h3>
                    <div class="result-score">{{ $candidate->votes_count }}<small>suara · {{ number_format($candidate->percentage, 1) }}%</small></div>
                </div>
            @endforeach
        </div>
    @endif
</section>

<div class="form-actions">
    <a class="btn-secondary" href="/admin">Kembali ke panel admin</a>
</div>
@endsection
