<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pattern_pattern_tag', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger(column: 'pattern_tag_id');
            $table->unsignedBigInteger(column: 'pattern_id');

            $table->foreign(columns: 'pattern_tag_id')->references('id')->on('pattern_tags')->onDelete('cascade');
            $table->foreign(columns: 'pattern_id')->references('id')->on('patterns')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pattern_pattern_tag');
    }
};
