<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pattern_pattern_category', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pattern_category_id');
            $table->unsignedBigInteger('pattern_id');

            $table->foreign('pattern_category_id')->references('id')->on('pattern_categories')->onDelete('cascade');
            $table->foreign('pattern_id')->references('id')->on('patterns')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pattern_pattern_category');
    }
};
