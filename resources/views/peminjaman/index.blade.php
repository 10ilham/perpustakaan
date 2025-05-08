@extends('layouts.app')

@section('content')
    <main>
        <div class="title">Data Peminjaman Buku</div>
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
            <li><a class="active">Peminjaman</a></li>
        </ul>

        <!-- Info Cards -->
        <div class="info-data">
            <!-- Card Total Peminjaman -->
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
                        style="margin-top: 10px; display: flex; gap: 10px;">
                        <select name="user_type" id="user_type" class="form-control" onchange="this.form.submit()"
                            style="max-width: 180px;">
                            <option value="">Semua Anggota</option>
                            <option value="siswa" {{ request('user_type') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="guru" {{ request('user_type') == 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="staff" {{ request('user_type') == 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Tampilan tabel untuk Admin -->
            <div class="tab-content" id="userTypesContent">
                @if (!request('user_type') || request('user_type') == 'siswa')
                    <div class="tab-pane fade show active" id="siswa-peminjaman" role="tabpanel"
                        aria-labelledby="siswa-tab">
                        <div class="data">
                            <div class="content-data">
                                <div class="head">
                                    <h3>Daftar Peminjaman Siswa</h3>
                                </div>

                                <div class="table-responsive p-3">
                                    <table id="tableSiswa" class="table align-items-center table-flush table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>No.</th>
                                                <th>No. Peminjaman</th>
                                                <th>Judul Buku</th>
                                                <th>Peminjam</th>
                                                <th>Tgl Pinjam</th>
                                                <th>Tgl Kembali</th>
                                                <th>Status</th>
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
                                                            @if ($item->status == 'Dipinjam')
                                                                <span class="badge">{{ $item->status }}</span>
                                                            @elseif ($item->status == 'Dikembalikan')
                                                                @if ($item->is_terlambat)
                                                                    <span class="badge">{{ $item->status }}</span>
                                                                    <span class="badge">Terlambat
                                                                        ({{ $item->jumlah_hari_terlambat }} hari)
                                                                    </span>
                                                                @else
                                                                    <span class="badge">{{ $item->status }}</span>
                                                                @endif
                                                            @elseif ($item->status == 'Terlambat')
                                                                <span class="badge">{{ $item->status }}
                                                                    ({{ $item->is_late ? $item->late_days : '?' }}
                                                                    hari)</span>
                                                            @else
                                                                <span class="badge">{{ $item->status }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="{{ route('peminjaman.detail', $item->id) }}"
                                                                    class="btn btn-sm btn-info" title="Detail">
                                                                    <i class="bx bx-info-circle"></i>
                                                                </a>

                                                                @if ($item->status == 'Dipinjam' || $item->status == 'Terlambat')
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-success return-btn"
                                                                        data-bs-toggle="modal" data-bs-target="#returnModal"
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
                    <div class="tab-pane fade show active" id="guru-peminjaman" role="tabpanel" aria-labelledby="guru-tab">
                        <div class="data">
                            <div class="content-data">
                                <div class="head">
                                    <h3>Daftar Peminjaman Guru</h3>
                                </div>

                                <div class="table-responsive p-3">
                                    <table id="tableGuru" class="table align-items-center table-flush table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>No.</th>
                                                <th>No. Peminjaman</th>
                                                <th>Judul Buku</th>
                                                <th>Peminjam</th>
                                                <th>Tgl Pinjam</th>
                                                <th>Tgl Kembali</th>
                                                <th>Status</th>
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
                                                            @if ($item->status == 'Dipinjam')
                                                                <span class="badge">{{ $item->status }}</span>
                                                            @elseif ($item->status == 'Dikembalikan')
                                                                @if ($item->is_terlambat)
                                                                    <span class="badge">{{ $item->status }}</span>
                                                                    <span class="badge">Terlambat
                                                                        ({{ $item->jumlah_hari_terlambat }} hari)
                                                                    </span>
                                                                @else
                                                                    <span class="badge">{{ $item->status }}</span>
                                                                @endif
                                                            @elseif ($item->status == 'Terlambat')
                                                                <span class="badge">{{ $item->status }}
                                                                    ({{ $item->is_late ? $item->late_days : '?' }}
                                                                    hari)</span>
                                                            @else
                                                                <span class="badge">{{ $item->status }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="{{ route('peminjaman.detail', $item->id) }}"
                                                                    class="btn btn-sm btn-info" title="Detail">
                                                                    <i class="bx bx-info-circle"></i>
                                                                </a>

                                                                @if ($item->status == 'Dipinjam' || $item->status == 'Terlambat')
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-success return-btn"
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
                                </div>

                                <div class="table-responsive p-3">
                                    <table id="tableStaff" class="table align-items-center table-flush table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>No.</th>
                                                <th>No. Peminjaman</th>
                                                <th>Judul Buku</th>
                                                <th>Peminjam</th>
                                                <th>Tgl Pinjam</th>
                                                <th>Tgl Kembali</th>
                                                <th>Status</th>
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
                                                            @if ($item->status == 'Dipinjam')
                                                                <span class="badge">{{ $item->status }}</span>
                                                            @elseif ($item->status == 'Dikembalikan')
                                                                @if ($item->is_terlambat)
                                                                    <span class="badge">{{ $item->status }}</span>
                                                                    <span class="badge">Terlambat
                                                                        ({{ $item->jumlah_hari_terlambat }} hari)
                                                                    </span>
                                                                @else
                                                                    <span class="badge">{{ $item->status }}</span>
                                                                @endif
                                                            @elseif ($item->status == 'Terlambat')
                                                                <span class="badge">{{ $item->status }}
                                                                    ({{ $item->is_late ? $item->late_days : '?' }}
                                                                    hari)</span>
                                                            @else
                                                                <span class="badge">{{ $item->status }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="{{ route('peminjaman.detail', $item->id) }}"
                                                                    class="btn btn-sm btn-info" title="Detail">
                                                                    <i class="bx bx-info-circle"></i>
                                                                </a>

                                                                @if ($item->status == 'Dipinjam' || $item->status == 'Terlambat')
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-success return-btn"
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
            <!-- Tampilan untuk user non-admin -->
            <div class="data">
                <div class="content-data">
                    <div class="head">
                        <h3>Daftar Peminjaman {{ ucfirst(auth()->user()->level) }}</h3>
                    </div>
                    <div class="table-responsive p-3">
                        <table id="dataTable" class="table align-items-center table-flush table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>No.</th>
                                    <th>No. Peminjaman</th>
                                    <th>Judul Buku</th>
                                    <th>Peminjam</th>
                                    <th>Tgl Pinjam</th>
                                    <th>Tgl Kembali</th>
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
                                                @if ($item->status == 'Dipinjam')
                                                    <span class="badge">{{ $item->status }}</span>
                                                @elseif ($item->status == 'Dikembalikan')
                                                    @if ($item->is_terlambat)
                                                        <span class="badge">{{ $item->status }}</span>
                                                        <span class="badge">Terlambat
                                                            ({{ $item->jumlah_hari_terlambat }} hari)
                                                        </span>
                                                    @else
                                                        <span class="badge">{{ $item->status }}</span>
                                                    @endif
                                                @elseif ($item->status == 'Terlambat')
                                                    <span class="badge">{{ $item->status }}
                                                        ({{ $item->is_late ? $item->late_days : '?' }} hari)</span>
                                                @else
                                                    <span class="badge">{{ $item->status }}</span>
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
                    <form id="return-form" method="POST" style="display: inline;">
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
                    <form id="delete-form" method="POST" style="display: inline;">
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handler untuk tombol pengembalian
            const returnModal = document.getElementById('returnModal');
            const returnForm = document.getElementById('return-form');

            document.querySelectorAll('.return-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const actionUrl = this.getAttribute('data-action');
                    returnForm.setAttribute('action', actionUrl);
                });
            });

            // Handler untuk tombol hapus
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const actionUrl = this.getAttribute('data-action');
                    document.getElementById('delete-form').setAttribute('action', actionUrl);
                });
            });
        });

        // Inisialisasi DataTable untuk tabel siswa
        $('#tableSiswa').DataTable({
            responsive: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.0.2/i18n/id.json'
            }
        });

        // Inisialisasi DataTable untuk tabel guru
        $('#tableGuru').DataTable({
            responsive: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.0.2/i18n/id.json'
            }
        });

        // Inisialisasi DataTable untuk tabel staff
        $('#tableStaff').DataTable({
            responsive: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.0.2/i18n/id.json'
            }
        });
    </script>
@endsection
