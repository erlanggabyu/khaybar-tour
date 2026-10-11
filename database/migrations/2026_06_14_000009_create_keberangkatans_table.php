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
        Schema::create('keberangkatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftarans')->onDelete('cascade');
            $table->foreignId('paket_umroh_id')->constrained('paket_umroh')->onDelete('restrict');
            $table->date('tanggal_berangkat');
            $table->date('tanggal_kembali');
            $table->enum('status_keberangkatan', [
                'Belum Berangkat',
                'Siap Berangkat',
                'Berangkat',
                'Selesai',
                'Dibatalkan'
            ])->default('Belum Berangkat');
            $table->text('catatan_keberangkatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keberangkatans');
    }
};
