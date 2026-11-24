@extends('layouts.app')

@section('content')
<div class="vote-hero">
    <div>
        <span class="eyebrow">Ruang pengelola</span>
        <h1 class="display-title">Perbarui data<br><em>pasangan calon.</em></h1>
        <p class="lead">Nomor urut, nama, visi, dan misi wajib diisi.</p>
    </div>
    <aside class="hero-note"><span>{{ str_pad($candidate->nomor_urut, 2, '0', STR_PAD_LEFT) }}</span><p>{{ $candidate->nama_ketua }} bersama {{ $candidate->nama_wakil }}</p></aside>
</div>

@if($errors->any())
    <div class="flash flash-error" role="alert"><span class="flash-dot"></span>{{ $errors->first() }}</div>
@endif

<section class="form-panel login-panel">
    <form action="{{ url('/admin/paslon/'.$candidate->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-grid">
            <div class="field"><label for="nomor_urut">Nomor urut</label><input id="nomor_urut" type="number" name="nomor_urut" min="1" required value="{{ old('nomor_urut', $candidate->nomor_urut) }}"></div>
            <div class="field field-wide">
                <label for="foto_url">Link langsung ke foto <span>(opsional)</span></label>
                <input id="foto_url" type="url" name="foto_url" placeholder="https://contoh.com/foto.jpg" value="{{ old('foto_url', filter_var($candidate->foto, FILTER_VALIDATE_URL) ? $candidate->foto : '') }}" data-photo-url>
                <small class="field-hint">Gunakan tautan langsung ke file gambar (.jpg, .png, atau .webp), bukan tautan postingan Instagram/Google Drive. Untuk tautan postingan, unduh gambarnya lalu unggah di bawah.</small>
            </div>
            <div class="field field-wide">
                <span class="field-label">Unggah atau tempel foto</span>
                <input id="foto_file" class="photo-file-input" type="file" name="foto_file" accept="image/*" data-photo-input>
                <label class="btn-secondary photo-select-button" for="foto_file">Pilih file gambar</label>
                <div class="photo-paste-area select-none" data-photo-paste role="group" aria-label="Tempel atau tarik gambar paslon di sini" tabindex="0">
                    <span class="photo-paste-icon" aria-hidden="true">+</span>
                    <strong data-photo-label>Tempel gambar dengan Ctrl+V atau tarik gambar ke sini</strong>
                    <small>Format gambar, maksimal 5 MB</small>
                    <img class="photo-preview" data-photo-preview src="{{ $candidate->photoUrl() }}" alt="Pratinjau foto paslon" @if(!$candidate->foto) hidden @endif>
                    <small class="photo-error" data-photo-error role="status" hidden>Tautan ini tidak menampilkan file gambar. Gunakan tautan langsung ke gambar atau unggah file.</small>
                </div>
            </div>
            <div class="field"><label for="nama_ketua">Nama ketua</label><input id="nama_ketua" type="text" name="nama_ketua" required value="{{ old('nama_ketua', $candidate->nama_ketua) }}"></div>
            <div class="field"><label for="nama_wakil">Nama wakil</label><input id="nama_wakil" type="text" name="nama_wakil" required value="{{ old('nama_wakil', $candidate->nama_wakil) }}"></div>
            <div class="field field-wide"><label for="visi">Visi</label><textarea id="visi" name="visi" required>{{ old('visi', $candidate->visi ?: $candidate->visi_misi) }}</textarea></div>
            <div class="field field-wide"><label for="misi">Misi</label><textarea id="misi" name="misi" required>{{ old('misi', $candidate->misi) }}</textarea></div>
        </div>
        <div class="form-actions">
            <a class="btn-secondary" href="/admin">Kembali</a>
            <button type="submit" class="btn-primary">Simpan perubahan</button>
        </div>
    </form>
</section>
@endsection