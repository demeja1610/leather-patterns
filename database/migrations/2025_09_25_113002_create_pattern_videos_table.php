<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pattern_videos', function (Blueprint $table): void {
            $table->id();

            $table->text('url');
            $table->string('source');
            $table->string('source_identifier')->nullable();

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
        Schema::dropIfExists('pattern_videos');
    }
};
