<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pattern_reviews', function (Blueprint $table) {
            $table->id();

            $table->string('reviewer_name');
            $table->smallInteger('rating')->unsigned();
            $table->text('comment')->nullable();
            $table->timestamp('reviewed_at');
            $table->boolean('is_approved')->default(false);

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unsignedBigInteger('pattern_id');
            $table->foreign('pattern_id')
                ->references('id')
                ->on('patterns')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pattern_reviews');
    }
};
