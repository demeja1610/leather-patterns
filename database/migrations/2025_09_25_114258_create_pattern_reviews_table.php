<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pattern_reviews', function (Blueprint $table): void {
            $table->id();

            $table->string(column: 'reviewer_name');
            $table->smallInteger(column: 'rating')->unsigned();
            $table->text(column: 'comment')->nullable();
            $table->timestamp(column: 'reviewed_at');
            $table->boolean(column: 'is_approved')->default(false);

            $table->unsignedBigInteger(column: 'user_id')->nullable();
            $table->foreign(columns: 'user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unsignedBigInteger(column: 'pattern_id');
            $table->foreign(columns: 'pattern_id')
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
