@extends('layouts.app')

@section('content')
    <!-- MAIN -->
    <main>
        <h1 class="title">Dashboard</h1>
        <ul class="breadcrumbs">
            <li><a>Home</a></li>
            <li class="divider">/</li>
            <li><a href="#" class="active">Dashboard</a></li>
        </ul>
        <div class="info-data">
            <!-- Card Jumlah Buku -->
            <div class="card">
                <div class="head">
                    <div>
                        <h2>{{ $totalBuku ?? '0' }}</h2>
                        <p>Jumlah Buku</p>
                    </div>
                    <i class='bx bx-book icon'></i>
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                    <a href="{{ route('buku.index') }}" class="label" style="color: #6f737b;">Lihat Detail</a>
                </div>
            </div>

            <!-- Card Jumlah Peminjaman -->
            <div class="card">
                <div class="head">
                    <div>
                        <h2>{{ $totalPeminjaman ?? '0' }}</h2>
                        <p>Jumlah Peminjaman</p>
                    </div>
                    <i class='bx bxs-book-open icon'></i>
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                    <a href="{{ route('peminjaman.index') }}" class="label" style="color: #6f737b;">Lihat Detail</a>
                </div>
            </div>

            <!-- Card Jumlah Kategori -->
            <div class="card">
                <div class="head">
                    <div>
                        <h2>{{ $totalKategori ?? '0' }}</h2>
                        <p>Jumlah Kategori</p>
                    </div>
                    <i class='bx bx-category icon'></i>
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                    <a href="{{ route('kategori.index') }}" class="label" style="color: #6f737b;">Lihat Detail</a>
                </div>
            </div>

            <!-- Card Jumlah Anggota -->
            <div class="card">
                <div class="head">
                    <div>
                        <h2>{{ $totalAnggota ?? '0' }}</h2>
                        <p>Jumlah Anggota</p>
                    </div>
                    <i class='bx bx-user icon'></i>
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                    <a href="{{ route('anggota.index') }}" class="label" style="color: #6f737b;">Lihat Detail</a>
                </div>
            </div>
        </div>

        <!-- Leaderboard Buku Populer -->
        <div class="data">
            <div class="content-data">
                <div class="head">
                    <h3>Leaderboard Buku Terpopuler</h3>
                    <div class="menu">
                        <i class='bx bx-dots-horizontal-rounded icon'></i>
                        <ul class="menu-link">
                            <li><a href="{{ route('buku.index') }}">Lihat Semua Buku</a></li>
                        </ul>
                    </div>
                </div>
                <div class="leaderboard">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Sampul</th>
                                <th>Judul Buku</th>
                                <th>Pengarang</th>
                                <th>Total Peminjaman</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($bukuPopuler) && count($bukuPopuler) > 0)
                                @foreach ($bukuPopuler as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if ($item->buku->foto)
                                                <img src="{{ asset('assets/img/buku/' . $item->buku->foto) }}"
                                                    alt="{{ $item->buku->judul }}"
                                                    style="width: 40px; height: 60px; object-fit: cover;">
                                            @else
                                                <img src="{{ asset('assets/img/default_buku.png') }}" alt="Default"
                                                    style="width: 40px; height: 60px; object-fit: cover;">
                                            @endif
                                        </td>
                                        <td>{{ $item->buku->judul }}</td>
                                        <td>{{ $item->buku->pengarang }}</td>
                                        <td>{{ $item->total_peminjaman }} kali</td>
                                        <td>
                                            <a href="{{ route('buku.detail', $item->buku->id) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="bx bx-info-circle"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada data peminjaman buku</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Laporan Peminjaman -->
        <div class="data">
            <div class="content-data">
                <div class="head">
                    <h3>Laporan Peminjaman</h3>
                    <div class="filter-period">
                        <select id="period-filter" class="form-control">
                            <option value="day" selected>1 Hari</option>
                            <option value="week">1 Minggu</option>
                            <option value="month">1 Bulan</option>
                        </select>
                    </div>
                </div>
                <div class="chart">
                    <div id="chart"></div>
                </div>
            </div>
        </div>
        <!-- End Laporan Peminjaman -->
    </main>
    <!-- END MAIN -->
@endsection

@section('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadChart('day'); // Default load 1 hari

            document.getElementById('period-filter').addEventListener('change', function() {
                const period = this.value;
                loadChart(period);
            });
        });

        function loadChart(period) {
            // Mendapatkan data berdasarkan periode
            fetch(`/admin/chart-data?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    renderChart(data);
                })
                .catch(error => {
                    console.error('Error loading chart data:', error);
                });
        }

        function renderChart(data) {
            // Destroy chart jika sudah ada
            if (window.chart) {
                window.chart.destroy();
            }

            const options = {
                series: [{
                        name: 'Siswa',
                        data: data.siswa || []
                    },
                    {
                        name: 'Guru',
                        data: data.guru || []
                    },
                    {
                        name: 'Staff',
                        data: data.staff || []
                    }
                ],
                chart: {
                    height: 350,
                    type: 'area'
                },
                colors: ['#1775f1', '#08ecf0', '#ffc107'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                title: {
                    text: 'Grafik Peminjaman Buku',
                    align: 'left'
                },
                grid: {
                    borderColor: '#e7e7e7',
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    }
                },
                xaxis: {
                    categories: data.labels || [],
                    title: {
                        text: 'Tanggal'
                    }
                },
                yaxis: {
                    title: {
                        text: 'Jumlah Peminjaman'
                    },
                    min: 0
                }
            };

            window.chart = new ApexCharts(document.querySelector("#chart"), options);
            window.chart.render();
        }
    </script>
@endsection

<style>
    .info-data .card {
        padding: 20px;
        border-radius: 10px;
        background-color: var(--light);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .info-data .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
    }

    .info-data .card .head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .info-data .card .count {
        font-size: 28px;
        font-weight: 600;
        color: var(--primary);
        margin: 15px 0 5px;
    }

    .info-data .card .label {
        display: block;
        text-align: right;
        font-size: 14px;
        color: var(--primary);
        text-decoration: none;
        margin-top: 10px;
        font-weight: 500;
        transition: color 0.3s;
    }

    .info-data .card .label:hover {
        color: var(--dark);
    }

    .info-data .card .icon {
        font-size: 30px;
        color: var(--primary);
        background-color: rgba(var(--primary-rgb), 0.1);
        padding: 8px;
        border-radius: 50%;
    }

    /* Leaderboard Styling */
    .leaderboard {
        margin-top: 20px;
        overflow-x: auto;
    }

    .leaderboard table {
        width: 100%;
        border-collapse: collapse;
    }

    .leaderboard th,
    .leaderboard td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .leaderboard th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .leaderboard tr:hover {
        background-color: #f1f1f1;
    }

    .leaderboard .btn-info {
        background-color: #17a2b8;
        color: white;
        border: none;
        padding: 5px 10px;
        font-size: 12px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .leaderboard .btn-info:hover {
        background-color: #138496;
    }
</style>
