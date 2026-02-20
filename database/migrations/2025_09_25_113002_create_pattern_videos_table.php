<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pattern_videos', function (Blueprint $table): void {
            $table->id();

            $table->text(column: 'url');
            $table->string(column: 'source');
            $table->string(column: 'source_identifier')->nullable();

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
        Schema::dropIfExists('pattern_videos');
    }
};
