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
        Schema::create('dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jamaah_id')->constrained('jamaahs')->onDelete('cascade');
            $table->foreignId('pendaftaran_id')->constrained('pendaftarans')->onDelete('cascade');
            $table->enum('jenis_dokumen', ['KTP', 'Kartu Keluarga', 'Paspor', 'Foto', 'Buku Vaksin/Dokumen Kesehatan']);
            $table->string('nama_file');
            $table->string('path_file');
            $table->string('mime_type', 100);
            $table->integer('ukuran_file'); // in bytes
            $table->enum('status_dokumen', [
                'Belum Upload',
                'Menunggu Pemeriksaan',
                'Valid',
                'Tidak Valid',
                'Perlu Revisi'
            ])->default('Belum Upload');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['jamaah_id', 'jenis_dokumen']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumens');
    }
};
