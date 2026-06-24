<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->char('label', 1);              // A / B / C / D / E
            $table->text('teks_opsi');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();

            $table->unique(['question_id', 'label']);
            $table->index('question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
