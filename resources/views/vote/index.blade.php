@extends('layouts.app')

@section('content')
<div class="vote-hero">
    <div>
        <span class="eyebrow">Bilik suara digital · 2026</span>
        <h1 class="display-title">Satu pilihan.<br><em>Satu arah baru.</em></h1>
        <p class="lead">Kenali gagasan setiap pasangan calon, lalu gunakan hak pilihmu untuk menentukan cerita OSIS tahun ini.</p>
    </div>
    <aside class="hero-note">
        <span>{{ $candidates->count() }}</span>
        <p>pasangan calon siap mendengar dan mewakili suara siswa.</p>
    </aside>
</div>

<div class="section-heading">
    <div>
        <span class="eyebrow">Pilihanmu dimulai di sini</span>
        <h2>Pasangan calon</h2>
    </div>
    <p>Geser lebih dekat, baca visi mereka.</p>
</div>

<div class="candidate-grid">
    @forelse($candidates as $c)
        <article class="candidate-card">
            <div class="candidate-top">
                <div class="candidate-number">{{ str_pad($c->nomor_urut, 2, '0', STR_PAD_LEFT) }}</div>
                @if($c->foto)
                    <img class="candidate-photo" src="{{ $c->photoUrl() }}" alt="Foto {{ $c->nama_ketua }} dan {{ $c->nama_wakil }}" data-candidate-photo>
                    <div class="candidate-initials" aria-hidden="true" data-candidate-photo-fallback hidden>{{ strtoupper(substr($c->nama_ketua, 0, 1)) }}</div>
                @else
                    <div class="candidate-initials" aria-hidden="true">{{ strtoupper(substr($c->nama_ketua, 0, 1)) }}</div>
                @endif
            </div>
            <div class="candidate-copy">
                <span class="candidate-label">Pasangan calon {{ $c->nomor_urut }}</span>
                <h3 class="candidate-name">{{ $c->nama_ketua }} <span>bersama</span> {{ $c->nama_wakil }}</h3>
                <p class="vision"><strong>Visi:</strong> {{ $c->visi ?: $c->visi_misi }}</p>
                @if($c->misi)
                    <p class="vision candidate-mission"><strong>Misi:</strong> {{ $c->misi }}</p>
                @endif
            </div>
            <div class="candidate-footer">
                <small>Pilih dengan sadar. Suaramu bersifat rahasia.</small>
                    <form action="/vote" method="POST" data-vote-form>
                    @csrf
                    <input type="hidden" name="candidate_id" value="{{ $c->id }}">
                    <button
                        type="submit"
                        class="btn-primary"
                        data-candidate-number="{{ str_pad($c->nomor_urut, 2, '0', STR_PAD_LEFT) }}"
                        data-candidate-name="{{ $c->nama_ketua }} &amp; {{ $c->nama_wakil }}"
                        {{ auth()->user()->has_voted ? 'disabled' : '' }}
                    >
                        {{ auth()->user()->has_voted ? 'Sudah memilih' : 'Pilih paslon' }}
                    </button>
                </form>
            </div>
        </article>
    @empty
        <div class="empty-state">Belum ada pasangan calon yang ditambahkan.</div>
    @endforelse
</div>

@if(auth()->user()->has_voted)
    <div class="status-banner"><strong>Pilihanmu sudah tercatat.</strong> Terima kasih sudah ikut menentukan masa depan OSIS.</div>
@endif

<div class="vote-confirm-overlay" data-vote-confirm hidden>
    <section class="vote-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="vote-confirm-title" aria-describedby="vote-confirm-message" tabindex="-1">
        <h2 id="vote-confirm-title">Konfirmasi Pilihan</h2>
        <p class="vote-confirm-candidate" data-vote-candidate></p>
        <p id="vote-confirm-message">Apakah Anda yakin memilih pasangan calon ini? Pilihan tidak dapat diubah setelah suara dikirim.</p>
        <div class="vote-confirm-actions">
            <button type="button" class="vote-confirm-cancel" data-vote-cancel>Batal</button>
            <button type="button" class="vote-confirm-submit" data-vote-submit>Ya, Kirim Suara</button>
        </div>
    </section>
</div>
@endsection
