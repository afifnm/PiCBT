<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 20)->unique();
            $table->string('nama');
            $table->year('tahun_masuk');
            $table->string('jurusan')->nullable();
            // kelas_awal: override manual jika perlu (tidak dipakai untuk logika otomatis)
            $table->enum('kelas_awal', ['X', 'XI', 'XII'])->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tahun_masuk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
