<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pattern_categories', function (Blueprint $table): void {
            $table->unsignedBigInteger('replace_id')
                ->nullable()
                ->after('name');

            $table->foreign('replace_id')
                ->references('id')
                ->on('pattern_categories');
        });
    }

    public function down(): void
    {
        Schema::table('pattern_categories', function (Blueprint $table): void {
            $table->dropForeign(['replace_id']);

            $table->dropColumn('replace_id');
        });
    }
};
