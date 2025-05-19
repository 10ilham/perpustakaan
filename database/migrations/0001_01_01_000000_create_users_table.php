<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 80);
            $table->string('email', 70)->unique();
            $table->string('password', 64);
            $table->enum('level', ['admin', 'siswa', 'guru', 'staff']); // Ganti level
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 70)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Tabel users berdasarkan level
        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('nip', 20)->unique(); // Nomor Induk Pegawai
            $table->string('tanggal_lahir', 10);
            $table->string('alamat', 100);
            $table->string('no_telepon', 20);
            $table->string('foto', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('nis', 20)->unique(); // Nomor Induk Siswa
            $table->string('kelas', 10); // Kelas siswa
            $table->date('tanggal_lahir');
            $table->string('alamat', 100);
            $table->string('no_telepon', 20);
            $table->string('foto', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('nip', 20)->unique(); // Nomor Induk Pegawai
            $table->string('mata_pelajaran', 40); // Mata pelajaran yang diajarkan
            $table->date('tanggal_lahir');
            $table->string('alamat', 100);
            $table->string('no_telepon', 20);
            $table->string('foto', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke users
            $table->string('nip', 20)->unique(); // Nomor Induk Pegawai
            $table->string('bagian', 30);
            $table->date('tanggal_lahir');
            $table->string('alamat', 100);
            $table->string('no_telepon', 20);
            $table->string('foto', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('guru');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
