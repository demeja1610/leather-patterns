<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pattern_images', function (Blueprint $table): void {
            $table->id();

            $table->text('path');
            $table->string('extension');
            $table->integer('size');
            $table->string('mime_type');
            $table->string('hash_algorithm');
            $table->string('hash');

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
        Schema::dropIfExists('pattern_images');
    }
};
