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
        Schema::create('paket_umroh', function (Blueprint $table) {
            $table->id();
            $table->string('kode_paket')->unique();
            $table->string('nama_paket');
            $table->string('jenis_perjalanan');
            $table->integer('durasi');
            $table->decimal('harga', 15, 2);
            $table->string('kota_keberangkatan');
            $table->date('tanggal_keberangkatan');
            $table->integer('kuota_jamaah');
            $table->integer('sisa_kuota');
            $table->text('fasilitas')->nullable();
            $table->text('jadwal_perjalanan')->nullable();
            $table->text('persyaratan_dokumen')->nullable();
            $table->enum('status_paket', ['tersedia', 'penuh', 'ditutup', 'selesai'])->default('tersedia');

            // Paket Umroh attributes
            $table->text('program_ibadah')->nullable();
            $table->string('hotel_mekkah')->nullable();
            $table->string('hotel_madinah')->nullable();
            $table->string('maskapai')->nullable();
            $table->string('embarkasi')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_umroh');
    }
};
