<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->char('jawaban_pg', 5)->nullable();       // label pilihan: A/B/C/D/E
            $table->longText('jawaban_esai')->nullable();
            $table->decimal('skor', 6, 2)->nullable();
            $table->text('ai_feedback')->nullable();
            $table->enum('dinilai_oleh', ['ai', 'manual'])->nullable();
            $table->timestamps();

            $table->unique(['exam_attempt_id', 'question_id']);
            $table->index('exam_attempt_id');
            $table->index(['exam_attempt_id', 'dinilai_oleh']); // untuk filter koreksi manual
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_answers');
    }
};
