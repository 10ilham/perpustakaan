@extends('layouts.app')

@section('content')
    <main>
        <h1 class="title">Laporan Sudah Dikembalikan</h1>
        <ul class="breadcrumbs">
            <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="divider">/</li>
            <li><a href="{{ route('laporan.index') }}">Laporan</a></li>
            <li class="divider">/</li>
            <li><a class="active">Sudah Dikembalikan</a></li>
        </ul>

        {{-- Button untuk kembali ke dashboard laporan --}}
        <div class="back-button">
            <a href="{{ route('laporan.index') }}" class="btn-secondary">
                <i class='bx bx-arrow-back'></i> Kembali
            </a>
        </div>

        <!-- Filter Form -->
        <div class="data">
            <div class="content-data">
                <div class="head">
                    <h3>Filter Laporan</h3>
                </div>
                <form method="GET" action="{{ route('laporan.sudah-kembali') }}" class="filter-form-grid">
                    <div class="filter-form-group">
                        <label for="tanggal_mulai" class="filter-form-label">Tanggal Mulai</label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                            class="filter-form-input">
                    </div>
                    <div class="filter-form-group">
                        <label for="tanggal_selesai" class="filter-form-label">Tanggal Selesai</label>
                        <input type="date" id="tanggal_selesai" name="tanggal_selesai"
                            value="{{ request('tanggal_selesai') }}" class="filter-form-input">
                    </div>
                    <div class="filter-form-group">
                        <label for="level" class="filter-form-label">Level User</label>
                        <select id="level" name="level" class="filter-form-input">
                            <option value="">Semua Level</option>
                            @foreach ($levels as $level)
                                <option value="{{ $level }}" {{ request('level') === $level ? 'selected' : '' }}>
                                    {{ ucfirst($level) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-buttons-container">
                        <button type="submit" class="btn-download btn-filter">
                            <i class='bx bx-search'></i> Filter
                        </button>
                        <a href="{{ route('laporan.sudah-kembali') }}" class="btn-download btn-reset">
                            <i class='bx bx-refresh'></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Table -->
        <div class="data">
            <div class="content-data">
                <div class="head">
                    <h3>Daftar Buku Sudah Dikembalikan</h3>
                    <div class="menu">
                        <span style="color: var(--dark-grey); font-size: 14px;">
                            <i class='bx bx-info-circle'></i> Gunakan tombol export di bawah tabel untuk mengunduh data
                        </span>
                    </div>
                </div>

                <p style="color: var(--dark-grey); margin-bottom: 20px;">Total: {{ $peminjamanSudahKembali->count() }}
                    peminjaman</p>

                @if ($peminjamanSudahKembali->count() > 0)
                    <div class="table-responsive p-3">
                        <table id="dataTableExport" class="table align-items-center table-flush table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Peminjam</th>
                                    <th>Email</th>
                                    <th>Level</th>
                                    <th>Buku</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Tanggal Dikembalikan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($peminjamanSudahKembali as $index => $peminjaman)
                                    @php
                                        $tanggalKembali = \Carbon\Carbon::parse($peminjaman->tanggal_kembali);
                                        $tanggalDikembalikan = \Carbon\Carbon::parse($peminjaman->tanggal_pengembalian);
                                        $statusPengembalian = 'Tepat Waktu';
                                        $statusClass = 'completed';

                                        if ($tanggalDikembalikan->gt($tanggalKembali)) {
                                            $hariTerlambat = $tanggalDikembalikan->diffInDays($tanggalKembali);
                                            $statusPengembalian = 'Terlambat ' . $hariTerlambat . ' hari';
                                            $statusClass = 'pending';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <span class="status">{{ $peminjaman->user->nama }}</span>
                                        </td>
                                        <td>
                                            <span class="status">{{ $peminjaman->user->email }}</span>
                                        </td>
                                        <td>
                                            <span class="status completed">{{ ucfirst($peminjaman->user->level) }}</span>
                                        </td>
                                        <td>
                                            <span class="status">{{ $peminjaman->buku->judul }}</span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($peminjaman->tanggal_kembali)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($peminjaman->tanggal_pengembalian)->format('d/m/Y') }}
                                        </td>
                                        <td>
                                            <span class="badge"
                                                style="background-color: {{ $statusClass === 'completed' ? '#28a745' : '#ffc107' }}; color: {{ $statusClass === 'completed' ? 'white' : 'black' }};">
                                                {{ $statusPengembalian }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('peminjaman.detail', ['id' => $peminjaman->id, 'ref' => 'laporan_sudah_kembali']) }}"
                                                    class="btn btn-sm btn-info" title="Detail">
                                                    <i class='bx bx-info-circle'></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align: center; padding: 40px;">
                        <i class='bx bx-info-circle'
                            style="font-size: 48px; color: var(--dark-grey); margin-bottom: 16px;"></i>
                        <p style="color: var(--dark-grey);">Tidak ada data peminjaman yang sudah dikembalikan dengan filter
                            tersebut.</p>
                    </div>
                @endif
            </div>
        </div>
    </main>
@endsection


