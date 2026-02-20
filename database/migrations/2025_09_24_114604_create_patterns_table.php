<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patterns', function (Blueprint $table): void {
            $table->id();

            $table->text(column: 'title')->nullable();
            $table->string(column: 'source');
            $table->text(column: 'source_url');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patterns');
    }
};
