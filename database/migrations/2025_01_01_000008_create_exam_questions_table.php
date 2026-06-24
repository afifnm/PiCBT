<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Snapshot soal & bobot saat ujian dibuat.
        // Perubahan bank soal setelah ujian dibuat tidak mempengaruhi skor.
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('urutan');
            $table->decimal('bobot_snapshot', 6, 2);   // snapshot bobot saat exam dibuat
            $table->timestamps();

            $table->unique(['exam_id', 'question_id']);
            $table->index(['exam_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
