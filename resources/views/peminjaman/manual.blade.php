@extends('layouts.app')

@section('content')
    <div class="profile-page">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12">
                        <h1 class="title">Form Peminjaman Manual</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="divider">/</li>
                            <li class="breadcrumb-item"><a href="{{ route('peminjaman.index') }}">Peminjaman</a></li>
                            <li class="divider">/</li>
                            <li class="breadcrumb-item active" aria-current="page">Peminjaman Manual</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <!-- Detail Buku yang Dipinjam -->
                <div class="col-12 col-md-4">
                    <div class="profile-card">
                        <div class="card-body">
                            <h4 class="card-title">Detail Buku</h4>
                            <div class="row buku-container single-card">
                                <div class="col-12">
                                    <div class="card card-buku text-center align-items-center justify-content-center">
                                        <img id="bukuImage" class="card-img-top" style="max-height: 180px; display: none;"
                                            src="" alt="Book Cover">
                                        <img id="defaultImage" class="card-img-top" style="height: 200px;"
                                            src="{{ asset('assets/img/default_buku.png') }}" alt="Default Book Cover">
                                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                            <div class="detail-buku" style="width: 100%;">
                                                <div id="bukuInfo">
                                                    <h5 class="card-title text-center text-muted">
                                                        Pilih buku untuk melihat detail
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Peminjaman Manual -->
                <div class="col-12 col-md-8">
                    <div class="profile-card">
                        <div class="card-body">
                            <h4 class="card-title">Form Peminjaman Manual</h4>

                            <form method="POST" action="{{ route('peminjaman.manual.simpan') }}" id="manualPeminjamanForm">
                                @csrf

                                <!-- Pilih Buku -->
                                <div class="form-group mb-3">
                                    <label for="buku_id">Pilih Buku <span class="text-danger">*</span></label>
                                    <!-- Search Input dengan style yang sama seperti index buku -->
                                    <div class="navbar-search mb-2" style="max-width: 400px;">
                                        <div class="input-group">
                                            <input type="text" id="searchBuku"
                                                class="form-control bg-light border-1 small"
                                                placeholder="Cari judul buku..." aria-label="Search"
                                                aria-describedby="basic-addon2" autocomplete="off"
                                                style="border-color: #244fbc;">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="button" id="clearSearch"
                                                    title="Hapus pencarian">
                                                    <i class="bx bx-x"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <select name="buku_id" id="buku_id"
                                        class="form-control @error('buku_id') is-invalid @enderror" required
                                        style="width: 100%;">
                                        <option value="">-- Pilih Buku --</option>
                                        @foreach ($buku as $item)
                                            <option value="{{ $item->id }}" data-judul="{{ $item->judul }}"
                                                data-pengarang="{{ $item->pengarang }}"
                                                data-penerbit="{{ $item->penerbit }}" data-stok="{{ $item->stok_buku }}"
                                                data-kategori="{{ $item->kategori->pluck('nama')->implode(', ') }}"
                                                data-foto="{{ $item->foto }}" data-kode-buku="{{ $item->kode_buku }}"
                                                data-tahun-terbit="{{ $item->tahun_terbit }}"
                                                data-status="{{ $item->status }}"
                                                {{ old('buku_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->judul }} - {{ $item->pengarang }} (Stok:
                                                {{ $item->stok_buku }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('buku_id')
                                        <div class="custom-alert" role="alert">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z">
                                                </path>
                                            </svg>
                                            <p>{{ $message }}</p>
                                        </div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <!-- Level Anggota -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="user_level">Level Anggota <span
                                                    class="text-danger">*</span></label>
                                            <select name="user_level" id="user_level"
                                                class="form-control @error('user_level') is-invalid @enderror" required>
                                                <option value="">-- Pilih Level --</option>
                                                <option value="siswa"
                                                    {{ old('user_level') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                                                <option value="guru"
                                                    {{ old('user_level') == 'guru' ? 'selected' : '' }}>
                                                    Guru</option>
                                                <option value="staff"
                                                    {{ old('user_level') == 'staff' ? 'selected' : '' }}>Staff</option>
                                            </select>
                                            @error('user_level')
                                                <div class="custom-alert" role="alert">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                        <path
                                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z">
                                                        </path>
                                                    </svg>
                                                    <p>{{ $message }}</p>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Pilih Anggota -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="user_id">Pilih Anggota <span class="text-danger">*</span></label>
                                            <select name="user_id" id="user_id"
                                                class="form-control @error('user_id') is-invalid @enderror" required
                                                disabled>
                                                <option value="">-- Pilih level terlebih dahulu --</option>
                                            </select>
                                            @error('user_id')
                                                <div class="custom-alert" role="alert">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                        <path
                                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z">
                                                        </path>
                                                    </svg>
                                                    <p>{{ $message }}</p>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Tanggal Pinjam -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="tanggal_pinjam">Tanggal Pinjam</label>
                                            <input type="date" name="tanggal_pinjam" id="tanggal_pinjam"
                                                class="form-control @error('tanggal_pinjam') is-invalid @enderror"
                                                value="{{ old('tanggal_pinjam', date('Y-m-d')) }}"
                                                min="{{ date('Y-m-d') }}" required>
                                            @error('tanggal_pinjam')
                                                <div class="custom-alert" role="alert">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                        <path
                                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z">
                                                        </path>
                                                    </svg>
                                                    <p>{{ $message }}</p>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Tanggal Kembali -->
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="tanggal_kembali">Tanggal Kembali</label>
                                            <input type="date" name="tanggal_kembali" id="tanggal_kembali"
                                                class="form-control @error('tanggal_kembali') is-invalid @enderror"
                                                value="{{ old('tanggal_kembali') }}" min="{{ date('Y-m-d') }}"
                                                placeholder="yyyy-mm-dd" required>
                                            <small class="form-text" style="color: #6c757d; opacity: 0.7;">
                                                *Tanggal pengembalian maksimal 3 hari dari tanggal pinjam.
                                            </small>
                                            @error('tanggal_kembali')
                                                <div class="custom-alert" role="alert">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                        <path
                                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z">
                                                        </path>
                                                    </svg>
                                                    <p>{{ $message }}</p>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Catatan -->
                                <div class="form-group mb-3">
                                    <label for="catatan">Catatan (Opsional)</label>
                                    <textarea name="catatan" id="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3"
                                        maxlength="500" placeholder="Masukkan catatan Anda di sini (opsional)">{{ old('catatan') }}</textarea>
                                    @error('catatan')
                                        <div class="custom-alert" role="alert">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z">
                                                </path>
                                            </svg>
                                            <p>{{ $message }}</p>
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group text-end">
                                    <div class="d-flex justify-content-between">
                                        <button type="submit" class="btn btn-success" id="submitBtn">
                                            <i class="bx bx-book"></i> Simpan Peminjaman
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
        <div
            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: white;">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p>Memuat data anggota...</p>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Simpan opsi asli untuk pencarian buku
            var originalOptions = [];
            var bukuSelect = $('#buku_id');

            // Simpan semua opsi buku ke array untuk dibaca dalam pencarian
            bukuSelect.find('option').each(function() {
                originalOptions.push({
                    value: $(this).val(),
                    text: $(this).text(),
                    html: $(this)[0].outerHTML,
                    judul: $(this).attr('data-judul') || '',
                    pengarang: $(this).attr('data-pengarang') || ''
                });
            });

            // Fungsi pencarian buku
            $('#searchBuku').on('input keyup', function() {
                var kata = $(this).val().toLowerCase().trim();

                if (kata === '') {
                    // Tampilkan semua opsi
                    bukuSelect.find('option').remove();
                    originalOptions.forEach(function(option) {
                        bukuSelect.append(option.html);
                    });
                } else {
                    // Filter berdasarkan kata kunci
                    bukuSelect.find('option').remove();
                    bukuSelect.append('<option value="">-- Pilih Buku --</option>');

                    var hasilFilter = originalOptions.filter(function(option) {
                        if (option.value === '') return false;
                        return option.judul.toLowerCase().includes(kata) ||
                            option.pengarang.toLowerCase().includes(kata) ||
                            option.text.toLowerCase().includes(kata);
                    });

                    hasilFilter.forEach(function(option) {
                        bukuSelect.append(option.html);
                    });

                    // Tampilkan pesan jika tidak ada hasil
                    if (hasilFilter.length === 0) {
                        bukuSelect.append(
                            '<option value="" disabled>Tidak ada buku yang ditemukan</option>');
                    }
                }

                // Reset pilihan jika tidak ada di hasil filter
                if (bukuSelect.val() && bukuSelect.find('option:selected').length === 0) {
                    bukuSelect.val('').trigger('change');
                }
            });

            // Auto-pilih jika hanya 1 hasil saat Enter
            $('#searchBuku').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    var opsiTerlihat = bukuSelect.find('option:not(:first):not(:disabled)');
                    if (opsiTerlihat.length === 1) {
                        bukuSelect.val(opsiTerlihat.first().val()).trigger('change');
                        $(this).blur();
                    }
                }
            });

            // Update field pencarian saat buku dipilih
            $('#buku_id').on('change', function() {
                if ($(this).val()) {
                    var judulTerpilih = $(this).find('option:selected').attr('data-judul');
                    if (judulTerpilih) {
                        $('#searchBuku').val(judulTerpilih);
                    }
                } else {
                    $('#searchBuku').val('');
                }
            });

            // Tombol hapus pencarian
            $('#clearSearch').on('click', function() {
                $('#searchBuku').val('').trigger('input');
                $('#buku_id').val('').trigger('change'); // Reset pilihan buku dan detail
                $('#searchBuku').focus();
            });

            // Tampilkan detail buku saat dipilih
            $('#buku_id').on('change', function() {
                var bukuId = $(this).val();
                var selectedOption = $(this).find('option:selected');

                if (bukuId && bukuId !== '') {
                    // Ambil data buku
                    var judul = selectedOption.attr('data-judul');
                    var pengarang = selectedOption.attr('data-pengarang');
                    var penerbit = selectedOption.attr('data-penerbit');
                    var stok = selectedOption.attr('data-stok');
                    var kategori = selectedOption.attr('data-kategori');
                    var foto = selectedOption.attr('data-foto');
                    var kodeBuku = selectedOption.attr('data-kode-buku');
                    var tahunTerbit = selectedOption.attr('data-tahun-terbit');
                    var status = selectedOption.attr('data-status');

                    // Update gambar buku
                    if (foto) {
                        $('#bukuImage').attr('src', '{{ asset('assets/img/buku/') }}/' + foto)
                            .attr('alt', judul).show();
                        $('#defaultImage').hide();
                    } else {
                        $('#bukuImage').hide();
                        $('#defaultImage').show();
                    }

                    // Tentukan warna status
                    var warnaStatus = 'orange';
                    if (status === 'Tersedia') warnaStatus = 'green';
                    else if (status === 'Habis') warnaStatus = 'red';

                    // Update info buku
                    var infoBuku = `
                        <h5 class="card-title text-center">${judul || 'N/A'}</h5>
                        <p class="card-text m-0" style="text-align: justify;">Kode Buku: ${kodeBuku || 'N/A'}</p>
                        <p class="card-text m-0" style="text-align: justify;">Pengarang: ${pengarang || 'N/A'}</p>
                        <p class="card-text m-0" style="text-align: justify;">Kategori: ${kategori || 'N/A'}</p>
                        <p class="card-text m-0" style="text-align: justify;">Penerbit: ${penerbit || 'N/A'}</p>
                        <p class="card-text m-0" style="text-align: justify;">Tahun Terbit: ${tahunTerbit || 'N/A'}</p>
                        <p class="card-text m-0" style="text-align: justify;">Stok: ${stok || 'N/A'}</p>
                        <p class="card-text m-0" style="text-align: justify;">Status:
                            <span style="color: ${warnaStatus};">${status || 'N/A'}</span>
                        </p>
                    `;
                    $('#bukuInfo').html(infoBuku);
                } else {
                    // Reset ke keadaan awal
                    $('#bukuImage').hide();
                    $('#defaultImage').show();
                    $('#bukuInfo').html(`
                        <h5 class="card-title text-center text-muted">
                            Pilih buku untuk melihat detail
                        </h5>
                    `);
                }
            });

            // Load anggota berdasarkan level
            $('#user_level').change(function() {
                var level = $(this).val();
                var userSelect = $('#user_id');

                if (level) {
                    $('#loadingOverlay').show();
                    userSelect.prop('disabled', true).html('<option value="">Loading...</option>');

                    $.ajax({
                        url: "{{ url('/peminjaman/manual/get-anggota') }}/" + level,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            userSelect.html('<option value="">-- Pilih Anggota --</option>');

                            if (data.length > 0) {
                                $.each(data, function(index, anggota) {
                                    var selected = "{{ old('user_id') }}" == anggota
                                        .id ? 'selected' : '';
                                    userSelect.append('<option value="' + anggota.id +
                                        '" ' + selected + '>' +
                                        anggota.nama + ' (' + anggota.info +
                                        ')</option>');
                                });
                                userSelect.prop('disabled', false);
                            } else {
                                userSelect.html(
                                    '<option value="">Tidak ada anggota tersedia untuk level ' +
                                    level + '</option>');
                            }
                            $('#loadingOverlay').hide();
                        },
                        error: function() {
                            userSelect.html('<option value="">Error memuat data</option>');
                            $('#loadingOverlay').hide();
                            alert('Terjadi kesalahan saat memuat data anggota');
                        }
                    });
                } else {
                    userSelect.html('<option value="">-- Pilih level terlebih dahulu --</option>');
                    userSelect.prop('disabled', true);
                }
            });

            // Auto-set tanggal kembali (maksimal 3 hari)
            $('#tanggal_pinjam').change(function() {
                const tanggalPinjam = new Date($(this).val());
                if (tanggalPinjam) {
                    const tanggalKembali = new Date(tanggalPinjam);
                    tanggalKembali.setDate(tanggalPinjam.getDate() + 1);

                    const tanggalMax = new Date(tanggalPinjam);
                    tanggalMax.setDate(tanggalPinjam.getDate() + 3);

                    const inputKembali = $('#tanggal_kembali');
                    inputKembali.attr('min', tanggalKembali.toISOString().split('T')[0]);
                    inputKembali.attr('max', tanggalMax.toISOString().split('T')[0]);
                    inputKembali.val(tanggalKembali.toISOString().split('T')[0]);
                }
            });

            // Validasi tanggal kembali
            $('#tanggal_kembali').change(function() {
                var tanggalPinjam = new Date($('#tanggal_pinjam').val());
                var tanggalKembali = new Date($(this).val());
                var tanggalMax = new Date(tanggalPinjam);
                tanggalMax.setDate(tanggalPinjam.getDate() + 3);

                if (tanggalKembali <= tanggalPinjam) {
                    alert('Tanggal kembali harus setelah tanggal pinjam');
                    $(this).val('');
                } else if (tanggalKembali > tanggalMax) {
                    alert('Tanggal kembali maksimal 3 hari dari tanggal pinjam');
                    $(this).val('');
                }
            });

            // Trigger event untuk data lama
            if ($('#user_level').val()) $('#user_level').trigger('change');
            if ($('#buku_id').val()) $('#buku_id').trigger('change');
            $('#tanggal_pinjam').trigger('change');

            // Validasi form sebelum submit
            $('#manualPeminjamanForm').submit(function(e) {
                $('#submitBtn').prop('disabled', true).html(
                    '<i class="bx bx-loader bx-spin"></i> Menyimpan...');
            });
        });
    </script>
@endsection
