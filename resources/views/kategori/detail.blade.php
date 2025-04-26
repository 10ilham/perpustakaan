@extends('layouts.app')

@section('content')
<div class="profile-page">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12">
                    <h1 class="title">Detail Kategori</h1>
                    <ol class="breadcrumb">
                        @if(auth()->user()->level == 'admin')
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
        <div class="row">
            <div class="col-md-4">
                <div class="profile-card">
                    <div class="card-body">
                        <h3 class="card-title">{{ $kategori->nama }}</h3>
                        <div class="badge badge-outline-primary" style="margin-bottom: 15px;">
                            {{ $kategori->buku->count() }} buku dalam kategori ini
                        </div>

                        <p>{{ $kategori->deskripsi ?: 'Tidak ada deskripsi' }}</p>

                        <div class="d-flex justify-content-center mt-4">
                            <a href="{{ route('kategori.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> Kembali
                            </a>

                            @if(auth()->user()->level == 'admin')
                            <a href="{{ route('kategori.edit', $kategori->id) }}" class="btn btn-success">
                                <i class="bx bx-edit"></i> Edit
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="profile-card">
                    <div class="card-body">
                        <h5 class="card-title">Buku dalam Kategori Ini</h5>

                        @if($kategori->buku->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Judul</th>
                                        <th>Pengarang</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kategori->buku as $index => $buku)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $buku->kode_buku }}</td>
                                        <td>{{ $buku->judul }}</td>
                                        <td>{{ $buku->pengarang }}</td>
                                        <td>
                                            @if($buku->status == 'Tersedia')
                                            <span class="badge badge-outline-success">{{ $buku->status }}</span>
                                            @elseif($buku->status == 'Dipinjam')
                                            <span class="badge badge-outline-warning">{{ $buku->status }}</span>
                                            @else
                                            <span class="badge badge-outline-danger">{{ $buku->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('buku.detail', $buku->id) }}" class="btn btn-sm btn-info" title="Detail">
                                                <i class="bx bx-info-circle"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
@endsection
