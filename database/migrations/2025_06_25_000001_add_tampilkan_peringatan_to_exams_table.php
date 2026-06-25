<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // false = pelanggaran tetap direkam untuk admin, tapi tanpa peringatan ke siswa
            $table->boolean('tampilkan_peringatan')->default(true)->after('auto_keluar');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('tampilkan_peringatan');
        });
    }
};
