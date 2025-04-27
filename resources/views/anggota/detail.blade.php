@extends('layouts.app')

@section('content')
    <div class="profile-page">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h1 class="title">Detail Anggota</h1>
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
                            <li class="breadcrumb-item"><a href="{{ route('anggota.index') }}">Anggota</a></li>
                            <li class="divider">/</li>
                            <li class="breadcrumb-item active" aria-current="page">Detail</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <!-- Profil Anggota -->
                <div class="col-12 col-lg-4">
                    <div class="profile-card">
                        <div class="card-body text-center">
                            <div class="avatar">
                                @php
                                    $defaultImage = asset('assets/img/boy.png');
                                    $photoPath = $defaultImage;

                                    if ($user->level === 'admin' && isset($profileData->foto)) {
                                        $photoPath = asset('assets/img/admin_foto/' . $profileData->foto);
                                    } elseif ($user->level === 'siswa' && isset($profileData->foto)) {
                                        $photoPath = asset('assets/img/siswa_foto/' . $profileData->foto);
                                    } elseif ($user->level === 'guru' && isset($profileData->foto)) {
                                        $photoPath = asset('assets/img/guru_foto/' . $profileData->foto);
                                    } elseif ($user->level === 'staff' && isset($profileData->foto)) {
                                        $photoPath = asset('assets/img/staff_foto/' . $profileData->foto);
                                    }
                                @endphp
                                <img src="{{ $photoPath }}" alt="Foto Profil">
                            </div>
                            <h3 class="mt-3">{{ $user->nama }}</h3>
                            <p class="text-muted">
                                <span
                                    class="badge badge-outline-
                            @if ($user->level === 'admin') primary
                            @elseif($user->level === 'siswa')
                                success
                            @elseif($user->level === 'guru')
                                warning
                            @elseif($user->level === 'staff')
                                secondary @endif
                            ">
                                    {{ ucfirst($user->level) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Detail Informasi -->
                <div class="col-12 col-lg-8">
                    <div class="profile-card">
                        <div class="card-body">
                            <form class="profile-display">
                                <!-- Informasi Umum -->
                                <div class="form-group">
                                    <label for="nama">Nama Lengkap</label>
                                    <input type="text" id="nama" class="form-control" value="{{ $user->nama }}"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" class="form-control" value="{{ $user->email }}"
                                        readonly>
                                </div>

                                <!-- Informasi Khusus Sesuai Level -->
                                @if ($user->level === 'admin')
                                    <div class="form-group">
                                        <label for="nip">NIP</label>
                                        <input type="text" id="nip" class="form-control"
                                            value="{{ $profileData->nip ?? '-' }}" readonly>
                                    </div>
                                @elseif($user->level === 'siswa')
                                    <div class="form-group">
                                        <label for="nis">NIS</label>
                                        <input type="text" id="nis" class="form-control"
                                            value="{{ $profileData->nis ?? '-' }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="kelas">Kelas</label>
                                        <input type="text" id="kelas" class="form-control"
                                            value="{{ $profileData->kelas ?? '-' }}" readonly>
                                    </div>
                                @elseif($user->level === 'guru')
                                    <div class="form-group">
                                        <label for="nip">NIP</label>
                                        <input type="text" id="nip" class="form-control"
                                            value="{{ $profileData->nip ?? '-' }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="mapel">Mata Pelajaran</label>
                                        <input type="text" id="mapel" class="form-control"
                                            value="{{ $profileData->mapel ?? '-' }}" readonly>
                                    </div>
                                @elseif($user->level === 'staff')
                                    <div class="form-group">
                                        <label for="nip">NIP</label>
                                        <input type="text" id="nip" class="form-control"
                                            value="{{ $profileData->nip ?? '-' }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="bagian">Bagian</label>
                                        <input type="text" id="bagian" class="form-control"
                                            value="{{ $profileData->bagian ?? '-' }}" readonly>
                                    </div>
                                @endif

                                <!-- Informasi Kontak -->
                                <div class="form-group">
                                    <label for="tanggal_lahir">Tanggal Lahir</label>
                                    <input type="date" id="tanggal_lahir" class="form-control"
                                        value="{{ $profileData->tanggal_lahir ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <textarea id="alamat" class="form-control" rows="3" readonly>{{ $profileData->alamat ?? '-' }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="no_telepon">Nomor Telepon</label>
                                    <input type="text" id="no_telepon" class="form-control"
                                        value="{{ $profileData->no_telepon ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="created_at">Terdaftar Pada</label>
                                    <input type="text" id="created_at" class="form-control"
                                        value="{{ $user->created_at->format('d F Y H:i') }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="updated_at">Terakhir Diperbarui</label>
                                    <input type="text" id="updated_at" class="form-control"
                                        value="{{ $user->updated_at->format('d F Y H:i') }}" readonly>
                                </div>

                                <!-- Tombol Aksi -->
                                <div class="form-group">
                                    <div>
                                        <a href="{{ route('anggota.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back"></i> Kembali
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
