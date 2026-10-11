<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cicilans', function (Blueprint $table): void {
            if (! Schema::hasColumn('cicilans', 'bukti_file')) {
                $table->string('bukti_file')->nullable()->after('status');
            }

            if (! Schema::hasColumn('cicilans', 'nama_file')) {
                $table->string('nama_file')->nullable()->after('bukti_file');
            }

            if (! Schema::hasColumn('cicilans', 'mime_type')) {
                $table->string('mime_type', 100)->nullable()->after('nama_file');
            }

            if (! Schema::hasColumn('cicilans', 'ukuran_file')) {
                $table->integer('ukuran_file')->nullable()->after('mime_type');
            }

            if (! Schema::hasColumn('cicilans', 'catatan_admin')) {
                $table->text('catatan_admin')->nullable()->after('ukuran_file');
            }

            if (! Schema::hasColumn('cicilans', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('catatan_admin')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('cicilans', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cicilans', function (Blueprint $table): void {
            foreach (['verified_at', 'verified_by', 'catatan_admin', 'ukuran_file', 'mime_type', 'nama_file', 'bukti_file'] as $column) {
                if (Schema::hasColumn('cicilans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
