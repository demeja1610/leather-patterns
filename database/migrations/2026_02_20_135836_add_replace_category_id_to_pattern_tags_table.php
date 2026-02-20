<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pattern_tags', function (Blueprint $table): void {
            $table->unsignedBigInteger(column: 'replace_category_id')
                ->nullable()
                ->after('replace_author_id');

            $table->foreign(columns: 'replace_category_id')
                ->references('id')
                ->on('pattern_categories');
        });
    }

    public function down(): void
    {
        Schema::table('pattern_tags', function (Blueprint $table): void {
            $table->dropForeign(index: ['replace_category_id']);

            $table->dropColumn(columns: 'replace_category_id');
        });
    }
};
