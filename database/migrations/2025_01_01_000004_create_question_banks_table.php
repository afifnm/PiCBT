<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_banks');
    }
};
