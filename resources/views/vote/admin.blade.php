@extends('layouts.app')

@section('content')
<div class="admin-page-header">
    <span class="eyebrow">Ruang pengelola</span>
    <h1 class="display-title">Kelola paslon<br><em>dan hasil suara.</em></h1>
    <p class="lead">Ubah informasi pasangan calon dan pantau jumlah suara yang masuk.</p>
</div>

@if($errors->any())
    <div class="flash flash-error" role="alert">
        <span class="flash-dot"></span>
        <ul class="validation-error-list">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="admin-section">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Ringkasan partisipasi</span>
            <h2>Ringkasan partisipasi &amp; hasil suara</h2>
        </div>
        <p>{{ $totalVotes }} suara masuk · {{ $totalStudents }} siswa terdaftar</p>
    </div>

    <div class="admin-summary-grid">
        <div class="admin-summary-card stat-item">
            <span>Total siswa terdaftar</span>
            <strong>{{ $totalStudents }}</strong>
        </div>
        <div class="admin-summary-card stat-item">
            <span>Sudah berpartisipasi</span>
            <strong>{{ $votedStudents }}</strong>
        </div>
        <div class="admin-summary-card stat-item">
            <span>Belum berpartisipasi</span>
            <strong>{{ $totalStudents - $votedStudents }}</strong>
        </div>
    </div>

    <div class="admin-summary-subheading">
        <h3>Hasil tiap pasangan</h3>
        <span>{{ $totalVotes }} suara tercatat</span>
    </div>
    @if($candidates->isEmpty())
        <section class="results-panel">
            <div class="empty-state">Belum ada data pasangan calon untuk ditampilkan.</div>
        </section>
    @else
        <div class="admin-vote-grid">
            @foreach($candidates as $candidate)
                <article class="admin-summary-card admin-vote-card">
                    <div class="result-number">{{ str_pad($candidate->nomor_urut, 2, '0', STR_PAD_LEFT) }}</div>
                    <h3 class="result-name">{{ $candidate->nama_ketua }}<small>bersama {{ $candidate->nama_wakil }}</small></h3>
                    <div class="result-score">{{ $candidate->votes_count }}<small>suara · {{ number_format($candidate->percentage, 1) }}%</small></div>
                </article>
            @endforeach
        </div>
    @endif
</section>

<section class="admin-section">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Ubah informasi pasangan</span>
            <h2>Kelola Data Paslon</h2>
        </div>
        <p>Edit informasi pasangan atau tambahkan paslon baru.</p>
    </div>
    <details class="candidate-create" @if($errors->hasAny(['nomor_urut', 'nama_ketua', 'nama_wakil', 'visi', 'misi', 'foto_url', 'foto_file'])) open @endif>
        <summary class="btn-primary">+ Tambah Paslon</summary>
        <section class="form-panel candidate-create-panel">
            <h3>Tambah pasangan calon</h3>
            <form action="{{ route('admin.candidates.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="new_nomor_urut">Nomor urut</label>
                        <input id="new_nomor_urut" type="number" name="nomor_urut" min="1" required value="{{ old('nomor_urut') }}">
                        @error('nomor_urut')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field">
                        <label for="new_nama_ketua">Nama ketua</label>
                        <input id="new_nama_ketua" type="text" name="nama_ketua" required value="{{ old('nama_ketua') }}">
                        @error('nama_ketua')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field">
                        <label for="new_nama_wakil">Nama wakil</label>
                        <input id="new_nama_wakil" type="text" name="nama_wakil" required value="{{ old('nama_wakil') }}">
                        @error('nama_wakil')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field field-wide">
                        <label for="new_foto_url">Link langsung ke foto <span>(opsional)</span></label>
                        <input id="new_foto_url" type="url" name="foto_url" placeholder="https://contoh.com/foto.jpg" value="{{ old('foto_url') }}">
                        @error('foto_url')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field field-wide">
                        <label for="new_foto_file">Unggah foto <span>(opsional, maksimal 5 MB)</span></label>
                        <input id="new_foto_file" type="file" name="foto_file" accept="image/*">
                        @error('foto_file')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field field-wide">
                        <label for="new_visi">Visi</label>
                        <textarea id="new_visi" name="visi" required>{{ old('visi') }}</textarea>
                        @error('visi')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field field-wide">
                        <label for="new_misi">Misi</label>
                        <textarea id="new_misi" name="misi" required>{{ old('misi') }}</textarea>
                        @error('misi')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Simpan Paslon</button>
                </div>
            </form>
        </section>
    </details>
    <section class="results-panel" id="kelola-paslon">
        @forelse($candidates as $candidate)
            <div class="admin-row">
                <div class="result-number">{{ str_pad($candidate->nomor_urut, 2, '0', STR_PAD_LEFT) }}</div>
                <div class="admin-row-copy">
                    <strong>{{ $candidate->nama_ketua }} bersama {{ $candidate->nama_wakil }}</strong>
                    <small>{{ $candidate->votes_count }} suara</small>
                </div>
                <div class="admin-actions">
                    <a class="btn-secondary" href="{{ url('/admin/paslon/'.$candidate->id.'/edit') }}">Edit</a>
                </div>
            </div>
        @empty
            <div class="empty-state">Belum ada pasangan calon.</div>
        @endforelse
    </section>
