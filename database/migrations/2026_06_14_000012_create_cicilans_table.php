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
        Schema::create('cicilans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftarans')->onDelete('cascade');
            $table->unsignedInteger('cicilan_ke');
            $table->decimal('nominal', 15, 2);
            $table->date('due_date');
            $table->enum('status', ['Belum Bayar', 'Menunggu Verifikasi', 'Valid', 'Tidak Valid'])->default('Belum Bayar');
            $table->string('bukti_file')->nullable();
            $table->string('nama_file')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->integer('ukuran_file')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['pendaftaran_id', 'cicilan_ke']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cicilans');
    }
};
