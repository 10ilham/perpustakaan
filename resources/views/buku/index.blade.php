@extends('layouts.app')

@section('content')
    <main>
        <div class="title">Data Buku Perpustakaan</div>
        <ul class="breadcrumbs">
            @if (auth()->user()->level == 'admin')
                <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            @elseif(auth()->user()->level == 'siswa')
                <li><a href="{{ route('siswa.dashboard') }}">Dashboard</a></li>
            @elseif(auth()->user()->level == 'guru')
                <li><a href="{{ route('guru.dashboard') }}">Dashboard</a></li>
            @elseif(auth()->user()->level == 'staff')
                <li><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
            @endif
            <li class="divider">/</li>
            <li><a class="active">Buku</a></li>
        </ul>

        <div class="info-data">
            <!-- Card Total Buku -->
            <div class="card">
                <div class="head">
                    <div>
                        <h2>{{ $totalBuku }}</h2>
                        <p>Total Buku</p>
                    </div>
                    <i class='bx bxs-book icon'></i>
                </div>
            </div>
            <!-- Card Buku Tersedia -->
            <div class="card">
                <div class="head">
                    <div>
                        <h2>{{ $tersedia }}</h2>
                        <p>Tersedia</p>
                    </div>
                    <i class='bx bxs-book-open icon'></i>
                </div>
            </div>
            <!-- Card Buku Dipinjam -->
            <div class="card">
                <div class="head">
                    <div>
                        <h2>{{ $dipinjam }}</h2>
                        <p>Dipinjam</p>
                    </div>
                    <i class='bx bxs-user-check icon'></i>
                </div>
            </div>
        </div>

        <div class="filter">
            <!-- Card Filter Buku -->
            <div class="card">
                <div class="head">
                    <h3>Filter Buku</h3>
                </div>
                <form action="{{ route('buku.index') }}" method="GET" class="form-group"
                    style="margin-top: 10px; display: flex; gap: 10px;">
                    <select name="kategori" id="kategori" class="form-control" onchange="this.form.submit()"
                        style="max-width: 180px;">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategori as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama }}</option>
                        @endforeach
                    </select>
                    <select name="status" id="status" class="form-control" onchange="this.form.submit()"
                        style="max-width: 150px;">
                        <option value="">Semua Status</option>
                        <option value="Tersedia" {{ request('status') == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="Dipinjam" {{ request('status') == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="data">
            <div class="content-data">
                <div class="head">
                    <h3>Daftar Buku</h3>
                    @if (auth()->user()->level == 'admin')
                        <a href="{{ route('buku.tambah') }}" class="btn btn-success d-flex align-items-center">
                            <i class='bx bxs-plus-circle me-1'></i>
                            <span>Tambah Buku</span>
                        </a>
                    @endif
                </div>

                <form class="navbar-search mb-3" action="/buku" method="GET">
                    <div class="input-group">
                        <input type="search" name="search" class="form-control bg-light border-1 small"
                            placeholder="Cari Judul Buku" aria-label="Search" aria-describedby="basic-addon2"
                            style="border-color: #244fbc;">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>

                <div class="row d-flex flex-wrap justify-content-center">
                    @forelse ($buku as $item)
                        <div class="col-auto my-2" style="width: 18rem;">
                            <div class="card card-buku mx-2 my-2 text-center align-items-center justify-content-center"
                                style="min-height: 28rem;">
                                @if ($item->foto)
                                    <img class="card-img-top" style="max-height: 180px;"
                                        src="{{ asset('assets/img/buku/' . $item->foto) }}" alt="{{ $item->judul }}">
                                @else
                                    <img class="card-img-top" style="height: 200px;"
                                        src="{{ asset('assets/img/default_buku.png') }}" alt="Default Book Cover">
                                @endif
                                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                    <div class="detail-buku">
                                        <h5 class="card-title">
                                            <a>
                                                {{ $item->judul }}
                                            </a>
                                        </h5>
                                        <p class="card-text m-0">Kode Buku: {{ $item->kode_buku }}</p>
                                        <p class="card-text m-0">Pengarang: {{ $item->pengarang }}</p>
                                        <p class="card-text m-0">Kategori: {{ $item->kategori->nama }}</p>
                                        <p class="card-text m-0">Status:
                                            @if ($item->status === 'Tersedia')
                                                <span class="badge badge-outline-success">{{ $item->status }}</span>
                                            @elseif ($item->status === 'Dipinjam')
                                                <span class="badge badge-outline-warning">{{ $item->status }}</span>
                                            @else
                                                <span class="badge badge-outline-danger">{{ $item->status }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    @if (auth()->user()->level == 'admin' || auth()->user()->level == 'staff')
                                        <div class="button-area mt-3">
                                            <a href="{{ route('buku.detail', $item->id) }}"
                                                class="btn btn-sm btn-info px-2"
                                                style="text-decoration: none; color: white;">Detail</a>
                                            <a href="{{ route('buku.edit', $item->id) }}"
                                                class="btn btn-sm btn-warning px-2"
                                                style="text-decoration: none; color: white;">Edit</a>
                                            <button class="btn btn-sm btn-danger px-3 delete-btn" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-action="{{ route('buku.hapus', $item->id) }}">Hapus</button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <h1 class="text-primary mt-3">Tidak ada buku</h1>
                    @endforelse
                </div>

                <div class="d-flex justify-content-between align-items-center mx-2 my-2" style="margin-top: 10px;">
                    @if ($buku instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <p class="text-primary my-2 mb-0">Menampilkan data ke-{{ $buku->firstItem() }} hingga
                            {{ $buku->lastItem() }}
                            dari {{ $buku->total() }} buku</p>
                        <div class="pagination-wrapper">
                            {{ $buku->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <p class="text-primary my-2 mb-0">Menampilkan 1 dari 1 Halaman</p>
                    @endif
                </div>

            </div>
        </div>
    </main>

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
