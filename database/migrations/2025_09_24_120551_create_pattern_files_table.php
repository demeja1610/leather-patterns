<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pattern_files', function (Blueprint $table): void {
            $table->id();

            $table->text(column: 'path');
            $table->string(column: 'type');
            $table->string(column: 'extension');
            $table->integer(column: 'size');
            $table->string(column: 'mime_type');
            $table->string(column: 'hash_algorithm');
            $table->string(column: 'hash');
            $table->unsignedBigInteger(column: 'pattern_id')->index();

            $table->foreign(columns: 'pattern_id')
                ->references('id')
                ->on('patterns')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pattern_files');
    }
};
