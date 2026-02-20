<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table): void {
            $table->string(column: 'key')->primary();
            $table->mediumText(column: 'value');
            $table->integer(column: 'expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table): void {
            $table->string(column: 'key')->primary();
            $table->string(column: 'owner');
            $table->integer(column: 'expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