</section>

<section class="admin-section">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Data pemilih</span>
            <h2>Data Pemilih</h2>
        </div>
        <p>Import data CSV dan kelola daftar siswa terdaftar.</p>
    </div>

    <div class="voter-management-grid">
        <div class="voter-import-column">
            <section class="results-panel voter-import-panel">
                <div class="admin-panel-heading">
                    <h3>Import data pemilih</h3>
                    <p>Unggah data siswa dalam format CSV.</p>
                </div>
                <form action="{{ route('admin.voters.import') }}" method="POST" enctype="multipart/form-data" class="voter-import-form">
                    @csrf
                    <div class="field">
                        <label for="csv_file">File CSV siswa</label>
                        <input id="csv_file" type="file" name="csv_file" accept=".csv,text/csv" required>
                        <small class="field-hint">Urutan kolom: nisn, name, class, birth_date (YYYY-MM-DD) · Maksimal 10 MB.</small>
                    </div>
                    <div class="voter-import-actions">
                        <a class="btn-secondary" href="{{ route('admin.voters.template') }}">Download Template CSV</a>
                        <button type="submit" class="btn-primary">Import Data</button>
                    </div>
                </form>
            </section>

            <aside class="voter-info-card">
                <span class="eyebrow">Catatan impor</span>
                <strong>Siapkan format CSV dengan benar</strong>
                <p>Gunakan kolom nisn, name, class, dan birth_date. Tanggal lahir memakai format YYYY-MM-DD.</p>
                <div class="voter-info-total">
                    <span>Total siswa terdaftar</span>
                    <strong>{{ $totalStudents }}</strong>
                </div>
            </aside>
        </div>

        <section class="results-panel voter-list-panel">
            <div class="admin-panel-heading voter-list-heading">
                <div>
                    <span class="eyebrow">Daftar pemilih</span>
                    <h3>Data siswa terdaftar</h3>
                </div>
                <p>{{ $totalStudents }} siswa</p>
            </div>
            <form action="{{ url('/admin') }}" method="GET" class="voter-search">
                <div class="field">
                    <label for="search">Cari NISN atau nama</label>
                    <input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Contoh: 2026000001 atau Alya">
                </div>
                <button type="submit" class="btn-primary">Cari</button>
            </form>

            <div class="voter-list-scroll" role="region" aria-label="Daftar siswa terdaftar" tabindex="0">
                @forelse($voters as $voter)
                    <div class="student-row">
                        <div class="student-copy">
                            <strong>{{ $voter->name }}</strong>
                            <small>NISN {{ $voter->nisn }} · {{ $voter->kelas ?? '-' }}</small>
                        </div>
                        <span class="student-status {{ $voter->has_voted ? 'status-voted' : 'status-pending' }}">
                            {{ $voter->has_voted ? 'Sudah Memilih' : 'Belum Memilih' }}
                        </span>
                    </div>
                @empty
                    <div class="empty-state">
                        {{ request('search') ? 'Pemilih dengan pencarian tersebut tidak ditemukan.' : 'Belum ada data pemilih.' }}
                    </div>
                @endforelse
            </div>

            @if($voters->hasPages())
                <div class="voter-pagination">{{ $voters->links() }}</div>
            @endif
        </section>
    </div>
</section>
@endsection
