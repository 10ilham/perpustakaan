@extends('layouts.app')

@section('content')
    <div class="profile-page">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12">
                        <h1 class="title">Edit Buku</h1>
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
                            <li class="breadcrumb-item active" aria-current="page">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="profile-card">
                <div class="card-body">
                    <form action="{{ route('buku.update', $buku->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Informasi Dasar Buku -->
                                <div class="form-group">
                                    <label for="kode_buku">Kode Buku</label>
                                    <input type="text" name="kode_buku" id="kode_buku" class="form-control"
                                        value="{{ old('kode_buku', $buku->kode_buku) }}" required>
                                    @error('kode_buku')
                                        <div class="custom-alert" role="alert">
                                            <p>{{ $message }}</p>
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="judul">Judul Buku</label>
                                    <input type="text" name="judul" id="judul" class="form-control"
                                        value="{{ old('judul', $buku->judul) }}" required>
                                    @error('judul')
                                        <div class="custom-alert" role="alert">
                                            <p>{{ $message }}</p>
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="kategori_id">Kategori</label>
                                    <select name="kategori_id" id="kategori_id" class="form-control" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach ($kategori as $kat)
                                            <option value="{{ $kat->id }}"
                                                {{ old('kategori_id', $buku->kategori_id) == $kat->id ? 'selected' : '' }}>
                                                {{ $kat->nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('kategori_id')
                                        <div class="custom-alert" role="alert">
                                            <p>{{ $message }}</p>
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="pengarang">Pengarang</label>
                                    <input type="text" name="pengarang" id="pengarang" class="form-control"
                                        value="{{ old('pengarang', $buku->pengarang) }}" required>
                                    @error('pengarang')
                                        <div class="custom-alert" role="alert">
                                            <p>{{ $message }}</p>
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status">Status Buku</label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="Tersedia"
                                            {{ old('status', $buku->status) == 'Tersedia' ? 'selected' : '' }}>Tersedia
                                        </option>
                                        <option value="Dipinjam"
                                            {{ old('status', $buku->status) == 'Dipinjam' ? 'selected' : '' }}>Dipinjam
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="custom-alert" role="alert">
                                            <p>{{ $message }}</p>
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="penerbit">Penerbit</label>
                                    <input type="text" name="penerbit" id="penerbit" class="form-control"
                                        value="{{ old('penerbit', $buku->penerbit) }}" required>
                                    @error('penerbit')
                                        <div class="custom-alert" role="alert">
                                            <p>{{ $message }}</p>
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="tahun_terbit">Tahun Terbit</label>
                                    <input type="text" name="tahun_terbit" id="tahun_terbit" class="form-control"
                                        value="{{ old('tahun_terbit', $buku->tahun_terbit) }}" required>
                                    @error('tahun_terbit')
                                        <div class="custom-alert" role="alert">
                                            <p>{{ $message }}</p>
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="foto">Foto Sampul</label>
                                    <input type="file" name="foto" id="foto" class="form-control"
                                        onchange="previewImage(event)">
                                    <small class="form-text text-muted">Format: JPEG, PNG, JPG, GIF. Maksimal 2MB.
                                        Kosongkan jika tidak ingin mengubah foto sampul.</small>
                                    @error('foto')
                                        <div class="custom-alert" role="alert">
                                            <p>{{ $message }}</p>
                                        </div>
                                    @enderror

                                    <div class="mt-3" style="margin-bottom: 10px;">
                                        <label>Sampul Saat Ini:</label>
                                        <div
                                            style="border: 1px solid #ddd; border-radius: 5px; padding: 10px; display: inline-block;">
                                            @if ($buku->foto)
                                                <img src="{{ asset('assets/img/buku/' . $buku->foto) }}"
                                                    alt="{{ $buku->judul }}"
                                                    style="max-width: 150px; max-height: 200px;">
                                            @else
                                                <img src="{{ asset('assets/img/default-book.png') }}"
                                                    alt="Default Book Cover" style="max-width: 150px; max-height: 200px;">
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-3" id="preview-container" style="display: none;">
                                        <label for="preview">Preview Sampul Baru:</label>
                                        <div
                                            style="border: 1px solid #ddd; border-radius: 5px; padding: 10px; display: inline-block;">
                                            <img id="preview" src="#" alt="Preview Sampul"
                                                style="max-width: 150px; max-height: 200px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label for="deskripsi">Deskripsi Buku</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5" required>{{ old('deskripsi', $buku->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="custom-alert" role="alert">
                                    <p>{{ $message }}</p>
                                </div>
                            @enderror
                        </div>

                        <div class="form-group mt-4 text-end">
                            <a href="{{ route('buku.index') }}" class="btn btn-secondary me-2">
                                <i class="bx bx-arrow-back"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bx bx-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('preview');
                output.src = reader.result;
                document.getElementById('preview-container').style.display = 'block';
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        document.getElementById('tahun_terbit').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 4) {
                this.value = this.value.slice(0, 4);
            }
        });
    </script>
@endsection
