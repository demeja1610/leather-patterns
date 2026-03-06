<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('pattern_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('replace_tag_id')
                ->nullable()
                ->after('replace_id');

            $table->foreign(columns: 'replace_tag_id')
                ->references('id')
                ->on('pattern_tags');
        });
    }

    public function down(): void
    {
        Schema::table('pattern_categories', function (Blueprint $table) {
            $table->dropForeign(['replace_tag_id']);

            $table->dropColumn('replace_tag_id');
        });
    }
};
