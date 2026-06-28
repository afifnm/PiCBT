<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->index('question_id');
        });

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->index('selesai_at');
        });
    }

    public function down(): void
    {
        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->dropIndex(['question_id']);
        });

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropIndex(['selesai_at']);
        });
    }
};
