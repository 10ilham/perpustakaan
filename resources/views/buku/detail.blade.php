@extends('layouts.app')

@section('content')
    <div class="profile-page">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h1 class="title">Detail Buku</h1>
                        <ol class="breadcrumb">
                            @if (auth()->user()->level == 'admin')
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            @elseif(auth()->user()->level == 'siswa')
                                <li class="breadcrumb-item"><a href="{{ route('siswa.dashboard') }}">Dashboard</a></li>
                            @elseif(auth()->user()->level == 'guru')
                                <li class="breadcrumb-item"><a href="{{ route('guru.dashboard') }}">Dashboard</a></li>
                            @elseif(auth()->user()->level == 'staff')
                                <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                            @endif
                            <li class="divider">/</li>
                            <li class="breadcrumb-item"><a href="{{ route('buku.index') }}">Buku</a></li>
                            <li class="divider">/</li>
                            <li class="breadcrumb-item active" aria-current="page">Detail</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <!-- Sampul Buku -->
                <div class="col-12 col-lg-4">
                    <div class="profile-card">
                        <div class="card-body text-center">
                            <!-- Kode Buku -->
                            <div class="kode-buku position-absolute"
                                style="top: 10px; left: 10px; background: rgba(0, 0, 0, 0.7); color: white; padding: 5px 8px; border-radius: 5px; display: inline-block; font-size: 14px; margin-bottom: 10px;">
                                Kode Buku: {{ $buku->kode_buku }}
                            </div>
                            <div class="book-cover">
                                @if ($buku->foto)
                                    <img src="{{ asset('assets/img/buku/' . $buku->foto) }}" alt="{{ $buku->judul }}"
                                        style="max-width: 100%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                                @else
                                    <img src="{{ asset('assets/img/default-book.png') }}" alt="Default Book Cover"
                                        style="max-width: 100%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                                @endif
                            </div>
                            <h3 class="mt-4">{{ $buku->judul }}</h3>
                            <p class="text-muted mb-2">{{ $buku->pengarang }}</p>
                            <div class="status-badge-center">
                                @if ($buku->status === 'Tersedia')
                                    <span
                                        class="badge badge-outline-success status-badge-custom">{{ $buku->status }}</span>
                                @elseif($buku->status === 'Dipinjam')
                                    <span
                                        class="badge badge-outline-warning status-badge-custom">{{ $buku->status }}</span>
                                @else
                                    <span class="badge badge-outline-danger status-badge-custom">{{ $buku->status }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Informasi Buku -->
                <div class="col-12 col-lg-8">
                    <div class="profile-card">
                        <div class="card-body">
                            <h4 class="card-title mb-4" style="margin-bottom: 10px">Informasi Buku</h4>

                            <form class="profile-display">
                                {{-- <div class="form-group">
                                    <label for="kode_buku">Kode Buku</label>
                                    <input type="text" id="kode_buku" class="form-control"
                                        value="{{ $buku->kode_buku }}" readonly>
                                </div> --}}

                                <div class="form-group">
                                    <label for="kategori">Kategori</label>
                                    <input type="text" id="kategori" class="form-control"
                                        value="{{ $buku->kategori->nama }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="penerbit">Penerbit</label>
                                    <input type="text" id="penerbit" class="form-control" value="{{ $buku->penerbit }}"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label for="tahun_terbit">Tahun Terbit</label>
                                    <input type="text" id="tahun_terbit" class="form-control"
                                        value="{{ $buku->tahun_terbit }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="deskripsi">Deskripsi</label>
                                    <textarea id="deskripsi" class="form-control" rows="5" readonly>{{ $buku->deskripsi }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="created_at">Ditambahkan Pada</label>
                                    <input type="text" id="created_at" class="form-control"
                                        value="{{ $buku->created_at->format('d F Y H:i') }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="updated_at">Terakhir Diperbarui</label>
                                    <input type="text" id="updated_at" class="form-control"
                                        value="{{ $buku->updated_at->format('d F Y H:i') }}" readonly>
                                </div>

                                <!-- Tombol Aksi -->
                                <div class="form-group text-end">
                                    <a href="{{ route('buku.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back"></i> Kembali
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
