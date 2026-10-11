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
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->enum('jenis_laporan', ['Pendaftaran', 'Pembayaran', 'Rekap Jamaah']);
            $table->date('periode_awal')->nullable();
            $table->date('periode_akhir')->nullable();
            $table->longText('isi_laporan')->nullable(); // menyimpan log data laporan yang dicetak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};
