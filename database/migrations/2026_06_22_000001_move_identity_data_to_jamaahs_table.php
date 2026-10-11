<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'nik')) {
                $table->dropColumn('nik');
            }

            if (Schema::hasColumn('users', 'no_ktp')) {
                $table->dropColumn('no_ktp');
            }
        });

        Schema::table('pelanggans', function (Blueprint $table): void {
            foreach (['alamat', 'tanggal_lahir', 'jenis_kelamin', 'no_ktp'] as $column) {
                if (Schema::hasColumn('pelanggans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('jamaahs', function (Blueprint $table): void {
            if (! Schema::hasColumn('jamaahs', 'nik')) {
                $table->string('nik')->after('nama_lengkap');
                $table->unique(['pendaftaran_id', 'nik']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('pelanggans', function (Blueprint $table): void {
            if (! Schema::hasColumn('pelanggans', 'alamat')) {
                $table->text('alamat')->nullable();
            }

            if (! Schema::hasColumn('pelanggans', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable();
            }

            if (! Schema::hasColumn('pelanggans', 'jenis_kelamin')) {
                $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            }

            if (! Schema::hasColumn('pelanggans', 'no_ktp')) {
                $table->string('no_ktp')->nullable();
            }
        });
    }
};