@section('scripts')
    <!-- Additional library for Word export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable with export buttons
            $('#dataTableExport').DataTable({
                responsive: true,
                order: [
                    [0, 'asc']
                ], // Sort by the first column (No) in ascending order
                dom: '<"export-buttons-container"B>frtip',
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.0.2/i18n/id.json'
                },
                buttons: [{
                        extend: 'copy',
                        text: '<i class="bx bx-copy"></i><span>Copy</span>',
                        className: 'btn btn-outline-primary btn-sm export-btn',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'csv',
                        text: '<i class="bx bx-file"></i><span>CSV</span>',
                        className: 'btn btn-outline-success btn-sm export-btn',
                        filename: 'Laporan_Sudah_Dikembalikan_{{ date('d-m-Y') }}',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="bx bx-file-blank"></i><span>Excel</span>',
                        className: 'btn btn-outline-success btn-sm export-btn',
                        filename: 'Laporan_Sudah_Dikembalikan_{{ date('d-m-Y') }}',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        text: '<i class="bx bxs-file-doc"></i><span>Word</span>',
                        className: 'btn btn-outline-info btn-sm export-btn',
                        action: function(e, dt, button, config) {
                            exportToWord(dt);
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="bx bxs-file-pdf"></i><span>PDF</span>',
                        className: 'btn btn-outline-danger btn-sm export-btn',
                        filename: 'Laporan_Sudah_Dikembalikan_{{ date('d-m-Y') }}',
                        orientation: 'landscape',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="bx bx-printer"></i><span>Print</span>',
                        className: 'btn btn-outline-warning btn-sm export-btn',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    }
                ],
                columnDefs: [{
                        responsivePriority: 1,
                        targets: [0, 1, 4, 8] // No, Peminjam, Buku, Status
                    },
                    {
                        responsivePriority: 2,
                        targets: [9] // Aksi
                    },
                    {
                        orderable: false,
                        targets: [-1] // Kolom aksi tidak dapat diurutkan
                    }
                ]
            });

            // Function to export table data to Word format
            function exportToWord(dt) {
                // Get table data
                const data = dt.buttons.exportData({
                    columns: ':not(:last-child)' // Exclude actions column
                });

                // Create HTML content for Word document
                let htmlContent = `
                    <html xmlns:o="urn:schemas-microsoft-com:office:office"
                          xmlns:w="urn:schemas-microsoft-com:office:word"
                          xmlns="http://www.w3.org/TR/REC-html40">
                    <head>
                        <meta charset="utf-8">
                        <title>Laporan Buku Sudah Dikembalikan</title>
                        <!--[if gte mso 9]>
                        <xml>
                            <w:WordDocument>
                                <w:View>Print</w:View>
                                <w:Zoom>90</w:Zoom>
                                <w:Orientation>Landscape</w:Orientation>
                            </w:WordDocument>
                        </xml>
                        <![endif]-->
                        <style>
                            @page {
                                size: A4 landscape;
                                margin: 0.5in;
                            }
                            body {
                                font-family: Arial, sans-serif;
                                font-size: 10px;
                                margin: 0;
                                padding: 0;
                            }
                            .header {
                                text-align: center;
                                margin-bottom: 15px;
                            }
                            .header h2 {
                                font-size: 14px;
                                margin: 0 0 5px 0;
                            }
                            .date {
                                text-align: center;
                                margin-bottom: 15px;
                                color: #666;
                                font-size: 9px;
                            }
                            table {
                                border-collapse: collapse;
                                width: 100%;
                                font-size: 8px;
                                table-layout: fixed;
                            }
                            th, td {
                                border: 1px solid #ddd;
                                padding: 4px;
                                text-align: left;
                                word-wrap: break-word;
                                overflow-wrap: break-word;
                                vertical-align: top;
                            }
                            th {
                                background-color: #f2f2f2;
                                font-weight: bold;
                                font-size: 9px;
                            }
                            /* Specific column widths for sudah dikembalikan */
                            .col-no { width: 4%; }
                            .col-nama { width: 12%; }
                            .col-email { width: 15%; }
                            .col-level { width: 7%; }
                            .col-buku { width: 18%; }
                            .col-tgl-pinjam { width: 11%; }
                            .col-tgl-kembali { width: 11%; }
                            .col-tgl-dikembalikan { width: 11%; }
                            .col-status { width: 11%; }
                            .text-center { text-align: center; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h2>Laporan Buku Sudah Dikembalikan</h2>
                        </div>
                        <div class="date">
                            <p>Data per tanggal {{ date('d/m/Y') }}</p>
                        </div>
                        <table>
                            <thead>
                                <tr>`;

                // Add headers with specific classes
                const headerClasses = ['col-no', 'col-nama', 'col-email', 'col-level', 'col-buku', 'col-tgl-pinjam',
                    'col-tgl-kembali', 'col-tgl-dikembalikan', 'col-status'
                ];
                data.header.forEach(function(header, index) {
                    const className = headerClasses[index] || '';
                    htmlContent += `<th class="${className}">${header}</th>`;
                });

                htmlContent += `
                                </tr>
                            </thead>
                            <tbody>`;

                // Add data rows
                data.body.forEach(function(row) {
                    htmlContent += '<tr>';
                    row.forEach(function(cell, index) {
                        // Clean cell data (remove HTML tags and extra spaces)
                        let cleanCell = cell.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim();
                        const className = headerClasses[index] || '';
                        htmlContent += `<td class="${className}">${cleanCell}</td>`;
                    });
                    htmlContent += '</tr>';
                });

                htmlContent += `
                            </tbody>
                        </table>
                    </body>
                    </html>`;

                // Create blob and download
                const blob = new Blob([htmlContent], {
                    type: 'application/msword'
                });

                const fileName = 'Laporan_Sudah_Dikembalikan_{{ date('d-m-Y') }}.doc';

                // Use FileSaver.js to download the file
                saveAs(blob, fileName);
            }
        });
    </script>
@endsection
