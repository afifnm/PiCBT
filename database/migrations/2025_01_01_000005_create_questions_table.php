<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->cascadeOnDelete();
            $table->enum('tipe', ['pilihan_ganda', 'esai']);
            $table->longText('pertanyaan');
            $table->string('gambar')->nullable();       // path ke storage
            $table->decimal('bobot', 6, 2)->default(1);
            // PG: 'A'/'B'/'C'... | esai: rubrik / jawaban acuan untuk AI
            $table->text('kunci_jawaban')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['question_bank_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
