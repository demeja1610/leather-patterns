<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pattern_likes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pattern_id');
            $table->unsignedBigInteger('user_id');

            $table->timestamps();

            $table->foreign('pattern_id')
                ->references('id')
                ->on('patterns')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->unique(['pattern_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pattern_likes');
    }
};
