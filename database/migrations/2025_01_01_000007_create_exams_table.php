<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->restrictOnDelete();
            $table->string('judul');
            $table->char('token', 8)->unique();        // atomic, 8-char alphanumeric
            $table->enum('target_kelas', ['X', 'XI', 'XII']);
            $table->year('target_tahun_masuk')->nullable(); // filter opsional by angkatan
            $table->unsignedSmallInteger('durasi_menit');
            $table->boolean('acak_soal')->default(false);
            $table->boolean('acak_opsi')->default(false);
            $table->dateTime('mulai_pada')->nullable();
            $table->dateTime('selesai_pada')->nullable();
            $table->unsignedTinyInteger('max_pelanggaran')->nullable(); // null = hanya rekam
            $table->boolean('auto_keluar')->default(false);
            $table->boolean('tampilkan_peringatan')->default(true); // false = rekam diam-diam tanpa peringatan ke siswa
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->timestamps();

            $table->index(['status', 'mulai_pada', 'selesai_pada']);
            $table->index('target_kelas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
