@extends('layouts.app')

@section('content')
    <main>
        <div class="title">Data Peminjaman Buku</div>
        <ul class="breadcrumbs">
            @if (auth()->user()->level == 'admin')
                <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            @elseif(auth()->user()->level == 'siswa')
                <li><a href="{{ route('anggota.dashboard') }}">Dashboard</a></li>
            @elseif(auth()->user()->level == 'guru')
                <li><a href="{{ route('anggota.dashboard') }}">Dashboard</a></li>
            @elseif(auth()->user()->level == 'staff')
                <li><a href="{{ route('anggota.dashboard') }}">Dashboard</a></li>
            @endif
            <li class="divider">/</li>
            <li><a class="active">Peminjaman</a></li>
        </ul>

        <!-- Info Cards -->
        <div class="info-data">
            <div class="card">
                <div class="head">
                    <div>
                        <h2>{{ $totalPeminjaman }}</h2>
                        <p>Total Peminjaman {{ request('user_type') ? ucfirst(request('user_type')) : '' }}</p>
                    </div>
                    <i class='bx bxs-book-bookmark icon'></i>
                </div>
            </div>

            <!-- Card Dipinjam -->
            <div class="card">
                <div class="head">
                    <div>
                        <h2>{{ $dipinjam }}</h2>
                        <p>Sedang Dipinjam {{ request('user_type') ? ucfirst(request('user_type')) : '' }}</p>
                    </div>
                    <i class='bx bxs-book icon'></i>
                </div>
            </div>

            <!-- Card Dikembalikan -->
            <div class="card">
                <div class="head">
                    <div>
                        <h2>{{ $dikembalikan }}</h2>
                        <p>Dikembalikan {{ request('user_type') ? ucfirst(request('user_type')) : '' }}</p>
                    </div>
                    <i class='bx bx-check-circle icon'></i>
                </div>
            </div>

            <!-- Card Terlambat -->
            <div class="card">
                <div class="head">
                    <div>
                        <h2>{{ $terlambat }}</h2>
                        <p>Terlambat {{ request('user_type') ? ucfirst(request('user_type')) : '' }}</p>
                    </div>
                    <i class='bx bxs-time icon'></i>
                </div>
            </div>
        </div>

        <!-- Filter untuk Admin -->
        @if (auth()->user()->level == 'admin')
            <div class="filter">
                <div class="card">
                    <div class="head">
                        <h3>Filter Peminjaman</h3>
                    </div>
                    <form action="{{ route('peminjaman.index') }}" method="GET" class="form-group"
                        style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 10px;">
                        <select name="user_type" id="user_type" class="form-control" style="max-width: 180px;">
                            <option value="">Semua Anggota</option>
                            <option value="siswa" {{ request('user_type') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="guru" {{ request('user_type') == 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="staff" {{ request('user_type') == 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>

                        <!-- Filter Rentang Waktu -->
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <label for="start_date">Dari:</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ request('start_date') }}" style="width: 150px;">
                        </div>

                        <div style="display: flex; align-items: center; gap: 5px;">
                            <label for="end_date">Sampai:</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ request('end_date') }}" style="width: 150px;">
                        </div>

                        <button type="submit" class="btn btn-primary" style="padding: 5px 15px;">
                            <i class='bx bx-filter'></i> Filter
                        </button>

                        @if (request('user_type') || request('start_date') || request('end_date'))
                            <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary" style="padding: 5px 15px;">
                                <i class='bx bx-reset'></i> Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Global Export Section for All Tables Combined -->
            <div class="global-export-section">
                <div class="data">
                    <div class="content-data">
                        <div class="head">
                            <h3>Export Semua Data Peminjaman</h3>
                            <div class="menu">
                                <span style="color: var(--dark-grey); font-size: 14px;">
                                    <i class='bx bx-info-circle'></i> Export data dari semua tabel (Siswa, Guru, Staff)
                                    dalam satu file
                                </span>
                            </div>
                        </div>

                        <div class="export-all-buttons-container">
                            <button id="exportAllToExcel" class="btn btn-outline-success export-btn">
                                <i class="bx bx-file-blank"></i><span>Excel Semua Data</span>
                            </button>
                            <button id="exportAllToWord" class="btn btn-outline-info export-btn">
                                <i class="bx bxs-file-doc"></i><span>Word Semua Data</span>
                            </button>
                            <button id="exportAllToPDF" class="btn btn-outline-danger export-btn">
                                <i class="bx bxs-file-pdf"></i><span>PDF Semua Data</span>
                            </button>
                            <button id="exportAllToCSV" class="btn btn-outline-success export-btn">
                                <i class="bx bx-file"></i><span>CSV Semua Data</span>
                            </button>
                            <button id="printAllData" class="btn btn-outline-warning export-btn">
                                <i class="bx bx-printer"></i><span>Print Semua Data</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tampilan tabel untuk Halaman Admin -->
            <div class="tab-content" id="userTypesContent">
                @if (!request('user_type') || request('user_type') == 'siswa')
                    <div class="tab-pane fade show active" id="siswa-peminjaman" role="tabpanel"
                        aria-labelledby="siswa-tab">
                        <div class="data">
                            <div class="content-data">
                                <div class="head">
                                    <h3>Daftar Peminjaman Siswa</h3>
                                    <div class="menu">
                                        <span style="color: var(--dark-grey); font-size: 14px;">
                                            <i class='bx bx-info-circle'></i> Gunakan tombol export di bawah tabel untuk
                                            mengunduh data
                                        </span>
                                    </div>
                                </div>

                                <div class="table-responsive p-3">
                                    <table id="tableSiswa" class="table align-items-center table-flush table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>No.</th>
                                                <th>No. Peminjaman</th>
                                                <th>Judul Buku</th>
                                                <th>Nama Peminjam</th>
                                                <th>Tanggal Pinjam</th>
                                                <th>Tanggal Batas Kembali</th>
                                                <th>Tanggal Pengembalian</th>
                                                <th>Status</th>
                                                <th style="display: none;">Catatan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $siswaCount = 0; @endphp
                                            @foreach ($peminjaman as $index => $item)
                                                @if ($item->user && $item->user->level == 'siswa')
                                                    @php $siswaCount++; @endphp
                                                    <tr>
                                                        <td>{{ $siswaCount }}</td>
                                                        <td>{{ $item->no_peminjaman }}</td>
                                                        <td>{{ $item->buku->judul }}</td>
                                                        <td>{{ $item->user->nama }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($item->tanggal_pinjam)->format('d/m/Y') }}
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($item->tanggal_kembali)->format('d/m/Y') }}
                                                        </td>
                                                        <td>
                                                            @if ($item->tanggal_pengembalian)
                                                                {{ \Carbon\Carbon::parse($item->tanggal_pengembalian)->format('d/m/Y') }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($item->status == 'Dipinjam')
                                                                <span class="badge"
                                                                    style="color: #ffc107;">{{ $item->status }}
                                                                </span>
                                                            @elseif ($item->status == 'Dikembalikan')
                                                                @if ($item->is_terlambat)
                                                                    <span class="badge"
                                                                        style="color: #28a745;">{{ $item->status }}
                                                                    </span>
                                                                    <span class="badge" style="color: #dc3545;">Terlambat
                                                                        ({{ $item->jumlah_hari_terlambat }} hari)
                                                                    </span>
                                                                @else
                                                                    <span class="badge"
                                                                        style="color: #28a745;">{{ $item->status }}
                                                                    </span>
                                                                @endif
                                                            @elseif ($item->status == 'Terlambat')
                                                                <span class="badge"
                                                                    style="color: #dc3545;">{{ $item->status }}
                                                                    ({{ $item->is_late ? $item->late_days : '?' }} hari)
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td style="display: none;">{{ $item->catatan ?? '-' }}</td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="{{ route('peminjaman.detail', $item->id) }}"
                                                                    class="btn btn-sm btn-info" title="Detail">
                                                                    <i class="bx bx-info-circle"></i>
                                                                </a>

                                                                @if ($item->status == 'Dipinjam' || $item->status == 'Terlambat')
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-success-peminjaman"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#returnModal"
                                                                        data-action="{{ route('peminjaman.kembalikan', $item->id) }}"
                                                                        title="Konfirmasi Pengembalian">
                                                                        <i class="bx bx-check"></i>
                                                                    </button>
                                                                @endif

                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger delete-btn"
                                                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                                    data-action="{{ route('peminjaman.hapus', $item->id) }}"
                                                                    title="Hapus">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if (!request('user_type') || request('user_type') == 'guru')
                    <div class="tab-pane fade show active" id="guru-peminjaman" role="tabpanel"
                        aria-labelledby="guru-tab">
                        <div class="data">
                            <div class="content-data">
                                <div class="head">
                                    <h3>Daftar Peminjaman Guru</h3>
                                    <div class="menu">
                                        <span style="color: var(--dark-grey); font-size: 14px;">
                                            <i class='bx bx-info-circle'></i> Gunakan tombol export di bawah tabel untuk
                                            mengunduh data
                                        </span>
                                    </div>
                                </div>

                                <div class="table-responsive p-3">
                                    <table id="tableGuru" class="table align-items-center table-flush table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>No.</th>
                                                <th>No. Peminjaman</th>
                                                <th>Judul Buku</th>
                                                <th>Nama Peminjam</th>
                                                <th>Tanggal Pinjam</th>
                                                <th>Tanggal Batas Kembali</th>
                                                <th>Tanggal Pengembalian</th>
                                                <th>Status</th>
                                                <th style="display: none;">Catatan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $guruCount = 0; @endphp
                                            @foreach ($peminjaman as $index => $item)
                                                @if ($item->user && $item->user->level == 'guru')
                                                    @php $guruCount++; @endphp
                                                    <tr>
                                                        <td>{{ $guruCount }}</td>
                                                        <td>{{ $item->no_peminjaman }}</td>
                                                        <td>{{ $item->buku->judul }}</td>
                                                        <td>{{ $item->user->nama }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($item->tanggal_pinjam)->format('d/m/Y') }}
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($item->tanggal_kembali)->format('d/m/Y') }}
                                                        </td>
                                                        <td>
                                                            @if ($item->tanggal_pengembalian)
                                                                {{ \Carbon\Carbon::parse($item->tanggal_pengembalian)->format('d/m/Y') }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($item->status == 'Dipinjam')
                                                                <span class="badge"
                                                                    style="color: #ffc107;">{{ $item->status }}
                                                                </span>
                                                            @elseif ($item->status == 'Dikembalikan')
                                                                @if ($item->is_terlambat)
                                                                    <span class="badge"
                                                                        style="color: #28a745;">{{ $item->status }}
                                                                    </span>
                                                                    <span class="badge" style="color: #dc3545;">Terlambat
                                                                        ({{ $item->jumlah_hari_terlambat }} hari)
                                                                    </span>
                                                                @else
                                                                    <span class="badge"
                                                                        style="color: #28a745;">{{ $item->status }}
                                                                    </span>
                                                                @endif
                                                            @elseif ($item->status == 'Terlambat')
                                                                <span class="badge"
                                                                    style="color: #dc3545;">{{ $item->status }}
                                                                    ({{ $item->is_late ? $item->late_days : '?' }} hari)
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td style="display: none;">{{ $item->catatan ?? '-' }}</td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="{{ route('peminjaman.detail', $item->id) }}"
                                                                    class="btn btn-sm btn-info" title="Detail">
                                                                    <i class="bx bx-info-circle"></i>
                                                                </a>

                                                                @if ($item->status == 'Dipinjam' || $item->status == 'Terlambat')
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-success-peminjaman"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#returnModal"
                                                                        data-action="{{ route('peminjaman.kembalikan', $item->id) }}"
                                                                        title="Konfirmasi Pengembalian">
                                                                        <i class="bx bx-check"></i>
                                                                    </button>
                                                                @endif

                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger delete-btn"
                                                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                                    data-action="{{ route('peminjaman.hapus', $item->id) }}"
                                                                    title="Hapus">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if (!request('user_type') || request('user_type') == 'staff')
                    <div class="tab-pane fade {{ request('user_type') == 'staff' ? 'show active' : '' }}"
                        id="staff-peminjaman" role="tabpanel" aria-labelledby="staff-tab">
                        <div class="data">
                            <div class="content-data">
                                <div class="head">
                                    <h3>Daftar Peminjaman Staff</h3>
                                    <div class="menu">
                                        <span style="color: var(--dark-grey); font-size: 14px;">
                                            <i class='bx bx-info-circle'></i> Gunakan tombol export di bawah tabel untuk
                                            mengunduh data
                                        </span>
                                    </div>
                                </div>

                                <div class="table-responsive p-3">
                                    <table id="tableStaff" class="table align-items-center table-flush table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>No.</th>
                                                <th>No. Peminjaman</th>
                                                <th>Judul Buku</th>
                                                <th>Nama Peminjam</th>
                                                <th>Tanggal Pinjam</th>
                                                <th>Tanggal Batas Kembali</th>
                                                <th>Tanggal Pengembalian</th>
                                                <th>Status</th>
                                                <th style="display: none;">Catatan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $staffCount = 0; @endphp
                                            @foreach ($peminjaman as $index => $item)
                                                @if ($item->user && $item->user->level == 'staff')
                                                    @php $staffCount++; @endphp
                                                    <tr>
                                                        <td>{{ $staffCount }}</td>
                                                        <td>{{ $item->no_peminjaman }}</td>
                                                        <td>{{ $item->buku->judul }}</td>
                                                        <td>{{ $item->user->nama }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($item->tanggal_pinjam)->format('d/m/Y') }}
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($item->tanggal_kembali)->format('d/m/Y') }}
                                                        </td>
                                                        <td>
                                                            @if ($item->tanggal_pengembalian)
                                                                {{ \Carbon\Carbon::parse($item->tanggal_pengembalian)->format('d/m/Y') }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($item->status == 'Dipinjam')
                                                                <span class="badge"
                                                                    style="color: #ffc107;">{{ $item->status }}
                                                                </span>
                                                            @elseif ($item->status == 'Dikembalikan')
                                                                @if ($item->is_terlambat)
                                                                    <span class="badge"
                                                                        style="color: #28a745;">{{ $item->status }}
                                                                    </span>
                                                                    <span class="badge" style="color: #dc3545;">Terlambat
                                                                        ({{ $item->jumlah_hari_terlambat }} hari)
                                                                    </span>
                                                                @else
                                                                    <span class="badge"
                                                                        style="color: #28a745;">{{ $item->status }}
                                                                    </span>
                                                                @endif
                                                            @elseif ($item->status == 'Terlambat')
                                                                <span class="badge"
                                                                    style="color: #dc3545;">{{ $item->status }}
                                                                    ({{ $item->is_late ? $item->late_days : '?' }} hari)
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td style="display: none;">{{ $item->catatan ?? '-' }}</td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="{{ route('peminjaman.detail', $item->id) }}"
                                                                    class="btn btn-sm btn-info" title="Detail">
                                                                    <i class="bx bx-info-circle"></i>
                                                                </a>

                                                                @if ($item->status == 'Dipinjam' || $item->status == 'Terlambat')
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-success-peminjaman"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#returnModal"
                                                                        data-action="{{ route('peminjaman.kembalikan', $item->id) }}"
                                                                        title="Konfirmasi Pengembalian">
                                                                        <i class="bx bx-check"></i>
                                                                    </button>
                                                                @endif

                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger delete-btn"
                                                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                                    data-action="{{ route('peminjaman.hapus', $item->id) }}"
                                                                    title="Hapus">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <!-- Filter untuk non-admin -->
            <div class="filter">
                <div class="card">
                    <div class="head">
                        <h3>Filter Rentang Waktu</h3>
                    </div>
                    <form action="{{ route('peminjaman.index') }}" method="GET" class="form-group"
                        style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 10px;">

                        <div style="display: flex; align-items: center; gap: 5px;">
                            <label for="start_date">Dari:</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ request('start_date') }}" style="width: 150px;">
                        </div>

                        <div style="display: flex; align-items: center; gap: 5px;">
                            <label for="end_date">Sampai:</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ request('end_date') }}" style="width: 150px;">
                        </div>

                        <button type="submit" class="btn btn-primary" style="padding: 5px 15px;">
                            <i class='bx bx-filter'></i> Filter
                        </button>

                        @if (request('start_date') || request('end_date'))
                            <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary"
                                style="padding: 5px 15px;">
                                <i class='bx bx-reset'></i> Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Tampilan untuk user non-admin -->
            <div class="data">
                <div class="content-data">
                    <div class="head">
                        <h3>Daftar Peminjaman Anda</h3>
                    </div>
                    <div class="table-responsive p-3">
                        <table id="dataTable" class="table align-items-center table-flush table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>No.</th>
                                    <th>No. Peminjaman</th>
                                    <th>Judul Buku</th>
                                    <th>Nama Peminjam</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Tanggal Batas Kembali</th>
                                    <th>Tanggal Pengembalian</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $count = 0; @endphp
                                @foreach ($peminjaman as $index => $item)
                                    @if ($item->user_id == auth()->user()->id)
                                        @php $count++; @endphp
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td>{{ $item->no_peminjaman }}</td>
                                            <td>{{ $item->buku->judul }}</td>
                                            <td>{{ $item->user->nama }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->tanggal_pinjam)->format('d/m/Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->tanggal_kembali)->format('d/m/Y') }}</td>
                                            <td>
                                                @if ($item->tanggal_pengembalian)
                                                    {{ \Carbon\Carbon::parse($item->tanggal_pengembalian)->format('d/m/Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($item->status == 'Dipinjam')
                                                    <span class="badge" style="color: #ffc107;">{{ $item->status }}
                                                    </span>
                                                @elseif ($item->status == 'Dikembalikan')
                                                    @if ($item->is_terlambat)
                                                        <span class="badge" style="color: #28a745;">{{ $item->status }}
                                                        </span>
                                                        <span class="badge" style="color: #dc3545;">Terlambat
                                                            ({{ $item->jumlah_hari_terlambat }} hari)
                                                        </span>
                                                    @else
                                                        <span class="badge" style="color: #28a745;">{{ $item->status }}
                                                        </span>
                                                    @endif
                                                @elseif ($item->status == 'Terlambat')
                                                    <span class="badge" style="color: #dc3545;">{{ $item->status }}
                                                        ({{ $item->is_late ? $item->late_days : '?' }} hari)
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('peminjaman.detail', $item->id) }}"
                                                        class="btn btn-sm btn-info" title="Detail">
                                                        <i class="bx bx-info-circle"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </main>

    <!-- Modal Konfirmasi Pengembalian -->
    <div class="modal fade bootstrap-modal" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnModalLabel">Konfirmasi Pengembalian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah anda yakin ingin menyetujui pengembalian buku ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="return-form" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">Konfirmasi</button>
                    </form>
                </div>
            </div>
        </div>
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
                    Apakah Anda yakin ingin menghapus data peminjaman ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="delete-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Additional library for Word export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

    <script>
        $(document).ready(function() {
            // Handler untuk tombol pengembalian
            document.querySelectorAll('.btn-success-peminjaman').forEach(button => {
                button.addEventListener('click', function() {
                    const actionUrl = this.getAttribute('data-action');
                    document.getElementById('return-form').setAttribute('action', actionUrl);
                });
            });

            // Handler untuk tombol hapus
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const actionUrl = this.getAttribute('data-action');
                    document.getElementById('delete-form').setAttribute('action', actionUrl);
                });
            });

            // Handler untuk date inputs
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');

            if (startDate && endDate) {
                startDate.addEventListener('change', function() {
                    if (startDate.value) {
                        endDate.min = startDate.value;
                    }
                });

                endDate.addEventListener('change', function() {
                    if (endDate.value) {
                        startDate.max = endDate.value;
                    }
                });

                if (startDate.value) {
                    endDate.min = startDate.value;
                }

                if (endDate.value) {
                    startDate.max = endDate.value;
                }
            }

            // Fungsi Global Export untuk Gabungan Semua Tabel
            function getAllTableData() {
                const allData = [];
                let no = 1;

                // Fungsi helper untuk membersihkan teks dari HTML
                const getCleanText = (element) => {
                    if (typeof element === 'string') {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = element;
                        return tempDiv.textContent || tempDiv.innerText || element;
                    }
                    return element;
                };

                // Fungsi untuk mengumpulkan data dari tabel
                const collectDataFromTable = (tableId, userLevel) => {
                    if ($.fn.DataTable.isDataTable(tableId)) {
                        const table = $(tableId).DataTable();
                        table.rows().every(function() {
                            const row = this.data();
                            const rowData = [
                                no++,
                                getCleanText(row[1]), // No. Peminjaman
                                getCleanText(row[2]), // Judul Buku
                                getCleanText(row[3]), // Nama Peminjam
                                getCleanText(row[4]), // Tanggal Pinjam
                                getCleanText(row[5]), // Tanggal Batas Kembali
                                getCleanText(row[6]), // Tanggal Pengembalian
                                getCleanText(row[7]), // Status
                                getCleanText(row[8]), // Catatan
                                userLevel
                            ];
                            allData.push(rowData);
                        });
                    }
                };

                // Kumpulkan data dari semua tabel
                collectDataFromTable('#tableSiswa', 'Siswa');
                collectDataFromTable('#tableGuru', 'Guru');
                collectDataFromTable('#tableStaff', 'Staff');

                return allData;
            }

            // Konfigurasi dan Data Export
            const exportConfig = {
                headers: ['No', 'No. Peminjaman', 'Judul Buku', 'Nama Peminjam', 'Tanggal Pinjam',
                    'Tanggal Batas Kembali', 'Tanggal Pengembalian', 'Status', 'Catatan', 'Level'
                ],
                fileName: 'Data_Peminjaman_Semua_{{ date('d-m-Y') }}',
                dateExport: '{{ date('d/m/Y H:i:s') }}'
            };

            // Validasi Data Export
            const validateExportData = () => {
                const data = getAllTableData();
                if (data.length === 0) {
                    alert('Tidak ada data untuk diekspor!');
                    return null;
                }
                return data;
            };

            // Export ke Excel
            $('#exportAllToExcel').on('click', function() {
                const data = validateExportData();
                if (!data) return;

                const ws = XLSX.utils.aoa_to_sheet([exportConfig.headers, ...data]);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Data Peminjaman Semua');
                XLSX.writeFile(wb, exportConfig.fileName + '.xlsx');
            });

            // Export ke Word dengan format yang diperbaiki
            $('#exportAllToWord').on('click', function() {
                const data = validateExportData();
                if (!data) return;

                // Template HTML untuk Word dengan orientasi landscape
                const wordTemplate = `
                <!DOCTYPE html>
                <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
                <head>
                    <meta charset="utf-8">
                    <title>Data Peminjaman Semua Anggota</title>
                    <!--[if gte mso 9]>
                    <xml><w:WordDocument><w:View>Print</w:View><w:Zoom>90</w:Zoom><w:Orientation>Landscape</w:Orientation></w:WordDocument></xml>
                    <![endif]-->
                    <style>
                        @page { size: A4 landscape; margin: 0.5in; }
                        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; }
                        .header { text-align: center; margin-bottom: 15px; }
                        .header h2 { font-size: 14px; margin: 0 0 5px 0; }
                        .date { text-align: center; margin-bottom: 15px; color: #666; font-size: 9px; }
                        table { border-collapse: collapse; width: 100%; font-size: 8px; table-layout: fixed; }
                        th, td { border: 1px solid #ddd; padding: 4px; text-align: left; word-wrap: break-word; vertical-align: top; }
                        th { background-color: #f2f2f2; font-weight: bold; font-size: 9px; }
                    </style>
                </head>
                <body>
                    <div class="header"><h2>Data Peminjaman Semua Anggota</h2></div>
                    <div class="date"><p>Total Data: ${data.length} Peminjaman - Tanggal Export: ${exportConfig.dateExport}</p></div>
                    <table><thead><tr>${exportConfig.headers.map(h => `<th>${h}</th>`).join('')}</tr></thead>
                    <tbody>${data.map(row => `<tr>${row.map(cell => `<td>${(cell || '').toString().replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim() || '-'}</td>`).join('')}</tr>`).join('')}</tbody>
                    </table>
                </body>
                </html>`;

                // Blob buat word kegunaan untuk menyimpan file
                const blob = new Blob([wordTemplate], {
                    type: 'application/msword'
                });
                saveAs(blob, exportConfig.fileName + '.doc');
            });

            // Export ke PDF
            $('#exportAllToPDF').on('click', function() {
                const data = validateExportData();
                if (!data) return;

                const {
                    jsPDF
                } = window.jspdf;
                const doc = new jsPDF('l', 'mm', 'a4'); // landscape

                // Header PDF
                doc.setFontSize(16);
                doc.text('Data Peminjaman Semua Anggota', doc.internal.pageSize.getWidth() / 2, 15, {
                    align: 'center'
                });
                doc.setFontSize(10);
                doc.text(`Total Data: ${data.length} Peminjaman`, doc.internal.pageSize.getWidth() / 2,
                    25, {
                        align: 'center'
                    });
                doc.text(`Tanggal Export: ${exportConfig.dateExport}`, doc.internal.pageSize.getWidth() / 2,
                    30, {
                        align: 'center'
                    });

                // Tabel PDF
                doc.autoTable({
                    head: [exportConfig.headers],
                    body: data,
                    startY: 35,
                    styles: {
                        fontSize: 8
                    },
                    headStyles: {
                        fillColor: [66, 139, 202]
                    },
                    margin: {
                        top: 35
                    }
                });

                doc.save(exportConfig.fileName + '.pdf');
            });

            // Export ke CSV
            $('#exportAllToCSV').on('click', function() {
                const data = validateExportData();
                if (!data) return;

                const csvContent = [exportConfig.headers.join(',')]
                    .concat(data.map(row => row.map(cell => `"${cell || '-'}"`).join(',')))
                    .join('\n');

                const blob = new Blob([csvContent], {
                    type: 'text/csv'
                });
                saveAs(blob, exportConfig.fileName + '.csv');
            });

            // Print Semua Data
            $('#printAllData').on('click', function() {
                const data = validateExportData();
                if (!data) return;

                const printWindow = window.open('', '_blank');
                const printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Data Peminjaman Semua</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        h2 { text-align: center; margin-bottom: 10px; }
                        .info { text-align: center; margin-bottom: 20px; color: #666; }
                        table { border-collapse: collapse; width: 100%; font-size: 12px; }
                        th, td { border: 1px solid black; padding: 6px; text-align: left; }
                        th { background-color: #f2f2f2; font-weight: bold; }
                        @media print { body { margin: 0; } }
                    </style>
                </head>
                <body>
                    <h2>Data Peminjaman Semua Anggota</h2>
                    <div class="info">
                        <p>Total Data: ${data.length} Peminjaman</p>
                        <p>Tanggal Print: ${exportConfig.dateExport}</p>
                    </div>
                    <table>
                        <thead>
                            <tr>${exportConfig.headers.map(h => `<th>${h}</th>`).join('')}</tr>
                        </thead>
                        <tbody>
                            ${data.map(row => `<tr>${row.map(cell => `<td>${cell || '-'}</td>`).join('')}</tr>`).join('')}
                        </tbody>
                    </table>
                </body>
                </html>`;

                printWindow.document.write(printContent);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            });

            @if (auth()->user()->level == 'admin')
                // Fungsi untuk ekspor data tabel ke format Word
                function exportToWord(dt) {
                    // Ambil data tabel termasuk kolom catatan yang tersembunyi
                    const data = dt.buttons.exportData({
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] // Semua kolom kecuali Aksi (indeks 9)
                    });

                    // Template HTML untuk dokumen Word dengan orientasi landscape
                    const wordTemplate = `
                    <!DOCTYPE html>
                    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
                    <head>
                        <meta charset="utf-8">
                        <title>Data Peminjaman Buku</title>
                        <!--[if gte mso 9]>
                        <xml><w:WordDocument><w:View>Print</w:View><w:Zoom>90</w:Zoom><w:Orientation>Landscape</w:Orientation></w:WordDocument></xml>
                        <![endif]-->
                        <style>
                            @page { size: A4 landscape; margin: 0.5in; }
                            body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; }
                            .header { text-align: center; margin-bottom: 15px; }
                            .header h2 { font-size: 14px; margin: 0 0 5px 0; }
                            .date { text-align: center; margin-bottom: 15px; color: #666; font-size: 9px; }
                            table { border-collapse: collapse; width: 100%; font-size: 8px; table-layout: fixed; }
                            th, td { border: 1px solid #ddd; padding: 4px; text-align: left; word-wrap: break-word; vertical-align: top; }
                            th { background-color: #f2f2f2; font-weight: bold; font-size: 9px; }
                        </style>
                    </head>
                    <body>
                        <div class="header"><h2>Data Peminjaman Buku</h2></div>
                        <div class="date"><p>Data per tanggal {{ date('d/m/Y') }}</p></div>
                        <table><thead><tr>${data.header.map(h => `<th>${h}</th>`).join('')}</tr></thead>
                        <tbody>${data.body.map(row => `<tr>${row.map(cell => `<td>${cell.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim()}</td>`).join('')}</tr>`).join('')}</tbody>
                        </table>
                    </body>
                    </html>`;

                    const blob = new Blob([wordTemplate], {
                        type: 'application/msword'
                    });
                    saveAs(blob, 'Data_Peminjaman_Buku_{{ date('d-m-Y') }}.doc');
                }

                // Konfigurasi standar untuk DataTable
                const dataTableConfig = {
                    responsive: true,
                    order: [
                        [0, 'asc']
                    ],
                    dom: '<"export-buttons-container"B>frtip',
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/2.0.2/i18n/id.json'
                    },
                    columnDefs: [{
                            responsivePriority: 1,
                            targets: [0, 1, 2, 3, 7]
                        }, // Prioritas kolom utama
                        {
                            responsivePriority: 2,
                            targets: [9]
                        }, // Kolom aksi
                        {
                            orderable: false,
                            targets: [-1]
                        }, // Kolom aksi tidak dapat diurutkan
                        {
                            visible: false,
                            targets: [8]
                        } // Sembunyikan kolom catatan
                    ]
                };

                // Fungsi untuk membuat tombol export DataTable
                const createExportButtons = (userType) => ([{
                        extend: 'copy',
                        text: '<i class="bx bx-copy"></i><span>Copy</span>',
                        className: 'btn btn-outline-primary btn-sm export-btn',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                        }
                    },
                    {
                        extend: 'csv',
                        text: '<i class="bx bx-file"></i><span>CSV</span>',
                        className: 'btn btn-outline-success btn-sm export-btn',
                        filename: `Data_Peminjaman_${userType}_{{ date('d-m-Y') }}`,
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="bx bx-file-blank"></i><span>Excel</span>',
                        className: 'btn btn-outline-success btn-sm export-btn',
                        filename: `Data_Peminjaman_${userType}_{{ date('d-m-Y') }}`,
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                        }
                    },
                    {
                        text: '<i class="bx bxs-file-doc"></i><span>Word</span>',
                        className: 'btn btn-outline-info btn-sm export-btn',
                        action: (e, dt) => exportToWord(dt)
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="bx bxs-file-pdf"></i><span>PDF</span>',
                        className: 'btn btn-outline-danger btn-sm export-btn',
                        filename: `Data_Peminjaman_${userType}_{{ date('d-m-Y') }}`,
                        orientation: 'landscape',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="bx bx-printer"></i><span>Print</span>',
                        className: 'btn btn-outline-warning btn-sm export-btn',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                        }
                    }
                ]);

                // Inisialisasi DataTable untuk semua tabel
                ['Siswa', 'Guru', 'Staff'].forEach(userType => {
                    $(`#table${userType}`).DataTable({
                        ...dataTableConfig,
                        buttons: createExportButtons(userType)
                    });
                });
            @endif
        });
    </script>
@endsection
