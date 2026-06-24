<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->enum('jenis', [
                'blur',
                'fullscreen_exit',
                'key_ctrl',
                'key_copy',
                'key_paste',
                'key_print',
                'right_click',
                'dev_tools',
                'multi_window',
                'other',
            ]);
            $table->json('detail')->nullable();
            $table->dateTime('terjadi_at');
            $table->timestamps();

            $table->index(['exam_attempt_id', 'terjadi_at']);
            $table->index(['exam_attempt_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheat_logs');
    }
};
