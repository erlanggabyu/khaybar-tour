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
        Schema::create('pendaftarans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pendaftaran')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('paket_umroh_id')->constrained('paket_umroh')->onDelete('restrict');
            $table->date('tanggal_pendaftaran');
            $table->integer('jumlah_jamaah')->default(1);
            $table->decimal('total_harga', 15, 2);
            $table->enum('status_pendaftaran', [
                'Draft',
                'Menunggu Pembayaran DP',
                'Menunggu Verifikasi Pembayaran',
                'DP Terverifikasi',
                'Dokumen Belum Lengkap',
                'Dokumen Perlu Revisi',
                'Dokumen Lengkap',
                'Menunggu Pelunasan',
                'Lunas',
                'Siap Berangkat',
                'Berangkat',
                'Selesai',
                'Dibatalkan'
            ])->default('Draft');
            $table->text('catatan_pelanggan')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftarans');
    }
};
