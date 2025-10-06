<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pattern_metas', function (Blueprint $table) {
            $table->id();

            $table->boolean('pattern_downloaded')->default(false);
            $table->boolean('images_downloaded')->default(false);
            $table->timestamp('reviews_updated_at')->nullable();

            $table->unsignedBigInteger('pattern_id')->unique();
            $table->foreign('pattern_id')
                ->references('id')
                ->on('patterns')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pattern_metas');
    }
};
