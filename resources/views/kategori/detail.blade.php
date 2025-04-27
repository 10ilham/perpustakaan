@extends('layouts.app')

@section('content')
    <div class="profile-page">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12">
                        <h1 class="title">Detail Kategori</h1>
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
                            <li class="breadcrumb-item"><a href="{{ route('kategori.index') }}">Kategori</a></li>
                            <li class="divider">/</li>
                            <li class="breadcrumb-item active" aria-current="page">Detail</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="row detail-kategori-container">
                <!-- Informasi Kategori -->
                <div class="col-md-4">
                    <div class="profile-card">
                        <div class="card-body">
                            <h3 class="card-title">{{ $kategori->nama }}</h3>
                            <p>{{ $kategori->deskripsi ?: 'Tidak ada deskripsi' }}</p>
                            <div class="badge badge-outline-primary"
                                style="margin-bottom: 15px; margin-top: 10px; display: block;">
                                {{ $kategori->buku->count() }} buku dalam kategori ini
                            </div>

                            <div class="d-flex justify-content-center mt-4">
                                <a href="{{ route('kategori.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back"></i> Kembali
                                </a>

                                @if (auth()->user()->level == 'admin')
                                    <a href="{{ route('kategori.edit', $kategori->id) }}" class="btn btn-success">
                                        <i class="bx bx-edit"></i> Edit
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buku dalam Kategori -->
                <div class="col-md-8">
                    <div class="profile-card">
                        <div class="card-body">
                            <h5 class="card-title">Buku dalam Kategori Ini</h5>

                            @if ($kategori->buku->count() > 0)
                                <div class="row buku-container {{ $kategori->buku->count() === 1 ? 'single-card' : ($kategori->buku->count() === 2 ? 'two-cards' : ($kategori->buku->count() === 3 ? 'three-cards' : '')) }}">
                                    @foreach ($kategori->buku as $item)
                                        <div class="col-auto" style="width: calc(25% - 20px);">
                                            <div class="card card-buku text-center align-items-center justify-content-center"
                                                style="min-height: 28rem;">
                                                @if ($item->foto)
                                                    <img class="card-img-top" style="max-height: 180px;"
                                                        src="{{ asset('assets/img/buku/' . $item->foto) }}"
                                                        alt="{{ $item->judul }}">
                                                @else
                                                    <img class="card-img-top" style="height: 200px;"
                                                        src="{{ asset('assets/img/default_buku.png') }}"
                                                        alt="Default Book Cover">
                                                @endif
                                                <div
                                                    class="card-body d-flex flex-column align-items-center justify-content-center">
                                                    <div class="detail-buku">
                                                        <h5 class="card-title">
                                                            <a>
                                                                {{ $item->judul }}
                                                            </a>
                                                        </h5>
                                                        <p class="card-text m-0">Kode Buku: {{ $item->kode_buku }}</p>
                                                        <p class="card-text m-0">Pengarang: {{ $item->pengarang }}</p>
                                                        <p class="card-text m-0">Kategori: {{ $kategori->nama }}</p>
                                                        <p class="card-text m-0">Status:
                                                            @if ($item->status === 'Tersedia')
                                                                <span
                                                                    class="badge badge-outline-success">{{ $item->status }}</span>
                                                            @elseif ($item->status === 'Habis')
                                                                <span
                                                                    class="badge badge-outline-danger">{{ $item->status }}</span>
                                                            @else
                                                                <span
                                                                    class="badge badge-outline-warning">{{ $item->status }}</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="button-area mt-3">
                                                        <a href="{{ route('buku.detail', $item->id) }}"
                                                            class="btn btn-sm btn-info px-2"
                                                            style="text-decoration: none; color: white;">Detail</a>
                                                        @if (auth()->user()->level == 'admin' || auth()->user()->level == 'staff')
                                                            <a href="{{ route('buku.edit', $item->id) }}"
                                                                class="btn btn-sm btn-warning px-2"
                                                                style="text-decoration: none; color: white;">Edit</a>
                                                            <button class="btn btn-sm btn-danger px-3 delete-btn"
                                                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                                data-action="{{ route('buku.hapus', $item->id) }}">Hapus</button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    Belum ada buku dalam kategori ini.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade bootstrap-modal" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus item ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="delete-form" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
