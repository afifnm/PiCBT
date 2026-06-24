<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->dateTime('mulai_at')->nullable();
            $table->dateTime('selesai_at')->nullable();
            $table->dateTime('batas_waktu_at');                          // server-authoritative deadline
            $table->enum('status', ['berlangsung', 'selesai', 'dibatalkan', 'dikeluarkan'])
                  ->default('berlangsung');
            $table->decimal('total_skor', 8, 2)->nullable();
            $table->unsignedSmallInteger('jumlah_pelanggaran')->default(0);
            $table->boolean('is_void')->default(false);
            $table->string('void_reason')->nullable();
            $table->timestamps();

            // 1 siswa 1 attempt per ujian (non-void)
            $table->unique(['exam_id', 'student_id']);

            $table->index(['exam_id', 'status']);
            $table->index(['student_id', 'status']);
            $table->index('is_void');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
