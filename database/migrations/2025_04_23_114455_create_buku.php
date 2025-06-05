<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buku', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->string('kode_buku', 20)->unique();
            $table->string('judul', 60);
            $table->string('pengarang', 50);
            $table->string('penerbit', 50);
            $table->string('tahun_terbit', 4);
            $table->text('deskripsi');
            $table->string('foto', 150)->nullable();
            $table->unsignedInteger('total_buku')->default(0);
            $table->integer('stok_buku')->default(0);
            $table->string('status', 20)->default('Tersedia');
            $table->timestamps();

            // Foreign key
            $table->foreign('id_admin')->references('id')->on('admin')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku');
    }
};
