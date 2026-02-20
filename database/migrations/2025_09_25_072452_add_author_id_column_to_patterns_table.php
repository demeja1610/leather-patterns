<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('patterns', function (Blueprint $table): void {
            $table->unsignedBigInteger(column: 'author_id')
                ->nullable()
                ->after('id');

            $table->foreign(columns: 'author_id')
                ->references('id')
                ->on('pattern_authors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patterns', function (Blueprint $table): void {
            $table->dropForeign(index: ['author_id']);
            $table->dropColumn(columns: 'author_id');
        });
    }
};
