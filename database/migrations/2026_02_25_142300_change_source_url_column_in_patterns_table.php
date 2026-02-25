<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patterns', function (Blueprint $table) {
            $table->dropUnique(['source_url']);
            $table->string(column: 'source_url')->nullable()->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('patterns', function (Blueprint $table) {
            $table->dropUnique(['source_url']);
            $table->string(column: 'source_url')->unique()->change();
        });
    }
};
