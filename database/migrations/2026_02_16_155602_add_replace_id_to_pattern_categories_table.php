<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pattern_categories', function (Blueprint $table): void {
            $table->unsignedBigInteger(column: 'replace_id')
                ->nullable()
                ->after('name');

            $table->foreign(columns: 'replace_id')
                ->references('id')
                ->on('pattern_categories');
        });
    }

    public function down(): void
    {
        Schema::table('pattern_categories', function (Blueprint $table): void {
            $table->dropForeign(index: ['replace_id']);

            $table->dropColumn(columns: 'replace_id');
        });
    }
};
