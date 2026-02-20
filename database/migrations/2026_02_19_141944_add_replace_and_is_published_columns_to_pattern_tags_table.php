<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pattern_tags', function (Blueprint $table): void {
            $table->unsignedBigInteger(column: 'replace_id')
                ->nullable()
                ->after('name');

            $table->unsignedBigInteger(column: 'replace_author_id')
                ->nullable()
                ->after('replace_id');

            $table->boolean(column: 'remove_on_appear')->default(false)->after('replace_author_id');

            $table->boolean(column: 'is_published')->default(false)->after('remove_on_appear')->index();

            $table->foreign(columns: 'replace_id')
                ->references('id')
                ->on('pattern_tags');

            $table->foreign(columns: 'replace_author_id')
                ->references('id')
                ->on('pattern_authors');
        });
    }

    public function down(): void
    {
        Schema::table('pattern_tags', function (Blueprint $table): void {
            $table->dropForeign(index: ['replace_id']);

            $table->dropColumn(columns: 'replace_id');

            $table->dropForeign(index: ['replace_author_id']);

            $table->dropColumn(columns: 'replace_author_id');

            $table->dropColumn(columns: 'remove_on_appear');

            $table->dropIndex(index: ['is_published']);

            $table->dropColumn(columns: 'is_published');
        });
    }
};
