<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pattern_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('replace_id')
                ->nullable()
                ->after('name');

            $table->unsignedBigInteger('replace_author_id')
                ->nullable()
                ->after('replace_id');

            $table->boolean('remove_on_appear')->default(false)->after('replace_author_id');

            $table->boolean('is_published')->default(false)->after('remove_on_appear')->index();

            $table->foreign('replace_id')
                ->references('id')
                ->on('pattern_tags');

            $table->foreign('replace_author_id')
                ->references('id')
                ->on('pattern_authors');
        });
    }

    public function down(): void
    {
        Schema::table('pattern_tags', function (Blueprint $table) {
            $table->dropForeign(['replace_id']);

            $table->dropColumn('replace_id');

            $table->dropForeign(['replace_author_id']);

            $table->dropColumn('replace_author_id');

            $table->dropColumn('remove_on_appear');

            $table->dropIndex(['is_published']);

            $table->dropColumn('is_published');
        });
    }
};
