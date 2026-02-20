<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('patterns', function (Blueprint $table): void {
            $table->string(column: 'source_url')->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('patterns', function (Blueprint $table): void {
            $table->dropUnique(index: ['source_url']);
        });
    }
};
