@extends('layouts.app')

@section('content')
<section class="vote-hero candidate-intro-hero">
    <div class="candidate-intro-copy">
        <span class="eyebrow">Kenali sebelum memilih</span>
        <h1 class="display-title">Calon pemimpin<br><em>OSIS kita.</em></h1>
        <p class="lead">Lihat foto, visi, dan misi setiap pasangan calon sebelum menentukan pilihan.</p>
    </div>
    <aside class="candidate-intro-summary" aria-label="Ringkasan pasangan calon">
        <span class="eyebrow">Pasangan calon terdaftar</span>
        <strong class="candidate-intro-count">{{ $candidates->count() }}</strong>
        <p>pasangan calon siap memperjuangkan gagasan mereka.</p>
        <div class="candidate-intro-guide">
            <span aria-hidden="true">01</span>
            <p>Baca visi dan misi setiap paslon sebelum menentukan pilihan.</p>
        </div>
    </aside>
</section>

<div class="section-heading">
    <div><span class="eyebrow">Pasangan calon</span><h2>Gagasan mereka</h2></div>
    <a class="btn-primary" href="{{ auth()->check() && !auth()->user()->isAdmin() ? '/vote' : (auth()->check() ? '/admin' : route('voter.login')) }}">Lanjut pilih</a>
</div>

<div class="candidate-grid candidate-grid-public">
    @forelse($candidates as $candidate)
        <article class="candidate-card">
            <div class="candidate-top">
                <div class="candidate-number">{{ str_pad($candidate->nomor_urut, 2, '0', STR_PAD_LEFT) }}</div>
                @if($candidate->foto)
                    <img class="candidate-photo" src="{{ $candidate->photoUrl() }}" alt="Foto {{ $candidate->nama_ketua }} dan {{ $candidate->nama_wakil }}" data-candidate-photo>
                    <div class="candidate-initials" aria-hidden="true" data-candidate-photo-fallback hidden>{{ strtoupper(substr($candidate->nama_ketua, 0, 1)) }}</div>
                @else
                    <div class="candidate-initials" aria-hidden="true">{{ strtoupper(substr($candidate->nama_ketua, 0, 1)) }}</div>
                @endif
            </div>
            <div class="candidate-copy">
                <span class="candidate-label">Pasangan calon {{ $candidate->nomor_urut }}</span>
                <h3 class="candidate-name">{{ $candidate->nama_ketua }}<span>bersama</span>{{ $candidate->nama_wakil }}</h3>
                <p class="vision"><strong>Visi:</strong> {{ $candidate->visi ?: $candidate->visi_misi }}</p>
                @if($candidate->misi)
                    <p class="vision candidate-mission"><strong>Misi:</strong> {{ $candidate->misi }}</p>
                @endif
            </div>
        </article>
    @empty
        <div class="empty-state">Belum ada pasangan calon untuk ditampilkan.</div>
    @endforelse
</div>

<div class="form-actions">
    <a class="btn-secondary" href="{{ route('home') }}">Kembali ke beranda</a>
</div>
@endsection
